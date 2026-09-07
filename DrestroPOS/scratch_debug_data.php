<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\OrderItem;

echo "Orders: " . Order::count() . "\n";
echo "Order Items: " . OrderItem::count() . "\n";
foreach (OrderItem::all() as $item) {
    echo "Item ID: {$item->id}, Order ID: {$item->order_id}, Printed: {$item->is_printed}\n";
}
