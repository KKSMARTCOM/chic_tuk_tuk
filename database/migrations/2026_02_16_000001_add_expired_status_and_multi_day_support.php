<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Ajouter le statut 'expired' à l'enum
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'expired'])->default('pending');
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Ajouter les champs pour supporter les réservations multi-jours
            $table->integer('remaining_days')->default(1)->after('days');
            $table->uuid('parent_booking_id')->nullable()->after('id');
            $table->boolean('is_recurring')->default(false)->after('parent_booking_id');
            $table->timestamp('next_recurring_date')->nullable()->after('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Retirer le statut 'expired' de l'enum
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Retirer les nouveaux champs
            $table->dropColumn(['remaining_days', 'is_recurring', 'next_recurring_date']);
            $table->dropColumn('parent_booking_id');
        });
    }
};
