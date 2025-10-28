<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";
include "objective.php";

$database = new Database();
$db = $database->getConnection();
$objective = new Objective($db);

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';
$objectives_id = $_POST['objectives_id'] ?? null;

$valid_fields = ['objectives_details'];
if (!in_array($field, $valid_fields)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid field']);
    exit();
}

$is_duplicate = $objective->isDuplicate($field, $value, $objectives_id);

header('Content-Type: application/json');
echo json_encode(['duplicate' => $is_duplicate]);
