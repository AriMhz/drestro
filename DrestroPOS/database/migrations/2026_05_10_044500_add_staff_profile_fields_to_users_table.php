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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('alt_email')->nullable()->after('email');
            $table->text('address')->nullable()->after('alt_email');
            $table->string('phone', 20)->nullable()->after('address');
            $table->string('username')->nullable()->unique()->after('phone');
            $table->text('description')->nullable()->after('role');
        });

        // Migrate existing 'name' data into first_name / last_name
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $parts = explode(' ', $user->name, 2);
            $user->first_name = $parts[0] ?? '';
            $user->last_name  = $parts[1] ?? '';
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'alt_email', 'address', 'phone', 'username', 'description']);
        });
    }
};
