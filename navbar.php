      <?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="#" role="button" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
              </a>
            </li>
          </ul>
          <!--end::Start Navbar Links-->
          
          <!--begin::Brand Name-->
          <div class="navbar-brand mx-3">
            <span class="fw-bold text-primary"><?php include_once __DIR__ . '/config/app.php'; echo $app_name; ?></span>
          </div>
          <!--end::Brand Name-->
          
        
          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">          
            
          
            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
              </a>
            </li>
            <!--end::Fullscreen Toggle-->

            <!--begin::User Menu Dropdown-->
            <li class="nav-item ">
              <a href="#" class="nav-link">
                <span class="d-none d-md-inline"><?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User'; ?></span>
              </a>
            </li>

            <li class="nav-item ">
              <a href="../logout.php" class="nav-link">
                <span class="d-none d-md-inline">Sign Out</span>
              </a>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>