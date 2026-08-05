<?php
include_once __DIR__ . '/../activity_log/activity_log.php';

function log_activity($db, $user_id, $action, $module, $details) {
    // Check if user_id exists in tbl_users (updated to match System-Function.txt)
    $query = "SELECT id FROM tbl_users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $activity_log = new ActivityLog($db);
        $activity_log->user_id = $user_id;
        $activity_log->action = $action;
        $activity_log->module = $module;
        $activity_log->details = $details;
        $activity_log->create();
    }
}
