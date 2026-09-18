<?php

namespace App\Domains\Booking\Application\Data;

use App\Domains\Booking\Application\Data\Concerns\MapsBookingSchedule;
use App\Models\Booking;
use App\Shared\Data\BaseData;

/**
 * Une ligne d'historique — GET /driver/bookings/history.
 *
 * ⚠️ `commission` et `driverEarning` ne sont JAMAIS null : les colonnes sont
 * decimal(10,2) NOT NULL DEFAULT 0. Une course annulée avant `complete()` porte donc un
 * gain de 0, et c'est ce qu'il faut afficher. Le `?? $total_price` de la vue Blade est
 * une branche morte : la reproduire donnerait au gain d'une course annulée la valeur de
 * son prix, ce qui est précisément ce qu'il fallait éviter.
 */
final class BookingHistoryData extends BaseData
{
    use MapsBookingSchedule;

    public function __construct(
        public string $id,
        public string $bookingNumber,
        /** `completed` | `cancelled`. */
        public string $status,
        public string $fromLocation,
        public string $toLocation,
        public string $pickupAt,
        public int $passengers,
        public ?int $remainingDays,
        public ?string $startedAt,
        public ?string $completedAt,
        public ?string $cancelledAt,
        public ?string $cancellationReason,
        /** Null tant que la course n'a pas été démarrée ET terminée. */
        public ?int $durationMinutes,
        public float $totalPrice,
        public float $commission,
        public float $driverEarning,
    ) {}

    public static function fromModel(Booking $booking): self
    {
        $duree = ($booking->started_at && $booking->completed_at)
            ? (int) round($booking->started_at->diffInSeconds($booking->completed_at) / 60)
            : null;

        return new self(
            id: $booking->id,
            bookingNumber: $booking->booking_number,
            status: $booking->status,
            fromLocation: $booking->from_location,
            toLocation: $booking->to_location,
            pickupAt: self::pickupAt($booking),
            passengers: (int) $booking->passengers,
            remainingDays: $booking->remaining_days,
            startedAt: self::instant($booking->started_at),
            completedAt: self::instant($booking->completed_at),
            cancelledAt: self::instant($booking->cancelled_at),
            cancellationReason: $booking->cancellation_reason,
            durationMinutes: $duree,
            totalPrice: (float) $booking->total_price,
            commission: (float) $booking->commission,
            driverEarning: (float) $booking->driver_earning,
        );
    }
}
