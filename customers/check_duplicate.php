<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";
include "customer.php";

$database = new Database();
$db = $database->getConnection();
$customer = new Customer($db);

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';
$customerId = $_POST['customer_id'] ?? null;

$valid_fields = ['name'];
if (!in_array($field, $valid_fields)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid field']);
    exit();
}

// Check if name exists
if ($field === 'name') {
    $customer->name = $value;
    $is_duplicate = $customer->nameExists($customerId);
} else {
    $is_duplicate = false;
}

header('Content-Type: application/json');
echo json_encode(['duplicate' => $is_duplicate]);
?>