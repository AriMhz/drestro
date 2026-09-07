<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Signature('app:prepare-distribution')]
#[Description('Wipes all personal data (restaurant name, logo, license, orders, menus, inventory) to prepare a clean copy for client distribution.')]
class PrepareDistribution extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('⚠️  WARNING: This will DELETE all your restaurant data, license, menus, orders, inventory, and uploaded images. This is meant for creating a CLEAN copy to send to a client. Are you sure?')) {
            $this->info('Cancelled.');
            return;
        }

        $this->info('');
        $this->info('🧹 Preparing clean distribution copy...');

        // 1. Wipe all order/transaction data
        $this->info('[1/6] Clearing all orders & invoices...');
        DB::table('order_items')->delete();
        DB::table('invoices')->delete();
        DB::table('orders')->delete();

        // 2. Wipe all menu data
        $this->info('[2/6] Clearing all menu items & categories...');
        DB::table('variations')->delete();
        DB::table('menu_items')->delete();
        DB::table('menu_categories')->delete();

        // 3. Wipe inventory
        $this->info('[3/6] Clearing inventory...');
        DB::table('inventory_logs')->delete();
        DB::table('inventory_items')->delete();

        // 4. Wipe tables
        $this->info('[4/6] Clearing tables...');
        DB::table('tables')->delete();

        // 5. Reset restaurant settings (name, logo, license, etc.)
        $this->info('[5/6] Resetting restaurant settings & license...');
        $restaurant = DB::table('restaurants')->first();
        if ($restaurant) {
            DB::table('restaurants')->update([
                'name' => 'My Restaurant',
                'address' => null,
                'phone' => null,
                'email' => null,
                'logo' => null,
                'license_key' => null,
                'machine_id' => null,
            ]);
        }

        // 6. Clean uploaded files (logos, menu images)
        $this->info('[6/6] Cleaning uploaded files...');
        $storagePath = storage_path('app/public');
        if (File::isDirectory($storagePath)) {
            // Delete all files inside storage/app/public but keep the directory
            foreach (File::allFiles($storagePath) as $file) {
                File::delete($file->getPathname());
            }
            // Delete subdirectories
            foreach (File::directories($storagePath) as $dir) {
                File::deleteDirectory($dir);
            }
        }

        // Remove any machine_id fallback file
        $machineIdFile = storage_path('app/machine_id');
        if (File::exists($machineIdFile)) {
            File::delete($machineIdFile);
        }

        // Reset SQLite auto-increment sequences
        $tables = ['order_items', 'invoices', 'orders', 'menu_items', 'menu_categories', 'variations', 'tables', 'inventory_items', 'inventory_logs'];
        foreach ($tables as $table) {
            DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}'");
        }

        $this->info('');
        $this->info('✅ Distribution copy is ready!');
        $this->info('');
        $this->info('📦 To distribute to a client:');
        $this->info('   1. Delete these folders: node_modules, tests, .git');
        $this->info('   2. Delete: Drestro_Keygen folder, keys.json');
        $this->info('   3. Zip the folder and send to the client');
        $this->info('   4. Client runs Install.cmd → Open Firewall.cmd → Run Server.cmd');
        $this->info('');
    }
}
