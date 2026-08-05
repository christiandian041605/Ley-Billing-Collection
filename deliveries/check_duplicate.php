<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "delivery.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $delivery = new Delivery($db);

    if (isset($_POST['delivery_no'])) {
        $delivery->delivery_no = $_POST['delivery_no'];
        $delivery_id = isset($_POST['delivery_id']) ? $_POST['delivery_id'] : null;

        $exists = $delivery->deliveryNoExists($delivery_id);

        echo json_encode(['exists' => $exists]);
    } else {
        echo json_encode(['error' => 'Missing parameters']);
    }

} catch (Exception $e) {
    error_log("Check duplicate error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error']);
}
?>
