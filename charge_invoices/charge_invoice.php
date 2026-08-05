<?php
class ChargeInvoice {
    private $db;
    private $table_name = "tbl_charge_invoices";
    private $deliveries_table = "tbl_deliveries";
    private $invoice_deliveries_table = "tbl_invoice_deliveries";
    
    public $id;
    public $invoice_no;
    public $invoice_date;
    public $customer_id;
    public $address;
    public $total_amount;
    public $payment_status;
    public $created_by;
    public $created_at;
    public $updated_at;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function create($data) {
        try {
            $this->db->beginTransaction();
            
            // Validate delivery IDs and calculate total amount
            $delivery_ids = $data['delivery_ids'] ?? [];
            if (empty($delivery_ids)) {
                throw new Exception("No deliveries selected.");
            }
            
            // Calculate total amount from selected deliveries
            $total_amount = $this->calculateTotalFromDeliveries($delivery_ids);
            
            $customer_id = intval($data['customer_id']);
            $withholding_tax_5 = 0.00;
            $withholding_tax_1 = 0.00;
            $net_amount = $total_amount;
            $is_government = 0;

            // Check if customer is government
            $customerQuery = "SELECT customer_type FROM tbl_customers WHERE id = :customer_id";
            $customerStmt = $this->db->prepare($customerQuery);
            $customerStmt->bindParam(':customer_id', $customer_id);
            $customerStmt->execute();
            $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

            if ($customer && $customer['customer_type'] === 'Government') {
                $is_government = 1;
                $withholding_tax_5 = $total_amount * 0.05; // 5% withholding tax
                $withholding_tax_1 = $total_amount * 0.01; // 1% withholding tax
                $net_amount = $total_amount - ($withholding_tax_5 + $withholding_tax_1);
            }
            
            $query = "INSERT INTO " . $this->table_name . " 
                      SET invoice_no=:invoice_no,
                          invoice_date=:invoice_date,
                          customer_id=:customer_id,
                          address=:address,
                          total_amount=:total_amount,
                          withholding_tax_5=:withholding_tax_5,
                          withholding_tax_1=:withholding_tax_1,
                          net_amount=:net_amount,
                          is_government=:is_government,
                          payment_status=:payment_status,
                          created_by=:created_by";
            
            $stmt = $this->db->prepare($query);
            
            $invoice_no = htmlspecialchars(strip_tags($data['invoice_no']));
            $invoice_date = htmlspecialchars(strip_tags($data['invoice_date']));
            $address = htmlspecialchars(strip_tags($data['address'] ?? ''));
            $payment_status = htmlspecialchars(strip_tags($data['payment_status'] ?? 'Unpaid'));
            $created_by = intval($data['created_by']);
            
            $stmt->bindParam(':invoice_no', $invoice_no);
            $stmt->bindParam(':invoice_date', $invoice_date);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':total_amount', $total_amount);
            $stmt->bindParam(':withholding_tax_5', $withholding_tax_5);
            $stmt->bindParam(':withholding_tax_1', $withholding_tax_1);
            $stmt->bindParam(':net_amount', $net_amount);
            $stmt->bindParam(':is_government', $is_government);
            $stmt->bindParam(':payment_status', $payment_status);
            $stmt->bindParam(':created_by', $created_by);
            
            if ($stmt->execute()) {
                $invoice_id = $this->db->lastInsertId();
                
                // Link deliveries
                $this->linkDeliveries($invoice_id, $delivery_ids);
                
                $this->db->commit();
                return ['success' => true, 'id' => $invoice_id];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create invoice'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Invoice create exception: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function calculateTotalFromDeliveries($delivery_ids) {
        if (empty($delivery_ids)) return 0.00;
        
        $placeholders = implode(',', array_fill(0, count($delivery_ids), '?'));
        $query = "SELECT SUM(total_amount) as total FROM " . $this->deliveries_table . " WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($query);
        $stmt->execute($delivery_ids);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return floatval($row['total'] ?? 0);
    }
    
    private function linkDeliveries($invoice_id, $delivery_ids) {
        $query = "INSERT INTO " . $this->invoice_deliveries_table . " 
                  (charge_invoice_id, delivery_id) VALUES (:invoice_id, :delivery_id)";
        $stmt = $this->db->prepare($query);
        
        foreach ($delivery_ids as $delivery_id) {
            $stmt->bindParam(':invoice_id', $invoice_id);
            $stmt->bindParam(':delivery_id', $delivery_id);
            $stmt->execute();
        }
    }
    
    public function getAll($params = []) {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = $params['search'] ?? '';
        $invoice_type = $params['invoice_type'] ?? 'government';
        
        $where = "WHERE 1=1";
        
        // Filter by customer type based on invoice type
        // Note: With new flow, Government invoices are the primary use of ChargeInvoice
        if ($invoice_type === 'dr_v') {
             // Technically "Charge Invoice" is now Exclusive to Government per spec, 
             // but if we support listing old ones or if logic allows:
            $where .= " AND c.customer_type IN ('Private', 'Business')";
        } else {
            $where .= " AND c.customer_type = 'Government'";
        }
        
        if (!empty($search)) {
            $where .= " AND (ci.invoice_no LIKE :search1 
                        OR c.name LIKE :search2 
                        OR ci.address LIKE :search3)";
        }
        
        $query = "SELECT ci.*, 
                         c.name as customer_name, 
                         c.customer_type,
                         c.business_name,
                         u.first_name, u.last_name
                  FROM " . $this->table_name . " ci
                  LEFT JOIN tbl_customers c ON ci.customer_id = c.id
                  LEFT JOIN tbl_users u ON ci.created_by = u.id
                  " . $where . "
                  ORDER BY ci.created_at DESC
                  LIMIT :start, :length";
        
        $stmt = $this->db->prepare($query);
        
        if (!empty($search)) {
            $searchParam = "%{$search}%";
            $stmt->bindParam(':search1', $searchParam);
            $stmt->bindParam(':search2', $searchParam);
            $stmt->bindParam(':search3', $searchParam);
        }
        
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':length', $length, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        
        $countQuery = "SELECT COUNT(*) as total 
                       FROM " . $this->table_name . " ci
                       LEFT JOIN tbl_customers c ON ci.customer_id = c.id
                       " . $where;
        
        $countStmt = $this->db->prepare($countQuery);
        if (!empty($search)) {
            $countStmt->bindParam(':search1', $searchParam);
            $countStmt->bindParam(':search2', $searchParam);
            $countStmt->bindParam(':search3', $searchParam);
        }
        $countStmt->execute();
        $totalFiltered = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $totalQuery = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $totalStmt = $this->db->query($totalQuery);
        $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'draw' => intval($draw),
            'recordsTotal' => intval($totalRecords),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data
        ];
    }
    
    public function getById($id) {
        $query = "SELECT ci.*, 
                         c.name as customer_name, 
                         c.customer_type,
                         c.address as customer_address
                  FROM " . $this->table_name . " ci
                  LEFT JOIN tbl_customers c ON ci.customer_id = c.id
                  WHERE ci.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($invoice) {
            $invoice['linked_deliveries'] = $this->getLinkedDeliveries($id);
        }
        
        return $invoice;
    }
    
    public function getLinkedDeliveries($invoice_id) {
        $query = "SELECT d.* 
                  FROM " . $this->deliveries_table . " d
                  INNER JOIN " . $this->invoice_deliveries_table . " id ON d.id = id.delivery_id
                  WHERE id.charge_invoice_id = :invoice_id
                  ORDER BY d.delivery_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':invoice_id', $invoice_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        try {
            $this->db->beginTransaction();
            
            // Validate delivery IDs and calculate total amount
            $delivery_ids = $data['delivery_ids'] ?? [];
            if (empty($delivery_ids)) {
                throw new Exception("No deliveries selected.");
            }
            
            $total_amount = $this->calculateTotalFromDeliveries($delivery_ids);
            
            $customer_id = intval($data['customer_id']);
            $withholding_tax_5 = 0.00;
            $withholding_tax_1 = 0.00;
            $net_amount = $total_amount;
            $is_government = 0;

            // Check if customer is government
            $customerQuery = "SELECT customer_type FROM tbl_customers WHERE id = :customer_id";
            $customerStmt = $this->db->prepare($customerQuery);
            $customerStmt->bindParam(':customer_id', $customer_id);
            $customerStmt->execute();
            $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

            if ($customer && $customer['customer_type'] === 'Government') {
                $is_government = 1;
                $withholding_tax_5 = $total_amount * 0.05; // 5% withholding tax
                $withholding_tax_1 = $total_amount * 0.01; // 1% withholding tax
                $net_amount = $total_amount - ($withholding_tax_5 + $withholding_tax_1);
            }
            
            $query = "UPDATE " . $this->table_name . " 
                      SET invoice_no=:invoice_no,
                          invoice_date=:invoice_date,
                          customer_id=:customer_id,
                          address=:address,
                          total_amount=:total_amount,
                          withholding_tax_5=:withholding_tax_5,
                          withholding_tax_1=:withholding_tax_1,
                          net_amount=:net_amount,
                          is_government=:is_government,
                          payment_status=:payment_status
                      WHERE id=:id";
            
            $stmt = $this->db->prepare($query);
            
            $invoice_no = htmlspecialchars(strip_tags($data['invoice_no']));
            $invoice_date = htmlspecialchars(strip_tags($data['invoice_date']));
            $address = htmlspecialchars(strip_tags($data['address'] ?? ''));
            $payment_status = htmlspecialchars(strip_tags($data['payment_status']));
            
            $stmt->bindParam(':invoice_no', $invoice_no);
            $stmt->bindParam(':invoice_date', $invoice_date);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':total_amount', $total_amount);
            $stmt->bindParam(':withholding_tax_5', $withholding_tax_5);
            $stmt->bindParam(':withholding_tax_1', $withholding_tax_1);
            $stmt->bindParam(':net_amount', $net_amount);
            $stmt->bindParam(':is_government', $is_government);
            $stmt->bindParam(':payment_status', $payment_status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // Remove existing links
                $deleteQuery = "DELETE FROM " . $this->invoice_deliveries_table . " WHERE charge_invoice_id = :invoice_id";
                $deleteStmt = $this->db->prepare($deleteQuery);
                $deleteStmt->bindParam(':invoice_id', $id);
                $deleteStmt->execute();
                
                // Add new links
                $this->linkDeliveries($id, $delivery_ids);
                
                $this->db->commit();
                return ['success' => true];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to update invoice'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Invoice update exception: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            $deleteLinks = "DELETE FROM " . $this->invoice_deliveries_table . " WHERE charge_invoice_id = :id";
            $stmtLinks = $this->db->prepare($deleteLinks);
            $stmtLinks->bindParam(':id', $id);
            $stmtLinks->execute();
            
            $deleteInvoice = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmtInvoice = $this->db->prepare($deleteInvoice);
            $stmtInvoice->bindParam(':id', $id);
            
            if ($stmtInvoice->execute()) {
                $this->db->commit();
                return ['success' => true];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to delete invoice'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Invoice delete exception: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function checkDuplicate($invoice_no, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE invoice_no = :invoice_no";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':invoice_no', $invoice_no);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    public function generateInvoiceNo() {
        $year = date('Y');
        $month = date('m');
        $prefix = "CI-{$year}{$month}-";
        
        $query = "SELECT invoice_no FROM " . $this->table_name . " 
                  WHERE invoice_no LIKE :prefix 
                  ORDER BY invoice_no DESC LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $searchPrefix = $prefix . '%';
        $stmt->bindParam(':prefix', $searchPrefix);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastNo = intval(substr($row['invoice_no'], -4));
            $newNo = $lastNo + 1;
        } else {
            $newNo = 1;
        }
        
        return $prefix . str_pad($newNo, 4, '0', STR_PAD_LEFT);
    }
    
    public function getDeliveriesForSelect($customer_id) {
        // Get deliveries that are NOT already linked to a charge invoice
        // Filter by customer_id and ensure delivery type is 'DR Government' (per spec)
        $query = "SELECT d.id, d.delivery_no, d.delivery_date, d.total_amount 
                  FROM " . $this->deliveries_table . " d
                  LEFT JOIN " . $this->invoice_deliveries_table . " id ON d.id = id.delivery_id
                  WHERE d.customer_id = :customer_id 
                  AND d.delivery_type = 'DR Government'
                  AND id.id IS NULL
                  ORDER BY d.delivery_date DESC";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}