<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('currency', 10)->default('Rs.')->after('logo');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('currency');
            $table->decimal('service_charge_percent', 5, 2)->default(0)->after('tax_percent');
            $table->string('pan_number')->nullable()->after('service_charge_percent');
            $table->string('tagline')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['currency', 'tax_percent', 'service_charge_percent', 'pan_number', 'tagline']);
        });
    }
};
