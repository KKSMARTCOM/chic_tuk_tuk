<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time')->nullable();
        });

        // Convert existing pickup_datetime to separate fields
        DB::statement("UPDATE bookings SET pickup_date = pickup_datetime::date, pickup_time = TO_CHAR(pickup_datetime, 'HH24:MI')::time WHERE pickup_datetime IS NOT NULL");

        Schema::table('bookings', function (Blueprint $table) {
            $table->date('pickup_date')->nullable(false)->change();
            $table->time('pickup_time')->nullable(false)->change();
            $table->dropColumn('pickup_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('pickup_datetime')->nullable();
        });

        // Convert back to pickup_datetime
        DB::statement("UPDATE bookings SET pickup_datetime = (pickup_date || ' ' || pickup_time)::timestamp WHERE pickup_date IS NOT NULL AND pickup_time IS NOT NULL");

        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('pickup_datetime')->nullable(false)->change();
            $table->dropColumn(['pickup_date', 'pickup_time']);
        });
    }
};
