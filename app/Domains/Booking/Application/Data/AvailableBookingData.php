<?php

namespace App\Domains\Booking\Application\Data;

use App\Domains\Booking\Application\Data\Concerns\MapsBookingSchedule;
use App\Models\Booking;
use App\Shared\Data\BaseData;

/**
 * Une course que l'agent peut prendre — GET /driver/bookings/available.
 *
 * ⚠️ AUCUNE coordonnée client. Vérifié dans resources/views/pages/driver/bookings/
 * available.blade.php : l'écran n'affiche ni téléphone, ni nom du client, ni demandes
 * particulières, ni prix. Seul le nom du client d'un abonnement PARENT y figure, et
 * c'est `parent_client_name`.
 *
 * C'est une règle de confidentialité, pas une mise en page : elle est portée par la
 * structure de cette classe, et non par des champs facultatifs qu'un oubli remplirait.
 * Ne JAMAIS ajouter `phone`, `client_name`, `special_requests` ni `base_price` ici.
 */
final class AvailableBookingData extends BaseData
{
    use MapsBookingSchedule;

    public function __construct(
        public string $id,
        public string $tripType,
        public bool $roundTrip,
        public string $fromLocation,
        public string $toLocation,
        public string $pickupAt,
        public ?string $returnTime,
        public bool $isSimpleReturn,
        public bool $isSubscriptionParent,
        public bool $isSubscriptionChild,
        public bool $isRevoked,
        /** Jamais null : l'accesseur renvoie « Course unique » par défaut. */
        public string $subscriptionLabel,
        public ?string $subscriptionEndDate,
        /** `lun_ven` | `lun_sam` | `lun_dim` — une chaîne, pas une liste. */
        public ?string $weekDays,
        public ?int $days,
        public ?int $remainingDays,
        /** Le seul nom de client visible avant acceptation. */
        public ?string $parentClientName,
    ) {}

    /** Attend une course ayant chargé `parentBooking.user`. */
    public static function fromModel(Booking $booking): self
    {
        return new self(
            id: $booking->id,
            tripType: $booking->trip_type,
            roundTrip: (bool) $booking->round_trip,
            fromLocation: $booking->from_location,
            toLocation: $booking->to_location,
            pickupAt: self::pickupAt($booking),
            returnTime: $booking->return_time,
            isSimpleReturn: $booking->is_simple_return,
            isSubscriptionParent: $booking->is_subscription_parent,
            isSubscriptionChild: $booking->is_subscription_child,
            isRevoked: (bool) $booking->is_revoked,
            subscriptionLabel: $booking->subscription_label,
            subscriptionEndDate: $booking->subscription_end_date?->format('Y-m-d'),
            weekDays: $booking->week_days,
            days: $booking->days,
            remainingDays: $booking->remaining_days,
            parentClientName: self::nomDuClientParent($booking),
        );
    }

    /**
     * La cascade exacte de la vue Blade : client_name, puis le nom de l'utilisateur,
     * puis le numéro de la course du parent. Null s'il n'y a pas de parent.
     */
    private static function nomDuClientParent(Booking $booking): ?string
    {
        $parent = $booking->parentBooking;

        if (! $parent) {
            return null;
        }

        return $parent->client_name ?? $parent->user?->name ?? $parent->booking_number;
    }
}
