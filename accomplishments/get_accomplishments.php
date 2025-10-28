<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";
include "accomplishment.php";

$database = new Database();
$db = $database->getConnection();

$accomplishment = new Accomplishment($db);
$stmt = $accomplishment->read();

$data = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($row);
    
    $kpi_display = htmlspecialchars($kpi ?? 'N/A');
    $office_display = htmlspecialchars($office_unit ?? 'N/A');
    $target_display = '';
    
    if ($target_value) {
        $target_display .= number_format($target_value, 2);
    }
    if ($quarter) {
        $target_display .= " ({$quarter})";
    }
    if ($year) {
        $target_display .= " - {$year}";
    }
    if (empty($target_display)) {
        $target_display = 'N/A';
    }
    
    $accomplishment_display = htmlspecialchars($accomplishment ?? 'N/A');
    $evidence_display = htmlspecialchars($evidence ?? '-');
    $description_display = htmlspecialchars($description ?? '-');
    
    $actions = '
        <button class="btn btn-sm btn-info edit-btn" 
            data-id="' . $accomplishment_id . '" 
            data-sdp_id="' . $sdp_id . '" 
            data-target_id="' . ($target_id ?? '') . '" 
            data-accomplishment="' . htmlspecialchars($accomplishment) . '" 
            data-r_matrix_id="' . ($r_matrix_id ?? '') . '" 
            data-evidence="' . htmlspecialchars($evidence) . '" 
            data-description="' . htmlspecialchars($description) . '" 
            data-bs-toggle="modal" 
            data-bs-target="#editAccomplishmentModal">
            <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-danger delete-btn" 
            data-id="' . $accomplishment_id . '" 
            data-kpi="' . htmlspecialchars($kpi) . '" 
            data-bs-toggle="modal" 
            data-bs-target="#deleteAccomplishmentModal">
            <i class="bi bi-trash"></i>
        </button>
    ';
    
    $data[] = array(
        $kpi_display,
        $office_display,
        $target_display,
        $accomplishment_display,
        $evidence_display,
        $description_display,
        $actions
    );
}

echo json_encode(array("data" => $data));
