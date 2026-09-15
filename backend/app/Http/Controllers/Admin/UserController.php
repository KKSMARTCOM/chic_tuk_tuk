<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'is_active']);
        $users   = $this->userService->getAll($filters);
        $stats   = $this->userService->getStats();
        $roles   = $this->userService->getAvailableRoles();

        return view('pages.admin.users.index', compact('users', 'stats', 'roles'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'nullable|email|unique:users,email,NULL,id,profil,' . $request->profil,
                'phone'    => 'required|string|unique:users,phone,NULL,id,profil,' . $request->profil,
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/',
                ],
                'role'     => 'nullable|exists:roles,name',
                'adresse'  => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required'   => 'Le nom est obligatoire.',
                'email.unique'    => 'Cette adresse email est déjà utilisée.',
                'phone.required'  => 'Le téléphone est obligatoire.',
                'phone.unique'    => 'Ce numéro est déjà utilisé.',
                'password.min'    => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.regex'  => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (@$!%*#?&).',
                'role.exists'     => 'Le rôle sélectionné n\'existe pas.',
            ]);

            $this->userService->create($validated);

            return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur création utilisateur : ' . $e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'nullable|email|unique:users,email,' . $user->id . ',id,profil,' . $request->profil,
                'phone'    => 'required|string|unique:users,phone,' . $user->id . ',id,profil,' . $request->profil,
                'role'     => 'nullable|exists:roles,name',
                'adresse'  => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required'   => 'Le nom est obligatoire.',
                'email.unique'    => 'Cette adresse email est déjà utilisée.',
                'phone.required'  => 'Le téléphone est obligatoire.',
                'phone.unique'    => 'Ce numéro est déjà utilisé.',
                'role.exists'     => 'Le rôle sélectionné n\'existe pas.',
            ]);

            $this->userService->update($user, $validated);

            return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour utilisateur : ' . $e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function updatePassword(Request $request, User $user)
    {
        try {
            $request->validate([
                'password'              => 'required|string|min:8|confirmed|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
                'password_confirmation' => 'required',
            ], [
                'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.regex'     => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (@$!%*#?&).',
                'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            ]);

            $this->userService->updatePassword($user, $request->password);

            return back()->with('success', 'Mot de passe mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour mot de passe : ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            $this->userService->delete($user);
            return back()->with('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression utilisateur : ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(User $user)
    {
        try {
            $this->userService->toggleStatus($user);
            return back()->with('success', 'Statut mis à jour.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour statut : ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    // Retourner les véhicules d'un utilisateur (AJAX depuis le modal)
    public function vehicles(User $user)
    {
        $vehicles = $user->vehicles()
            ->with('activeVehicleContract')
            ->get()
            ->map(fn($v) => [
                'id'                  => $v->id,
                'vehicle_number'      => $v->vehicle_number,
                'vehicle_type'        => $v->vehicle_type,
                'color'               => $v->color,
                'has_active_contract' => $v->activeVehicleContract !== null,
            ]);

        return response()->json($vehicles);
    }

    // Génère un mot de passe via AJAX
    public function generatePassword()
    {
        return response()->json([
            'password' => $this->userService->generatePassword(),
        ]);
    }
}
