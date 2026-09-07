<?php
$zipFile = __DIR__ . '/../final_routing_fix.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (final_routing_fix.zip) was not found. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The POS root routing has been fixed!</p>";
    echo "<ul>";
    echo "<li>Unauthenticated users visiting <b>portal.drestro.com</b> will now properly see the POS Login form instead of being kicked to the SaaS site.</li>";
    echo "<li>Waiters can now freely log in via the POS Login form without any hidden redirects overriding them.</li>";
    echo "</ul>";
    echo "<p>You can now go back and test the Waiter login!</p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
