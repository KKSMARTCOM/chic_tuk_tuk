<?php

namespace App\Domains\Booking\Application\Data;

use App\Models\Booking;
use App\Shared\Data\BaseData;

/**
 * GET /driver/dashboard.
 *
 * Les dix compteurs que la vue Blade affiche, plus les deux que la spec ajoute
 * (total_trips, rating). Trois compteurs manquaient à la spec — total_earnings,
 * commission_today et total_commission — et sont repris ici : les perdre ne se verrait
 * qu'à la bascule de 3c, trop tard pour s'en apercevoir à peu de frais.
 */
final class DriverDashboardData extends BaseData
{
    public function __construct(
        public int $totalTrips,
        public float $rating,
        public int $confirmedTrips,
        public int $completedTrips,
        public int $cancelledTrips,
        public float $earningsToday,
        public float $totalEarnings,
        public float $commissionToday,
        public float $totalCommission,
        public int $totalDurationMinutes,
        /** @var array<int, AvailableBookingData> les cinq premières */
        public array $recentAvailable,
        /** @var array<int, AssignedBookingData> les cinq premières confirmées */
        public array $recentAssigned,
    ) {}

    /** @param array<string, mixed> $stats la sortie de BuildDriverDashboard */
    public static function fromStats(array $stats): self
    {
        return new self(
            totalTrips: (int) $stats['total_trips'],
            rating: (float) $stats['rating'],
            confirmedTrips: (int) $stats['confirmed_trips'],
            completedTrips: (int) $stats['completed_trips'],
            cancelledTrips: (int) $stats['cancelled_trips'],
            earningsToday: (float) $stats['earnings_today'],
            totalEarnings: (float) $stats['total_earnings'],
            commissionToday: (float) $stats['commission_today'],
            totalCommission: (float) $stats['total_commission'],
            totalDurationMinutes: (int) $stats['total_duration_minutes'],
            recentAvailable: collect($stats['recent_available'])
                ->map(fn (Booking $b) => AvailableBookingData::fromModel($b))->all(),
            recentAssigned: collect($stats['recent_assigned'])
                ->map(fn (Booking $b) => AssignedBookingData::fromModel($b))->all(),
        );
    }
}
