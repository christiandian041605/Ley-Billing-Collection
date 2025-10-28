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
include "user.php";

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

function getRoleName($role) {
    switch ($role) {
        case 'Admin':
            return '<span class="badge bg-danger">Admin</span>';
        case 'Faculty':
            return '<span class="badge bg-info">Faculty</span>';
        case 'Staff':
            return '<span class="badge bg-primary">Staff</span>';
        case 'Dean':
            return '<span class="badge bg-success">Dean</span>';
        case 'Director':
            return '<span class="badge bg-warning">Director</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

function generateAvatar($name) {
    if (empty($name)) {
        $name = '';
    }
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        if (!empty($w)) {
            $initials .= $w[0];
        }
    }
    $initials = strtoupper(substr($initials, 0, 2));
    $hash = md5($name);
    $color = substr($hash, 0, 6);
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    $textColor = ($yiq >= 128) ? 'black' : 'white';

    return '<div class="avatar" style="background-color: #'.$color.'; color: '.$textColor.';">'.$initials.'</div>';
}

// Fetch offices for dropdown
$office_stmt = $db->query("SELECT * FROM tbl_responsibility_matrix ORDER BY office_unit");
$offices = $office_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all users
$stmt = $user->read();
?>
<!doctype html>
<html lang="en">

  <!--begin::Head-->
  <?php include "../header.php"; ?>
  <style>
    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        float: left;
        margin-right: 10px;
    }
  </style>
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
              <div class="col-sm-6"><h3 class="mb-0">Users</h3></div>
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
                    <!-- this button will call the add user modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
                    <div class="card-tools">
                      
                    </div>
                  </div>
                  <div class="card-body">
                    <table id="usersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Position</th>
                                <th>Office</th>
                                <th>Role</th>
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

      <!-- Add User Modal -->
      <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                  <label for="addFullName" class="form-label">Full Name</label>
                  <input type="text" class="form-control" id="addFullName" name="full_name" required>
                </div>

                <div class="mb-3">
                  <label for="addUsername" class="form-label">Username</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="addUsername" name="username" required>
                    <span class="input-group-text" id="addUsernameSpinner" style="display: none;"><span class="spinner-border spinner-border-sm"></span></span>
                  </div>
                  <div id="addUsernameError" class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                  <label for="addPosition" class="form-label">Position</label>
                  <input type="text" class="form-control" id="addPosition" name="position">
                </div>
                <div class="mb-3">
                  <label for="addOffice" class="form-label">Office</label>
                  <select class="form-select" id="addOffice" name="r_matrix_id">
                    <option value="">Select Office</option>
                    <?php foreach ($offices as $office_row): ?>
                      <option value="<?php echo $office_row['r_matrix_id']; ?>"><?php echo htmlspecialchars($office_row['office_unit']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="addPassword" class="form-label">Password</label>
                  <input type="password" class="form-control" id="addPassword" name="password" required>
                </div>
                <div class="mb-3">
                  <label for="addRole" class="form-label">Role</label>
                  <select class="form-select" id="addRole" name="role" required>
                    <option value="Admin">Admin</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Staff" selected>Staff</option>
                    <option value="Dean">Dean</option>
                    <option value="Director">Director</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="addUserSubmit" class="btn btn-primary">Save User</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Edit User Modal -->
      <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="editUserId" name="user_id">
                <input type="hidden" name="action" value="update">
                <div class="mb-3">
                  <label for="editFullName" class="form-label">Full Name</label>
                  <input type="text" class="form-control" id="editFullName" name="full_name" required>
                </div>

                <div class="mb-3">
                  <label for="editUsername" class="form-label">Username</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="editUsername" name="username" required>
                    <span class="input-group-text" id="editUsernameSpinner" style="display: none;"><span class="spinner-border spinner-border-sm"></span></span>
                  </div>
                  <div id="editUsernameError" class="invalid-feedback"></div>
                </div>
                <div class="mb-3">
                  <label for="editPosition" class="form-label">Position</label>
                  <input type="text" class="form-control" id="editPosition" name="position">
                </div>
                <div class="mb-3">
                  <label for="editOffice" class="form-label">Office</label>
                  <select class="form-select" id="editOffice" name="r_matrix_id">
                    <option value="">Select Office</option>
                    <?php foreach ($offices as $office_row): ?>
                      <option value="<?php echo $office_row['r_matrix_id']; ?>"><?php echo htmlspecialchars($office_row['office_unit']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="editPassword" class="form-label">New Password (leave blank to keep current)</label>
                  <input type="password" class="form-control" id="editPassword" name="password">
                </div>
                <div class="mb-3">
                  <label for="editRole" class="form-label">Role</label>
                  <select class="form-select" id="editRole" name="role" required>
                    <option value="Admin">Admin</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Staff">Staff</option>
                    <option value="Dean">Dean</option>
                    <option value="Director">Director</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="editUserSubmit" class="btn btn-primary">Update User</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Delete User Modal -->
      <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteUserForm">
                <div class="modal-body">
                    <p id="deleteUserConfirmationText"></p>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" id="deleteUserId" name="user_id">
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
            $('#usersTable').DataTable({
                "ajax": "get_users.php",
                "processing": true,
                "columns": [
                    { "data": 0 }, // Name with avatar
                    { "data": 1 }, // Position
                    { "data": 2 }, // Office
                    { "data": 3 }, // Role
                    { "data": 4, "orderable": false } // Actions
                ]
            });

            // Edit User - Populate Modal
            $(document).on('click', '.edit-btn', function() {
                $('#editUserId').val($(this).data('id'));
                $('#editFullName').val($(this).data('full_name'));
                $('#editUsername').val($(this).data('username'));
                $('#editPosition').val($(this).data('position'));
                $('#editOffice').val($(this).data('r_matrix_id'));
                $('#editRole').val($(this).data('role'));
                $('#editPassword').val(''); // Clear password field
            });

            // Delete User - Populate Modal
            $(document).on('click', '.delete-btn', function() {
                $('#deleteUserId').val($(this).data('id'));
                var userName = $(this).data('full_name');
                $('#deleteUserConfirmationText').html('Are you sure you want to delete <strong>' + userName + '</strong>?');
            });

            function checkDuplicate(field, value, userId, errorSelector, inputSelector, submitSelector) {
                const spinner = $(inputSelector).next('.input-group-text');
                spinner.show();

                $.ajax({
                    url: 'check_duplicate.php',
                    type: 'POST',
                    data: {
                        field: field,
                        value: value,
                        user_id: userId
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

            // Add User Modal Validation
            $('#addUsername').on('input', function() {
                const field = $(this).attr('name');
                const value = $(this).val();
                const capitalizedField = field.charAt(0).toUpperCase() + field.slice(1);
                if (value.length > 0) {
                    checkDuplicate(field, value, null, '#add' + capitalizedField + 'Error', '#add' + capitalizedField, '#addUserSubmit');
                } else {
                    $(this).removeClass('is-invalid');
                    if ($('#addUserModal').find('.is-invalid').length === 0) {
                        $('#addUserSubmit').prop('disabled', false);
                    }
                }
            });

            // Edit User Modal Validation
            $('#editUsername').on('input', function() {
                const field = $(this).attr('name');
                const value = $(this).val();
                const userId = $('#editUserId').val();
                const capitalizedField = field.charAt(0).toUpperCase() + field.slice(1);
                if (value.length > 0) {
                    checkDuplicate(field, value, userId, '#edit' + capitalizedField + 'Error', '#edit' + capitalizedField, '#editUserSubmit');
                } else {
                    $(this).removeClass('is-invalid');
                    if ($('#editUserModal').find('.is-invalid').length === 0) {
                        $('#editUserSubmit').prop('disabled', false);
                    }
                }
            });

            // Reset state on modal close
            $('#addUserModal, #editUserModal').on('hidden.bs.modal', function () {
                $(this).find('input.is-invalid').removeClass('is-invalid');
                $(this).find('.invalid-feedback').text('');
                $(this).find('button[type="submit"]').prop('disabled', false);
                if ($(this).attr('id') === 'addUserModal') {
                    $('#addUserForm')[0].reset();
                }
            });

            // AJAX Form Submission for Add User
            $('#addUserForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $('#addUserSubmit');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#addUserModal').modal('hide');
                            showToast('success', response.message);
                            // Reload DataTable without page refresh
                            $('#usersTable').DataTable().ajax.reload(null, false);
                            $('#addUserForm')[0].reset();
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Save User');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while creating the user';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Save User');
                    }
                });
            });

            // AJAX Form Submission for Edit User
            $('#editUserForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $('#editUserSubmit');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#editUserModal').modal('hide');
                            showToast('success', response.message);
                            // Reload DataTable without page refresh
                            $('#usersTable').DataTable().ajax.reload(null, false);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Update User');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while updating the user';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Update User');
                    }
                });
            });

            // AJAX Form Submission for Delete User
            $('#deleteUserForm').on('submit', function(e) {
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
                            $('#deleteUserModal').modal('hide');
                            showToast('success', response.message);
                            // Reload DataTable without page refresh
                            $('#usersTable').DataTable().ajax.reload(null, false);
                        } else {
                            showToast('error', response.message);
                        }
                        submitBtn.prop('disabled', false).html('Delete');
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while deleting the user';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Delete');
                    }
                });
            });

            // Show toast notification if there are any session messages
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