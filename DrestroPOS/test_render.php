<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::withoutGlobalScopes()->first();
if (!$user) {
    echo "No user found!";
    exit;
}
Auth::login($user);
$restaurant = App\Models\Restaurant::first();
if ($restaurant) {
    app()->instance('restaurant', $restaurant);
    session(['tenant_slug' => $restaurant->slug]);
}

try {
    $html = Livewire\Livewire::mount('staff.waiter-dashboard');
    echo "SUCCESS";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
