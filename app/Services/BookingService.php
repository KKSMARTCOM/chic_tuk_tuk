<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\PromoCode;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class BookingService
{
    protected $pricingService;
    protected $commissionService;

    public function __construct(PricingService $pricingService, CommissionService $commissionService)
    {
        $this->pricingService = $pricingService;
        $this->commissionService = $commissionService;
    }

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
        // Calcul de la distance
        $distance = $this->pricingService->getDistance($data['from_lng'], $data['from_lat'], $data['to_lng'], $data['to_lat']);

        if (!$distance) {
            throw new \Exception('Erreur lors du calcul de l\'itinéraire.');
        }

        $basePrice = $this->pricingService->getPrice($distance);

        // Gestion promo
        $discount = 0;
        $promoCodeId = null;
        if ($data['promo_code']) {
            $promo = PromoCode::where('code', $data['promo_code'])->first();

            if ($promo && $promo->isValid()) {
                $discount = $promo->applyDiscount($basePrice);
                $promoCodeId = $promo->id;
                $promo->increment('used_count');
            } else {
                throw new \Exception('Code promo invalide ou expiré.');
            }
        }

        $totalPrice = $basePrice - $discount;
        $commission = ceil((($totalPrice * 15) / 100) / 50) * 50;
        $driverEarning = $totalPrice - $commission;

        $isRecurring = isset($data['days']) && $data['days'] > 1;

        $booking = Booking::create([
            'from_location'       => $data['from_location'],
            'to_location'         => $data['to_location'],
            'from_lng'            => $data['from_lng'],
            'from_lat'            => $data['from_lat'],
            'to_lng'              => $data['to_lng'],
            'to_lat'              => $data['to_lat'],
            'phone'               => $data['phone'],
            'days'                => $data['days'],
            'remaining_days'      => $data['days'] ?? 1,
            'pickup_date'         => $data['pickup_date'],
            'pickup_time'         => $data['pickup_time'],
            'special_requests'    => $data['special_requests'],
            'distance'            => $distance,
            'base_price'          => $basePrice,
            'total_price'         => $totalPrice,
            'commission'          => $commission,
            'driver_earning'      => $driverEarning,
            'is_recurring'        => $isRecurring,
            'next_recurring_date' => $isRecurring ? Carbon::parse($data['pickup_date'] . ' ' . $data['pickup_time'])->addDay() : null,
        ]);

        return $booking;
    }

    public function get(?string $status = null)
    {
        $query = Booking::query();

        if ($status) {
            $query->where('status', $status);
        } else {
            // Exclure les courses expirées si aucun statut n'est spécifié
            $query->where('status', '!=', 'expired');
        }

        return $query->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) DESC")->get();
    }

    public function getById(string $bookingId)
    {
        return Booking::findOrFail($bookingId);
    }

    public function getByUserId(string $userId, ?string $search = null)
    {
        $query = Booking::where('user_id', $userId)
            ->whereIn('status', ['completed', 'cancelled']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhereHas('fromZone', function ($zoneQuery) use ($search) {
                        $zoneQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('toZone', function ($zoneQuery) use ($search) {
                        $zoneQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        return $query->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) DESC")->get();
    }

    public function getByDriverId(string $driverId, string|array|null $status = null, ?string $search = null)
    {
        $query = Booking::query()
            ->where('driver_id', $driverId)
            ->when($status, function ($query, $status) {
                is_array($status)
                    ? $query->whereIn('status', $status)
                    : $query->where('status', $status);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhereHas('fromZone', function ($zoneQuery) use ($search) {
                        $zoneQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('toZone', function ($zoneQuery) use ($search) {
                        $zoneQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        return $query->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) ASC")->get();
    }

    public function take(string $bookingId, string $driverId)
    {
        DB::transaction(function () use ($bookingId, $driverId) {

            $booking = Booking::lockForUpdate()->findOrFail($bookingId);

            if ($booking->status !== 'pending' || $booking->driver_id) {
                throw new \Exception('Réservation déjà prise ou annulée.');
            }

            $driver = Driver::lockForUpdate()->findOrFail($driverId);

            $pickup = Carbon::parse($booking->pickup_date_time);

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

            // Créer une nouvelle course avec les mêmes données
            $newBooking = Booking::create([
                'from_location'      => $booking->from_location,
                'to_location'        => $booking->to_location,
                'from_lng'           => $booking->from_lng,
                'from_lat'           => $booking->from_lat,
                'to_lng'             => $booking->to_lng,
                'to_lat'             => $booking->to_lat,
                'distance'           => $booking->distance,
                'phone'              => $booking->phone,
                'days'               => $booking->days,
                'remaining_days'     => $booking->remaining_days,
                'pickup_date'        => $booking->pickup_date,
                'pickup_time'        => $booking->pickup_time,
                'special_requests'   => $booking->special_requests,
                'tourist_circuit_id' => $booking->tourist_circuit_id,
                'discount'           => $booking->discount,
                'promo_code_id'      => $booking->promo_code_id,
                'base_price'         => $booking->base_price,
                'total_price'        => $booking->total_price,
                'commission'         => $booking->commission,
                'driver_earning'     => $booking->driver_earning,
                'status'             => 'pending',
                'is_recurring'       => $booking->is_recurring,
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

            $commissionData = [
                'driver_id'       => $driverId,
                'booking_id'      => $bookingId,
                'amount'          => $booking->commission,
                'date'            => now(),
            ];

            $this->commissionService->create($commissionData);

            return $booking;
        });
    }

    public function update(Booking $booking, array $data)
    {
        $distance = $booking->distance;
        $basePrice = $booking->base_price;
        if (isset($data['from_lng']) && (float) $data['from_lng'] !== (float) $booking->from_lng || isset($data['to_lng']) && (float) $data['to_lng'] !== (float) $booking->to_lng) {
            $distance = $this->pricingService->getDistance($data['from_lng'], $data['from_lat'], $data['to_lng'], $data['to_lat']);
            $basePrice = $this->pricingService->getPrice($distance);
        }

        $commission = $booking->commission;
        $driverEarning = $booking->driver_earning;
        if (isset($data['total_price']) && (int) $data['total_price'] !== (int) $booking->total_price) {
            $commission = ceil((($data['total_price'] * 15) / 100) / 50) * 50;
            $driverEarning = $data['total_price'] - $commission;
        }

        $isRecurring = $booking->is_recurring;
        $nextRecurringDate = $booking->next_recurring_date;
        if (isset($data['days']) && $data['days'] !== $booking->days) {
            $isRecurring = isset($data['days']) && $data['days'] > 1;
            $pickup_date = $data['pickup_date'] instanceof Carbon ? $data['pickup_date']->format('Y-m-d') : $data['pickup_date'];
            $nextRecurringDate = $isRecurring ?  Carbon::parse($pickup_date . ' ' . $data['pickup_time'])->addDay() : null;
        }

        if (isset($data['status']) || isset($data['driver_id'])) {
            $booking->update($data);
            return $booking->refresh();
        }

        $booking->update(
            [
                'user_id'             => $data['user_id'] ?? $booking->user_id,
                'from_location'       => $data['from_location'] ?? $booking->from_location,
                'to_location'         => $data['to_location'] ?? $booking->to_location,
                'from_lng'            => $data['from_lng'] ?? $booking->from_lng,
                'from_lat'            => $data['from_lat'] ?? $booking->from_lat,
                'to_lng'              => $data['to_lng'] ?? $booking->to_lng,
                'to_lat'              => $data['to_lat'] ?? $booking->to_lat,
                'phone'               => $data['phone'] ?? $booking->phone,
                'days'                => $data['days'] ?? $booking->days,
                'remaining_days'      => $data['days'] ?? $booking->days,
                'pickup_date'         => $data['pickup_date'] ?? $booking->pickup_date,
                'pickup_time'         => $data['pickup_time'] ?? $booking->pickup_time,
                'status'              => $data['status'] ?? $booking->status,
                'special_requests'    => $data['special_requests'] ?? $booking->special_requests,
                'total_price'         => $data['total_price'] ?? $booking->total_price,
                'distance'            => $distance,
                'base_price'          => $basePrice,
                'commission'          => $commission,
                'driver_earning'      => $driverEarning,
                'is_recurring'        => $isRecurring,
                'next_recurring_date' => $nextRecurringDate,
            ]
        );

        return $booking->refresh();
    }

    public function delete(string $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $booking->delete();
    }

    public function markExpiredBookings()
    {
        $expiredBookings = Booking::where('status', 'pending')
            ->whereRaw("CONCAT(pickup_date, ' ', pickup_time) < ?", [now()])
            ->where('expired_at', null)
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'expired']);
        }

        return $expiredBookings->count();
    }

    public function createRecurringBookings()
    {
        $recurringBookings = Booking::where('is_recurring', true)
            ->where('remaining_days', '>', 0)
            ->where(function ($query) {
                $query->whereNull('next_recurring_date')
                    ->orWhere('next_recurring_date', '<=', now());
            })
            ->get();

        foreach ($recurringBookings as $booking) {
            // Créer la nouvelle course pour le jour suivant
            $newPickupDate = Carbon::parse($booking->pickup_date . ' ' . $booking->pickup_time)->addDay();

            Booking::create([
                'from_location'       => $booking->from_location,
                'to_location'         => $booking->to_location,
                'from_lng'            => $booking->from_lng,
                'from_lat'            => $booking->from_lat,
                'to_lng'              => $booking->to_lng,
                'to_lat'              => $booking->to_lat,
                'distance'            => $booking->distance,
                'phone'               => $booking->phone,
                'days'                => $booking->days,
                'remaining_days'      => $booking->remaining_days - 1,
                'pickup_date'         => $newPickupDate->toDateString(),
                'pickup_time'         => $newPickupDate->format('H:i'),
                'special_requests'    => $booking->special_requests,
                'tourist_circuit_id'  => $booking->tourist_circuit_id,
                'promo_code_id'       => $booking->promo_code_id,
                'discount'            => $booking->discount,
                'base_price'          => $booking->base_price,
                'total_price'         => $booking->total_price,
                'commission'          => $booking->commission,
                'driver_earning'      => $booking->driver_earning,
                'status'              => 'pending',
                'is_recurring'        => true,
                'parent_booking_id'   => $booking->parent_booking_id ?? $booking->id,
                'next_recurring_date' => $newPickupDate->addDay(),
            ]);

            // Mettre à jour la course actuelle avec le nombre de jours restants
            $booking->update([
                'remaining_days' => $booking->remaining_days - 1,
                'next_recurring_date' => $newPickupDate->addDay(),
            ]);
        }

        return $recurringBookings->count();
    }
}
