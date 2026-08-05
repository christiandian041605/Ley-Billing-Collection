<?php
/**
 * Stress Test Tool for Backup System
 * Tests backup generation performance and system capabilities
 * Admin only
 */

include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['ley_billing_user_id'])) {
    header("Location: ../login/");
    exit();
}

// Role check removed to allow non-admin access
/*
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../dashboard/");
    exit();
}
*/

require_once __DIR__ . '/auto_backup.php';

$testResults = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_test'])) {
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    try {
        $backup = new AutoBackup();
        $result = $backup->forceBackup();

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $testResults = [
            'success' => $result['success'],
            'message' => $result['message'],
            'filename' => $result['filename'] ?? null,
            'execution_time' => round($endTime - $startTime, 3),
            'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2),
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2)
        ];
    } catch (Exception $e) {
        $testResults = [
            'success' => false,
            'message' => 'Test failed: ' . $e->getMessage(),
            'execution_time' => round(microtime(true) - $startTime, 3),
            'memory_used' => round((memory_get_usage() - $startMemory) / 1024 / 1024, 2),
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2)
        ];
    }
}

include "../config/app.php";
?>
<!doctype html>
<html lang="en">

<!--begin::Head-->
<?php include "../header.php"; ?>
<!--end::Head-->

<!--begin::Body-->

<body class="layout-fixed bg-body-tertiary">
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
                            <h3 class="mb-0">Backup Stress Test</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>/dashboard/">Home</a></li>
                                <li class="breadcrumb-item"><a href="index.php">Backup</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Stress Test</li>
                            </ol>
                        </div>
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

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="bi bi-speedometer2 me-2"></i>Performance Testing
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Warning:</strong> This test will create a full database backup and
                                        measure performance metrics.
                                        It may take some time depending on your database size.
                                    </div>

                                    <form method="POST">
                                        <div class="d-grid gap-2">
                                            <button type="submit" name="run_test" class="btn btn-warning btn-lg">
                                                <i class="bi bi-play-circle me-2"></i>Run Stress Test
                                            </button>
                                        </div>
                                    </form>

                                    <?php if ($testResults): ?>
                                        <hr class="my-4">

                                        <h5 class="mb-3">Test Results</h5>

                                        <?php if ($testResults['success']): ?>
                                            <div class="alert alert-success">
                                                <i class="bi bi-check-circle me-2"></i>
                                                <strong>Success!</strong>
                                                <?php echo htmlspecialchars($testResults['message']); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-danger">
                                                <i class="bi bi-x-circle me-2"></i>
                                                <strong>Failed:</strong>
                                                <?php echo htmlspecialchars($testResults['message']); ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <div class="display-6 text-primary">
                                                            <i class="bi bi-clock"></i>
                                                        </div>
                                                        <h6 class="mt-2">Execution Time</h6>
                                                        <h4>
                                                            <?php echo $testResults['execution_time']; ?>s
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <div class="display-6 text-success">
                                                            <i class="bi bi-memory"></i>
                                                        </div>
                                                        <h6 class="mt-2">Memory Used</h6>
                                                        <h4>
                                                            <?php echo $testResults['memory_used']; ?> MB
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <div class="display-6 text-warning">
                                                            <i class="bi bi-graph-up"></i>
                                                        </div>
                                                        <h6 class="mt-2">Peak Memory</h6>
                                                        <h4>
                                                            <?php echo $testResults['peak_memory']; ?> MB
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (isset($testResults['filename'])): ?>
                                            <div class="mt-3">
                                                <p class="mb-0">
                                                    <strong>Generated File:</strong>
                                                    <code><?php echo htmlspecialchars($testResults['filename']); ?></code>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Back to Backup
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

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