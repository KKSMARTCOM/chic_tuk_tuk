<?php

namespace App\Domains\Booking\Application\Actions;

use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

/**
 * Démarrer une course — ex-BookingService::start(), déplacée sans réécriture.
 *
 * ⚠️ Deux refus qui se ressemblent et n'ont pas la même cause : une course déjà
 * `in_progress`, et une course antérieure non soldée. Le second repose sur
 * Driver::hasBlockingPreviousBookings(), dont la comparaison ignore les courses
 * antérieures du MÊME JOUR — défaut existant, transposé tel quel et verrouillé par les
 * tests de caractérisation.
 */
final class StartBooking
{
    public function __invoke(string $bookingId, string $driverId): Booking
    {
        return DB::transaction(function () use ($bookingId, $driverId) {

            $booking = Booking::lockForUpdate()->findOrFail($bookingId);

            $driver = Driver::lockForUpdate()->findOrFail($driverId);

            if ($booking->driver_id !== $driverId || $booking->status !== 'confirmed') {
                throw new \Exception('Démarrage non autorisé.');
            }

            $hasOngoingTrip = Booking::where('driver_id', $driverId)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->exists();

            if ($hasOngoingTrip) {
                throw new \Exception(
                    'Vous avez déjà une course en cours.'
                );
            }

            if ($driver->hasBlockingPreviousBookings($booking)) {
                throw new \Exception(
                    'Vous devez terminer ou annuler toutes les courses précédentes avant de démarrer celle-ci.'
                );
            }

            $booking->update([
                'status'      => 'in_progress',
                'started_at'  => now(),
            ]);

            return $booking;
        });
    }
}
