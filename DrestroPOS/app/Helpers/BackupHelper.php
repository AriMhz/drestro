<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class BackupHelper
{
    /**
     * Start the USB backup in a background OS process to avoid blocking HTTP requests.
     */
    public static function backupToUsbAsync()
    {
        try {
            $phpBinary = file_exists(base_path('php/php.exe')) ? base_path('php/php.exe') : 'php';
            $artisanPath = base_path('artisan');
            
            if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                // Windows background execution (start /B runs it in the background)
                $cmd = 'start /B "" "' . $phpBinary . '" "' . $artisanPath . '" app:backup-usb';
                pclose(popen($cmd, 'r'));
            } else {
                // Unix background execution
                $cmd = '"' . $phpBinary . '" "' . $artisanPath . '" app:backup-usb > /dev/null 2>&1 &';
                exec($cmd);
            }
            Log::info('Triggered background USB auto-backup.');
        } catch (\Exception $e) {
            Log::error('Failed to trigger background USB auto-backup: ' . $e->getMessage());
        }
    }

    /**
     * Scan connected drive letters (D: to Z:) and backup the database to active USB drives.
     * 
     * @return array List of drive letters where backup succeeded.
     */
    public static function backupToUsb()
    {
        $backedUpDrives = [];
        $databasePath = database_path('database.sqlite');

        if (!file_exists($databasePath)) {
            Log::warning('Backup failed: Database file not found at ' . $databasePath);
            return [];
        }

        // Loop through Windows drive letters D: to Z:
        for ($letter = 'D'; $letter <= 'Z'; $letter++) {
            $drive = $letter . ':';
            
            // Check if drive is accessible and writable
            if (is_dir($drive . '\\')) {
                $testFile = $drive . '\\drestro_write_test.tmp';
                
                // Try writing a small temp file to verify it's writable and is a removable/local drive
                if (@file_put_contents($testFile, 'test') !== false) {
                    @unlink($testFile);
                    
                    try {
                        $backupDir = $drive . '\\DrestroPOS_Backups';
                        if (!is_dir($backupDir)) {
                            mkdir($backupDir, 0777, true);
                        }

                        $timestamp = date('Y-m-d_His');
                        $backupFileName = "database_backup_{$timestamp}.sqlite";
                        $backupPath = $backupDir . '\\' . $backupFileName;

                        if (copy($databasePath, $backupPath)) {
                            $backedUpDrives[] = $drive;
                            Log::info("Auto-backup succeeded to USB drive: {$backupPath}");
                            
                            // Prune old backups on this USB drive to prevent filling it up
                            self::pruneOldBackups($backupDir);
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed backing up to drive {$drive}: " . $e->getMessage());
                    }
                }
            }
        }

        return $backedUpDrives;
    }

    /**
     * Keep only the latest 30 backups on the USB drive to prevent storage exhaustion.
     * 
     * @param string $backupDir Path to the backups folder.
     */
    private static function pruneOldBackups($backupDir)
    {
        try {
            $files = glob($backupDir . '\\database_backup_*.sqlite');
            if (count($files) > 30) {
                // Sort by last modified time (oldest first)
                usort($files, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });

                // Delete oldest files until we are down to 30
                $filesToDeleteCount = count($files) - 30;
                for ($i = 0; $i < $filesToDeleteCount; $i++) {
                    if (file_exists($files[$i])) {
                        @unlink($files[$i]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to prune old backups in {$backupDir}: " . $e->getMessage());
        }
    }
}
