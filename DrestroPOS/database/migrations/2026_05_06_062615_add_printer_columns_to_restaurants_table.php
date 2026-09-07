<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('kitchen_printer_type')->nullable()->default('network'); // 'network' or 'usb'
            $table->string('kitchen_printer_path')->nullable(); // IP address or Share Name
            
            $table->string('cashier_printer_type')->nullable()->default('network');
            $table->string('cashier_printer_path')->nullable();
            
            $table->boolean('auto_print_kot')->default(false);
            $table->boolean('auto_print_receipt')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'kitchen_printer_type',
                'kitchen_printer_path',
                'cashier_printer_type',
                'cashier_printer_path',
                'auto_print_kot',
                'auto_print_receipt',
            ]);
        });
    }
};
