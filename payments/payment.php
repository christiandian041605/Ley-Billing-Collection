<?php
class Payment
{
    private $conn;
    private $table_receipts = "tbl_official_receipts";
    private $table_or_invoice = "tbl_or_invoice";
    private $table_payment_history = "tbl_payment_history";
    private $table_invoices = "tbl_charge_invoices";
    private $table_deliveries = "tbl_deliveries";
    private $table_or_delivery = "tbl_or_delivery";
    private $table_customers = "tbl_customers";
    private $table_banks = "tbl_banks";

    public $id;
    public $or_no;
    public $or_date;
    public $customer_id;
    public $amount_received;
    public $payment_type;
    public $bank_id;
    public $cheque_no;
    public $cheque_date;
    public $cheque_name;
    public $check_amount;
    public $notes;
    public $created_by;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function isGovernmentCustomer($customer_id)
    {
        $query = "SELECT customer_type FROM " . $this->table_customers . " WHERE id = :customer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($customer && $customer['customer_type'] === 'Government');
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_receipts . " 
                 SET or_no = :or_no,
                     or_date = :or_date,
                     customer_id = :customer_id,
                     amount_received = :amount_received,
                     payment_type = :payment_type,
                     bank_id = :bank_id,
                     cheque_no = :cheque_no,
                     cheque_date = :cheque_date,
                     cheque_name = :cheque_name,
                     check_amount = :check_amount,
                     notes = :notes,
                     created_by = :created_by";

        $stmt = $this->conn->prepare($query);

        $this->or_no = htmlspecialchars(strip_tags($this->or_no));
        $this->or_date = htmlspecialchars(strip_tags($this->or_date));
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->amount_received = htmlspecialchars(strip_tags($this->amount_received));
        $this->payment_type = htmlspecialchars(strip_tags($this->payment_type));
        $this->bank_id = !empty($this->bank_id) ? htmlspecialchars(strip_tags($this->bank_id)) : null;
        $this->cheque_no = !empty($this->cheque_no) ? htmlspecialchars(strip_tags($this->cheque_no)) : null;
        $this->cheque_date = !empty($this->cheque_date) ? htmlspecialchars(strip_tags($this->cheque_date)) : null;
        $this->cheque_name = !empty($this->cheque_name) ? htmlspecialchars(strip_tags($this->cheque_name)) : null;
        $this->check_amount = !empty($this->check_amount) ? htmlspecialchars(strip_tags($this->check_amount)) : null;
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));

        $stmt->bindParam(":or_no", $this->or_no);
        $stmt->bindParam(":or_date", $this->or_date);
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":amount_received", $this->amount_received);
        $stmt->bindParam(":payment_type", $this->payment_type);
        $stmt->bindParam(":bank_id", $this->bank_id);
        $stmt->bindParam(":cheque_no", $this->cheque_no);
        $stmt->bindParam(":cheque_date", $this->cheque_date);
        $stmt->bindParam(":cheque_name", $this->cheque_name);
        $stmt->bindParam(":check_amount", $this->check_amount);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    public function getUnpaidInvoices($customer_id, $payment_id = null)
    {
        // First determine customer type
        $query = "SELECT customer_type FROM " . $this->table_customers . " WHERE id = :customer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer)
            return [];

        $payment_filter = "";
        if ($payment_id) {
            if ($customer['customer_type'] === 'Government') {
                $payment_filter = " OR ci.id IN (SELECT charge_invoice_id FROM " . $this->table_or_invoice . " WHERE or_id = :payment_id)";
            } else {
                $payment_filter = " OR d.id IN (SELECT delivery_id FROM " . $this->table_or_delivery . " WHERE or_id = :payment_id)";
            }
        }

        if ($customer['customer_type'] === 'Government') {
            // Retrieve Unpaid/Partially Paid Charge Invoices
            $query = "SELECT id, invoice_no, invoice_date, total_amount, net_amount, payment_status,
                             (CASE 
                                WHEN net_amount > 0 THEN net_amount 
                                ELSE total_amount 
                             END) as amount_due,
                             COALESCE((SELECT SUM(applied_amount) 
                                       FROM " . $this->table_or_invoice . " 
                                       WHERE charge_invoice_id = ci.id), 0) as amount_paid,
                             (CASE 
                                WHEN net_amount > 0 THEN net_amount 
                                ELSE total_amount 
                             END) as original_balance,
                             ((CASE WHEN net_amount > 0 THEN net_amount ELSE total_amount END) 
                             - COALESCE((SELECT SUM(applied_amount) 
                                         FROM " . $this->table_or_invoice . " 
                                         WHERE charge_invoice_id = ci.id), 0)) as current_balance
                      FROM " . $this->table_invoices . " ci
                      WHERE customer_id = :customer_id 
                        AND (payment_status != 'Paid' $payment_filter)
                      ORDER BY invoice_date ASC";
        } else {
            // Retrieve Unpaid/Partially Paid Deliveries (Private/Business)
            // Aliasing fields to match the 'invoice' structure expected by frontend
            $query = "SELECT id, delivery_no as invoice_no, delivery_date as invoice_date, total_amount, 
                             total_amount as net_amount, payment_status,
                             total_amount as amount_due,
                             COALESCE((SELECT SUM(applied_amount) 
                                       FROM " . $this->table_or_delivery . " 
                                       WHERE delivery_id = d.id), 0) as amount_paid,
                             total_amount as original_balance,
                             (total_amount - COALESCE((SELECT SUM(applied_amount) 
                                                        FROM " . $this->table_or_delivery . " 
                                                        WHERE delivery_id = d.id), 0)) as current_balance
                      FROM " . $this->table_deliveries . " d
                      WHERE customer_id = :customer_id 
                        AND (payment_status != 'Paid' $payment_filter)
                        AND delivery_type = 'DR V' 
                      ORDER BY delivery_date ASC";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $customer_id);
        if ($payment_id) {
            $stmt->bindParam(":payment_id", $payment_id);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function applyToInvoices($invoiceAllocations)
    {
        $this->conn->beginTransaction();

        try {
            // Determine customer type from the current OR
            $query = "SELECT customer_type FROM " . $this->table_customers . " WHERE id = :customer_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            $is_government = ($customer && $customer['customer_type'] === 'Government');

            foreach ($invoiceAllocations as $allocation) {
                if ($is_government) {
                    $query = "INSERT INTO " . $this->table_or_invoice . " 
                             SET or_id = :or_id,
                                 charge_invoice_id = :item_id,
                                 applied_amount = :applied_amount";
                } else {
                    $query = "INSERT INTO " . $this->table_or_delivery . " 
                             SET or_id = :or_id,
                                 delivery_id = :item_id,
                                 applied_amount = :applied_amount";
                }

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":or_id", $this->id);
                $stmt->bindParam(":item_id", $allocation['invoice_id']); // invoice_id here means delivery_id if private
                $stmt->bindParam(":applied_amount", $allocation['amount_applied']);
                $stmt->execute();

                $this->updatePaymentStatus($allocation['invoice_id'], $is_government);

                if ($is_government) {
                    $this->recordPaymentHistory($allocation['invoice_id'], $allocation['amount_applied']);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Payment allocation error: " . $e->getMessage());
            return false;
        }
    }

    public function recalculateInvoiceStatus($id, $is_government = true)
    {
        return $this->updatePaymentStatus($id, $is_government);
    }

    private function updatePaymentStatus($id, $is_government)
    {
        if ($is_government) {
            $query = "SELECT 
                        ci.total_amount,
                        ci.net_amount,
                        COALESCE(SUM(oi.applied_amount), 0) as total_paid
                      FROM " . $this->table_invoices . " ci
                      LEFT JOIN " . $this->table_or_invoice . " oi ON ci.id = oi.charge_invoice_id
                      WHERE ci.id = :id
                      GROUP BY ci.id";
            $table = $this->table_invoices;
        } else {
            $query = "SELECT 
                        d.total_amount,
                        d.total_amount as net_amount, 
                        COALESCE(SUM(od.applied_amount), 0) as total_paid
                      FROM " . $this->table_deliveries . " d
                      LEFT JOIN " . $this->table_or_delivery . " od ON d.id = od.delivery_id
                      WHERE d.id = :id
                      GROUP BY d.id";
            $table = $this->table_deliveries;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row)
            return;

        $amount_to_compare = ($is_government && $row['net_amount'] > 0) ? $row['net_amount'] : $row['total_amount'];
        $total_paid = $row['total_paid'];

        // Floating point comparison precaution
        if ($total_paid >= ($amount_to_compare - 0.01)) {
            $status = 'Paid';
        } elseif ($total_paid > 0) {
            $status = 'Partially Paid';
        } else {
            $status = 'Unpaid';
        }

        $update_query = "UPDATE " . $table . " SET payment_status = :status WHERE id = :id";
        $update_stmt = $this->conn->prepare($update_query);
        $update_stmt->bindParam(":status", $status);
        $update_stmt->bindParam(":id", $id);
        $update_stmt->execute();
    }

    private function recordPaymentHistory($invoice_id, $amount_applied)
    {
        // Only for Government (Charge Invoices) per current logic
        $query = "SELECT customer_id, customer_type, withholding_tax_5, withholding_tax_1, net_amount 
                  FROM " . $this->table_invoices . " ci
                  LEFT JOIN " . $this->table_customers . " c ON ci.customer_id = c.id
                  WHERE ci.id = :invoice_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":invoice_id", $invoice_id);
        $stmt->execute();
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($invoice) {
            $is_government = ($invoice['customer_type'] === 'Government') ? 1 : 0;
            $percent_five = $invoice['withholding_tax_5'] ?? 0;
            $percent_one = $invoice['withholding_tax_1'] ?? 0;
            $net_value = $invoice['net_amount'] ?? $amount_applied;

            $history_query = "INSERT INTO " . $this->table_payment_history . " 
                             SET or_id = :or_id,
                                 charge_invoice_id = :charge_invoice_id,
                                 amount_received = :amount_received,
                                 net_value = :net_value,
                                 percent_one = :percent_one,
                                 percent_five = :percent_five,
                                 is_government_payment = :is_government_payment,
                                 status = 'Active',
                                 created_by = :created_by";

            $history_stmt = $this->conn->prepare($history_query);
            $history_stmt->bindParam(":or_id", $this->id);
            $history_stmt->bindParam(":charge_invoice_id", $invoice_id);
            $history_stmt->bindParam(":amount_received", $amount_applied);
            $history_stmt->bindParam(":net_value", $net_value);
            $history_stmt->bindParam(":percent_one", $percent_one);
            $history_stmt->bindParam(":percent_five", $percent_five);
            $history_stmt->bindParam(":is_government_payment", $is_government);
            $history_stmt->bindParam(":created_by", $this->created_by);
            $history_stmt->execute();
        }
    }

    public function read($start = 0, $length = 10, $search = '', $order_column = 'ofrec.created_at', $order_dir = 'DESC', $category = 'all')
    {
        $query = "SELECT ofrec.id, ofrec.or_no, ofrec.or_date, ofrec.customer_id, ofrec.amount_received, 
                         ofrec.payment_type, ofrec.bank_id, ofrec.cheque_no, ofrec.cheque_date, 
                         ofrec.cheque_name, ofrec.check_amount, ofrec.notes, ofrec.created_by, ofrec.created_at,
                         c.name as customer_name, c.customer_type,
                         b.bank_name,
                         CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM " . $this->table_receipts . " ofrec
                  LEFT JOIN " . $this->table_customers . " c ON ofrec.customer_id = c.id
                  LEFT JOIN " . $this->table_banks . " b ON ofrec.bank_id = b.id
                  LEFT JOIN tbl_users u ON ofrec.created_by = u.id
                  WHERE 1=1";

        // Apply category filter
        if ($category === 'private') {
            $query .= " AND c.customer_type IN ('Private', 'Business')";
        } elseif ($category === 'government') {
            $query .= " AND c.customer_type = 'Government'";
        }

        if (!empty($search)) {
            $query .= " AND (ofrec.or_no LIKE :search 
                       OR c.name LIKE :search 
                       OR ofrec.payment_type LIKE :search 
                       OR ofrec.cheque_no LIKE :search
                       OR ofrec.cheque_name LIKE :search)";
        }

        $allowed_columns = ['ofrec.id', 'ofrec.or_no', 'ofrec.or_date', 'c.name', 'ofrec.amount_received', 'ofrec.payment_type', 'ofrec.created_at'];
        if (!in_array($order_column, $allowed_columns)) {
            $order_column = 'ofrec.created_at';
        }
        
        $order_dir = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY " . $order_column . " " . $order_dir;
        $query .= " LIMIT :start, :length";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search", $search_param);
        }

        $stmt->bindParam(":start", $start, PDO::PARAM_INT);
        $stmt->bindParam(":length", $length, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    public function countAll($search = '', $category = 'all')
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_receipts . " ofrec
                  LEFT JOIN " . $this->table_customers . " c ON ofrec.customer_id = c.id
                  WHERE 1=1";

        // Apply category filter
        if ($category === 'private') {
            $query .= " AND c.customer_type IN ('Private', 'Business')";
        } elseif ($category === 'government') {
            $query .= " AND c.customer_type = 'Government'";
        }

        if (!empty($search)) {
            $query .= " AND (ofrec.or_no LIKE :search 
                       OR c.name LIKE :search 
                       OR ofrec.payment_type LIKE :search 
                       OR ofrec.cheque_no LIKE :search
                       OR ofrec.cheque_name LIKE :search)";
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

    public function readOne()
    {
        $query = "SELECT ofrec.*, c.name as customer_name, c.customer_type,
                         b.bank_name,
                         CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM " . $this->table_receipts . " ofrec
                  LEFT JOIN " . $this->table_customers . " c ON ofrec.customer_id = c.id
                  LEFT JOIN " . $this->table_banks . " b ON ofrec.bank_id = b.id
                  LEFT JOIN tbl_users u ON ofrec.created_by = u.id
                  WHERE ofrec.id = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->or_no = $row['or_no'];
            $this->or_date = $row['or_date'];
            $this->customer_id = $row['customer_id'];
            $this->amount_received = $row['amount_received'];
            $this->payment_type = $row['payment_type'];
            $this->bank_id = $row['bank_id'];
            $this->cheque_no = $row['cheque_no'];
            $this->cheque_date = $row['cheque_date'];
            $this->cheque_name = $row['cheque_name'];
            $this->check_amount = $row['check_amount'];
            $this->notes = $row['notes'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            return $row;
        }

        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_receipts . " 
                 SET or_no = :or_no,
                     or_date = :or_date,
                     customer_id = :customer_id,
                     amount_received = :amount_received,
                     payment_type = :payment_type,
                     bank_id = :bank_id,
                     cheque_no = :cheque_no,
                     cheque_date = :cheque_date,
                     cheque_name = :cheque_name,
                     check_amount = :check_amount,
                     notes = :notes
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->or_no = htmlspecialchars(strip_tags($this->or_no));
        $this->or_date = htmlspecialchars(strip_tags($this->or_date));
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->amount_received = htmlspecialchars(strip_tags($this->amount_received));
        $this->payment_type = htmlspecialchars(strip_tags($this->payment_type));
        $this->bank_id = !empty($this->bank_id) ? htmlspecialchars(strip_tags($this->bank_id)) : null;
        $this->cheque_no = !empty($this->cheque_no) ? htmlspecialchars(strip_tags($this->cheque_no)) : null;
        $this->cheque_date = !empty($this->cheque_date) ? htmlspecialchars(strip_tags($this->cheque_date)) : null;
        $this->cheque_name = !empty($this->cheque_name) ? htmlspecialchars(strip_tags($this->cheque_name)) : null;
        $this->check_amount = !empty($this->check_amount) ? htmlspecialchars(strip_tags($this->check_amount)) : null;
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":or_no", $this->or_no);
        $stmt->bindParam(":or_date", $this->or_date);
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":amount_received", $this->amount_received);
        $stmt->bindParam(":payment_type", $this->payment_type);
        $stmt->bindParam(":bank_id", $this->bank_id);
        $stmt->bindParam(":cheque_no", $this->cheque_no);
        $stmt->bindParam(":cheque_date", $this->cheque_date);
        $stmt->bindParam(":cheque_name", $this->cheque_name);
        $stmt->bindParam(":check_amount", $this->check_amount);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete()
    {
        $this->conn->beginTransaction();

        try {
            // Check customer type to know which table to delete from
            $query = "SELECT c.customer_type 
                      FROM " . $this->table_receipts . " r
                      JOIN " . $this->table_customers . " c ON r.customer_id = c.id
                      WHERE r.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $allocation_table = ($row && $row['customer_type'] === 'Government') ? $this->table_or_invoice : $this->table_or_delivery;

            $query = "DELETE FROM " . $allocation_table . " WHERE or_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();

            $query = "DELETE FROM " . $this->table_receipts . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function isDuplicate($field, $value, $id = null)
    {
        $query = "SELECT id FROM " . $this->table_receipts . " WHERE " . $field . " = :value";
        if ($id) {
            $query .= " AND id != :id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":value", $value);
        if ($id) {
            $stmt->bindParam(":id", $id);
        }

        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getAllBanks()
    {
        $query = "SELECT id, bank_name, branch as branch_name 
                  FROM " . $this->table_banks . " 
                  ORDER BY bank_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaymentAllocations($or_id)
    {
        // Determine if it was allocated to invoices or deliveries
        // We can check the OR's customer type first
        $query = "SELECT c.customer_type 
                  FROM " . $this->table_receipts . " r
                  JOIN " . $this->table_customers . " c ON r.customer_id = c.id
                  WHERE r.id = :or_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":or_id", $or_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['customer_type'] === 'Government') {
            $query = "SELECT oi.*, ci.invoice_no, ci.total_amount, ci.net_amount,
                             oi.applied_amount as amount_applied,
                             ci.id as invoice_id,
                             (CASE WHEN ci.net_amount > 0 THEN ci.net_amount ELSE ci.total_amount END) 
                             - COALESCE((SELECT SUM(applied_amount) FROM " . $this->table_or_invoice . " WHERE charge_invoice_id = ci.id), 0) as balance
                      FROM " . $this->table_or_invoice . " oi
                      JOIN " . $this->table_invoices . " ci ON oi.charge_invoice_id = ci.id
                      WHERE oi.or_id = :or_id
                      ORDER BY oi.id ASC";
        } else {
            $query = "SELECT od.*, d.delivery_no as invoice_no, d.total_amount, 
                             d.total_amount as net_amount,
                             od.applied_amount as amount_applied,
                             d.id as invoice_id,
                             d.total_amount - COALESCE((SELECT SUM(applied_amount) FROM " . $this->table_or_delivery . " WHERE delivery_id = d.id), 0) as balance
                      FROM " . $this->table_or_delivery . " od
                      JOIN " . $this->table_deliveries . " d ON od.delivery_id = d.id
                      WHERE od.or_id = :or_id
                      ORDER BY od.id ASC";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":or_id", $or_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function deleteAllocations($or_id)
    {
        // Determine table based on customer type of the OR
        $query = "SELECT c.customer_type 
                  FROM " . $this->table_receipts . " r
                  JOIN " . $this->table_customers . " c ON r.customer_id = c.id
                  WHERE r.id = :or_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":or_id", $or_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $table = ($row && $row['customer_type'] === 'Government') ? $this->table_or_invoice : $this->table_or_delivery;

        $query = "DELETE FROM " . $table . " WHERE or_id = :or_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":or_id", $or_id);
        return $stmt->execute();
    }

    public function generateOrNo()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "OR-{$year}{$month}-";

        $query = "SELECT or_no FROM " . $this->table_receipts . " 
                  WHERE or_no LIKE :prefix 
                  ORDER BY or_no DESC LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $searchPrefix = $prefix . '%';
        $stmt->bindParam(':prefix', $searchPrefix);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastNo = intval(substr($row['or_no'], -4));
            $newNo = $lastNo + 1;
        } else {
            $newNo = 1;
        }

        return $prefix . str_pad($newNo, 4, '0', STR_PAD_LEFT);
    }
}