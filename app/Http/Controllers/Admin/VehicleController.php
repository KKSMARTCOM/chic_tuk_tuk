<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public function __construct(private VehicleService $vehicleService) {}

    public function index(Request $request)
    {
        $filters  = $request->only(['search', 'is_active', 'owner_id']);
        $vehicles = $this->vehicleService->getAll($filters);
        $owners   = User::whereHas('roles', fn($q) => $q->where('name', 'proprietaire'))->get();

        return view('pages.admin.vehicles.index', compact('vehicles', 'owners'));
    }

    public function create()
    {
        $owners = User::whereHas('roles', fn($q) => $q->where('name', 'proprietaire'))->get();
        return view('pages.admin.vehicles.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_id'       => 'required|exists:users,id',
            'vehicle_number' => 'required|string|unique:vehicles,vehicle_number',
            'vehicle_type'   => 'required|string|in:moto,tricycle,car',
            'notes'          => 'nullable|string|max:500',
        ], [
            'owner_id.required'       => 'Le propriétaire est obligatoire.',
            'vehicle_number.required' => 'Le numéro de véhicule est obligatoire.',
            'vehicle_number.unique'   => 'Ce numéro de véhicule existe déjà.',
            'vehicle_type.required'   => 'Le type de véhicule est obligatoire.',
        ]);

        $this->vehicleService->create($validated);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Véhicule créé avec succès.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'owner',
            'activeVehicleContract',
            'activeDriverContract.driver.user',
            'driverContracts.driver.user',
            'pauses.driverContract.driver.user',
            'activePause',
        ]);

        return view('pages.admin.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $owners = User::whereHas('roles', fn($q) => $q->where('name', 'proprietaire'))->get();
        return view('pages.admin.vehicles.edit', compact('vehicle', 'owners'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'owner_id'       => 'required|exists:users,id',
            'vehicle_number' => 'required|string|unique:vehicles,vehicle_number,' . $vehicle->id,
            'vehicle_type'   => 'required|string|in:moto,tricycle,car',
            'notes'          => 'nullable|string|max:500',
        ]);

        $this->vehicleService->update($vehicle, $validated);

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Véhicule mis à jour avec succès.');
    }

    public function destroy(Vehicle $vehicle)
    {
        try {
            $vehicle->delete();
            return redirect()->route('admin.vehicles.index')
                ->with('success', 'Véhicule supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Impossible de supprimer ce véhicule : ' . $e->getMessage());
        }
    }

    public function toggleStatus(Vehicle $vehicle)
    {
        $this->vehicleService->toggleStatus($vehicle);
        return back()->with('success', 'Statut du véhicule mis à jour.');
    }

    // AJAX — véhicules d'un propriétaire
    public function byOwner(User $owner)
    {
        $vehicles = $owner->vehicles()
            ->select('id', 'vehicle_number', 'vehicle_type', 'is_active')
            ->get();

        return response()->json($vehicles);
    }

    // Détacher un véhicule de son propriétaire
    public function detachOwner(Vehicle $vehicle)
    {
        DB::transaction(function () use ($vehicle) {

            // Clôturer le contrat actif si existant
            $active = $vehicle->activeVehicleContract;
            if ($active) {
                $active->update([
                    'status'   => 'cancelled',
                    'end_date' => now()->toDateString(),
                    'notes'    => ($active->notes ? $active->notes . "\n" : '')
                        . 'Contrat clôturé suite au détachement du propriétaire.',
                ]);
            }

            // Retirer le propriétaire
            $vehicle->update(['owner_id' => null]);
        });

        return response()->json([
            'success' => true,
            'message' => "Véhicule {$vehicle->vehicle_number} détaché avec succès.",
            'vehicle' => $vehicle->only('id', 'vehicle_number', 'vehicle_type', 'color'),
        ]);
    }
}
