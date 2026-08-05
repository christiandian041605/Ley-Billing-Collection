<?php
/**
 * Silent Backup Trigger
 * Called via AJAX from dashboard to run automated backups in background
 * No authentication required - backup logic handles schedule checking
 */

require_once __DIR__ . '/auto_backup.php';

// Suppress any output except JSON
ob_start();

try {
    $backup = new AutoBackup();
    $result = $backup->run();

    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'ran' => false
    ]);
}
