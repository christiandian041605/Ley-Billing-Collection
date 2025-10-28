<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";
include "sdp.php";

$database = new Database();
$db = $database->getConnection();
$sdp = new Sdp($db);

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';
$sdpId = $_POST['sdp_id'] ?? null;

$valid_fields = ['kpi'];
if (!in_array($field, $valid_fields)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid field']);
    exit();
}

$is_duplicate = $sdp->isDuplicate($field, $value, $sdpId);

header('Content-Type: application/json');
echo json_encode(['duplicate' => $is_duplicate]);
