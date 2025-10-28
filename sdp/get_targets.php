<?php
include_once __DIR__ . '/../config/session.php';
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include_once "../config/database.php";
include "sdp.php";

$database = new Database();
$db = $database->getConnection();
$sdp = new Sdp($db);

$sdp->sdp_id = $_POST['sdp_id'] ?? null;

if (!$sdp->sdp_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing sdp_id']);
    exit();
}

$targets = $sdp->getTargets();

header('Content-Type: application/json');
echo json_encode(['targets' => $targets]);
