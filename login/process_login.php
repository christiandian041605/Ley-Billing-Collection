<?php
include_once __DIR__ . '/../config/session.php';
include_once '../config/database.php';

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please provide both username and password']);
        exit();
    }

    $query = "SELECT * FROM tbl_users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['ley_billing_user_id'] = $user['id']; // Updated to use id field from tbl_users
        $_SESSION['full_name'] = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']);
        $_SESSION['role'] = $user['role'];
        // Generate CSRF token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => '../dashboard/index.php']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
    exit();
}
