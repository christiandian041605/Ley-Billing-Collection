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
include "objective.php";

$database = new Database();
$db = $database->getConnection();

$objective = new Objective($db);

$stmt = $objective->read();
?>
<!doctype html>
<html lang="en">

  <?php include "../header.php"; ?>

  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

      <?php include "../navbar.php"; ?>
      <?php include "../sidebar.php"; ?>

      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Objectives</h3></div>
            </div>
          </div>
        </div>
        <div class="app-content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header justify-content-between border-0">
                    <button type="button" id="openAddObjectiveBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Objective</button>
                  </div>
                  <div class="card-body">
                    <table id="objectivesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Objective Details</th>
                                <th>Focus Area</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by DataTables AJAX -->
                        </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Add Modal -->
      <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addModalLabel">Add New Objective</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addObjectiveForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                  <label for="addObjectivesDetails" class="form-label">Objective Details</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="addObjectivesDetails" name="objectives_details" required>
                    <span class="input-group-text" id="addObjectivesDetailsSpinner" style="display: none;"><span class="spinner-border spinner-border-sm"></span></span>
                  </div>
                  <div id="addObjectivesDetailsError" class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                  <label for="addFocusArea" class="form-label">Focus Area</label>
                  <input type="text" class="form-control" id="addFocusArea" name="focus_area">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="addSubmit" class="btn btn-primary">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editModalLabel">Edit Objective</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editObjectiveForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="editObjectivesId" name="objectives_id">
                <input type="hidden" name="action" value="update">
                <div class="mb-3">
                  <label for="editObjectivesDetails" class="form-label">Objective Details</label>
                   <div class="input-group">
                    <input type="text" class="form-control" id="editObjectivesDetails" name="objectives_details" required>
                    <span class="input-group-text" id="editObjectivesDetailsSpinner" style="display: none;"><span class="spinner-border spinner-border-sm"></span></span>
                  </div>
                  <div id="editObjectivesDetailsError" class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                  <label for="editFocusArea" class="form-label">Focus Area</label>
                  <input type="text" class="form-control" id="editFocusArea" name="focus_area">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="editSubmit" class="btn btn-primary">Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Delete Modal -->
      <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteObjectiveForm">
                <div class="modal-body">
                    <p id="deleteConfirmationText"></p>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" id="deleteObjectivesId" name="objectives_id">
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

      <?php include "../footer.php"; ?>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Notification</strong>
          <small>Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
        </div>
      </div>
    </div>

    <?php include "../script.php"; ?>
    <script>
        $(document).ready(function() {
            $('#objectivesTable').DataTable({
                "ajax": "get_objectives.php",
                "processing": true,
                "columns": [
                    { "data": 0 }, // Objectives Details
                    { "data": 1 }, // Focus Area
                    { "data": 2, "orderable": false } // Actions
                ]
            });

            $(document).on('click', '.edit-btn', function() {
                $('#editObjectivesId').val($(this).data('id'));
                $('#editObjectivesDetails').val($(this).data('details'));
                $('#editFocusArea').val($(this).data('focus_area'));
            });

            $(document).on('click', '.delete-btn', function() {
                $('#deleteObjectivesId').val($(this).data('id'));
                var details = $(this).data('details');
                $('#deleteConfirmationText').html('Are you sure you want to delete <strong>' + details + '</strong>?');
            });

            function checkDuplicate(field, value, objectives_id, errorSelector, inputSelector, submitSelector) {
                const spinner = $(inputSelector).next('.input-group-text');
                spinner.show();

                $.ajax({
                    url: 'check_duplicate.php',
                    type: 'POST',
                    data: {
                        field: field,
                        value: value,
                        objectives_id: objectives_id
                    },
                    dataType: 'json',
                    success: function(response) {
                        const errorEl = $(errorSelector);
                        const inputEl = $(inputSelector);
                        const submitBtn = $(submitSelector);

                        if (response.duplicate) {
                            inputEl.addClass('is-invalid');
                            errorEl.text('This objective is already taken.');
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

            $('#addObjectivesDetails').on('input', function() {
                const field = $(this).attr('name');
                const value = $(this).val();
                if (value.length > 0) {
                    checkDuplicate(field, value, null, '#addObjectivesDetailsError', '#addObjectivesDetails', '#addSubmit');
                } else {
                    $(this).removeClass('is-invalid');
                    if ($('#addModal').find('.is-invalid').length === 0) {
                        $('#addSubmit').prop('disabled', false);
                    }
                }
            });

            $('#editObjectivesDetails').on('input', function() {
                const field = $(this).attr('name');
                const value = $(this).val();
                const objectives_id = $('#editObjectivesId').val();
                if (value.length > 0) {
                    checkDuplicate(field, value, objectives_id, '#editObjectivesDetailsError', '#editObjectivesDetails', '#editSubmit');
                } else {
                    $(this).removeClass('is-invalid');
                    if ($('#editModal').find('.is-invalid').length === 0) {
                        $('#editSubmit').prop('disabled', false);
                    }
                }
            });

            $('#addModal, #editModal').on('hidden.bs.modal', function () {
                $(this).find('input.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');
                $(this).find('button[type="submit"]').prop('disabled', false);
                if ($(this).attr('id') === 'addModal') {
                    $('#addObjectiveForm')[0].reset();
                }
            });

            // AJAX Form Submission for Add Objective
            $('#addObjectiveForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $('#addSubmit');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
          success: function(response) {
            if (response.success) {
                            $('#addModal').modal('hide');
                            showToast('success', response.message);
                            $('#objectivesTable').DataTable().ajax.reload(null, false);
                            $('#addObjectiveForm')[0].reset();
                            // Move focus back to the Add button for accessibility
                            setTimeout(function(){ $('#openAddObjectiveBtn').focus(); }, 10);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Save');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while creating the objective';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Save');
                    }
                });
            });

            // AJAX Form Submission for Edit Objective
            $('#editObjectiveForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $('#editSubmit');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#editModal').modal('hide');
                            showToast('success', response.message);
                            $('#objectivesTable').DataTable().ajax.reload(null, false);
                            // Return focus to Add button for accessibility
                            setTimeout(function(){ $('#openAddObjectiveBtn').focus(); }, 10);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Update');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while updating the objective';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Update');
                    }
                });
            });

            // AJAX Form Submission for Delete Objective
            $('#deleteObjectiveForm').on('submit', function(e) {
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
                            $('#deleteModal').modal('hide');
                            showToast('success', response.message);
                            $('#objectivesTable').DataTable().ajax.reload(null, false);
                            // Return focus to Add button for accessibility
                            setTimeout(function(){ $('#openAddObjectiveBtn').focus(); }, 10);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Delete');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while deleting the objective';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Delete');
                    }
                });
            });

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
</html>
