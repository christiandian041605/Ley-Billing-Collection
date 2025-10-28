<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(0);

include_once "../config/database.php";
include "../config/app.php";
include "objective.php";
include_once __DIR__ . '/../helpers/activity_logger.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$objective = new Objective($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $objective->objectives_details = $_POST['objectives_details'];
            $objective->focus_area = $_POST['focus_area'];

            if ($objective->create()) {
                $log_message = "Created new objective '{$objective->objectives_details}' (ID: {$objective->objectives_id})";
                log_activity($db, (int)$_SESSION['user_id'], 'Created Objective', 'Objectives', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Objective was created successfully', 'objectives_id' => $objective->objectives_id]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to create objective']);
            }
            exit();

        case 'update':
            $objective->objectives_id = $_POST['objectives_id'];

            $objective_before = new Objective($db);
            $objective_before->objectives_id = $objective->objectives_id;
            $objective_before->readOne();

            $objective->objectives_details = $_POST['objectives_details'];
            $objective->focus_area = $_POST['focus_area'];

            if ($objective->update()) {
                $details = [];
                if ($objective_before->objectives_details !== $objective->objectives_details) {
                    $details[] = "details from '{$objective_before->objectives_details}' to '{$objective->objectives_details}'";
                }
                if ($objective_before->focus_area !== $objective->focus_area) {
                    $details[] = "focus area from '{$objective_before->focus_area}' to '{$objective->focus_area}'";
                }

                if (!empty($details)) {
                    $log_message = "Updated objective '{$objective_before->objectives_details}': " . implode(', ', $details) . ".";
                } else {
                    $log_message = "Attempted to update objective '{$objective_before->objectives_details}', but no values were changed.";
                }
                
                log_activity($db, (int)$_SESSION['user_id'], 'Updated Objective', 'Objectives', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Objective was updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to update objective']);
            }
            exit();

        case 'delete':
            $objective->objectives_id = $_POST['objectives_id'];

            $objective->readOne();
            $deleted_objective_name = $objective->objectives_details;

            if ($objective->delete()) {
                $log_message = "Deleted objective '{$deleted_objective_name}' (ID: {$objective->objectives_id})";
                log_activity($db, (int)$_SESSION['user_id'], 'Deleted Objective', 'Objectives', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Objective was deleted successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to delete objective']);
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
