<?php

namespace App\Domains\Booking\Application\Actions;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * L'historique d'un agent : ses courses terminées et annulées.
 *
 * ⚠️ Transposée depuis PageController::historiesBookings, branche `profil === 'driver'`
 * — et NON depuis BookingService::getByDriverId, que l'écran d'historique n'appelle pas.
 * Les deux diffèrent sur la pagination, l'ordre et les relations, et c'est la seconde que
 * le CLAUDE.md décrivait.
 *
 * L'ordre est DESCENDANT : le plus récent d'abord, comme le Blade.
 */
final class ListBookingHistory
{
    /** Dix par page, comme le Blade. */
    private const PAR_PAGE = 10;

    public function __invoke(string $driverId, ?string $search = null): LengthAwarePaginator
    {
        return Booking::query()
            ->where('driver_id', $driverId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->when($search, function ($query, string $search) {
                // Les quatre colonnes du Blade. `from_location` et `to_location` sont
                // des colonnes texte sur ce chemin — ce sont des relations `zones` sur
                // le chemin client, d'où le whereHas qu'on trouve dans getByUserId.
                $query->where(function ($q) use ($search) {
                    $q->where('booking_number', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('from_location', 'LIKE', "%{$search}%")
                        ->orWhere('to_location', 'LIKE', "%{$search}%");
                });
            })
            ->with(['user', 'driver.user'])
            ->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) DESC")
            ->paginate(self::PAR_PAGE);
    }
}
