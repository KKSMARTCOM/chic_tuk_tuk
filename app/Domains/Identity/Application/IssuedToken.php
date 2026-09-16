<?php

namespace App\Domains\Identity\Application;

use App\Models\User;

/** Résultat d'une authentification réussie : l'utilisateur et son jeton en clair. */
final readonly class IssuedToken
{
    public function __construct(
        public User $user,
        public string $plainTextToken,
    ) {}
}
