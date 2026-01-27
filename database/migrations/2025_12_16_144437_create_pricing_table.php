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
        Schema::create('pricing', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('from_location');
            $table->string('to_location');
            $table->decimal('base_price', 10, 2);
            $table->decimal('price_per_km', 10, 2)->default(0);
            $table->integer('estimated_duration')->comment('en minutes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing');
    }
};
