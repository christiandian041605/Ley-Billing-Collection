<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";
include "responsibility_matrix.php";

$database = new Database();
$db = $database->getConnection();
$responsibility_matrix = new ResponsibilityMatrix($db);

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';
$r_matrix_id = $_POST['r_matrix_id'] ?? null;

$valid_fields = ['office_unit'];
if (!in_array($field, $valid_fields)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid field']);
    exit();
}

$is_duplicate = $responsibility_matrix->isDuplicate($field, $value, $r_matrix_id);

header('Content-Type: application/json');
echo json_encode(['duplicate' => $is_duplicate]);
