<?php
/**
 * Automated Backup Script
 * Generates database backups based on schedule and manages retention
 */

// Set time and memory limits
set_time_limit(0);
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/backup_settings.php';

// Ensure consistent timezone for scheduling
date_default_timezone_set('Asia/Manila');

class AutoBackup
{
    private $db;
    private $settings;
    private $backupDir;
    private $dbName;
    private $host;
    private $user;
    private $pass;

    public function __construct($settings = null)
    {
        $this->settings = $settings ?: new BackupSettings();
        $this->backupDir = __DIR__ . '/backups/';

        // Database credentials
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->dbName = getenv('DB_NAME') ?: 'db_ley_supply_inventory';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->pass = getenv('DB_PASS') ?: '';

        // Get database connection
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Execute backup if conditions are met
     */
    public function run()
    {
        if (!$this->settings->shouldRunBackup()) {
            return [
                'success' => false,
                'message' => 'Backup not scheduled or already completed today',
                'ran' => false
            ];
        }

        try {
            $filename = $this->generateBackup();
            if ($filename) {
                $this->settings->updateLastBackup();
                $this->manageRetention();
                error_log("AutoBackup: Success - Created $filename");
            }

            return [
                'success' => true,
                'message' => 'Backup completed successfully',
                'filename' => $filename,
                'ran' => true
            ];
        } catch (Exception $e) {
            error_log("AutoBackup: Error - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
                'ran' => false
            ];
        }
    }

    /**
     * Generate SQL backup file
     */
    private function generateBackup()
    {
        $tables = [];
        $result = $this->db->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sql = "-- Database: `{$this->dbName}`\n";
        $sql .= "-- Generation Time: " . date("Y-m-d H:i:s") . "\n\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET time_zone = \"+00:00\";\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        $constraints = [];

        foreach ($tables as $table) {
            // Drop existing table
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            // Get table structure
            $result = $this->db->query("SHOW CREATE TABLE `{$table}`");
            $row = $result->fetch(PDO::FETCH_NUM);
            $createSql = $row[1];

            // Extract and strip constraints
            if (preg_match_all('/^\s*CONSTRAINT\s+.*$/m', $createSql, $matches)) {
                foreach ($matches[0] as $match) {
                    $cleanConstraint = rtrim(trim($match), ',');
                    $constraints[] = "ALTER TABLE `{$table}` ADD {$cleanConstraint};";
                }
                $createSql = preg_replace('/^\s*CONSTRAINT\s+.*$/m', '', $createSql);
                $createSql = preg_replace('/^\\n/m', '', $createSql);
                $createSql = preg_replace('/,(\s*)\)/', '$1)', $createSql);
            }

            $sql .= "\n\n{$createSql};\n\n";

            // Get table data
            $result = $this->db->query("SELECT * FROM {$table}");
            $numFields = $result->columnCount();

            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $sql .= "INSERT INTO {$table} VALUES(";
                for ($j = 0; $j < $numFields; $j++) {
                    if (isset($row[$j])) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                        $sql .= '"' . $row[$j] . '"';
                    } else {
                        $sql .= 'NULL';
                    }
                    if ($j < ($numFields - 1)) {
                        $sql .= ',';
                    }
                }
                $sql .= ");\n";
            }
            $sql .= "\n\n\n";
        }

        // Add constraints at the end
        if (!empty($constraints)) {
            $sql .= "\n-- \n-- Constraints for dumped tables\n-- \n\n";
            foreach ($constraints as $constraint) {
                $sql .= $constraint . "\n";
            }
        }

        $sql .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";

        // Generate filename
        $filename = $this->dbName . '_' . date("Y-m-d_H-i-s") . '.sql';
        $filepath = $this->backupDir . $filename;

        // Ensure backup directory exists and is writable
        if (!is_dir($this->backupDir)) {
            if (!@mkdir($this->backupDir, 0777, true)) {
                throw new Exception("Could not create backup directory: {$this->backupDir}");
            }
        }

        if (@file_put_contents($filepath, $sql) === false) {
            throw new Exception("Could not write backup file to $filepath. Check permissions.");
        }

        return $filename;
    }

    /**
     * Manage backup retention - delete old backups
     */
    private function manageRetention()
    {
        $retention = (int) $this->settings->getRetention();
        $backupFiles = [];

        if (is_dir($this->backupDir)) {
            $files = @scandir($this->backupDir);
            if ($files !== false) {
                foreach ($files as $file) {
                    if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'sql') {
                        $filePath = $this->backupDir . $file;
                        $backupFiles[] = [
                            'path' => $filePath,
                            'time' => @filemtime($filePath) ?: 0
                        ];
                    }
                }
            }
        }

        // Sort by time (newest first)
        usort($backupFiles, function ($a, $b) {
            return $b['time'] - $a['time'];
        });

        // Delete old backups beyond retention limit
        if (count($backupFiles) > $retention) {
            $filesToDelete = array_slice($backupFiles, $retention);
            foreach ($filesToDelete as $file) {
                @unlink($file['path']);
            }
        }
    }

    /**
     * Force backup regardless of schedule
     */
    public function forceBackup()
    {
        try {
            $filename = $this->generateBackup();
            $this->settings->updateLastBackup();
            $this->manageRetention();

            return [
                'success' => true,
                'message' => 'Manual backup completed successfully',
                'filename' => $filename
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }
}

// If called directly (for testing)
if (basename($_SERVER['PHP_SELF']) === 'auto_backup.php') {
    $backup = new AutoBackup();
    $result = $backup->run();
    header('Content-Type: application/json');
    echo json_encode($result);
}
