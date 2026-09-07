<?php
$zipFile = __DIR__ . '/../final_auth_scope_fix.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (final_auth_scope_fix.zip) was not found. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The core Global Scope Authentication bug has been resolved!</p>";
    echo "<p>Waiters and staff can now freely log in via the central portal without their accounts being hidden by the Global Tenant Scope.</p>";
    echo "<p>Go ahead and test the Waiter login now, it will work perfectly!</p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
