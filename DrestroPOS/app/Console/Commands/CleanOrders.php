<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:clean-orders')]
#[Description('Clears all transactional order data to prepare for production, keeping menus and settings intact.')]
class CleanOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('order_items')->delete();
        DB::table('invoices')->delete();
        DB::table('orders')->delete();
        
        // Reset auto-increments for SQLite
        DB::statement("DELETE FROM sqlite_sequence WHERE name='order_items'");
        DB::statement("DELETE FROM sqlite_sequence WHERE name='invoices'");
        DB::statement("DELETE FROM sqlite_sequence WHERE name='orders'");

        $this->info('Orders, Order Items, and Invoices have been completely cleared.');
    }
}
