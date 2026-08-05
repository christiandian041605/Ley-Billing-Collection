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

$database = new Database();
$db = $database->getConnection();
?>
<!doctype html>
<html lang="en">

<!--begin::Head-->
<?php include "../header.php"; ?>
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
              <h3 class="mb-0">Statement of Accounts</h3>
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
              <!-- Filter Card -->
              <div class="card card-primary card-outline mb-4">
                <div class="card-header">
                  <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filter Options</h5>
                </div>
                <div class="card-body">
                  <form id="filterForm">
                    <div class="row">
                      <div class="col-md-4 mb-3">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-select select2" id="customer_id" name="customer_id" data-theme="bootstrap-5"
                          style="width: 100%;">
                          <option value="">All Customers</option>
                        </select>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
                      </div>
                      <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                          <option value="">All Status</option>
                          <option value="Unpaid">Unpaid</option>
                          <option value="Partially Paid">Partially Paid</option>
                          <option value="Paid">Paid</option>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-3 mb-3">
                        <label for="customer_type" class="form-label">Customer Type</label>
                        <select class="form-select" id="customer_type" name="customer_type">
                          <option value="">All Types</option>
                          <option value="Private">Private</option>
                          <option value="Business">Business</option>
                          <option value="Government">Government</option>
                        </select>
                      </div>
                      <div class="col-md-9 mb-3 d-flex align-items-end">
                        <button type="button" id="generateBtn" class="btn btn-primary me-2">
                          <i class="bi bi-search me-1"></i> Generate Report
                        </button>
                        <button type="button" id="resetBtn" class="btn btn-secondary me-2">
                          <i class="bi bi-arrow-clockwise me-1"></i> Reset
                        </button>
                        <button type="button" id="printBtn" class="btn btn-success me-2" disabled>
                          <i class="bi bi-printer me-1"></i> Print
                        </button>
                        <button type="button" id="exportBtn" class="btn btn-info" disabled>
                          <i class="bi bi-file-earmark-excel me-1"></i> Export
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Results Card -->
              <div class="card card-primary card-outline" id="resultsCard" style="display: none;">
                <div class="card-header">
                  <h5 class="card-title mb-0"><i class="bi bi-file-earmark-text me-2"></i>Statement of Accounts</h5>
                </div>
                <div class="card-body">
                  <div id="soaContent"></div>
                </div>
              </div>

            </div>
          </div>
          <!--end::Row-->

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

  <!-- Toast Notification -->
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto">Notification</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body"></div>
    </div>
  </div>

  <!--begin::Script-->
  <?php include "../script.php"; ?>

  <script>
    $(document).ready(function () {
      // Initialize Select2 for customer dropdown
      $('#customer_id').select2({
        theme: 'bootstrap-5'
      });

      // Load customers for dropdown
      loadCustomers();

      // Generate Report
      $('#generateBtn').on('click', function () {
        generateSOA();
      });

      // Reset filters
      $('#resetBtn').on('click', function () {
        $('#filterForm')[0].reset();
        $('#customer_id').val('').trigger('change');
        $('#resultsCard').hide();
        $('#printBtn, #exportBtn').prop('disabled', true);
      });

      // Print functionality
      $('#printBtn').on('click', function () {
        printSOA();
      });

      // Export functionality
      $('#exportBtn').on('click', function () {
        exportSOA();
      });

      function loadCustomers() {
        $.ajax({
          url: 'get_customers_list.php',
          type: 'GET',
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              let options = '<option value="">All Customers</option>';
              response.data.forEach(function (customer) {
                options += `<option value="${customer.id}">${customer.name} ${customer.business_name ? '(' + customer.business_name + ')' : ''}</option>`;
              });
              $('#customer_id').html(options);
            }
          },
          error: function () {
            showToast('error', 'Failed to load customers');
          }
        });
      }

      function generateSOA() {
        const formData = $('#filterForm').serialize();
        const generateBtn = $('#generateBtn');

        generateBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating...');

        $.ajax({
          url: 'get_soa_data.php',
          type: 'GET',
          data: formData,
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              displaySOA(response.data);
              $('#resultsCard').show();
              $('#printBtn, #exportBtn').prop('disabled', false);
            } else {
              showToast('error', response.message || 'No data found');
              $('#resultsCard').hide();
            }
            generateBtn.prop('disabled', false).html('<i class="bi bi-search me-1"></i> Generate Report');
          },
          error: function (xhr) {
            let errorMsg = 'Failed to generate report';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            showToast('error', errorMsg);
            generateBtn.prop('disabled', false).html('<i class="bi bi-search me-1"></i> Generate Report');
          }
        });
      }

      function displaySOA(data) {
        let html = `
                    <div class="soa-report" id="printableArea">
                        <div class="text-center mb-4">
                            <h4 class="mb-1">STATEMENT OF ACCOUNTS</h4>
                            <p class="text-muted mb-0">${data.report_info.company_name || 'Ley Billing System'}</p>
                            ${data.report_info.company_address ? '<p class="text-muted small">' + data.report_info.company_address + '</p>' : ''}
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <strong>Customer:</strong> ${data.customer_info.name}<br>
                                ${data.customer_info.business_name ? '<strong>Business:</strong> ' + data.customer_info.business_name + '<br>' : ''}
                                <strong>Type:</strong> ${data.customer_info.type}<br>
                                ${data.customer_info.address ? '<strong>Address:</strong> ' + data.customer_info.address + '<br>' : ''}
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Report Date:</strong> ${data.report_info.date}<br>
                                <strong>Period:</strong> ${data.report_info.period_from} to ${data.report_info.period_to}<br>
                            </div>
                        </div>

                        <h5 class="mb-3">Invoice / DR Summary</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice / DR No.</th>
                                        <th>Date</th>
                                        ${data.customer_info.name === 'All Customers' ? '<th>Customer Name</th><th>Type</th>' : ''}
                                        <th class="text-end">Amount</th>
                                        ${(data.customer_info.type === 'Government' || data.customer_info.type === 'All') ? '<th class="text-end">Withholding</th><th class="text-end">Net Amount</th>' : ''}
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Balance</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>`;

        if (data.invoices.length > 0) {
          data.invoices.forEach(function (invoice) {
            html += `
                            <tr>
                                <td>${invoice.invoice_no}</td>
                                <td>${invoice.invoice_date}</td>
                                ${data.customer_info.name === 'All Customers' ? '<td>' + (invoice.customer_name || '-') + '</td><td>' + (invoice.customer_type || '-') + '</td>' : ''}
                                <td class="text-end">${invoice.total_amount}</td>
                                ${(data.customer_info.type === 'Government' || data.customer_info.type === 'All') ? '<td class="text-end">' + invoice.withholding + '</td><td class="text-end">' + invoice.net_amount + '</td>' : ''}
                                <td class="text-end">${invoice.paid}</td>
                                <td class="text-end">${invoice.balance}</td>
                                <td class="text-center">${invoice.status_badge}</td>
                            </tr>`;
          });
        } else {
          let colspan = 6;
          if (data.customer_info.type === 'Government' || data.customer_info.type === 'All') colspan += 2;
          if (data.customer_info.name === 'All Customers') colspan += 2;
          html += `<tr><td colspan="${colspan}" class="text-center text-muted">No invoices found</td></tr>`;
        }

        html += `
                                </tbody>
                            </table>
                        </div>

                        <h5 class="mb-3">Payment History</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>OR No.</th>
                                        <th>Date</th>
                                        <th>Payment Method</th>
                                        <th class="text-end">Amount</th>
                                        <th>Applied To</th>
                                    </tr>
                                </thead>
                                <tbody>`;

        if (data.payments.length > 0) {
          data.payments.forEach(function (payment) {
            html += `
                            <tr>
                                <td>${(payment.customer_type === 'Government') ? (payment.or_no || '') : ''}</td>
                                <td>${payment.or_date}</td>
                                <td>${payment.payment_method}</td>
                                <td class="text-end">${payment.amount}</td>
                                <td>${payment.applied_to}</td>
                            </tr>`;
          });
        } else {
          let colspan = 5;
          html += `<tr><td colspan="${colspan}" class="text-center text-muted">No payments recorded</td></tr>`;
        }

        html += `
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-end"><strong>Total Invoiced:</strong></td>
                                        <td class="text-end" style="width: 150px;">${data.summary.total_invoiced}</td>
                                    </tr>
                                    ${(data.customer_info.type === 'Government' || data.customer_info.type === 'All') ? `
                                    <tr>
                                        <td class="text-end"><strong>Total Withholding:</strong></td>
                                        <td class="text-end">${data.summary.total_withholding}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end"><strong>Net Amount:</strong></td>
                                        <td class="text-end">${data.summary.net_amount}</td>
                                    </tr>
                                    ` : ''}
                                    <tr>
                                        <td class="text-end"><strong>Total Paid:</strong></td>
                                        <td class="text-end">${data.summary.total_paid}</td>
                                    </tr>
                                    <tr class="table-active">
                                        <td class="text-end"><strong>Outstanding Balance:</strong></td>
                                        <td class="text-end"><strong>${data.summary.outstanding_balance}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

        $('#soaContent').html(html);
      }

      function printSOA() {
        const printContents = document.getElementById('printableArea').innerHTML;
        const originalContents = document.body.innerHTML;

        // Create print styles
        const printStyles = `
                    <style>
                        @media print {
                            body { font-size: 12pt; }
                            .table { font-size: 10pt; }
                            .table-bordered { border: 1px solid #000 !important; }
                            .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
                        }
                    </style>
                `;

        document.body.innerHTML = printStyles + printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
      }

      function exportSOA() {
        const formData = $('#filterForm').serialize();
        window.location.href = 'export_soa.php?' + formData;
      }

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