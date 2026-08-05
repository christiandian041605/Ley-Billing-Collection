<?php
class Delivery
{
    private $conn;
    private $table_name = "tbl_deliveries";
    private $invoice_delivery_table = "tbl_invoice_deliveries";

    public $id;
    public $delivery_no;
    public $delivery_date;
    public $customer_id;
    public $delivery_type;
    public $address;
    public $checker;
    public $driver;
    public $plate_number;
    public $total_amount;
    public $created_by;
    public $created_at;
    public $payment_status;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET delivery_no = :delivery_no,
                     delivery_date = :delivery_date,
                     customer_id = :customer_id,
                     delivery_type = :delivery_type,
                     address = :address,
                     checker = :checker,
                     driver = :driver,
                     plate_number = :plate_number,
                     total_amount = :total_amount,
                     created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        $this->delivery_no = htmlspecialchars(strip_tags($this->delivery_no));
        $this->delivery_date = htmlspecialchars(strip_tags($this->delivery_date));
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->delivery_type = htmlspecialchars(strip_tags($this->delivery_type));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->checker = htmlspecialchars(strip_tags($this->checker));
        $this->driver = htmlspecialchars(strip_tags($this->driver));
        $this->plate_number = htmlspecialchars(strip_tags($this->plate_number));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));

        $stmt->bindParam(":delivery_no", $this->delivery_no);
        $stmt->bindParam(":delivery_date", $this->delivery_date);
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":delivery_type", $this->delivery_type);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":checker", $this->checker);
        $stmt->bindParam(":driver", $this->driver);
        $stmt->bindParam(":plate_number", $this->plate_number);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function linkInvoices($invoice_ids)
    {
        if (empty($invoice_ids) || !is_array($invoice_ids)) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $deleteQuery = "DELETE FROM " . $this->invoice_delivery_table . " WHERE delivery_id = :delivery_id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(":delivery_id", $this->id);
            $deleteStmt->execute();

            $insertQuery = "INSERT INTO " . $this->invoice_delivery_table . " 
                           (charge_invoice_id, delivery_id) VALUES (:invoice_id, :delivery_id)";
            $insertStmt = $this->conn->prepare($insertQuery);

            foreach ($invoice_ids as $invoice_id) {
                $insertStmt->bindParam(":invoice_id", $invoice_id);
                $insertStmt->bindParam(":delivery_id", $this->id);
                $insertStmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Link invoices error: " . $e->getMessage());
            return false;
        }
    }

    public function read($start = 0, $length = 10, $search = '', $order_column = 'created_at', $order_dir = 'DESC', $delivery_type = 'all')
    {
        $query = "SELECT d.id, d.delivery_no, d.delivery_date, d.customer_id, d.delivery_type, 
                         d.address, d.checker, d.driver, d.plate_number, d.total_amount, d.created_at, 
                         CASE 
                            WHEN d.delivery_type = 'DR Government' THEN COALESCE(MAX(ci.payment_status), 'Unpaid')
                            ELSE MAX(d.payment_status) 
                         END as payment_status,
                         c.name as customer_name, c.customer_type,
                         u.username as created_by_name,
                         GROUP_CONCAT(DISTINCT ci.invoice_no SEPARATOR ', ') as invoice_numbers
                 FROM " . $this->table_name . " d
                 LEFT JOIN tbl_customers c ON d.customer_id = c.id
                 LEFT JOIN tbl_users u ON d.created_by = u.id
                 LEFT JOIN " . $this->invoice_delivery_table . " id ON d.id = id.delivery_id
                 LEFT JOIN tbl_charge_invoices ci ON id.charge_invoice_id = ci.id";

        $where_conditions = [];

        // Filter by delivery type
        if ($delivery_type === 'dr_v') {
            $where_conditions[] = "d.delivery_type = 'DR V'";
        } elseif ($delivery_type === 'dr_government') {
            $where_conditions[] = "d.delivery_type = 'DR Government'";
        }

        // Search filter
        if (!empty($search)) {
            $where_conditions[] = "(d.delivery_no LIKE :search1 
                       OR c.name LIKE :search2 
                       OR d.delivery_type LIKE :search3 
                       OR d.address LIKE :search4
                       OR d.checker LIKE :search5
                       OR d.driver LIKE :search6
                       OR d.plate_number LIKE :search7)";
        }

        if (!empty($where_conditions)) {
            $query .= " WHERE " . implode(' AND ', $where_conditions);
        }

        $query .= " GROUP BY d.id";

        $allowed_columns = ['id', 'delivery_no', 'delivery_date', 'customer_name', 'delivery_type', 'total_amount', 'created_at'];
        if (!in_array($order_column, $allowed_columns)) {
            $order_column = 'created_at';
        }

        $column_map = ['customer_name' => 'c.name'];
        $db_column = $column_map[$order_column] ?? 'd.' . $order_column;

        $order_dir = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY " . $db_column . " " . $order_dir;
        $query .= " LIMIT :start, :length";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search1", $search_param);
            $stmt->bindParam(":search2", $search_param);
            $stmt->bindParam(":search3", $search_param);
            $stmt->bindParam(":search4", $search_param);
            $stmt->bindParam(":search5", $search_param);
            $stmt->bindParam(":search6", $search_param);
            $stmt->bindParam(":search7", $search_param);
        }

        $stmt->bindParam(":start", $start, PDO::PARAM_INT);
        $stmt->bindParam(":length", $length, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    public function countAll($search = '', $delivery_type = 'all')
    {
        $query = "SELECT COUNT(DISTINCT d.id) as total 
                 FROM " . $this->table_name . " d
                 LEFT JOIN tbl_customers c ON d.customer_id = c.id";

        $where_conditions = [];

        // Filter by delivery type
        if ($delivery_type === 'dr_v') {
            $where_conditions[] = "d.delivery_type = 'DR V'";
        } elseif ($delivery_type === 'dr_government') {
            $where_conditions[] = "d.delivery_type = 'DR Government'";
        }

        // Search filter
        if (!empty($search)) {
            $where_conditions[] = "(d.delivery_no LIKE :search1 
                       OR c.name LIKE :search2 
                       OR d.delivery_type LIKE :search3 
                       OR d.address LIKE :search4
                       OR d.checker LIKE :search5
                       OR d.driver LIKE :search6
                       OR d.plate_number LIKE :search7)";
        }

        if (!empty($where_conditions)) {
            $query .= " WHERE " . implode(' AND ', $where_conditions);
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search1", $search_param);
            $stmt->bindParam(":search2", $search_param);
            $stmt->bindParam(":search3", $search_param);
            $stmt->bindParam(":search4", $search_param);
            $stmt->bindParam(":search5", $search_param);
            $stmt->bindParam(":search6", $search_param);
            $stmt->bindParam(":search7", $search_param);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function readOne()
    {
        $query = "SELECT d.*, c.name as customer_name, c.customer_type, c.address as customer_address,
                         u.username as created_by_name
                 FROM " . $this->table_name . " d
                 LEFT JOIN tbl_customers c ON d.customer_id = c.id
                 LEFT JOIN tbl_users u ON d.created_by = u.id
                 WHERE d.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->delivery_no = $row['delivery_no'];
            $this->delivery_date = $row['delivery_date'];
            $this->customer_id = $row['customer_id'];
            $this->delivery_type = $row['delivery_type'];
            $this->address = $row['address'];
            $this->checker = $row['checker'];
            $this->driver = $row['driver'];
            $this->plate_number = $row['plate_number'];
            $this->total_amount = $row['total_amount'];
            $this->payment_status = $row['payment_status'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    public function getLinkedInvoices()
    {
        $query = "SELECT ci.id, ci.invoice_no, ci.invoice_date, ci.total_amount, ci.payment_status
                 FROM tbl_charge_invoices ci
                 INNER JOIN " . $this->invoice_delivery_table . " id ON ci.id = id.charge_invoice_id
                 WHERE id.delivery_id = :delivery_id
                 ORDER BY ci.invoice_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":delivery_id", $this->id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                 SET delivery_no = :delivery_no,
                     delivery_date = :delivery_date,
                     customer_id = :customer_id,
                     delivery_type = :delivery_type,
                     address = :address,
                     checker = :checker,
                     driver = :driver,
                     plate_number = :plate_number,
                     total_amount = :total_amount
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->delivery_no = htmlspecialchars(strip_tags($this->delivery_no));
        $this->delivery_date = htmlspecialchars(strip_tags($this->delivery_date));
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->delivery_type = htmlspecialchars(strip_tags($this->delivery_type));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->checker = htmlspecialchars(strip_tags($this->checker));
        $this->driver = htmlspecialchars(strip_tags($this->driver));
        $this->plate_number = htmlspecialchars(strip_tags($this->plate_number));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":delivery_no", $this->delivery_no);
        $stmt->bindParam(":delivery_date", $this->delivery_date);
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":delivery_type", $this->delivery_type);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":checker", $this->checker);
        $stmt->bindParam(":driver", $this->driver);
        $stmt->bindParam(":plate_number", $this->plate_number);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete()
    {
        try {
            $this->conn->beginTransaction();

            $query1 = "DELETE FROM " . $this->invoice_delivery_table . " WHERE delivery_id = :id";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(":id", $this->id);
            $stmt1->execute();

            $query2 = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt2 = $this->conn->prepare($query2);
            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt2->bindParam(":id", $this->id);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Delete delivery error: " . $e->getMessage());
            return false;
        }
    }

    public function deliveryNoExists($exclude_id = null)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE delivery_no = :delivery_no";

        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":delivery_no", $this->delivery_no);

        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function generateDeliveryNo()
    {
        $prefix = "DR-" . date('Y') . "-";

        $query = "SELECT delivery_no FROM " . $this->table_name . " 
                 WHERE delivery_no LIKE :prefix 
                 ORDER BY id DESC LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $search_prefix = $prefix . "%";
        $stmt->bindParam(":prefix", $search_prefix);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $last_no = $row['delivery_no'];
            $number = (int) substr($last_no, -4);
            $new_number = $number + 1;
        } else {
            $new_number = 1;
        }

        return $prefix . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    public function getAvailableInvoices($customer_id)
    {
        $query = "SELECT id, invoice_no, invoice_date, total_amount 
                 FROM tbl_charge_invoices 
                 WHERE customer_id = :customer_id 
                 ORDER BY invoice_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatistics()
    {
        $stats = [];

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total'] = $row['total'];

        $query = "SELECT delivery_type, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 GROUP BY delivery_type";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $stats['by_type'] = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['by_type'][$row['delivery_type']] = $row['count'];
        }

        $query = "SELECT COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 WHERE MONTH(delivery_date) = MONTH(CURDATE()) 
                 AND YEAR(delivery_date) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_month'] = $row['count'];

        $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                 FROM " . $this->table_name . " 
                 WHERE MONTH(delivery_date) = MONTH(CURDATE()) 
                 AND YEAR(delivery_date) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['amount_this_month'] = $row['total'];

        return $stats;
    }
}
?>