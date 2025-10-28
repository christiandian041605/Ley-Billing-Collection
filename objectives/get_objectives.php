<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "objective.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $objective = new Objective($db);
    
    $stmt = $objective->read();
    $items = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $actions = '<div class="dropdown">
            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton-' . $row['objectives_id'] . '" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-' . $row['objectives_id'] . '">
                <li><a class="dropdown-item edit-btn" href="#" 
                    data-id="' . $row['objectives_id'] . '" 
                    data-details="' . htmlspecialchars($row['objectives_details']) . '" 
                    data-focus_area="' . htmlspecialchars($row['focus_area']) . '" 
                    data-bs-toggle="modal" data-bs-target="#editModal">
                    <i class="bi bi-pencil-square"></i> Edit</a></li>
                <li><a class="dropdown-item delete-btn" href="#" 
                    data-id="' . $row['objectives_id'] . '" 
                    data-details="' . htmlspecialchars($row['objectives_details']) . '" 
                    data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> Delete</a></li>
            </ul>
        </div>';
        
        $items[] = [
            htmlspecialchars($row['objectives_details']),
            htmlspecialchars($row['focus_area']),
            $actions
        ];
    }
    
    echo json_encode(['data' => $items]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
