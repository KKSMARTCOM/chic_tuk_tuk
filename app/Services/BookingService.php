<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function calculateTotalPrice(int $basePrice, int $days, int $discount = 0): int
    {
        if ($days < 1) {
            $days = 1;
        }

        $totalPrice = $basePrice /* * $days */ - $discount;

        return max(0, $totalPrice);
    }

    public function create(array $data)
    {
        $booking = Booking::create([
            'from_zone_id' => $data['from_zone_id'],
            'to_zone_id' => $data['to_zone_id'],
            'phone' => $data['phone'],
            'days' => $data['days'],
            'pickup_datetime' => $data['pickup_datetime'],
            //'passengers' => $data['passengers'],
            'special_requests' => $data['special_requests'],
            'tourist_circuit_id' => $data['tourist_circuit_id'],
            'base_price' => $data['base_price'],
            'total_price' => $data['total_price'],
        ]);

        return $booking;
    }

    public function get(?string $status = null)
    {
        return Booking::when($status, function ($query, $status) {
            $query->where('status', $status);
        })->orderBy('pickup_datetime')
            ->get();
    }

    public function getById(string $bookingId)
    {
        return Booking::findOrFail($bookingId);
    }

    public function getByUserId(string $userId)
    {
        return Booking::where('user_id', $userId)->get();
    }

    public function getByDriverId(string $driverId, string|array|null $status = null)
    {
        return Booking::query()
            ->where('driver_id', $driverId)
            ->when($status, function ($query, $status) {
                is_array($status)
                    ? $query->whereIn('status', $status)
                    : $query->where('status', $status);
            })
            ->orderBy('pickup_datetime')
            ->get();
    }

    public function take(string $bookingId, string $driverId)
    {
        DB::transaction(function () use ($bookingId, $driverId) {

            $booking = Booking::lockForUpdate()->findOrFail($bookingId);

            if ($booking->status !== 'pending' || $booking->driver_id) {
                throw new \Exception('Réservation déjà prise ou annulée.');
            }

            $driver = Driver::lockForUpdate()->findOrFail($driverId);

            $pickup = Carbon::parse($booking->pickup_datetime);

            // ❌ Fenêtre de blocage ±2h
            if ($driver->hasConflictWithinTwoHours($pickup)) {
                throw new \Exception(
                    'Vous avez déjà une course dans une plage de 2 heures autour de cet horaire.'
                );
            }

            // ❌ Limite journalière
            /* if ($driver->hasReachedDailyLimit($pickup)) {
                throw new \Exception(
                    'Limite journalière de courses atteinte.'
                );
            } */

            $booking->update([
                'driver_id' => $driver->id,
                'status'    => 'confirmed',
            ]);
        });
    }

    public function cancel(string $bookingId, string $driverId, string $reason): Booking
    {
        return DB::transaction(function () use ($bookingId, $driverId, $reason) {

            $booking = Booking::lockForUpdate()->findOrFail($bookingId);

            if ($booking->driver_id !== $driverId) {
                throw new \Exception('Accès non autorisé.');
            }

            if (!$booking->canBeCancelled()) {
                throw new \Exception(
                    'Cette réservation ne peut plus être annulée.'
                );
            }

            $booking->update([
                'status'               => 'cancelled',
                'cancelled_at'         => now(),
                'cancellation_reason'  => $reason,
            ]);

            return $booking;
        });
    }

    public function start(string $bookingId, string $driverId)
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

    public function complete(string $bookingId, string $driverId)
    {
        return DB::transaction(function () use ($bookingId, $driverId) {

            $booking = Booking::lockForUpdate()->findOrFail($bookingId);

            if (
                $booking->driver_id !== $driverId ||
                $booking->status !== 'in_progress'
            ) {
                throw new \Exception('Finalisation non autorisée.');
            }

            $booking->update([
                'status'        => 'completed',
                'completed_at'  => now(),
            ]);

            Driver::where('id', $driverId)
                ->lockForUpdate()
                ->increment('total_trips');

            return $booking;
        });
    }

    public function update(string $bookingId, array $data)
    {
        $booking = Booking::findOrFail($bookingId);
        $booking->update($data);

        return $booking;
    }

    public function delete(string $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $booking->delete();
    }
}
