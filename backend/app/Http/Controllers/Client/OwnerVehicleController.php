<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleContract;
use App\Services\VehicleContractService;
use App\Services\VehicleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerVehicleController extends Controller
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function index()
    {
        $vehiclesStats = $this->vehicleService->getOwnerVehiclesWithStats(Auth::id());

        return view('pages.client.owner.index', compact('vehiclesStats'));
    }

    public function show(Vehicle $vehicle)
    {
        if ($vehicle->owner_id !== Auth::id()) {
            abort(403, "Ce véhicule ne vous appartient pas.");
        }

        $vehicle->load(['activeVehicleContract', 'activePause', 'activeDriverContract.driver.user']);

        $contract = $vehicle->activeVehicleContract;
        $driverContract = $vehicle->activeDriverContract;

        return view('pages.client.owner.vehicles.show', compact('vehicle', 'contract', 'driverContract'));
    }

    public function leaves(Vehicle $vehicle)
    {
        if ($vehicle->owner_id !== Auth::id()) {
            abort(403, "Ce véhicule ne vous appartient pas.");
        }

        $vehicle->load(['pauses' => fn($q) => $q->orderByDesc('start_date'), 'activeVehicleContract']);
        $contract = $vehicle->activeVehicleContract;

        return view('pages.client.owner.leaves', compact('vehicle', 'contract'));
    }

    public function payments(Vehicle $vehicle)
    {
        if ($vehicle->owner_id !== Auth::id()) {
            abort(403, "Ce véhicule ne vous appartient pas.");
        }

        $contract = $vehicle->activeVehicleContract;
        $monthlyRecap = collect();

        if ($contract) {
            $contract->load('payments');
            $monthlyRecap = $this->buildMonthlyRecap($contract);
        }

        return view('pages.client.owner.payments', compact('vehicle', 'contract', 'monthlyRecap'));
    }

    private function buildMonthlyRecap(VehicleContract $contract)
    {
        $payments = $contract->payments;
        $today = Carbon::today();

        $months = $payments
            ->map(fn($p) => Carbon::parse($p->payment_month)->format('Y-m'))
            ->unique()
            ->sort();

        // S'assurer que le mois courant est toujours inclus, même sans paiement encore généré
        $currentMonthKey = $today->format('Y-m');
        if (!$months->contains($currentMonthKey)) {
            $months = $months->push($currentMonthKey);
        }

        $vehiclePauses = $contract->pauses()->get();
        $driverContractIds = $contract->driverContracts()->pluck('id');
        $leaveRequests = \App\Models\LeaveRequest::whereIn('driver_contract_id', $driverContractIds)
            ->whereIn('status', ['completed', 'ongoing'])
            ->get();

        return $months->map(function ($monthKey) use ($payments, $contract, $today, $vehiclePauses, $leaveRequests) {
            $monthStart = Carbon::parse($monthKey . '-01')->startOfDay();
            $monthEnd = $monthStart->copy()->endOfMonth();

            // Toujours inclus : tous les mois passés + le mois courant (même en cours)
            $isFuture = $monthStart->gt($today);
            if ($isFuture) {
                return null;
            }

            // Borne effective : fin de mois si mois passé, aujourd'hui si mois courant
            $effectiveMonthEnd = min($monthEnd, $today);

            $monthPayments = $payments->filter(
                fn($p) => Carbon::parse($p->payment_month)->format('Y-m') === $monthKey
            );

            $workedDays = $monthPayments->pluck('payment_date')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->unique()
                ->count();

            $agentLeaveDays = $leaveRequests->sum(function ($lr) use ($monthStart, $effectiveMonthEnd) {
                $lrStart = $lr->start_date;
                $lrEnd   = $lr->end_date ?? Carbon::today();

                $overlapStart = $lrStart->greaterThan($monthStart) ? $lrStart : $monthStart;
                $overlapEnd   = $lrEnd->lessThan($effectiveMonthEnd) ? $lrEnd : $effectiveMonthEnd;

                if ($overlapStart->gt($overlapEnd)) {
                    return 0;
                }

                return \App\Models\LeaveRequest::countBusinessDays($overlapStart, $overlapEnd);
            });

            $immobilizationDays = $vehiclePauses->sum(function ($pause) use ($monthStart, $effectiveMonthEnd) {
                $pauseStart = $pause->start_date;
                $pauseEnd   = $pause->end_date ?? Carbon::today();

                $overlapStart = $pauseStart->greaterThan($monthStart) ? $pauseStart : $monthStart;
                $overlapEnd   = $pauseEnd->lessThan($effectiveMonthEnd) ? $pauseEnd : $effectiveMonthEnd;

                if ($overlapStart->gt($overlapEnd)) {
                    return 0;
                }

                return \App\Models\LeaveRequest::countBusinessDays($overlapStart, $overlapEnd);
            });

            return [
                'month'                => $monthStart,
                'is_current'           => $monthStart->isSameMonth($today) && $monthStart->isSameYear($today),
                'validated_amount'     => $monthPayments->where('status', 'completed')->sum('net_amount'),
                'pending_amount'       => $monthPayments->where('status', 'pending')->sum('net_amount'),
                'cancelled_amount'     => $monthPayments->where('status', 'cancelled')->sum('net_amount'),
                'total_charges'        => $contract->total_charges,
                'fixed_amount'         => $monthPayments->where('status', 'completed')->sum('net_amount') - $contract->total_charges,
                'worked_days'          => $workedDays,
                'agent_leave_days'     => $agentLeaveDays,
                'immobilization_days'  => $immobilizationDays,
            ];
        })->filter()->sortByDesc('month')->values();
    }
}
