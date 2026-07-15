<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleContract;
use App\Models\VehiclePause;

class VehicleService
{
    public function getAll(array $filters = [])
    {
        $query = Vehicle::with(['owner', 'activeVehicleContract', 'activeDriverContract.driver.user', 'activePause']);

        if (!empty($filters['search'])) {
            $query->where('vehicle_number', 'LIKE', "%{$filters['search']}%");
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        return $query->latest()->get();
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create([
            'owner_id'       => $data['owner_id'],
            'vehicle_number' => $data['vehicle_number'],
            'vehicle_type'   => $data['vehicle_type'] ?? 'tricycle',
            'is_active'      => true,
            'notes'          => $data['notes'] ?? null,
        ]);
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update([
            'owner_id'       => $data['owner_id']       ?? $vehicle->owner_id,
            'vehicle_number' => $data['vehicle_number'] ?? $vehicle->vehicle_number,
            'vehicle_type'   => $data['vehicle_type']   ?? $vehicle->vehicle_type,
            'notes'          => $data['notes']           ?? $vehicle->notes,
        ]);

        return $vehicle->refresh();
    }

    public function toggleStatus(Vehicle $vehicle): Vehicle
    {
        $vehicle->update(['is_active' => !$vehicle->is_active]);
        return $vehicle->refresh();
    }

    // Mettre le véhicule en pause manuellement
    public function pauseVehicle(Vehicle $vehicle, array $data): VehiclePause
    {
        $activeContract = $vehicle->activeVehicleContract;
        $activeDriverContract = $vehicle->activeDriverContract;

        return VehiclePause::create([
            'vehicle_id'          => $vehicle->id,
            'vehicle_contract_id' => $activeContract?->id,
            'driver_contract_id'  => $activeDriverContract?->id,
            'start_date'          => $data['start_date'] ?? now(),
            'end_date'            => $data['end_date'] ?? null,
            'reason_type'         => $data['reason_type'],
            'reason_notes'        => $data['reason_notes'] ?? null,
            'is_auto'             => false,
        ]);
    }

    // Terminer une pause véhicule
    public function endPause(VehiclePause $pause, ?string $endDate = null): VehiclePause
    {
        $pause->update(['end_date' => $endDate ?? now()->toDateString()]);
        return $pause->refresh();
    }

    // Créer automatiquement une pause suite à un congé agent
    public function createAutoAgentPause(string $vehicleId, string $driverContractId, array $dates): void
    {
        $vehicle = Vehicle::find($vehicleId);
        $activeContract = $vehicle?->activeVehicleContract;

        VehiclePause::create([
            'vehicle_id'          => $vehicleId,
            'vehicle_contract_id' => $activeContract?->id,
            'driver_contract_id'  => $driverContractId,
            'start_date'          => min($dates),
            'end_date'            => max($dates),
            'reason_type'         => 'agent_leave',
            'reason_notes'        => 'Congé agent — dates : ' . implode(', ', $dates),
            'is_auto'             => true,
        ]);
    }
}
