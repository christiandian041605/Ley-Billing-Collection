<?php
/**
 * Bank Management Class
 * Handles bank and branch CRUD operations for check processing
 */

require_once '../config/database.php';

class Bank {
    private $conn;
    private $table = 'tbl_banks';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get all banks with optional status filter
     */
    public function getAll($status = null) {
        $query = "SELECT * FROM " . $this->table;
        
        if ($status !== null) {
            $query .= " WHERE status = ?";
        }
        
        $query .= " ORDER BY bank_name ASC, branch ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($status !== null) {
            $stmt->execute([$status]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    /**
     * Get bank by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get banks by bank name
     */
    public function getByBankName($bank_name) {
        $query = "SELECT * FROM " . $this->table . " WHERE bank_name = ? ORDER BY branch ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$bank_name]);
        return $stmt->fetchAll();
    }

    /**
     * Create new bank
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['bank_name']) || empty($data['branch_name'])) {
            return ['success' => false, 'message' => 'Bank name and branch name are required'];
        }

        // Check for duplicates
        if ($this->isDuplicate($data['bank_name'], $data['branch_name'])) {
            return ['success' => false, 'message' => 'This bank-branch combination already exists'];
        }

        $query = "INSERT INTO " . $this->table . " 
                  (bank_name, branch, bank_code, status) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $status = isset($data['status']) ? $data['status'] : 'Active';
        $bank_code = isset($data['bank_code']) ? $data['bank_code'] : '';
        
        if ($stmt->execute([
            $data['bank_name'],
            $data['branch_name'],
            $bank_code,
            $status
        ])) {
            return [
                'success' => true,
                'message' => 'Bank added successfully',
                'id' => $this->conn->lastInsertId()
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to add bank'];
    }

    /**
     * Update bank information
     */
    public function update($id, $data) {
        // Get existing bank
        $existing = $this->getById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Bank not found'];
        }

        // Check for duplicate if bank/branch name changed
        $new_bank_name = isset($data['bank_name']) ? $data['bank_name'] : $existing['bank_name'];
        $new_branch_name = isset($data['branch_name']) ? $data['branch_name'] : $existing['branch'];
        
        if ($new_bank_name !== $existing['bank_name'] || $new_branch_name !== $existing['branch']) {
            if ($this->isDuplicate($new_bank_name, $new_branch_name, $id)) {
                return ['success' => false, 'message' => 'This bank-branch combination already exists'];
            }
        }

        $query = "UPDATE " . $this->table . " SET 
                  bank_name = ?,
                  branch = ?,
                  bank_code = ?,
                  status = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $bank_code = isset($data['bank_code']) ? $data['bank_code'] : ($existing['bank_code'] ?? '');
        $status = isset($data['status']) ? $data['status'] : ($existing['status'] ?? 'Active');
        
        if ($stmt->execute([
            $new_bank_name,
            $new_branch_name,
            $bank_code,
            $status,
            $id
        ])) {
            return ['success' => true, 'message' => 'Bank updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update bank'];
    }

    /**
     * Delete bank (soft delete via status)
     */
    public function delete($id) {
        // Check if bank is being used in payments
        if ($this->isInUse($id)) {
            return ['success' => false, 'message' => 'Cannot delete bank that has associated payments'];
        }

        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$id])) {
            return ['success' => true, 'message' => 'Bank deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete bank'];
    }

    /**
     * Check if bank-branch combination is duplicate
     */
    public function isDuplicate($bank_name, $branch_name, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . 
                 " WHERE bank_name = ? AND branch = ?";
        
        $params = [$bank_name, $branch_name];
        
        if ($exclude_id !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }

    /**
     * Check if bank is in use in payment system
     */
    public function isInUse($id) {
        $query = "SELECT COUNT(*) as count FROM tbl_official_receipts 
                  WHERE bank_id = ? AND bank_id IS NOT NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }

    /**
     * Get count of banks
     */
    public function getCount($status = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table;
        
        if ($status !== null) {
            $query .= " WHERE status = ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($status !== null) {
            $stmt->execute([$status]);
        } else {
            $stmt->execute();
        }
        
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Get active banks only
     */
    public function getActive() {
        return $this->getAll('Active');
    }

    /**
     * Toggle bank status
     */
    public function toggleStatus($id) {
        $bank = $this->getById($id);
        if (!$bank) {
            return ['success' => false, 'message' => 'Bank not found'];
        }

        $new_status = $bank['status'] === 'Active' ? 'Inactive' : 'Active';
        
        $query = "UPDATE " . $this->table . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$new_status, $id])) {
            return ['success' => true, 'message' => "Bank status changed to $new_status"];
        }
        
        return ['success' => false, 'message' => 'Failed to update bank status'];
    }

    /**
     * Search banks by name or branch
     */
    public function search($term) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE bank_name LIKE ? OR branch LIKE ? OR bank_code LIKE ?
                  ORDER BY bank_name ASC, branch ASC";
        
        $searchTerm = "%$term%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        
        return $stmt->fetchAll();
    }
}
?>
