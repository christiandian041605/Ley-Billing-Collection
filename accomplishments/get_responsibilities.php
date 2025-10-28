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
    $query = "SELECT sr.r_matrix_id, rm.office_unit 
              FROM tbl_sdp_responsibility sr 
              LEFT JOIN tbl_responsibility_matrix rm ON sr.r_matrix_id = rm.r_matrix_id 
              WHERE sr.sdp_id = :sdp_id 
              ORDER BY rm.office_unit";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':sdp_id', $sdp_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $responsibilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['responsibilities' => $responsibilities]);
} else {
    echo json_encode(['responsibilities' => []]);
}
