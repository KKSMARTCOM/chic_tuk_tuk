<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleContract;
use App\Models\VehiclePause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        return DB::transaction(function () use ($data) {
            $mode = $data['_owner_mode'] ?? 'existing';

            // 1. Résoudre le propriétaire
            if ($mode === 'existing') {
                $ownerId = $data['owner_id'];
            } else {
                $ownerRole = Role::firstOrCreate(
                    ['name' => 'proprietaire', 'guard_name' => 'web'],
                    ['label' => 'Propriétaire']
                );
                $owner = User::create([
                    'name'      => $data['new_owner_name'],
                    'phone'     => $data['new_owner_phone'],
                    'email'     => $data['new_owner_email'] ?? null,
                    'password'  => Hash::make($data['new_owner_password']),
                    'profil'    => 'client',
                    'is_active' => true,
                ]);
                $owner->assignRole($ownerRole);
                $ownerId = $owner->id;
            }

            // 2. Créer le véhicule
            $vehicle = Vehicle::create([
                'owner_id'       => $ownerId,
                'vehicle_number' => $data['vehicle_number'],
                'vehicle_type'   => $data['vehicle_type'],
                'color'          => $data['color']  ?? null,
                'notes'          => $data['notes']  ?? null,
                'is_active'      => true,
            ]);

            // 3. Créer le contrat si montant renseigné
            if (!empty($data['contract_total_amount'])) {
                VehicleContract::create([
                    'vehicle_id'      => $vehicle->id,
                    'owner_id'        => $ownerId,
                    'total_amount'    => $data['contract_total_amount'],
                    'monthly_payment' => $data['contract_monthly_payment'] ?? 0,
                    'start_date'      => $data['contract_start_date']      ?? now(),
                    'end_date'        => $data['contract_end_date']         ?? null,
                    'notes'           => $data['contract_notes']            ?? null,
                    'status'          => 'active',
                ]);
            }

            return $vehicle;
        });
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($data, $vehicle) {

            // Résoudre le propriétaire
            if ($data['_owner_mode'] === 'existing') {
                $newOwnerId = $data['owner_id'];

                // Si changement de proprio et véhicule a un contrat actif → clôturer
                if ($vehicle->owner_id !== $newOwnerId && $vehicle->activeVehicleContract) {
                    $vehicle->activeVehicleContract->update([
                        'status'   => 'cancelled',
                        'end_date' => now()->toDateString(),
                        'notes'    => ($vehicle->activeVehicleContract->notes ? $vehicle->activeVehicleContract->notes . "\n" : '')
                            . 'Contrat clôturé — changement de propriétaire.',
                    ]);
                }
            } else {
                $ownerRole = Role::firstOrCreate(
                    ['name' => 'proprietaire', 'guard_name' => 'web'],
                    ['label' => 'Propriétaire']
                );
                $owner = User::create([
                    'name'      => $data['new_owner_name'],
                    'phone'     => $data['new_owner_phone'],
                    'email'     => $data['new_owner_email'] ?? null,
                    'password'  => Hash::make($data['new_owner_password']),
                    'profil'    => 'client',
                    'is_active' => true,
                ]);
                $owner->assignRole($ownerRole);
                $newOwnerId = $owner->id;

                // Clôturer contrat actif si changement de proprio
                if ($vehicle->activeVehicleContract) {
                    $vehicle->activeVehicleContract->update([
                        'status'   => 'cancelled',
                        'end_date' => now()->toDateString(),
                        'notes'    => ($vehicle->activeVehicleContract->notes ? $vehicle->activeVehicleContract->notes . "\n" : '')
                            . 'Contrat clôturé — changement de propriétaire.',
                    ]);
                }
            }

            // Mettre à jour le véhicule
            $vehicle->update([
                'owner_id'       => $newOwnerId,
                'vehicle_number' => $data['vehicle_number'],
                'vehicle_type'   => $data['vehicle_type'],
                'color'          => $data['color'] ?? $vehicle->color,
                'notes'          => $data['notes'] ?? $vehicle->notes,
            ]);

            // Créer le contrat si montant renseigné et pas de contrat actif
            if (!$data['has_active_contract'] && !empty($data['contract_total_amount'])) {
                VehicleContract::create([
                    'vehicle_id'      => $vehicle->id,
                    'owner_id'        => $newOwnerId,
                    'total_amount'    => $data['contract_total_amount'],
                    'monthly_payment' => $data['contract_monthly_payment'] ?? 0,
                    'start_date'      => $data['contract_start_date']      ?? now(),
                    'end_date'        => $data['contract_end_date']         ?? null,
                    'notes'           => $data['contract_notes']            ?? null,
                    'status'          => 'active',
                ]);
            }

            return $vehicle->refresh();
        });
    }

    public function toggleStatus(Vehicle $vehicle): Vehicle
    {
        $vehicle->update(['is_active' => !$vehicle->is_active]);
        return $vehicle->refresh();
    }

    // Mettre le véhicule en pause manuellement
    public function pauseVehicle(Vehicle $vehicle, array $data): VehiclePause
    {
        return DB::transaction(function () use ($vehicle, $data) {
            // Clôturer la pause active si existante
            if ($vehicle->activePause) {
                $vehicle->activePause->update(['end_date' => $data['start_date'] ?? now()->toDateString()]);
            }

            // Créer une nouvelle pause
            $pause = VehiclePause::create([
                'vehicle_id'          => $vehicle->id,
                'vehicle_contract_id' => $vehicle->activeVehicleContract?->id,
                'driver_contract_id'  => $data['driver_contract_id'] ?? null,
                'start_date'          => $data['start_date'] ?? now()->toDateString(),
                'end_date'            => $data['end_date'] ?? null,
                'reason_type'         => $data['reason_type'] ?? 'manual',
                'reason_notes'        => $data['reason_notes'] ?? null,
                'is_auto'             => false,
            ]);

            $vehicle->update(['is_active' => false]);

            return $pause;
        });
    }

    // Terminer une pause véhicule
    public function endPause(VehiclePause $pause, ?string $endDate = null): VehiclePause
    {
        return DB::transaction(function () use ($pause, $endDate) {
            $pause->update(['end_date' => $endDate ?? now()->toDateString()]);

            // Réactiver le véhicule si pas de pause active
            if (!$pause->vehicle->activePause) {
                $pause->vehicle->update(['is_active' => true]);
            }

            return $pause->refresh();
        });
    }

    // Créer automatiquement une pause suite à un congé agent
    public function createAutoAgentPause(string $vehicleId, string $driverContractId, array $dates): VehiclePause
    {
        return DB::transaction(function () use ($vehicleId, $driverContractId, $dates) {
            $vehicle = Vehicle::findOrFail($vehicleId);

            // Clôturer la pause active si existante
            if ($vehicle->activePause) {
                $vehicle->activePause->update(['end_date' => $dates['start_date']]);
            }

            // Créer une nouvelle pause automatique
            $pause = VehiclePause::create([
                'vehicle_id'          => $vehicle->id,
                'vehicle_contract_id' => $vehicle->activeVehicleContract?->id,
                'driver_contract_id'  => $driverContractId,
                'start_date'          => $dates['start_date'],
                'end_date'            => $dates['end_date'] ?? null,
                'reason_type'         => 'agent_leave',
                'reason_notes'        => 'Pause automatique suite à un congé agent.',
                'is_auto'             => true,
            ]);

            $vehicle->update(['is_active' => false]);

            return $pause;
        });
    }

    public function removeOwner(Vehicle $vehicle): Vehicle
    {
        return DB::transaction(function () use ($vehicle) {

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

            return $vehicle->refresh();
        });
    }

    // Récupère les véhicules d'un propriétaire avec statistiques (contrat, paiements par mois, pauses)
    public function getOwnerVehiclesWithStats(string $ownerId)
    {
        $vehicles = Vehicle::where('owner_id', $ownerId)
            ->with(['activeVehicleContract.payments', 'activePause', 'pauses'])
            ->latest()
            ->get();

        return $vehicles->map(function (Vehicle $vehicle) {
            $contract = $vehicle->activeVehicleContract;
            $payments = $contract ? $contract->payments : collect();

            $paymentsByMonth = $payments->groupBy(function ($p) {
                return \Carbon\Carbon::parse($p->payment_month)->format('Y-m');
            })->map(function ($group) {
                return $group->sum('amount');
            });

            return (object) [
                'vehicle' => $vehicle,
                'contract' => $contract,
                'total_paid' => $contract?->total_paid ?? 0,
                'remaining' => $contract?->remaining_amount ?? 0,
                'surplus' => $contract?->surplus ?? 0,
                'progress' => $contract?->progress_percentage ?? 0,
                'payments_by_month' => $paymentsByMonth,
                'active_pause' => $vehicle->activePause,
                'pauses' => $vehicle->pauses,
            ];
        });
    }
}
