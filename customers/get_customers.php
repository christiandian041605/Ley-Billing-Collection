<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "customer.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $customer = new Customer($db);
    
    $stmt = $customer->read();
    $customers = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $customerType = $row['customer_type'] ?? 'Private';
        $name = $row['name'] ?? '';
        $businessName = $row['business_name'] ?? '';
        $displayName = !empty($businessName) ? $businessName : $name;
        
        // Customer type badge
        $typeBadge = '';
        switch($customerType) {
            case 'Private':
                $typeBadge = '<span class="badge bg-primary">Private</span>';
                break;
            case 'Business':
                $typeBadge = '<span class="badge bg-success">Business</span>';
                break;
            case 'Government':
                $typeBadge = '<span class="badge bg-info">Government</span>';
                break;
        }
        
        $actions = '<div class="btn-group" role="group" aria-label="Customer Actions">
            <a class="btn btn-primary btn-sm edit-btn" href="#"
                data-id="' . $row['id'] . '"
                data-customertype="' . htmlspecialchars($customerType) . '"
                data-name="' . htmlspecialchars($name) . '"
                data-businessname="' . htmlspecialchars($businessName) . '"
                data-address="' . htmlspecialchars($row['address'] ?? '') . '"
                data-contactnumber="' . htmlspecialchars($row['contact_number'] ?? '') . '"
                data-email="' . htmlspecialchars($row['email'] ?? '') . '"
                data-bs-toggle="modal" data-bs-target="#editCustomerModal" title="Edit Customer">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
            <a class="btn btn-danger btn-sm delete-btn" href="#"
                data-id="' . $row['id'] . '"
                data-name="' . htmlspecialchars($displayName) . '"
                data-bs-toggle="modal" data-bs-target="#deleteCustomerModal" title="Delete Customer">
                <i class="bi bi-trash"></i> Delete
            </a>
        </div>';
        
        $customers[] = [
            htmlspecialchars($displayName),
            $typeBadge,
            htmlspecialchars($row['address'] ?: '-'),
            htmlspecialchars($row['contact_number'] ?: '-'),
            htmlspecialchars($row['email'] ?: '-'),
            $actions
        ];
    }
    
    echo json_encode(['data' => $customers]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
