<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>DrestroPOS Security Fix Deployment</h1>";

$zipFile = realpath(__DIR__ . '/../license_enforcement_fix.zip');
$extractTo = realpath(__DIR__ . '/../');

if (!$zipFile || !file_exists($zipFile)) {
    die("<p style='color:red;'>Error: Could not find <b>license_enforcement_fix.zip</b> in " . $extractTo . ". Make sure you uploaded it!</p>");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo "<h2 style='color:green;'>✅ Successfully deployed License Enforcement security patch!</h2>";
    echo "<p>Files updated:</p><ul>";
    echo "<li>app/Http/Middleware/CheckLicense.php</li>";
    echo "</ul>";
    
    $cacheDir = __DIR__ . '/../bootstrap/cache/';
    $files = glob($cacheDir . '*.php');
    foreach($files as $file) {
        if(is_file($file)) {
            unlink($file);
        }
    }
    echo "<p style='color:green;'><b>All Laravel caches completely cleared!</b></p>";
    
    echo "<h3>You can now delete this script and the zip file!</h3>";
} else {
    echo "<h2 style='color:red;'>❌ Failed to open zip file. It might be corrupted.</h2>";
}
?>
