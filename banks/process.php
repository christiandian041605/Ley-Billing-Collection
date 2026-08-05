<?php
session_start();
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../helpers/activity_logger.php';
require_once '../helpers/csrf.php';
require_once 'bank.php';

// Check authentication
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user is Admin or Encoder
if (!in_array($_SESSION['role'], ['Admin', 'Encoder'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$bank = new Bank();
$action = $_POST['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'create':
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF token verification failed']);
            exit;
        }

        $result = $bank->create($_POST);
        
        if ($result['success']) {
            log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Created Bank', 'Banks', 
                        "Added new bank: {$_POST['bank_name']} - {$_POST['branch_name']}");
        }
        
        echo json_encode($result);
        break;

    case 'update':
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF token verification failed']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Bank ID is required']);
            exit;
        }

        $result = $bank->update($id, $_POST);
        
        if ($result['success']) {
            log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Updated Bank', 'Banks', 
                        "Updated bank ID: $id");
        }
        
        echo json_encode($result);
        break;

    case 'delete':
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Bank ID is required']);
            exit;
        }

        $result = $bank->delete($id);
        
        if ($result['success']) {
            log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Deleted Bank', 'Banks', 
                        "Deleted bank ID: $id");
        }
        
        echo json_encode($result);
        break;

    case 'get':
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF token verification failed']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Bank ID is required']);
            exit;
        }

        $bankData = $bank->getById($id);
        
        if ($bankData) {
            echo json_encode(['success' => true, 'data' => $bankData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bank not found']);
        }
        break;

    case 'toggle_status':
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Bank ID is required']);
            exit;
        }

        $result = $bank->toggleStatus($id);
        
        if ($result['success']) {
            log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Updated Bank Status', 'Banks', 
                        "Toggled status for bank ID: $id");
        }
        
        echo json_encode($result);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}
?>
