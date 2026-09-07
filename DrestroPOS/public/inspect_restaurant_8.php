<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h1>All Restaurants in POS Database:</h1>";
$restaurants = \App\Models\Restaurant::all();
foreach ($restaurants as $r) {
    echo "ID: " . htmlspecialchars($r->id) . " | Name: " . htmlspecialchars($r->name) . " | License: " . htmlspecialchars($r->license_key) . "<br>";
}
