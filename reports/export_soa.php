<?php
include_once __DIR__ . '/../config/session.php';
include_once "../config/database.php";

if (!isset($_SESSION['ley_billing_user_id'])) {
    header("Location: ../login/");
    exit();
}

function formatCurrency($amount) {
    return number_format($amount, 2);
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

    // Get customer info
    $customer_info = null;
    if ($customer_id) {
        $customer_query = "SELECT id, name, business_name, customer_type, address 
                          FROM tbl_customers WHERE id = :customer_id";
        $customer_stmt = $db->prepare($customer_query);
        $customer_stmt->bindParam(':customer_id', $customer_id);
        $customer_stmt->execute();
        $customer_info = $customer_stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $customer_info = [
            'id' => null,
            'name' => 'All Customers',
            'business_name' => '',
            'customer_type' => $customer_type ?: 'All',
            'address' => ''
        ];
    }

    // Determine target type
    $target_type = null;
    if ($customer_info['id']) {
        $target_type = $customer_info['customer_type'];
    } elseif ($customer_type) {
        $target_type = $customer_type;
    }

    // Set headers for CSV download
    $filename = "SOA_" . ($customer_info['name'] ? str_replace(' ', '_', $customer_info['name']) : 'All_Customers') . "_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

    // Header info
    fputcsv($output, ['STATEMENT OF ACCOUNTS']);
    fputcsv($output, [$app_settings['app_name'] ?? 'Ley Billing System']);
    fputcsv($output, ['']);
    fputcsv($output, ['Customer:', $customer_info['name']]);
    if ($customer_info['business_name']) {
        fputcsv($output, ['Business:', $customer_info['business_name']]);
    }
    fputcsv($output, ['Type:', $customer_info['customer_type']]);
    fputcsv($output, ['Report Date:', date('F d, Y')]);
    fputcsv($output, ['Period:', ($date_from ? date('M d, Y', strtotime($date_from)) : 'Beginning') . ' to ' . ($date_to ? date('M d, Y', strtotime($date_to)) : 'Current')]);
    fputcsv($output, ['']);

    // Invoice Summary Header
    fputcsv($output, ['INVOICE SUMMARY']);
    $headers = ['Invoice No.', 'Date', 'Amount'];
    if ($target_type === 'Government' || $target_type === null) {
        $headers[] = 'Withholding';
        $headers[] = 'Net Amount';
    }
    $headers = array_merge($headers, ['Paid', 'Balance', 'Status', 'Type']);
    fputcsv($output, $headers);

    // Build Queries
    $queries = [];

    // Government Query
    if ($target_type === 'Government' || $target_type === null) {
        $q = "SELECT 
                ci.invoice_no,
                ci.invoice_date,
                ci.total_amount,
                (ci.withholding_tax_5 + ci.withholding_tax_1) as withholding,
                ci.net_amount,
                ci.payment_status,
                'Government' as type,
                COALESCE(SUM(oi.applied_amount), 0) as total_paid
              FROM tbl_charge_invoices ci
              LEFT JOIN tbl_or_invoice oi ON ci.id = oi.charge_invoice_id
              WHERE 1=1";
        if ($customer_id) $q .= " AND ci.customer_id = :customer_id";
        if ($date_from) $q .= " AND ci.invoice_date >= :date_from";
        if ($date_to) $q .= " AND ci.invoice_date <= :date_to";
        if ($status) $q .= " AND ci.payment_status = :status";
        $q .= " GROUP BY ci.id";
        $queries[] = $q;
    }

    // Private Query
    if ($target_type === 'Private' || $target_type === 'Business' || $target_type === null) {
        $q = "SELECT 
                d.delivery_no as invoice_no,
                d.delivery_date as invoice_date,
                d.total_amount,
                0 as withholding,
                d.total_amount as net_amount,
                d.payment_status,
                'Private' as type,
                COALESCE(SUM(od.applied_amount), 0) as total_paid
              FROM tbl_deliveries d
              LEFT JOIN tbl_or_delivery od ON d.id = od.delivery_id
              WHERE d.delivery_type = 'DR V'";
        if ($customer_id) $q .= " AND d.customer_id = :customer_id";
        if ($date_from) $q .= " AND d.delivery_date >= :date_from";
        if ($date_to) $q .= " AND d.delivery_date <= :date_to";
        if ($status) $q .= " AND d.payment_status = :status";
        $q .= " GROUP BY d.id";
        $queries[] = $q;
    }

    $total_invoiced = 0;
    $total_withholding = 0;
    $total_paid = 0;
    $net_amount_total = 0;

    foreach ($queries as $sql) {
        $stmt = $db->prepare($sql);
        if ($customer_id) $stmt->bindValue(':customer_id', $customer_id);
        if ($date_from) $stmt->bindValue(':date_from', $date_from);
        if ($date_to) $stmt->bindValue(':date_to', $date_to);
        if ($status) $stmt->bindValue(':status', $status);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $balance = $row['net_amount'] - $row['total_paid'];
            
            $line = [
                $row['invoice_no'],
                date('M d, Y', strtotime($row['invoice_date'])),
                formatCurrency($row['total_amount'])
            ];

            if ($target_type === 'Government' || $target_type === null) {
                $line[] = formatCurrency($row['withholding']);
                $line[] = formatCurrency($row['net_amount']);
            }

            $line = array_merge($line, [
                formatCurrency($row['total_paid']),
                formatCurrency($balance),
                $row['payment_status'],
                $row['type']
            ]);

            fputcsv($output, $line);

            $total_invoiced += $row['total_amount'];
            $total_withholding += $row['withholding'];
            $total_paid += $row['total_paid'];
            $net_amount_total += $row['net_amount'];
        }
    }

    fputcsv($output, ['']);
    fputcsv($output, ['SUMMARY']);
    fputcsv($output, ['Total Invoiced:', formatCurrency($total_invoiced)]);
    if ($target_type === 'Government' || $target_type === null) {
        fputcsv($output, ['Total Withholding:', formatCurrency($total_withholding)]);
        fputcsv($output, ['Net Amount:', formatCurrency($net_amount_total)]);
    }
    fputcsv($output, ['Total Paid:', formatCurrency($total_paid)]);
    fputcsv($output, ['Outstanding Balance:', formatCurrency($net_amount_total - $total_paid)]);

    fclose($output);
    exit();

} catch (Exception $e) {
    error_log("Error in export_soa.php: " . $e->getMessage());
    echo "Error generating export: " . $e->getMessage();
    exit();
}
?>