<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
  header("Location: ../login/");
  exit();
}

include "../config/app.php";
include_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

// Overall statistics
$stats = [];

// Total Customers by Type
$stmt = $db->query("SELECT customer_type, COUNT(*) as count FROM tbl_customers GROUP BY customer_type");
$customer_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stats['customers'] = [];
foreach ($customer_types as $type) {
  $stats['customers'][$type['customer_type']] = $type['count'];
}
$stats['total_customers'] = array_sum($stats['customers']);

// Invoice Statistics (Combining Charge Invoices and DR V)
// Count Charge Invoices
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_charge_invoices");
$ci_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Count DR V Deliveries (Private Sales)
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_deliveries WHERE delivery_type = 'DR V'");
$drv_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stats['total_invoices'] = $ci_count + $drv_count;

// Paid
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_charge_invoices WHERE payment_status = 'Paid'");
$ci_paid = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_deliveries WHERE delivery_type = 'DR V' AND payment_status = 'Paid'");
$drv_paid = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$stats['paid_invoices'] = $ci_paid + $drv_paid;

// Unpaid
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_charge_invoices WHERE payment_status = 'Unpaid'");
$ci_unpaid = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_deliveries WHERE delivery_type = 'DR V' AND payment_status = 'Unpaid'");
$drv_unpaid = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$stats['unpaid_invoices'] = $ci_unpaid + $drv_unpaid;

// Partially Paid
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_charge_invoices WHERE payment_status = 'Partially Paid'");
$ci_partial = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_deliveries WHERE delivery_type = 'DR V' AND payment_status = 'Partially Paid'");
$drv_partial = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$stats['partial_invoices'] = $ci_partial + $drv_partial;

