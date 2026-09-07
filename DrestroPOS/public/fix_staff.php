<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find the super admin's restaurant
$admin = \App\Models\User::withoutGlobalScopes()->where('role', 'super_admin')->first();
if (!$admin || !$admin->restaurant_id) {
    die("No super admin or restaurant found!");
}

$restaurantId = $admin->restaurant_id;

// Fix all users that have a null restaurant_id
$users = \App\Models\User::withoutGlobalScopes()->whereNull('restaurant_id')->get();
$count = 0;
foreach ($users as $u) {
    $u->restaurant_id = $restaurantId;
    $u->save();
    $count++;
}

echo "<h2>✅ Success!</h2>";
echo "<p>Fixed $count staff members that were missing their restaurant connection!</p>";
echo "<p>Please try logging in again!</p>";
echo "<p><a href='/login' style='display:inline-block;background:#10b981;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Go back to Login</a></p>";
?>
