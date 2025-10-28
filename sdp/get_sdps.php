<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "sdp.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $sdp = new Sdp($db);
    
    $stmt = $sdp->read();
    
    // Prepare responsibility query
    $resp_query = "SELECT rm.office_unit FROM tbl_sdp_responsibility sr 
                    LEFT JOIN tbl_responsibility_matrix rm ON sr.r_matrix_id = rm.r_matrix_id 
                    WHERE sr.sdp_id = ?";
    $resp_stmt = $db->prepare($resp_query);
    
    $items = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Get responsibilities
        $resp_stmt->execute([$row['sdp_id']]);
        $resp_units = $resp_stmt->fetchAll(PDO::FETCH_COLUMN);
        $respHtml = '';
        if (!empty($resp_units)) {
            foreach ($resp_units as $unit) {
                $respHtml .= "<span class='badge bg-secondary me-1'>" . htmlspecialchars($unit) . "</span>";
            }
        } else {
            $respHtml = "<span class='text-muted'>None</span>";
        }
        
        // Focus area badge
        $focusHtml = !empty($row['focus_area']) 
            ? "<span class='badge bg-info text-dark'>" . htmlspecialchars($row['focus_area']) . "</span>"
            : "<span class='text-muted'>N/A</span>";
            
        // Targets button
        $targetsHtml = "<button type='button' class='btn btn-info btn-sm view-targets-btn' 
            data-id='" . $row['sdp_id'] . "' 
            data-kpi='" . htmlspecialchars($row['kpi'], ENT_QUOTES) . "' 
            data-bs-toggle='modal' data-bs-target='#viewTargetsModal'>
            <i class='bi bi-eye'></i> View Targets
        </button>";
        
        // Actions dropdown
        $actions = '<div class="dropdown">
            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton-' . $row['sdp_id'] . '" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-' . $row['sdp_id'] . '">
                <li><a class="dropdown-item edit-btn" href="#" 
                    data-id="' . $row['sdp_id'] . '" 
                    data-objectives_id="' . $row['objectives_id'] . '" 
                    data-kpi="' . htmlspecialchars($row['kpi'], ENT_QUOTES) . '" 
                    data-initiatives="' . htmlspecialchars($row['initiatives'], ENT_QUOTES) . '" 
                    data-bs-toggle="modal" data-bs-target="#editSdpModal">
                    <i class="bi bi-pencil-square"></i> Edit</a></li>
                <li><a class="dropdown-item delete-btn" href="#" 
                    data-id="' . $row['sdp_id'] . '" 
                    data-kpi="' . htmlspecialchars($row['kpi'], ENT_QUOTES) . '" 
                    data-bs-toggle="modal" data-bs-target="#deleteSdpModal">
                    <i class="bi bi-trash"></i> Delete</a></li>
            </ul>
        </div>';
        
        $initiativesShort = strlen($row['initiatives']) > 100 
            ? substr($row['initiatives'], 0, 100) . '...' 
            : $row['initiatives'];
        
        $items[] = [
            htmlspecialchars($row['kpi']),
            htmlspecialchars($initiativesShort),
            htmlspecialchars($row['objectives_details'] ?? '<span class="text-muted">N/A</span>'),
            $focusHtml,
            $respHtml,
            $targetsHtml,
            $actions
        ];
    }
    
    echo json_encode(['data' => $items]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

