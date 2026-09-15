<?php

namespace App\Domains\Workforce\Domain\Enums;

use App\Shared\Enums\HasOptions;

/** Statut d'un contrat agent — colonne `driver_contracts.status`. */
enum DriverContractStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Ended  = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Actif',
            self::Ended  => 'Terminé',
        };
    }
}
