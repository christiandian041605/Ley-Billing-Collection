<?php
session_start();
require_once '../config/session.php';
require_once 'bank.php';

// Check authentication
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    echo json_encode(['available' => false]);
    exit;
}

header('Content-Type: application/json');

$bank = new Bank();
$bank_name = $_GET['bank_name'] ?? '';
$branch_name = $_GET['branch_name'] ?? '';
$exclude_id = $_GET['exclude_id'] ?? null;

if (empty($bank_name) || empty($branch_name)) {
    echo json_encode(['available' => true]); // Allow if empty
    exit;
}

// Check if duplicate exists
$isDuplicate = $bank->isDuplicate($bank_name, $branch_name, $exclude_id);

echo json_encode(['available' => !$isDuplicate]);
?>
