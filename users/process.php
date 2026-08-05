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
            // Get individual name fields directly from form
            $user->first_name = $_POST['first_name'] ?? '';
            $user->middle_name = $_POST['middle_name'] ?? '';
            $user->last_name = $_POST['last_name'] ?? '';
            $user->position = $_POST['position'] ?? '';
            $user->username = $_POST['username'];
            $user->password = $_POST['password'];
            $user->role = $_POST['role'];
            
            // Create full name for display and logging
            $full_name = trim(implode(' ', array_filter([$user->first_name, $user->middle_name, $user->last_name])));
            
            // Set backwards compatibility properties
            $user->fullname = $full_name;
            $user->accounttype = $_POST['role'];

            // Check for duplicate username before creating
            if ($user->isDuplicate('username', $user->username)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Username already exists. Please choose a different username.']);
                exit();
            }

            if ($user->create()) {
                $log_message = "Created new user '{$full_name}' (ID: {$user->id})";
                log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Created User', 'Users', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User was created successfully', 'user_id' => $user->id]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to create user. Please check all required fields and try again.']);
            }
            exit();

        case 'update':
            $user->id = $_POST['user_id'];
            $user->user_id = $_POST['user_id']; // For backwards compatibility

            // Get user state before update
            $user_before = new User($db);
            $user_before->id = $user->id;
            $user_before->user_id = $user->id;
            $user_before->readOne();

            // Get individual name fields directly from form
            $user->first_name = $_POST['first_name'] ?? '';
            $user->middle_name = $_POST['middle_name'] ?? '';
            $user->last_name = $_POST['last_name'] ?? '';
            $user->position = $_POST['position'] ?? '';
            $user->username = $_POST['username'];
            $user->role = $_POST['role'];
            
            // Create full name for display and logging
            $full_name = trim(implode(' ', array_filter([$user->first_name, $user->middle_name, $user->last_name])));
            
            // Set backwards compatibility properties
            $user->fullname = $full_name;
            $user->accounttype = $_POST['role'];
            $user->password = $_POST['password'] ?? ''; // Password might be empty if not changed

            if ($user->update()) {
                $details = [];
                if ($user_before->fullname !== $user->fullname) {
                    $details[] = "full name from '{$user_before->fullname}' to '{$user->fullname}'";
                }
                if ($user_before->username !== $user->username) {
                    $details[] = "username from '{$user_before->username}' to '{$user->username}'";
                }
                if ($user_before->position !== $user->position) {
                    $details[] = "position from '{$user_before->position}' to '{$user->position}'";
                }
                if ($user_before->role !== $user->role) {
                    $details[] = "role from '{$user_before->role}' to '{$user->role}'";
                }

                if (!empty($user->password)) {
                    $details[] = "password updated";
                }

                if (!empty($details)) {
                    $log_message = "Updated user '{$user_before->fullname}': " . implode(', ', $details) . ".";
                } else {
                    $log_message = "Attempted to update user '{$user_before->fullname}', but no values were changed.";
                }
                
                log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Updated User', 'Users', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User was updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to update user']);
            }
            exit();

        case 'delete':
            $user->id = $_POST['user_id'];
            $user->user_id = $_POST['user_id']; // For backwards compatibility

            // Get user details before deleting
            $user->readOne();
            $deleted_user_name = $user->fullname;

            if ($user->delete()) {
                $log_message = "Deleted user '{$deleted_user_name}' (ID: {$user->id})";
                log_activity($db, (int)$_SESSION['ley_billing_user_id'], 'Deleted User', 'Users', $log_message);
                
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
