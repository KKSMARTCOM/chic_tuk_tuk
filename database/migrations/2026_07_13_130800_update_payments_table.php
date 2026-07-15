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
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('vehicle_contract_id')->nullable()->after('driver_id');
            $table->uuid('driver_contract_id')->nullable()->after('vehicle_contract_id');
            $table->date('payment_month')->nullable()->after('payment_date'); // mois concerné
            $table->foreign('vehicle_contract_id')->references('id')->on('vehicle_contracts')->nullOnDelete();
            $table->foreign('driver_contract_id')->references('id')->on('driver_contracts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['vehicle_contract_id']);
            $table->dropForeign(['driver_contract_id']);
            $table->dropColumn(['vehicle_contract_id', 'driver_contract_id', 'payment_month']);
        });
    }
};
