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
include "sdp.php";
include_once __DIR__ . '/../helpers/activity_logger.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$sdp = new Sdp($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $sdp->objectives_id = empty($_POST['objectives_id']) ? null : $_POST['objectives_id'];
            $sdp->kpi = $_POST['kpi'];
            $sdp->initiatives = $_POST['initiatives'];
            $responsibilities = $_POST['responsibilities'] ?? [];
            $targets = $_POST['targets'] ?? [];

            if ($sdp->create()) {
                // Add responsibilities if any
                foreach ($responsibilities as $r_matrix_id) {
                    $sdp->addResponsibility($r_matrix_id);
                }

                // Add targets if any
                foreach ($targets as $target) {
                    $tv = isset($target['target_value']) ? trim((string)$target['target_value']) : '';
                    $desc = isset($target['description']) ? trim((string)$target['description']) : '';
                    $vtype = $target['value_type'] ?? 'Other';
                    $qtr = isset($target['quarter']) ? trim((string)$target['quarter']) : '';
                    $yr = isset($target['year']) ? trim((string)$target['year']) : '';

                    // Skip completely empty target rows
                    if ($tv === '' && $desc === '' && $qtr === '' && $yr === '' && ($vtype === '' || $vtype === 'Other')) {
                        continue;
                    }

                    // Normalize target_value to NULL when empty
                    $tvParam = ($tv === '' ? null : $tv);
                    // Default year if other fields are provided but year empty
                    $yrParam = ($yr === '' ? date('Y') : $yr);

                    $sdp->addTarget(
                        $tvParam,
                        $desc === '' ? null : $desc,
                        $vtype ?: 'Other',
                        $qtr === '' ? null : $qtr,
                        $yrParam
                    );
                }

                $objective_name = $sdp->getObjectiveById($sdp->objectives_id);
                $log_message = "Created new SDP '{$sdp->kpi}' (ID: {$sdp->sdp_id})";
                if ($objective_name) {
                    $log_message .= " for objective '{$objective_name}'";
                }
                log_activity($db, (int)$_SESSION['user_id'], 'Created SDP', 'SDP', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'SDP was created successfully', 'sdp_id' => $sdp->sdp_id]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to create SDP. KPI may already be in use.']);
            }
            exit();

        case 'update':
            $sdp->sdp_id = $_POST['sdp_id'];

            // Get SDP state before update
            $sdp_before = new Sdp($db);
            $sdp_before->sdp_id = $sdp->sdp_id;
            $sdp_before->readOne();

            $sdp->objectives_id = empty($_POST['objectives_id']) ? null : $_POST['objectives_id'];
            $sdp->kpi = $_POST['kpi'];
            $sdp->initiatives = $_POST['initiatives'];
            $responsibilities = $_POST['responsibilities'] ?? [];
            $targets = $_POST['targets'] ?? [];

            if ($sdp->update()) {
                // Update responsibilities
                $sdp->removeAllResponsibilities();
                foreach ($responsibilities as $r_matrix_id) {
                    $sdp->addResponsibility($r_matrix_id);
                }

                // Update targets
                $sdp->removeAllTargets();
                foreach ($targets as $target) {
                    $tv = isset($target['target_value']) ? trim((string)$target['target_value']) : '';
                    $desc = isset($target['description']) ? trim((string)$target['description']) : '';
                    $vtype = $target['value_type'] ?? 'Other';
                    $qtr = isset($target['quarter']) ? trim((string)$target['quarter']) : '';
                    $yr = isset($target['year']) ? trim((string)$target['year']) : '';

                    // Skip completely empty target rows
                    if ($tv === '' && $desc === '' && $qtr === '' && $yr === '' && ($vtype === '' || $vtype === 'Other')) {
                        continue;
                    }

                    // Normalize target_value to NULL when empty
                    $tvParam = ($tv === '' ? null : $tv);
                    // Default year if other fields are provided but year empty
                    $yrParam = ($yr === '' ? date('Y') : $yr);

                    $sdp->addTarget(
                        $tvParam,
                        $desc === '' ? null : $desc,
                        $vtype ?: 'Other',
                        $qtr === '' ? null : $qtr,
                        $yrParam
                    );
                }

                $details = [];
                if ($sdp_before->kpi !== $sdp->kpi) {
                    $details[] = "KPI from '{$sdp_before->kpi}' to '{$sdp->kpi}'";
                }
                if ($sdp_before->initiatives !== $sdp->initiatives) {
                    $details[] = "initiatives updated";
                }
                if ($sdp_before->objectives_id != $sdp->objectives_id) {
                    $new_objective_name = $sdp->getObjectiveById($sdp->objectives_id);
                    $details[] = "objective from '{$sdp_before->objectives_details}' to '{$new_objective_name}'";
                }

                if (!empty($details)) {
                    $log_message = "Updated SDP '{$sdp_before->kpi}': " . implode(', ', $details) . ".";
                } else {
                    $log_message = "Attempted to update SDP '{$sdp_before->kpi}', but no values were changed.";
                }
                
                log_activity($db, (int)$_SESSION['user_id'], 'Updated SDP', 'SDP', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'SDP was updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to update SDP']);
            }
            exit();

        case 'delete':
            $sdp->sdp_id = $_POST['sdp_id'];

            // Get SDP details before deleting
            $sdp->readOne();
            $deleted_sdp_kpi = $sdp->kpi;

            if ($sdp->delete()) {
                $log_message = "Deleted SDP '{$deleted_sdp_kpi}' (ID: {$sdp->sdp_id})";
                log_activity($db, (int)$_SESSION['user_id'], 'Deleted SDP', 'SDP', $log_message);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'SDP was deleted successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unable to delete SDP']);
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
