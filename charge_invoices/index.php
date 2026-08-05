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
include "charge_invoice.php";

$database = new Database();
$db = $database->getConnection();

$chargeInvoice = new ChargeInvoice($db);

$page_title = 'Charge Invoices - Government';
$default_type = 'Government';
// Get Government customers for Government invoices
$query = "SELECT id, name, customer_type, business_name, address FROM tbl_customers WHERE customer_type = 'Government' ORDER BY name ASC";


$stmt = $db->prepare($query);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge($status)
{
  switch ($status) {
    case 'Paid':
      return '<span class="badge bg-success">Paid</span>';
    case 'Partially Paid':
      return '<span class="badge bg-warning text-dark">Partially Paid</span>';
    case 'Unpaid':
      return '<span class="badge bg-danger">Unpaid</span>';
    default:
      return '<span class="badge bg-secondary">Unknown</span>';
  }
}

function formatCurrency($amount)
{
  return '₱' . number_format($amount, 2);
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

  .deliveries-list-container {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    max-height: 250px;
    overflow-y: auto;
    padding: 10px;
    background-color: #fff;
  }

  .tax-info-box {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-top: 10px;
  }

  .tax-info-box.government {
    background-color: #e7f3ff;
    border-color: #0dcaf0;
  }
</style>

<body class="layout-fixed bg-body-tertiary">
  <div class="app-wrapper">

    <?php include "../navbar.php"; ?>
    <?php include "../sidebar.php"; ?>

    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <h3 class="mb-0"><?php echo $page_title; ?></h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="../dashboard/">Dashboard</a></li>
                <li class="breadcrumb-item active">Charge Invoices</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card card-primary card-outline">
                <div class="card-header justify-content-between border-0">
                  <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#addInvoiceModal">Add Charge Invoice</button>
                </div>
                <div class="card-body">
                  <table id="invoicesTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Net Amount</th>
                        <th>Status</th>
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

    <!-- Add Invoice Modal -->
    <div class="modal fade" id="addInvoiceModal" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add New Charge Invoice</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form id="addInvoiceForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="create">
              <input type="hidden" name="total_amount" id="addTotalAmount">

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label for="addInvoiceNo" class="form-label">Invoice No <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="addInvoiceNo" name="invoice_no" required>
                    <button type="button" class="btn btn-outline-secondary" id="generateInvoiceNo">
                      <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <span class="input-group-text" id="addInvoiceNoSpinner" style="display: none;">
                      <span class="spinner-border spinner-border-sm"></span>
                    </span>
                  </div>
                  <div id="addInvoiceNoError" class="invalid-feedback"></div>
                </div>

                <div class="col-md-4 mb-3">
                  <label for="addInvoiceDate" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="addInvoiceDate" name="invoice_date" required>
                </div>

                <div class="col-md-4 mb-3">
                  <label for="addCustomer" class="form-label">Customer <span class="text-danger">*</span></label>
                  <select class="form-select select2" id="addCustomer" name="customer_id" required
                    data-theme="bootstrap-5" style="width: 100%;">
                    <option value="">Select Customer</option>
                    <?php if (empty($customers)): ?>
                      <option value="" disabled>No customers available for this type</option>
                    <?php else: ?>
                      <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>" data-type="<?php echo $customer['customer_type']; ?>"
                          data-address="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
                          <?php echo htmlspecialchars($customer['name']); ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                  <div class="form-text">
                    <?php echo $default_type === 'Government' ? 'Charge Invoices for Government customers.' : 'Charge Invoices for Private and Business customers.'; ?>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="addAddress" class="form-label">Address</label>
                <textarea class="form-control" id="addAddress" name="address" rows="2"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Link Deliveries <span class="text-danger">*</span></label>
                <div class="deliveries-list-container" id="addDeliveriesContainer">
                  <p class="text-center text-muted my-3">Select a customer to view available deliveries.</p>
                </div>
                <div class="form-text text-muted">Select one or more deliveries to generate the invoice total.</div>
              </div>

              <div class="row">
                <div class="col-md-8">
                  <div id="addTaxInfo" class="tax-info-box" style="display: none;">
                    <h6 class="mb-2" id="addTaxInfoTitle">Tax Calculation</h6>
                    <div class="row">
                      <div class="col-md-4">
                        <small class="text-muted">5% Withholding Tax:</small>
                        <div id="addTax5Display" class="fw-bold">₱0.00</div>
                      </div>
                      <div class="col-md-4">
                        <small class="text-muted">1% Withholding Tax:</small>
                        <div id="addTax1Display" class="fw-bold">₱0.00</div>
                      </div>
                      <div class="col-md-4">
                        <small class="text-muted">Net Amount:</small>
                        <div id="addNetAmountDisplay" class="fw-bold text-success">₱0.00</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="text-end">
                    <h5>Total Amount: <span id="addTotalDisplay" class="text-primary">₱0.00</span></h5>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" id="addInvoiceSubmit" class="btn btn-primary">Save Invoice</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Invoice Modal -->
    <div class="modal fade" id="editInvoiceModal" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Charge Invoice</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form id="editInvoiceForm">
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" id="editInvoiceId">
              <input type="hidden" name="total_amount" id="editTotalAmount">

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label for="editInvoiceNo" class="form-label">Invoice No <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="editInvoiceNo" name="invoice_no" required>
                    <span class="input-group-text" id="editInvoiceNoSpinner" style="display: none;">
                      <span class="spinner-border spinner-border-sm"></span>
                    </span>
                  </div>
                  <div id="editInvoiceNoError" class="invalid-feedback"></div>
                </div>

                <div class="col-md-4 mb-3">
                  <label for="editInvoiceDate" class="form-label">Invoice Date <span
                      class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="editInvoiceDate" name="invoice_date" required>
                </div>

                <div class="col-md-4 mb-3">
                  <label for="editCustomer" class="form-label">Customer (Government Only) <span
                      class="text-danger">*</span></label>
                  <select class="form-select select2" id="editCustomer" name="customer_id" required
                    data-theme="bootstrap-5" style="width: 100%;">
                    <option value="">Select Government Customer</option>
                    <?php foreach ($customers as $customer): ?>
                      <option value="<?php echo $customer['id']; ?>" data-type="<?php echo $customer['customer_type']; ?>"
                        data-address="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
                        <?php echo htmlspecialchars($customer['name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text">Charge Sales Invoices are only available for Government customers.</div>
                </div>
              </div>

              <div class="mb-3">
                <label for="editAddress" class="form-label">Address</label>
                <textarea class="form-control" id="editAddress" name="address" rows="2"></textarea>
              </div>

              <div class="mb-3">
                <label for="editPaymentStatus" class="form-label">Payment Status</label>
                <select class="form-select" id="editPaymentStatus" name="payment_status" required>
                  <option value="Unpaid">Unpaid</option>
                  <option value="Partially Paid">Partially Paid</option>
                  <option value="Paid">Paid</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Link Deliveries <span class="text-danger">*</span></label>
                <div class="deliveries-list-container" id="editDeliveriesContainer">
                  <p class="text-center text-muted my-3">Loading deliveries...</p>
                </div>
              </div>

              <div class="row">
                <div class="col-md-8">
                  <div id="editTaxInfo" class="tax-info-box" style="display: none;">
                    <h6 class="mb-2" id="editTaxInfoTitle">Tax Calculation</h6>
                    <div class="row">
                      <div class="col-md-4">
                        <small class="text-muted">5% Withholding Tax:</small>
                        <div id="editTax5Display" class="fw-bold">₱0.00</div>
                      </div>
                      <div class="col-md-4">
                        <small class="text-muted">1% Withholding Tax:</small>
                        <div id="editTax1Display" class="fw-bold">₱0.00</div>
                      </div>
                      <div class="col-md-4">
                        <small class="text-muted">Net Amount:</small>
                        <div id="editNetAmountDisplay" class="fw-bold text-success">₱0.00</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="text-end">
                    <h5>Total Amount: <span id="editTotalDisplay" class="text-primary">₱0.00</span></h5>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" id="editInvoiceSubmit" class="btn btn-primary">Update Invoice</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Invoice Modal -->
    <div class="modal fade" id="viewInvoiceModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">View Charge Invoice</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="viewInvoiceContent">
            <div class="text-center">
              <div class="spinner-border" role="status">
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

    <!-- Delete Invoice Modal -->
    <div class="modal fade" id="deleteInvoiceModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form id="deleteInvoiceForm">
            <div class="modal-body">
              <p id="deleteInvoiceText"></p>
              <p class="text-danger"><strong>This action cannot be undone.</strong></p>
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="id" id="deleteInvoiceId">
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
        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body"></div>
    </div>
  </div>

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
  <script src="invoice_management.js"></script>

</body>

</html>