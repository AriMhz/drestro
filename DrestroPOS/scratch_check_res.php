<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$r = App\Models\Restaurant::first();
echo "Restaurant Name: " . ($r->name ?? 'NULL') . "\n";
