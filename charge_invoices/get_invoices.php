<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "charge_invoice.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    $invoice = new ChargeInvoice($db);
    
    $stmt = $invoice->read();
    $invoices = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $displayName = $row['display_name'] ?? 'N/A';
        
        // Payment status badge
        $statusBadge = '';
        switch($row['payment_status']) {
            case 'Unpaid':
                $statusBadge = '<span class="badge bg-danger">Unpaid</span>';
                break;
            case 'Partially Paid':
                $statusBadge = '<span class="badge bg-warning">Partially Paid</span>';
                break;
            case 'Paid':
                $statusBadge = '<span class="badge bg-success">Paid</span>';
                break;
        }
        
        $actions = '<div class="btn-group" role="group" aria-label="Invoice Actions">
            <a class="btn btn-info btn-sm view-btn" href="#" 
                data-id="' . $row['id'] . '"
                data-bs-toggle="modal" data-bs-target="#viewInvoiceModal" title="View Invoice">
                <i class="bi bi-eye"></i> View
            </a>
            <a class="btn btn-primary btn-sm edit-btn" href="#" 
                data-id="' . $row['id'] . '" 
                data-invoice-no="' . htmlspecialchars($row['invoice_no']) . '" 
                data-invoice-date="' . htmlspecialchars($row['invoice_date']) . '"
                data-customer-id="' . htmlspecialchars($row['customer_id']) . '"
                data-address="' . htmlspecialchars($row['address'] ?? '') . '"
                data-bs-toggle="modal" data-bs-target="#editInvoiceModal" title="Edit Invoice">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
            <a class="btn btn-danger btn-sm delete-btn" href="#" 
                data-id="' . $row['id'] . '" 
                data-invoice-no="' . htmlspecialchars($row['invoice_no']) . '" 
                data-bs-toggle="modal" data-bs-target="#deleteInvoiceModal" title="Delete Invoice">
                <i class="bi bi-trash"></i> Delete
            </a>
        </div>';
        
        $invoices[] = [
            htmlspecialchars($row['invoice_no']),
            date('M d, Y', strtotime($row['invoice_date'])),
            htmlspecialchars($displayName),
            '₱' . number_format($row['total_amount'], 2),
            $statusBadge,
            $actions
        ];
    }
    
    echo json_encode(['data' => $invoices]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
