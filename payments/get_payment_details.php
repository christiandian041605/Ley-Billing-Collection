<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

include_once "../config/database.php";
include "payment.php";

header('Content-Type: application/json');

$payment_id = $_GET['payment_id'] ?? null;

if (!$payment_id) {
    echo json_encode(["success" => false, "message" => "Payment ID is required"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$payment = new Payment($db);
$payment->id = $payment_id;

$payment_data = $payment->readOne();

if (!$payment_data) {
    echo json_encode(["success" => false, "message" => "Payment not found"]);
    exit();
}

$allocations = $payment->getPaymentAllocations($payment_id);

echo json_encode([
    "success" => true,
    "data" => $payment_data,
    "allocations" => $allocations
]);
