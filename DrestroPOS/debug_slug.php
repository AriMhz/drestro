<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = '84b32dec-a0ff-4ef9-be2f-8559f4f3cabc';
echo "Looking for slug: $slug\n";

$r = \App\Models\Restaurant::where('slug', $slug)->first();
echo "Found: " . ($r ? "id={$r->id} slug={$r->slug}" : "NULL") . "\n";

echo "\nAll restaurants:\n";
foreach(\App\Models\Restaurant::all() as $r) {
    echo "  id={$r->id} slug='{$r->slug}'\n";
}

// Check if 'slug' column exists
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('restaurants');
echo "\nColumns: " . implode(', ', $columns) . "\n";
