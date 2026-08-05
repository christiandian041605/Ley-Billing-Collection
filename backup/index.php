<?php
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

include "../config/app.php";
include_once "../config/database.php";
require_once __DIR__ . '/backup_settings.php';
require_once __DIR__ . '/auto_backup.php';

// Set timezone for consistent scheduling
date_default_timezone_set('Asia/Manila');

$database = new Database();
$db = $database->getConnection();
$settings = new BackupSettings();

// Handle settings form submission first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_settings') {
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        $day = (int)$_POST['day'];
        $time = $_POST['time'];
        $retention = (int)$_POST['retention'];

        $settings->setEnabled($enabled);
        $settings->setSchedule($day, $time);
        $settings->setRetention($retention);

        $success_message = "Settings saved successfully!";
    }
}

// Check for and run scheduled backup (after potentially updating settings)
$autoBackup = new AutoBackup($settings); // Pass current settings instance
$autoResult = $autoBackup->run();
if ($autoResult['ran'] && $autoResult['success']) {
    $success_message = (isset($success_message) ? $success_message . " • " : "") . "Scheduled backup executed: " . $autoResult['filename'];
} elseif ($autoResult['ran'] && !$autoResult['success']) {
    $error_message = "Automated backup failed: " . $autoResult['message'];
}

// Fetch database info
$db_name = getenv('DB_NAME') ?: 'db_ley_supply_inventory';

// Get current settings
$currentSettings = $settings->getSettings();
$schedule = $settings->getSchedule();

// Get backup files using a more robust discovery method
$backupDir = __DIR__ . '/backups/';
$backupFiles = [];
if (is_dir($backupDir)) {
    $files = @scandir($backupDir);
    if ($files !== false) {
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === 'sql') {
                $filePath = $backupDir . $file;
                $backupFiles[] = [
                    'name' => $file,
                    'size' => @filesize($filePath) ?: 0,
                    'time' => @filemtime($filePath) ?: time()
                ];
            }
        }
    } else {
        $error_message = (isset($error_message) ? $error_message . " • " : "") . "The system cannot read the backup directory. Please check file permissions.";
    }
    // Sort by time (newest first)
    if (!empty($backupFiles)) {
        usort($backupFiles, function($a, $b) {
            return $b['time'] - $a['time'];
        });
    }
}
$recentBackups = array_slice($backupFiles, 0, 5);

