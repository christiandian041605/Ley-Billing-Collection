<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "charge_invoice.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

function getStatusBadge($status) {
    switch ($status) {
        case 'Paid':
            return '<span class="badge bg-success">Paid</span>';
        case 'Partially Paid':
            return '<span class="badge bg-warning text-dark">Partially Paid</span>';
        case 'Unpaid':
            return '<span class="badge bg-danger">Unpaid</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Database connection failed');
    }

    $chargeInvoice = new ChargeInvoice($db);

    // DataTables parameters
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search_value = $_POST['search']['value'] ?? '';
    $invoice_type = $_POST['invoice_type'] ?? 'government';

    // Get invoices data using existing getAll method
    $result = $chargeInvoice->getAll([
        'draw' => $draw,
        'start' => $start,
        'length' => $length,
        'search' => $search_value,
        'invoice_type' => $invoice_type
    ]);

    $invoices = $result['data'];
    $total_count = $result['recordsTotal'];
    $filtered_count = $result['recordsFiltered'];

    $data = [];
        foreach ($invoices as $row) {
        // Invoice number with government indicator
        $invoice_no = htmlspecialchars($row['invoice_no']);
        if ($row['customer_type'] === 'Government') {
            $invoice_no .= ' <span class="badge bg-info text-white">Gov</span>';
        }

        // Date
        $invoice_date = formatDate($row['invoice_date']);

        // Customer info
        $customer_name = htmlspecialchars($row['customer_name']);
        if (!empty($row['business_name'] ?? '')) {
            $customer_name .= '<br><small class="text-muted">' . htmlspecialchars($row['business_name']) . '</small>';
        }

        // Total amount
        $total_amount = formatCurrency($row['total_amount']);

        // Net amount (use net_amount field if available, otherwise use total_amount)
        $net_amount_value = $row['net_amount'] ?? $row['total_amount'];
        $net_amount = formatCurrency($net_amount_value);
        
        if ($row['customer_type'] === 'Government' && $net_amount_value < $row['total_amount']) {
            $tax_deducted = $row['total_amount'] - $net_amount_value;
            $net_amount .= '<br><small class="text-muted">Tax: ' . formatCurrency($tax_deducted) . '</small>';
        }        // Status badge
        $status_badge = getStatusBadge($row['payment_status']);

        // Actions
        $actions = '<div class="btn-group" role="group">
                     <button class="btn btn-info btn-sm view-btn" type="button"
                             data-id="' . $row['id'] . '"
                             data-bs-toggle="modal" data-bs-target="#viewInvoiceModal"
                             title="View Invoice">
                         <i class="bi bi-eye"></i>
                     </button>
                     <button class="btn btn-primary btn-sm edit-btn" type="button"
                             data-id="' . $row['id'] . '"
                             data-bs-toggle="modal" data-bs-target="#editInvoiceModal"
                             title="Edit Invoice">
                         <i class="bi bi-pencil-square"></i>
                     </button>
                     <button class="btn btn-danger btn-sm delete-btn" type="button"
                             data-id="' . $row['id'] . '" 
                             data-invoice-no="' . htmlspecialchars($row['invoice_no']) . '" 
                             data-bs-toggle="modal" 
                             data-bs-target="#deleteInvoiceModal"
                             title="Delete Invoice">
                         <i class="bi bi-trash"></i>
                     </button>
                   </div>';

        $data[] = [
            $invoice_no,
            $invoice_date,
            $customer_name,
            $total_amount,
            $net_amount,
            $status_badge,
            $actions
        ];
    }

    $response = [
        'draw' => $draw,
        'recordsTotal' => $total_count,
        'recordsFiltered' => $filtered_count,
        'data' => $data
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_charge_invoices.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'An error occurred while fetching invoices',
        'draw' => $draw ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
}
