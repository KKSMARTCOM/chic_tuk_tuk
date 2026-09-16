<?php

namespace App\Domains\Booking\Domain\Enums;

use App\Shared\Enums\HasOptions;

/** Sens du trajet — colonne `bookings.trip_type`, défaut « go ». */
enum TripType: string
{
    use HasOptions;

    case Go     = 'go';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::Go     => 'Aller',
            self::Return => 'Retour',
        };
    }
}
