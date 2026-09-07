<?php
$zipFile = __DIR__ . '/../sync_license_ui_fix.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (sync_license_ui_fix.zip) was not found at $zipFile. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    // Clear Laravel View Cache
    $cacheDir = __DIR__ . '/../storage/framework/views/';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The sync license UI fix was successfully deployed and the view cache was cleared!</p>";
    echo "<p>You can now delete this file (deploy_sync_ui.php) and the zip file.</p>";
    echo "<p><a href='/admin/license'>Go back to License Manager</a></p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
