<?php

$basePath = realpath(__DIR__ . '/..');
$storageLink = __DIR__ . '/storage';

echo "<h1>Drestro POS - Storage Link Fixer</h1>";

// 1. Delete existing broken symlink or folder
if (file_exists($storageLink) || is_link($storageLink)) {
    if (is_link($storageLink)) {
        if (unlink($storageLink)) {
            echo "<p>✅ Removed existing broken/outdated symlink.</p>";
        } else {
            echo "<p>❌ Failed to remove existing symlink. Please check permissions.</p>";
        }
    } else if (is_dir($storageLink)) {
        echo "<p>⚠️ Detected a physical directory at <code>public/storage</code> instead of a symlink.</p>";
        $newName = __DIR__ . '/storage_old_' . time();
        if (rename($storageLink, $newName)) {
            echo "<p>✅ Renamed existing storage folder to <strong>" . basename($newName) . "</strong> as backup.</p>";
        } else {
            echo "<p>❌ Failed to rename the existing storage folder.</p>";
        }
    } else {
        if (unlink($storageLink)) {
            echo "<p>✅ Removed existing file at public/storage.</p>";
        }
    }
}

// 2. Ensure the target directory exists
$target = $basePath . '/storage/app/public';
if (!file_exists($target)) {
    mkdir($target, 0755, true);
    echo "<p>✅ Created missing target directory: <code>$target</code></p>";
}

// 3. Re-create the symlink
if (symlink($target, $storageLink)) {
    echo "<p>✅ <strong>Successfully created storage symlink!</strong></p>";
    echo "<p>Target: <code>$target</code></p>";
    echo "<p>Link: <code>$storageLink</code></p>";
} else {
    echo "<p>⚠️ Native symlink() failed. Attempting shell command fallback...</p>";
    $output = shell_exec("ln -s " . escapeshellarg($target) . " " . escapeshellarg($storageLink));
    if (is_link($storageLink)) {
        echo "<p>✅ <strong>Successfully created storage symlink via shell command!</strong></p>";
    } else {
        echo "<p>❌ Shell command failed. Output: <pre>$output</pre></p>";
    }
}

// 4. Clear compiled views to refresh image paths
$viewsPath = $basePath . '/storage/framework/views';
$count = 0;
if (is_dir($viewsPath)) {
    $files = glob($viewsPath . '/*.php');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) { @unlink($file); $count++; }
        }
    }
}
echo "<p>✅ Cleared $count view cache files.</p>";

echo "<h2>Done! Please refresh your website now.</h2>";
?>
