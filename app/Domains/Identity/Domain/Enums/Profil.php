<?php

namespace App\Domains\Identity\Domain\Enums;

use App\Shared\Enums\HasOptions;

/**
 * Profil d'un utilisateur — colonne `users.profil`.
 * Source de vérité : contrainte CHECK `users_profil_check`.
 *
 * Détermine l'espace accessible (middleware `profil:xxx`) et sert de nom d'ability
 * sur les tokens Sanctum.
 */
enum Profil: string
{
    use HasOptions;

    case Admin  = 'admin';
    case Client = 'client';
    case Driver = 'driver';
    case Owner  = 'owner';

    public function label(): string
    {
        return match ($this) {
            self::Admin  => 'Administrateur',
            self::Client => 'Client',
            self::Driver => 'Agent',
            self::Owner  => 'Propriétaire',
        };
    }

    /** Chemin du tableau de bord de l'espace, côté front Nuxt. */
    public function dashboardPath(): string
    {
        return match ($this) {
            self::Admin  => '/admin/dashboard',
            self::Client => '/client/dashboard',
            self::Driver => '/driver/dashboard',
            self::Owner  => '/owner/dashboard',
        };
    }
}
