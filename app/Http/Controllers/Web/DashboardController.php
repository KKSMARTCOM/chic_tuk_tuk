<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function admin()
    {
        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_drivers' => Driver::count(),
            'active_drivers' => Driver::where('is_available', true)->count(),
            'total_revenue' => Booking::where('status', 'completed')->sum('total_price'),
        ];

        // Statistiques du jour
        $todayStats = [
            'completed_today' => Booking::where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'in_progress_today' => Booking::where('status', 'in_progress')
                ->whereDate('started_at', today())
                ->count(),
            'cancelled_today' => Booking::where('status', 'cancelled')
                ->whereDate('cancelled_at', today())
                ->count(),
        ];

        $recentBookings = Booking::with(['user', 'driver', 'fromZone', 'toZone'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        return view('pages.admin.dashboard', compact('stats', 'todayStats', 'recentBookings'));
    }

    public function driver()
    {
        $driver = Auth::user()->driver;

        // Calcul du temps total de courses en minutes
        $total_duration_seconds = Booking::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get()
            ->sum(function ($booking) {
                return $booking->started_at->diffInSeconds($booking->completed_at);
            });

        $recentBookings = Booking::where('status', 'pending')
            ->with(['fromZone', 'toZone'])
            ->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) DESC")
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'total_trips' => $driver->total_trips,
            'rating' => $driver->rating,
            'confirmed_trips' => Booking::where('driver_id', $driver->id)->where('status', 'confirmed')->count(),
            'completed_trips' => Booking::where('driver_id', $driver->id)->where('status', 'completed')->count(),
            'cancelled_trips' => Booking::where('driver_id', $driver->id)->where('status', 'cancelled')->count(),
            'earnings_today' => Booking::where('driver_id', $driver->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->sum('total_price'),
            'total_earnings' => Booking::where('driver_id', $driver->id)
                ->where('status', 'completed')
                ->sum('total_price'),
            'total_duration_minutes' => round($total_duration_seconds / 60),
        ];

        return view('pages.driver.dashboard', compact('stats', 'recentBookings'));
    }
}
