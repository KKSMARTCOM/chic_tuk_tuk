<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleContract;
use App\Services\VehicleContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleContractController extends Controller
{
    public function __construct(private VehicleContractService $contractService) {}

    public function index()
    {
        $contracts = VehicleContract::with(['vehicle', 'owner', 'driverContracts.driver.user'])
            ->latest()
            ->get();

        foreach ($contracts as $contract) {
            $contract->stats = $this->contractService->getStats($contract);
        }

        return view('pages.admin.contracts.owner', compact('contracts'));
    }

    public function create()
    {
        $vehicles = Vehicle::with('owner')
            ->where('is_active', true)
            ->whereDoesntHave('vehicleContracts', fn($q) => $q->where('status', 'active'))
            ->get();

        return view('pages.admin.vehicle-contracts.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'      => 'required|exists:vehicles,id',
            'total_amount'    => 'required|numeric|min:1',
            'monthly_payment' => 'required|numeric|min:1',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after:start_date',
            'notes'           => 'nullable|string|max:1000',
        ], [
            'vehicle_id.required'      => 'Le véhicule est obligatoire.',
            'total_amount.required'    => 'Le montant total est obligatoire.',
            'monthly_payment.required' => 'La mensualité est obligatoire.',
            'start_date.required'      => 'La date de début est obligatoire.',
        ]);

        // Récupérer le owner_id depuis le véhicule
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $validated['owner_id'] = $vehicle->owner_id;

        $this->contractService->create($validated);

        return redirect()->route('admin.vehicle-contracts.index')->with('success', 'Contrat véhicule créé avec succès.');
    }

    public function show(VehicleContract $vehicleContract)
    {
        $vehicleContract->load([
            'vehicle.owner',
            'driverContracts.driver.user',
            'payments.driverContract.driver.user',
            'pauses.driverContract.driver.user',
        ]);

        $stats = $this->contractService->getStats($vehicleContract);

        // Paiements par mois
        $paymentsByMonth = $vehicleContract->payments()
            ->selectRaw("DATE_TRUNC('month', payment_date) as month, SUM(amount) as total")
            ->groupByRaw("DATE_TRUNC('month', payment_date)")
            ->orderByRaw("DATE_TRUNC('month', payment_date) DESC")
            ->get();

        return view('pages.admin.vehicle-contracts.show', compact(
            'vehicleContract',
            'stats',
            'paymentsByMonth'
        ));
    }

    public function edit(VehicleContract $vehicleContract)
    {
        $vehicles = Vehicle::with('owner')->where('is_active', true)->get();
        return view('pages.admin.vehicle-contracts.edit', compact('vehicleContract', 'vehicles'));
    }

    public function update(Request $request, VehicleContract $vehicleContract)
    {
        $validated = $request->validate([
            'total_amount'    => 'required|numeric|min:1',
            'monthly_payment' => 'required|numeric|min:1',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after:start_date',
            'status'          => 'required|in:active,completed,cancelled',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $vehicleContract->update($validated);

        return redirect()->route('admin.vehicle-contracts.show', $vehicleContract)->with('success', 'Contrat mis à jour avec succès.');
    }

    public function destroy(VehicleContract $vehicleContract)
    {
        try {
            $vehicleContract->delete();
            return redirect()->route('admin.vehicle-contracts.index')->with('success', 'Contrat supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du contrat véhicule : ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Impossible de supprimer ce contrat : ' . $e->getMessage());
        }
    }
}
