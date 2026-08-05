# Database Backup Module

A robust, automated database backup system designed for PHP/MySQL applications. This module allows administrators to manage manual backups, view an archive of past snapshots, and configure a scheduled automated backup system that runs in the background.

## 📂 File Structure

| File | Description |
| :--- | :--- |
| `index.php` | The main management dashboard. Handles UI, settings updates, and the backup archive list. |
| `auto_backup.php` | The core engine. Contains the logic for generating SQL dumps, saving files, and managing retention. |
| `backup_settings.php` | A helper class for reading and writing configuration to `backup_settings.json`. |
| `backup_settings.json` | Stores the current configuration (enabled status, schedule, retention, and last backup time). |
| `download_backup.php` | Secure handler for downloading backup files from the protected folder with authentication checks. |
| `trigger_backup.php` | A silent endpoint designed to be called via AJAX to trigger the automated backup logic. |
| `backup_db.php` | A legacy script for immediate SQL generation and browser streaming. |
| `stress_test.php` | A performance benchmarking tool to measure execution time and memory usage during a backup. |
| `/backups/` | The protected directory where SQL snapshot files are stored. |

---

## 🛠 Features

### 1. Automated Backups
The system checks the schedule on every page load (integrated via `script.php`). If the current time matches or exceeds the scheduled time and no backup has run yet today, it silently generates a snapshot.
- **Configurable Schedule**: Set a specific day of the week and a target time (e.g., Tuesday at 07:07 AM).
- **Retention Management**: Automatically deletes older backups based on your configuration (e.g., keep only the last 8 backups).
- **Background Execution**: AJAX-based triggering ensures the user experience is not delayed by the backup process.

### 2. Manual Control
- **Instant Snapshot**: Generate and download a full SQL dump immediately with one click.
- **Settings Dashboard**: Enable/Disable the automation and adjust the retention limit on the fly.

### 3. Management Dashboard
- **Backup Archive**: A DataTables-powered list of all available backups with searchable filenames and dates.
- **Secure Downloads**: Files are kept in a protected directory and can only be accessed by authenticated administrators.

---

## 🚀 Replication Guide

To replicate this module in a new system:

### Phase 1: Core Files
1. Copy the entire `backup/` directory to your new project.
2. Ensure you have a `config/database.php` class that provides a `getConnection()` method returning a `PDO` instance.
3. Ensure your `config/session.php` or main entry point sets `$_SESSION['role'] === 'Admin'` for access control.

### Phase 2: Configuration
1. Give the web server full **write permissions** to `backup/backups/` and `backup/backup_settings.json`.
   ```bash
   chmod -R 777 backup/backups/
   chmod 777 backup/backup_settings.json
   ```
2. Update the `DB_NAME` and other credentials in `auto_backup.php` or ensure they are available via environment variables.

### Phase 3: Global Trigger
To ensure automated backups run without a cron job, add the following trigger to your main footer or global JavaScript file (`script.php`):

```javascript
(function() {
    if (typeof window.backupTriggered === 'undefined') {
        window.backupTriggered = true;
        // Adjust the path to where trigger_backup.php is located
        fetch('backup/trigger_backup.php')
            .then(response => response.json())
            .then(data => {
                if (data.ran && data.success) console.log('Auto-Backup Success:', data.filename);
            })
            .catch(err => console.debug('Backup trigger skipped'));
    }
})();
```

### Phase 4: Dependencies
The management UI (`index.php`) depends on:
- **Bootstrap 5** & **Bootstrap Icons**
- **DataTables** (for the archive search)
- **AdminLTE 4** (optional, recommended for the theme)

---

## 🔒 Security Notes
- The `/backups/` directory contains an `.htaccess` file with `Deny from all` to prevent direct browser access to your database dumps.
- All entry points (`index.php`, `download_backup.php`, etc.) include mandatory session checks to ensure only authorized Administrators can access data.
- The `auto_backup.php` engine uses `addslashes` and prepared structure queries to ensure broad compatibility and safety.
