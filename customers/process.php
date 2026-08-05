<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(0);

include_once "../config/database.php";
include "../config/app.php";
include "customer.php";
include_once __DIR__ . '/../helpers/activity_logger.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$customer = new Customer($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            // Set customer properties
            $customer->customer_type = $_POST['customer_type'] ?? '';
            $customer->name = $_POST['name'] ?? '';
            $customer->business_name = $_POST['business_name'] ?? '';
            $customer->address = $_POST['address'] ?? '';
            $customer->contact_number = $_POST['contact_number'] ?? '';
            $customer->email = $_POST['email'] ?? '';

            // Validation
            if (empty($customer->customer_type)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer type is required']);
                exit();
            }

            if (empty($customer->name)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer name is required']);
                exit();
            }

            // Validate customer type
            if (!in_array($customer->customer_type, ['Private', 'Business', 'Government'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid customer type']);
                exit();
            }

            // Validate email format if provided
            if (!empty($customer->email) && !filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit();
            }

            // Check if customer name already exists
            if ($customer->nameExists()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'A customer with this name already exists']);
                exit();
            }

            if ($customer->create()) {
                // Log the activity
                $activity_details = "Created new customer '" . htmlspecialchars($customer->name) . "' (ID: " . $customer->id . ", Type: " . $customer->customer_type . ")";
                log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Created Customer', 'Customers', $activity_details);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Customer created successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create customer']);
            }
            break;

        case 'update':
            $customer_id = $_POST['customer_id'] ?? '';

            if (empty($customer_id)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
                exit();
            }

            // Get current customer data for comparison
            $customer->id = $customer_id;
            if (!$customer->readOne()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer not found']);
                exit();
            }

            // Store original values for comparison
            $original_data = [
                'customer_type' => $customer->customer_type,
                'name' => $customer->name,
                'business_name' => $customer->business_name,
                'address' => $customer->address,
                'contact_number' => $customer->contact_number,
                'email' => $customer->email
            ];

            // Set new values
            $customer->customer_type = $_POST['customer_type'] ?? '';
            $customer->name = $_POST['name'] ?? '';
            $customer->business_name = $_POST['business_name'] ?? '';
            $customer->address = $_POST['address'] ?? '';
            $customer->contact_number = $_POST['contact_number'] ?? '';
            $customer->email = $_POST['email'] ?? '';

            // Validation
            if (empty($customer->customer_type)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer type is required']);
                exit();
            }

            if (empty($customer->name)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer name is required']);
                exit();
            }

            // Validate customer type
            if (!in_array($customer->customer_type, ['Private', 'Business', 'Government'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid customer type']);
                exit();
            }

            // Validate email format if provided
            if (!empty($customer->email) && !filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                exit();
            }

            // Check if customer name already exists (excluding current customer)
            if ($customer->nameExists($customer_id)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'A customer with this name already exists']);
                exit();
            }

            // Check for changes
            $changes = [];
            $new_data = [
                'customer_type' => $customer->customer_type,
                'name' => $customer->name,
                'business_name' => $customer->business_name,
                'address' => $customer->address,
                'contact_number' => $customer->contact_number,
                'email' => $customer->email
            ];

            foreach ($new_data as $field => $new_value) {
                $old_value = $original_data[$field] ?? '';
                if ($old_value !== $new_value) {
                    $field_name = ucwords(str_replace('_', ' ', $field));
                    if (empty($old_value)) {
                        $changes[] = "{$field_name} set to '{$new_value}'";
                    } else if (empty($new_value)) {
                        $changes[] = "{$field_name} cleared";
                    } else {
                        $changes[] = "{$field_name} changed from '{$old_value}' to '{$new_value}'";
                    }
                }
            }

            if (empty($changes)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No changes detected']);
                exit();
            }

            if ($customer->update()) {
                // Log the activity
                $activity_details = "Updated customer '" . htmlspecialchars($customer->name) . "': " . implode(', ', $changes);
                log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Updated Customer', 'Customers', $activity_details);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update customer']);
            }
            break;

        case 'delete':
            $customer_id = $_POST['customer_id'] ?? '';

            if (empty($customer_id)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
                exit();
            }

            $customer->id = $customer_id;
            
            // Get customer data for logging
            if (!$customer->readOne()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer not found']);
                exit();
            }

            $customer_name = $customer->name;

            // Check if customer has related records
            if ($customer->hasRelatedRecords()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot delete customer. This customer has related invoices, deliveries, or payments.']);
                exit();
            }

            if ($customer->delete()) {
                // Log the activity
                $activity_details = "Deleted customer '" . htmlspecialchars($customer_name) . "' (ID: " . $customer_id . ")";
                log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Deleted Customer', 'Customers', $activity_details);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to delete customer']);
            }
            break;

        case 'get':
            $customer_id = $_POST['customer_id'] ?? '';

            if (empty($customer_id)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
                exit();
            }

            $customer->id = $customer_id;
            if ($customer->readOne()) {
                $customer_data = [
                    'id' => $customer->id,
                    'customer_type' => $customer->customer_type,
                    'name' => $customer->name,
                    'business_name' => $customer->business_name,
                    'address' => $customer->address,
                    'contact_number' => $customer->contact_number,
                    'email' => $customer->email,
                    'created_at' => $customer->created_at
                ];

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'data' => $customer_data]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Customer not found']);
            }
            break;

        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>