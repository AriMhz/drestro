<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderItem;
echo "Unprinted items: " . OrderItem::where('is_printed', 0)->count() . "\n";
echo "Printed items: " . OrderItem::where('is_printed', 1)->count() . "\n";
