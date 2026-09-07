<?php
$zipFile = __DIR__ . '/../final_login_fix.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (final_login_fix.zip) was not found. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The Final Login Patch was successfully deployed!</p>";
    echo "<p>Next, click the button below to fix your staff accounts so they properly connect to your restaurant.</p>";
    echo "<p><a href='/fix_staff.php' style='display:inline-block;background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Fix Staff Connections</a></p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
