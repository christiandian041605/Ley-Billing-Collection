<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
  header("Location: ../login/");
  exit();
}

include "../config/app.php";
include_once "../config/database.php";
include_once "../helpers/csrf.php";
include "payment.php";

ensure_csrf_token();

$database = new Database();
$db = $database->getConnection();

$payment = new Payment($db);

$query_customers = "SELECT id, name, customer_type FROM tbl_customers ORDER BY name ASC";
$stmt_customers = $db->prepare($query_customers);
$stmt_customers->execute();
$customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);

$banks = $payment->getAllBanks();
?>
<!doctype html>
<html lang="en">

<!--begin::Head-->
<?php include "../header.php"; ?>
<style>
  .modal-backdrop {
    background-color: transparent !important;
  }

  .invoice-allocation-row {
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
  }

  .payment-type-fields {
    display: none;
  }

  .badge-govt {
    font-size: 0.75rem;
    padding: 2px 6px;
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
              <h3 class="mb-0">Payments</h3>
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
                  <!-- this button will call the add payment modal -->
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addPaymentModal">Add Payment</button>
                  <div class="card-tools">

                  </div>
                </div>
                <div class="card-body">
                  <table id="paymentsTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>OR Number</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
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

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addPaymentModalLabel">Record New Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="addPaymentForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="create">
              <input type="hidden" id="addInvoiceAllocations" name="invoice_allocations">

              <div class="row">
                <div class="col-md-6">
                  <h6 class="border-bottom pb-2 mb-3">Payment Information</h6>

                  <div class="mb-3">
                    <label for="addCustomerId" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select class="form-select select2" id="addCustomerId" name="customer_id" required
                      data-theme="bootstrap-5" style="width: 100%;">
                      <option value="">Select Customer</option>
                      <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>"
                          data-customer-type="<?php echo $customer['customer_type']; ?>">
                          <?php echo htmlspecialchars($customer['name']); ?>
                          <?php if ($customer['customer_type'] === 'Government'): ?>
                            (Government)
                          <?php endif; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="addOrNo" class="form-label">OR Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input type="text" class="form-control" id="addOrNo" name="or_no" required>
                      <button type="button" class="btn btn-outline-secondary" id="generateOrNo">
                        <i class="bi bi-arrow-clockwise"></i>
                      </button>
                      <span class="input-group-text" id="addOrNoSpinner" style="display: none;">
                        <span class="spinner-border spinner-border-sm"></span>
                      </span>
                    </div>
                    <div id="addOrNoError" class="invalid-feedback"></div>
                  </div>

                  <div class="mb-3">
                    <label for="addOrDate" class="form-label">OR Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="addOrDate" name="or_date" required
                      value="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div class="mb-3">
                    <label for="addAmountReceived" class="form-label">Amount Received <span
                        class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="addAmountReceived" name="amount_received" step="0.01"
                      min="0.01" required>
                  </div>

                  <div class="mb-3">
                    <label for="addPaymentType" class="form-label">Payment Method <span
                        class="text-danger">*</span></label>
                    <select class="form-select" id="addPaymentType" name="payment_type" required>
                      <option value="Cash">Cash</option>
                      <option value="Check">Check</option>
                      <option value="Bank Transfer">Bank Transfer</option>
                      <option value="GCash">GCash</option>
                    </select>
                  </div>

                  <!-- Check/Bank Fields -->
                  <div id="addCheckFields" class="payment-type-fields">
                    <div class="mb-3">
                      <label for="addBankId" class="form-label">Bank <span class="text-danger">*</span></label>
                      <select class="form-select" id="addBankId" name="bank_id">
                        <option value="">Select Bank</option>
                        <?php foreach ($banks as $bank): ?>
                          <option value="<?php echo $bank['id']; ?>">
                            <?php echo htmlspecialchars($bank['bank_name']); ?>
                            <?php if (!empty($bank['branch'])): ?>
                              - <?php echo htmlspecialchars($bank['branch']); ?>
                            <?php endif; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label for="addChequeNo" class="form-label">Check Number <span
                          class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="addChequeNo" name="cheque_no">
                    </div>

                    <div class="mb-3">
                      <label for="addChequeDate" class="form-label">Check Date</label>
                      <input type="date" class="form-control" id="addChequeDate" name="cheque_date">
                    </div>

                    <div class="mb-3">
                      <label for="addCheckName" class="form-label">Name on Check (Government Requirement)</label>
                      <input type="text" class="form-control" id="addCheckName" name="cheque_name">
                    </div>

                    <div class="mb-3">
                      <label for="addCheckAmount" class="form-label">Check Amount (Verification)</label>
                      <input type="number" class="form-control" id="addCheckAmount" name="check_amount" step="0.01"
                        min="0">
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="addNotes" class="form-label">Notes</label>
                    <textarea class="form-control" id="addNotes" name="notes" rows="3"></textarea>
                  </div>
                </div>

                <div class="col-md-6">
                  <h6 class="border-bottom pb-2 mb-3">Apply Payment to Invoices / DRs</h6>
                  <div id="addInvoiceList" class="mb-3">
                    <p class="text-muted">Select a customer to view unpaid invoices</p>
                  </div>
                  <div class="alert alert-info" id="addRemainingAlert">
                    <strong>Total Amount:</strong> ₱<span id="addTotalAmount">0.00</span><br>
                    <strong>Applied:</strong> ₱<span id="addAppliedAmount">0.00</span><br>
                    <strong>Remaining:</strong> ₱<span id="addRemainingAmount">0.00</span>
                  </div>
                  <div id="addOverpaymentWarning" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Overpayment Alert:</strong> The allocated amount exceeds the payment amount by ₱<span
                      id="addOverpaymentAmount">0.00</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="addPaymentSubmit" class="btn btn-primary">Record Payment</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="editPaymentForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" id="editPaymentId" name="payment_id">
              <input type="hidden" name="action" value="update">
              <input type="hidden" id="editInvoiceAllocations" name="invoice_allocations">

              <div class="row">
                <div class="col-md-6">
                  <h6 class="border-bottom pb-2 mb-3">Payment Information</h6>
                  <div class="mb-3">
                    <label for="editCustomerId" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select class="form-select select2" id="editCustomerId" name="customer_id" required
                      data-theme="bootstrap-5" style="width: 100%;">
                      <option value="">Select Customer</option>
                      <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>"
                          data-customer-type="<?php echo $customer['customer_type']; ?>">
                          <?php echo htmlspecialchars($customer['name']); ?>
                          <?php if ($customer['customer_type'] === 'Government'): ?>
                            (Government)
                          <?php endif; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="editOrNo" class="form-label">OR Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input type="text" class="form-control" id="editOrNo" name="or_no" required>
                      <span class="input-group-text" id="editOrNoSpinner" style="display: none;">
                        <span class="spinner-border spinner-border-sm"></span>
                      </span>
                    </div>
                    <div id="editOrNoError" class="invalid-feedback"></div>
                  </div>

                  <div class="mb-3">
                    <label for="editOrDate" class="form-label">OR Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="editOrDate" name="or_date" required>
                  </div>

                  <div class="mb-3">
                    <label for="editAmountReceived" class="form-label">Amount Received <span
                        class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="editAmountReceived" name="amount_received" step="0.01"
                      min="0.01" required>
                  </div>

                  <div class="mb-3">
                    <label for="editPaymentType" class="form-label">Payment Method <span
                        class="text-danger">*</span></label>
                    <select class="form-select" id="editPaymentType" name="payment_type" required>
                      <option value="Cash">Cash</option>
                      <option value="Check">Check</option>
                      <option value="Bank Transfer">Bank Transfer</option>
                      <option value="GCash">GCash</option>
                    </select>
                  </div>

                  <div id="editCheckFields" class="payment-type-fields">
                    <div class="mb-3">
                      <label for="editBankId" class="form-label">Bank <span class="text-danger">*</span></label>
                      <select class="form-select" id="editBankId" name="bank_id">
                        <option value="">Select Bank</option>
                        <?php foreach ($banks as $bank): ?>
                          <option value="<?php echo $bank['id']; ?>">
                            <?php echo htmlspecialchars($bank['bank_name']); ?>
                            <?php if (!empty($bank['branch'])): ?>
                              - <?php echo htmlspecialchars($bank['branch']); ?>
                            <?php endif; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label for="editChequeNo" class="form-label">Check Number <span
                          class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="editChequeNo" name="cheque_no">
                    </div>

                    <div class="mb-3">
                      <label for="editChequeDate" class="form-label">Check Date</label>
                      <input type="date" class="form-control" id="editChequeDate" name="cheque_date">
                    </div>

                    <div class="mb-3">
                      <label for="editCheckName" class="form-label">Name on Check (Government Requirement)</label>
                      <input type="text" class="form-control" id="editCheckName" name="cheque_name">
                    </div>

                    <div class="mb-3">
                      <label for="editCheckAmount" class="form-label">Check Amount (Verification)</label>
                      <input type="number" class="form-control" id="editCheckAmount" name="check_amount" step="0.01"
                        min="0">
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="editNotes" class="form-label">Notes</label>
                    <textarea class="form-control" id="editNotes" name="notes" rows="3"></textarea>
                  </div>
                </div>

                <div class="col-md-6">
                  <h6 class="border-bottom pb-2 mb-3">Apply Payment to Invoices / DRs</h6>
                  <div id="editInvoiceList" class="mb-3">
                    <p class="text-muted">Select a customer to view unpaid invoices</p>
                  </div>
                  <div class="alert alert-info" id="editRemainingAlert">
                    <strong>Total Amount:</strong> ₱<span id="editTotalAmount">0.00</span><br>
                    <strong>Applied:</strong> ₱<span id="editAppliedAmount">0.00</span><br>
                    <strong>Remaining:</strong> ₱<span id="editRemainingAmount">0.00</span>
                  </div>
                  <div id="editOverpaymentWarning" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Overpayment Alert:</strong> The allocated amount exceeds the payment amount by ₱<span
                      id="editOverpaymentAmount">0.00</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" id="editPaymentSubmit" class="btn btn-primary">Update Payment</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Payment Modal -->
    <div class="modal fade" id="viewPaymentModal" tabindex="-1" aria-labelledby="viewPaymentModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewPaymentModalLabel">Payment Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="viewPaymentContent">
            <div class="text-center">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-info" id="printPaymentBtn">
              <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Payment Modal -->
    <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deletePaymentModalLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="deletePaymentForm">
            <div class="modal-body">
              <p id="deletePaymentConfirmationText"></p>
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" id="deletePaymentId" name="payment_id">
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
      $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
      });
    });
  </script>
  <script src="script.js"></script>

</body>
<!--end::Body-->

</html>