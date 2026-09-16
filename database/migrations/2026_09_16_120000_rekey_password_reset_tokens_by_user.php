<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réindexe password_reset_tokens sur l'utilisateur au lieu de l'email.
 *
 * L'unicité des comptes porte sur (email, profil) depuis la migration
 * 2026_06_03_120825 : une même adresse peut donc porter jusqu'à quatre comptes,
 * alors que la table de Laravel n'accepte qu'une ligne par email.
 *
 * Aucune donnée n'est perdue : aucune route ne servait les vues de réinitialisation,
 * la table est donc vide et l'image précédente n'y écrit jamais — cette migration est
 * sans risque au regard du `migrate --force` exécuté à chaque démarrage de conteneur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('password_reset_tokens');

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();

            // Supprimer un compte emporte ses demandes en cours.
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
};
