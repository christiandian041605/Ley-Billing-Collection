<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";
include "bank.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    $bank = new Bank();
    $allBanks = $bank->getAll();
    $banks = [];
    
    foreach ($allBanks as $row) {
        $status = $row['status'] ?? 'Active';
        $statusBadge = $status === 'Active' 
            ? '<span class="badge bg-success">Active</span>' 
            : '<span class="badge bg-danger">Inactive</span>';
        
        $createdAt = isset($row['created_at']) 
            ? date('M d, Y', strtotime($row['created_at'])) 
            : '-';
        
        $actions = '<div class="btn-group" role="group">
            <button class="btn btn-primary btn-sm editBtn" type="button"
                data-id="' . $row['id'] . '"
                data-bs-toggle="modal" data-bs-target="#editBankModal"
                title="Edit Bank">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-danger btn-sm deleteBtn" type="button"
                data-id="' . $row['id'] . '"
                data-bs-toggle="modal" data-bs-target="#deleteBankModal"
                title="Delete Bank">
                <i class="bi bi-trash"></i>
            </button>
        </div>';
        
        $banks[] = [
            htmlspecialchars($row['bank_name'] ?? ''),
            htmlspecialchars($row['branch'] ?? ''),
            htmlspecialchars($row['bank_code'] ?? '-'),
            $statusBadge,
            $createdAt,
            $actions
        ];
    }
    
    echo json_encode(['data' => $banks]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
