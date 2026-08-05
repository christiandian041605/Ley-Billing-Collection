<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    exit();
}

include_once "../config/database.php";
include "charge_invoice.php";

$database = new Database();
$db = $database->getConnection();

$chargeInvoice = new ChargeInvoice($db);

$response = ['duplicate' => false];

if (isset($_POST['invoice_no'])) {
    $invoice_no = $_POST['invoice_no'];
    $exclude_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : null;
    
    $response['duplicate'] = $chargeInvoice->checkDuplicate($invoice_no, $exclude_id);
}

header('Content-Type: application/json');
echo json_encode($response);
