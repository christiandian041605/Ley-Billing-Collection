<?php
class AppSetting {
    private $conn;
    private $table_name = "tbl_app_setting";

    public $setting_id;
    public $app_name;
    public $address;
    public $contact_number;
    public $email;
    public $about;

    public function __construct($db) {
        $this->conn = $db;
    }

        function create() {
        $query = "INSERT INTO " . $this->table_name . " SET app_name=:app_name, address=:address, contact_number=:contact_number, email=:email, about=:about";
        $stmt = $this->conn->prepare($query);

        $this->app_name = htmlspecialchars(strip_tags($this->app_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->contact_number = htmlspecialchars(strip_tags($this->contact_number));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->about = htmlspecialchars(strip_tags($this->about));

        $stmt->bindParam(":app_name", $this->app_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":contact_number", $this->contact_number);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":about", $this->about);

        if ($stmt->execute()) {
            $this->setting_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE setting_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->setting_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->setting_id = $row['setting_id'];
            $this->app_name = $row['app_name'];
            $this->address = $row['address'];
            $this->contact_number = $row['contact_number'];
            $this->email = $row['email'];
            $this->about = $row['about'];
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET app_name=:app_name, address=:address, contact_number=:contact_number, email=:email, about=:about WHERE setting_id=:setting_id";

        $stmt = $this->conn->prepare($query);

        $this->app_name = htmlspecialchars(strip_tags($this->app_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->contact_number = htmlspecialchars(strip_tags($this->contact_number));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->about = htmlspecialchars(strip_tags($this->about));
        $this->setting_id = htmlspecialchars(strip_tags($this->setting_id));

        $stmt->bindParam(':app_name', $this->app_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':contact_number', $this->contact_number);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':about', $this->about);
        $stmt->bindParam(':setting_id', $this->setting_id);

        try {
            if ($stmt->execute()) {
                return true;
            }
            // Log error info for debugging
            error_log("AppSetting update failed: " . implode(", ", $stmt->errorInfo()));
            return false;
        } catch (Exception $e) {
            error_log("AppSetting update exception: " . $e->getMessage());
            return false;
        }
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE setting_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->setting_id = htmlspecialchars(strip_tags($this->setting_id));
        $stmt->bindParam(1, $this->setting_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function ensureDefaultExists() {
        // Check if a record with setting_id = 1 exists
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE setting_id = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] == 0) {
            // Create default record (include logo field since it still exists in database)
            $query = "INSERT INTO " . $this->table_name . " (setting_id, app_name, address, contact_number, email, about, logo) VALUES (1, 'Default App Name', '', '', '', '', 'default.png')";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute();
        }
        return true;
    }
}
