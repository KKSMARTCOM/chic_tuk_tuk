<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleContract;
use Illuminate\Support\Facades\DB;

class VehicleContractService
{
    public function create(array $data): VehicleContract
    {
        return VehicleContract::create([
            'vehicle_id'          => $data['vehicle_id'],
            'owner_id'            => $data['owner_id'],
            'total_amount'        => $data['total_amount'],
            'monthly_payment'     => $data['monthly_payment'],
            'start_date'          => $data['start_date'],
            'end_date'            => $data['end_date'] ?? null,
            'status'              => 'active',
            'notes'               => $data['notes'] ?? null,
            'contract_months'     => $data['contract_months'] ?? null,
            'unlimited_internet'  => $data['unlimited_internet'] ?? null,
            'spotify_premium'     => $data['spotify_premium'] ?? null,
            'manager_remuneration' => $data['manager_remuneration'] ?? null,
        ]);
    }

    public function getStats(VehicleContract $contract): array
    {
        $totalPaid     = (float) $contract->payments()->sum('net_amount');
        $totalAmount   = (float) $contract->total_amount;
        $remaining     = $totalAmount - $totalPaid;
        $surplus       = $remaining < 0 ? abs($remaining) : 0;
        $remaining     = max(0, $remaining);

        return [
            'total_amount'       => $totalAmount,
            'total_paid'         => $totalPaid,
            'remaining'          => $remaining,
            'surplus'            => $surplus,
            'progress_percent'   => $totalAmount > 0 ? min(100, round(($totalPaid / $totalAmount) * 100)) : 0,
            'payments_count'     => $contract->payments()->count(),
            'monthly_payment'    => (float) $contract->monthly_payment,
        ];
    }

    public function update(VehicleContract $contract, array $data): VehicleContract
    {
        return DB::transaction(function () use ($contract, $data) {
            if ($contract->vehicle && $contract->vehicle->activeDriverContract()->exists()) {
                throw new \Exception('Impossible de modifier ce contrat : le véhicule possède un agent actif.');
            }

            $updateData = [
                'total_amount'        => $data['total_amount'],
                'start_date'          => $data['start_date'],
                'end_date'            => $data['end_date'] ?? null,
                'status'              => $data['status'] ?? $contract->status,
                'notes'               => $data['notes'] ?? null,
                'contract_months'     => $data['contract_months'] ?? $contract->contract_months,
                'unlimited_internet'  => $data['unlimited_internet'] ?? $contract->unlimited_internet,
                'spotify_premium'     => $data['spotify_premium'] ?? $contract->spotify_premium,
                'manager_remuneration' => $data['manager_remuneration'] ?? $contract->manager_remuneration,
            ];

            if (!empty($data['vehicle_id']) && $data['vehicle_id'] !== $contract->vehicle_id) {
                $newVehicle = Vehicle::findOrFail($data['vehicle_id']);
                if ($newVehicle->activeDriverContract()->exists()) {
                    throw new \Exception('Impossible de changer le véhicule : le véhicule sélectionné possède un agent actif.');
                }

                $updateData['vehicle_id'] = $newVehicle->id;
                $updateData['owner_id']   = $newVehicle->owner_id;
            }

            $contract->update($updateData);

            return $contract->refresh();
        });
    }

    public function delete(VehicleContract $contract): void
    {
        DB::transaction(function () use ($contract) {
            if ($contract->status === 'active') {
                throw new \Exception('Impossible de supprimer un contrat actif.');
            }

            if ($contract->driverContracts()->where('status', 'active')->exists()) {
                throw new \Exception('Impossible de supprimer ce contrat : un agent est encore assigné.');
            }

            if ($contract->vehicle) {
                $contract->vehicle->update(['owner_id' => null]);
            }

            $contract->delete();
        });
    }
}
