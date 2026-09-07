<?php
// PHP deploy script to unzip drestropos_updates.zip and clear cache
$zipFile = __DIR__ . '/../drestropos_updates.zip';
$extractTo = __DIR__ . '/../';

if (!file_exists($zipFile)) {
    die("Error: ZIP file not found at " . realpath($zipFile));
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo "<h1>ZIP Extracted Successfully!</h1>";
} else {
    die("Error: Failed to open ZIP archive.");
}

// Clear Laravel Cache
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>OPCache reset successfully.</p>";
}

\Illuminate\Support\Facades\Artisan::call('optimize:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');

echo "<p>All Laravel caches cleared successfully.</p>";
unlink(__FILE__); // Self destruct for security
