<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderItem;
use App\Models\Order;

$totalItems = OrderItem::count();
echo "Total Items: $totalItems\n";

$items = OrderItem::with('order')->get();
foreach ($items as $i) {
    echo "ID: {$i->id}, Order: {$i->order_id}, Name: " . ($i->menuItem->name ?? 'N/A') . ", Printed: {$i->is_printed}\n";
}
