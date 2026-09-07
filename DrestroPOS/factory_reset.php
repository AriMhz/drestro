<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Restaurant;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Starting TOTAL FACTORY RESET...\n";

try {
    DB::beginTransaction();
    DB::statement('PRAGMA foreign_keys = OFF');

    $tables = [
        'order_items', 'orders', 'invoices', 'payments', 
        'inventory_transactions', 'inventory_items', 
        'menu_items', 'menu_categories', 'tables', 
        'notifications', 'users', 'restaurants', 'failed_jobs',
        'sessions', 'cache'
    ];

    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            DB::table($table)->delete();
            DB::statement("DELETE FROM sqlite_sequence WHERE name = '$table'");
            echo "✓ Wiped $table\n";
        }
    }

    // 1. Create Default Administrative Users
    User::create([
        'name' => 'Admin',
        'email' => 'admin@admin.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);
    User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@admin.com',
        'password' => Hash::make('superadmin123'),
        'role' => 'super_admin',
    ]);
    echo "✓ Created Default Admin (admin@admin.com / admin123)\n";
    echo "✓ Created Default Super Admin (superadmin@admin.com / superadmin123)\n";

    // 2. Create Empty Restaurant
    Restaurant::create([
        'name' => 'Drestro POS',
        'email' => 'admin@admin.com',
        'license_key' => null,
        'license_data' => null
    ]);
    echo "✓ Created Fresh Restaurant Config\n";

    // 3. Clear Storage Images
    $folders = ['categories', 'menu_items', 'restaurants'];
    foreach ($folders as $folder) {
        $path = storage_path("app/public/$folder");
        if (file_exists($path)) {
            $files = glob($path . '/*'); 
            foreach($files as $file){
                if(is_file($file)) unlink($file);
            }
        }
    }
    echo "✓ Cleared Storage Images\n";

    DB::statement('PRAGMA foreign_keys = ON');
    DB::commit();

    echo "\n✨ FACTORY RESET SUCCESSFUL! ✨\n";
    echo "The software is now like a brand-new installation.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
