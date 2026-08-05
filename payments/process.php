<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit();
}

include_once "../config/database.php";
include_once "../helpers/csrf.php";
include "payment.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Invalid CSRF token"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$payment = new Payment($db);
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'generate_or_no':
            $orNo = $payment->generateOrNo();
            echo json_encode(['success' => true, 'or_no' => $orNo]);
            exit();

        case 'create':
            $payment->or_no = $_POST['or_no'] ?? '';
            $payment->or_date = $_POST['or_date'] ?? '';
            $payment->customer_id = $_POST['customer_id'] ?? '';
            $payment->amount_received = $_POST['amount_received'] ?? 0;
            $payment->payment_type = $_POST['payment_type'] ?? 'Cash';
            $payment->bank_id = !empty($_POST['bank_id']) ? $_POST['bank_id'] : null;
            $payment->cheque_no = $_POST['cheque_no'] ?? null;
            $payment->cheque_date = !empty($_POST['cheque_date']) ? $_POST['cheque_date'] : null;
            $payment->check_name = $_POST['check_name'] ?? null;
            $payment->check_amount = !empty($_POST['check_amount']) ? $_POST['check_amount'] : null;
            $payment->notes = $_POST['notes'] ?? '';
            $payment->created_by = $_SESSION['ley_billing_user_id'];

            // Debug logging
            error_log("Payment Create Debug: OR: {$payment->or_no}, Date: {$payment->or_date}, Cust: {$payment->customer_id}, Amt: {$payment->amount_received}");

            if (empty($payment->or_no) || empty($payment->or_date) || empty($payment->customer_id) || $payment->amount_received <= 0) {
                throw new Exception("Please fill in all required fields (OR: {$payment->or_no}, Date: {$payment->or_date}, Cust: {$payment->customer_id}, Amt: {$payment->amount_received})");
            }

            if ($payment->payment_type === 'Check' || $payment->payment_type === 'Bank Transfer') {
                if (empty($payment->bank_id)) {
                    throw new Exception("Bank selection is required for check/bank transfer payments");
                }
                if (empty($payment->cheque_no)) {
                    throw new Exception("Check number is required for check payments");
                }
            }

            if ($payment->isDuplicate('or_no', $payment->or_no)) {
                throw new Exception("OR Number already exists");
            }

            if ($payment->create()) {
                if (!empty($_POST['invoice_allocations'])) {
                    $invoice_allocations = json_decode($_POST['invoice_allocations'], true);
                    if (!$payment->applyToInvoices($invoice_allocations)) {
                        throw new Exception("Payment created but failed to allocate to invoices");
                    }
                }

                include_once "../activity_log/activity_log.php";
                $activity_log = new ActivityLog($db);
                $activity_log->user_id = $_SESSION['ley_billing_user_id'];
                $activity_log->action = 'CREATE';
                $activity_log->module = 'Payments';
                $activity_log->details = "Created payment OR# " . $payment->or_no . " for customer ID " . $payment->customer_id;
                $activity_log->create();

                echo json_encode([
                    "success" => true,
                    "message" => "Payment recorded successfully",
                    "payment_id" => $payment->id
                ]);
            } else {
                throw new Exception("Failed to record payment");
            }
            break;

        case 'update':
            $payment->id = $_POST['payment_id'] ?? '';
            $payment->or_no = $_POST['or_no'] ?? '';
            $payment->or_date = $_POST['or_date'] ?? '';
            $payment->customer_id = $_POST['customer_id'] ?? '';
            $payment->amount_received = $_POST['amount_received'] ?? 0;
            $payment->payment_type = $_POST['payment_type'] ?? 'Cash';
            $payment->bank_id = !empty($_POST['bank_id']) ? $_POST['bank_id'] : null;
            $payment->cheque_no = $_POST['cheque_no'] ?? null;
            $payment->cheque_date = !empty($_POST['cheque_date']) ? $_POST['cheque_date'] : null;
            $payment->check_name = $_POST['check_name'] ?? null;
            $payment->check_amount = !empty($_POST['check_amount']) ? $_POST['check_amount'] : null;
            $payment->notes = $_POST['notes'] ?? '';

            if (empty($payment->id) || empty($payment->or_no) || empty($payment->or_date) || empty($payment->customer_id) || $payment->amount_received <= 0) {
                throw new Exception("Please fill in all required fields");
            }

            if ($payment->isDuplicate('or_no', $payment->or_no, $payment->id)) {
                throw new Exception("OR Number already exists");
            }

            if ($payment->update()) {
                // Handle invoice allocations for update
                if (!empty($_POST['invoice_allocations'])) {
                    $invoice_allocations = json_decode($_POST['invoice_allocations'], true);

                    // Get old allocations to recalculate status later
                    $old_allocations = $payment->getPaymentAllocations($payment->id);

                    // Delete existing allocations
                    $payment->deleteAllocations($payment->id);

                    // Update status for items that had allocations removed
                    $is_government = $payment->isGovernmentCustomer($payment->customer_id);

                    foreach ($old_allocations as $alloc) {
                        $payment->recalculateInvoiceStatus($alloc['invoice_id'], $is_government);
                    }

                    // Add new allocations
                    if (!$payment->applyToInvoices($invoice_allocations)) {
                        throw new Exception("Failed to update invoice allocations");
                    }
                }

                include_once "../activity_log/activity_log.php";
                $activity_log = new ActivityLog($db);
                $activity_log->user_id = $_SESSION['ley_billing_user_id'];
                $activity_log->action = 'UPDATE';
                $activity_log->module = 'Payments';
                $activity_log->details = "Updated payment OR# " . $payment->or_no;
                $activity_log->create();

                echo json_encode([
                    "success" => true,
                    "message" => "Payment updated successfully"
                ]);
            } else {
                throw new Exception("Failed to update payment");
            }
            break;

        case 'delete':
            $payment->id = $_POST['payment_id'] ?? '';

            if (empty($payment->id)) {
                throw new Exception("Invalid payment ID");
            }

            $payment_data = $payment->readOne();
            if (!$payment_data) {
                throw new Exception("Payment not found");
            }

            // Get allocations before deletion
            $old_allocations = $payment->getPaymentAllocations($payment->id);
            $is_government = ($payment_data['customer_type'] === 'Government');

            if ($payment->delete()) {
                // Update status for invoices that had allocations removed
                foreach ($old_allocations as $alloc) {
                    $payment->recalculateInvoiceStatus($alloc['invoice_id'], $is_government);
                }

                include_once "../activity_log/activity_log.php";
                $activity_log = new ActivityLog($db);
                $activity_log->user_id = $_SESSION['ley_billing_user_id'];
                $activity_log->action = 'DELETE';
                $activity_log->module = 'Payments';
                $activity_log->details = "Deleted payment OR# " . $payment_data['or_no'];
                $activity_log->create();

                echo json_encode([
                    "success" => true,
                    "message" => "Payment deleted successfully"
                ]);
            } else {
                throw new Exception("Failed to delete payment");
            }
            break;

        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
