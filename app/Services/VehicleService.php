<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleContract;
use App\Models\VehiclePause;
use Carbon\Carbon;
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
            $ownerId = $this->resolveOwner($mode, $data);

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
            VehicleContract::create([
                'vehicle_id'      => $vehicle->id,
                'owner_id'        => $ownerId,
                'total_amount'    => $data['contract_total_amount'] ?? 0,
                'monthly_payment' => $data['contract_monthly_payment'] ?? 0,
                'start_date'      => $data['contract_start_date']      ?? now(),
                'end_date'        => $data['contract_end_date']         ?? null,
                'notes'           => $data['contract_notes']            ?? null,
                'status'          => 'active',
            ]);

            return $vehicle;
        });
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($data, $vehicle) {
            $mode              = $data['_owner_mode'] ?? 'existing';
            $hasActiveContract = $data['has_active_contract'] ?? false;
            $existingContractId = $data['existing_contract_id'] ?? null;

            // Résoudre le propriétaire
            if ($mode === 'existing') {
                $newOwnerId = $data['owner_id'];

                // Changement de proprio → clôturer le contrat actif
                if ($vehicle->owner_id !== $newOwnerId && $vehicle->activeVehicleContract) {
                    $this->cancelActiveContract($vehicle->activeVehicleContract, 'Changement de propriétaire.');
                    $hasActiveContract  = false;
                    $existingContractId = null;
                }
            } else {
                $newOwnerId = $this->resolveOwner($mode, $data);

                // Nouveau proprio → toujours clôturer l'éventuel contrat actif
                if ($vehicle->activeVehicleContract) {
                    $this->cancelActiveContract($vehicle->activeVehicleContract, 'Changement de propriétaire.');
                    $hasActiveContract  = false;
                    $existingContractId = null;
                }
            }

            // 2. Mettre à jour le véhicule
            $vehicle->update([
                'owner_id'       => $newOwnerId,
                'vehicle_number' => $data['vehicle_number'],
                'vehicle_type'   => $data['vehicle_type'],
                'notes'          => $data['notes'] ?? $vehicle->notes,
            ]);

            // 3. Gestion du contrat
            if (!empty($data['contract_total_amount'])) {

                if ($hasActiveContract && $existingContractId) {
                    // ── Mettre à jour le contrat existant ──────────────
                    VehicleContract::where('id', $existingContractId)
                        ->where('vehicle_id', $vehicle->id) // sécurité
                        ->update([
                            'total_amount'    => $data['contract_total_amount'],
                            'monthly_payment' => $data['contract_monthly_payment'] ?? 0,
                            'start_date'      => $data['contract_start_date']      ?? now(),
                            'end_date'        => $data['contract_end_date']         ?? null,
                            'notes'           => $data['contract_notes']            ?? null,
                        ]);
                } else {
                    // ── Créer un nouveau contrat ────────────────────────
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
            $startDate = Carbon::parse(min($dates))->startOfDay();
            $endDate   = Carbon::parse(max($dates))->startOfDay();
            $today     = Carbon::today();

            // Déterminer si la pause est passée, présente ou future
            $isPast    = $endDate->lt($today);
            $isCurrent = $startDate->lte($today) && $endDate->gte($today);
            // $isFuture  = $startDate->gt($today);  // implicite

            // ── Clôturer la pause active éventuelle ──────────────
            // Uniquement si la nouvelle pause commence aujourd'hui ou dans le futur
            if (!$isPast && $vehicle->activePause) {
                $vehicle->activePause->update(['end_date' => $startDate->toDateString()]);
            }

            // ── Créer la pause véhicule ───────────────────────────
            $pause = VehiclePause::create([
                'vehicle_id'          => $vehicle->id,
                'vehicle_contract_id' => $vehicle->activeVehicleContract?->id,
                'driver_contract_id'  => $driverContractId,
                'start_date'          => $startDate->toDateString(),
                'end_date'            => $endDate->toDateString(), // toujours renseigné car on connaît les dates
                'reason_type'         => 'agent_leave',
                'reason_notes'        => 'Pause automatique suite à une pause agent — '
                    . ($isPast ? 'passé' : ($isCurrent ? 'en cours' : 'futur'))
                    . '.',
                'is_auto'             => true,
            ]);

            // ── Désactiver le véhicule uniquement si la pause est en cours ──
            // Passée → le véhicule n'est plus en pause, rien à changer
            // Présente → désactiver
            // Future → on ne touche pas à is_active maintenant (sera géré par un job ou à la date)
            if ($isCurrent) {
                $vehicle->update(['is_active' => false]);
            }

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

    // ── Méthodes privées ──────────────────────────────────────────

    private function resolveOwner(string $mode, array $data): string
    {
        if ($mode === 'existing') {
            return $data['owner_id'];
        }

        $ownerRole = Role::firstOrCreate(
            ['name' => 'proprietaire', 'guard_name' => 'web'],
            ['label' => 'Propriétaire']
        );

        $owner = User::create([
            'name'      => $data['new_owner_name'],
            'phone'     => $data['new_owner_phone'],
            'email'     => $data['new_owner_email']     ?? null,
            'password'  => Hash::make($data['new_owner_password']),
            'profil'    => 'client',
            'is_active' => true,
        ]);

        $owner->assignRole($ownerRole);

        return $owner->id;
    }

    private function cancelActiveContract(VehicleContract $contract, string $reason = ''): void
    {
        $contract->update([
            'status'   => 'cancelled',
            'end_date' => now()->toDateString(),
            'notes'    => trim(($contract->notes ? $contract->notes . "\n" : '') . $reason),
        ]);
    }
}
