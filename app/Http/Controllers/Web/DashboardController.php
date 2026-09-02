<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Driver;
use App\Services\DriverService;
use App\Services\VehicleContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $driverService;

    public function __construct(
        DriverService $driverService,
        private VehicleContractService $contractService
    ) {
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

        $recentBookings = Booking::with(['user', 'driver',])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Revenue par agent/Agent
        $driverRevenues = Driver::with('user')

            ->whereHas('bookings', function ($query) {
                $query->where('status', 'completed')
                    ->where('driver_earning', '>', 0);
            })

            ->withSum(['bookings' => function ($query) {
                $query->where('status', 'completed')
                    ->where('driver_earning', '>', 0);
            }], 'driver_earning')

            ->withSum('commissions', 'amount')

            ->withSum(['payments' => function ($query) {
                $query->where('payment_type', 'commission');
            }], 'amount')

            ->orderByDesc('bookings_sum_driver_earning')

            ->limit(5)

            ->get();

        foreach ($driverRevenues as $driver) {
            $driver->commission_due = ($driver->commissions_sum_amount ?? 0) - ($driver->payments_sum_amount ?? 0);
        }

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

    public function client()
    {
        $user = Auth::user();

        return view('pages.client.dashboard');
    }

    public function owner()
    {
        $user = Auth::user();

        $vehicles = $user->vehicles()->with([
            'activeVehicleContract',
            'activeDriverContract.driver.user',
            'activePause',
        ])->get();

        $stats = $vehicles->map(function ($vehicle) {
            $contract = $vehicle->activeVehicleContract;
            return [
                'vehicle'      => $vehicle,
                'contract'     => $contract,
                'stats'        => $contract ? $this->contractService->getStats($contract) : null,
                'is_on_pause'  => $vehicle->isOnPause(),
                'active_driver' => $vehicle->activeDriverContract?->driver?->user,
            ];
        });

        return view('pages.client.owner.payments', compact('vehicles', 'stats'));
    }
}
