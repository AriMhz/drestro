<?php
$zipFile = __DIR__ . '/../auth_tenant_fix.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (auth_tenant_fix.zip) was not found. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The login routing fix was successfully deployed!</p>";
    echo "<p>Next, click the button below to forcefully reset the password to <strong>password123</strong> to test the login.</p>";
    echo "<p><a href='/force_reset.php' style='display:inline-block;background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Force Reset Staff Password</a></p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
