<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';

if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";
include "charge_invoice.php";

$database = new Database();
$db = $database->getConnection();
$chargeInvoice = new ChargeInvoice($db);

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_invoice':
        if (!isset($_POST['id'])) {
            echo json_encode(['error' => 'Invoice ID is required']);
            exit();
        }
        
        $invoice = $chargeInvoice->getById($_POST['id']);
        if ($invoice) {
            echo json_encode($invoice);
        } else {
            echo json_encode(['error' => 'Invoice not found']);
        }
        break;
        
    case 'generate_invoice_no':
        $invoiceNo = $chargeInvoice->generateInvoiceNo();
        echo json_encode(['invoice_no' => $invoiceNo]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action', 'received_action' => $action]);
        break;
}