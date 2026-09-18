<?php

namespace App\Domains\Booking\Application\Actions;

use App\Models\Booking;
use Illuminate\Support\Collection;

/**
 * Les courses acceptées d'un agent : confirmées et en cours.
 *
 * Équivalent de l'appel que fait le Blade — getByDriverId($id, ['confirmed',
 * 'in_progress']) — avec les relations que la vue consomme, chargées d'avance.
 *
 * L'ordre est ASCENDANT sur CONCAT(pickup_date, ' ', pickup_time), et non sur
 * l'addition PostgreSQL utilisée par les courses disponibles. Deux expressions
 * différentes pour le même tri : c'est ainsi dans l'original, et les uniformiser serait
 * une réécriture.
 */
final class ListAssignedBookings
{
    /** @return Collection<int, Booking> */
    public function __invoke(string $driverId): Collection
    {
        return Booking::query()
            ->where('driver_id', $driverId)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with(['user', 'parentBooking.user'])
            ->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) ASC")
            ->get();
    }
}
