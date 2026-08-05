<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
  header("Location: ../login/");
  exit();
}

include "../config/app.php";
include_once "../config/database.php";
include "delivery.php";
include_once "../customers/customer.php";

$database = new Database();
$db = $database->getConnection();
$delivery = new Delivery($db);
$customer = new Customer($db);

// Get delivery type from URL parameter
$delivery_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$page_title = 'Delivery Management';
$default_type = 'DR V';

if ($delivery_type === 'dr_v') {
  $page_title = 'Various - Standard Deliveries';
  $default_type = 'DR V';
} elseif ($delivery_type === 'dr_government') {
  $page_title = 'Government - Government Deliveries';
  $default_type = 'DR Government';
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
              <h3 class="mb-0"><?php echo $page_title; ?></h3>
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
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addDeliveryModal">Add Delivery</button>
                  <div class="card-tools">

                  </div>
                </div>
                <div class="card-body">
                  <table id="deliveriesTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Delivery No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Logistics</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Invoices</th>
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

    <!-- Add Delivery Modal -->
    <div class="modal fade" id="addDeliveryModal" tabindex="-1" aria-labelledby="addDeliveryModalLabel"
      aria-hidden="true" data-delivery-type="<?php echo $default_type; ?>">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addDeliveryModalLabel">Add New Delivery</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="addDeliveryForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="create">

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="addDeliveryNo" class="form-label">Delivery No</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="addDeliveryNo" name="delivery_no" required>
                    <button type="button" class="btn btn-outline-secondary" id="generateDeliveryNo">
                      <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <span class="input-group-text" id="addDeliveryNoSpinner" style="display: none;">
                      <span class="spinner-border spinner-border-sm"></span>
                    </span>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="addDeliveryDate" class="form-label">Delivery Date</label>
                  <input type="date" class="form-control" id="addDeliveryDate" name="delivery_date" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="addCustomerId" class="form-label">Customer</label>
                  <select class="form-select" id="addCustomerId" name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php
                    $stmtAdd = $customer->getCustomersForSelect();
                    while ($rowAdd = $stmtAdd->fetch(PDO::FETCH_ASSOC)) {
                      echo '<option value="' . $rowAdd['id'] . '" data-customer-type="' . $rowAdd['customer_type'] . '" data-address="' . htmlspecialchars($rowAdd['address']) . '">' . $rowAdd['name'] . ' (' . $rowAdd['customer_type'] . ')</option>';
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="addDeliveryType" class="form-label">Delivery Type</label>
                  <select class="form-select" id="addDeliveryType" name="delivery_type" required>
                    <option value="DR V" <?php echo ($default_type === 'DR V') ? 'selected' : ''; ?>>Various (Standard)
                    </option>
                    <option value="DR Government" <?php echo ($default_type === 'DR Government') ? 'selected' : ''; ?>>
                      Government</option>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label for="addAddress" class="form-label">Delivery Address</label>
                <textarea class="form-control" id="addAddress" name="address" rows="2"></textarea>
              </div>

              <div class="row" id="addLogisticsRow">
                <div class="col-md-4 mb-3">
                  <label for="addChecker" class="form-label">Checker</label>
                  <input type="text" class="form-control" id="addChecker" name="checker">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="addDriver" class="form-label">Driver</label>
                  <input type="text" class="form-control" id="addDriver" name="driver">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="addPlateNumber" class="form-label">Plate Number</label>
                  <input type="text" class="form-control" id="addPlateNumber" name="plate_number">
                </div>
              </div>

              <div class="mb-3">
                <label for="addTotalAmount" class="form-label">Total Amount</label>
                <input type="number" class="form-control" id="addTotalAmount" name="total_amount" step="0.01" min="0"
                  required>
              </div>


            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="addDeliverySubmit" class="btn btn-primary">Save Delivery</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Delivery Modal -->
    <div class="modal fade" id="editDeliveryModal" tabindex="-1" aria-labelledby="editDeliveryModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editDeliveryModalLabel">Edit Delivery</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="editDeliveryForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" id="editDeliveryId" name="id">
              <input type="hidden" name="action" value="update">

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editDeliveryNo" class="form-label">Delivery No</label>
                  <input type="text" class="form-control" id="editDeliveryNo" name="delivery_no" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editDeliveryDate" class="form-label">Delivery Date</label>
                  <input type="date" class="form-control" id="editDeliveryDate" name="delivery_date" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editCustomerId" class="form-label">Customer</label>
                  <select class="form-select" id="editCustomerId" name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php
                    $stmtEdit = $customer->getCustomersForSelect();
                    while ($rowEdit = $stmtEdit->fetch(PDO::FETCH_ASSOC)) {
                      echo '<option value="' . $rowEdit['id'] . '" data-customer-type="' . $rowEdit['customer_type'] . '" data-address="' . htmlspecialchars($rowEdit['address']) . '">' . $rowEdit['name'] . ' (' . $rowEdit['customer_type'] . ')</option>';
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editDeliveryType" class="form-label">Delivery Type</label>
                  <select class="form-select" id="editDeliveryType" name="delivery_type" required>
                    <option value="DR V">Various (Standard)</option>
                    <option value="DR Government">Government</option>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label for="editAddress" class="form-label">Delivery Address</label>
                <textarea class="form-control" id="editAddress" name="address" rows="2"></textarea>
              </div>

              <div class="row" id="editLogisticsRow">
                <div class="col-md-4 mb-3">
                  <label for="editChecker" class="form-label">Checker</label>
                  <input type="text" class="form-control" id="editChecker" name="checker">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="editDriver" class="form-label">Driver</label>
                  <input type="text" class="form-control" id="editDriver" name="driver">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="editPlateNumber" class="form-label">Plate Number</label>
                  <input type="text" class="form-control" id="editPlateNumber" name="plate_number">
                </div>
              </div>

              <div class="mb-3">
                <label for="editTotalAmount" class="form-label">Total Amount</label>
                <input type="number" class="form-control" id="editTotalAmount" name="total_amount" step="0.01" min="0"
                  required>
              </div>


            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="editDeliverySubmit" class="btn btn-primary">Update Delivery</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Delivery Modal -->
    <div class="modal fade" id="viewDeliveryModal" tabindex="-1" aria-labelledby="viewDeliveryModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewDeliveryModalLabel">Delivery Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="viewDeliveryContent">
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Delivery Modal -->
    <div class="modal fade" id="deleteDeliveryModal" tabindex="-1" aria-labelledby="deleteDeliveryModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteDeliveryModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="deleteDeliveryForm">
            <div class="modal-body">
              <p id="deleteDeliveryConfirmationText"></p>
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" id="deleteDeliveryId" name="id">
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
    $(document).ready(function () {
      // Ensure modal backdrops are removed and body state is cleaned up when ANY modal is closed
      // This handles both manual closing and closing via JS
      $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
      });

      // Get delivery type from URL
      var urlParams = new URLSearchParams(window.location.search);
      var deliveryType = urlParams.get('type') || 'all';

      // Store original options for customer selects
      var addCustomerOptions = $('#addCustomerId').find('option').clone();
      var editCustomerOptions = $('#editCustomerId').find('option').clone();

      // Initialize DataTable
      var table = $('#deliveriesTable').DataTable({
        "ajax": {
          "url": "get_deliveries.php",
          "type": "POST",
          "data": function (d) {
            d.delivery_type = deliveryType;
          }
        },
        "processing": true,
        "serverSide": true,
        "columns": [
          { "data": "delivery_no" },
          { "data": "delivery_date" },
          { "data": "customer_name" },
          { "data": "logistics", "orderable": false, "visible": deliveryType !== 'dr_government' },
          { "data": "delivery_type" },
          { "data": "total_amount" },
          { "data": "payment_status" },
          { "data": "invoice_numbers", "visible": deliveryType !== 'dr_v' },
          { "data": "actions", "orderable": false, "searchable": false }
        ],
        "order": [[1, 'desc']]
      });

      // Initialize Select2 on modal shown
      $('#addDeliveryModal').on('shown.bs.modal', function () {
        // First filter customers and toggle logistics based on delivery type
        handleDeliveryTypeChange('#addDeliveryType', '#addCustomerId', '#addLogisticsRow');

        // Then initialize Select2
        if (!$('#addCustomerId').hasClass('select2-hidden-accessible')) {
          $('#addCustomerId').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addDeliveryModal'),
            width: '100%'
          });
        }
      });

      $('#editDeliveryModal').on('shown.bs.modal', function () {
        // Initialize Select2 without filtering - show all customers
        if (!$('#editCustomerId').hasClass('select2-hidden-accessible')) {
          $('#editCustomerId').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#editDeliveryModal'),
            width: '100%'
          });
        }
      });

      // Filter customers and toggle logistics based on delivery type
      function handleDeliveryTypeChange(deliveryTypeSelector, customerSelector, logisticsRowSelector) {
        var deliveryType = $(deliveryTypeSelector).val();
        var $customerSelect = $(customerSelector);
        var storedOptions = $(customerSelector).attr('id') === 'addCustomerId' ? addCustomerOptions : editCustomerOptions;

        // Toggle Logistics Row
        if (deliveryType === 'DR Government') {
          $(logisticsRowSelector).hide();
        } else {
          $(logisticsRowSelector).show();
        }

        // Get all options from stored copy
        var allOptions = [];
        storedOptions.each(function () {
          allOptions.push({
            $element: $(this).clone(),
            value: $(this).val(),
            text: $(this).text(),
            customerType: $(this).data('customer-type'),
            address: $(this).data('address')
          });
        });

        // Filter options based on delivery type
        var visibleOptions = allOptions.filter(function (option) {
          if (option.value === '') {
            return true; // Always show placeholder
          }

          if (deliveryType === 'DR Government' && option.customerType === 'Government') {
            return true;
          } else if (deliveryType === 'DR V' && (option.customerType === 'Private' || option.customerType === 'Business')) {
            return true;
          }
          return false;
        });

        // Update the select options
        $customerSelect.empty();
        visibleOptions.forEach(function (option) {
          $customerSelect.append(option.$element);
        });

        // Trigger change to update Select2
        $customerSelect.trigger('change.select2');
      }

      // Listen to delivery type changes
      $('#addDeliveryType, #editDeliveryType').on('change', function () {
        var isAddForm = $(this).attr('id') === 'addDeliveryType';
        var deliveryTypeSelector = isAddForm ? '#addDeliveryType' : '#editDeliveryType';
        var customerSelector = isAddForm ? '#addCustomerId' : '#editCustomerId';
        var logisticsRowSelector = isAddForm ? '#addLogisticsRow' : '#editLogisticsRow';
        handleDeliveryTypeChange(deliveryTypeSelector, customerSelector, logisticsRowSelector);
      });

      // Auto-populate address on customer selection
      $('#addCustomerId').on('change', function () {
        var selectedOption = $(this).find('option:selected');
        var address = selectedOption.data('address');
        if (address) {
          $('#addAddress').val(address);
        }
      });

      // Generate delivery number on modal open
      $('#addDeliveryModal').on('show.bs.modal', function () {
        generateDeliveryNumber();
        $('#addDeliveryDate').val(new Date().toISOString().split('T')[0]);

        // Set delivery type from URL parameter if available
        var defaultDeliveryType = $(this).data('delivery-type');
        if (defaultDeliveryType) {
          $('#addDeliveryType').val(defaultDeliveryType);
        }

        // Filter customers on modal open based on default delivery type
        handleDeliveryTypeChange('#addDeliveryType', '#addCustomerId', '#addLogisticsRow');
      });

      // Generate delivery number button
      $(document).on('click', '#generateDeliveryNo', function (e) {
        e.preventDefault();
        generateDeliveryNumber();
      });

      function generateDeliveryNumber() {
        const $btn = $('#generateDeliveryNo');
        const $spinner = $('#addDeliveryNoSpinner');

        $btn.prop('disabled', true);
        $spinner.show();

        $.ajax({
          url: 'process.php',
          type: 'POST',
          data: {
            action: 'generate_delivery_no',
            csrf_token: $('input[name="csrf_token"]').val()
          },
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#addDeliveryNo').val(response.delivery_no);
            } else {
              showToast('error', response.message || 'Failed to generate delivery number');
            }
          },
          error: function (xhr) {
            let errorMsg = 'Error generating delivery number';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            showToast('error', errorMsg);
          },
          complete: function () {
            $btn.prop('disabled', false);
            $spinner.hide();
          }
        });
      }

      // Add Delivery Form Submit
      $('#addDeliveryForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('#addDeliverySubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
          url: 'process.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#addDeliveryModal').modal('hide');

              // Force cleanup backdrop and body state
              setTimeout(function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
              }, 200);

              $('#addDeliveryForm')[0].reset();
              $('#addInvoicesList').html('<p class="text-muted">Select a customer to see available invoices</p>');
              $('#addInvoiceIds').val('[]');
              if ($('#addCustomerId').hasClass('select2-hidden-accessible')) {
                $('#addCustomerId').val('').trigger('change');
              }

              showToast('success', response.message);
              $('#deliveriesTable').DataTable().ajax.reload(null, false);
            } else {
              showToast('error', response.message);
            }
            submitBtn.prop('disabled', false).html('Save Delivery');
          },
          error: function (xhr) {
            let errorMsg = 'An error occurred while saving the delivery';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            showToast('error', errorMsg);
            submitBtn.prop('disabled', false).html('Save Delivery');
          }
        });
      });



      // View Delivery
      $(document).on('click', '.view-btn', function () {
        var deliveryId = $(this).data('id');

        $.ajax({
          url: 'process.php',
          type: 'POST',
          data: {
            action: 'read',
            id: deliveryId,
            csrf_token: $('input[name="csrf_token"]').val()
          },
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              displayDeliveryDetails(response.delivery, response.invoices);
              $('#viewDeliveryModal').modal('show');
            }
          }
        });
      });

      function displayDeliveryDetails(delivery, invoices) {
        var html = '<div class="row">';
        html += '<div class="col-md-6"><strong>Delivery No:</strong> ' + delivery.delivery_no + '</div>';
        html += '<div class="col-md-6"><strong>Date:</strong> ' + new Date(delivery.delivery_date).toLocaleDateString() + '</div>';
        html += '</div><hr>';
        html += '<div class="row">';
        html += '<div class="col-md-6"><strong>Type:</strong> <span class="badge ' + (delivery.delivery_type === 'DR Government' ? 'bg-success' : 'bg-primary') + '">' + delivery.delivery_type + '</span></div>';
        html += '<div class="col-md-6"><strong>Total Amount:</strong> ₱' + parseFloat(delivery.total_amount).toFixed(2) + '</div>';
        html += '</div><hr>';
        html += '<div><strong>Address:</strong><br>' + (delivery.address || 'N/A') + '</div><hr>';
        html += '<div class="row">';
        html += '<div class="col-md-4"><strong>Checker:</strong> ' + (delivery.checker || 'N/A') + '</div>';
        html += '<div class="col-md-4"><strong>Driver:</strong> ' + (delivery.driver || 'N/A') + '</div>';
        html += '<div class="col-md-4"><strong>Plate Number:</strong> ' + (delivery.plate_number || 'N/A') + '</div>';
        html += '</div><hr>';

        if (invoices && invoices.length > 0) {
          html += '<div><strong>Linked Invoices:</strong><br>';
          html += '<table class="table table-sm table-bordered mt-2">';
          html += '<thead><tr><th>Invoice No</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
          invoices.forEach(function (invoice) {
            var statusClass = invoice.payment_status === 'Paid' ? 'success' : (invoice.payment_status === 'Partially Paid' ? 'warning' : 'danger');
            html += '<tr>';
            html += '<td>' + invoice.invoice_no + '</td>';
            html += '<td>' + new Date(invoice.invoice_date).toLocaleDateString() + '</td>';
            html += '<td>₱' + parseFloat(invoice.total_amount).toFixed(2) + '</td>';
            html += '<td><span class="badge bg-' + statusClass + '">' + invoice.payment_status + '</span></td>';
            html += '</tr>';
          });
          html += '</tbody></table></div>';
        } else {
          html += '<div class="alert alert-info">No invoices linked to this delivery</div>';
        }

        $('#viewDeliveryContent').html(html);
      }

      // Edit Delivery
      $(document).on('click', '.edit-btn', function () {
        var deliveryId = $(this).data('id');
        var csrfToken = $('input[name="csrf_token"]').first().val();

        $.ajax({
          url: 'process.php',
          type: 'POST',
          data: {
            action: 'read',
            id: deliveryId,
            csrf_token: csrfToken
          },
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              var delivery = response.delivery;

              $('#editDeliveryId').val(delivery.id);
              $('#editDeliveryNo').val(delivery.delivery_no);
              $('#editDeliveryDate').val(delivery.delivery_date);
              $('#editDeliveryType').val(delivery.delivery_type);

              // Trigger visibility update and filter customers
              handleDeliveryTypeChange('#editDeliveryType', '#editCustomerId', '#editLogisticsRow');

              $('#editAddress').val(delivery.address);
              $('#editChecker').val(delivery.checker);
              $('#editDriver').val(delivery.driver);
              $('#editPlateNumber').val(delivery.plate_number);
              $('#editTotalAmount').val(delivery.total_amount);

              // Set customer (handleDeliveryTypeChange resets options, so we just set value)
              // If the customer is not in the filtered list (unlikely if data is consistent), it won't be selected.
              // To be safe, we can check if it exists and append if missing, but let's trust the filter for now.
              $('#editCustomerId').val(delivery.customer_id).trigger('change');

              // Load and check linked invoices
              setTimeout(function () {
                response.invoices.forEach(function (invoice) {
                  $('#invoice_' + invoice.id).prop('checked', true);
                });
                updateInvoiceIds('#editInvoicesList', '#editInvoiceIds');
              }, 500);

              $('#editDeliveryModal').modal('show');
            } else {
              showToast('error', response.message || 'Failed to load delivery details');
            }
          },
          error: function () {
            showToast('error', 'Failed to load delivery details');
          }
        });
      });

      // Edit Delivery Form Submit
      $('#editDeliveryForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('#editDeliverySubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        $.ajax({
          url: 'process.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              $('#editDeliveryModal').modal('hide');

              // Force cleanup backdrop and body state
              setTimeout(function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
              }, 200);

              showToast('success', response.message);
              $('#deliveriesTable').DataTable().ajax.reload(null, false);
            } else {
              showToast('error', response.message);
            }
            submitBtn.prop('disabled', false).html('Update Delivery');
          },
          error: function (xhr) {
            let errorMsg = 'An error occurred while updating the delivery';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            showToast('error', errorMsg);
            submitBtn.prop('disabled', false).html('Update Delivery');
          }
        });
      });

      // Delete Delivery
      $(document).on('click', '.delete-btn', function () {
        var deliveryId = $(this).data('id');
        var deliveryNo = $(this).data('delivery-no');

        $('#deleteDeliveryId').val(deliveryId);
        $('#deleteDeliveryConfirmationText').text('Are you sure you want to delete delivery ' + deliveryNo + '?');
        $('#deleteDeliveryModal').modal('show');
      });

      // Delete Delivery Form Submit
      $('#deleteDeliveryForm').on('submit', function (e) {
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
              $('#deleteDeliveryModal').modal('hide');

              // Force cleanup backdrop and body state
              setTimeout(function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
              }, 200);

              showToast('success', response.message);
              $('#deliveriesTable').DataTable().ajax.reload(null, false);
            } else {
              showToast('error', response.message);
            }
            submitBtn.prop('disabled', false).html('Delete');
          },
          error: function (xhr) {
            let errorMsg = 'An error occurred while deleting the delivery';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            showToast('error', errorMsg);
            submitBtn.prop('disabled', false).html('Delete');
          }
        });
      });

      // Toast notification function (matching users module)
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