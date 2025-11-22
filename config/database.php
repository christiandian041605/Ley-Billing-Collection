<?php
// Database connection wrapper.
// This reads connection parameters from environment variables when available,
// falling back to the previous defaults for local development (XAMPP).

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'db_ley_billing';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $socket = getenv('DB_SOCKET') ?: '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            if ($this->host === 'localhost' && file_exists($socket)) {
                $dsn = "mysql:unix_socket={$socket};dbname={$this->db_name};charset=utf8mb4";
            }
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch(PDOException $exception) {
            // Don't echo sensitive info in production. Log the error and re-throw.
            error_log('Database connection error: ' . $exception->getMessage());
            throw $exception;
        }
        return $this->conn;
    }
}
