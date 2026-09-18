<?php

namespace App\Domains\Booking\Application\Data\Concerns;

use App\Models\Booking;
use Carbon\Carbon;

/**
 * Mise en forme des horaires d'une course, partagée par les classes Data.
 *
 * Écrit une fois plutôt que trois : la distinction entre une heure murale et un instant
 * est exactement le genre de règle qu'une copie finit par perdre.
 */
trait MapsBookingSchedule
{
    /**
     * `pickup_date` + `pickup_time` en ISO 8601 LOCAL, sans décalage.
     *
     * Ces deux colonnes (`date` et `time`) décrivent une heure murale et non un instant :
     * elles sont saisies et affichées telles quelles par le Blade, sans conversion.
     * L'application tourne en UTC alors que le Bénin est à UTC+1 ; suffixer « +00:00 »
     * ferait reculer l'affichage d'une heure dans le navigateur d'un agent, exactement
     * comme `new Date('2026-09-18')` recule une date d'un jour.
     */
    private static function pickupAt(Booking $booking): string
    {
        $date = $booking->pickup_date instanceof Carbon
            ? $booking->pickup_date->format('Y-m-d')
            : (string) $booking->pickup_date;

        $heure = substr((string) $booking->pickup_time, 0, 5);

        return "{$date}T{$heure}:00";
    }

    /** Un vrai instant, lui : colonne `timestamp`, décalage conservé. */
    private static function instant(?Carbon $moment): ?string
    {
        return $moment?->toIso8601String();
    }
}
