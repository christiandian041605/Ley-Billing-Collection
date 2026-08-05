<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

function formatCurrency($amount)
{
    return '₱' . number_format($amount, 2);
}

function getStatusBadge($status)
{
    switch ($status) {
        case 'Paid':
            return '<span class="badge bg-success">Paid</span>';
        case 'Partially Paid':
            return '<span class="badge bg-warning text-dark">Partially Paid</span>';
        case 'Unpaid':
            return '<span class="badge bg-danger">Unpaid</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Get filter parameters
    $customer_id = isset($_GET['customer_id']) && $_GET['customer_id'] !== '' ? intval($_GET['customer_id']) : null;
    $date_from = isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null;
    $date_to = isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $_GET['date_to'] : null;
    $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
    $customer_type = isset($_GET['customer_type']) && $_GET['customer_type'] !== '' ? $_GET['customer_type'] : null;

    // Get app settings
    $app_query = "SELECT app_name, address FROM tbl_app_setting LIMIT 1";
    $app_stmt = $db->prepare($app_query);
    $app_stmt->execute();
    $app_settings = $app_stmt->fetch(PDO::FETCH_ASSOC);

    // Get customer info if specific customer selected
    $customer_info = null;
    if ($customer_id) {
        $customer_query = "SELECT id, name, business_name, customer_type, address, contact_number, email 
                          FROM tbl_customers WHERE id = :customer_id";
        $customer_stmt = $db->prepare($customer_query);
        $customer_stmt->bindParam(':customer_id', $customer_id);
        $customer_stmt->execute();
        $customer_info = $customer_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer_info) {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            exit();
        }
    } else {
        $customer_info = [
            'id' => null,
            'name' => 'All Customers',
            'business_name' => '',
            'customer_type' => $customer_type ?: 'All',
            'address' => '',
            'contact_number' => '',
            'email' => ''
        ];
    }

    // Determine which tables to query based on customer type
    // If a specific customer is selected, use their type
    // If not, check if a customer type filter is applied
    // If neither, we might need to fetch both (UNION)

    $target_type = null;
    if ($customer_info['id']) {
        $target_type = $customer_info['customer_type'];
    } elseif ($customer_type) {
        $target_type = $customer_type;
    }

    $invoices = [];
    $total_invoiced = 0;
    $total_withholding = 0;
    $total_paid = 0;
    $net_amount_total = 0;

    // Helper to build query
    function buildQuery($type, $customer_id, $date_from, $date_to, $status)
    {
        if ($type === 'Government') {
            $query = "SELECT 
                        ci.id,
                        ci.invoice_no,
                        ci.invoice_date,
                        ci.total_amount,
                        (ci.withholding_tax_5 + ci.withholding_tax_1) as withholding,
                        ci.net_amount,
                        ci.payment_status,
                        c.name as customer_name,
                        c.customer_type,
                        COALESCE(SUM(oi.applied_amount), 0) as total_paid
                      FROM tbl_charge_invoices ci
                      LEFT JOIN tbl_customers c ON ci.customer_id = c.id
                      LEFT JOIN tbl_or_invoice oi ON ci.id = oi.charge_invoice_id
                      WHERE 1=1";
            if ($customer_id)
                $query .= " AND ci.customer_id = :customer_id";
        } else {
            // Private/Business uses Deliveries
            $query = "SELECT 
                        d.id,
                        d.delivery_no as invoice_no,
                        d.delivery_date as invoice_date,
                        d.total_amount,
                        0 as withholding,
                        d.total_amount as net_amount,
                        d.payment_status,
                        c.name as customer_name,
                        c.customer_type,
                        COALESCE(SUM(od.applied_amount), 0) as total_paid
                      FROM tbl_deliveries d
                      LEFT JOIN tbl_customers c ON d.customer_id = c.id
                      LEFT JOIN tbl_or_delivery od ON d.id = od.delivery_id
                      WHERE d.delivery_type = 'DR V'"; // Only DR V are sales/receivables
            if ($customer_id)
                $query .= " AND d.customer_id = :customer_id";
        }

        if ($date_from) {
            $col = ($type === 'Government') ? 'ci.invoice_date' : 'd.delivery_date';
            $query .= " AND $col >= :date_from";
        }
        if ($date_to) {
            $col = ($type === 'Government') ? 'ci.invoice_date' : 'd.delivery_date';
            $query .= " AND $col <= :date_to";
        }
        if ($status) {
            $col = ($type === 'Government') ? 'ci.payment_status' : 'd.payment_status';
            $query .= " AND $col = :status";
        }

        $groupCol = ($type === 'Government') ? 'ci.id' : 'd.id';
        $orderCol = ($type === 'Government') ? 'ci.invoice_date' : 'd.delivery_date';
        $query .= " GROUP BY $groupCol ORDER BY $orderCol DESC";

        return $query;
    }

    $queries = [];
    if ($target_type === 'Government') {
        $queries[] = ['type' => 'Government', 'sql' => buildQuery('Government', $customer_id, $date_from, $date_to, $status)];
    } elseif ($target_type === 'Private' || $target_type === 'Business') {
        $queries[] = ['type' => 'Private', 'sql' => buildQuery('Private', $customer_id, $date_from, $date_to, $status)];
    } else {
        // All types
        $queries[] = ['type' => 'Government', 'sql' => buildQuery('Government', null, $date_from, $date_to, $status)];
        $queries[] = ['type' => 'Private', 'sql' => buildQuery('Private', null, $date_from, $date_to, $status)];
    }

    foreach ($queries as $q) {
        $stmt = $db->prepare($q['sql']);
        if ($customer_id)
            $stmt->bindValue(':customer_id', $customer_id);
        if ($date_from)
            $stmt->bindValue(':date_from', $date_from);
        if ($date_to)
            $stmt->bindValue(':date_to', $date_to);
        if ($status)
            $stmt->bindValue(':status', $status);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $balance = $row['net_amount'] - $row['total_paid'];

            $invoices[] = [
                'invoice_no' => $row['invoice_no'],
                'invoice_date' => date('M d, Y', strtotime($row['invoice_date'])),
                'customer_name' => $row['customer_name'] ?? '',
                'customer_type' => $row['customer_type'] ?? '',
                'total_amount' => formatCurrency($row['total_amount']),
                'withholding' => formatCurrency($row['withholding']),
                'net_amount' => formatCurrency($row['net_amount']),
                'paid' => formatCurrency($row['total_paid']),
                'balance' => formatCurrency($balance),
                'status' => $row['payment_status'],
                'status_badge' => getStatusBadge($row['payment_status'])
            ];

            $total_invoiced += $row['total_amount'];
            $total_withholding += $row['withholding'];
            $total_paid += $row['total_paid'];
            $net_amount_total += $row['net_amount'];
        }
    }

    // Sort combined results by date if mixed
    usort($invoices, function ($a, $b) {
        return strtotime($b['invoice_date']) - strtotime($a['invoice_date']);
    });

    // Get payment history
    // Logic: If Gov -> tbl_or_invoice. If Private -> tbl_or_delivery.

    $payment_queries = [];

    // Government Payments
    $gov_pay_sql = "SELECT 
                        o.or_no,
                        o.or_date,
                        o.amount_received,
                        o.payment_type,
                        o.cheque_no,
                        GROUP_CONCAT(ci.invoice_no SEPARATOR ', ') as applied_invoices,
                        c.customer_type
                      FROM tbl_official_receipts o
                      LEFT JOIN tbl_customers c ON o.customer_id = c.id
                      INNER JOIN tbl_or_invoice oi ON o.id = oi.or_id
                      INNER JOIN tbl_charge_invoices ci ON oi.charge_invoice_id = ci.id
                      WHERE 1=1";

    // Private Payments
    $priv_pay_sql = "SELECT 
                        o.or_no,
                        o.or_date,
                        o.amount_received,
                        o.payment_type,
                        o.cheque_no,
                        GROUP_CONCAT(d.delivery_no SEPARATOR ', ') as applied_invoices,
                        c.customer_type
                      FROM tbl_official_receipts o
                      LEFT JOIN tbl_customers c ON o.customer_id = c.id
                      INNER JOIN tbl_or_delivery od ON o.id = od.or_id
                      INNER JOIN tbl_deliveries d ON od.delivery_id = d.id
                      WHERE 1=1";

    if ($customer_id) {
        $gov_pay_sql .= " AND o.customer_id = :customer_id";
        $priv_pay_sql .= " AND o.customer_id = :customer_id";
    }
    if ($date_from) {
        $gov_pay_sql .= " AND o.or_date >= :date_from";
        $priv_pay_sql .= " AND o.or_date >= :date_from";
    }
    if ($date_to) {
        $gov_pay_sql .= " AND o.or_date <= :date_to";
        $priv_pay_sql .= " AND o.or_date <= :date_to";
    }

    $gov_pay_sql .= " GROUP BY o.id";
    $priv_pay_sql .= " GROUP BY o.id";

    $payment_sqls = [];
    if ($target_type === 'Government') {
        $payment_sqls[] = $gov_pay_sql;
    } elseif ($target_type === 'Private' || $target_type === 'Business') {
        $payment_sqls[] = $priv_pay_sql;
    } else {
        $payment_sqls[] = $gov_pay_sql;
        $payment_sqls[] = $priv_pay_sql;
    }

    $payments = [];
    foreach ($payment_sqls as $sql) {
        $stmt = $db->prepare($sql);
        if ($customer_id)
            $stmt->bindValue(':customer_id', $customer_id);
        if ($date_from)
            $stmt->bindValue(':date_from', $date_from);
        if ($date_to)
            $stmt->bindValue(':date_to', $date_to);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $payment_method = '';
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

            // Avoid duplicates if mixed (rare but possible if logic overlaps, though tables are disjoint mostly)
            $payments[] = [
                'or_no' => $row['or_no'],
                'or_date' => date('M d, Y', strtotime($row['or_date'])),
                'customer_type' => $row['customer_type'] ?? '',
                'payment_method' => $payment_method,
                'amount' => formatCurrency($row['amount_received']),
                'applied_to' => $row['applied_invoices']
            ];
        }
    }

    // Sort payments
    usort($payments, function ($a, $b) {
        return strtotime($b['or_date']) - strtotime($a['or_date']);
    });

    $outstanding_balance = $net_amount_total - $total_paid;

    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'report_info' => [
                'company_name' => $app_settings['app_name'] ?? 'Ley Billing System',
                'company_address' => $app_settings['address'] ?? '',
                'date' => date('F d, Y'),
                'period_from' => $date_from ? date('M d, Y', strtotime($date_from)) : 'Beginning',
                'period_to' => $date_to ? date('M d, Y', strtotime($date_to)) : 'Current'
            ],
            'customer_info' => [
                'name' => $customer_info['name'],
                'business_name' => $customer_info['business_name'],
                'type' => $customer_info['customer_type'],
                'address' => $customer_info['address']
            ],
            'invoices' => $invoices,
            'payments' => $payments,
            'summary' => [
                'total_invoiced' => formatCurrency($total_invoiced),
                'total_withholding' => formatCurrency($total_withholding),
                'net_amount' => formatCurrency($net_amount_total),
                'total_paid' => formatCurrency($total_paid),
                'outstanding_balance' => formatCurrency($outstanding_balance)
            ]
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_soa_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while generating the report: ' . $e->getMessage()
    ]);
}
?>