<?php include_once __DIR__ . '/config/app.php'; ?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">



  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <!--begin::Sidebar Menu-->
      <?php $current_page = $_SERVER['REQUEST_URI']; ?>
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

        <!-- Main Dashboard -->

        <li class="nav-item"><a href="../dashboard/"
            class="nav-link <?php echo (strpos($current_page, '/dashboard/') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-speedometer2"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- Accounts Section -->
        <li class="nav-header">ACCOUNTS</li>

        <li class="nav-item"><a href="../customers/"
            class="nav-link <?php echo (strpos($current_page, '/customers/') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-person-vcard"></i>
            <p>Customers</p>
          </a>
        </li>

        <li class="nav-header">DELIVERY</li>
        <li class="nav-item">
          <a href="../deliveries/?type=dr_v"
            class="nav-link <?php echo (strpos($current_page, 'type=dr_v') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-circle"></i>
            <p>Various</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../deliveries/?type=dr_government"
            class="nav-link <?php echo (strpos($current_page, 'type=dr_government') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-circle"></i>
            <p>Government</p>
          </a>
        </li>

        <li class="nav-header">CHARGE SALES INVOICE</li>
        <li class="nav-item">
          <a href="../charge_invoices/?type=government"
            class="nav-link <?php echo (strpos($current_page, '/charge_invoices/') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-receipt"></i>
            <p>Charge Invoices</p>
          </a>
        </li>

        <!-- Payment Details -->
        <li class="nav-header">PAYMENT DETAILS</li>
        <li class="nav-item">
          <a href="../payments/?category=private"
            class="nav-link <?php echo (strpos($current_page, 'category=private') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-cash-coin"></i>
            <p>Private/Customer</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../payments/?category=government"
            class="nav-link <?php echo (strpos($current_page, 'category=government') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-bank2"></i>
            <p>Government (Charge Sales)</p>
          </a>
        </li>

        <!-- Reports -->
        <li class="nav-header">REPORTS</li>
        <li class="nav-item"><a href="../reports/"
            class="nav-link <?php echo (strpos($current_page, '/reports/') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
            <p>Statement of Accounts</p>
          </a>
        </li>

        <!-- Management -->
        <li class="nav-header">MANAGEMENT</li>
        <li class="nav-item"><a href="../banks/"
            class="nav-link <?php echo (strpos($current_page, '/banks/') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-bank"></i>
            <p>Banks & Branches</p>
          </a>
        </li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
          <li class="nav-item"><a href="../users/"
              class="nav-link <?php echo (strpos($current_page, '/users/') !== false) ? 'active' : ''; ?>">
              <i class="nav-icon bi bi-people"></i>
              <p>Users</p>
            </a>
          </li>
        <?php endif; ?>

        <!-- System -->
        <li class="nav-header">SYSTEM</li>

        <!-- Database Backup - Available to all users -->
        <li class="nav-item">
          <a href="../backup/"
            class="nav-link <?php echo (strpos($current_page, '/backup/') !== false) ? 'active' : ''; ?>">
            <i class="nav-icon bi bi-database-down"></i>
            <p>Database Backup</p>
          </a>
        </li>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
          <li class="nav-item"><a href="../app_setting/"
              class="nav-link <?php echo (strpos($current_page, '/app_setting/') !== false) ? 'active' : ''; ?>">
              <i class="nav-icon bi bi-gear"></i>
              <p>App Settings</p>
            </a>
          </li>

          <li class="nav-item"><a href="../activity_log/"
              class="nav-link <?php echo (strpos($current_page, '/activity_log/') !== false) ? 'active' : ''; ?>">
              <i class="nav-icon bi bi-clock-history"></i>
              <p>Activity Logs</p>
            </a>
          </li>
        <?php endif; ?>

      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>