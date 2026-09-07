<?php
// deploy_fixes.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>DrestroPOS Fix Deployment</h1>";

// This script is in public/ (e.g. portal.drestro.com/)
// The zip file should be in the same folder.
$zipFile = __DIR__ . '/all_portal_fixes_final.zip';

// We want to extract to the root of DrestroPOS (one level up from public/)
$extractTo = realpath(__DIR__ . '/../');

if (!file_exists($zipFile)) {
    die("<p style='color:red;'>Error: Could not find <b>all_portal_fixes_final.zip</b> in " . __DIR__ . "</p>");
}

if (!is_writable($extractTo)) {
    echo "<p style='color:orange;'>Warning: Destination directory might not be fully writable. Attempting extraction anyway...</p>";
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
    
    // Clear caches just to be safe
    @unlink(__DIR__ . '/../bootstrap/cache/routes.php');
    @unlink(__DIR__ . '/../bootstrap/cache/services.php');
    @unlink(__DIR__ . '/../bootstrap/cache/packages.php');
    echo "<p>Caches cleared successfully.</p>";
    
    echo "<h3>You can now delete this script and the zip file!</h3>";
} else {
    echo "<h2 style='color:red;'>❌ Failed to open zip file. It might be corrupted.</h2>";
}
?>
