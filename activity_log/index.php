<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../config/app.php";
include "activity_log.php";

$activity_log = new ActivityLog($db);

// Fetch all logs
$stmt = $activity_log->read();
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
              <div class="col-sm-6"><h3 class="mb-0">Activity Logs</h3></div>
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
                <div class="card">
                  <div class="card-body">
                    <table id="logsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Details</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                extract($row);
                                echo "<tr>";
                                echo "<td>{$log_id}</td>";
                                echo "<td>{$full_name}</td>";
                                echo "<td>{$action}</td>";
                                echo "<td>{$module}</td>";
                                echo "<td>{$details}</td>";
                                echo "<td>{$created_at}</td>";
                                echo "</tr>";
                            }
                            ?>
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

      <!--begin::Footer-->
      <?php include "../footer.php"; ?>
      <!--end::Footer-->

    </div>
    <!--end::App Wrapper-->

    <!--begin::Script-->
    <?php include "../script.php"; ?>
    <script>
        $(document).ready(function() {
            $('#logsTable').DataTable({
                "order": [[ 0, "desc" ]]
            });
        });
    </script>
    
  </body>
  <!--end::Body-->
</html>