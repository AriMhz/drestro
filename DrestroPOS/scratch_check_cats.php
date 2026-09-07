<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$categories = \App\Models\MenuCategory::all();
foreach($categories as $cat) {
    echo "Category: {$cat->name} | Department: {$cat->department}\n";
}
