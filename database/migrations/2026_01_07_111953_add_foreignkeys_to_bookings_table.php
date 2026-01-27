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
        Schema::table('bookings', function (Blueprint $table) {
            //Ajout des nouvelles colonnes
            $table->uuid('from_zone_id')->after('tourist_circuit_id');
            $table->uuid('to_zone_id')->after('from_zone_id');
            //Clés étrangères
            $table->foreign('from_zone_id')
                ->references('id')
                ->on('zones')
                ->cascadeOnDelete();
            $table->foreign('to_zone_id')
                ->references('id')
                ->on('zones')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            //Suppression des clés étrangères
            $table->dropForeign(['from_zone_id']);
            $table->dropForeign(['to_zone_id']);
            //Suppression des nouvelles colonnes
            $table->dropColumn(['from_zone_id', 'to_zone_id']);
        });
    }
};
