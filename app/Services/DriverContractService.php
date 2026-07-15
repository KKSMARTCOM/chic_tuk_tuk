<?php

namespace App\Services;

use App\Models\DriverContract;
use App\Models\VehiclePause;

class DriverContractService
{
    public function create(array $data): DriverContract
    {
        // Terminer tout contrat actif sur ce véhicule
        DriverContract::where('vehicle_id', $data['vehicle_id'])
            ->where('status', 'active')
            ->update([
                'status'     => 'ended',
                'end_date'   => now()->toDateString(),
                'end_reason' => 'new_contract',
            ]);

        return DriverContract::create([
            'driver_id'           => $data['driver_id'],
            'vehicle_id'          => $data['vehicle_id'],
            'vehicle_contract_id' => $data['vehicle_contract_id'],
            'start_date'          => $data['start_date'],
            'end_date'            => null,
            'contract_months'     => $data['contract_months'],
            'status'              => 'active',
        ]);
    }

    public function end(DriverContract $contract, array $data): DriverContract
    {
        $contract->update([
            'status'     => 'ended',
            'end_date'   => $data['end_date'] ?? now()->toDateString(),
            'end_reason' => $data['end_reason'],
            'end_notes'  => $data['end_notes'] ?? null,
        ]);

        // Créer une pause véhicule pour changement d'agent
        VehiclePause::create([
            'vehicle_id'          => $contract->vehicle_id,
            'vehicle_contract_id' => $contract->vehicle_contract_id,
            'driver_contract_id'  => $contract->id,
            'start_date'          => $data['end_date'] ?? now()->toDateString(),
            'end_date'            => null, // sera fermée à la création du prochain contrat agent
            'reason_type'         => 'agent_change',
            'reason_notes'        => $data['end_reason'] . ($data['end_notes'] ? ' — ' . $data['end_notes'] : ''),
            'is_auto'             => true,
        ]);

        return $contract->refresh();
    }

    public function getStats(DriverContract $contract): array
    {
        $usedDays    = $contract->used_leave_days;
        $accruedDays = $contract->accrued_leave_days;
        $available   = $accruedDays - $usedDays;
        $surplus     = $available < 0 ? abs($available) : 0;

        return [
            'accrued_leave_days'  => $accruedDays,
            'used_leave_days'     => $usedDays,
            'available_leave_days' => max(0, $available),
            'surplus_leave_days'  => $surplus,
            'months_elapsed'      => $contract->months_elapsed,
            'total_paid'          => $contract->total_paid,
        ];
    }
}
