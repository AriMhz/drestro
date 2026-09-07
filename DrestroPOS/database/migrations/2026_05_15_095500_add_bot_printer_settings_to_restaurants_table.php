<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('bot_printer_type')->nullable()->default('network');
            $table->string('bot_printer_path')->nullable();
            $table->boolean('separate_kot_bot')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['bot_printer_type', 'bot_printer_path', 'separate_kot_bot']);
        });
    }
};
