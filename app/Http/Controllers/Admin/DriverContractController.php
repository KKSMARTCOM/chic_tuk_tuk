<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverContract;
use App\Models\Vehicle;
use App\Services\DriverContractService;
use App\Services\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DriverContractController extends Controller
{
    public function __construct(
        private DriverContractService $contractService,
    ) {}

    public function index()
    {
        $contracts = DriverContract::with([
            'driver.user',
            'vehicle.owner',
            'vehicleContract',
        ])->latest()->get();

        return view('pages.admin.contracts.driver', compact('contracts'));
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
            $this->contractService->validateVehicleAssignment($vehicle, $driver->id);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation du contrat agent : ' . $e->getMessage(), ['exception' => $e]);
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

    public function update(Request $request, DriverContract $driverContract)
    {
        $validated = $request->validate([
            'vehicle_id'      => 'required|exists:vehicles,id',
            'start_date'      => 'required|date',
            'contract_months' => 'required|integer|in:24,30,36',
        ], [
            'vehicle_id.required'      => 'Le véhicule est obligatoire.',
            'start_date.required'      => 'La date de début est obligatoire.',
            'contract_months.required' => 'La durée du contrat est obligatoire.',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $driver  = $driverContract->driver;

        // Valider uniquement si le véhicule a changé
        // Si c'est le même véhicule → pas besoin de vérifier les conflits
        $vehicleChanged = $driverContract->vehicle_id !== $vehicle->id;

        if ($vehicleChanged) {
            try {
                // On exclut le driver courant pour la règle 1 (véhicule déjà pris)
                // On exclut le contrat courant pour la règle 2 (comptage des agents)
                $this->contractService->validateVehicleAssignment(
                    $vehicle,
                    $driver->id,
                    $driverContract->id  // ← exclure le contrat en cours de modification
                );
            } catch (\Exception $e) {
                Log::error('Erreur lors de la validation du contrat agent : ' . $e->getMessage(), ['exception' => $e]);
                return back()->with('error', $e->getMessage());
            }
        }

        try {
            $this->contractService->update($driverContract, $validated, $vehicle);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du contrat agent : ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Contrat agent mis à jour avec succès.');
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

        return redirect()->back()->with('success', 'Contrat agent terminé. Une pause véhicule a été créée automatiquement.');
    }

    public function destroy(DriverContract $driverContract)
    {
        try {
            $driverContract->delete();
            return redirect()->back()->with('success', 'Contrat supprimé.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du contrat agent : ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', $e->getMessage());
        }
    }
}
