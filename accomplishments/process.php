<?php
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../helpers/csrf.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log all POST data for debugging
error_log("POST data: " . print_r($_POST, true));

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

include_once "../config/database.php";
include "accomplishment.php";
include_once __DIR__ . '/../helpers/activity_logger.php';

$database = new Database();
$db = $database->getConnection();

$accomplishment = new Accomplishment($db);
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    switch ($action) {
        case 'create':
            // Validate required fields
            if (empty($_POST['sdp_id'])) {
                throw new Exception('SDP is required');
            }
            if (empty($_POST['r_matrix_id'])) {
                throw new Exception('Responsibility Matrix (Office) is required');
            }
            if (empty($_POST['accomplishment'])) {
                throw new Exception('Accomplishment value is required');
            }

            // Log values being set
            error_log("Setting accomplishment values:");
            error_log("sdp_id: " . $_POST['sdp_id']);
            error_log("target_id: " . ($_POST['target_id'] ?? 'NULL'));
            error_log("accomplishment: " . $_POST['accomplishment']);
            error_log("r_matrix_id: " . $_POST['r_matrix_id']);
            error_log("evidence: " . ($_POST['evidence'] ?? ''));
            error_log("description: " . ($_POST['description'] ?? ''));

            $accomplishment->sdp_id = $_POST['sdp_id'];
            $accomplishment->target_id = !empty($_POST['target_id']) ? $_POST['target_id'] : null;
            $accomplishment->accomplishment = $_POST['accomplishment'];
            $accomplishment->r_matrix_id = $_POST['r_matrix_id'];
            $accomplishment->evidence = $_POST['evidence'] ?? '';
            $accomplishment->description = $_POST['description'] ?? '';

            if ($accomplishment->create()) {
                log_activity($db, $_SESSION['user_id'], 'Create', 'Accomplishments', 
                    'Created accomplishment for SDP ID: ' . $accomplishment->sdp_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Accomplishment added successfully'
                ]);
            } else {
                throw new Exception('Failed to create accomplishment in database');
            }
            break;

        case 'update':
            $accomplishment->accomplishment_id = $_POST['accomplishment_id'];
            $accomplishment->sdp_id = $_POST['sdp_id'];
            $accomplishment->target_id = !empty($_POST['target_id']) ? $_POST['target_id'] : null;
            $accomplishment->accomplishment = $_POST['accomplishment'];
            $accomplishment->r_matrix_id = !empty($_POST['r_matrix_id']) ? $_POST['r_matrix_id'] : null;
            $accomplishment->evidence = $_POST['evidence'] ?? '';
            $accomplishment->description = $_POST['description'] ?? '';

            if ($accomplishment->update()) {
                log_activity($db, $_SESSION['user_id'], 'Update', 'Accomplishments', 
                    'Updated accomplishment ID: ' . $accomplishment->accomplishment_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Accomplishment updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update accomplishment');
            }
            break;

        case 'delete':
            $accomplishment->accomplishment_id = $_POST['accomplishment_id'];
            
            if ($accomplishment->delete()) {
                log_activity($db, $_SESSION['user_id'], 'Delete', 'Accomplishments', 
                    'Deleted accomplishment ID: ' . $accomplishment->accomplishment_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Accomplishment deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete accomplishment');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
