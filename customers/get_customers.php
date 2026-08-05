<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "customer.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

function getCustomerTypeBadge($type) {
    switch ($type) {
        case 'Private':
            return '<span class="badge bg-primary">Private</span>';
        case 'Business':
            return '<span class="badge bg-success">Business</span>';
        case 'Government':
            return '<span class="badge bg-warning text-dark">Government</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}




function formatContactInfo($contact, $email) {
    $contact_parts = [];
    if (!empty($contact)) {
        $contact_parts[] = '<i class="fas fa-phone text-muted"></i> ' . htmlspecialchars($contact);
    }
    if (!empty($email)) {
        $contact_parts[] = '<i class="fas fa-envelope text-muted"></i> ' . htmlspecialchars($email);
    }
    return !empty($contact_parts) ? implode('<br>', $contact_parts) : '<span class="text-muted">No contact info</span>';
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Database connection failed');
    }

    $customer = new Customer($db);

    // DataTables parameters
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search_value = $_POST['search']['value'] ?? '';

    // Order parameters
    $order_column_index = $_POST['order'][0]['column'] ?? 5;
    $order_dir = $_POST['order'][0]['dir'] ?? 'desc';

    // Map column index to actual column name
    $columns = ['', 'customer_type', 'business_name', '', ''];
    $order_column = $columns[$order_column_index] ?? 'created_at';

    // Get customers data
    $stmt = $customer->read($start, $length, $search_value, $order_column, $order_dir);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $total_count = $customer->countAll();
    $filtered_count = $customer->countAll($search_value);

    $data = [];
    foreach ($customers as $row) {
        // Customer info as text only
        $display_name = htmlspecialchars($row['name']);
        $business_display = '';
        if (!empty($row['business_name'])) {
            $business_display = '<br><small class="text-muted">' . htmlspecialchars($row['business_name']) . '</small>';
        }
        
        $customer_info = '<div class="customer-details">
                              <div class="customer-name font-weight-bold">' . $display_name . '</div>
                              <div class="customer-meta">ID: #' . $row['id'] . $business_display . '</div>
                          </div>';

        // Type badge
        $type_badge = getCustomerTypeBadge($row['customer_type']);

        // Business name
        $business_name = !empty($row['business_name']) ? htmlspecialchars($row['business_name']) : '<span class="text-muted">N/A</span>';

        // Contact info
        $contact_info = formatContactInfo($row['contact_number'], $row['email']);

        // Address
        $address = !empty($row['address']) ? htmlspecialchars($row['address']) : '<span class="text-muted">No address</span>';

        // Created date
        $created_date = date('M d, Y', strtotime($row['created_at']));
        $created_time = date('h:i A', strtotime($row['created_at']));
        $created_at = $created_date . '<br><small class="text-muted">' . $created_time . '</small>';

        // Actions - Include all data in data-* attributes for instant modal population
        $actions = '<div class="btn-group" role="group">
                     <button class="btn btn-primary btn-sm edit-btn" type="button"
                             data-id="' . $row['id'] . '"
                             data-customer_type="' . htmlspecialchars($row['customer_type']) . '"
                             data-name="' . htmlspecialchars($row['name']) . '"
                             data-business_name="' . htmlspecialchars($row['business_name'] ?? '') . '"
                             data-address="' . htmlspecialchars($row['address'] ?? '') . '"
                             data-contact_number="' . htmlspecialchars($row['contact_number'] ?? '') . '"
                             data-email="' . htmlspecialchars($row['email'] ?? '') . '"
                             data-bs-toggle="modal" data-bs-target="#editCustomerModal"
                             title="Edit Customer">
                         <i class="bi bi-pencil-square"></i>
                     </button>
                     <button class="btn btn-danger btn-sm delete-btn" type="button"
                             data-id="' . $row['id'] . '"
                             data-name="' . htmlspecialchars($row['name']) . '"
                             data-bs-toggle="modal" data-bs-target="#deleteCustomerModal"
                             title="Delete Customer">
                         <i class="bi bi-trash"></i>
                     </button>
                   </div>';

        $data[] = [
            'customer_info' => $customer_info,
            'type_badge' => $type_badge,
            'business_name' => $business_name,
            'contact_info' => $contact_info,
            'actions' => $actions
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
    error_log("Error in get_customers.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'An error occurred while fetching customers',
        'draw' => $draw ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);
}
?>