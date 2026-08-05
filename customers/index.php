<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
  header("Location: ../login/");
  exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../config/app.php";
include_once "../config/database.php";
include "customer.php";

$database = new Database();
$db = $database->getConnection();

$customer = new Customer($db);

function getCustomerTypeBadge($type)
{
  switch ($type) {
    case 'Private':
      return '<span class="badge bg-primary">Private</span>';
    case 'Business':
      return '<span class="badge bg-success">Business</span>';
    case 'Government':
      return '<span class="badge bg-warning text-dark">Government</span>';
    default:
      return '<span class="badge bg-secondary">Unknown</span>';
  }
}



// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
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
              <h3 class="mb-0">Customers</h3>
            </div>
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
                  <!-- this button will call the add customer modal -->
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addCustomerModal">Add Customer</button>
                  <div class="card-tools">

                  </div>
                </div>
                <div class="card-body">
                  <table id="customersTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Business Name</th>
                        <th>Contact Info</th>
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

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="addCustomerForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="create">

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="addCustomerType" class="form-label">Customer Type</label>
                  <select class="form-select" id="addCustomerType" name="customer_type" required>
                    <option value="">Select Type</option>
                    <option value="Private">Private</option>
                    <option value="Business">Business</option>
                    <option value="Government">Government</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="addCustomerName" class="form-label">Customer Name</label>
                  <input type="text" class="form-control" id="addCustomerName" name="name" required>
                  <div id="addNameError" class="invalid-feedback"></div>
                </div>
              </div>

              <div class="mb-3">
                <label for="addBusinessName" class="form-label">Business Name</label>
                <input type="text" class="form-control" id="addBusinessName" name="business_name">
                <small class="text-muted">For Business and Government customers</small>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="addContactNumber" class="form-label">Contact Number</label>
                  <input type="text" class="form-control" id="addContactNumber" name="contact_number">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="addEmail" class="form-label">Email Address</label>
                  <input type="email" class="form-control" id="addEmail" name="email">
                </div>
              </div>

              <div class="mb-3">
                <label for="addAddress" class="form-label">Address</label>
                <textarea class="form-control" id="addAddress" name="address" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="addCustomerSubmit" class="btn btn-primary">Save Customer</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="editCustomerForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" id="editCustomerId" name="customer_id">
              <input type="hidden" name="action" value="update">

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editCustomerType" class="form-label">Customer Type</label>
                  <select class="form-select" id="editCustomerType" name="customer_type" required>
                    <option value="">Select Type</option>
                    <option value="Private">Private</option>
                    <option value="Business">Business</option>
                    <option value="Government">Government</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editCustomerName" class="form-label">Customer Name</label>
                  <input type="text" class="form-control" id="editCustomerName" name="name" required>
                  <div id="editNameError" class="invalid-feedback"></div>
                </div>
              </div>

              <div class="mb-3">
                <label for="editBusinessName" class="form-label">Business Name</label>
                <input type="text" class="form-control" id="editBusinessName" name="business_name">
                <small class="text-muted">For Business and Government customers</small>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editContactNumber" class="form-label">Contact Number</label>
                  <input type="text" class="form-control" id="editContactNumber" name="contact_number">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editEmail" class="form-label">Email Address</label>
                  <input type="email" class="form-control" id="editEmail" name="email">
                </div>
              </div>

              <div class="mb-3">
                <label for="editAddress" class="form-label">Address</label>
                <textarea class="form-control" id="editAddress" name="address" rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="editCustomerSubmit" class="btn btn-primary">Update Customer</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Customer Modal -->
    <div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteCustomerModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="deleteCustomerForm">
            <div class="modal-body">
              <p id="deleteCustomerConfirmationText"></p>
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" id="deleteCustomerId" name="customer_id">
              <input type="hidden" name="action" value="delete">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="deleteCustomerSubmit" class="btn btn-danger">Delete Customer</button>
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

  <!-- Initialize the page -->
  <script>
    $(document).ready(function () {
      // Ensure modal backdrops are removed and body state is cleaned up when ANY modal is closed
      $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
      });

      // Initialize DataTable
      const customersTable = $('#customersTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "get_customers.php",
          "type": "POST"
        },
        "columns": [
          { "data": "customer_info", "orderable": false },
          { "data": "type_badge", "orderable": false },
          { "data": "business_name" },
          { "data": "contact_info", "orderable": false },
          { "data": "actions", "orderable": false }
        ],
        "order": [[0, "asc"]],
        "pageLength": 10,
        "responsive": true,
        "language": {
          "processing": '<i class="fas fa-spinner fa-spin"></i> Loading...',
          "emptyTable": "No customers found",
          "zeroRecords": "No matching customers found"
        }
      });

      // Edit Customer - Populate Modal from data attributes
      $(document).on('click', '.edit-btn', function () {
        const customerId = $(this).data('id');
        editCustomer(customerId);
      });

      // Delete Customer - Populate Modal
      $(document).on('click', '.delete-btn', function () {
        $('#deleteCustomerId').val($(this).data('id'));
        $('#deleteCustomerConfirmationText').text(`Are you sure you want to delete customer "${$(this).data('name')}"? This action cannot be undone.`);
      });

      // Show toast notification
      function showToast(type, message) {
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';

        const toast = $(`
              <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                  <div class="d-flex">
                      <div class="toast-body">
                          <i class="bi ${iconClass} me-2"></i>${message}
                      </div>
                      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                  </div>
              </div>
          `);

        // Create toast container if it doesn't exist
        if ($('#toast-container').length === 0) {
          $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000;"></div>');
        }

        $('#toast-container').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        // Remove toast element after it's hidden
        toast.on('hidden.bs.toast', function () {
          $(this).remove();
        });
      }

      // AJAX Form Submission for Add Customer
      $('#addCustomerForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('#addCustomerSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
          url: 'process.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#addCustomerModal').modal('hide');

              // Force cleanup backdrop and body state
              setTimeout(function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
              }, 200);

              showToast('success', response.message);
              // Reload DataTable without page refresh
              $('#customersTable').DataTable().ajax.reload(null, false);
              $('#addCustomerForm')[0].reset();
            } else {
              showToast('error', response.message);
            }
            submitBtn.prop('disabled', false).html('Save Customer');
          },
          error: function (xhr) {
            let errorMsg = 'An error occurred while creating the customer';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            showToast('error', errorMsg);
            submitBtn.prop('disabled', false).html('Save Customer');
          }
        });
      });

      // AJAX Form Submission for Edit Customer
      $('#editCustomerForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('#editCustomerSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        $.ajax({
          url: 'process.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#editCustomerModal').modal('hide');

              // Force cleanup backdrop and body state
              setTimeout(function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
              }, 200);

              showToast('success', response.message);
              // Reload DataTable without page refresh
              $('#customersTable').DataTable().ajax.reload(null, false);
            } else {
              showToast('error', response.message);
            }
            submitBtn.prop('disabled', false).html('Update Customer');
          },
          error: function (xhr) {
            let errorMsg = 'An error occurred while updating the customer';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            showToast('error', errorMsg);
            submitBtn.prop('disabled', false).html('Update Customer');
          }
        });
      });

      // AJAX Form Submission for Delete Customer
      $('#deleteCustomerForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('#deleteCustomerSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        $.ajax({
          url: 'process.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#deleteCustomerModal').modal('hide');

              // Force cleanup backdrop and body state
              setTimeout(function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
              }, 200);

              showToast('success', response.message);
              // Reload DataTable without page refresh
              $('#customersTable').DataTable().ajax.reload(null, false);
            } else {
              showToast('error', response.message);
            }
            submitBtn.prop('disabled', false).html('Delete Customer');
          },
          error: function (xhr) {
            let errorMsg = 'An error occurred while deleting the customer';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            showToast('error', errorMsg);
            submitBtn.prop('disabled', false).html('Delete Customer');
          }
        });
      });

      // Reset modals on close
      $('#addCustomerModal, #editCustomerModal').on('hidden.bs.modal', function () {
        $(this).find('input.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').text('');
        $(this).find('button[type="submit"]').prop('disabled', false);
        if ($(this).attr('id') === 'addCustomerModal') {
          $('#addCustomerForm')[0].reset();
        }
      });

      // Customer type change handler
      $('#addCustomerType, #editCustomerType').on('change', function () {
        const type = $(this).val();
        const modalPrefix = $(this).attr('id').includes('add') ? 'add' : 'edit';
        const businessNameLabel = $(`label[for="${modalPrefix}BusinessName"]`);

        if (type === 'Private') {
          businessNameLabel.html('Business Name');
        } else if (type === 'Business') {
          businessNameLabel.html('Business Name');
        } else if (type === 'Government') {
          businessNameLabel.html('Department/Office Name');
        }
      });

      // Real-time duplicate check for Customer Name
      function checkDuplicate(field, value, customerId, errorSelector, inputSelector, submitSelector) {
        $.ajax({
          url: 'check_duplicate.php',
          type: 'POST',
          data: {
            field: field,
            value: value,
            customer_id: customerId,
            csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
          },
          dataType: 'json',
          success: function (response) {
            const errorEl = $(errorSelector);
            const inputEl = $(inputSelector);
            const submitBtn = $(submitSelector);

            if (response.duplicate) {
              inputEl.addClass('is-invalid');
              errorEl.text('A customer with this name already exists.');
              submitBtn.prop('disabled', true);
            } else {
              inputEl.removeClass('is-invalid');
              errorEl.text('');

              // Only re-enable if no other fields are invalid in the modal
              const modal = inputEl.closest('.modal');
              if (modal.find('.is-invalid').length === 0) {
                submitBtn.prop('disabled', false);
              }
            }
          },
          error: function () {
            // silent error
          }
        });
      }

      $('#addCustomerName').on('input', function () {
        const value = $(this).val();
        if (value.length > 0) {
          checkDuplicate('name', value, null, '#addNameError', '#addCustomerName', '#addCustomerSubmit');
        } else {
          $(this).removeClass('is-invalid');
          $('#addNameError').text('');
          $('#addCustomerSubmit').prop('disabled', false);
        }
      });

      $('#editCustomerName').on('input', function () {
        const value = $(this).val();
        const customerId = $('#editCustomerId').val();
        if (value.length > 0) {
          checkDuplicate('name', value, customerId, '#editNameError', '#editCustomerName', '#editCustomerSubmit');
        } else {
          $(this).removeClass('is-invalid');
          $('#editNameError').text('');
          $('#editCustomerSubmit').prop('disabled', false);
        }
      });
    });

    // Global functions for DataTable actions
    function editCustomer(id) {
      // Populate edit modal from data attributes
      const editBtn = $(`.edit-btn[data-id="${id}"]`);

      if (editBtn.length) {
        $('#editCustomerId').val(editBtn.data('id'));
        $('#editCustomerType').val(editBtn.data('customer_type')).trigger('change');
        $('#editCustomerName').val(editBtn.data('name') || '');
        $('#editBusinessName').val(editBtn.data('business_name') || '');
        $('#editContactNumber').val(editBtn.data('contact_number') || '');
        $('#editEmail').val(editBtn.data('email') || '');
        $('#editAddress').val(editBtn.data('address') || '');

        // Show modal
        $('#editCustomerModal').modal('show');
      } else {
        showToast('error', 'Customer data not found');
      }
    }

    function deleteCustomer(id, name) {
      $('#deleteCustomerId').val(id);
      $('#deleteCustomerConfirmationText').text(`Are you sure you want to delete customer "${name}"? This action cannot be undone.`);
      $('#deleteCustomerModal').modal('show');
    }

    function showToast(type, message) {
      const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
      const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';

      const toast = $(`
          <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex">
                  <div class="toast-body">
                      <i class="bi ${iconClass} me-2"></i>${message}
                  </div>
                  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
          </div>
      `);

      // Create toast container if it doesn't exist
      if ($('#toast-container').length === 0) {
        $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000;"></div>');
      }

      $('#toast-container').append(toast);
      const bsToast = new bootstrap.Toast(toast[0]);
      bsToast.show();

      // Remove toast element after it's hidden
      toast.on('hidden.bs.toast', function () {
        $(this).remove();
      });
    }
  </script>

  <!--begin::Script-->
  <?php include "../script.php"; ?>

</body>
<!--end::Body-->

</html>
<!--end::HTML-->