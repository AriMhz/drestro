<?php
// This goes in public/deploy_portal_fixes.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>DrestroPOS Fix Deployment</h1>";

// Zip is located in the parent directory (root of DrestroPOS) since you scp'd it there
$zipFile = realpath(__DIR__ . '/../all_portal_fixes_final.zip');

// We want to extract to the root of DrestroPOS (one level up from public/)
$extractTo = realpath(__DIR__ . '/../');

if (!$zipFile || !file_exists($zipFile)) {
    die("<p style='color:red;'>Error: Could not find <b>all_portal_fixes_final.zip</b> in " . $extractTo . ". Make sure you uploaded it!</p>");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    // Extract everything
    $zip->extractTo($extractTo);
    $zip->close();
    echo "<h2 style='color:green;'>✅ Successfully extracted all fixes to DrestroPOS!</h2>";
    echo "<p>Files updated:</p><ul>";
    echo "<li>resources/views/livewire/admin/table-manager.blade.php</li>";
    echo "<li>resources/views/livewire/admin/room-manager.blade.php</li>";
    echo "<li>app/Livewire/Admin/RoomManager.php</li>";
    echo "<li>resources/views/livewire/staff/room-service.blade.php</li>";
    echo "<li>resources/views/livewire/staff/cashier-panel.blade.php</li>";
    echo "<li>routes/web.php</li>";
    echo "<li>resources/views/livewire/staff/waiter-dashboard.blade.php</li>";
    echo "<li>app/Livewire/Staff/WaiterDashboard.php</li>";
    echo "</ul>";
    
    // REALLY Clear caches (including Laravel 11 routes-v7.php)
    $cacheDir = __DIR__ . '/../bootstrap/cache/';
    $files = glob($cacheDir . '*.php');
    foreach($files as $file) {
        if(is_file($file)) {
            unlink($file);
            echo "<p>Deleted cache file: " . basename($file) . "</p>";
        }
    }
    echo "<p style='color:green;'><b>All Laravel caches completely cleared!</b></p>";
    
    echo "<h3>You can now delete this script and the zip file!</h3>";
} else {
    echo "<h2 style='color:red;'>❌ Failed to open zip file. It might be corrupted.</h2>";
}
?>
