<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::withoutGlobalScopes()->where('email', 'arimhz2004@gmail.com')->first();
if ($user) {
    echo "<h1>User Details</h1>";
    echo "<pre>";
    print_r($user->toArray());
    echo "</pre>";
    
    echo "<h2>Restaurant Details</h2>";
    if ($user->restaurant_id) {
        $rest = \App\Models\Restaurant::find($user->restaurant_id);
        if ($rest) {
            echo "<pre>";
            print_r($rest->toArray());
            echo "</pre>";
        } else {
            echo "Restaurant ID {$user->restaurant_id} NOT found in restaurants table!";
        }
    } else {
        echo "User has NO restaurant_id!";
    }
} else {
    echo "User NOT found.";
}
