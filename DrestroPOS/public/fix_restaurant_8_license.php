<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$restaurant = \App\Models\Restaurant::find(8);
if ($restaurant) {
    $restaurant->update(['license_key' => 'DR-7B33-9316-458E']);
    echo "<h1>Successfully updated Restaurant ID 8 license key to 'DR-7B33-9316-458E'!</h1>";
} else {
    echo "<h1>Restaurant ID 8 not found!</h1>";
}
