<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrderItem;

$affected = OrderItem::where('is_printed', false)->update(['is_printed' => true]);
echo "Marked $affected old items as printed.\n";
