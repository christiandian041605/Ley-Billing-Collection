<?php
class Customer
{
    private $conn;
    private $table_name = "tbl_customers";

    public $id;
    public $customer_type;
    public $name;
    public $business_name;
    public $address;
    public $contact_number;
    public $email;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create customer
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET customer_type = :customer_type,
                     name = :name,
                     business_name = :business_name,
                     address = :address,
                     contact_number = :contact_number,
                     email = :email";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->customer_type = htmlspecialchars(strip_tags($this->customer_type));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->business_name = htmlspecialchars(strip_tags($this->business_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->contact_number = htmlspecialchars(strip_tags($this->contact_number));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(":customer_type", $this->customer_type);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":business_name", $this->business_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":contact_number", $this->contact_number);
        $stmt->bindParam(":email", $this->email);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read all customers with pagination and search
    public function read($start = 0, $length = 10, $search = '', $order_column = 'created_at', $order_dir = 'DESC')
    {
        // Base query
        $query = "SELECT id, customer_type, name, business_name, address, contact_number, email, created_at 
                 FROM " . $this->table_name;

        // Add search condition
        if (!empty($search)) {
            $query .= " WHERE name LIKE :search 
                       OR business_name LIKE :search 
                       OR customer_type LIKE :search 
                       OR contact_number LIKE :search 
                       OR email LIKE :search 
                       OR address LIKE :search";
        }

        // Add order
        $allowed_columns = ['id', 'customer_type', 'name', 'business_name', 'contact_number', 'email', 'created_at'];
        if (!in_array($order_column, $allowed_columns)) {
            $order_column = 'created_at';
        }
        
        $order_dir = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY " . $order_column . " " . $order_dir;

        // Add limit
        $query .= " LIMIT :start, :length";

        $stmt = $this->conn->prepare($query);

        // Bind search parameter if exists
        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search", $search_param);
        }

        // Bind pagination parameters
        $stmt->bindParam(":start", $start, PDO::PARAM_INT);
        $stmt->bindParam(":length", $length, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    // Count total customers for pagination
    public function countAll($search = '')
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;

        if (!empty($search)) {
            $query .= " WHERE name LIKE :search 
                       OR business_name LIKE :search 
                       OR customer_type LIKE :search 
                       OR contact_number LIKE :search 
                       OR email LIKE :search 
                       OR address LIKE :search";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search", $search_param);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Read single customer
    public function readOne()
    {
        $query = "SELECT id, customer_type, name, business_name, address, contact_number, email, created_at 
                 FROM " . $this->table_name . " 
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->customer_type = $row['customer_type'];
            $this->name = $row['name'];
            $this->business_name = $row['business_name'];
            $this->address = $row['address'];
            $this->contact_number = $row['contact_number'];
            $this->email = $row['email'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Update customer
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                 SET customer_type = :customer_type,
                     name = :name,
                     business_name = :business_name,
                     address = :address,
                     contact_number = :contact_number,
                     email = :email
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->customer_type = htmlspecialchars(strip_tags($this->customer_type));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->business_name = htmlspecialchars(strip_tags($this->business_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->contact_number = htmlspecialchars(strip_tags($this->contact_number));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":customer_type", $this->customer_type);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":business_name", $this->business_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":contact_number", $this->contact_number);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete customer
    public function delete()
    {
        // Check if customer has related records
        if ($this->hasRelatedRecords()) {
            return false;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Check if customer has related records (invoices, deliveries, etc.)
    public function hasRelatedRecords()
    {
        $tables = [
            'tbl_charge_invoices',
            'tbl_deliveries',
            'tbl_official_receipts'
        ];

        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as count FROM " . $table . " WHERE customer_id = :customer_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $this->id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row['count'] > 0) {
                return true;
            }
        }

        return false;
    }

    // Check if customer name exists (for validation)
    public function nameExists($exclude_id = null)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = :name";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $this->name);
        
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }

    // Get customer for select dropdown
    public function getCustomersForSelect($type = null)
    {
        $query = "SELECT id, name, customer_type, business_name, address 
                 FROM " . $this->table_name;
        
        if ($type) {
            $query .= " WHERE customer_type = :type";
        }
        
        $query .= " ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        
        if ($type) {
            $stmt->bindParam(":type", $type);
        }

        $stmt->execute();
        return $stmt;
    }

    // Get customer statistics
    public function getStatistics()
    {
        $stats = [];

        // Total customers
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total'] = $row['total'];

        // Customers by type
        $query = "SELECT customer_type, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 GROUP BY customer_type";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $stats['by_type'] = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['by_type'][$row['customer_type']] = $row['count'];
        }

        // New customers this month
        $query = "SELECT COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 WHERE MONTH(created_at) = MONTH(CURDATE()) 
                 AND YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['new_this_month'] = $row['count'];

        return $stats;
    }
}
?>