<?php
include_once __DIR__ . '/../config/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$sdp_id = isset($_POST['sdp_id']) ? intval($_POST['sdp_id']) : 0;

if ($sdp_id > 0) {
    $query = "SELECT t.target_id, t.target_value, t.quarter, t.year, t.value_type, t.description 
              FROM tbl_target t 
              WHERE t.sdp_id = :sdp_id 
              ORDER BY t.year, 
                       CASE t.quarter 
                           WHEN 'Q1' THEN 1 
                           WHEN 'Q2' THEN 2 
                           WHEN 'Q3' THEN 3 
                           WHEN 'Q4' THEN 4 
                           ELSE 5 
                       END";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':sdp_id', $sdp_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $targets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['targets' => $targets]);
} else {
    echo json_encode(['targets' => []]);
}
