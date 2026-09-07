<?php
$zipFile = __DIR__ . '/../staff_password_fix.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: The zip file (staff_password_fix.zip) was not found. Please upload it first.");
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<h2>✅ Success!</h2>";
    echo "<p>The staff password fix was successfully deployed!</p>";
    echo "<p>Please edit your staff member's password to fix their login.</p>";
    echo "<p><a href='/admin/staff'>Go back to Staff Manager</a></p>";
} else {
    echo "<h2>❌ Failed!</h2>";
    echo "<p>Could not open the zip file.</p>";
}
?>
