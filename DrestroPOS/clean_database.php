<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Starting Database Cleanup for Client Delivery...\n";

try {
    DB::beginTransaction();

    // Disable foreign key checks for clean deletion
    DB::statement('PRAGMA foreign_keys = OFF');

    // Tables to wipe completely (transactional data)
    $tablesToWipe = [
        'order_items',
        'orders',
        'invoices',
        'payments',
        'inventory_transactions',
        'notifications',
        'failed_jobs',
    ];

    foreach ($tablesToWipe as $table) {
        if (Schema::hasTable($table)) {
            DB::table($table)->delete();
            // Reset auto-increment counters in SQLite
            DB::statement("DELETE FROM sqlite_sequence WHERE name = '$table'");
            echo "✓ Cleared $table\n";
        }
    }

    // Optional: Reset table status to 'available' and clear guest names
    if (Schema::hasTable('tables')) {
        DB::table('tables')->update([
            'status' => 'available',
            'room_status' => 'available',
            'guest_name' => null,
            'guest_phone' => null,
            'check_in_at' => null,
            'current_order_id' => null
        ]);
        echo "✓ Reset all Tables & Rooms to 'Available'\n";
    }

    DB::statement('PRAGMA foreign_keys = ON');
    DB::commit();

    echo "\n✨ DATABASE IS NOW CLEAN AND READY FOR DELIVERY! ✨\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
