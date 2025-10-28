<?php
class Sdp
{
    private $conn;
    private $table_name = "tbl_sdp";

    public $sdp_id;
    public $objectives_id;
    public $kpi;
    public $initiatives;

    // Helper properties
    public $objectives_details;
    public $focus_area;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getObjectiveById($id)
    {
        if (empty($id)) {
            return null;
        }
        $query = "SELECT objectives_details FROM tbl_objectives WHERE objectives_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['objectives_details'];
        }
        return null;
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET objectives_id=:objectives_id, kpi=:kpi, initiatives=:initiatives";
        $stmt = $this->conn->prepare($query);

        // Sanitize and prepare data
        $this->objectives_id = empty($this->objectives_id) ? null : htmlspecialchars(strip_tags($this->objectives_id));
        $this->kpi = htmlspecialchars(strip_tags($this->kpi));
        $this->initiatives = htmlspecialchars(strip_tags($this->initiatives));

        $stmt->bindParam(":objectives_id", $this->objectives_id);
        $stmt->bindParam(":kpi", $this->kpi);
        $stmt->bindParam(":initiatives", $this->initiatives);

        if ($stmt->execute()) {
            $this->sdp_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    function isDuplicate($field, $value, $sdpId = null)
    {
        // Check for duplicate KPI
        if ($field !== 'kpi') {
            return false;
        }
        $query = "SELECT sdp_id FROM " . $this->table_name . " WHERE " . $field . " = :value";
        if ($sdpId) {
            $query .= " AND sdp_id != :sdp_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":value", $value);
        if ($sdpId) {
            $stmt->bindParam(":sdp_id", $sdpId);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    function read()
    {
        $query = "SELECT s.sdp_id, s.objectives_id, s.kpi, s.initiatives, 
                         o.objectives_details, o.focus_area 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN tbl_objectives o ON s.objectives_id = o.objectives_id 
                  ORDER BY s.sdp_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne()
    {
        $query = "SELECT s.*, o.objectives_details, o.focus_area 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN tbl_objectives o ON s.objectives_id = o.objectives_id 
                  WHERE s.sdp_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->sdp_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->sdp_id = $row['sdp_id'];
            $this->objectives_id = $row['objectives_id'];
            $this->kpi = $row['kpi'];
            $this->initiatives = $row['initiatives'];
            $this->objectives_details = $row['objectives_details'];
            $this->focus_area = $row['focus_area'];
        }
    }

    function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET objectives_id=:objectives_id, kpi=:kpi, initiatives=:initiatives 
                  WHERE sdp_id=:sdp_id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and prepare data
        $this->objectives_id = empty($this->objectives_id) ? null : htmlspecialchars(strip_tags($this->objectives_id));
        $this->kpi = htmlspecialchars(strip_tags($this->kpi));
        $this->initiatives = htmlspecialchars(strip_tags($this->initiatives));
        $this->sdp_id = htmlspecialchars(strip_tags($this->sdp_id));

        $stmt->bindParam(':objectives_id', $this->objectives_id);
        $stmt->bindParam(':kpi', $this->kpi);
        $stmt->bindParam(':initiatives', $this->initiatives);
        $stmt->bindParam(':sdp_id', $this->sdp_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE sdp_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->sdp_id = htmlspecialchars(strip_tags($this->sdp_id));
        $stmt->bindParam(1, $this->sdp_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get responsibilities for an SDP
    function getResponsibilities()
    {
        $query = "SELECT sr.s_responsibility_id, sr.r_matrix_id, rm.office_unit 
                  FROM tbl_sdp_responsibility sr 
                  LEFT JOIN tbl_responsibility_matrix rm ON sr.r_matrix_id = rm.r_matrix_id 
                  WHERE sr.sdp_id = :sdp_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sdp_id', $this->sdp_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add responsibility
    function addResponsibility($r_matrix_id)
    {
        $query = "INSERT INTO tbl_sdp_responsibility SET sdp_id=:sdp_id, r_matrix_id=:r_matrix_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sdp_id', $this->sdp_id);
        $stmt->bindParam(':r_matrix_id', $r_matrix_id);
        return $stmt->execute();
    }

    // Remove all responsibilities for SDP (used before updating)
    function removeAllResponsibilities()
    {
        $query = "DELETE FROM tbl_sdp_responsibility WHERE sdp_id = :sdp_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sdp_id', $this->sdp_id);
        return $stmt->execute();
    }

    // Get targets for an SDP
    function getTargets()
    {
        $query = "SELECT * FROM tbl_target WHERE sdp_id = :sdp_id ORDER BY year, quarter";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sdp_id', $this->sdp_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add target
    function addTarget($target_value, $description, $value_type, $quarter, $year)
    {
        $query = "INSERT INTO tbl_target SET sdp_id=:sdp_id, target_value=:target_value, description=:description, value_type=:value_type, quarter=:quarter, year=:year";
        $stmt = $this->conn->prepare($query);
        
        // Coerce and sanitize values
        $clean_value_type = $value_type ?: 'Other';
        $clean_quarter = $quarter ?: null; // enum allows NULL
        $clean_year = (int)$year; // YEAR is NOT NULL
        
        // target_value may be NULL; if empty string or not numeric, set NULL
        if ($target_value === '' || $target_value === null) {
            $clean_target_value = null;
        } else {
            $clean_target_value = is_numeric($target_value) ? (float)$target_value : null;
        }
        // description nullable
        $clean_description = ($description === '' ? null : $description);

        $stmt->bindParam(':sdp_id', $this->sdp_id, PDO::PARAM_INT);
        if ($clean_target_value === null) {
            $stmt->bindValue(':target_value', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':target_value', $clean_target_value);
        }
        if ($clean_description === null) {
            $stmt->bindValue(':description', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':description', $clean_description);
        }
        $stmt->bindValue(':value_type', $clean_value_type);
        if ($clean_quarter === null) {
            $stmt->bindValue(':quarter', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':quarter', $clean_quarter);
        }
        $stmt->bindValue(':year', $clean_year, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Remove all targets for SDP (used before updating)
    function removeAllTargets()
    {
        $query = "DELETE FROM tbl_target WHERE sdp_id = :sdp_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sdp_id', $this->sdp_id);
        return $stmt->execute();
    }
}
