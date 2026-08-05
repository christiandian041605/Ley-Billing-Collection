<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include_once "../helpers/csrf.php";
include_once "../helpers/activity_logger.php";
include "delivery.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $delivery = new Delivery($db);

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $delivery->delivery_no = $_POST['delivery_no'] ?? '';
            $delivery->delivery_date = $_POST['delivery_date'] ?? '';
            $delivery->customer_id = $_POST['customer_id'] ?? '';
            $delivery->delivery_type = $_POST['delivery_type'] ?? 'DR V';
            $delivery->address = $_POST['address'] ?? '';
            $delivery->checker = $_POST['checker'] ?? '';
            $delivery->driver = $_POST['driver'] ?? '';
            $delivery->plate_number = $_POST['plate_number'] ?? '';
            $delivery->total_amount = $_POST['total_amount'] ?? 0;
            $delivery->created_by = $_SESSION['ley_billing_user_id'];

            if ($delivery->deliveryNoExists()) {
                echo json_encode(['success' => false, 'message' => 'Delivery number already exists']);
                exit();
            }

            if ($delivery->create()) {


                log_activity(
                    $db,
                    $_SESSION['ley_billing_user_id'],
                    'CREATE',
                    'Deliveries',
                    'Created delivery: ' . $delivery->delivery_no
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Delivery created successfully',
                    'delivery_id' => $delivery->id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create delivery']);
            }
            break;

        case 'read':
            if (!isset($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'Delivery ID required']);
                exit();
            }

            $delivery->id = $_POST['id'];

            if ($delivery->readOne()) {
                $linked_invoices = $delivery->getLinkedInvoices();

                $response = [
                    'success' => true,
                    'delivery' => [
                        'id' => (string) $delivery->id,
                        'delivery_no' => $delivery->delivery_no,
                        'delivery_date' => $delivery->delivery_date,
                        'customer_id' => (string) $delivery->customer_id,
                        'delivery_type' => $delivery->delivery_type,
                        'address' => $delivery->address,
                        'checker' => $delivery->checker,
                        'driver' => $delivery->driver,
                        'plate_number' => $delivery->plate_number,
                        'total_amount' => $delivery->total_amount,
                        'created_at' => $delivery->created_at
                    ],
                    'invoices' => $linked_invoices
                ];

                echo json_encode($response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Delivery not found']);
            }
            break;

        case 'update':
            if (!isset($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'Delivery ID required']);
                exit();
            }

            $delivery->id = $_POST['id'];
            $delivery->delivery_no = $_POST['delivery_no'] ?? '';
            $delivery->delivery_date = $_POST['delivery_date'] ?? '';
            $delivery->customer_id = $_POST['customer_id'] ?? '';
            $delivery->delivery_type = $_POST['delivery_type'] ?? 'DR V';
            $delivery->address = $_POST['address'] ?? '';
            $delivery->checker = $_POST['checker'] ?? '';
            $delivery->driver = $_POST['driver'] ?? '';
            $delivery->plate_number = $_POST['plate_number'] ?? '';
            $delivery->total_amount = $_POST['total_amount'] ?? 0;

            if ($delivery->deliveryNoExists($delivery->id)) {
                echo json_encode(['success' => false, 'message' => 'Delivery number already exists']);
                exit();
            }

            if ($delivery->update()) {


                log_activity(
                    $db,
                    $_SESSION['ley_billing_user_id'],
                    'UPDATE',
                    'Deliveries',
                    'Updated delivery: ' . $delivery->delivery_no
                );

                echo json_encode(['success' => true, 'message' => 'Delivery updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update delivery']);
            }
            break;

        case 'delete':
            if (!isset($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'Delivery ID required']);
                exit();
            }

            $delivery->id = $_POST['id'];
            $delivery->readOne();
            $delivery_no = $delivery->delivery_no;

            if ($delivery->delete()) {
                log_activity(
                    $db,
                    $_SESSION['ley_billing_user_id'],
                    'DELETE',
                    'Deliveries',
                    'Deleted delivery: ' . $delivery_no
                );

                echo json_encode(['success' => true, 'message' => 'Delivery deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete delivery']);
            }
            break;

        case 'generate_delivery_no':
            $new_delivery_no = $delivery->generateDeliveryNo();
            echo json_encode(['success' => true, 'delivery_no' => $new_delivery_no]);
            break;



        case 'get_customer_details':
            if (!isset($_POST['customer_id'])) {
                echo json_encode(['success' => false, 'message' => 'Customer ID required']);
                exit();
            }

            include_once "../customers/customer.php";
            $customer = new Customer($db);
            $customer->id = $_POST['customer_id'];

            if ($customer->readOne()) {
                echo json_encode([
                    'success' => true,
                    'customer_type' => $customer->customer_type,
                    'address' => $customer->address
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Customer not found']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }

} catch (Exception $e) {
    error_log("Process delivery error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>