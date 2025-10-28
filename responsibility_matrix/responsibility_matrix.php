<?php
class ResponsibilityMatrix
{
    private $conn;
    private $table_name = "tbl_responsibility_matrix";

    public $r_matrix_id;
    public $office_unit;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET office_unit=:office_unit";
        $stmt = $this->conn->prepare($query);

        $this->office_unit = htmlspecialchars(strip_tags($this->office_unit));
        $stmt->bindParam(":office_unit", $this->office_unit);

        if ($stmt->execute()) {
            $this->r_matrix_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    function isDuplicate($field, $value, $r_matrix_id = null)
    {
        if ($field !== 'office_unit') {
            return false;
        }
        $query = "SELECT r_matrix_id FROM " . $this->table_name . " WHERE " . $field . " = :value";
        if ($r_matrix_id) {
            $query .= " AND r_matrix_id != :r_matrix_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":value", $value);
        if ($r_matrix_id) {
            $stmt->bindParam(":r_matrix_id", $r_matrix_id);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    function read()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE r_matrix_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->r_matrix_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->r_matrix_id = $row['r_matrix_id'];
            $this->office_unit = $row['office_unit'];
        }
    }

    function update()
    {
        $query = "UPDATE " . $this->table_name . " SET office_unit=:office_unit WHERE r_matrix_id=:r_matrix_id";
        $stmt = $this->conn->prepare($query);

        $this->office_unit = htmlspecialchars(strip_tags($this->office_unit));
        $this->r_matrix_id = htmlspecialchars(strip_tags($this->r_matrix_id));

        $stmt->bindParam(':office_unit', $this->office_unit);
        $stmt->bindParam(':r_matrix_id', $this->r_matrix_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE r_matrix_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->r_matrix_id = htmlspecialchars(strip_tags($this->r_matrix_id));
        $stmt->bindParam(1, $this->r_matrix_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>