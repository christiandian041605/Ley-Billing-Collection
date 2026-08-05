<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "user.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

function getRoleBadge($role) {
    switch ($role) {
        case 'Admin':
            return '<span class="badge bg-danger">Admin</span>';
        case 'Encoder':
            return '<span class="badge bg-primary">Encoder</span>';
        case 'Viewer':
            return '<span class="badge bg-info">Viewer</span>';
        // Legacy roles for backwards compatibility
        case 'Faculty':
            return '<span class="badge bg-warning">Faculty</span>';
        case 'Staff':
            return '<span class="badge bg-primary">Staff</span>';
        case 'Dean':
            return '<span class="badge bg-success">Dean</span>';
        case 'Director':
            return '<span class="badge bg-info">Director</span>';
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
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    $user = new User($db);
    
    $stmt = $user->read();
    $users = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fullname = $row['fullname'] ?? '';
        $position = $row['position'] ?? '';
        $role = $row['role'] ?? 'Staff';
        
        $avatar = generateAvatar($fullname);
        $nameCell = $avatar . '<div><strong>' . htmlspecialchars($fullname) . '</strong><br><small>@' . htmlspecialchars($row['username']) . '</small></div>';
        
        $actions = '<div class="btn-group" role="group">
            <button class="btn btn-primary btn-sm edit-btn" type="button"
                data-id="' . $row['user_id'] . '" 
                data-firstname="' . htmlspecialchars($row['first_name'] ?? '') . '" 
                data-middlename="' . htmlspecialchars($row['middle_name'] ?? '') . '" 
                data-lastname="' . htmlspecialchars($row['last_name'] ?? '') . '" 
                data-position="' . htmlspecialchars($position) . '" 
                data-username="' . htmlspecialchars($row['username']) . '" 
                data-role="' . htmlspecialchars($role) . '" 
                data-bs-toggle="modal" data-bs-target="#editUserModal"
                title="Edit User">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-danger btn-sm delete-btn" type="button"
                data-id="' . $row['user_id'] . '" 
                data-fullname="' . htmlspecialchars($fullname) . '" 
                data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                title="Delete User">
                <i class="bi bi-trash"></i>
            </button>
        </div>';
        
        $users[] = [
            $nameCell,
            htmlspecialchars($position ?: '-'),
            getRoleBadge($role),
            $actions
        ];
    }
    
    echo json_encode(['data' => $users]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