// Days of week
$daysOfWeek = [
    0 => 'Sunday',
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday'
];

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
                            <h3 class="mb-0">Database Backup & Automation</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>/dashboard/">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Database Backup
                                </li>
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

                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-3 h4 mb-0"></i>
                            <div>
                                <strong>Success!</strong> <?php echo $success_message; ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-3 h4 mb-0"></i>
                            <div>
                                <strong>Error!</strong> <?php echo $error_message; ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Backup Settings -->
                        <div class="col-lg-6 mb-4">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="bi bi-gear-fill me-2"></i>Automated Backup Settings
                                    </h3>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="action" value="save_settings">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enabled" name="enabled" 
                                                    <?php echo $currentSettings['enabled'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="enabled">
                                                    <strong>Enable Automated Backups</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">When enabled, backups run automatically based on the schedule below.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="day" class="form-label">Backup Day</label>
                                            <select class="form-select" id="day" name="day">
                                                <?php foreach ($daysOfWeek as $value => $label): ?>
                                                <option value="<?php echo $value; ?>" 
                                                    <?php echo $schedule['day'] == $value ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="time" class="form-label">Backup Time</label>
                                            <input type="time" class="form-control" id="time" name="time" 
                                                value="<?php echo htmlspecialchars($schedule['time']); ?>" required>
                                            <small class="text-muted">24-hour format (e.g., 02:00 for 2:00 AM)</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="retention" class="form-label">Retention (Number of Backups to Keep)</label>
                                            <input type="number" class="form-control" id="retention" name="retention" 
                                                value="<?php echo $currentSettings['retention']; ?>" min="1" max="30" required>
                                            <small class="text-muted">Older backups will be automatically deleted.</small>
                                        </div>

                                        <?php if ($currentSettings['last_backup']): ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Last Backup:</strong> 
                                            <?php echo date('F j, Y g:i A', strtotime($currentSettings['last_backup'])); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Save Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Manual Backup -->
                        <div class="col-lg-6 mb-4">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="bi bi-database-fill-down me-2"></i>Manual Backup
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        <div class="display-1 text-success mb-3">
                                            <i class="bi bi-database-check"></i>
                                        </div>
                                        <h4>Ready to Back Up</h4>
                                        <p class="text-muted">
                                            Generate a full SQL export of your database instantly.
                                        </p>
                                    </div>

                                    <div class="alert alert-info">
                                        <ul class="mb-0">
                                            <li><strong>Database:</strong> <?php echo htmlspecialchars($db_name); ?></li>
                                            <li><strong>Format:</strong> SQL (compatible with phpMyAdmin)</li>
                                            <li><strong>Include:</strong> Structure & Data</li>
                                        </ul>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="backup_db.php?action=download" class="btn btn-success btn-lg">
                                            <i class="bi bi-download me-2"></i>Download Backup Now
                                        </a>
                                    </div>
                                </div>
                            </div>

                            </div>
                        </div>

                    <!-- Backup History & Archive -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="card card-outline card-secondary shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title fw-bold">
                                        <i class="bi bi-archive-fill me-2"></i>Backup History & Download Archive
                                    </h3>
                                    <?php if (!empty($backupFiles)): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">
                                            <i class="bi bi-database-check me-1"></i><?php echo count($backupFiles); ?> Backups Found
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="backup-table" class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-uppercase small fw-bold">
                                                <tr>
                                                    <th class="ps-4 py-3" style="width: 45%;">Backup Filename</th>
                                                    <th class="py-3">Created Date & Time</th>
                                                    <th class="py-3">File Size</th>
                                                    <th class="text-center px-4 py-3">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($backupFiles)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-5">
                                                        <div class="text-muted mb-3">
                                                            <i class="bi bi-cloud-slash display-4"></i>
                                                        </div>
                                                        <h5 class="text-muted fw-normal">No backup files found in the archive</h5>
                                                        <p class="text-muted small">Automated backups will appear here once they are generated.</p>
                                                    </td>
                                                </tr>
                                                <?php else: ?>
                                                    <?php foreach ($backupFiles as $backup): ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="d-flex align-items-center">
                                                                <div class="icon-box bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="bi bi-file-earmark-zip h4 mb-0"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($backup['name']); ?></div>
                                                                    <div class="text-muted x-small">SQL Database Dump</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="text-dark fw-medium"><?php echo date('F j, Y', $backup['time']); ?></div>
                                                            <div class="text-muted small"><?php echo date('g:i A', $backup['time']); ?></div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border fw-normal">
                                                                <?php echo number_format($backup['size'] / 1024, 2); ?> KB
                                                            </span>
                                                        </td>
                                                        <td class="text-center px-4">
                                                            <a href="download_backup.php?file=<?php echo urlencode($backup['name']); ?>" 
                                                               class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm hover-elevate">
                                                                <i class="bi bi-download me-1"></i>Download
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-light-subtle py-3 border-top-0">
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-shield-check me-2 h5 mb-0 text-success"></i>
                                        <span>Backups are stored in a protected directory and automatically managed based on your retention settings.</span>
                                    </div>
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

    <script>
        $(document).ready(function() {
            $('#backup-table').DataTable({
                "order": [[ 1, "desc" ]], // Sort by date by default
                "pageLength": 10,
                "responsive": true,
                "language": {
                    "emptyTable": "No backup files found in the archive",
                    "search": "Filter Backups:"
                },
                "columnDefs": [
                    { "orderable": false, "targets": 3 } // Disable sorting on action column
                ]
            });
        });

        // Automatically trigger download of the newly created scheduled backup
        window.addEventListener('load', function() {
            setTimeout(function() {
                const urlParams = new URLSearchParams(window.location.search);
                <?php if (isset($autoResult) && $autoResult['ran'] && $autoResult['success']): ?>
                const downloadUrl = 'download_backup.php?file=<?php echo urlencode($autoResult['filename']); ?>';
                window.location.href = downloadUrl;
                <?php endif; ?>
            }, 1500);
        });
    </script>

</body>
<!--end::Body-->

</html>