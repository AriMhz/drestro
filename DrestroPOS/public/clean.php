<?php

// This script clears the Laravel cache manually from the filesystem
$basePath = __DIR__ . '/..';

echo "<h1>DrestroPOS System Refresh</h1>";

// 1. Clear View Cache
$viewCache = $basePath . '/storage/framework/views';
$files = glob($viewCache . '/*.php');
$viewCount = 0;
if ($files) {
    foreach($files as $file) {
        if(@unlink($file)) $viewCount++;
    }
}
echo "<p>✅ Cleared $viewCount view files.</p>";

// 2. Clear Route Cache
$routeCache = $basePath . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCache)) {
    @unlink($routeCache);
    echo "<p>✅ Cleared Route cache.</p>";
}

// 3. Clear Config Cache
$configCache = $basePath . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    @unlink($configCache);
    echo "<p>✅ Cleared Config cache.</p>";
}

echo "<h2>Done! Please <a href='/staff/take-order'>click here to return to Take Order</a></h2>";
