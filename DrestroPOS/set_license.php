<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$restaurant = \App\Models\Restaurant::first();
if ($restaurant) {
    $restaurant->license_key = 'DR-648F-0693-B406';
    $restaurant->save();
    echo "Successfully set license key to DR-648F-0693-B406!\n";
} else {
    echo "No restaurant found in local DB!\n";
}
