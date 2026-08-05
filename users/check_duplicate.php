<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";
include "user.php";

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';
$userId = $_POST['user_id'] ?? null;

$valid_fields = ['username'];
if (!in_array($field, $valid_fields)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid field']);
    exit();
}

$is_duplicate = $user->isDuplicate($field, $value, $userId);

header('Content-Type: application/json');
echo json_encode(['duplicate' => $is_duplicate]);
