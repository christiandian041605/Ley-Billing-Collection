<?php
class Accomplishment
{
    private $conn;
    private $table_name = "tbl_accomplishment";

    public $accomplishment_id;
    public $sdp_id;
    public $target_id;
    public $accomplishment;
    public $r_matrix_id;
    public $evidence;
    public $description;

    // Helper properties
    public $kpi;
    public $office_unit;
    public $target_value;
    public $quarter;
    public $year;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function create()
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET sdp_id=:sdp_id, target_id=:target_id, accomplishment=:accomplishment, 
                          r_matrix_id=:r_matrix_id, evidence=:evidence, description=:description";
            $stmt = $this->conn->prepare($query);

            // Sanitize and prepare data
            $this->sdp_id = htmlspecialchars(strip_tags($this->sdp_id));
            $this->target_id = empty($this->target_id) ? null : htmlspecialchars(strip_tags($this->target_id));
            $this->accomplishment = htmlspecialchars(strip_tags($this->accomplishment));
            $this->r_matrix_id = empty($this->r_matrix_id) ? null : htmlspecialchars(strip_tags($this->r_matrix_id));
            $this->evidence = htmlspecialchars(strip_tags($this->evidence));
            $this->description = htmlspecialchars(strip_tags($this->description));

            $stmt->bindParam(":sdp_id", $this->sdp_id, PDO::PARAM_INT);
            
            if ($this->target_id === null) {
                $stmt->bindValue(":target_id", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(":target_id", $this->target_id, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(":accomplishment", $this->accomplishment);
            
            if ($this->r_matrix_id === null) {
                $stmt->bindValue(":r_matrix_id", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(":r_matrix_id", $this->r_matrix_id, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(":evidence", $this->evidence);
            $stmt->bindParam(":description", $this->description);

            if ($stmt->execute()) {
                $this->accomplishment_id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Accomplishment create error: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    function read()
    {
        $query = "SELECT a.*, s.kpi, t.target_value, t.quarter, t.year, t.value_type, rm.office_unit 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN tbl_sdp s ON a.sdp_id = s.sdp_id 
                  LEFT JOIN tbl_target t ON a.target_id = t.target_id 
                  LEFT JOIN tbl_responsibility_matrix rm ON a.r_matrix_id = rm.r_matrix_id 
                  ORDER BY a.accomplishment_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne()
    {
        $query = "SELECT a.*, s.kpi, t.target_value, t.quarter, t.year, t.value_type, rm.office_unit 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN tbl_sdp s ON a.sdp_id = s.sdp_id 
                  LEFT JOIN tbl_target t ON a.target_id = t.target_id 
                  LEFT JOIN tbl_responsibility_matrix rm ON a.r_matrix_id = rm.r_matrix_id 
                  WHERE a.accomplishment_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->accomplishment_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->accomplishment_id = $row['accomplishment_id'];
            $this->sdp_id = $row['sdp_id'];
            $this->target_id = $row['target_id'];
            $this->accomplishment = $row['accomplishment'];
            $this->r_matrix_id = $row['r_matrix_id'];
            $this->evidence = $row['evidence'];
            $this->description = $row['description'];
            $this->kpi = $row['kpi'];
            $this->office_unit = $row['office_unit'];
            $this->target_value = $row['target_value'];
            $this->quarter = $row['quarter'];
            $this->year = $row['year'];
        }
    }

    function update()
    {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET sdp_id=:sdp_id, target_id=:target_id, accomplishment=:accomplishment, 
                          r_matrix_id=:r_matrix_id, evidence=:evidence, description=:description 
                      WHERE accomplishment_id=:accomplishment_id";
            $stmt = $this->conn->prepare($query);

            // Sanitize and prepare data
            $this->sdp_id = htmlspecialchars(strip_tags($this->sdp_id));
            $this->target_id = empty($this->target_id) ? null : htmlspecialchars(strip_tags($this->target_id));
            $this->accomplishment = htmlspecialchars(strip_tags($this->accomplishment));
            $this->r_matrix_id = empty($this->r_matrix_id) ? null : htmlspecialchars(strip_tags($this->r_matrix_id));
            $this->evidence = htmlspecialchars(strip_tags($this->evidence));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->accomplishment_id = htmlspecialchars(strip_tags($this->accomplishment_id));

            $stmt->bindParam(':sdp_id', $this->sdp_id, PDO::PARAM_INT);
            
            if ($this->target_id === null) {
                $stmt->bindValue(':target_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':target_id', $this->target_id, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':accomplishment', $this->accomplishment);
            
            if ($this->r_matrix_id === null) {
                $stmt->bindValue(':r_matrix_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':r_matrix_id', $this->r_matrix_id, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':evidence', $this->evidence);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':accomplishment_id', $this->accomplishment_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Accomplishment update error: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE accomplishment_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->accomplishment_id = htmlspecialchars(strip_tags($this->accomplishment_id));
        $stmt->bindParam(1, $this->accomplishment_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
