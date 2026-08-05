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

$customer_id = $_GET['customer_id'] ?? null;
$payment_id = $_GET['payment_id'] ?? null;

if (!$customer_id) {
    echo json_encode(["success" => false, "message" => "Customer ID is required"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$payment = new Payment($db);
$invoices = $payment->getUnpaidInvoices($customer_id, $payment_id);

echo json_encode([
    "success" => true,
    "invoices" => $invoices
]);
