<?php
// Handle AJAX requests for getting invoice details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Only set header for JSON responses, not if we are redirecting or if headers already sent
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    include_once __DIR__ . '/../config/session.php';
    include_once __DIR__ . '/../config/database.php';
    include_once "charge_invoice.php";
    include_once "../helpers/csrf.php";
    include_once "../helpers/activity_logger.php";
    
    if (!isset($_SESSION['ley_billing_user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        $chargeInvoice = new ChargeInvoice($db);
        
        $action = $_POST['action'];

        if ($action === 'get_invoice' && isset($_POST['id'])) {
            $invoice = $chargeInvoice->getById($_POST['id']);
            if (!$invoice) {
                echo json_encode(['error' => 'Invoice not found']);
            } else {
                echo json_encode($invoice);
            }
            exit();
        }

        if ($action === 'get_available_deliveries' && isset($_POST['customer_id'])) {
            $deliveries = $chargeInvoice->getDeliveriesForSelect($_POST['customer_id']);
            echo json_encode(['success' => true, 'deliveries' => $deliveries]);
            exit();
        }

        // Verify CSRF for modifying actions
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
             throw new Exception('Invalid security token');
        }

        switch ($action) {
            case 'create':
                // Validate customer exists
                if (!isset($_POST['customer_id'])) {
                    throw new Exception('Customer is required');
                }
                
                // Validate deliveries selected
                if (!isset($_POST['delivery_ids']) || empty($_POST['delivery_ids'])) {
                     throw new Exception('At least one delivery must be selected');
                }
                
                $delivery_ids = $_POST['delivery_ids'];
                if (!is_array($delivery_ids)) {
                    // Handle if it comes as a string (e.g. from some form serialization methods)
                    // But usually jQuery serialize array handles this. 
                    // If JSON string:
                    $delivery_ids = json_decode($delivery_ids, true);
                }

                $data = [
                    'invoice_no' => $_POST['invoice_no'],
                    'invoice_date' => $_POST['invoice_date'],
                    'customer_id' => $_POST['customer_id'],
                    'address' => $_POST['address'],
                    'payment_status' => $_POST['payment_status'] ?? 'Unpaid',
                    'created_by' => $_SESSION['ley_billing_user_id'],
                    'delivery_ids' => $delivery_ids
                ];
                
                $result = $chargeInvoice->create($data);
                
                if ($result['success']) {
                    log_activity(
                        $db,
                        $_SESSION['ley_billing_user_id'], 
                        'Created Charge Invoice', 
                        'Charge Invoices', 
                        "Created charge invoice '{$data['invoice_no']}' (ID: {$result['id']})"
                    );
                    echo json_encode(['success' => true, 'message' => 'Charge invoice created successfully', 'id' => $result['id']]);
                } else {
                    throw new Exception($result['message'] ?? 'Failed to create invoice');
                }
                break;
                
            case 'update':
                if (!isset($_POST['id'])) {
                    throw new Exception('Invoice ID is required');
                }
                
                 // Validate deliveries selected
                if (!isset($_POST['delivery_ids']) || empty($_POST['delivery_ids'])) {
                     throw new Exception('At least one delivery must be selected');
                }

                 $delivery_ids = $_POST['delivery_ids'];
                if (!is_array($delivery_ids)) {
                     $delivery_ids = json_decode($delivery_ids, true);
                }
                
                $data = [
                    'invoice_no' => $_POST['invoice_no'],
                    'invoice_date' => $_POST['invoice_date'],
                    'customer_id' => $_POST['customer_id'],
                    'address' => $_POST['address'],
                    'payment_status' => $_POST['payment_status'],
                    'delivery_ids' => $delivery_ids
                ];
                
                $result = $chargeInvoice->update($_POST['id'], $data);
                
                if ($result['success']) {
                    log_activity(
                        $db,
                        $_SESSION['ley_billing_user_id'], 
                        'Updated Charge Invoice', 
                        'Charge Invoices', 
                        "Updated charge invoice '{$data['invoice_no']}' (ID: {$_POST['id']})"
                    );
                    echo json_encode(['success' => true, 'message' => 'Charge invoice updated successfully']);
                } else {
                    throw new Exception($result['message'] ?? 'Failed to update invoice');
                }
                break;
                
            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Invoice ID is required');
                }
                
                $invoice = $chargeInvoice->getById($_POST['id']);
                if (!$invoice) {
                    throw new Exception('Invoice not found');
                }
                
                $result = $chargeInvoice->delete($_POST['id']);
                
                if ($result['success']) {
                    log_activity(
                        $db,
                        $_SESSION['ley_billing_user_id'], 
                        'Deleted Charge Invoice', 
                        'Charge Invoices', 
                        "Deleted charge invoice with ID: {$_POST['id']}"
                    );
                    echo json_encode(['success' => true, 'message' => 'Charge invoice deleted successfully']);
                } else {
                    throw new Exception($result['message'] ?? 'Failed to delete invoice');
                }
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        error_log("Charge Invoice Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}