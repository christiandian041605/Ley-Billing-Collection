<?php include_once __DIR__ . '/config/app.php'; ?>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $app_name; ?></title>

    <script>
        window.csrf_token = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    </script>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="<?php echo $app_name; ?>" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Third Party Plugin(Font Awesome)-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--end::Third Party Plugin(Font Awesome)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../dist/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::Custom Styles-->
    <link rel="stylesheet" href="../dist/css/custom.css" />
    <link rel="stylesheet" href="../dist/css/custom_brand.css" />
    <!--end::Custom Styles-->
  <!-- Custom font stack (Optima first) -->
  <link rel="stylesheet" href="../dist/css/custom-fonts.css" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
    /* Sidebar Toggle Functionality */
    .app-sidebar {
        transition: transform 0.3s ease-in-out;
        width: 250px;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }
    
    .sidebar-wrapper {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    .sidebar-collapse .app-sidebar {
        transform: translateX(-100%);
    }
    
    .app-main {
        margin-left: 250px;
        transition: margin-left 0.3s ease-in-out;
    }
    
    .sidebar-collapse .app-main {
        margin-left: 0;
    }
    
    .app-header {
        margin-left: 250px;
        transition: margin-left 0.3s ease-in-out;
    }
    
    .sidebar-collapse .app-header {
        margin-left: 0;
    }
    
    .app-footer {
        margin-left: 250px;
        transition: margin-left 0.3s ease-in-out;
    }
    
    .sidebar-collapse .app-footer {
        margin-left: 0;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .app-sidebar {
            transform: translateX(-100%);
        }
        
        .app-main,
        .app-header {
            margin-left: 0;
        }
        
        .sidebar-open .app-sidebar {
            transform: translateX(0);
        }
    }
    
    /* Treeview Menu Styles */
    .nav-treeview {
        display: none;
        list-style: none;
        padding-left: 0;
    }
    
    .has-treeview.menu-open > .nav-treeview {
        display: block !important;
    }
    
    .nav-treeview .nav-item .nav-link {
        padding-left: 2rem;
    }
    
    .has-treeview > .nav-link .nav-arrow {
        transition: transform 0.3s ease;
        float: right;
        margin-top: 3px;
    }
    
    .has-treeview.menu-open > .nav-link .nav-arrow {
        transform: rotate(90deg);
    }
    
    .nav-treeview .nav-link {
        font-size: 0.9rem;
    }
    </style>
  </head>