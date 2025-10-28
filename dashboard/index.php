


<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}
$app_name = "AdminLTE 44";
?>
<!doctype html>
<html lang="en">

  <!--begin::Head-->
  <?php include "../header.php"; ?>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
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
              <div class="col-sm-6"><h3 class="mb-0">Fixed Layout</h3></div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">

                    <!--begin::Container-->
          <div class="container-fluid">

            <!--begin::Row-->
            <div class="row">
              <div class="col-12">
                <!-- Default box -->
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Title</h3>
                    <div class="card-tools">
                      
                    </div>
                  </div>
                  <div class="card-body">Start creating your amazing application!</div>
                  <!-- /.card-body -->
                  <div class="card-footer">Footer</div>
                  <!-- /.card-footer-->
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

      <!--begin::Footer-->
      <?php include "../footer.php"; ?>
      <!--end::Footer-->

    </div>
    <!--end::App Wrapper-->

    <!--begin::Script-->
    <?php include "../script.php"; ?>
    <!--end::Script-->
    
  </body>
  <!--end::Body-->
</html>
