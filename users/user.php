<?php
class User
{
    private $conn;
    private $table_name = "tbl_user";

    public $user_id;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $position;
    public $username;
    public $password;
    public $role;
    public $r_matrix_id;

    // Helper properties for forms
    public $full_name;
    public $office;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getOfficeUnitById($id)
    {
        if (empty($id)) {
            return null;
        }
        $query = "SELECT office_unit FROM tbl_responsibility_matrix WHERE r_matrix_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['office_unit'];
        }
        return null;
    }

    private function mapRoleToDb($role_id)
    {
        $role_map = [
            'Admin' => 'Admin',
            'Faculty' => 'Faculty',
            'Staff' => 'Staff',
            'Dean' => 'Dean',
            'Director' => 'Director',
            // For numeric roles from old form
            1 => 'Admin',
            2 => 'Staff' // Assuming Encoder maps to Staff
        ];
        return $role_map[$role_id] ?? 'Staff';
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET first_name=:first_name, middle_name=:middle_name, last_name=:last_name, username=:username, position=:position, r_matrix_id=:r_matrix_id, password=:password, role=:role";
        $stmt = $this->conn->prepare($query);

        // Sanitize and prepare data
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $name_parts = explode(' ', $this->full_name, 3);
        $this->first_name = $name_parts[0] ?? '';
        $this->middle_name = (count($name_parts) == 3) ? $name_parts[1] : null;
        $this->last_name = (count($name_parts) == 3) ? $name_parts[2] : ($name_parts[1] ?? '');

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->role = $this->mapRoleToDb($this->role);

        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":middle_name", $this->middle_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":position", $this->position);
        $stmt->bindParam(":r_matrix_id", $this->r_matrix_id);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);

        if ($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    function isDuplicate($field, $value, $userId = null)
    {
        // Only username is supported for duplication check now
        if ($field !== 'username') {
            return false;
        }
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE " . $field . " = :value";
        if ($userId) {
            $query .= " AND user_id != :user_id";
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
        $query = "SELECT u.user_id, u.first_name, u.middle_name, u.last_name, u.position, u.username, u.role, u.r_matrix_id, rm.office_unit as office, CONCAT(u.first_name, ' ', u.last_name) as full_name FROM " . $this->table_name . " u LEFT JOIN tbl_responsibility_matrix rm ON u.r_matrix_id = rm.r_matrix_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne()
    {
        $query = "SELECT u.*, rm.office_unit as office FROM " . $this->table_name . " u LEFT JOIN tbl_responsibility_matrix rm ON u.r_matrix_id = rm.r_matrix_id WHERE u.user_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->user_id = $row['user_id'];
            $this->first_name = $row['first_name'];
            $this->middle_name = $row['middle_name'];
            $this->last_name = $row['last_name'];
            $this->full_name = trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $this->username = $row['username'];
            $this->position = $row['position'];
            $this->role = $row['role'];
            $this->r_matrix_id = $row['r_matrix_id'];
            $this->office = $row['office'];
        }
    }

    function update()
    {
        $query = "UPDATE " . $this->table_name . " SET first_name=:first_name, middle_name=:middle_name, last_name=:last_name, username=:username, position=:position, r_matrix_id=:r_matrix_id, role=:role";

        if (!empty($this->password)) {
            $query .= ", password=:password";
        }

        $query .= " WHERE user_id=:user_id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and prepare data
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $name_parts = explode(' ', $this->full_name, 3);
        $this->first_name = $name_parts[0] ?? '';
        $this->middle_name = (count($name_parts) == 3) ? $name_parts[1] : null;
        $this->last_name = (count($name_parts) == 3) ? $name_parts[2] : ($name_parts[1] ?? '');

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        $this->role = $this->mapRoleToDb($this->role);

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':middle_name', $this->middle_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':r_matrix_id', $this->r_matrix_id);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':user_id', $this->user_id);

        if (!empty($this->password)) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $this->password);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $stmt->bindParam(1, $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>