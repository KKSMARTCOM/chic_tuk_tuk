<?php

namespace App\Domains\Booking\Application\Actions;

use App\Models\Booking;
use App\Models\Driver;

/**
 * Les compteurs du tableau de bord de l'agent.
 *
 * Transposée de DriverService::getDriverDashboardStats(). Les dix entrées que la vue
 * Blade affiche sont toutes reprises — y compris total_earnings, commission_today et
 * total_commission, que la spec omettait : les laisser tomber ferait disparaître le gain
 * cumulé de l'écran au moment de la bascule de 3c, trop tard pour s'en apercevoir à peu
 * de frais.
 *
 * L'aperçu des courses acceptées filtre sur `confirmed` SEUL, et non sur
 * ['confirmed', 'in_progress'] comme l'écran dédié. C'est ainsi dans l'original.
 */
final class BuildDriverDashboard
{
    public function __construct(private readonly ListAvailableBookings $available) {}

    /** @return array<string, mixed> */
    public function __invoke(Driver $driver): array
    {
        // La durée se calcule en PHP et non en SQL : `started_at` et `completed_at` sont
        // des timestamps, et l'original somme des diffInSeconds sur une collection
        // chargée. Passer à une somme SQL changerait les arrondis.
        $dureeEnSecondes = Booking::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get()
            ->sum(fn (Booking $booking) => $booking->started_at->diffInSeconds($booking->completed_at));

        $termineesDeCetAgent = fn () => Booking::where('driver_id', $driver->id)
            ->where('status', 'completed');

        return [
            'total_trips' => $driver->total_trips,
            'rating' => (float) $driver->rating,
            'confirmed_trips' => Booking::where('driver_id', $driver->id)->where('status', 'confirmed')->count(),
            'completed_trips' => Booking::where('driver_id', $driver->id)->where('status', 'completed')->count(),
            'cancelled_trips' => Booking::where('driver_id', $driver->id)->where('status', 'cancelled')->count(),

            'earnings_today' => (float) $termineesDeCetAgent()->whereDate('completed_at', today())->sum('driver_earning'),
            'total_earnings' => (float) $termineesDeCetAgent()->sum('driver_earning'),
            'commission_today' => (float) $termineesDeCetAgent()->whereDate('completed_at', today())->sum('commission'),
            'total_commission' => (float) $termineesDeCetAgent()->sum('commission'),

            'total_duration_minutes' => (int) round($dureeEnSecondes / 60),

            // Les cinq premières de la liste réelle : le tableau de bord consomme la
            // même fonction que l'écran dédié, jamais une requête parallèle.
            'recent_available' => ($this->available)($driver->id)->take(5)->values(),

            'recent_assigned' => Booking::where('driver_id', $driver->id)
                ->where('status', 'confirmed')
                ->with(['user', 'parentBooking.user'])
                ->orderByRaw('(pickup_date::date + pickup_time::time) ASC')
                ->take(5)
                ->get(),
        ];
    }
}
