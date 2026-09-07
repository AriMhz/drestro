<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Adjusting user roles...\n";

// Update admin@admin.com to standard 'admin' role
$admin = User::where('email', 'admin@admin.com')->first();
if ($admin) {
    $admin->role = 'admin';
    $admin->save();
    echo "✓ Updated admin@admin.com role to 'admin'.\n";
} else {
    echo "✗ admin@admin.com user not found.\n";
}

// Create superadmin@admin.com as 'super_admin'
$superAdmin = User::where('email', 'superadmin@admin.com')->first();
if (!$superAdmin) {
    User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@admin.com',
        'password' => Hash::make('superadmin123'),
        'role' => 'super_admin',
        'email_verified_at' => now(),
    ]);
    echo "✓ Created new super_admin account: superadmin@admin.com (password: superadmin123).\n";
} else {
    $superAdmin->role = 'super_admin';
    $superAdmin->save();
    echo "✓ Verified superadmin@admin.com has role 'super_admin'.\n";
}

echo "Database role update complete!\n";
