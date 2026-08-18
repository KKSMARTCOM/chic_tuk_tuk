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
        Schema::table('vehicle_contracts', function (Blueprint $table) {
            $table->integer('contract_months')->default(24)->after('monthly_payment');
            $table->decimal('unlimited_internet', 10, 2)->default(0)->after('contract_months');
            $table->decimal('spotify_premium', 10, 2)->default(0)->after('unlimited_internet');
            $table->decimal('manager_remuneration', 12, 2)->default(0)->after('spotify_premium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_contracts', function (Blueprint $table) {
            $table->dropColumn('contract_months');
            $table->dropColumn('unlimited_internet');
            $table->dropColumn('spotify_premium');
            $table->dropColumn('manager_remuneration');
        });
    }
};
