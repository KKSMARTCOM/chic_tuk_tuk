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
        Schema::create('vehicle_pauses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vehicle_id');
            $table->uuid('vehicle_contract_id');
            $table->uuid('driver_contract_id')->nullable(); // null si pause non liée à un agent
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('vehicle_contract_id')->references('id')->on('vehicle_contracts')->onDelete('cascade');
            $table->foreign('driver_contract_id')->references('id')->on('driver_contracts')->nullOnDelete();

            $table->date('start_date');
            $table->date('end_date')->nullable();           // null = pause en cours
            $table->string('reason_type'); // agent_leave, agent_change, technical, accident, legal, other
            $table->text('reason_notes')->nullable();
            $table->boolean('is_auto')->default(false);    // true = créée automatiquement par un congé agent
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_pauses');
    }
};
