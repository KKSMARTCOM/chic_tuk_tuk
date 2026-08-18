<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        // Récupérer les véhicules disponibles pour un nouveau contrat
        $availableVehicles = Vehicle::with('owner')
            ->where('is_active', true)
            ->whereDoesntHave('vehicleContracts', fn($q) => $q->where('status', 'active'))
            ->get();

        return view('pages.admin.contracts.owner', compact('contracts', 'availableVehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::with('owner')
            ->where('is_active', true)
            ->whereDoesntHave('vehicleContracts', fn($q) => $q->where('status', 'active'))
            ->get();

        return view('pages.admin.contracts.owner-create', compact('vehicles'));
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

        return view('pages.admin.contracts.owner-show', compact('vehicleContract', 'stats', 'paymentsByMonth'));
    }

    public function edit(VehicleContract $vehicleContract)
    {
        $vehicles = Vehicle::with('owner')->where('is_active', true)->get();
        return view('pages.admin.contracts.owner-edit', compact('vehicleContract', 'vehicles'));
    }

    public function update(Request $request, VehicleContract $vehicleContract)
    {
        $validated = $request->validate([
            'vehicle_id'         => 'nullable|exists:vehicles,id',
            'contract_months'    => 'required|integer|min:1',
            'contract_total_amount' => 'required|numeric|min:1',
            'contract_start_date' => 'required|date',
            'status'             => 'required|in:active,completed,cancelled',
            'notes'              => 'nullable|string|max:1000',
            'unlimited_internet' => 'nullable|numeric|min:0',
            'spotify_premium'    => 'nullable|numeric|min:0',
            'manager_remuneration' => 'nullable|numeric|min:0',
        ], [
            'vehicle_id.exists' => 'Le véhicule sélectionné est invalide.',
            'contract_total_amount.required' => 'Le montant total du contrat est obligatoire.',
            'contract_start_date.required' => 'La date de début est obligatoire.',
            'contract_months.required' => 'La durée du contrat en mois est obligatoire.',
            'status.required' => 'Le statut du contrat est obligatoire.',
            'status.in' => 'Le statut du contrat doit être actif, complété ou annulé.',
            'contract_total_amount.min' => 'Le montant total du contrat doit être supérieur à 0.',
            'contract_months.min' => 'La durée du contrat en mois doit être supérieure à 0.',
            'unlimited_internet.min' => 'Le coût de l\'internet illimité doit être supérieur ou égal à 0.',
            'spotify_premium.min' => 'Le coût de Spotify Premium doit être supérieur ou égal à 0.',
            'manager_remuneration.min' => 'La rémunération du gestionnaire doit être supérieure ou égale à 0.',
        ]);

        $data = [
            'total_amount'        => $validated['contract_total_amount'],
            'start_date'          => $validated['contract_start_date'],
            'end_date'            => $validated['end_date'] ?? null,
            'status'              => $validated['status'],
            'notes'               => $validated['notes'] ?? null,
            'contract_months'     => $validated['contract_months'],
            'unlimited_internet'  => $validated['unlimited_internet'] ?? null,
            'spotify_premium'     => $validated['spotify_premium'] ?? null,
            'manager_remuneration' => $validated['manager_remuneration'] ?? null,
        ];

        if (!empty($validated['vehicle_id'])) {
            $data['vehicle_id'] = $validated['vehicle_id'];
        }

        try {
            $this->contractService->update($vehicleContract, $data);
            return back()->with('success', 'Contrat véhicule mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour contrat véhicule : ' . $e->getMessage());
            return back()->with('error', 'Impossible de mettre à jour ce contrat : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(VehicleContract $vehicleContract)
    {
        try {
            $this->contractService->delete($vehicleContract);
            return back()->with('success', 'Contrat supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du contrat véhicule : ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Impossible de supprimer ce contrat : ' . $e->getMessage());
        }
    }
}
