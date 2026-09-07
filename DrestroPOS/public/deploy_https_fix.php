<?php
$zipFile = __DIR__ . '/../final_https_fix.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (final_https_fix.zip) was not found. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The core session security patch was successfully applied!</p>";
    echo "<p>Next, click the button below to completely wipe the stuck/duplicate staff account so you can recreate it properly in your dashboard.</p>";
    echo "<p><a href='/wipe_stuck_user.php' style='display:inline-block;background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Wipe Stuck Account</a></p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
