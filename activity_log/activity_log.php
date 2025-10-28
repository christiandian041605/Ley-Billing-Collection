<?php
class ActivityLog {
    private $conn;
    private $table_name = "tbl_activity_logs";

    public $log_id;
    public $user_id;
    public $action;
    public $module;
    public $details;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, action=:action, module=:module, details=:details";
        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->action = htmlspecialchars(strip_tags($this->action));
        $this->module = htmlspecialchars(strip_tags($this->module));
        $this->details = htmlspecialchars(strip_tags($this->details));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":action", $this->action);
        $stmt->bindParam(":module", $this->module);
        $stmt->bindParam(":details", $this->details);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function read() {
        $query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as full_name FROM " . $this->table_name . " a LEFT JOIN tbl_user u ON a.user_id = u.user_id ORDER BY a.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
