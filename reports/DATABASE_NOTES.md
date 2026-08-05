# Database Schema Notes for Reports Module

## Actual Database Structure Used

### tbl_app_setting
**Actual columns:**
- `setting_id` - Primary key
- `app_name` - Application/Company name
- `address` - Company address
- `contact_number` - Contact number
- `email` - Email address
- `about` - About text
- `updated_at` - Last update timestamp
- `logo` - Logo filename

**Note:** Uses `app_name` and `address`, NOT `company_name` and `company_address`

---

### tbl_official_receipts
**Actual columns:**
- `id` - Primary key
- `or_no` - Official Receipt number
- `or_date` - Receipt date
- `customer_id` - Foreign key to tbl_customers
- `amount_received` - Payment amount
- `payment_type` - ENUM('Cash','Check','Bank Transfer','GCash')
- `bank_id` - Foreign key to tbl_banks (nullable)
- `cheque_no` - Check number (nullable)
- `cheque_date` - Check date (nullable)
- `notes` - Additional notes
- `created_by` - User who created the record
- `created_at` - Creation timestamp
- `cheque_name` - Check name for government transactions (nullable)
- `check_amount` - Check amount verification (nullable)
- `payment_details` - Additional payment details (nullable)

**Missing columns from System Blueprint v2.0:**
- `is_government_payment` - Not implemented yet
- `cash_type` - Not implemented yet
- `dr_v_reference_id` - Not implemented yet
- `government_cash_details` - Not implemented yet
- `withholding_tax_5_amount` - Not implemented yet
- `withholding_tax_1_amount` - Not implemented yet
- `gross_amount` - Not implemented yet
- `net_payment_amount` - Not implemented yet

**Current Implementation Status:**
The database schema is still in its original state and has NOT been migrated to Version 2.0 with the dual payment workflow enhancements described in System-Blueprint-v2.txt.

---

### tbl_charge_invoices
**Actual columns:**
- `id` - Primary key
- `invoice_no` - Invoice number
- `invoice_date` - Invoice date
- `customer_id` - Foreign key to tbl_customers
- `address` - Invoice address
- `total_amount` - Total invoice amount
- `payment_status` - ENUM('Unpaid','Partially Paid','Paid')
- `created_by` - User who created
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp
- `withholding_tax_5` - 5% withholding tax (Government)
- `withholding_tax_1` - 1% withholding tax (Government)
- `net_amount` - Amount after withholding deductions
- `is_government` - Flag for government transactions (TINYINT)

**Good news:** Invoice table has withholding tax columns implemented! ✅

---

## Report Module Queries - Current Implementation

### Query 1: Get App Settings
```sql
SELECT app_name, address 
FROM tbl_app_setting 
LIMIT 1
```

### Query 2: Get Customer Info
```sql
SELECT id, name, business_name, customer_type, address, contact_number, email 
FROM tbl_customers 
WHERE id = :customer_id
```

### Query 3: Get Invoices with Payments
```sql
SELECT 
    ci.id,
    ci.invoice_no,
    ci.invoice_date,
    ci.total_amount,
    ci.withholding_tax_5,
    ci.withholding_tax_1,
    ci.net_amount,
    ci.payment_status,
    c.customer_type,
    COALESCE(SUM(oi.applied_amount), 0) as total_paid
FROM tbl_charge_invoices ci
INNER JOIN tbl_customers c ON ci.customer_id = c.id
LEFT JOIN tbl_or_invoice oi ON ci.id = oi.charge_invoice_id
WHERE [filters]
GROUP BY ci.id 
ORDER BY ci.invoice_date DESC
```

### Query 4: Get Payment History
```sql
SELECT 
    o.or_no,
    o.or_date,
    o.amount_received,
    o.payment_type,
    o.cheque_no,
    o.bank_id,
    GROUP_CONCAT(ci.invoice_no SEPARATOR ', ') as applied_invoices
FROM tbl_official_receipts o
INNER JOIN tbl_or_invoice oi ON o.id = oi.or_id
INNER JOIN tbl_charge_invoices ci ON oi.charge_invoice_id = ci.id
WHERE [filters]
GROUP BY o.id 
ORDER BY o.or_date DESC
```

---

## Payment Method Display Logic

Since the database doesn't have the v2.0 payment categorization yet, we use the existing `payment_type` enum:

```php
switch ($row['payment_type']) {
    case 'Cash':
        $payment_method = 'Cash';
        break;
    case 'Check':
        $payment_method = 'Check' . ($row['cheque_no'] ? ' #' . $row['cheque_no'] : '');
        break;
    case 'Bank Transfer':
        $payment_method = 'Bank Transfer';
        break;
    case 'GCash':
        $payment_method = 'GCash';
        break;
    default:
        $payment_method = $row['payment_type'];
}
```

---

## Migration Needed (Future)

To fully implement System Blueprint v2.0, the following migration would be needed:

```sql
-- Add new columns to tbl_official_receipts
ALTER TABLE tbl_official_receipts
ADD COLUMN is_government_payment BOOLEAN DEFAULT FALSE AFTER amount_received,
ADD COLUMN cash_type ENUM('Private/Customer', 'DR V') DEFAULT 'Private/Customer' AFTER payment_type,
ADD COLUMN dr_v_reference_id INT NULL AFTER cash_type,
ADD COLUMN government_cash_details TEXT NULL AFTER dr_v_reference_id,
ADD COLUMN withholding_tax_5_amount DECIMAL(15,2) DEFAULT 0.00,
ADD COLUMN withholding_tax_1_amount DECIMAL(15,2) DEFAULT 0.00,
ADD COLUMN gross_amount DECIMAL(15,2) NULL,
ADD COLUMN net_payment_amount DECIMAL(15,2) NULL;

-- Update payment_type enum
ALTER TABLE tbl_official_receipts 
MODIFY COLUMN payment_type ENUM('Cash', 'Bank') DEFAULT 'Cash';

-- Add foreign key for DR V reference
ALTER TABLE tbl_official_receipts
ADD FOREIGN KEY (dr_v_reference_id) REFERENCES tbl_deliveries(id) ON DELETE SET NULL;
```

---

## Current SOA Report Features Working

✅ Customer filtering (specific or all)
✅ Date range filtering
✅ Invoice status filtering
✅ Customer type filtering
✅ Invoice summary with withholding tax (for Government customers)
✅ Payment history (using existing payment types)
✅ Outstanding balance calculation
✅ Print functionality
✅ CSV Export
✅ Responsive UI matching system design

---

**Last Updated:** December 10, 2025
**Status:** Working with current database schema
**Future Enhancement:** Implement v2.0 payment workflow after database migration
