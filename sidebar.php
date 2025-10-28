<?php include_once __DIR__ . '/config/app.php'; ?>
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="#" class="brand-link">
            <!--begin::Brand Image-->
            <img src="<?php echo $base_url; ?>/dist/img/<?php echo $logo; ?>" alt="<?php echo $app_name; ?> Logo" class="brand-image opacity-75 shadow">
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light"><?php echo $app_name; ?></span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->

        
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <?php $current_page = $_SERVER['REQUEST_URI']; ?>
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="menu"
              data-accordion="false">

              <li class="nav-item"><a href="../dashboard/" class="nav-link <?php echo (strpos($current_page, '/dashboard/') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Dashboard</p>
                </a>
              </li> 
              
              <li class="nav-item"><a href="../users/" class="nav-link <?php echo (strpos($current_page, '/users/') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon bi bi-people"></i>
                  <p>Users</p>
                </a>
              </li> 
              <li class="nav-item"><a href="../responsibility_matrix/" class="nav-link <?php echo (strpos($current_page, '/responsibility_matrix/') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon bi bi-diagram-3"></i>
                  <p>Responsibility Matrix</p>
                </a>
              </li> 
              <li class="nav-item"><a href="../objectives/" class="nav-link <?php echo (strpos($current_page, '/objectives/') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon bi bi-journal-check"></i>
                  <p>Objectives</p>
                </a>
              </li> 
              <li class="nav-item"><a href="../sdp/" class="nav-link <?php echo (strpos($current_page, '/sdp/') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon bi bi-list-task"></i>
                  <p>SDP</p>
                </a>
              </li> 
              <li class="nav-item"><a href="../app_setting/" class="nav-link <?php echo (strpos($current_page, '/app_setting/') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon bi bi-gear"></i>
                  <p>App Settings</p>
                </a>
              </li> 
              <li class="nav-item"><a href="../activity_log/" class="nav-link <?php echo (strpos($current_page, '/activity_log/') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon bi bi-clock-history"></i>
                  <p>Activity Logs</p>
                </a>
              </li> 
              
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>