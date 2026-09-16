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
            // Abonnement
            $table->string('week_days')->nullable()->after('days');         // lun_ven / lun_sam / lun_dim
            $table->boolean('round_trip')->default(false)->after('week_days');
            $table->string('return_time')->nullable()->after('round_trip'); // heure retour HH:MM
            $table->string('trip_type')->default('go')->after('return_time'); // go / return
            $table->integer('trip_count')->default(1)->after('trip_type');
            // Agent lié temporairement à l'abonnement
            $table->uuid('subscription_driver_id')->nullable()->after('driver_id');
            $table->foreign('subscription_driver_id')->references('id')->on('drivers')->nullOnDelete();
            // Révocation (cours d'abonnement libérée)
            $table->boolean('is_revoked')->default(false)->after('subscription_driver_id');
            $table->timestamp('revoked_at')->nullable()->after('is_revoked');
            $table->uuid('revoked_by')->nullable()->after('revoked_at'); // driver_id qui a révoqué
            $table->string('client_name')->nullable()->after('revoked_by');
            $table->date('subscription_end_date')->nullable()->after('next_recurring_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['subscription_driver_id']);
            $table->dropColumn([
                'week_days',
                'round_trip',
                'return_time',
                'trip_type',
                'trip_count',
                'subscription_driver_id',
                'is_revoked',
                'revoked_at',
                'revoked_by',
                'client_name',
                'subscription_end_date',
            ]);
        });
    }
};
