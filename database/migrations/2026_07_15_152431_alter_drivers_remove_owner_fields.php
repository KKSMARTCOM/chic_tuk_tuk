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
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'tricycle_owner',
                'owner_phone',
                'contract_type',
                'start_date',
                'vehicle_number',
                'vehicle_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('tricycle_owner')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('contract_type')->nullable();
            $table->date('start_date')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('vehicle_type')->nullable();
        });
    }
};
