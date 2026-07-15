<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function getAll(array $filters = [])
    {
        $query = User::with('roles')
            ->whereIn('profil', ['admin', 'client']); // exclure les drivers

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['profil']) && $filters['profil'] !== '') {
            $query->where('profil', $filters['profil']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->latest()->get();
    }

    public function getStats(): array
    {
        $users = User::whereIn('profil', ['admin', 'client']);

        return [
            'total'    => $users->count(),
            'active'   => (clone $users)->where('is_active', true)->count(),
            'inactive' => (clone $users)->where('is_active', false)->count(),
            'admins'   => (clone $users)->where('profil', 'admin')->count(),
            'clients'  => (clone $users)->where('profil', 'client')->count(),
        ];
    }

    public function generatePassword(int $length = 8): string
    {
        // Génère un mot de passe qui respecte les règles de validation : 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $digits    = '0123456789';
        $specials  = '@$!%*#?&';

        $password = [
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specials[random_int(0, strlen($specials) - 1)],
        ];

        $allChars = $uppercase . $lowercase . $digits . $specials;

        for ($i = count($password); $i < $length; $i++) {
            $password[] = $allChars[random_int(0, strlen($allChars) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }

    /*     public function generatePassword(): string
    {
        
        $upper   = strtoupper(Str::random(2));
        $digits  = rand(10, 99);
        $special = Str::random(2, '@$!%*#?&');
        $lower   = Str::random(4);

        return str_shuffle($upper . $digits . $special . $lower);
    } */

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // 1. Créer l'utilisateur
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'] ?? null,
                'phone'     => $data['phone'],
                'password'  => Hash::make($data['password']),
                'profil'    => $data['profil'],
                'adresse'   => $data['adresse'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 2. Assigner le rôle Spatie
            $role = !empty($data['role']) ? $data['role'] : $data['profil'];
            $user->assignRole($role);

            // 3. Si rôle propriétaire → gérer véhicule + contrat
            if ($role === 'proprietaire') {
                $vehicle = null;

                // 3a. Nouveau véhicule à créer
                if (!empty($data['new_vehicle_number'])) {
                    $vehicle = Vehicle::create([
                        'owner_id'       => $user->id,
                        'vehicle_number' => $data['new_vehicle_number'],
                        'vehicle_type'   => $data['new_vehicle_type']  ?? 'tricycle',
                        'color'          => $data['new_vehicle_color'] ?? null,
                        'notes'          => $data['new_vehicle_notes'] ?? null,
                        'is_active'      => true,
                    ]);
                }
                // 3b. Véhicule existant sélectionné → changer le propriétaire
                elseif (!empty($data['vehicle_id'])) {
                    $vehicle = Vehicle::find($data['vehicle_id']);
                    $vehicle?->update(['owner_id' => $user->id]);
                }

                // 4. Créer le contrat proprio-véhicule si montant renseigné
                if ($vehicle && !empty($data['contract_total_amount'])) {
                    VehicleContract::create([
                        'vehicle_id'      => $vehicle->id,
                        'owner_id'        => $user->id,
                        'total_amount'    => $data['contract_total_amount'],
                        'monthly_payment' => $data['contract_monthly_payment'] ?? 0,
                        'start_date'      => $data['contract_start_date']      ?? now(),
                        'end_date'        => $data['contract_end_date']         ?? null,
                        'notes'           => $data['contract_notes']            ?? null,
                        'status'          => 'active',
                    ]);
                }
            }

            return $user;
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Mettre à jour les infos de base
            $user->update([
                'name'      => $data['name'],
                'email'     => $data['email']   ?? $user->email,
                'phone'     => $data['phone'],
                'profil'    => $data['profil'],
                'adresse'   => $data['adresse'] ?? $user->adresse,
                'is_active' => $data['is_active'] ?? $user->is_active,
            ]);

            // 2. Mettre à jour le rôle Spatie
            if (!empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            // 3. Si rôle propriétaire → gérer véhicule + contrat éventuel
            if (($data['role'] ?? '') === 'proprietaire') {
                $vehicle = null;

                // 3a. Nouveau véhicule à créer
                if (!empty($data['new_vehicle_number'])) {
                    $vehicle = Vehicle::create([
                        'owner_id'       => $user->id,
                        'vehicle_number' => $data['new_vehicle_number'],
                        'vehicle_type'   => $data['new_vehicle_type']  ?? 'tricycle',
                        'color'          => $data['new_vehicle_color'] ?? null,
                        'is_active'      => true,
                    ]);
                }
                // 3b. Véhicule existant sélectionné
                elseif (!empty($data['vehicle_id'])) {
                    $vehicle = Vehicle::find($data['vehicle_id']);

                    // ⚠️ Vérifier qu'il n'a pas déjà un autre propriétaire
                    if ($vehicle && $vehicle->owner_id && $vehicle->owner_id !== $user->id) {
                        throw new \Exception(
                            "Le véhicule {$vehicle->vehicle_number} appartient déjà à un autre propriétaire."
                        );
                    }

                    $vehicle?->update(['owner_id' => $user->id]);
                }

                // 4. Créer le contrat si montant renseigné
                if ($vehicle && !empty($data['contract_total_amount'])) {

                    // Vérifier qu'il n'a pas déjà un contrat actif
                    $existingActive = VehicleContract::where('vehicle_id', $vehicle->id)
                        ->where('status', 'active')
                        ->first();

                    if ($existingActive) {
                        throw new \Exception(
                            "Le véhicule {$vehicle->vehicle_number} a déjà un contrat actif. Clôturez-le avant d'en créer un nouveau."
                        );
                    }

                    VehicleContract::create([
                        'vehicle_id'      => $vehicle->id,
                        'owner_id'        => $user->id,
                        'total_amount'    => $data['contract_total_amount'],
                        'monthly_payment' => $data['contract_monthly_payment'] ?? 0,
                        'start_date'      => $data['contract_start_date']      ?? now(),
                        'end_date'        => $data['contract_end_date']         ?? null,
                        'notes'           => $data['contract_notes']            ?? null,
                        'status'          => 'active',
                    ]);
                }
            }

            return $user->refresh();
        });
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);
    }

    public function toggleStatus(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user->refresh();
    }

    public function delete(User $user): void
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === Auth::id()) {
            throw new \Exception('Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();
    }

    public function getAvailableRoles(): \Illuminate\Support\Collection
    {
        // Tous les rôles sauf driver
        return Role::where('name', '!=', 'driver')->get();
    }
}
