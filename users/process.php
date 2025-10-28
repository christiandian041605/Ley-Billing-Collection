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
include "user.php";
include_once __DIR__ . '/../helpers/activity_logger.php';



$database = new Database();
$db = $database->getConnection();

if (!$db) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user = new User($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $user->full_name = $_POST['full_name'];
            $user->username = $_POST['username'];
            $user->position = $_POST['position'];
            $user->r_matrix_id = empty($_POST['r_matrix_id']) ? null : $_POST['r_matrix_id'];
            $user->password = $_POST['password'];
            $user->role = $_POST['role'];

            if ($user->create()) {
                $log_message = "Created new user '{$user->full_name}' (ID: {$user->user_id})";
                log_activity($db, (int)$_SESSION['user_id'], 'Created User', 'Users', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User was created successfully', 'user_id' => $user->user_id]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to create user. Username may already be in use.']);
            }
            exit();

        case 'update':
            $user->user_id = $_POST['user_id'];

            // Get user state before update
            $user_before = new User($db);
            $user_before->user_id = $user->user_id;
            $user_before->readOne();

            $user->full_name = $_POST['full_name'];
            $user->username = $_POST['username'];
            $user->position = $_POST['position'];
            $user->r_matrix_id = empty($_POST['r_matrix_id']) ? null : $_POST['r_matrix_id'];
            $user->role = $_POST['role'];
            $user->password = $_POST['password'] ?? ''; // Password might be empty if not changed

            if ($user->update()) {
                $details = [];
                if ($user_before->full_name !== $user->full_name) {
                    $details[] = "full name from '{$user_before->full_name}' to '{$user->full_name}'";
                }
                if ($user_before->username !== $user->username) {
                    $details[] = "username from '{$user_before->username}' to '{$user->username}'";
                }
                if ($user_before->position !== $user->position) {
                    $details[] = "position from '{$user_before->position}' to '{$user->position}'";
                }
                if ($user_before->r_matrix_id != $user->r_matrix_id) {
                    $new_office_name = $user->getOfficeUnitById($user->r_matrix_id);
                    $details[] = "office from '{$user_before->office}' to '{$new_office_name}'";
                }

                // Role comparison
                $role_map = [1 => 'Admin', 2 => 'Staff', 'Admin' => 'Admin', 'Faculty' => 'Faculty', 'Staff' => 'Staff', 'Dean' => 'Dean', 'Director' => 'Director'];
                $new_role_name = $role_map[$_POST['role']] ?? 'Staff';
                if ($user_before->role !== $new_role_name) {
                    $details[] = "role from '{$user_before->role}' to '{$new_role_name}'";
                }

                if (!empty($user->password)) {
                    $details[] = "password updated";
                }

                if (!empty($details)) {
                    $log_message = "Updated user '{$user_before->full_name}': " . implode(', ', $details) . ".";
                } else {
                    $log_message = "Attempted to update user '{$user_before->full_name}', but no values were changed.";
                }
                
                log_activity($db, (int)$_SESSION['user_id'], 'Updated User', 'Users', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User was updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to update user']);
            }
            exit();

        case 'delete':
            $user->user_id = $_POST['user_id'];

            // Get user details before deleting
            $user->readOne();
            $deleted_user_name = $user->full_name;

            if ($user->delete()) {
                $log_message = "Deleted user '{$deleted_user_name}' (ID: {$user->user_id})";
                log_activity($db, (int)$_SESSION['user_id'], 'Deleted User', 'Users', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User was deleted successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to delete user']);
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
