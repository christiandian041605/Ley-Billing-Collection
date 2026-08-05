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

include_once "../config/database.php";

// Set time and memory limits for large databases
set_time_limit(0);
ini_set('memory_limit', '512M');

class DatabaseBackup
{
    private $db;
    private $host;
    private $user;
    private $pass;
    private $name;

    public function __construct($db)
    {
        $this->db = $db;
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->name = getenv('DB_NAME') ?: 'db_ley_supply_inventory';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->pass = getenv('DB_PASS') ?: '';
    }

    public function export()
    {
        try {
            $tables = array();
            $result = $this->db->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $return = "-- Database: `" . $this->name . "`\n";
            $return .= "-- Generation Time: " . date("Y-m-d H:i:s") . "\n\n";
            $return .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $return .= "SET time_zone = \"+00:00\";\n";
            $return .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            $constraints = [];

            foreach ($tables as $table) {
                // Drop existing table
                $return .= "DROP TABLE IF EXISTS `" . $table . "`;\n";

                // Get table structure
                $result = $this->db->query("SHOW CREATE TABLE `" . $table . "`");
                $row = $result->fetch(PDO::FETCH_NUM);
                $createSql = $row[1];

                // Extract and strip constraints to add later
                // This prevents foreign key errors during table creation
                if (preg_match_all('/^\s*CONSTRAINT\s+.*$/m', $createSql, $matches)) {
                    foreach ($matches[0] as $match) {
                        // Clean up the constraint string (remove trailing comma and whitespace)
                        $cleanConstraint = rtrim(trim($match), ',');
                        $constraints[] = "ALTER TABLE `" . $table . "` ADD " . $cleanConstraint . ";";
                    }
                    // Remove constraint lines from CREATE TABLE
                    $createSql = preg_replace('/^\s*CONSTRAINT\s+.*$/m', '', $createSql);
                    // Remove empty lines resulting from removal
                    $createSql = preg_replace('/^\n/m', '', $createSql);
                    // Remove trailing comma from the last column/index definition if it exists
                    // This finds a comma followed by whitespace and the closing parenthesis
                    $createSql = preg_replace('/,(\s*)\)/', '$1)', $createSql);
                }

                $return .= "\n\n" . $createSql . ";\n\n";

                // Get table data
                $result = $this->db->query("SELECT * FROM " . $table);
                $num_fields = $result->columnCount();

                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $return .= "INSERT INTO " . $table . " VALUES(";
                    for ($j = 0; $j < $num_fields; $j++) {
                        if (isset($row[$j])) {
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = str_replace("\n", "\\n", $row[$j]);
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= 'NULL';
                        }
                        if ($j < ($num_fields - 1)) {
                            $return .= ',';
                        }
                    }
                    $return .= ");\n";
                }
                $return .= "\n\n\n";
            }

            // Add constraints at the end
            if (!empty($constraints)) {
                $return .= "\n-- \n-- Constraints for dumped tables\n-- \n\n";
                foreach ($constraints as $constraint) {
                    $return .= $constraint . "\n";
                }
            }

            $return .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";

            // Filename
            $fileName = $this->name . '_backup_' . date("Y-m-d_H-i-s") . '.sql';

            // Set headers for download
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . strlen($return));

            echo $return;
            exit;

        } catch (Exception $e) {
            die("Error during backup: " . $e->getMessage());
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'download') {
    $database = new Database();
    $db = $database->getConnection();
    $backup = new DatabaseBackup($db);
    $backup->export();
}
