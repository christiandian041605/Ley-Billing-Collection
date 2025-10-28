<?php
// health_check.php - lightweight health endpoint
header('Content-Type: application/json');

$result = [
    'ok' => false,
    'timestamp' => date('c'),
    'db' => [ 'ok' => false, 'error' => null ],
];

// Try DB connection
require_once __DIR__ . '/config/database.php';
$db = (new Database())->getConnection();
if ($db) {
    try {
        $stmt = $db->query('SELECT 1');
        $result['db']['ok'] = true;
    } catch (Exception $e) {
        $result['db']['error'] = $e->getMessage();
    }
}

// Basic app checks
if ($result['db']['ok']) {
    $result['ok'] = true;
}

echo json_encode($result, JSON_PRETTY_PRINT);
