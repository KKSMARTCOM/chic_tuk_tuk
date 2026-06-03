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
            $table->string('tricycle_owner')->nullable()->after('start_date');
            $table->string('owner_phone')->nullable()->after('tricycle_owner');
            $table->integer('leave_days_used')->default(0)->after('owner_phone');
            $table->json('leave_dates')->nullable()->after('leave_days_used'); // array of dates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['tricycle_owner', 'owner_phone', 'leave_days_used', 'leave_dates']);
        });
    }
};
