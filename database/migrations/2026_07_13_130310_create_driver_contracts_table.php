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
        Schema::create('driver_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('driver_id');
            $table->uuid('vehicle_id');
            $table->uuid('vehicle_contract_id');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('vehicle_contract_id')->references('id')->on('vehicle_contracts')->onDelete('cascade');

            $table->date('start_date');
            $table->date('end_date')->nullable();           // null = contrat actif
            $table->integer('contract_months');             // durée prévue
            $table->string('status')->default('active');   // active, ended
            $table->string('end_reason')->nullable();      // demission, abandon, fin_contrat, autre
            $table->text('end_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_contracts');
    }
};
