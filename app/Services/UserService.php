<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
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

    public function generatePassword(): string
    {
        // Génère un mot de passe qui respecte les règles de validation
        $upper   = strtoupper(Str::random(2));
        $digits  = rand(10, 99);
        $special = Str::random(2, '@$!%*#?&');
        $lower   = Str::random(4);

        return str_shuffle($upper . $digits . $special . $lower);
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
            'profil'   => $data['profil'],
            'adresse'  => $data['adresse'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        // Assigner le rôle Spatie
        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        } else {
            $user->assignRole($data['profil']); // rôle par défaut = profil
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update([
            'name'     => $data['name'],
            'email'    => $data['email'] ?? $user->email,
            'phone'    => $data['phone'],
            'profil'   => $data['profil'],
            'adresse'  => $data['adresse'] ?? $user->adresse,
            'is_active' => $data['is_active'] ?? $user->is_active,
        ]);

        // Mettre à jour le rôle Spatie
        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->refresh();
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
        if ($user->id === auth()->id()) {
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
