<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
