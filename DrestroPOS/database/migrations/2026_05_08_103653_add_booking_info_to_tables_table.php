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
        Schema::table('tables', function (Blueprint $table) {
            $table->string('guest_name')->nullable();
            $table->string('guest_phone')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->decimal('room_rate', 10, 2)->nullable();
            $table->string('room_status')->default('available'); // available, occupied, dirty
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_phone', 'check_in_at', 'room_rate', 'room_status']);
        });
    }
};
