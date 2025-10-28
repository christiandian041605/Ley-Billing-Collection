<?php
// Define the base URL of your application
// This should be the path from your web server's document root to your project directory.
// For example, if your project is at http://localhost/SUNN-SDP-Tracker/,
// then $base_url should be '/SUNN-SDP-Tracker'.
$base_url = '/SUNN-SDP-Tracker';

include_once __DIR__ . '/database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    $query = "SELECT app_name, logo FROM tbl_app_setting LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $app_name = $row ? $row['app_name'] : 'SUNN SDP TRACKER';
    $logo = $row ? $row['logo'] : 'default.png';
} else {
    $app_name = 'SUNN SDP TRACKER';
    $logo = 'default.png';
}

