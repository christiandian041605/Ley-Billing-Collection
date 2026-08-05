<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
  header("Location: ../login/");
  exit();
}

// Check if user is Admin or Encoder
if (!in_array($_SESSION['role'], ['Admin', 'Encoder'])) {
  header("Location: ../dashboard/");
  exit();
}

include "../config/app.php";
?>
<!doctype html>
<html lang="en">

<!--begin::Head-->
<?php include "../header.php"; ?>
<style>
  .modal-backdrop {
    background-color: transparent !important;
  }
</style>
<!--end::Head-->

<!--begin::Body-->

<body class="layout-fixed bg-body-tertiary">
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
              <h3 class="mb-0">Banks & Branches</h3>
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
          <!--begin::Row-->
          <div class="row">
            <div class="col-12">
              <!-- Default box -->
              <div class="card card-primary card-outline">
                <div class="card-header justify-content-between border-0">
                  <!-- this button will call the add bank modal -->
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addBankModal">Add Bank</button>
                  <div class="card-tools">

                  </div>
                </div>
                <div class="card-body">
                  <table id="banksTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Bank Name</th>
                        <th>Branch</th>
                        <th>Bank Code</th>
                        <th>Status</th>
                        <th>Created</th>
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

    <!-- Add Bank Modal -->
    <div class="modal fade" id="addBankModal" tabindex="-1" aria-labelledby="addBankModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addBankModalLabel">Add New Bank</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="addBankForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="create">

              <div class="mb-3">
                <label for="addBankName" class="form-label">Bank Name *</label>
                <input type="text" class="form-control" id="addBankName" name="bank_name" required>
                <small class="form-text text-muted">e.g., BDO, BPI, Metrobank</small>
              </div>

              <div class="mb-3">
                <label for="addBranchName" class="form-label">Branch Name *</label>
                <input type="text" class="form-control" id="addBranchName" name="branch_name" required>
                <small class="form-text text-muted">e.g., Makati Main, Quezon City</small>
              </div>

              <div class="mb-3">
                <label for="addBankCode" class="form-label">Bank Code</label>
                <input type="text" class="form-control" id="addBankCode" name="bank_code">
                <small class="form-text text-muted">Optional: e.g., BDO001, BPI002</small>
              </div>

              <div class="mb-3">
                <label for="addStatus" class="form-label">Status</label>
                <select class="form-select" id="addStatus" name="status" required>
                  <option value="Active" selected>Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="addBankSubmit" class="btn btn-primary">Save Bank</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Bank Modal -->
    <div class="modal fade" id="editBankModal" tabindex="-1" aria-labelledby="editBankModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editBankModalLabel">Edit Bank</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="editBankForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" id="editBankId" name="id">
              <input type="hidden" name="action" value="update">

              <div class="mb-3">
                <label for="editBankName" class="form-label">Bank Name *</label>
                <input type="text" class="form-control" id="editBankName" name="bank_name" required>
              </div>

              <div class="mb-3">
                <label for="editBranchName" class="form-label">Branch Name *</label>
                <input type="text" class="form-control" id="editBranchName" name="branch_name" required>
              </div>

              <div class="mb-3">
                <label for="editBankCode" class="form-label">Bank Code</label>
                <input type="text" class="form-control" id="editBankCode" name="bank_code">
              </div>

              <div class="mb-3">
                <label for="editStatus" class="form-label">Status</label>
                <select class="form-select" id="editStatus" name="status" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="editBankSubmit" class="btn btn-primary">Update Bank</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Bank Modal -->
    <div class="modal fade" id="deleteBankModal" tabindex="-1" aria-labelledby="deleteBankModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteBankModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="deleteBankForm">
            <div class="modal-body">
              <p id="deleteBankConfirmationText"></p>
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" id="deleteBankId" name="id">
              <input type="hidden" name="action" value="delete">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" id="deleteBankSubmit" class="btn btn-danger">Delete</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Notification</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">

        </div>
      </div>
    </div>
    <!--end::Footer-->

    <!--begin::Script Includes-->
    <?php include "../script.php"; ?>
    <!--end::Script Includes-->

    <script>
      $(document).ready(function () {
        // Ensure modal backdrops are removed and body state is cleaned up when ANY modal is closed
        $(document).on('hidden.bs.modal', '.modal', function () {
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
        });

        // Initialize DataTable
        let table = $('#banksTable').DataTable({
          ajax: {
            url: 'get_banks.php',
            dataSrc: 'data'
          },
          responsive: true,
          order: [[0, 'asc']]
        });

        // Show Add Bank Modal
        $('#addBankBtn').on('click', function () {
          $('#addBankForm')[0].reset();
          let modal = new bootstrap.Modal(document.getElementById('addBankModal'));
          modal.show();
        });

        // Add Bank Form Submit
        $('#addBankForm').on('submit', function (e) {
          e.preventDefault();
          const submitBtn = $('#addBankSubmit');
          submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

          $.ajax({
            url: 'process.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
              if (response.success) {
                $('#addBankModal').modal('hide');

                setTimeout(function () {
                  $('.modal-backdrop').remove();
                  $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
                }, 200);

                showToast('success', response.message || 'Bank added successfully');
                $('#addBankForm')[0].reset();
                $('#banksTable').DataTable().ajax.reload(null, false);
              } else {
                showToast('error', response.message || 'Failed to add bank');
              }
              submitBtn.prop('disabled', false).html('Save Bank');
            },
            error: function (xhr) {
              let errorMsg = 'Failed to add bank. Please try again.';
              try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || errorMsg;
              } catch (e) { }
              showToast('error', errorMsg);
              submitBtn.prop('disabled', false).html('Save Bank');
            }
          });
        });

        // Edit Button Click
        $(document).on('click', '.editBtn', function () {
          const id = $(this).data('id');
          const btn = $(this);
          btn.prop('disabled', true);

          $.ajax({
            url: 'process.php',
            type: 'POST',
            data: { action: 'get', id: id, csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' },
            dataType: 'json',
            success: function (response) {
              if (response.success && response.data) {
                const bank = response.data;
                $('#editBankId').val(bank.id);
                $('#editBankName').val(bank.bank_name || '');
                $('#editBranchName').val(bank.branch || '');
                $('#editBankCode').val(bank.bank_code || '');
                $('#editStatus').val(bank.status || 'Active');
                $('#editBankModal').modal('show');
              } else {
                showToast('error', 'Unable to load bank details');
              }
              btn.prop('disabled', false);
            },
            error: function () {
              showToast('error', 'Failed to load bank details');
              btn.prop('disabled', false);
            }
          });
        });

        // Edit Bank Form Submit
        $('#editBankForm').on('submit', function (e) {
          e.preventDefault();
          const submitBtn = $('#editBankSubmit');
          submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

          $.ajax({
            url: 'process.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
              if (response.success) {
                $('#editBankModal').modal('hide');

                setTimeout(function () {
                  $('.modal-backdrop').remove();
                  $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
                }, 200);

                showToast('success', response.message || 'Bank updated successfully');
                $('#banksTable').DataTable().ajax.reload(null, false);
              } else {
                showToast('error', response.message || 'Failed to update bank');
              }
              submitBtn.prop('disabled', false).html('Update Bank');
            },
            error: function (xhr) {
              let errorMsg = 'Failed to update bank. Please try again.';
              try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || errorMsg;
              } catch (e) { }
              showToast('error', errorMsg);
              submitBtn.prop('disabled', false).html('Update Bank');
            }
          });
        });

        // Delete Button Click
        $(document).on('click', '.deleteBtn', function () {
          const id = $(this).data('id');
          $('#deleteBankId').val(id);
          $('#deleteBankConfirmationText').text('Are you sure you want to delete this bank? This action cannot be undone.');
          $('#deleteBankModal').modal('show');
        });

        // Delete Bank Form Submit
        $('#deleteBankForm').on('submit', function (e) {
          e.preventDefault();
          const submitBtn = $(this).find('button[type="submit"]');
          submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

          $.ajax({
            url: 'process.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
              if (response.success) {
                $('#deleteBankModal').modal('hide');

                setTimeout(function () {
                  $('.modal-backdrop').remove();
                  $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
                }, 200);

                showToast('success', response.message || 'Bank deleted successfully');
                $('#banksTable').DataTable().ajax.reload(null, false);
              } else {
                showToast('error', response.message || 'Failed to delete bank');
              }
              submitBtn.prop('disabled', false).html('Delete');
            },
            error: function (xhr) {
              let errorMsg = 'Failed to delete bank. Please try again.';
              try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || errorMsg;
              } catch (e) { }
              showToast('error', errorMsg);
              submitBtn.prop('disabled', false).html('Delete');
            }
          });
        });

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