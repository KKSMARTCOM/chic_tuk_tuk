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
            $table->string('tricycle_owner')->nullable()->change();
            $table->string('owner_phone')->nullable()->change();
            $table->string('contract_type')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->string('vehicle_number')->nullable()->change();
            $table->string('vehicle_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('tricycle_owner')->nullable(false)->change();
            $table->string('owner_phone')->nullable(false)->change();
            $table->string('contract_type')->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
            $table->string('vehicle_number')->nullable(false)->change();
            $table->string('vehicle_type')->nullable(false)->change();
        });
    }
};
