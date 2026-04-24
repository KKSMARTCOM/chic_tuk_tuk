<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Driver;
use App\Services\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    public function admin()
    {
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

        // Revenue par agent/conducteur
        $driverRevenues = Driver::with('user')
            ->whereHas('commissions', function ($q) {
                $q->where('amount', '>', 0);
            })
            ->withSum('commissions', 'amount')
            ->orderByDesc('commissions_sum_amount')
            ->limit(5)
            ->get();

        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_drivers' => Driver::count(),
            'active_drivers' => Driver::where('is_available', true)->count(),
            'total_revenue' => Booking::where('status', 'completed')->sum('total_price'),
            'driver_revenues' => $driverRevenues,
        ];

        return view('pages.admin.dashboard', compact('stats', 'todayStats', 'recentBookings'));
    }

    public function driver()
    {
        $driver = Auth::user()->driver;

        $stats = $this->driverService->getDriverDashboardStats($driver);

        return view('pages.driver.dashboard', compact('stats'));
    }
}
