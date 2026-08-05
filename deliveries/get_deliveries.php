<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "delivery.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

function getDeliveryTypeBadge($type)
{
    switch ($type) {
        case 'DR V':
            return '<span class="badge bg-primary">DR V</span>';
        case 'DR Government':
            return '<span class="badge bg-success">DR Government</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars($type) . '</span>';
    }
}

function getPaymentStatusBadge($status)
{
    switch ($status) {
        case 'Paid':
            return '<span class="badge bg-success">Paid</span>';
        case 'Partially Paid':
            return '<span class="badge bg-warning text-dark">Partially Paid</span>';
        case 'Unpaid':
            return '<span class="badge bg-danger">Unpaid</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
    }
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $delivery = new Delivery($db);

    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $delivery_type = isset($_POST['delivery_type']) ? $_POST['delivery_type'] : 'all';

    $order_column_index = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 1;
    $order_dir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';

    $columns = ['delivery_no', 'delivery_date', 'customer_name', 'logistics', 'delivery_type', 'total_amount', 'payment_status', 'invoice_numbers', 'actions'];
    $order_column = isset($columns[$order_column_index]) ? $columns[$order_column_index] : 'delivery_date';

    $stmt = $delivery->read($start, $length, $search, $order_column, $order_dir, $delivery_type);
    $deliveries_arr = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $actions = '<div class="btn-group" role="group">
                    <button class="btn btn-info btn-sm view-btn" type="button"
                            data-id="' . $row['id'] . '" 
                            data-bs-toggle="modal" data-bs-target="#viewDeliveryModal"
                            title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-primary btn-sm edit-btn" type="button"
                            data-id="' . $row['id'] . '" 
                            data-bs-toggle="modal" data-bs-target="#editDeliveryModal"
                            title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-danger btn-sm delete-btn" type="button"
                            data-id="' . $row['id'] . '" 
                            data-delivery-no="' . htmlspecialchars($row['delivery_no']) . '" 
                            data-bs-toggle="modal" data-bs-target="#deleteDeliveryModal"
                            title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>';

        $logistics = '<small>';
        if (!empty($row['checker']))
            $logistics .= '<strong>Checker:</strong> ' . htmlspecialchars($row['checker']) . '<br>';
        if (!empty($row['driver']))
            $logistics .= '<strong>Driver:</strong> ' . htmlspecialchars($row['driver']) . '<br>';
        if (!empty($row['plate_number']))
            $logistics .= '<strong>Plate:</strong> ' . htmlspecialchars($row['plate_number']);
        $logistics .= '</small>';

        $delivery_item = [
            'delivery_no' => htmlspecialchars($row['delivery_no']),
            'delivery_date' => date('M d, Y', strtotime($row['delivery_date'])),
            'customer_name' => htmlspecialchars($row['customer_name'] ?? 'N/A'),
            'logistics' => $logistics,
            'delivery_type' => getDeliveryTypeBadge($row['delivery_type']),
            'total_amount' => '₱' . number_format($row['total_amount'], 2),
            'payment_status' => getPaymentStatusBadge($row['payment_status']),
            'invoice_numbers' => htmlspecialchars($row['invoice_numbers'] ?? 'None'),
            'actions' => $actions
        ];

        $deliveries_arr[] = $delivery_item;
    }

    $total_records = $delivery->countAll('', $delivery_type);
    $filtered_records = $delivery->countAll($search, $delivery_type);

    $response = [
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $filtered_records,
        'data' => $deliveries_arr
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Get deliveries error: " . $e->getMessage());
    echo json_encode([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Database error occurred'
    ]);
}
?>