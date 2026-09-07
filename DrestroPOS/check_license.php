<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$restaurant = \App\Models\Restaurant::first();
if ($restaurant) {
    echo "License Key in DB: " . ($restaurant->license_key ?? 'NULL') . "\n";
} else {
    echo "No restaurant found in local DB!\n";
}
