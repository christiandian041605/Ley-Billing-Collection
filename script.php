<?php include_once __DIR__ . '/config/app.php'; ?>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!--begin::Required Plugin(Bootstrap 5 Bundle - includes Popper)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5 Bundle)-->
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="<?php echo $base_url; ?>/dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
    
    <script>
    // Sidebar toggle functionality
    function toggleSidebar() {
        const body = document.body;
        const sidebar = document.querySelector('.app-sidebar');
        
        // Only proceed if sidebar exists
        if (!sidebar) {
            return;
        }
        
        // Check if mobile
        if (window.innerWidth <= 768) {
            if (body.classList.contains('sidebar-open')) {
                body.classList.remove('sidebar-open');
            } else {
                body.classList.add('sidebar-open');
            }
        } else {
            // Desktop behavior
            if (body.classList.contains('sidebar-collapse')) {
                body.classList.remove('sidebar-collapse');
                localStorage.setItem('sidebar-state', 'open');
            } else {
                body.classList.add('sidebar-collapse');
                localStorage.setItem('sidebar-state', 'collapsed');
            }
        }
    }
    
    // Initialize sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Only apply saved state on desktop
        if (window.innerWidth > 768) {
            const savedState = localStorage.getItem('sidebar-state');
            if (savedState === 'collapsed') {
                document.body.classList.add('sidebar-collapse');
            }
        } else {
            // On mobile, start with sidebar hidden
            document.body.classList.remove('sidebar-collapse');
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const body = document.body;
        if (window.innerWidth <= 768) {
            // Mobile: remove desktop classes
            body.classList.remove('sidebar-collapse');
        } else {
            // Desktop: remove mobile classes and restore saved state
            body.classList.remove('sidebar-open');
            const savedState = localStorage.getItem('sidebar-state');
            if (savedState === 'collapsed') {
                body.classList.add('sidebar-collapse');
            }
        }
    });
    
    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.app-sidebar');
            const toggleBtn = document.querySelector('[onclick="toggleSidebar()"]');
            const body = document.body;
            
            if (body.classList.contains('sidebar-open') && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target)) {
                body.classList.remove('sidebar-open');
            }
        }
    });
    </script>