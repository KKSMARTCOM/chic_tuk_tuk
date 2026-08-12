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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_type', ['commission', 'contract', 'bonus', 'other'])->default('commission')->after('driver_id');
            $table->enum('status', ['pending', 'completed', 'cancelled', 'failed'])->default('pending')->after('payment_type');
            $table->decimal('net_amount', 12, 2)->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'status', 'net_amount']);
        });
    }
};
