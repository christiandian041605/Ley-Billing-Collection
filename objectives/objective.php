<?php
class Objective
{
    private $conn;
    private $table_name = "tbl_objectives";

    public $objectives_id;
    public $objectives_details;
    public $focus_area;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET objectives_details=:objectives_details, focus_area=:focus_area";
        $stmt = $this->conn->prepare($query);

        $this->objectives_details = htmlspecialchars(strip_tags($this->objectives_details));
        $this->focus_area = htmlspecialchars(strip_tags($this->focus_area));

        $stmt->bindParam(":objectives_details", $this->objectives_details);
        $stmt->bindParam(":focus_area", $this->focus_area);

        if ($stmt->execute()) {
            $this->objectives_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    function isDuplicate($field, $value, $objectives_id = null)
    {
        if ($field !== 'objectives_details') {
            return false;
        }
        $query = "SELECT objectives_id FROM " . $this->table_name . " WHERE " . $field . " = :value";
        if ($objectives_id) {
            $query .= " AND objectives_id != :objectives_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":value", $value);
        if ($objectives_id) {
            $stmt->bindParam(":objectives_id", $objectives_id);
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
        $query = "SELECT * FROM " . $this->table_name . " WHERE objectives_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->objectives_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->objectives_id = $row['objectives_id'];
            $this->objectives_details = $row['objectives_details'];
            $this->focus_area = $row['focus_area'];
        }
    }

    function update()
    {
        $query = "UPDATE " . $this->table_name . " SET objectives_details=:objectives_details, focus_area=:focus_area WHERE objectives_id=:objectives_id";
        $stmt = $this->conn->prepare($query);

        $this->objectives_details = htmlspecialchars(strip_tags($this->objectives_details));
        $this->focus_area = htmlspecialchars(strip_tags($this->focus_area));
        $this->objectives_id = htmlspecialchars(strip_tags($this->objectives_id));

        $stmt->bindParam(':objectives_details', $this->objectives_details);
        $stmt->bindParam(':focus_area', $this->focus_area);
        $stmt->bindParam(':objectives_id', $this->objectives_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE objectives_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->objectives_id = htmlspecialchars(strip_tags($this->objectives_id));
        $stmt->bindParam(1, $this->objectives_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>