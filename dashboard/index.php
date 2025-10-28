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

// Fetch statistics
$stats = [];

// Total Users
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_user");
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total SDPs
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_sdp");
$stats['sdps'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total Objectives
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_objectives");
$stats['objectives'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total Accomplishments
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_accomplishment");
$stats['accomplishments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total Offices
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_responsibility_matrix");
$stats['offices'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total Targets
$stmt = $db->query("SELECT COUNT(*) as count FROM tbl_target");
$stats['targets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent Activities (Last 10)
$stmt = $db->query("SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name 
                    FROM tbl_activity_logs al 
                    LEFT JOIN tbl_user u ON al.user_id = u.user_id 
                    ORDER BY al.created_at DESC LIMIT 10");
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Accomplishments by Office
$stmt = $db->query("SELECT rm.office_unit, COUNT(a.accomplishment_id) as total 
                    FROM tbl_accomplishment a 
                    LEFT JOIN tbl_responsibility_matrix rm ON a.r_matrix_id = rm.r_matrix_id 
                    WHERE rm.office_unit IS NOT NULL
                    GROUP BY rm.office_unit 
                    ORDER BY total DESC 
                    LIMIT 5");
$top_offices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Quarterly Progress (Current Year)
$current_year = date('Y');
$stmt = $db->prepare("SELECT 
                        t.quarter,
                        COUNT(DISTINCT t.target_id) as target_count,
                        COUNT(DISTINCT a.accomplishment_id) as accomplishment_count
                      FROM tbl_target t
                      LEFT JOIN tbl_accomplishment a ON t.target_id = a.target_id
                      WHERE t.year = :year AND t.quarter IS NOT NULL
                      GROUP BY t.quarter
                      ORDER BY FIELD(t.quarter, 'Q1', 'Q2', 'Q3', 'Q4')");
$stmt->execute(['year' => $current_year]);
$quarterly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SDP with most targets
$stmt = $db->query("SELECT s.kpi, COUNT(t.target_id) as target_count 
                    FROM tbl_sdp s 
                    LEFT JOIN tbl_target t ON s.sdp_id = t.sdp_id 
                    GROUP BY s.sdp_id 
                    ORDER BY target_count DESC 
                    LIMIT 5");
$sdp_targets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Role distribution
$stmt = $db->query("SELECT role, COUNT(*) as count FROM tbl_user GROUP BY role");
$role_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

  <!--begin::Head-->
  <?php include "../header.php"; ?>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
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

            <!-- Statistics Cards Row -->
            <div class="row g-4 mb-4">
              <!-- Users Card -->
              <div class="col-lg-3 col-6">
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Total Users</p>
                  </div>
                  <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"></path>
                  </svg>
                  <a href="../users/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
              </div>

              <!-- SDPs Card -->
              <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3><?php echo $stats['sdps']; ?></h3>
                    <p>Strategic Development Plans</p>
                  </div>
                  <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0118 9.375v9.375a3 3 0 003-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 00-.673-.05A3 3 0 0015 1.5h-1.5a3 3 0 00-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6zM13.5 3A1.5 1.5 0 0012 4.5h4.5A1.5 1.5 0 0015 3h-1.5z"></path>
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 013 20.625V9.375zM6 12a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V12zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6 15a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V15zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6 18a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V18zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75z"></path>
                  </svg>
                  <a href="../sdp/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
              </div>

              <!-- Accomplishments Card -->
              <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3><?php echo $stats['accomplishments']; ?></h3>
                    <p>Total Accomplishments</p>
                  </div>
                  <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.166 2.621v.858c-1.035.148-2.059.33-3.071.543a.75.75 0 00-.584.859 6.753 6.753 0 006.138 5.6 6.73 6.73 0 002.743 1.346A6.707 6.707 0 019.279 15H8.54c-1.036 0-1.875.84-1.875 1.875V19.5h-.75a2.25 2.25 0 00-2.25 2.25c0 .414.336.75.75.75h15a.75.75 0 00.75-.75 2.25 2.25 0 00-2.25-2.25h-.75v-2.625c0-1.036-.84-1.875-1.875-1.875h-.739a6.706 6.706 0 01-1.112-3.173 6.73 6.73 0 002.743-1.347 6.753 6.753 0 006.139-5.6.75.75 0 00-.585-.858 47.077 47.077 0 00-3.07-.543V2.62a.75.75 0 00-.658-.744 49.22 49.22 0 00-6.093-.377c-2.063 0-4.096.128-6.093.377a.75.75 0 00-.657.744zm0 2.629c0 1.196.312 2.32.857 3.294A5.266 5.266 0 013.16 5.337a45.6 45.6 0 012.006-.343v.256zm13.5 0v-.256c.674.1 1.343.214 2.006.343a5.265 5.265 0 01-2.863 3.207 6.72 6.72 0 00.857-3.294z" clip-rule="evenodd"></path>
                  </svg>
                  <a href="../accomplishments/" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
              </div>

              <!-- Objectives Card -->
              <div class="col-lg-3 col-6">
                <div class="small-box text-bg-danger">
                  <div class="inner">
                    <h3><?php echo $stats['objectives']; ?></h3>
                    <p>Institutional Objectives</p>
                  </div>
                  <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M11.644 1.59a.75.75 0 01.712 0l9.75 5.25a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.712 0l-9.75-5.25a.75.75 0 010-1.32l9.75-5.25z"></path>
                    <path d="M3.265 10.602l7.668 4.129a2.25 2.25 0 002.134 0l7.668-4.13 1.37.739a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.71 0l-9.75-5.25a.75.75 0 010-1.32l1.37-.738z"></path>
                    <path d="M10.933 19.231l-7.668-4.13-1.37.739a.75.75 0 000 1.32l9.75 5.25c.221.12.489.12.71 0l9.75-5.25a.75.75 0 000-1.32l-1.37-.738-7.668 4.13a2.25 2.25 0 01-2.134-.001z"></path>
                  </svg>
                  <a href="../objectives/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
              </div>
            </div>
            <!-- End Statistics Cards Row -->

            <!-- Second Row of Stats -->
            <div class="row g-4 mb-4">
              <!-- Offices Card -->
              <div class="col-lg-4 col-6">
                <div class="small-box text-bg-info">
                  <div class="inner">
                    <h3><?php echo $stats['offices']; ?></h3>
                    <p>Offices/Units</p>
                  </div>
                  <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 2.25a.75.75 0 000 1.5v16.5h-.75a.75.75 0 000 1.5H15v-18a.75.75 0 000-1.5H3zM6.75 19.5v-2.25a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v2.25a.75.75 0 01-.75.75h-3a.75.75 0 01-.75-.75zM6 6.75A.75.75 0 016.75 6h.75a.75.75 0 010 1.5h-.75A.75.75 0 016 6.75zM6.75 9a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zM6 12.75a.75.75 0 01.75-.75h.75a.75.75 0 010 1.5h-.75a.75.75 0 01-.75-.75zM10.5 6a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zm-.75 3.75A.75.75 0 0110.5 9h.75a.75.75 0 010 1.5h-.75a.75.75 0 01-.75-.75zM10.5 12a.75.75 0 000 1.5h.75a.75.75 0 000-1.5h-.75zM16.5 6.75v15h5.25a.75.75 0 000-1.5H21v-12a.75.75 0 000-1.5h-4.5zm1.5 4.5a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75h-.008a.75.75 0 01-.75-.75v-.008zm.75 2.25a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75v-.008a.75.75 0 00-.75-.75h-.008zM18 17.25a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75h-.008a.75.75 0 01-.75-.75v-.008z" clip-rule="evenodd"></path>
                  </svg>
                  <a href="../responsibility_matrix/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
              </div>

              <!-- Targets Card -->
              <div class="col-lg-4 col-6">
                <div class="small-box text-bg-secondary">
                  <div class="inner">
                    <h3><?php echo $stats['targets']; ?></h3>
                    <p>Total Targets Set</p>
                  </div>
                  <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z" clip-rule="evenodd"></path>
                  </svg>
                  <a href="../sdp/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
              </div>

              <!-- Completion Rate Card -->
              <div class="col-lg-4 col-6">
                <div class="small-box text-bg-dark">
                  <div class="inner">
                    <h3><?php echo $stats['targets'] > 0 ? round(($stats['accomplishments'] / $stats['targets']) * 100, 1) : 0; ?>%</h3>
                    <p>Completion Rate</p>
                  </div>
                  <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"></path>
                  </svg>
                  <a href="../accomplishments/" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- Charts and Tables Row -->
            <div class="row g-4 mb-4">
              <!-- Quarterly Progress Chart -->
              <div class="col-lg-6">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">
                      <i class="bi bi-bar-chart-fill me-1"></i>
                      Quarterly Progress (<?php echo $current_year; ?>)
                    </h3>
                  </div>
                  <div class="card-body">
                    <canvas id="quarterlyChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                  </div>
                </div>
              </div>

              <!-- Top Performing Offices -->
              <div class="col-lg-6">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">
                      <i class="bi bi-award-fill me-1"></i>
                      Top Performing Offices
                    </h3>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th style="width: 10px">#</th>
                          <th>Office/Unit</th>
                          <th style="width: 40px">Count</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($top_offices as $office): ?>
                          <tr>
                            <td><?php echo $rank++; ?>.</td>
                            <td><?php echo htmlspecialchars($office['office_unit']); ?></td>
                            <td><span class="badge text-bg-success"><?php echo $office['total']; ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (empty($top_offices)): ?>
                          <tr>
                            <td colspan="3" class="text-center text-muted">No data available</td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- SDPs and Recent Activity Row -->
            <div class="row g-4 mb-4">
              <!-- SDPs with Most Targets -->
              <div class="col-lg-6">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">
                      <i class="bi bi-bullseye me-1"></i>
                      SDPs with Most Targets
                    </h3>
                  </div>
                  <div class="card-body p-0">
                    <table class="table">
                      <thead>
                        <tr>
                          <th>KPI</th>
                          <th style="width: 100px">Targets</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($sdp_targets as $sdp): ?>
                          <tr>
                            <td><?php echo htmlspecialchars(substr($sdp['kpi'], 0, 60)) . (strlen($sdp['kpi']) > 60 ? '...' : ''); ?></td>
                            <td><span class="badge text-bg-warning"><?php echo $sdp['target_count']; ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (empty($sdp_targets)): ?>
                          <tr>
                            <td colspan="2" class="text-center text-muted">No data available</td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="col-lg-6">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">
                      <i class="bi bi-clock-history me-1"></i>
                      Recent Activity
                    </h3>
                    <div class="card-tools">
                      <a href="../activity_log/" class="btn btn-tool btn-sm">
                        <i class="bi bi-arrow-right-circle"></i>
                      </a>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                      <?php foreach ($recent_activities as $activity): ?>
                        <li class="list-group-item">
                          <div class="d-flex justify-content-between align-items-start">
                            <div>
                              <strong><?php echo htmlspecialchars($activity['user_name'] ?: 'Unknown User'); ?></strong>
                              <span class="text-muted">- <?php echo htmlspecialchars($activity['action']); ?></span>
                              <br>
                              <small class="text-muted">
                                <?php echo htmlspecialchars($activity['module']); ?>
                                <?php if ($activity['details']): ?>
                                  - <?php echo htmlspecialchars(substr($activity['details'], 0, 50)) . (strlen($activity['details']) > 50 ? '...' : ''); ?>
                                <?php endif; ?>
                              </small>
                            </div>
                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></small>
                          </div>
                        </li>
                      <?php endforeach; ?>
                      <?php if (empty($recent_activities)): ?>
                        <li class="list-group-item text-center text-muted">No recent activity</li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- User Role Distribution -->
            <div class="row g-4 mb-4">
              <div class="col-lg-6">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">
                      <i class="bi bi-person-badge-fill me-1"></i>
                      User Role Distribution
                    </h3>
                  </div>
                  <div class="card-body">
                    <canvas id="roleChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                  </div>
                </div>
              </div>

              <!-- System Information -->
              <div class="col-lg-6">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">
                      <i class="bi bi-info-circle-fill me-1"></i>
                      System Information
                    </h3>
                  </div>
                  <div class="card-body">
                    <dl class="row">
                      <dt class="col-sm-6">Total Registered Users:</dt>
                      <dd class="col-sm-6"><?php echo $stats['users']; ?></dd>

                      <dt class="col-sm-6">Active SDPs:</dt>
                      <dd class="col-sm-6"><?php echo $stats['sdps']; ?></dd>

                      <dt class="col-sm-6">Objectives Defined:</dt>
                      <dd class="col-sm-6"><?php echo $stats['objectives']; ?></dd>

                      <dt class="col-sm-6">Targets Set:</dt>
                      <dd class="col-sm-6"><?php echo $stats['targets']; ?></dd>

                      <dt class="col-sm-6">Accomplishments Recorded:</dt>
                      <dd class="col-sm-6"><?php echo $stats['accomplishments']; ?></dd>

                      <dt class="col-sm-6">Overall Progress:</dt>
                      <dd class="col-sm-6">
                        <div class="progress">
                          <div class="progress-bar bg-success" role="progressbar" 
                               style="width: <?php echo $stats['targets'] > 0 ? round(($stats['accomplishments'] / $stats['targets']) * 100, 1) : 0; ?>%" 
                               aria-valuenow="<?php echo $stats['targets'] > 0 ? round(($stats['accomplishments'] / $stats['targets']) * 100, 1) : 0; ?>" 
                               aria-valuemin="0" aria-valuemax="100">
                            <?php echo $stats['targets'] > 0 ? round(($stats['accomplishments'] / $stats['targets']) * 100, 1) : 0; ?>%
                          </div>
                        </div>
                      </dd>

                      <dt class="col-sm-6">Current Year:</dt>
                      <dd class="col-sm-6"><?php echo $current_year; ?></dd>

                      <dt class="col-sm-6">System Status:</dt>
                      <dd class="col-sm-6"><span class="badge text-bg-success">Operational</span></dd>
                    </dl>
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
      // Quarterly Progress Chart
      const quarterlyData = <?php echo json_encode($quarterly_data); ?>;
      const quarterLabels = quarterlyData.map(q => q.quarter);
      const targetCounts = quarterlyData.map(q => parseInt(q.target_count));
      const accomplishmentCounts = quarterlyData.map(q => parseInt(q.accomplishment_count));

      const quarterlyCtx = document.getElementById('quarterlyChart').getContext('2d');
      new Chart(quarterlyCtx, {
        type: 'bar',
        data: {
          labels: quarterLabels.length > 0 ? quarterLabels : ['Q1', 'Q2', 'Q3', 'Q4'],
          datasets: [{
            label: 'Targets',
            data: targetCounts.length > 0 ? targetCounts : [0, 0, 0, 0],
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
          }, {
            label: 'Accomplishments',
            data: accomplishmentCounts.length > 0 ? accomplishmentCounts : [0, 0, 0, 0],
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });

      // Role Distribution Pie Chart
      const roleData = <?php echo json_encode($role_distribution); ?>;
      const roleLabels = roleData.map(r => r.role);
      const roleCounts = roleData.map(r => parseInt(r.count));

      const roleCtx = document.getElementById('roleChart').getContext('2d');
      new Chart(roleCtx, {
        type: 'pie',
        data: {
          labels: roleLabels.length > 0 ? roleLabels : ['No Data'],
          datasets: [{
            data: roleCounts.length > 0 ? roleCounts : [1],
            backgroundColor: [
              'rgba(255, 99, 132, 0.7)',
              'rgba(54, 162, 235, 0.7)',
              'rgba(255, 206, 86, 0.7)',
              'rgba(75, 192, 192, 0.7)',
              'rgba(153, 102, 255, 0.7)'
            ],
            borderColor: [
              'rgba(255, 99, 132, 1)',
              'rgba(54, 162, 235, 1)',
              'rgba(255, 206, 86, 1)',
              'rgba(75, 192, 192, 1)',
              'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    </script>
    
  </body>
  <!--end::Body-->
</html>
