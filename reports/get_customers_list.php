<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Database connection failed');
    }

    $query = "SELECT id, name, business_name, customer_type 
              FROM tbl_customers 
              ORDER BY name ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $customers = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $customers[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'business_name' => $row['business_name'],
            'customer_type' => $row['customer_type']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $customers
    ]);

} catch (Exception $e) {
    error_log("Error in get_customers_list.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching customers'
    ]);
}
?>
