<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::withoutGlobalScopes()->where('email', 'arimhz2004@gmail.com')->first();
if ($user) {
    echo "User found.\n";
    echo "Restaurant ID: " . ($user->restaurant_id ?? 'NULL') . "\n";
    if ($user->restaurant_id) {
        $rest = \App\Models\Restaurant::find($user->restaurant_id);
        echo "Restaurant Slug: " . ($rest->slug ?? 'NULL') . "\n";
    }
} else {
    echo "User NOT found.\n";
}
