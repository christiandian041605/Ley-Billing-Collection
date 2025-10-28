<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../config/database.php";
include "../config/app.php";
include "responsibility_matrix.php";
include_once __DIR__ . '/../helpers/activity_logger.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$responsibility_matrix = new ResponsibilityMatrix($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $responsibility_matrix->office_unit = $_POST['office_unit'];

            if ($responsibility_matrix->create()) {
                $log_message = "Created new office/unit '{$responsibility_matrix->office_unit}' (ID: {$responsibility_matrix->r_matrix_id})";
                log_activity($db, (int)$_SESSION['user_id'], 'Created Office/Unit', 'Responsibility Matrix', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Office/Unit was created successfully', 'r_matrix_id' => $responsibility_matrix->r_matrix_id]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to create Office/Unit. It may already exist.']);
            }
            exit();

        case 'update':
            $responsibility_matrix->r_matrix_id = $_POST['r_matrix_id'];

            $rm_before = new ResponsibilityMatrix($db);
            $rm_before->r_matrix_id = $responsibility_matrix->r_matrix_id;
            $rm_before->readOne();

            $responsibility_matrix->office_unit = $_POST['office_unit'];

            if ($responsibility_matrix->update()) {
                $details = [];
                if ($rm_before->office_unit !== $responsibility_matrix->office_unit) {
                    $details[] = "office/unit from '{$rm_before->office_unit}' to '{$responsibility_matrix->office_unit}'";
                }

                if (!empty($details)) {
                    $log_message = "Updated office/unit '{$rm_before->office_unit}': " . implode(', ', $details) . ".";
                } else {
                    $log_message = "Attempted to update office/unit '{$rm_before->office_unit}', but no values were changed.";
                }
                
                log_activity($db, (int)$_SESSION['user_id'], 'Updated Office/Unit', 'Responsibility Matrix', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Office/Unit was updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to update Office/Unit']);
            }
            exit();

        case 'delete':
            $responsibility_matrix->r_matrix_id = $_POST['r_matrix_id'];

            $responsibility_matrix->readOne();
            $deleted_rm_name = $responsibility_matrix->office_unit;

            if ($responsibility_matrix->delete()) {
                $log_message = "Deleted office/unit '{$deleted_rm_name}' (ID: {$responsibility_matrix->r_matrix_id})";
                log_activity($db, (int)$_SESSION['user_id'], 'Deleted Office/Unit', 'Responsibility Matrix', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Office/Unit was deleted successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to delete Office/Unit']);
            }
            exit();

        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}
