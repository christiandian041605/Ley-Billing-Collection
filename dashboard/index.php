<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

include "../config/app.php";
include_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

// Overall statistics
$stats = [];

// Total Charge Invoices
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_charge_invoices");
$stats['total_invoices'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total Revenue (sum of total_amount from paid invoices)
$stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM tbl_charge_invoices WHERE payment_status = 'Paid'");
$stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Paid Invoices
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_charge_invoices WHERE payment_status = 'Paid'");
$stats['paid_invoices'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Unpaid Invoices
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_charge_invoices WHERE payment_status = 'Unpaid'");
$stats['unpaid_invoices'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total Deliveries
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_deliveries");
$stats['total_deliveries'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total Official Receipts
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_official_receipts");
$stats['total_or'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total Private Customers
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_customers WHERE customer_type = 'Private'");
$stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent Invoices (Last 10)
$stmt = $db->query("SELECT ci.invoice_no, ci.invoice_date, c.name as customer_name, c.business_name, ci.total_amount, ci.payment_status, c.customer_type, ci.created_at 
                    FROM tbl_charge_invoices ci
                    JOIN tbl_customers c ON ci.customer_id = c.id
                    ORDER BY ci.created_at DESC 
                    LIMIT 10");
$recent_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly Revenue Chart Data (Last 6 months)
$stmt = $db->query("SELECT 
                        DATE_FORMAT(invoice_date, '%Y-%m') as month,
                        SUM(total_amount) as revenue,
                        COUNT(*) as invoice_count
                    FROM tbl_charge_invoices
                    WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND payment_status = 'Paid'
                    GROUP BY DATE_FORMAT(invoice_date, '%Y-%m')
                    ORDER BY month ASC");
$monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">

  <!--begin::Head-->
  <?php include "../header.php"; ?>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="layout-fixed sidebar-mini bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">

      <!--begin::Header-->
      <?php include "../navbar.php"; ?>
      <!--end::Header-->

      <!--begin::Sidebar-->
      <?php include "../sidebar.php"; ?>
      <!--end::Sidebar-->

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
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
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">

            <!-- Statistics Cards -->
            <div class="row">
              <div class="col-lg-3 col-6">
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3><?php echo $stats['total_invoices']; ?></h3>
                    <p>Total Invoices</p>
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
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3>₱<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    <p>Total Revenue</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-currency-dollar"></i>
                  </div>
                  <a href="../payments/" class="small-box-footer link-light">
                    More info <i class="bi bi-arrow-right-circle-fill"></i>
                  </a>
                </div>
              </div>

              <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3><?php echo $stats['unpaid_invoices']; ?></h3>
                    <p>Unpaid Invoices</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                  </div>
                  <a href="../charge_invoices/?filter=unpaid" class="small-box-footer link-dark">
                    More info <i class="bi bi-arrow-right-circle-fill"></i>
                  </a>
                </div>
              </div>

              <div class="col-lg-3 col-6">
                <div class="small-box text-bg-info">
                  <div class="inner">
                    <h3><?php echo $stats['total_customers']; ?></h3>
                    <p>Private Customers</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-people"></i>
                  </div>
                  <a href="../customers/" class="small-box-footer link-light">
                    More info <i class="bi bi-arrow-right-circle-fill"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="row">
              <div class="col-lg-4 col-6">
                <div class="small-box text-bg-secondary">
                  <div class="inner">
                    <h3><?php echo $stats['paid_invoices']; ?></h3>
                    <p>Paid Invoices</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-check-circle"></i>
                  </div>
                </div>
              </div>

              <div class="col-lg-4 col-6">
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3><?php echo $stats['total_deliveries']; ?></h3>
                    <p>Total Deliveries</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-truck"></i>
                  </div>
                  <a href="../deliveries/" class="small-box-footer link-light">
                    More info <i class="bi bi-arrow-right-circle-fill"></i>
                  </a>
                </div>
              </div>

              <div class="col-lg-4 col-6">
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3><?php echo $stats['total_or']; ?></h3>
                    <p>Official Receipts</p>
                  </div>
                  <div class="small-box-icon">
                    <i class="bi bi-file-earmark-text"></i>
                  </div>
                  <a href="../official_receipts/" class="small-box-footer link-light">
                    More info <i class="bi bi-arrow-right-circle-fill"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- Recent Invoices -->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Recent Charge Invoices</h3>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>Invoice No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (count($recent_invoices) > 0): ?>
                            <?php foreach ($recent_invoices as $invoice): ?>
                              <tr>
                                <td><?php echo htmlspecialchars($invoice['invoice_no']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($invoice['invoice_date'])); ?></td>
                                <td>
                                  <?php 
                                    $display_name = !empty($invoice['business_name']) ? $invoice['business_name'] : $invoice['customer_name'];
                                    echo htmlspecialchars($display_name); 
                                  ?>
                                </td>
                                <td>
                                  <?php 
                                    $badge_class = '';
                                    switch($invoice['customer_type']) {
                                        case 'Private': $badge_class = 'info'; break;
                                        case 'Business': $badge_class = 'primary'; break;
                                        case 'Government': $badge_class = 'secondary'; break;
                                        default: $badge_class = 'secondary'; break;
                                    }
                                  ?>
                                  <span class="badge bg-<?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($invoice['customer_type']); ?>
                                  </span>
                                </td>
                                <td>₱<?php echo number_format($invoice['total_amount'], 2); ?></td>
                                <td>
                                  <?php 
                                    $status_badge_class = '';
                                    switch($invoice['payment_status']) {
                                        case 'Paid': $status_badge_class = 'success'; break;
                                        case 'Partially Paid': $status_badge_class = 'warning'; break;
                                        case 'Unpaid': $status_badge_class = 'danger'; break;
                                        default: $status_badge_class = 'secondary'; break;
                                    }
                                  ?>
                                  <span class="badge bg-<?php echo $status_badge_class; ?>">
                                    <?php echo htmlspecialchars($invoice['payment_status']); ?>
                                  </span>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="6" class="text-center">No invoices found</td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!--begin::Footer-->
      <?php include "../footer.php"; ?>
      <!--end::Footer-->

    </div>
    <!--end::App Wrapper-->

    <!--begin::Script-->
    <?php include "../script.php"; ?>
    
  </body>
  <!--end::Body-->
</html>