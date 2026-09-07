<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MenuItem;

$items = MenuItem::with('category')->get();
echo "Items and their Departments:\n";
foreach ($items as $item) {
    echo "- {$item->name} (Category: {$item->category->name}): {$item->category->department}\n";
}