// Government Billing Statistics
$stmt = $db->query("SELECT 
                        COUNT(*) as count,
                        COALESCE(SUM(total_amount), 0) as total_amount,
                        COALESCE(SUM(withholding_tax_5), 0) as tax_5,
                        COALESCE(SUM(withholding_tax_1), 0) as tax_1,
                        COALESCE(SUM(net_amount), 0) as net_amount
                    FROM tbl_charge_invoices ci 
                    JOIN tbl_customers c ON ci.customer_id = c.id 
                    WHERE c.customer_type = 'Government'");
$gov_stats = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['government_invoices'] = $gov_stats['count'];
$stats['government_revenue'] = $gov_stats['total_amount'];
$stats['total_withholding_tax'] = $gov_stats['tax_5'] + $gov_stats['tax_1'];
$stats['government_net'] = $gov_stats['net_amount'];

// Revenue Statistics
$stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tbl_charge_invoices WHERE payment_status = 'Paid'");
$ci_rev = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tbl_deliveries WHERE delivery_type = 'DR V' AND payment_status = 'Paid'");
$drv_rev = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$stats['total_revenue'] = $ci_rev + $drv_rev;

$stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tbl_charge_invoices WHERE payment_status = 'Unpaid'");
$ci_out = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tbl_deliveries WHERE delivery_type = 'DR V' AND payment_status = 'Unpaid'");
$drv_out = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$stats['outstanding_revenue'] = $ci_out + $drv_out;

// Delivery Statistics by Category
$stmt = $db->query("SELECT delivery_type, COUNT(*) as count FROM tbl_deliveries GROUP BY delivery_type");
$delivery_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stats['deliveries'] = [];
foreach ($delivery_types as $type) {
  $stats['deliveries'][$type['delivery_type']] = $type['count'];
}
$stats['total_deliveries'] = array_sum($stats['deliveries']);

// Payment Statistics
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_official_receipts");
$stats['total_or'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $db->query("SELECT 
                        payment_type, 
                        COUNT(*) as count,
                        COALESCE(SUM(amount_received), 0) as total_amount
                    FROM tbl_official_receipts 
                    GROUP BY payment_type");
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stats['payments'] = [];
foreach ($payment_methods as $method) {
  $stats['payments'][$method['payment_type']] = [
    'count' => $method['count'],
    'amount' => $method['total_amount']
  ];
}

// Recent Transactions (Last 10 - Invoices and DR V)
$stmt = $db->query("
    SELECT * FROM (
        SELECT ci.invoice_no as ref_no, ci.invoice_date as date, c.name as customer_name, c.business_name, 
               ci.total_amount, ci.net_amount, ci.withholding_tax_5, ci.withholding_tax_1,
               ci.payment_status, c.customer_type, ci.created_at, 'Invoice' as type 
        FROM tbl_charge_invoices ci
        JOIN tbl_customers c ON ci.customer_id = c.id
        
        UNION ALL
        
        SELECT d.delivery_no as ref_no, d.delivery_date as date, c.name as customer_name, c.business_name,
               d.total_amount, d.total_amount as net_amount, 0 as withholding_tax_5, 0 as withholding_tax_1,
               d.payment_status, c.customer_type, d.created_at, 'DR V' as type
        FROM tbl_deliveries d
        JOIN tbl_customers c ON d.customer_id = c.id
        WHERE d.delivery_type = 'DR V'
    ) as transactions
    ORDER BY created_at DESC 
    LIMIT 10
");
$recent_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly Revenue Chart Data (Last 6 months - Combined)
$stmt = $db->query("
    SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        SUM(total_amount) as revenue,
        COUNT(*) as invoice_count
    FROM (
        SELECT invoice_date as date, total_amount FROM tbl_charge_invoices WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND payment_status = 'Paid'
        UNION ALL
        SELECT delivery_date as date, total_amount FROM tbl_deliveries WHERE delivery_type = 'DR V' AND delivery_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND payment_status = 'Paid'
    ) as combined_revenue
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month ASC
");
$monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Backup Overview for Dashboard
include_once __DIR__ . "/../backup/backup_settings.php";
$backupSettings = new BackupSettings();
$settingsData = $backupSettings->getSettings();

// Flatten settings for dashboard usage
$dash_backup_settings = [
  'enabled' => $settingsData['enabled'] ?? false,
  'day_num' => $settingsData['schedule']['day'] ?? 1,
  'time' => $settingsData['schedule']['time'] ?? '02:00',
  'last_run' => $settingsData['last_backup'] ?? null
];

// Map day number to name
$daysMap = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
$dash_backup_settings['day'] = $daysMap[$dash_backup_settings['day_num']] ?? 'Monday';

$dash_backup_dir = __DIR__ . '/../backup/backups';
$dash_recent_backups = [];
if (file_exists($dash_backup_dir)) {
  // Catch both auto and manual files
  $files = glob($dash_backup_dir . '/*.sql');
  if ($files) {
    usort($files, function ($a, $b) {
      return filemtime($b) - filemtime($a);
    });
    foreach (array_slice($files, 0, 5) as $file) {
      $dash_recent_backups[] = [
        'name' => basename($file),
        'size' => filesize($file),
        'date' => filemtime($file)
      ];
    }
  }
}
?>
<!doctype html>
<html lang="en">

<?php include "../header.php"; ?>

<body class="layout-fixed bg-body-tertiary">
  <div class="app-wrapper">

    <?php include "../navbar.php"; ?>
    <?php include "../sidebar.php"; ?>
    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <h3 class="mb-0">Dashboard</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <div class="app-content">
        <div class="container-fluid">

          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box text-bg-info">
                <div class="inner">
                  <h3><?php echo number_format($stats['total_customers']); ?></h3>
                  <p>Total Customers</p>
                  <small>
                    <?php if (isset($stats['customers']['Private'])): ?>
                      Private: <?php echo $stats['customers']['Private']; ?>
                    <?php endif; ?>
                    <?php if (isset($stats['customers']['Government'])): ?>
                      | Gov: <?php echo $stats['customers']['Government']; ?>
                    <?php endif; ?>
                  </small>
                </div>
                <div class="small-box-icon">
                  <i class="bi bi-people"></i>
                </div>
                <a href="../customers/" class="small-box-footer link-light">
                  More info <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box text-bg-success">
                <div class="inner">
                  <h3>₱<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                  <p>Total Revenue (Paid)</p>
                  <small>Outstanding: ₱<?php echo number_format($stats['outstanding_revenue'], 2); ?></small>
                </div>
                <div class="small-box-icon">
                  <i class="bi bi-currency-peso"></i>
                </div>
                <a href="../payments/" class="small-box-footer link-light">
                  More info <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box text-bg-primary">
                <div class="inner">
                  <h3><?php echo number_format($stats['total_invoices']); ?></h3>
                  <p>Total Invoices/DRs</p>
                  <small>
                    Paid: <?php echo $stats['paid_invoices']; ?> |
                    Unpaid: <?php echo $stats['unpaid_invoices']; ?>
                    <?php if ($stats['partial_invoices'] > 0): ?>
                      | Partial: <?php echo $stats['partial_invoices']; ?>
                    <?php endif; ?>
                  </small>
                </div>
                <div class="small-box-icon">
                  <i class="bi bi-receipt"></i>
                </div>
                <a href="../charge_invoices/" class="small-box-footer link-light">
                  More info <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box text-bg-warning">
                <div class="inner">
                  <h3><?php echo number_format($stats['government_invoices']); ?></h3>
                  <p>Government Invoices</p>
                  <small>
                    Rev: ₱<?php echo number_format($stats['government_revenue'], 2); ?><br>
                    Tax: ₱<?php echo number_format($stats['total_withholding_tax'], 2); ?>
                  </small>
                </div>
                <div class="small-box-icon">
                  <i class="bi bi-building"></i>
                </div>
                <a href="../charge_invoices/?filter=government" class="small-box-footer link-dark">
                  More info <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-3 col-6">
              <div class="small-box text-bg-secondary">
                <div class="inner">
                  <h3><?php echo number_format($stats['total_deliveries']); ?></h3>
                  <p>Total Deliveries</p>
                  <small>
                    <?php if (isset($stats['deliveries']['DR V'])): ?>
                      DR V: <?php echo $stats['deliveries']['DR V']; ?>
                    <?php endif; ?>
                    <?php if (isset($stats['deliveries']['DR Government'])): ?>
                      | DR Gov: <?php echo $stats['deliveries']['DR Government']; ?>
                    <?php endif; ?>
                  </small>
                </div>
                <div class="small-box-icon">
                  <i class="bi bi-truck"></i>
                </div>
                <a href="../deliveries/" class="small-box-footer link-light">
                  More info <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box text-bg-dark">
                <div class="inner">
                  <h3><?php echo number_format($stats['total_or']); ?></h3>
                  <p>Official Receipts</p>
                  <small>
                    <?php if (isset($stats['payments']['Cash'])): ?>
                      Cash: <?php echo $stats['payments']['Cash']['count']; ?>
                    <?php endif; ?>
                    <?php if (isset($stats['payments']['Check'])): ?>
                      | Check: <?php echo $stats['payments']['Check']['count']; ?>
                    <?php endif; ?>
                  </small>
                </div>
                <div class="small-box-icon">
                  <i class="bi bi-receipt-cutoff"></i>
                </div>
                <a href="../payments/" class="small-box-footer link-light">
                  More info <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box text-bg-info">
                <div class="inner">
                  <h3>
                    ₱<?php
                    $cash_amount = isset($stats['payments']['Cash']) ? $stats['payments']['Cash']['amount'] : 0;
                    $check_amount = isset($stats['payments']['Check']) ? $stats['payments']['Check']['amount'] : 0;
                    $bank_amount = isset($stats['payments']['Bank Transfer']) ? $stats['payments']['Bank Transfer']['amount'] : 0;
                    echo number_format($cash_amount + $check_amount + $bank_amount, 2);
                    ?>
                  </h3>
                  <p>Total Payments</p>
                  <small>
                    Cash: ₱<?php echo number_format($cash_amount, 2); ?><br>
                    Check: ₱<?php echo number_format($check_amount, 2); ?>
                  </small>
                </div>
                <div class="small-box-icon">
                  <i class="bi bi-cash-stack"></i>
                </div>
                <a href="../payments/" class="small-box-footer link-light">
                  More info <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
              </div>
            </div>

            <div class="col-lg-3 col-6">
              <div class="small-box text-bg-success">
                <div class="inner">
                  <h3><?php echo number_format($stats['paid_invoices'] + $stats['total_or']); ?></h3>
                  <p>Completed Transactions</p>
                  <small>
                    Invoices: <?php echo $stats['paid_invoices']; ?><br>
                    Receipts: <?php echo $stats['total_or']; ?>
                  </small>
                </div>
                <div class="small-box-icon">
                  <i class="bi bi-graph-up-arrow"></i>
                </div>
                <a href="../activity_log/" class="small-box-footer link-light">
                  More info <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-8">
              <div class="card">
                <div class="card-header border-0">
                  <h3 class="card-title">Recent Transactions</h3>
                </div>
                <div class="card-body table-responsive p-0">
                  <table class="table table-striped table-valign-middle">
                    <thead>
                      <tr>
                        <th>Ref No.</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($recent_invoices as $invoice): ?>
                        <tr>
                          <td>
                            <?php echo $invoice['ref_no']; ?>
                            <?php if ($invoice['type'] === 'Invoice'): ?>
                              <span class="badge bg-info text-white" style="font-size: 0.65rem;">Gov</span>
                            <?php endif; ?>
                          </td>
                          <td><?php echo $invoice['type']; ?></td>
                          <td><?php echo $invoice['customer_name']; ?></td>
                          <td>₱<?php echo number_format($invoice['total_amount'], 2); ?></td>
                          <td>
                            <?php
                            $status_class = 'secondary'; // Set default
                            switch ($invoice['payment_status']) {
                              case 'Paid':
                                $status_class = 'success';
                                break;
                              case 'Partially Paid':
                                $status_class = 'warning';
                                break;
                              case 'Unpaid':
                                $status_class = 'danger';
                                break;
                              default:
                                $status_class = 'secondary';
                                break;
                            }
                            ?>
                            <span
                              class="badge bg-<?php echo $status_class; ?>"><?php echo $invoice['payment_status']; ?></span>
                          </td>
                          <td><?php echo date('M d, Y', strtotime($invoice['date'])); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <!-- Backup Overview Card -->
              <div class="card">
                <div class="card-header border-0">
                  <h3 class="card-title">Database Backup Overview</h3>
                  <div class="card-tools">
                    <a href="../backup/" class="btn btn-tool" title="View Details">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  </div>
                </div>
                <div class="card-body">
                  <div class="mb-4">
                    <h6 class="fw-bold"><i class="bi bi-calendar-check me-2 text-primary"></i>Schedule</h6>
                    <div class="ps-1">
                      <p class="mb-1 small">
                        <?php if ($dash_backup_settings['enabled']): ?>
                          <span
                            class="badge bg-success-subtle text-success border border-success-subtle mb-1">Active</span><br>
                          Every <strong><?php echo $dash_backup_settings['day']; ?></strong> at
                          <strong><?php echo $dash_backup_settings['time']; ?></strong>
                        <?php else: ?>
                          <span
                            class="badge bg-danger-subtle text-danger border border-danger-subtle mb-1">Disabled</span>
                        <?php endif; ?>
                      </p>
                      <p class="text-muted small mb-0">
                        Last Run:
                        <strong><?php echo $dash_backup_settings['last_run'] ? date('M d, Y h:i A', strtotime($dash_backup_settings['last_run'])) : 'Never'; ?></strong>
                      </p>
                    </div>
                  </div>

                  <h6 class="fw-bold"><i class="bi bi-clock-history me-2 text-info"></i>Recent Backups</h6>
                  <div class="ps-1">
                    <?php if (!empty($dash_recent_backups)): ?>
                      <ul class="list-unstyled mb-0">
                        <?php foreach (array_slice($dash_recent_backups, 0, 5) as $backup): ?>
                          <li class="mb-2 border-bottom pb-2">
                            <div class="d-flex justify-content-between align-items-center">
                              <div class="d-flex flex-column" style="max-width: 80%;">
                                <span class="text-truncate small fw-medium"
                                  title="<?php echo htmlspecialchars($backup['name']); ?>">
                                  <?php echo htmlspecialchars($backup['name']); ?>
                                </span>
                                <div class="d-flex justify-content-between text-muted x-small mt-1"
                                  style="font-size: 0.75rem;">
                                  <span><?php echo date('M d, Y h:i A', $backup['date']); ?></span>
                                  <span class="ms-2"><?php echo number_format($backup['size'] / 1024, 2); ?> KB</span>
                                </div>
                              </div>
                              <a href="../backup/download_backup.php?file=<?php echo urlencode($backup['name']); ?>"
                                class="btn btn-link btn-sm text-primary p-0" title="Download">
                                <i class="bi bi-download"></i>
                              </a>
                            </div>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php else: ?>
                      <p class="text-muted small">No automatic backups found.</p>
                    <?php endif; ?>
                  </div>

                  <div class="d-grid mt-3">
                    <a href="../backup/" class="btn btn-primary btn-sm">
                      <i class="bi bi-gear-fill me-1"></i> Configure Settings
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
    <?php include "../footer.php"; ?>
  </div>
  <?php include "../script.php"; ?>

  <!-- Automatic Backup Trigger -->
  <script>
    fetch('../backup/trigger_backup.php')
      .then(response => response.text())
      .then(data => console.log('Backup check completed'))
      .catch(error => console.error('Backup trigger error:', error));
  </script>

</body>

</html>