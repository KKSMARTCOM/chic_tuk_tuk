<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'profil', 'is_active']);
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
                'phone'    => 'required|string|unique:users,phone',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/',
                ],
                'profil'   => 'required|in:admin,client',
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
                'profil.in'       => 'Le profil doit être admin ou client.',
            ]);

            $this->userService->create($validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur créé avec succès.');
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
                'phone'    => 'required|string|unique:users,phone,' . $user->id,
                'profil'   => 'required|in:admin,client',
                'role'     => 'nullable|exists:roles,name',
                'adresse'  => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);

            $this->userService->update($user, $validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
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

            return redirect()->route('admin.users.index')
                ->with('success', 'Mot de passe mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            $this->userService->delete($user);
            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(User $user)
    {
        try {
            $this->userService->toggleStatus($user);
            return back()->with('success', 'Statut mis à jour.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // Génère un mot de passe via AJAX
    public function generatePassword()
    {
        return response()->json([
            'password' => $this->userService->generatePassword(),
        ]);
    }
}
