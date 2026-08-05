<?php include_once __DIR__ . '/../config/session.php'; ?>
<?php
// Ensure CSRF helper is available and token exists for the login form
if (file_exists(__DIR__ . '/../helpers/csrf.php')) {
  include_once __DIR__ . '/../helpers/csrf.php';
  ensure_csrf_token();
}
?>
<!doctype html>
<html lang="en">

<!--begin::Head-->
<?php include "../header.php"; ?>
<!--end::Head-->

<body class="login-page bg-body-secondary">
  <div class="login-box">
    <div class="card card-outline card-primary">
      <div class="card-header text-center">
        <h2 class="text-center mb-3"><?php echo $app_name; ?></h2>
      </div>
      <div class="card-body login-card-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <div id="loginAlert" class="alert" style="display: none;" role="alert"></div>
        <form id="loginForm">
          <?php if (function_exists('csrf_input_field')) {
            echo csrf_input_field();
          } ?>
          <div class="input-group mb-1">
            <div class="form-floating">
              <input id="loginUsername" name="username" type="text" class="form-control" value=""
                placeholder="Username" />
              <label for="loginUsername">Username</label>
            </div>
            <div class="input-group-text"><span class="bi bi-person"></span></div>
          </div>
          <div class="input-group mb-1">
            <div class="form-floating">
              <input id="loginPassword" name="password" type="password" class="form-control" placeholder="Password" />
              <label for="loginPassword">Password</label>
            </div>
            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
          </div>
          <!--begin::Row-->
          <div class="row">
            <div class="col-8 d-inline-flex align-items-center">
            </div>
            <!-- /.col -->
            <div class="col-12 mt-2">
              <div class="d-grid gap-2">
                <button type="submit" id="loginBtn" class="btn btn-primary btn-block">Sign In</button>
              </div>
            </div>
            <!-- /.col -->
          </div>
          <!--end::Row-->
        </form>
        <!-- /.social-auth-links -->
      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
    integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ=" crossorigin="anonymous"></script>
  <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
  <script src="../../../dist/js/adminlte.js"></script>
  <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'leave',
      scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function () {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
      if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: Default.scrollbarTheme,
            autoHide: Default.scrollbarAutoHide,
            clickScroll: Default.scrollbarClickScroll,
          },
        });
      }
    });

    // AJAX Login Form
    $(document).ready(function () {
      $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('#loginBtn');
        const alertBox = $('#loginAlert');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Signing In...');
        alertBox.hide();

        $.ajax({
          url: 'process_login.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              alertBox.removeClass('alert-danger').addClass('alert-success')
                .html('<i class="bi bi-check-circle me-2"></i>' + response.message)
                .show();
              setTimeout(function () {
                window.location.href = response.redirect;
              }, 1000);
            } else {
              alertBox.removeClass('alert-success').addClass('alert-danger')
                .html('<i class="bi bi-exclamation-triangle me-2"></i>' + response.message)
                .show();
              submitBtn.prop('disabled', false).html('Sign In');
            }
          },
          error: function (xhr) {
            let errorMsg = 'An error occurred during login';
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) { }
            alertBox.removeClass('alert-success').addClass('alert-danger')
              .html('<i class="bi bi-exclamation-triangle me-2"></i>' + errorMsg)
              .show();
            submitBtn.prop('disabled', false).html('Sign In');
          }
        });
      });

      <?php
      // Show session error if exists
      if (isset($_SESSION['error'])) {
        echo "$('#loginAlert').removeClass('alert-success').addClass('alert-danger')";
        echo ".html('<i class=\"bi bi-exclamation-triangle me-2\"></i>" . addslashes($_SESSION['error']) . "')";
        echo ".show();";
        unset($_SESSION['error']);
      }
      ?>
    });
  </script>
  <!--end::OverlayScrollbars Configure-->
  <!--end::Script-->
</body>
<!--end::Body-->

</html>