<?php

namespace App\Domains\Booking\Application\Actions;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

/**
 * Révoquer une course d'abonnement — ex-BookingService::revokeFromSubscription().
 *
 * Déplacée sans réécriture. La course redevient `pending`, perd ses deux agents et
 * porte `is_revoked` : elle est alors visible de tous.
 */
final class RevokeFromSubscription
{
    public function __invoke(string $bookingId, string $driverId): Booking
    {
        return DB::transaction(function () use ($bookingId, $driverId) {

            $booking = Booking::lockForUpdate()->findOrFail($bookingId);

            // Seul l'agent lié peut révoquer
            if ($booking->subscription_driver_id !== $driverId) {
                throw new \Exception('Vous n\'êtes pas autorisé à révoquer cette course.');
            }

            if (!in_array($booking->status, ['pending', 'confirmed'])) {
                throw new \Exception('Cette course ne peut plus être révoquée.');
            }

            $booking->update([
                'driver_id'   => null,
                'status'      => 'pending',
                'is_revoked'  => true,
                'revoked_at'  => now(),
                'revoked_by'  => $driverId,
                'subscription_driver_id' => null,
            ]);

            return $booking;
        });
    }
}
