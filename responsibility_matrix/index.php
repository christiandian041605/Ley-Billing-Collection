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
include "responsibility_matrix.php";

$database = new Database();
$db = $database->getConnection();

$rm = new ResponsibilityMatrix($db);

// Fetch all
$stmt = $rm->read();
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
              <div class="col-sm-6"><h3 class="mb-0">Responsibility Matrix</h3></div>
            </div>
          </div>
        </div>
        <div class="app-content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <div class="card card-primary card-outline">
                  <div class="card-header justify-content-between border-0">
                    <button type="button" id="openAddRmBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Office/Unit</button>
                  </div>
                  <div class="card-body">
                    <table id="rmTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Office/Unit</th>
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
              <h5 class="modal-title" id="addModalLabel">Add New Office/Unit</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRMForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                  <label for="addOfficeUnit" class="form-label">Office/Unit</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="addOfficeUnit" name="office_unit" required>
                    <span class="input-group-text" id="addOfficeUnitSpinner" style="display: none;"><span class="spinner-border spinner-border-sm"></span></span>
                  </div>
                  <div id="addOfficeUnitError" class="invalid-feedback"></div>
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
              <h5 class="modal-title" id="editModalLabel">Edit Office/Unit</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRMForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="editRMatrixId" name="r_matrix_id">
                <input type="hidden" name="action" value="update">
                <div class="mb-3">
                  <label for="editOfficeUnit" class="form-label">Office/Unit</label>
                   <div class="input-group">
                    <input type="text" class="form-control" id="editOfficeUnit" name="office_unit" required>
                    <span class="input-group-text" id="editOfficeUnitSpinner" style="display: none;"><span class="spinner-border spinner-border-sm"></span></span>
                  </div>
                  <div id="editOfficeUnitError" class="invalid-feedback"></div>
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
            <form id="deleteRMForm">
                <div class="modal-body">
                    <p id="deleteConfirmationText"></p>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" id="deleteRMatrixId" name="r_matrix_id">
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
            $('#rmTable').DataTable({
                "ajax": "get_responsibilities.php",
                "processing": true,
                "columns": [
                    { "data": 0 }, // Office/Unit
                    { "data": 1, "orderable": false } // Actions
                ]
            });

            $(document).on('click', '.edit-btn', function() {
                $('#editRMatrixId').val($(this).data('id'));
                $('#editOfficeUnit').val($(this).data('office_unit'));
            });

            $(document).on('click', '.delete-btn', function() {
                $('#deleteRMatrixId').val($(this).data('id'));
                var officeUnit = $(this).data('office_unit');
                $('#deleteConfirmationText').html('Are you sure you want to delete <strong>' + officeUnit + '</strong>?');
            });

            function checkDuplicate(field, value, r_matrix_id, errorSelector, inputSelector, submitSelector) {
                const spinner = $(inputSelector).next('.input-group-text');
                spinner.show();

                $.ajax({
                    url: 'check_duplicate.php',
                    type: 'POST',
                    data: {
                        field: field,
                        value: value,
                        r_matrix_id: r_matrix_id
                    },
                    dataType: 'json',
                    success: function(response) {
                        const errorEl = $(errorSelector);
                        const inputEl = $(inputSelector);
                        const submitBtn = $(submitSelector);

                        if (response.duplicate) {
                            inputEl.addClass('is-invalid');
                            errorEl.text('This ' + field.replace('_', ' ') + ' is already taken.');
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

            $('#addOfficeUnit').on('input', function() {
                const field = $(this).attr('name');
                const value = $(this).val();
                if (value.length > 0) {
                    checkDuplicate(field, value, null, '#addOfficeUnitError', '#addOfficeUnit', '#addSubmit');
                } else {
                    $(this).removeClass('is-invalid');
                    if ($('#addModal').find('.is-invalid').length === 0) {
                        $('#addSubmit').prop('disabled', false);
                    }
                }
            });

            $('#editOfficeUnit').on('input', function() {
                const field = $(this).attr('name');
                const value = $(this).val();
                const r_matrix_id = $('#editRMatrixId').val();
                if (value.length > 0) {
                    checkDuplicate(field, value, r_matrix_id, '#editOfficeUnitError', '#editOfficeUnit', '#editSubmit');
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
                    $('#addRMForm')[0].reset();
                }
            });

            // AJAX Form Submission for Add Office/Unit
            $('#addRMForm').on('submit', function(e) {
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
                            $('#rmTable').DataTable().ajax.reload(null, false);
                            $('#addRMForm')[0].reset();
                            // Return focus to add button for accessibility
                            setTimeout(function(){ $('#openAddRmBtn').focus(); }, 10);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Save');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while creating the office/unit';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Save');
                    }
                });
            });

            // AJAX Form Submission for Edit Office/Unit
            $('#editRMForm').on('submit', function(e) {
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
                            $('#rmTable').DataTable().ajax.reload(null, false);
                            setTimeout(function(){ $('#openAddRmBtn').focus(); }, 10);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Update');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while updating the office/unit';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Update');
                    }
                });
            });

            // AJAX Form Submission for Delete Office/Unit
            $('#deleteRMForm').on('submit', function(e) {
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
                            $('#rmTable').DataTable().ajax.reload(null, false);
                            setTimeout(function(){ $('#openAddRmBtn').focus(); }, 10);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Delete');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while deleting the office/unit';
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
