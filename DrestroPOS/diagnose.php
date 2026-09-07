<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

echo "<h1>Diagnostic Report</h1>";

$dir = '../storage/framework/views';
if (!is_dir($dir)) {
    echo "Directory storage/framework/views does NOT exist. Attempting to create...<br>";
    if (@mkdir($dir, 0755, true)) {
        echo "Created successfully.<br>";
    } else {
        $error = error_get_last();
        echo "Failed to create directory: " . print_r($error, true) . "<br>";
    }
}

echo "Directory exists: " . (is_dir($dir) ? 'Yes' : 'No') . "<br>";
echo "Directory writable: " . (is_writable($dir) ? 'Yes' : 'No') . "<br>";

$testFile = $dir . '/test_write.php';
$bytes = @file_put_contents($testFile, '<?php echo "test";');
echo "Bytes written: " . var_export($bytes, true) . "<br>";
if ($bytes === false) {
    $error = error_get_last();
    echo "Error writing: " . print_r($error, true) . "<br>";
} else {
    @unlink($testFile);
}

$freeSpace = @disk_free_space($dir);
echo "Disk free space: " . ($freeSpace !== false ? round($freeSpace / 1024 / 1024, 2) . " MB" : "Unknown") . "<br>";

// Fix permissions just in case
@chmod('../storage', 0775);
@chmod('../storage/framework', 0775);
@chmod('../storage/framework/views', 0775);

echo "<h2>Please copy all the text above and send it to me!</h2>";
?>
