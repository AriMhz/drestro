<?php
$zipFile = __DIR__ . '/../tenant_routing_fix.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (tenant_routing_fix.zip) was not found. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The Tenant routing fix was successfully deployed!</p>";
    echo "<p>Your staff members will no longer be randomly kicked back to drestro.com after logging in!</p>";
    echo "<p><a href='/login' style='display:inline-block;background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Go back to Login</a></p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
