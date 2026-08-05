<?php
class User
{
    private $conn;
    private $table_name = "tbl_users"; // Updated to match System-Function.txt

    public $id; // Changed from user_id to match tbl_users schema
    public $first_name;
    public $middle_name;
    public $last_name;
    public $position;
    public $username;
    public $password;
    public $role;
    public $created_at;

    // Helper properties (for backwards compatibility)
    public $user_id; // For backwards compatibility
    public $fullname;
    public $accounttype;
    public $full_name;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function mapRoleToDb($role_id)
    {
        $role_map = [
            'Admin' => 'Admin', // Full system administrator
            'Encoder' => 'Encoder', // Data entry and billing operations
            'Viewer' => 'Viewer', // Read-only access to system data
            // Backwards compatibility
            'Staff' => 'Encoder', // Map old Staff role to Encoder
            'Faculty' => 'Encoder', // Map old Faculty role to Encoder
            'Dean' => 'Admin', // Map old Dean role to Admin
            'Director' => 'Admin', // Map old Director role to Admin
            1 => 'Admin',
            2 => 'Encoder'
        ];
        return $role_map[$role_id] ?? 'Encoder';
    }

    private function getFullName()
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $parts);
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET first_name=:first_name,
                      middle_name=:middle_name,
                      last_name=:last_name,
                      position=:position,
                      username=:username, 
                      password_hash=:password_hash, 
                      role=:role";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize and prepare data
        $this->first_name = htmlspecialchars(strip_tags($this->first_name ?? ''));
        $this->middle_name = htmlspecialchars(strip_tags($this->middle_name ?? ''));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name ?? ''));
        $this->position = htmlspecialchars(strip_tags($this->position ?? ''));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->role = $this->mapRoleToDb($this->role ?? $this->accounttype ?? 'Encoder');

        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":middle_name", $this->middle_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password_hash", $hashed_password);
        $stmt->bindParam(":role", $this->role);

        try {
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                $this->user_id = $this->id; // For backwards compatibility
                return true;
            }
            // Log error info for debugging
            error_log("User create failed: " . implode(", ", $stmt->errorInfo()));
            return false;
        } catch (Exception $e) {
            error_log("User create exception: " . $e->getMessage());
            return false;
        }
    }

    function isDuplicate($field, $value, $userId = null)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE " . $field . " = :value";
        if ($userId) {
            $query .= " AND id != :user_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":value", $value);
        if ($userId) {
            $stmt->bindParam(":user_id", $userId);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    function read()
    {
        $query = "SELECT id as user_id, first_name, middle_name, last_name, position, username, role, created_at,
                         CONCAT_WS(' ', first_name, middle_name, last_name) as fullname
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $user_id = $this->id ?: $this->user_id;
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->user_id = $row['id']; // For backwards compatibility
            $this->first_name = $row['first_name'];
            $this->middle_name = $row['middle_name'];
            $this->last_name = $row['last_name'];
            $this->position = $row['position'];
            $this->username = $row['username'];
            $this->role = $row['role'];
            $this->created_at = $row['created_at'];
            
            // Set helper properties for backwards compatibility
            $this->fullname = $this->getFullName();
            $this->accounttype = $row['role'];
        }
    }

    function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name=:first_name,
                      middle_name=:middle_name,
                      last_name=:last_name,
                      position=:position,
                      username=:username, 
                      role=:role";

        if (!empty($this->password)) {
            $query .= ", password_hash=:password_hash";
        }

        $query .= " WHERE id=:user_id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and prepare data
        $this->first_name = htmlspecialchars(strip_tags($this->first_name ?? ''));
        $this->middle_name = htmlspecialchars(strip_tags($this->middle_name ?? ''));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name ?? ''));
        $this->position = htmlspecialchars(strip_tags($this->position ?? ''));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $user_id = $this->id ?: $this->user_id;
        $this->role = $this->mapRoleToDb($this->role ?? $this->accounttype ?? 'Encoder');

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':middle_name', $this->middle_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':user_id', $user_id);

        if (!empty($this->password)) {
            $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password_hash', $hashed_password);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $user_id = htmlspecialchars(strip_tags($this->id ?: $this->user_id));
        $stmt->bindParam(1, $user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
