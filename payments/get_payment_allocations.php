<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit();
}

include_once "../config/database.php";
include_once "payment.php";

$database = new Database();
$db = $database->getConnection();

if (!$db || !isset($_GET['payment_id'])) {
    echo json_encode([]);
    exit();
}

try {
    $payment = new Payment($db);
    $payment_id = intval($_GET['payment_id']);
    
    // Use the class method that handles both Government and Private logic
    $allocations = $payment->getPaymentAllocations($payment_id);
    
    echo json_encode($allocations);
} catch (Exception $e) {
    error_log("Error in get_payment_allocations.php: " . $e->getMessage());
    echo json_encode([]);
}