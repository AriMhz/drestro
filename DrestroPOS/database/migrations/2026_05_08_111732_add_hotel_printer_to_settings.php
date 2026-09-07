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
            $table->string('hotel_printer_type')->default('windows_usb_share');
            $table->string('hotel_printer_address')->nullable();
            $table->boolean('hotel_auto_print')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['hotel_printer_type', 'hotel_printer_address', 'hotel_auto_print']);
        });
    }
};
