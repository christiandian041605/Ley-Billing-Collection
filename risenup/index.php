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

// Define RISENUP focus areas
$risenup = [
    'R' => [
        'letter' => 'R',
        'title' => 'Research',
        'full_name' => 'RESEARCH, TECHNOLOGY AND INNOVATION',
        'color' => 'primary',
        'icon' => 'bi-search'
    ],
    'I' => [
        'letter' => 'I',
        'title' => 'Internationalization',
        'full_name' => 'INTERNATIONALIZATION, EXTENSION AND LINKAGES',
        'color' => 'success',
        'icon' => 'bi-globe'
    ],
    'S' => [
        'letter' => 'S',
        'title' => 'Sustainability',
        'full_name' => 'SUSTAINABILITY OF GOOD GOVERNANCE',
        'color' => 'info',
        'icon' => 'bi-recycle'
    ],
    'E' => [
        'letter' => 'E',
        'title' => 'Excellence',
        'full_name' => 'EXCELLENCE IN ACADEMICS AND SERVICES',
        'color' => 'warning',
        'icon' => 'bi-award'
    ],
    'N' => [
        'letter' => 'N',
        'title' => 'Novelty',
        'full_name' => 'NOVELTY IN PRACTICES',
        'color' => 'danger',
        'icon' => 'bi-lightbulb'
    ],
    'U' => [
        'letter' => 'U',
        'title' => 'University Ranking',
        'full_name' => 'UNIVERSITY RANKING AND RECOGNITION',
        'color' => 'secondary',
        'icon' => 'bi-trophy'
    ],
    'P' => [
        'letter' => 'P',
        'title' => 'People',
        'full_name' => 'PEOPLE',
        'color' => 'dark',
        'icon' => 'bi-people'
    ]
];

// Get selected focus area
$selected = isset($_GET['focus']) ? strtoupper($_GET['focus']) : null;
$selected_area = $selected && isset($risenup[$selected]) ? $risenup[$selected] : null;

