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
        // Récupérer les IDs des users avec le rôle propriétaire
        $ownerIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'proprietaire')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('model_has_roles.model_id');

        if ($ownerIds->isEmpty()) {
            return;
        }

        // Mettre à jour leur profil
        DB::table('users')
            ->whereIn('id', $ownerIds)
            ->update(['profil' => 'owner']);

        $this->log("Profil 'owner' assigné à {$ownerIds->count()} utilisateur(s).");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        DB::table('users')
            ->where('profil', 'owner')
            ->update(['profil' => 'client']);
    }

    private function log(string $message): void
    {
        \Illuminate\Support\Facades\Log::info('[Migration] ' . $message);
    }
};
