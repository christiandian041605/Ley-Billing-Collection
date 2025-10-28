<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../config/app.php";
include_once "../config/database.php";
include "app_setting.php";

$database = new Database();
$db = $database->getConnection();

$app_setting = new AppSetting($db);

// Fetch the single app setting entry
$app_setting->setting_id = 1; // Assuming there is only one setting record with ID 1
$app_setting->readOne();

// Extract variables with fallbacks
$setting_id = $app_setting->setting_id ?? 1;
$app_name = $app_setting->app_name ?? '';
$address = $app_setting->address ?? '';
$contact_number = $app_setting->contact_number ?? '';
$email = $app_setting->email ?? '';
$about = $app_setting->about ?? '';
$logo = $app_setting->logo ?? 'default-logo.png';
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
              <div class="col-sm-6">
                <h3 class="mb-0">App Settings</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>/dashboard/">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">
                    App Settings
                  </li>
                </ol>
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
                    <div class="card">
                        <div class="card-body">
                            <form id="appSettingsForm" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="setting_id" value="<?php echo htmlspecialchars($setting_id); ?>">
                                <input type="hidden" name="action" value="update">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="appName" class="form-label">App Name</label>
                                            <input type="text" class="form-control" id="appName" name="app_name" value="<?php echo htmlspecialchars($app_name); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contactNumber" class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" id="contactNumber" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="about" class="form-label">About</label>
                                            <textarea class="form-control" id="about" name="about" rows="5"><?php echo htmlspecialchars($about); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">Logo</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" id="logo" name="logo">
                                            </div>
                                            <div class="mt-2">
                                                <img src="<?php echo $base_url; ?>/dist/img/<?php echo htmlspecialchars($logo); ?>" alt="Current Logo" id="logo-preview" class="img-thumbnail" width="150">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" id="saveSettingsBtn" class="btn btn-primary">Save Settings</button>
                            </form>
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
        $(document).ready(function() {
            // Preview logo before upload
            $('#logo').on('change', function() {
                const [file] = this.files
                if (file) {
                    $('#logo-preview').attr('src', URL.createObjectURL(file));
                }
            });

            // AJAX Form Submission for App Settings
            $('#appSettingsForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $('#saveSettingsBtn');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: 'process.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            if (response.logo) {
                                $('#logo-preview').attr('src', '<?php echo $base_url; ?>/dist/img/' + response.logo);
                            }
                            submitBtn.prop('disabled', false).html('Save Settings');
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html('Save Settings');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred while updating app settings';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showToast('error', errorMsg);
                        submitBtn.prop('disabled', false).html('Save Settings');
                    }
                });
            });

            // Show toast notification if there are any session messages
            <?php
            if (isset($_SESSION['success'])) {
                echo "showToast('success', '{$_SESSION['success']}');";
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo "showToast('error', '{$_SESSION['error']}');";
                unset($_SESSION['error']);
            }
            ?>

            function showToast(type, message) {
                var toastLiveExample = document.getElementById('liveToast');
                var toastBody = toastLiveExample.querySelector('.toast-body');
                
                var toastHeader = toastLiveExample.querySelector('.toast-header');
                toastHeader.classList.remove('bg-success', 'bg-danger', 'text-white');
                if (type === 'success') {
                    toastHeader.classList.add('bg-success', 'text-white');
                } else {
                    toastHeader.classList.add('bg-danger', 'text-white');
                }

                toastBody.textContent = message;
                var toast = new bootstrap.Toast(toastLiveExample);
                toast.show();
            }
        });
    </script>
    
  </body>
  <!--end::Body-->
</html>