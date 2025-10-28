<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

include "../config/app.php";
include_once "../config/database.php";
include_once __DIR__ . '/../helpers/csrf.php';
include "accomplishment.php";

// Ensure CSRF token exists
ensure_csrf_token();

$database = new Database();
$db = $database->getConnection();

// Fetch all SDPs for dropdown
$sdp_stmt = $db->query("SELECT sdp_id, kpi FROM tbl_sdp ORDER BY kpi");
$sdps = $sdp_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get distinct years from targets
$year_stmt = $db->query("SELECT DISTINCT year FROM tbl_target ORDER BY year DESC");
$years = $year_stmt->fetchAll(PDO::FETCH_ASSOC);
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
              <div class="col-sm-6"><h3 class="mb-0">Accomplishments</h3></div>
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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccomplishmentModal">Add Accomplishment</button>
                    <div class="card-tools">
                      
                    </div>
                  </div>
                  <div class="card-body table-responsive">
                    <table id="accomplishmentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>SDP (KPI)</th>
                                <th>Office</th>
                                <th>Target</th>
                                <th>Accomplishment</th>
                                <th>Evidence</th>
                                <th>Description</th>
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

      <!-- Add Accomplishment Modal -->
      <div class="modal fade" id="addAccomplishmentModal" tabindex="-1" aria-labelledby="addAccomplishmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addAccomplishmentModalLabel">Add New Accomplishment</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAccomplishmentForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="mb-3">
                  <label for="addSdp" class="form-label">Select SDP <span class="text-danger">*</span></label>
                  <select class="form-select" id="addSdp" name="sdp_id" required>
                    <option value="">-- Select SDP --</option>
                    <?php foreach ($sdps as $sdp): ?>
                      <option value="<?php echo $sdp['sdp_id']; ?>">
                        <?php echo htmlspecialchars($sdp['kpi']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="addYear" class="form-label">Year (Optional)</label>
                      <select class="form-select" id="addYear">
                        <option value="">-- All Years --</option>
                        <?php foreach ($years as $year): ?>
                          <option value="<?php echo $year['year']; ?>"><?php echo $year['year']; ?></option>
                        <?php endforeach; ?>
                      </select>
                      <small class="text-muted">Filter targets by year</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="addQuarter" class="form-label">Quarter (Optional)</label>
                      <select class="form-select" id="addQuarter">
                        <option value="">-- All Quarters --</option>
                        <option value="Q1">Q1</option>
                        <option value="Q2">Q2</option>
                        <option value="Q3">Q3</option>
                        <option value="Q4">Q4</option>
                      </select>
                      <small class="text-muted">Filter targets by quarter</small>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="addTarget" class="form-label">Target (Optional)</label>
                  <select class="form-select" id="addTarget" name="target_id" disabled>
                    <option value="">Select an SDP first</option>
                  </select>
                  <small class="text-muted">Select a specific target for this accomplishment</small>
                </div>

                <div class="mb-3">
                  <label for="addResponsibilityMatrix" class="form-label">Responsibility Matrix (Office) <span class="text-danger">*</span></label>
                  <select class="form-select" id="addResponsibilityMatrix" name="r_matrix_id" disabled>
                    <option value="">Select an SDP first</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="addAccomplishment" class="form-label">Accomplishment Value <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="addAccomplishment" name="accomplishment" required>
                  <small class="text-muted">Actual value achieved</small>
                </div>

                <div class="mb-3">
                  <label for="addEvidence" class="form-label">Evidence</label>
                  <textarea class="form-control" id="addEvidence" name="evidence" rows="2"></textarea>
                  <small class="text-muted">Links, documents, or proof of accomplishment</small>
                </div>

                <div class="mb-3">
                  <label for="addDescription" class="form-label">Description</label>
                  <textarea class="form-control" id="addDescription" name="description" rows="3"></textarea>
                  <small class="text-muted">Additional details about the accomplishment</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="addAccomplishmentSubmit" class="btn btn-primary">Save Accomplishment</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Edit Accomplishment Modal -->
      <div class="modal fade" id="editAccomplishmentModal" tabindex="-1" aria-labelledby="editAccomplishmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editAccomplishmentModalLabel">Edit Accomplishment</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAccomplishmentForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="editAccomplishmentId" name="accomplishment_id">
                <input type="hidden" name="action" value="update">
                
                <div class="mb-3">
                  <label for="editSdp" class="form-label">Select SDP <span class="text-danger">*</span></label>
                  <select class="form-select" id="editSdp" name="sdp_id" required>
                    <option value="">-- Select SDP --</option>
                    <?php foreach ($sdps as $sdp): ?>
                      <option value="<?php echo $sdp['sdp_id']; ?>">
                        <?php echo htmlspecialchars($sdp['kpi']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="editYear" class="form-label">Year (Optional)</label>
                      <select class="form-select" id="editYear">
                        <option value="">-- All Years --</option>
                        <?php foreach ($years as $year): ?>
                          <option value="<?php echo $year['year']; ?>"><?php echo $year['year']; ?></option>
                        <?php endforeach; ?>
                      </select>
                      <small class="text-muted">Filter targets by year</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="editQuarter" class="form-label">Quarter (Optional)</label>
                      <select class="form-select" id="editQuarter">
                        <option value="">-- All Quarters --</option>
                        <option value="Q1">Q1</option>
                        <option value="Q2">Q2</option>
                        <option value="Q3">Q3</option>
                        <option value="Q4">Q4</option>
                      </select>
                      <small class="text-muted">Filter targets by quarter</small>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="editTarget" class="form-label">Target (Optional)</label>
                  <select class="form-select" id="editTarget" name="target_id" disabled>
                    <option value="">Select an SDP first</option>
                  </select>
                  <small class="text-muted">Select a specific target for this accomplishment</small>
                </div>

                <div class="mb-3">
                  <label for="editResponsibilityMatrix" class="form-label">Responsibility Matrix (Office) <span class="text-danger">*</span></label>
                  <select class="form-select" id="editResponsibilityMatrix" name="r_matrix_id" disabled>
                    <option value="">Select an SDP first</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="editAccomplishment" class="form-label">Accomplishment Value <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="editAccomplishment" name="accomplishment" required>
                  <small class="text-muted">Actual value achieved</small>
                </div>

                <div class="mb-3">
                  <label for="editEvidence" class="form-label">Evidence</label>
                  <textarea class="form-control" id="editEvidence" name="evidence" rows="2"></textarea>
                  <small class="text-muted">Links, documents, or proof of accomplishment</small>
                </div>

                <div class="mb-3">
                  <label for="editDescription" class="form-label">Description</label>
                  <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                  <small class="text-muted">Additional details about the accomplishment</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="editAccomplishmentSubmit" class="btn btn-primary">Update Accomplishment</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Delete Accomplishment Modal -->
      <div class="modal fade" id="deleteAccomplishmentModal" tabindex="-1" aria-labelledby="deleteAccomplishmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteAccomplishmentModalLabel">Confirm Delete</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteAccomplishmentForm">
                <div class="modal-body">
                    <p id="deleteAccomplishmentConfirmationText"></p>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" id="deleteAccomplishmentId" name="accomplishment_id">
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
            $('#accomplishmentTable').DataTable({
                "ajax": "get_accomplishments.php",
                "processing": true,
                "columns": [
                    { "data": 0 }, // KPI
                    { "data": 1 }, // Office
                    { "data": 2 }, // Target
                    { "data": 3 }, // Accomplishment
                    { "data": 4 }, // Evidence
                    { "data": 5 }, // Description
                    { "data": 6, "orderable": false } // Actions
                ],
                responsive: true,
                autoWidth: false
            });

            // Function to load targets based on SDP selection
            function loadTargets(sdpId, targetSelectId, yearSelectId, quarterSelectId) {
                if (!sdpId) {
                    $(targetSelectId).html('<option value="">Select an SDP first</option>').prop('disabled', true);
                    return;
                }

                const year = $(yearSelectId).val();
                const quarter = $(quarterSelectId).val();

                $.ajax({
                    url: 'get_targets.php',
                    type: 'POST',
                    data: { sdp_id: sdpId },
                    dataType: 'json',
                    success: function(response) {
                        let options = '<option value="">-- Select Target (Optional) --</option>';
                        
                        if (response.targets && response.targets.length > 0) {
                            let filteredTargets = response.targets;
                            
                            // Filter by year if selected
                            if (year) {
                                filteredTargets = filteredTargets.filter(t => t.year == year);
                            }
                            
                            // Filter by quarter if selected
                            if (quarter) {
                                filteredTargets = filteredTargets.filter(t => t.quarter == quarter);
                            }
                            
                            filteredTargets.forEach(function(target) {
                                let label = '';
                                if (target.target_value) {
                                    label += target.target_value;
                                }
                                if (target.quarter) {
                                    label += ' (' + target.quarter + ')';
                                }
                                if (target.year) {
                                    label += ' - ' + target.year;
                                }
                                if (target.description) {
                                    label += ' | ' + target.description;
                                }
                                options += '<option value="' + target.target_id + '">' + label + '</option>';
                            });
                        }
                        
                        $(targetSelectId).html(options).prop('disabled', false);
                    },
                    error: function() {
                        $(targetSelectId).html('<option value="">Error loading targets</option>').prop('disabled', true);
                    }
                });
            }

            // Function to load responsibilities based on SDP selection
            function loadResponsibilities(sdpId, selectId) {
                if (!sdpId) {
                    $(selectId).html('<option value="">Select an SDP first</option>').prop('disabled', true);
                    return;
                }

                $.ajax({
                    url: 'get_responsibilities.php',
                    type: 'POST',
                    data: { sdp_id: sdpId },
                    dataType: 'json',
                    success: function(response) {
                        let options = '<option value="">-- Select Office --</option>';
                        
                        if (response.responsibilities && response.responsibilities.length > 0) {
                            response.responsibilities.forEach(function(resp) {
                                options += '<option value="' + resp.r_matrix_id + '">' + resp.office_unit + '</option>';
                            });
                        }
                        
                        $(selectId).html(options).prop('disabled', false);
                    },
                    error: function() {
                        $(selectId).html('<option value="">Error loading offices</option>').prop('disabled', true);
                    }
                });
            }

            // Add Modal - Load targets and responsibilities when SDP changes
            $('#addSdp').on('change', function() {
                const sdpId = $(this).val();
                loadTargets(sdpId, '#addTarget', '#addYear', '#addQuarter');
                loadResponsibilities(sdpId, '#addResponsibilityMatrix');
            });

            // Add Modal - Reload targets when year or quarter filter changes
            $('#addYear, #addQuarter').on('change', function() {
                const sdpId = $('#addSdp').val();
                if (sdpId) {
                    loadTargets(sdpId, '#addTarget', '#addYear', '#addQuarter');
                }
            });

            // Edit Modal - Load targets and responsibilities when SDP changes
            $('#editSdp').on('change', function() {
                const sdpId = $(this).val();
                loadTargets(sdpId, '#editTarget', '#editYear', '#editQuarter');
                loadResponsibilities(sdpId, '#editResponsibilityMatrix');
            });

            // Edit Modal - Reload targets when year or quarter filter changes
            $('#editYear, #editQuarter').on('change', function() {
                const sdpId = $('#editSdp').val();
                if (sdpId) {
                    loadTargets(sdpId, '#editTarget', '#editYear', '#editQuarter');
                }
            });

            // Edit Accomplishment - Populate Modal
            $(document).on('click', '.edit-btn', function() {
                var accomplishmentId = $(this).data('id');
                $('#editAccomplishmentId').val(accomplishmentId);
                $('#editSdp').val($(this).data('sdp_id')).trigger('change');
                $('#editAccomplishment').val($(this).data('accomplishment'));
                $('#editEvidence').val($(this).data('evidence'));
                $('#editDescription').val($(this).data('description'));
                
                // Wait for targets and responsibilities to load
                setTimeout(function() {
                    $('#editTarget').val($(this).data('target_id'));
                    $('#editResponsibilityMatrix').val($(this).data('r_matrix_id'));
                }.bind(this), 500);
            });

            // Delete Accomplishment - Populate Modal
            $(document).on('click', '.delete-btn', function() {
                $('#deleteAccomplishmentId').val($(this).data('id'));
                var kpi = $(this).data('kpi');
                $('#deleteAccomplishmentConfirmationText').html('Are you sure you want to delete this accomplishment for SDP: <strong>' + kpi + '</strong>?');
            });

            // Reset modal on close
            $('#addAccomplishmentModal, #editAccomplishmentModal').on('hidden.bs.modal', function () {
                $(this).find('form')[0].reset();
                $(this).find('select[name="target_id"]').html('<option value="">Select an SDP first</option>').prop('disabled', true);
                $(this).find('select[name="r_matrix_id"]').html('<option value="">Select an SDP first</option>').prop('disabled', true);
            });

            // AJAX Form Submission for Add Accomplishment
            $('#addAccomplishmentForm').on('submit', function(e) {
                e.preventDefault();
                
                // Custom validation
                if (!$('#addSdp').val()) {
                    showToast('error', 'Please select an SDP');
                    return false;
                }
                if (!$('#addResponsibilityMatrix').val()) {
                    showToast('error', 'Please select a Responsibility Matrix (Office)');
                    return false;
                }
                if (!$('#addAccomplishment').val()) {
                    showToast('error', 'Please enter an accomplishment value');
                    return false;
                }
                
                const submitBtn = $('#addAccomplishmentSubmit');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#addAccomplishmentModal').modal('hide');
                            showToast('success', response.message);
                            $('#accomplishmentTable').DataTable().ajax.reload(null, false);
                            $('#addAccomplishmentForm')[0].reset();
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Save Accomplishment');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while creating the accomplishment';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Save Accomplishment');
                    }
                });
            });

            // AJAX Form Submission for Edit Accomplishment
            $('#editAccomplishmentForm').on('submit', function(e) {
                e.preventDefault();
                
                // Custom validation
                if (!$('#editSdp').val()) {
                    showToast('error', 'Please select an SDP');
                    return false;
                }
                if (!$('#editResponsibilityMatrix').val()) {
                    showToast('error', 'Please select a Responsibility Matrix (Office)');
                    return false;
                }
                if (!$('#editAccomplishment').val()) {
                    showToast('error', 'Please enter an accomplishment value');
                    return false;
                }
                
                const submitBtn = $('#editAccomplishmentSubmit');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#editAccomplishmentModal').modal('hide');
                            showToast('success', response.message);
                            $('#accomplishmentTable').DataTable().ajax.reload(null, false);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Update Accomplishment');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while updating the accomplishment';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Update Accomplishment');
                    }
                });
            });

            // AJAX Form Submission for Delete Accomplishment
            $('#deleteAccomplishmentForm').on('submit', function(e) {
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
                            $('#deleteAccomplishmentModal').modal('hide');
                            showToast('success', response.message);
                            $('#accomplishmentTable').DataTable().ajax.reload(null, false);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Delete');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while deleting the accomplishment';
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
