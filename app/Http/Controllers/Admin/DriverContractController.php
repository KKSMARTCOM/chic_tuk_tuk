<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverContract;
use App\Models\Vehicle;
use App\Services\DriverContractService;
use App\Services\DriverService;
use Illuminate\Http\Request;

class DriverContractController extends Controller
{
    public function __construct(private DriverContractService $contractService, private DriverService $driverService) {}

    public function index()
    {
        $contracts = DriverContract::with([
            'driver.user',
            'vehicle.owner',
            'vehicleContract',
        ])->latest()->get();

        return view('pages.admin.driver-contracts.index', compact('contracts'));
    }

    public function create()
    {
        $drivers = Driver::with('user')
            ->whereDoesntHave('driverContracts', fn($q) => $q->where('status', 'active'))
            ->get();

        $vehicles = Vehicle::with(['owner', 'activeVehicleContract'])
            ->where('is_active', true)
            ->whereHas('vehicleContracts', fn($q) => $q->where('status', 'active'))
            ->get();

        return view('pages.admin.driver-contracts.create', compact('drivers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id'       => 'required|exists:drivers,id',
            'vehicle_id'      => 'required|exists:vehicles,id',
            'start_date'      => 'required|date',
            'contract_months' => 'required|integer|in:24,30,36',
        ], [
            'driver_id.required'       => 'L\'agent est obligatoire.',
            'vehicle_id.required'      => 'Le véhicule est obligatoire.',
            'start_date.required'      => 'La date de début est obligatoire.',
            'contract_months.required' => 'La durée du contrat est obligatoire.',
        ]);

        // Récupérer le contrat véhicule actif
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $driver  = Driver::findOrFail($validated['driver_id']);

        // ✅ Mêmes règles métier
        try {
            $this->driverService->validateVehicleAssignment($vehicle, $driver->id);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $vehicleContract = $vehicle->activeVehicleContract;

        if (!$vehicleContract) {
            return back()->with('error', 'Ce véhicule n\'a pas de contrat actif.');
        }

        $validated['vehicle_contract_id'] = $vehicleContract->id;

        // Fermer la pause véhicule si elle existe (changement d'agent)
        $vehicle->activePause?->update(['end_date' => $validated['start_date']]);

        $this->contractService->create($validated);

        return redirect()->route('admin.driver-contracts.index')
            ->with('success', 'Contrat agent créé avec succès.');
    }

    public function show(DriverContract $driverContract)
    {
        $driverContract->load([
            'driver.user',
            'vehicle.owner',
            'vehicleContract',
            'leaveRequests',
            'payments',
            'vehiclePauses',
        ]);

        $stats = $this->contractService->getStats($driverContract);

        return view('pages.admin.driver-contracts.show', compact('driverContract', 'stats'));
    }

    // Terminer un contrat agent
    public function end(Request $request, DriverContract $driverContract)
    {
        $validated = $request->validate([
            'end_date'   => 'required|date',
            'end_reason' => 'required|string|in:demission,abandon,fin_contrat,autre',
            'end_notes'  => 'nullable|string|max:500',
        ], [
            'end_date.required'   => 'La date de fin est obligatoire.',
            'end_reason.required' => 'La raison est obligatoire.',
        ]);

        $this->contractService->end($driverContract, $validated);

        return redirect()->route('admin.driver-contracts.show', $driverContract)
            ->with('success', 'Contrat agent terminé. Une pause véhicule a été créée automatiquement.');
    }

    public function destroy(DriverContract $driverContract)
    {
        try {
            $driverContract->delete();
            return redirect()->route('admin.driver-contracts.index')
                ->with('success', 'Contrat supprimé.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
