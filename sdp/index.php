<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../config/app.php";
include_once "../config/database.php";
include "sdp.php";

$database = new Database();
$db = $database->getConnection();

$sdp = new Sdp($db);

// Fetch objectives for dropdown
$objectives_stmt = $db->query("SELECT * FROM tbl_objectives ORDER BY objectives_details");
$objectives = $objectives_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch offices for dropdown
$office_stmt = $db->query("SELECT * FROM tbl_responsibility_matrix ORDER BY office_unit");
$offices = $office_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all SDPs
$stmt = $sdp->read();

// Prepare statement to fetch responsibilities per SDP (many-to-many)
$resp_stmt = $db->prepare("SELECT rm.office_unit 
                           FROM tbl_sdp_responsibility sr 
                           JOIN tbl_responsibility_matrix rm ON rm.r_matrix_id = sr.r_matrix_id 
                           WHERE sr.sdp_id = ? 
                           ORDER BY rm.office_unit");
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
              <div class="col-sm-6"><h3 class="mb-0">Strategic Development Plans</h3></div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
        <!--begin::App Content-->
        <div class="app-content">

          <!--begin::Container-->
          <div class="container-fluid">

            <!--begin::Row-->
            <div class="row">
              <div class="col-12">
                <!-- Default box -->
                <div class="card card-primary card-outline">
                  <div class="card-header justify-content-between border-0">
                    <!-- this button will call the add modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSdpModal">Add SDP</button>
                    <div class="card-tools">
                      
                    </div>
                  </div>
                                    <div class="card-body table-responsive">
                    <table id="sdpTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>KPI</th>
                                <th>Initiatives</th>
                                <th>Objective</th>
                                <th>Focus Area</th>
                                <th>Responsibility Matrix</th>
                                <th>Targets</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by DataTables AJAX -->
                        </tbody>
                    </table>
                  </div>
                  <!-- /.card-body -->
                </div>
                <!-- /.card -->
              </div>
            </div>
            <!--end::Row-->

          </div>
          <!--end::Container-->

        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!-- Add SDP Modal -->
      <div class="modal fade" id="addSdpModal" tabindex="-1" aria-labelledby="addSdpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addSdpModalLabel">Add New SDP</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSdpForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="mb-3">
                  <label for="addObjective" class="form-label">Objective</label>
                  <select class="form-select" id="addObjective" name="objectives_id">
                    <option value="">Select Objective (Optional)</option>
                    <?php foreach ($objectives as $obj): ?>
                      <option value="<?php echo $obj['objectives_id']; ?>">
                        <?php echo htmlspecialchars($obj['objectives_details']); ?>
                        <?php if (!empty($obj['focus_area'])): ?>
                          - (<?php echo htmlspecialchars($obj['focus_area']); ?>)
                        <?php endif; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="addKpi" class="form-label">KPI (Key Performance Indicator)</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="addKpi" name="kpi" required>
                    <span class="input-group-text" id="addKpiSpinner" style="display: none;"><span class="spinner-border spinner-border-sm"></span></span>
                  </div>
                  <div id="addKpiError" class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                  <label for="addInitiatives" class="form-label">Initiatives</label>
                  <textarea class="form-control" id="addInitiatives" name="initiatives" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Responsible Offices (Optional)</label>
                  <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem; padding: 0.5rem;">
                    <?php foreach ($offices as $office): ?>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="responsibilities[]" value="<?php echo $office['r_matrix_id']; ?>" id="addOffice<?php echo $office['r_matrix_id']; ?>">
                        <label class="form-check-label" for="addOffice<?php echo $office['r_matrix_id']; ?>">
                          <?php echo htmlspecialchars($office['office_unit']); ?>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label d-flex justify-content-between align-items-center">
                    <span>Targets</span>
                    <button type="button" class="btn btn-sm btn-success" id="addTargetRowBtn">
                      <i class="bi bi-plus-circle"></i> Add Target
                    </button>
                  </label>
                  <div id="targetsContainer">
                    <div class="target-row border rounded p-3 mb-2">
                      <div class="row g-2">
                        <div class="col-md-3">
                          <label class="form-label small">Quarter</label>
                          <select class="form-select form-select-sm" name="targets[0][quarter]">
                            <option value="">Select</option>
                            <option value="Q1">Q1</option>
                            <option value="Q2">Q2</option>
                            <option value="Q3">Q3</option>
                            <option value="Q4">Q4</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Year</label>
                          <input type="number" class="form-control form-control-sm" name="targets[0][year]" min="2020" max="2050" value="<?php echo date('Y'); ?>">
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Value Type</label>
                          <select class="form-select form-select-sm" name="targets[0][value_type]">
                            <option value="Other">Other</option>
                            <option value="Percentage">Percentage</option>
                            <option value="Count">Count</option>
                            <option value="Index">Index</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Target Value</label>
                          <input type="number" step="0.01" class="form-control form-control-sm" name="targets[0][target_value]" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small">Description</label>
                          <input type="text" class="form-control form-control-sm" name="targets[0][description]" placeholder="Optional">
                        </div>
                      </div>
                      <button type="button" class="btn btn-sm btn-danger mt-2 remove-target-btn" style="display: none;">
                        <i class="bi bi-trash"></i> Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="addSdpSubmit" class="btn btn-primary">Save SDP</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Edit SDP Modal -->
      <div class="modal fade" id="editSdpModal" tabindex="-1" aria-labelledby="editSdpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editSdpModalLabel">Edit SDP</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSdpForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="editSdpId" name="sdp_id">
                <input type="hidden" name="action" value="update">
                
                <div class="mb-3">
                  <label for="editObjective" class="form-label">Objective</label>
                  <select class="form-select" id="editObjective" name="objectives_id">
                    <option value="">Select Objective (Optional)</option>
                    <?php foreach ($objectives as $obj): ?>
                      <option value="<?php echo $obj['objectives_id']; ?>">
                        <?php echo htmlspecialchars($obj['objectives_details']); ?>
                        <?php if (!empty($obj['focus_area'])): ?>
                          - (<?php echo htmlspecialchars($obj['focus_area']); ?>)
                        <?php endif; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="editKpi" class="form-label">KPI (Key Performance Indicator)</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="editKpi" name="kpi" required>
                    <span class="input-group-text" id="editKpiSpinner" style="display: none;"><span class="spinner-border spinner-border-sm"></span></span>
                  </div>
                  <div id="editKpiError" class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                  <label for="editInitiatives" class="form-label">Initiatives</label>
                  <textarea class="form-control" id="editInitiatives" name="initiatives" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Responsible Offices (Optional)</label>
                  <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem; padding: 0.5rem;">
                    <?php foreach ($offices as $office): ?>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="responsibilities[]" value="<?php echo $office['r_matrix_id']; ?>" id="editOffice<?php echo $office['r_matrix_id']; ?>">
                        <label class="form-check-label" for="editOffice<?php echo $office['r_matrix_id']; ?>">
                          <?php echo htmlspecialchars($office['office_unit']); ?>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label d-flex justify-content-between align-items-center">
                    <span>Targets</span>
                    <button type="button" class="btn btn-sm btn-success" id="editTargetRowBtn">
                      <i class="bi bi-plus-circle"></i> Add Target
                    </button>
                  </label>
                  <div id="editTargetsContainer">
                    <!-- Targets will be loaded dynamically -->
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="editSdpSubmit" class="btn btn-primary">Update SDP</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Delete SDP Modal -->
      <div class="modal fade" id="deleteSdpModal" tabindex="-1" aria-labelledby="deleteSdpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteSdpModalLabel">Confirm Delete</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteSdpForm">
                <div class="modal-body">
                    <p id="deleteSdpConfirmationText"></p>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" id="deleteSdpId" name="sdp_id">
                    <input type="hidden" name="action" value="delete">
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
          </div>
        </div>
      </div>

      <!-- View Targets Modal -->
      <div class="modal fade" id="viewTargetsModal" tabindex="-1" aria-labelledby="viewTargetsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="viewTargetsModalLabel">Targets for: <span id="viewTargetsKpi"></span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div id="viewTargetsContent">
                <div class="text-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <!--begin::Footer-->
      <?php include "../footer.php"; ?>
      <!--end::Footer-->

    </div>
    <!--end::App Wrapper-->

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Notification</strong>
          <small>Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <!-- Toast message will be inserted here -->
        </div>
      </div>
    </div>

    <!--begin::Script-->
    <?php include "../script.php"; ?>
    <script>
        $(document).ready(function() {
            $('#sdpTable').DataTable({
                "ajax": "get_sdps.php",
                "processing": true,
                "columns": [
                    { "data": 0 }, // KPI
                    { "data": 1 }, // Initiatives
                    { "data": 2 }, // Objective
                    { "data": 3 }, // Focus Area
                    { "data": 4 }, // Responsibility Matrix
                    { "data": 5, "orderable": false }, // Targets
                    { "data": 6, "orderable": false } // Actions
                ],
                responsive: true,
                autoWidth: false
            });

            let targetRowCounter = 1;
            let editTargetRowCounter = 0;

            // Add Target Row for Add Modal
            $('#addTargetRowBtn').on('click', function() {
                const newRow = `
                    <div class="target-row border rounded p-3 mb-2">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">Quarter</label>
                                <select class="form-select form-select-sm" name="targets[${targetRowCounter}][quarter]">
                                    <option value="">Select</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Year</label>
                                <input type="number" class="form-control form-control-sm" name="targets[${targetRowCounter}][year]" min="2020" max="2050" value="<?php echo date('Y'); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Value Type</label>
                                <select class="form-select form-select-sm" name="targets[${targetRowCounter}][value_type]">
                                    <option value="Other">Other</option>
                                    <option value="Percentage">Percentage</option>
                                    <option value="Count">Count</option>
                                    <option value="Index">Index</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Target Value</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="targets[${targetRowCounter}][target_value]" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Description</label>
                                <input type="text" class="form-control form-control-sm" name="targets[${targetRowCounter}][description]" placeholder="Optional">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger mt-2 remove-target-btn">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                `;
                $('#targetsContainer').append(newRow);
                targetRowCounter++;
                updateRemoveButtons('#targetsContainer');
            });

            // Add Target Row for Edit Modal
            $('#editTargetRowBtn').on('click', function() {
                const newRow = `
                    <div class="target-row border rounded p-3 mb-2">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">Quarter</label>
                                <select class="form-select form-select-sm" name="targets[${editTargetRowCounter}][quarter]">
                                    <option value="">Select</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Year</label>
                                <input type="number" class="form-control form-control-sm" name="targets[${editTargetRowCounter}][year]" min="2020" max="2050" value="<?php echo date('Y'); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Value Type</label>
                                <select class="form-select form-select-sm" name="targets[${editTargetRowCounter}][value_type]">
                                    <option value="Other">Other</option>
                                    <option value="Percentage">Percentage</option>
                                    <option value="Count">Count</option>
                                    <option value="Index">Index</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Target Value</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="targets[${editTargetRowCounter}][target_value]" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Description</label>
                                <input type="text" class="form-control form-control-sm" name="targets[${editTargetRowCounter}][description]" placeholder="Optional">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger mt-2 remove-target-btn">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                `;
                $('#editTargetsContainer').append(newRow);
                editTargetRowCounter++;
                updateRemoveButtons('#editTargetsContainer');
            });

            // Remove Target Row
            $(document).on('click', '.remove-target-btn', function() {
                $(this).closest('.target-row').remove();
                const container = $(this).closest('[id$="TargetsContainer"]').attr('id');
                updateRemoveButtons('#' + container);
            });

            // Update visibility of remove buttons
            function updateRemoveButtons(containerSelector) {
                const rows = $(containerSelector + ' .target-row');
                if (rows.length === 1) {
                    rows.find('.remove-target-btn').hide();
                } else {
                    rows.find('.remove-target-btn').show();
                }
            }

            // Edit SDP - Populate Modal
            $(document).on('click', '.edit-btn', function() {
                var sdpId = $(this).data('id');
                $('#editSdpId').val(sdpId);
                $('#editObjective').val($(this).data('objectives_id'));
                $('#editKpi').val($(this).data('kpi'));
                $('#editInitiatives').val($(this).data('initiatives'));
                
                // Load existing responsibilities
                $.ajax({
                    url: 'get_responsibilities.php',
                    type: 'POST',
                    data: { sdp_id: sdpId },
                    dataType: 'json',
                    success: function(response) {
                        // Uncheck all first
                        $('#editSdpModal input[name="responsibilities[]"]').prop('checked', false);
                        // Check the ones that exist
                        if (response.responsibilities) {
                            response.responsibilities.forEach(function(r_matrix_id) {
                                $('#editOffice' + r_matrix_id).prop('checked', true);
                            });
                        }
                    }
                });

                // Load existing targets
                $.ajax({
                    url: 'get_targets.php',
                    type: 'POST',
                    data: { sdp_id: sdpId },
                    dataType: 'json',
                    success: function(response) {
                        $('#editTargetsContainer').empty();
                        editTargetRowCounter = 0;
                        
                        if (response.targets && response.targets.length > 0) {
                            response.targets.forEach(function(target) {
                                const targetRow = `
                                    <div class="target-row border rounded p-3 mb-2">
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <label class="form-label small">Quarter</label>
                                                <select class="form-select form-select-sm" name="targets[${editTargetRowCounter}][quarter]">
                                                    <option value="">Select</option>
                                                    <option value="Q1" ${target.quarter === 'Q1' ? 'selected' : ''}>Q1</option>
                                                    <option value="Q2" ${target.quarter === 'Q2' ? 'selected' : ''}>Q2</option>
                                                    <option value="Q3" ${target.quarter === 'Q3' ? 'selected' : ''}>Q3</option>
                                                    <option value="Q4" ${target.quarter === 'Q4' ? 'selected' : ''}>Q4</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Year</label>
                                                <input type="number" class="form-control form-control-sm" name="targets[${editTargetRowCounter}][year]" min="2020" max="2050" value="${target.year}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Value Type</label>
                                                <select class="form-select form-select-sm" name="targets[${editTargetRowCounter}][value_type]">
                                                    <option value="Other" ${target.value_type === 'Other' ? 'selected' : ''}>Other</option>
                                                    <option value="Percentage" ${target.value_type === 'Percentage' ? 'selected' : ''}>Percentage</option>
                                                    <option value="Count" ${target.value_type === 'Count' ? 'selected' : ''}>Count</option>
                                                    <option value="Index" ${target.value_type === 'Index' ? 'selected' : ''}>Index</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Target Value</label>
                                                <input type="number" step="0.01" class="form-control form-control-sm" name="targets[${editTargetRowCounter}][target_value]" value="${target.target_value}" placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Description</label>
                                                <input type="text" class="form-control form-control-sm" name="targets[${editTargetRowCounter}][description]" value="${target.description || ''}" placeholder="Optional">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger mt-2 remove-target-btn">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>
                                `;
                                $('#editTargetsContainer').append(targetRow);
                                editTargetRowCounter++;
                            });
                        } else {
                            // Add at least one empty target row
                            $('#editTargetRowBtn').click();
                        }
                        updateRemoveButtons('#editTargetsContainer');
                    }
                });
            });

            // Delete SDP - Populate Modal
            $(document).on('click', '.delete-btn', function() {
                $('#deleteSdpId').val($(this).data('id'));
                var kpi = $(this).data('kpi');
                $('#deleteSdpConfirmationText').html('Are you sure you want to delete SDP with KPI: <strong>' + kpi + '</strong>?<br><small class="text-danger">This will also delete all related targets and accomplishments.</small>');
            });

            // View Targets - Load targets for SDP
            $(document).on('click', '.view-targets-btn', function() {
                var sdpId = $(this).data('id');
                var kpi = $(this).data('kpi');
                
                $('#viewTargetsKpi').text(kpi);
                $('#viewTargetsContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                
                // Load targets via AJAX
                $.ajax({
                    url: 'get_targets.php',
                    type: 'POST',
                    data: { sdp_id: sdpId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.targets && response.targets.length > 0) {
                            var html = '<table class="table table-bordered table-striped">';
                            html += '<thead><tr>';
                            html += '<th>Quarter</th>';
                            html += '<th>Year</th>';
                            html += '<th>Value Type</th>';
                            html += '<th>Target Value</th>';
                            html += '<th>Description</th>';
                            html += '</tr></thead><tbody>';
                            
                            response.targets.forEach(function(target) {
                                html += '<tr>';
                                html += '<td>' + (target.quarter || '<span class="text-muted">-</span>') + '</td>';
                                html += '<td>' + target.year + '</td>';
                                html += '<td><span class="badge bg-info">' + target.value_type + '</span></td>';
                                html += '<td><strong>' + parseFloat(target.target_value).toFixed(2) + '</strong></td>';
                                html += '<td>' + (target.description || '<span class="text-muted">No description</span>') + '</td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody></table>';
                            $('#viewTargetsContent').html(html);
                        } else {
                            $('#viewTargetsContent').html('<div class="alert alert-info"><i class="fas fa-info-circle"></i> No targets have been set for this SDP yet.</div>');
                        }
                    },
                    error: function() {
                        $('#viewTargetsContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error loading targets. Please try again.</div>');
                    }
                });
            });

            function checkDuplicate(field, value, sdpId, errorSelector, inputSelector, submitSelector) {
                const spinner = $(inputSelector).next('.input-group-text');
                spinner.show();

                $.ajax({
                    url: 'check_duplicate.php',
                    type: 'POST',
                    data: {
                        field: field,
                        value: value,
                        sdp_id: sdpId
                    },
                    dataType: 'json',
                    success: function(response) {
                        const errorEl = $(errorSelector);
                        const inputEl = $(inputSelector);
                        const submitBtn = $(submitSelector);

                        if (response.duplicate) {
                            inputEl.addClass('is-invalid');
                            errorEl.text('This ' + field + ' is already taken.');
                            submitBtn.prop('disabled', true);
                        } else {
                            inputEl.removeClass('is-invalid');
                            errorEl.text('');
                            
                            const modal = inputEl.closest('.modal');
                            if (modal.find('.is-invalid').length === 0) {
                                submitBtn.prop('disabled', false);
                            }
                        }
                    },
                    error: function() {
                        const errorEl = $(errorSelector);
                        const inputEl = $(inputSelector);
                        errorEl.text('Error checking for duplicates.');
                        inputEl.addClass('is-invalid');
                        $(submitSelector).prop('disabled', true);
                    },
                    complete: function() {
                        spinner.hide();
                    }
                });
            }

            // Add SDP Modal Validation
            $('#addKpi').on('input', function() {
                const field = $(this).attr('name');
                const value = $(this).val();
                const capitalizedField = field.charAt(0).toUpperCase() + field.slice(1);
                if (value.length > 0) {
                    checkDuplicate(field, value, null, '#add' + capitalizedField + 'Error', '#add' + capitalizedField, '#addSdpSubmit');
                } else {
                    $(this).removeClass('is-invalid');
                    if ($('#addSdpModal').find('.is-invalid').length === 0) {
                        $('#addSdpSubmit').prop('disabled', false);
                    }
                }
            });

            // Edit SDP Modal Validation
            $('#editKpi').on('input', function() {
                const field = $(this).attr('name');
                const value = $(this).val();
                const sdpId = $('#editSdpId').val();
                const capitalizedField = field.charAt(0).toUpperCase() + field.slice(1);
                if (value.length > 0) {
                    checkDuplicate(field, value, sdpId, '#edit' + capitalizedField + 'Error', '#edit' + capitalizedField, '#editSdpSubmit');
                } else {
                    $(this).removeClass('is-invalid');
                    if ($('#editSdpModal').find('.is-invalid').length === 0) {
                        $('#editSdpSubmit').prop('disabled', false);
                    }
                }
            });

            // Reset state on modal close
            $('#addSdpModal, #editSdpModal').on('hidden.bs.modal', function () {
                $(this).find('input.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');
                $(this).find('button[type="submit"]').prop('disabled', false);
                $(this).find('input[type="checkbox"]').prop('checked', false);
                
                // Reset targets for add modal
                if ($(this).attr('id') === 'addSdpModal') {
                    $('#targetsContainer').html(`
                        <div class="target-row border rounded p-3 mb-2">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label small">Quarter</label>
                                    <select class="form-select form-select-sm" name="targets[0][quarter]">
                                        <option value="">Select</option>
                                        <option value="Q1">Q1</option>
                                        <option value="Q2">Q2</option>
                                        <option value="Q3">Q3</option>
                                        <option value="Q4">Q4</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Year</label>
                                    <input type="number" class="form-control form-control-sm" name="targets[0][year]" min="2020" max="2050" value="<?php echo date('Y'); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Value Type</label>
                                    <select class="form-select form-select-sm" name="targets[0][value_type]">
                                        <option value="Other">Other</option>
                                        <option value="Percentage">Percentage</option>
                                        <option value="Count">Count</option>
                                        <option value="Index">Index</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Target Value</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" name="targets[0][target_value]" placeholder="0.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Description</label>
                                    <input type="text" class="form-control form-control-sm" name="targets[0][description]" placeholder="Optional">
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger mt-2 remove-target-btn" style="display: none;">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                    `);
                    targetRowCounter = 1;
                    $('#addSdpForm')[0].reset();
                }
            });

            // AJAX Form Submission for Add SDP
            $('#addSdpForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $('#addSdpSubmit');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#addSdpModal').modal('hide');
                            showToast('success', response.message);
                            $('#sdpTable').DataTable().ajax.reload(null, false);
                            $('#addSdpForm')[0].reset();
                            $('#addTargetsContainer').empty();
                            targetRowCounter = 1;
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Save SDP');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while creating the SDP';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Save SDP');
                    }
                });
            });

            // AJAX Form Submission for Edit SDP
            $('#editSdpForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $('#editSdpSubmit');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#editSdpModal').modal('hide');
                            showToast('success', response.message);
                            $('#sdpTable').DataTable().ajax.reload(null, false);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Update SDP');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while updating the SDP';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Update SDP');
                    }
                });
            });

            // AJAX Form Submission for Delete SDP
            $('#deleteSdpForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#deleteSdpModal').modal('hide');
                            showToast('success', response.message);
                            $('#sdpTable').DataTable().ajax.reload(null, false);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Delete');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while deleting the SDP';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Delete');
                    }
                });
            });

            // Show toast notification if there are any session messages (for page loads)
            <?php
            if (isset($_SESSION['success'])) {
                echo "showToast('success', '{$_SESSION['success']}');";
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo "showToast('error', '{$_SESSION['error']}');";
                unset($_SESSION['error']);
            }
            ?>

            function showToast(type, message) {
                var toastLiveExample = document.getElementById('liveToast');
                var toastBody = toastLiveExample.querySelector('.toast-body');
                
                var toastHeader = toastLiveExample.querySelector('.toast-header');
                toastHeader.classList.remove('bg-success', 'bg-danger', 'text-white');
                if (type === 'success') {
                    toastHeader.classList.add('bg-success', 'text-white');
                } else {
                    toastHeader.classList.add('bg-danger', 'text-white');
                }

                toastBody.textContent = message;
                var toast = new bootstrap.Toast(toastLiveExample);
                toast.show();
            }
        });
    </script>
    
  </body>
  <!--end::Body-->
</html>
