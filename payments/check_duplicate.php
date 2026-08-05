<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

include_once "../config/database.php";
include "payment.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$payment = new Payment($db);

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';
$payment_id = $_POST['payment_id'] ?? null;

$allowed_fields = ['or_no', 'cheque_no'];
if (!in_array($field, $allowed_fields)) {
    echo json_encode(["duplicate" => false]);
    exit();
}

$isDuplicate = $payment->isDuplicate($field, $value, $payment_id);

echo json_encode(["duplicate" => $isDuplicate]);
