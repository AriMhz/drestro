<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Clearing Caches Properly</h1>";

try {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo "<pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
    echo "<h2 style='color:green;'>✅ Caches cleared via Artisan successfully!</h2>";
} catch (\Exception $e) {
    echo "<h2 style='color:red;'>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
