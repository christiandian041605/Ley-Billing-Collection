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
include "app_setting.php";
include_once __DIR__ . '/../helpers/activity_logger.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$app_setting = new AppSetting($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update':
            // Get settings state before update
            $app_setting_before = new AppSetting($db);
            $app_setting_before->setting_id = $_POST['setting_id'];
            $app_setting_before->readOne();

            $app_setting->setting_id = $_POST['setting_id'];
            $app_setting->app_name = $_POST['app_name'];
            $app_setting->address = $_POST['address'];
            $app_setting->contact_number = $_POST['contact_number'];
            $app_setting->email = $_POST['email'];
            $app_setting->about = $_POST['about'];

            $new_logo_filename = null;

            // Handle file upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $target_dir = "../dist/img/";
                $original_filename = $_FILES["logo"]["name"];
                $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
                $new_filename = uniqid('logo_', true) . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;

                // Check if file is an image
                $check = getimagesize($_FILES["logo"]["tmp_name"]);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                        $app_setting->logo = $new_filename;
                        $new_logo_filename = $new_filename;
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Sorry, there was an error uploading your file']);
                        exit();
                    }
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'File is not an image']);
                    exit();
                }
            }

            if ($app_setting->update()) {
                $details = [];
                if ($app_setting_before->app_name !== $app_setting->app_name) {
                    $details[] = "app name from '{$app_setting_before->app_name}' to '{$app_setting->app_name}'";
                }
                if ($app_setting_before->address !== $app_setting->address) {
                    $details[] = "address updated";
                }
                if ($app_setting_before->contact_number !== $app_setting->contact_number) {
                    $details[] = "contact number from '{$app_setting_before->contact_number}' to '{$app_setting->contact_number}'";
                }
                if ($app_setting_before->email !== $app_setting->email) {
                    $details[] = "email from '{$app_setting_before->email}' to '{$app_setting->email}'";
                }
                if ($app_setting_before->about !== $app_setting->about) {
                    $details[] = "about section updated";
                }
                if ($new_logo_filename) {
                    $details[] = "logo updated to '{$new_logo_filename}'";
                }

                if (!empty($details)) {
                    $log_message = "Updated app settings: " . implode(', ', $details) . ".";
                } else {
                    $log_message = "Attempted to update app settings, but no values were changed.";
                }

                log_activity($db, (int)$_SESSION['user_id'], 'Updated App Setting', 'App Settings', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'App Setting was updated successfully', 'logo' => $app_setting->logo ?? $app_setting_before->logo]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to update app setting']);
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
?>