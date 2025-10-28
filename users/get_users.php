<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "user.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

function getRoleBadge($role) {
    switch ($role) {
        case 'Admin':
            return '<span class="badge bg-danger">Admin</span>';
        case 'Faculty':
            return '<span class="badge bg-info">Faculty</span>';
        case 'Staff':
            return '<span class="badge bg-primary">Staff</span>';
        case 'Dean':
            return '<span class="badge bg-success">Dean</span>';
        case 'Director':
            return '<span class="badge bg-warning">Director</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

function generateAvatar($name) {
    if (empty($name)) {
        $name = '';
    }
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        if (!empty($w)) {
            $initials .= $w[0];
        }
    }
    $initials = strtoupper(substr($initials, 0, 2));
    $hash = md5($name);
    $color = substr($hash, 0, 6);
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));
    $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    $textColor = ($brightness > 128) ? '000000' : 'FFFFFF';
    
    return '<div class="d-flex align-items-center">
                <div style="width: 40px; height: 40px; background-color: #' . $color . '; color: #' . $textColor . '; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; margin-right: 10px;">
                    ' . $initials . '
                </div>
            </div>';
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    $stmt = $user->read();
    $users = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $avatar = generateAvatar($row['full_name']);
        $nameCell = $avatar . '<div><strong>' . htmlspecialchars($row['full_name']) . '</strong><br><small>@' . htmlspecialchars($row['username']) . '</small></div>';
        
        $actions = '<div class="dropdown">
            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton-' . $row['user_id'] . '" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-' . $row['user_id'] . '">
                <li><a class="dropdown-item edit-btn" href="#" 
                    data-id="' . $row['user_id'] . '" 
                    data-full_name="' . htmlspecialchars($row['full_name']) . '" 
                    data-username="' . htmlspecialchars($row['username']) . '" 
                    data-position="' . htmlspecialchars($row['position']) . '" 
                    data-r_matrix_id="' . $row['r_matrix_id'] . '" 
                    data-office="' . htmlspecialchars($row['office']) . '" 
                    data-role="' . htmlspecialchars($row['role']) . '" 
                    data-bs-toggle="modal" data-bs-target="#editUserModal">
                    <i class="bi bi-pencil-square"></i> Edit</a></li>
                <li><a class="dropdown-item delete-btn" href="#" 
                    data-id="' . $row['user_id'] . '" 
                    data-full_name="' . htmlspecialchars($row['full_name']) . '" 
                    data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                    <i class="bi bi-trash"></i> Delete</a></li>
            </ul>
        </div>';
        
        $users[] = [
            $nameCell,
            htmlspecialchars($row['position']),
            htmlspecialchars($row['office']),
            getRoleBadge($row['role']),
            $actions
        ];
    }
    
    echo json_encode(['data' => $users]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
