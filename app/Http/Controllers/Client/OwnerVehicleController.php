<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\VehicleContractService;
use Illuminate\Http\Request;

class OwnerVehicleController extends Controller
{
    public function __construct(private VehicleContractService $contractService) {}

    public function index()
    {
        $vehicles = auth()->user()->vehicles()
            ->with(['activeVehicleContract', 'activeDriverContract.driver.user', 'activePause'])
            ->get();

        return view('pages.owner.vehicles.index', compact('vehicles'));
    }

    public function show(Vehicle $vehicle)
    {
        abort_if($vehicle->owner_id !== auth()->id(), 403);

        $vehicle->load([
            'activeVehicleContract',
            'driverContracts.driver.user',
            'pauses.driverContract.driver.user',
            'activePause',
        ]);

        $contract = $vehicle->activeVehicleContract;
        $stats    = $contract ? $this->contractService->getStats($contract) : null;

        return view('pages.owner.vehicles.show', compact('vehicle', 'stats'));
    }

    public function payments(Vehicle $vehicle)
    {
        abort_if($vehicle->owner_id !== auth()->id(), 403);

        $contract = $vehicle->activeVehicleContract;

        $payments = $contract
            ? $contract->payments()
            ->with('driverContract.driver.user')
            ->orderBy('payment_date', 'desc')
            ->get()
            : collect();

        $paymentsByMonth = $contract
            ? $contract->payments()
            ->selectRaw("DATE_TRUNC('month', payment_date) as month, SUM(amount) as total")
            ->groupByRaw("DATE_TRUNC('month', payment_date)")
            ->orderByRaw("DATE_TRUNC('month', payment_date) DESC")
            ->get()
            : collect();

        $stats = $contract ? $this->contractService->getStats($contract) : null;

        return view('pages.owner.vehicles.payments', compact(
            'vehicle',
            'contract',
            'payments',
            'paymentsByMonth',
            'stats'
        ));
    }

    public function pauses(Vehicle $vehicle)
    {
        abort_if($vehicle->owner_id !== auth()->id(), 403);

        $pauses = $vehicle->pauses()
            ->with('driverContract.driver.user')
            ->orderBy('start_date', 'desc')
            ->get();

        $totalPauseDays = $pauses->whereNotNull('end_date')
            ->sum(fn($p) => $p->start_date->diffInDays($p->end_date));

        $activePause = $vehicle->activePause;

        return view('pages.owner.vehicles.pauses', compact(
            'vehicle',
            'pauses',
            'totalPauseDays',
            'activePause'
        ));
    }
}
