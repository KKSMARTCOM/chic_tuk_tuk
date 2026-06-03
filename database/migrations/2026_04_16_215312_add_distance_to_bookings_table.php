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
            // Supprimer les contraintes FK
            $table->dropForeign(['from_zone_id']);
            $table->dropForeign(['to_zone_id']);

            // Rendre nullable
            $table->uuid('from_zone_id')->nullable()->change();
            $table->uuid('to_zone_id')->nullable()->change();

            // Ajouter nouvelles colonnes
            $table->string('from_location')->after('to_zone_id');
            $table->decimal('from_lat', 10, 7)->nullable()->after('from_location');
            $table->decimal('from_lng', 10, 7)->nullable()->after('from_lat');

            $table->string('to_location')->after('from_lng');
            $table->decimal('to_lat', 10, 7)->nullable()->after('to_location');
            $table->decimal('to_lng', 10, 7)->nullable()->after('to_lat');

            $table->decimal('distance', 8, 2)->after('to_zone_id');

            // Recréer les FK
            $table->foreign('from_zone_id')
                ->references('id')
                ->on('zones')
                ->nullOnDelete();

            $table->foreign('to_zone_id')
                ->references('id')
                ->on('zones')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Supprimer FK
            $table->dropForeign(['from_zone_id']);
            $table->dropForeign(['to_zone_id']);

            // Supprimer colonnes ajoutées
            $table->dropColumn([
                'from_location',
                'from_lat',
                'from_lng',
                'to_location',
                'to_lat',
                'to_lng',
                'distance'
            ]);

            // Revenir en non nullable
            $table->uuid('from_zone_id')->nullable(false)->change();
            $table->uuid('to_zone_id')->nullable(false)->change();

            // Recréer FK avec cascade
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
};
