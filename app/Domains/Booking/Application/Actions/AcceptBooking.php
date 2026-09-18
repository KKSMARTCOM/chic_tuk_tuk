<?php

namespace App\Domains\Booking\Application\Actions;

use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Accepter une course — ex-BookingService::take().
 *
 * Déplacée sans réécriture le 2026-09-18 : même transaction, même lockForUpdate, même
 * ordre d'opérations, mêmes messages. BookingService::take() délègue désormais ici, de
 * sorte qu'il n'existe qu'une seule implémentation pour le chemin Blade et pour l'API.
 *
 * Ne renvoie rien, comme la méthode d'origine : son DB::transaction n'était pas
 * `return`é. Lui faire renvoyer la course serait une réécriture, si petite soit-elle.
 */
final class AcceptBooking
{
    public function __invoke(string $bookingId, string $driverId): void
    {
        DB::transaction(function () use ($bookingId, $driverId) {

            $booking = Booking::lockForUpdate()->findOrFail($bookingId);

            if ($booking->status !== 'pending' || $booking->driver_id) {
                throw new \Exception('Réservation déjà prise ou annulée.');
            }

            if (!$booking->isVisibleToDriver($driverId)) {
                throw new \Exception('Cette course n\'est pas accessible.');
            }

            $driver = Driver::lockForUpdate()->findOrFail($driverId);

            $updateData = [
                'driver_id' => $driver->id,
                'status'    => 'confirmed',
            ];

            // Abonnement parent → lier le titulaire + course retour abonnement
            if (
                $booking->is_recurring
                && is_null($booking->parent_booking_id)
                && !$booking->subscription_driver_id
            ) {
                $updateData['subscription_driver_id'] = $driver->id;

                Booking::where('parent_booking_id', $booking->id)
                    ->where('trip_type', 'return')
                    ->whereNull('subscription_driver_id')
                    ->update(['subscription_driver_id' => $driver->id]);
            }

            // Course unique aller avec aller-retour → lier l'agent à la course retour
            if (
                !$booking->is_recurring
                && $booking->round_trip
                && $booking->trip_type === 'go'
                && is_null($booking->parent_booking_id) // course aller principale
            ) {
                $updated = Booking::where('parent_booking_id', $booking->id)
                    ->where('trip_type', 'return')
                    ->whereNull('subscription_driver_id')
                    ->update(['subscription_driver_id' => $driver->id]);

                Log::info("[take] Courses retour simples liées à l'agent : {$updated}");
            }

            $booking->update($updateData);

            Log::info("[take] Booking {$bookingId} accepté par driver {$driverId}");
        });
    }
}