// Fetch SDPs for selected focus area
$sdps = [];
if ($selected_area) {
    // First, get unique SDPs
    $query = "SELECT DISTINCT s.sdp_id, s.kpi, s.initiatives, s.objectives_id,
              o.objectives_details, o.focus_area
              FROM tbl_sdp s
              LEFT JOIN tbl_objectives o ON s.objectives_id = o.objectives_id
              WHERE o.focus_area = :focus_area
              ORDER BY s.kpi";
    
    $stmt = $db->prepare($query);
    $stmt->execute(['focus_area' => $selected_area['full_name']]);
    $sdps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Then, fetch offices and targets for each SDP
    foreach ($sdps as $index => $sdp) {
        // Get offices
        $office_query = "SELECT DISTINCT rm.office_unit
                        FROM tbl_sdp_responsibility sr
                        LEFT JOIN tbl_responsibility_matrix rm ON sr.r_matrix_id = rm.r_matrix_id
                        WHERE sr.sdp_id = :sdp_id
                        ORDER BY rm.office_unit";
        $office_stmt = $db->prepare($office_query);
        $office_stmt->execute(['sdp_id' => $sdp['sdp_id']]);
        $offices = $office_stmt->fetchAll(PDO::FETCH_COLUMN);
        $sdps[$index]['offices'] = implode(', ', $offices);
        
        // Get targets
        $target_query = "SELECT * FROM tbl_target WHERE sdp_id = :sdp_id ORDER BY year, quarter";
        $target_stmt = $db->prepare($target_query);
        $target_stmt->execute(['sdp_id' => $sdp['sdp_id']]);
        $sdps[$index]['targets'] = $target_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get count for each focus area
$counts = [];
foreach ($risenup as $key => $area) {
    $count_query = "SELECT COUNT(DISTINCT s.sdp_id) as count
                    FROM tbl_sdp s
                    LEFT JOIN tbl_objectives o ON s.objectives_id = o.objectives_id
                    WHERE o.focus_area = :focus_area";
    $stmt = $db->prepare($count_query);
    $stmt->execute(['focus_area' => $area['full_name']]);
    $counts[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
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
                <h3 class="mb-0">RISENUP Framework</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="../dashboard/">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">RISENUP</li>
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

            <!-- RISENUP Pills Navigation -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                      <?php foreach ($risenup as $key => $area): ?>
                        <a href="?focus=<?php echo $key; ?>" class="btn btn-<?php echo $selected === $key ? 'primary' : 'outline-primary'; ?> rounded-pill px-4 py-2">
                          <span class="fw-bold fs-5"><?php echo $key; ?></span>
                          <span class="mx-2">•</span>
                          <span><?php echo $area['title']; ?></span>
                          <span class="badge text-bg-light text-primary ms-2"><?php echo $counts[$key]; ?></span>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php if ($selected_area): ?>
              <!-- Selected Focus Area Header -->
              <div class="row mb-4">
                <div class="col-12">
                  <div class="alert alert-<?php echo $selected_area['color']; ?> alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading">
                      <i class="<?php echo $selected_area['icon']; ?> me-2"></i>
                      <?php echo $selected_area['letter']; ?> - <?php echo $selected_area['full_name']; ?>
                    </h4>
                    <p class="mb-0">Displaying all Strategic Development Plans under this focus area.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                </div>
              </div>

              <!-- SDPs Display -->
              <?php if (count($sdps) > 0): ?>
                <!-- Search Bar -->
                <div class="row mb-3">
                  <div class="col-12">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-search"></i></span>
                      <input type="text" class="form-control" id="sdpSearch" placeholder="Search SDPs by KPI, initiatives, or objectives...">
                    </div>
                  </div>
                </div>

                <!-- SDPs Cards -->
                <div class="row g-4">
                  <?php 
                  $rendered_sdps = []; // Track which SDPs we've rendered
                  $index = 0;
                  foreach ($sdps as $sdp): 
                    // Skip if we've already rendered this SDP ID
                    if (in_array($sdp['sdp_id'], $rendered_sdps)) {
                      continue;
                    }
                    $rendered_sdps[] = $sdp['sdp_id'];
                    
                    // Calculate progress
                    $target_count = count($sdp['targets']);
                    $total_target_value = 0;
                    
                    foreach ($sdp['targets'] as $target) {
                      $total_target_value += floatval($target['target_value']);
                    }
                    
                    // Get accomplishments for this SDP
                    $acc_query = "SELECT SUM(accomplishment) as total FROM tbl_accomplishment WHERE sdp_id = :sdp_id";
                    $acc_stmt = $db->prepare($acc_query);
                    $acc_stmt->execute(['sdp_id' => $sdp['sdp_id']]);
                    $acc_result = $acc_stmt->fetch(PDO::FETCH_ASSOC);
                    $total_accomplishment = floatval($acc_result['total'] ?? 0);
                    
                    $progress_percentage = $total_target_value > 0 ? min(100, round(($total_accomplishment / $total_target_value) * 100, 1)) : 0;
                    
                    $progress_color = 'danger';
                    if ($progress_percentage >= 75) {
                      $progress_color = 'success';
                    } elseif ($progress_percentage >= 50) {
                      $progress_color = 'warning';
                    } elseif ($progress_percentage >= 25) {
                      $progress_color = 'info';
                    }
                  ?>
                    <div class="col-lg-6 col-12 sdp-item" data-kpi="<?php echo strtolower(htmlspecialchars($sdp['kpi'] ?? '')); ?>" data-initiatives="<?php echo strtolower(htmlspecialchars($sdp['initiatives'] ?? '')); ?>" data-objectives="<?php echo strtolower(htmlspecialchars($sdp['objectives_details'] ?? '')); ?>" data-sdp-id="<?php echo $sdp['sdp_id']; ?>">
                      <div class="card shadow-sm h-100">
                        <div class="card-body">
                          <!-- KPI Header with Progress -->
                          <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                              <h5 class="card-title mb-0 flex-grow-1"><?php echo htmlspecialchars($sdp['kpi'] ?? 'N/A'); ?></h5>
                              <span class="badge bg-primary ms-2"><?php echo $progress_percentage; ?>%</span>
                            </div>
                            <div class="progress mb-2" style="height: 8px;">
                              <div class="progress-bar bg-<?php echo $progress_color; ?>" role="progressbar" style="width: <?php echo $progress_percentage; ?>%" aria-valuenow="<?php echo $progress_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">
                              Progress: <?php echo number_format($total_accomplishment, 2); ?> / <?php echo number_format($total_target_value, 2); ?>
                            </small>
                          </div>

                          <!-- Accordion for Details -->
                          <div class="accordion" id="accordion<?php echo $sdp['sdp_id']; ?>">
                            
                            <!-- Initiatives -->
                            <div class="accordion-item">
                              <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#initiatives<?php echo $sdp['sdp_id']; ?>">
                                  <i class="bi bi-lightbulb me-2"></i> Initiatives
                                </button>
                              </h2>
                              <div id="initiatives<?php echo $sdp['sdp_id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordion<?php echo $sdp['sdp_id']; ?>">
                                <div class="accordion-body">
                                  <?php echo htmlspecialchars($sdp['initiatives'] ?? 'N/A'); ?>
                                </div>
                              </div>
                            </div>

                            <!-- Objective -->
                            <div class="accordion-item">
                              <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#objective<?php echo $sdp['sdp_id']; ?>">
                                  <i class="bi bi-target me-2"></i> Objective
                                </button>
                              </h2>
                              <div id="objective<?php echo $sdp['sdp_id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordion<?php echo $sdp['sdp_id']; ?>">
                                <div class="accordion-body">
                                  <?php echo htmlspecialchars($sdp['objectives_details'] ?? 'N/A'); ?>
                                </div>
                              </div>
                            </div>

                            <!-- Focus Area & Responsibility -->
                            <div class="accordion-item">
                              <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#details<?php echo $sdp['sdp_id']; ?>">
                                  <i class="bi bi-info-circle me-2"></i> Details
                                </button>
                              </h2>
                              <div id="details<?php echo $sdp['sdp_id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordion<?php echo $sdp['sdp_id']; ?>">
                                <div class="accordion-body">
                                  <div class="mb-3">
                                    <strong><i class="bi bi-bullseye me-2"></i>Focus Area:</strong><br>
                                    <span class="badge text-bg-primary mt-1">
                                      <?php echo htmlspecialchars($sdp['focus_area'] ?? 'N/A'); ?>
                                    </span>
                                  </div>
                                  <div>
                                    <strong><i class="bi bi-people me-2"></i>Responsibility Matrix:</strong><br>
                                    <?php if (!empty($sdp['offices'])): ?>
                                      <?php 
                                      $offices = explode(', ', $sdp['offices']);
                                      foreach ($offices as $office): ?>
                                        <span class="badge bg-secondary me-1 mt-1"><?php echo htmlspecialchars($office); ?></span>
                                      <?php endforeach; ?>
                                    <?php else: ?>
                                      <span class="text-muted">No offices assigned</span>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <!-- Targets -->
                            <div class="accordion-item">
                              <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#targets<?php echo $sdp['sdp_id']; ?>">
                                  <i class="bi bi-calendar-check me-2"></i> Targets
                                </button>
                              </h2>
                              <div id="targets<?php echo $sdp['sdp_id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordion<?php echo $sdp['sdp_id']; ?>">
                                <div class="accordion-body">
                                  <?php if (count($sdp['targets']) > 0): ?>
                                    <div class="table-responsive">
                                      <table class="table table-sm table-bordered table-hover mb-0">
                                        <thead class="table-light">
                                          <tr>
                                            <th>Year</th>
                                            <th>Quarter</th>
                                            <th>Target Value</th>
                                            <th>Type</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          <?php foreach ($sdp['targets'] as $target): ?>
                                            <tr>
                                              <td><?php echo htmlspecialchars($target['year']); ?></td>
                                              <td><?php echo htmlspecialchars($target['quarter'] ?? '-'); ?></td>
                                              <td><strong><?php echo htmlspecialchars($target['target_value']); ?></strong></td>
                                              <td>
                                                <span class="badge bg-info">
                                                  <?php echo htmlspecialchars($target['value_type']); ?>
                                                </span>
                                              </td>
                                            </tr>
                                          <?php endforeach; ?>
                                        </tbody>
                                      </table>
                                    </div>
                                  <?php else: ?>
                                    <p class="text-muted mb-0">No targets set for this SDP.</p>
                                  <?php endif; ?>
                                </div>
                              </div>
                            </div>

                          </div>
                        </div>
                      </div>
                    </div>
                  <?php 
                    $index++;
                  endforeach; 
                  ?>
                </div>
                
              <?php else: ?>
                <div class="row">
                  <div class="col-12">
                    <div class="alert alert-info text-center">
                      <i class="bi bi-info-circle me-2"></i>
                      No Strategic Development Plans found for this focus area.
                    </div>
                  </div>
                </div>
              <?php endif; ?>

            <?php else: ?>
              <!-- No selection message -->
              <div class="row">
                <div class="col-12">
                  <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    Please select a focus area from the pills above to view Strategic Development Plans.
                  </div>
                </div>
              </div>
            <?php endif; ?>

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
    
    <script>
      // Search functionality for SDPs
      document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('sdpSearch');
        
        if (searchInput) {
          searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const sdpItems = document.querySelectorAll('.sdp-item');
            
            sdpItems.forEach(function(item) {
              const kpi = item.getAttribute('data-kpi') || '';
              const initiatives = item.getAttribute('data-initiatives') || '';
              const objectives = item.getAttribute('data-objectives') || '';
              
              const searchText = kpi + ' ' + initiatives + ' ' + objectives;
              
              if (searchText.includes(searchTerm)) {
                item.style.display = '';
              } else {
                item.style.display = 'none';
              }
            });
          });
        }
      });
    </script>
    
  </body>
  <!--end::Body-->
</html>
