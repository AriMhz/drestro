<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MenuCategory;

$affected = MenuCategory::where('name', 'LIKE', '%drink%')
    ->orWhere('name', 'LIKE', '%beverage%')
    ->orWhere('name', 'LIKE', '%bar%')
    ->orWhere('name', 'LIKE', '%juice%')
    ->update(['department' => 'bar']);

echo "Updated $affected categories to 'bar' department.\n";
