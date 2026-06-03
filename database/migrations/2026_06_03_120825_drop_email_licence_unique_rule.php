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
        // Email unique par profil
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['email', 'profil'], 'users_email_profil_unique');
        });

        // Supprimer complètement l'unicité de license_number
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropUnique(['license_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_profil_unique');
            $table->unique('email');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->unique('license_number');
        });
    }
};
