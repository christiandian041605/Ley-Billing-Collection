<?php
/**
 * BackupSettings Class
 * Handles reading and writing backup configuration from/to JSON file
 */
date_default_timezone_set('Asia/Manila');
class BackupSettings
{
    private $settingsFile;
    private $settings;

    public function __construct($settingsFile = null)
    {
        $this->settingsFile = $settingsFile ?: __DIR__ . '/backup_settings.json';
        $this->loadSettings();
    }

    /**
     * Load settings from JSON file
     */
    private function loadSettings()
    {
        if (file_exists($this->settingsFile)) {
            $json = file_get_contents($this->settingsFile);
            $this->settings = json_decode($json, true);
        } else {
            // Default settings
            $this->settings = [
                'enabled' => false,
                'schedule' => [
                    'day' => 1, // Monday
                    'time' => '02:00'
                ],
                'retention' => 8,
                'last_backup' => null
            ];
            $this->saveSettings();
        }
    }

    /**
     * Save settings to JSON file
     */
    private function saveSettings()
    {
        $json = json_encode($this->settings, JSON_PRETTY_PRINT);
        file_put_contents($this->settingsFile, $json);
    }

    /**
     * Get all settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Check if automated backups are enabled
     */
    public function isEnabled()
    {
        return $this->settings['enabled'] ?? false;
    }

    /**
     * Set enabled status
     */
    public function setEnabled($enabled)
    {
        $this->settings['enabled'] = (bool) $enabled;
        $this->saveSettings();
    }

    /**
     * Get schedule settings
     */
    public function getSchedule()
    {
        return $this->settings['schedule'] ?? ['day' => 1, 'time' => '02:00'];
    }

    /**
     * Set schedule
     */
    public function setSchedule($day, $time)
    {
        $this->settings['schedule'] = [
            'day' => (int) $day,
            'time' => $time
        ];
        $this->saveSettings();
    }

    /**
     * Get retention count
     */
    public function getRetention()
    {
        return $this->settings['retention'] ?? 8;
    }

    /**
     * Set retention count
     */
    public function setRetention($count)
    {
        $this->settings['retention'] = (int) $count;
        $this->saveSettings();
    }

    /**
     * Get last backup timestamp
     */
    public function getLastBackup()
    {
        return $this->settings['last_backup'];
    }

    /**
     * Update last backup timestamp
     */
    public function updateLastBackup($timestamp = null)
    {
        $this->settings['last_backup'] = $timestamp ?: date('Y-m-d H:i:s');
        $this->saveSettings();
    }

    /**
     * Check if backup should run based on schedule
     */
    public function shouldRunBackup()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $schedule = $this->getSchedule();
        $currentDay = (int) date('w'); // 0 (Sunday) to 6 (Saturday)
        $currentTime = date('H:i');
        $lastBackup = $this->getLastBackup();

        // Check if today is the scheduled day
        if ($currentDay !== $schedule['day']) {
            return false;
        }

        // Check if current time has passed the scheduled time
        if ($currentTime < $schedule['time']) {
            return false;
        }

        // Check if backup already ran today AT OR AFTER the scheduled time
        if ($lastBackup) {
            $lastBackupTime = strtotime($lastBackup);
            $scheduledTimeToday = strtotime(date('Y-m-d') . ' ' . $schedule['time']);

            // If the last backup happened today and it was after the scheduled time, don't run again
            if (date('Y-m-d', $lastBackupTime) === date('Y-m-d') && $lastBackupTime >= $scheduledTimeToday) {
                return false;
            }
        }

        return true;
    }
}
