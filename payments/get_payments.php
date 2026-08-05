<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

include_once "../config/database.php";
include "payment.php";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode([
        "draw" => 1,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Database connection failed"
    ]);
    exit();
}

try {
    $payment = new Payment($db);

    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $search_value = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : 'all';

    $order_column_index = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
    $order_dir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'DESC';

    $columns = ['ofrec.id', 'ofrec.or_no', 'ofrec.or_date', 'c.name', 'ofrec.amount_received', 'ofrec.payment_type', 'ofrec.created_at'];
    $order_column = isset($columns[$order_column_index]) ? $columns[$order_column_index] : 'ofrec.created_at';

    $stmt = $payment->read($start, $length, $search_value, $order_column, $order_dir, $category);
    $total_records = $payment->countAll('', $category);
    $filtered_records = $payment->countAll($search_value, $category);
} catch (Exception $e) {
    echo json_encode([
        "draw" => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Error: " . $e->getMessage()
    ]);
    exit();
}

$data = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $payment_type_badge = '';
    switch($row['payment_type']) {
        case 'Cash':
            $payment_type_badge = '<span class="badge bg-success">Cash</span>';
            break;
        case 'Check':
            $payment_type_badge = '<span class="badge bg-info">Check</span>';
            break;
        case 'Bank Transfer':
            $payment_type_badge = '<span class="badge bg-primary">Bank Transfer</span>';
            break;
        case 'GCash':
            $payment_type_badge = '<span class="badge bg-warning">GCash</span>';
            break;
        default:
            $payment_type_badge = '<span class="badge bg-secondary">' . htmlspecialchars($row['payment_type']) . '</span>';
    }

    $customer_type_badge = '';
    if ($row['customer_type'] === 'Government') {
        $customer_type_badge = ' <span class="badge bg-danger">Gov</span>';
    }

    $payment_details = '';
    if (!empty($row['cheque_no'])) {
        $payment_details = '<br><small class="text-muted">Check #: ' . htmlspecialchars($row['cheque_no']) . '</small>';
    }
    if (!empty($row['bank_name'])) {
        $payment_details .= '<br><small class="text-muted">Bank: ' . htmlspecialchars($row['bank_name']) . '</small>';
    }

    $actions = '<div class="btn-group" role="group">
        <button class="btn btn-info btn-sm view-btn" type="button"
                data-id="' . $row['id'] . '"
                data-bs-toggle="modal" 
                data-bs-target="#viewPaymentModal"
                title="View">
            <i class="bi bi-eye"></i>
        </button>
        <button class="btn btn-primary btn-sm edit-btn" type="button"
                data-id="' . $row['id'] . '"
                data-or_no="' . htmlspecialchars($row['or_no']) . '"
                data-or_date="' . $row['or_date'] . '"
                data-customer_id="' . $row['customer_id'] . '"
                data-amount_received="' . $row['amount_received'] . '"
                data-payment_type="' . htmlspecialchars($row['payment_type']) . '"
                data-bank_id="' . $row['bank_id'] . '"
                data-cheque_no="' . htmlspecialchars($row['cheque_no']) . '"
                data-cheque_date="' . $row['cheque_date'] . '"
                data-cheque_name="' . htmlspecialchars($row['cheque_name']) . '"
                data-check_amount="' . $row['check_amount'] . '"
                data-notes="' . htmlspecialchars($row['notes']) . '"
                data-bs-toggle="modal" 
                data-bs-target="#editPaymentModal"
                title="Edit">
            <i class="bi bi-pencil-square"></i>
        </button>
        <button class="btn btn-danger btn-sm delete-btn" type="button"
                data-id="' . $row['id'] . '"
                data-or_no="' . htmlspecialchars($row['or_no']) . '"
                data-bs-toggle="modal" 
                data-bs-target="#deletePaymentModal"
                title="Delete">
            <i class="bi bi-trash"></i>
        </button>
    </div>';

    $data[] = array(
        htmlspecialchars($row['or_no']),
        date('M d, Y', strtotime($row['or_date'])),
        htmlspecialchars($row['customer_name']) . $customer_type_badge,
        '₱ ' . number_format($row['amount_received'], 2),
        $payment_type_badge . $payment_details,
        $actions
    );
}

$response = array(
    "draw" => $draw,
    "recordsTotal" => intval($total_records),
    "recordsFiltered" => intval($filtered_records),
    "data" => $data
);

echo json_encode($response);
