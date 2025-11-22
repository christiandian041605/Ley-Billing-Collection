<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

include "../config/app.php";
include_once "../config/database.php";
include "charge_invoice.php";

$database = new Database();
$db = $database->getConnection();
$invoice = new ChargeInvoice($db);
?>
<!doctype html>
<html lang="en">

  <!--begin::Head-->
  <?php include "../header.php"; ?>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="layout-fixed sidebar-mini bg-body-tertiary">
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
              <div class="col-sm-6"><h3 class="mb-0">Charge Invoices</h3></div>
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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">Create Invoice</button>
                    <div class="card-tools"></div>
                  </div>
                  <div class="card-body">
                    <table id="invoicesTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
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
            <!--end::Row-->

          </div>
          <!--end::Container-->

        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!-- Add Invoice Modal -->
      <div class="modal fade" id="addInvoiceModal" tabindex="-1" aria-labelledby="addInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addInvoiceModalLabel">Create New Invoice</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addInvoiceForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="addInvoiceDate" class="form-label">Invoice Date</label>
                    <input type="date" class="form-control" id="addInvoiceDate" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required>
                  </div>
                  
                  <div class="col-md-6 mb-3">
                    <label for="addCustomer" class="form-label">Customer</label>
                    <select class="form-select" id="addCustomer" name="customer_id" required>
                      <option value="">Select Customer</option>
                    </select>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="addAddress" class="form-label">Address</label>
                  <textarea class="form-control" id="addAddress" name="address" rows="2"></textarea>
                </div>
                
                <hr>
                <h6>Invoice Items</h6>
                
                <table class="table table-bordered" id="addItemsTable">
                  <thead>
                    <tr>
                      <th style="width: 50%">Description</th>
                      <th style="width: 15%">Quantity</th>
                      <th style="width: 15%">Unit Price</th>
                      <th style="width: 15%">Amount</th>
                      <th style="width: 5%">Action</th>
                    </tr>
                  </thead>
                  <tbody id="addItemsBody">
                    <tr class="item-row">
                      <td><input type="text" class="form-control item-description" name="items[0][description]" required></td>
                      <td><input type="number" step="0.01" class="form-control item-qty" name="items[0][qty]" value="1" required></td>
                      <td><input type="number" step="0.01" class="form-control item-price" name="items[0][unit_price]" value="0" required></td>
                      <td><input type="number" step="0.01" class="form-control item-amount" name="items[0][amount]" value="0" readonly></td>
                      <td><button type="button" class="btn btn-danger btn-sm remove-item-btn" disabled><i class="bi bi-trash"></i></button></td>
                    </tr>
                  </tbody>
                </table>
                
                <button type="button" class="btn btn-success btn-sm" id="addItemBtn"><i class="bi bi-plus"></i> Add Item</button>
                
                <div class="row mt-3">
                  <div class="col-md-8"></div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="fw-bold">Total Amount:</label>
                      <input type="number" step="0.01" class="form-control form-control-lg fw-bold" id="addTotalAmount" name="total_amount" value="0" readonly>
                    </div>
                  </div>
                </div>
                
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="addInvoiceSubmit" class="btn btn-primary">Create Invoice</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Edit Invoice Modal (similar structure to Add) -->
      <div class="modal fade" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editInvoiceModalLabel">Edit Invoice</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editInvoiceForm">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="editInvoiceId" name="id">
                <input type="hidden" name="action" value="update">
                
                <div class="alert alert-info">
                  <strong>Invoice No:</strong> <span id="editInvoiceNo"></span>
                </div>
                
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="editInvoiceDate" class="form-label">Invoice Date</label>
                    <input type="date" class="form-control" id="editInvoiceDate" name="invoice_date" required>
                  </div>
                  
                  <div class="col-md-6 mb-3">
                    <label for="editCustomer" class="form-label">Customer</label>
                    <select class="form-select" id="editCustomer" name="customer_id" required>
                      <option value="">Select Customer</option>
                    </select>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="editAddress" class="form-label">Address</label>
                  <textarea class="form-control" id="editAddress" name="address" rows="2"></textarea>
                </div>
                
                <hr>
                <h6>Invoice Items</h6>
                
                <table class="table table-bordered" id="editItemsTable">
                  <thead>
                    <tr>
                      <th style="width: 50%">Description</th>
                      <th style="width: 15%">Quantity</th>
                      <th style="width: 15%">Unit Price</th>
                      <th style="width: 15%">Amount</th>
                      <th style="width: 5%">Action</th>
                    </tr>
                  </thead>
                  <tbody id="editItemsBody">
                  </tbody>
                </table>
                
                <button type="button" class="btn btn-success btn-sm" id="editAddItemBtn"><i class="bi bi-plus"></i> Add Item</button>
                
                <div class="row mt-3">
                  <div class="col-md-8"></div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="fw-bold">Total Amount:</label>
                      <input type="number" step="0.01" class="form-control form-control-lg fw-bold" id="editTotalAmount" name="total_amount" value="0" readonly>
                    </div>
                  </div>
                </div>
                
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="editInvoiceSubmit" class="btn btn-primary">Update Invoice</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- View Invoice Modal -->
      <div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="viewInvoiceModalLabel">Invoice Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewInvoiceContent">
              <div class="text-center"><span class="spinner-border spinner-border-sm"></span> Loading...</div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Invoice Modal -->
      <div class="modal fade" id="deleteInvoiceModal" tabindex="-1" aria-labelledby="deleteInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteInvoiceModalLabel">Confirm Delete</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteInvoiceForm">
                <div class="modal-body">
                    <p id="deleteInvoiceConfirmationText"></p>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" id="deleteInvoiceId" name="id">
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
    <script src="invoice.js"></script>
    
  </body>
  <!--end::Body-->
</html>