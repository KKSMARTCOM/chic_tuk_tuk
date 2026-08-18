<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer l'ancienne contrainte sur l'ancienne colonne role
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        // Supprimer la contrainte profil si elle existe avec les anciennes valeurs
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_profil_check");
        // Recréer avec owner inclus
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_profil_check CHECK (profil IN ('admin', 'client', 'driver', 'owner'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_profil_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_profil_check CHECK (profil IN ('admin', 'client', 'driver'))");
    }
};
