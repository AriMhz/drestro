<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$zipFile = __DIR__ . '/../final_ui_tweaks.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (final_ui_tweaks.zip) was not found. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    // Clear Laravel views cache to ensure the blade changes take effect
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The Waiter UI fixes have been applied!</p>";
    echo "<ul>";
    echo "<li>The 'RENEW SOON' banner is now completely hidden from regular staff members (Waiters, Cashiers, Kitchen, etc).</li>";
    echo "<li>The 4-digit PIN is now <b>optional</b> when staff accept their invitation.</li>";
    echo "</ul>";
    echo "<p>You can now go back to your dashboard!</p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
