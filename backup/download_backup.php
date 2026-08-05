<?php
/**
 * Secure Backup Download Handler
 * Streams backup files from protected directory with authentication
 */

include_once __DIR__ . '/../config/session.php';

// Require authentication
if (!isset($_SESSION['ley_billing_user_id'])) {
    http_response_code(403);
    die('Access denied');
}

// Role check removed to allow non-admin access
/*
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    die('Admin access required');
}
*/

// Get filename from query parameter
$filename = $_GET['file'] ?? '';

// Validate filename
if (empty($filename)) {
    http_response_code(400);
    die('No file specified');
}

// Sanitize filename - prevent directory traversal
$filename = basename($filename);

// Ensure it's a SQL file
if (!preg_match('/\.sql$/', $filename)) {
    http_response_code(400);
    die('Invalid file type');
}

// Build file path
$backupDir = __DIR__ . '/backups/';
$filepath = $backupDir . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    die('File not found');
}

// Stream the file
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($filepath);
exit;
