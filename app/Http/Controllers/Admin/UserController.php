<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
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

        // Véhicules sans propriétaire ou disponibles pour réassignation
        $availableVehicles = Vehicle::where('is_active', true)
            ->whereDoesntHave('activeVehicleContract')
            ->get(['id', 'vehicle_number', 'vehicle_type']);

        return view('pages.admin.users.index', compact('users', 'stats', 'roles', 'availableVehicles'));
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
                'profil'   => 'required|in:admin,client',
                'role'     => 'nullable|exists:roles,name',
                'adresse'  => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',

                // Véhicule existant (optionnel)
                'vehicle_id' => 'nullable|exists:vehicles,id',

                // Nouveau véhicule (optionnel)
                'new_vehicle_number' => [
                    'nullable',
                    'string',
                    // Obligatoire si on n'a pas sélectionné un véhicule existant
                    // ET que le rôle est propriétaire
                    function ($attr, $value, $fail) use ($request) {
                        if (
                            $request->role === 'proprietaire' &&
                            empty($request->vehicle_id) &&
                            empty($value)
                        ) {
                            // Pas obligatoire — le proprio peut n'avoir aucun véhicule pour l'instant
                        }
                    },
                    'unique:vehicles,vehicle_number',
                ],
                'new_vehicle_type'  => 'nullable|in:moto,tricycle,car',
                'new_vehicle_notes' => 'nullable|string|max:500',

                // Contrat (optionnel — seulement si montant renseigné)
                'contract_total_amount'    => 'nullable|numeric|min:1',
                'contract_monthly_payment' => 'nullable|numeric|min:0',
                'contract_start_date'      => 'nullable|date',
                'contract_end_date'        => 'nullable|date|after:contract_start_date',
                'contract_notes'           => 'nullable|string|max:1000',
            ], [
                'name.required'   => 'Le nom est obligatoire.',
                'email.unique'    => 'Cette adresse email est déjà utilisée.',
                'phone.required'  => 'Le téléphone est obligatoire.',
                'phone.unique'    => 'Ce numéro est déjà utilisé.',
                'password.min'    => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.regex'  => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (@$!%*#?&).',
                'profil.in'       => 'Le profil doit être admin ou client.',

                'vehicle_id.exists' => 'Le véhicule sélectionné n\'existe pas.',
                'new_vehicle_number.unique' => 'Ce numéro de véhicule est déjà utilisé.',
                'new_vehicle_type.in' => 'Le type de véhicule doit être moto, tricycle ou car.',

                'contract_total_amount.numeric' => 'Le montant total doit être un nombre.',
                'contract_monthly_payment.numeric' => 'Le paiement mensuel doit être un nombre.',
                'contract_start_date.date' => 'La date de début du contrat doit être une date valide.',
                'contract_end_date.date' => 'La date de fin du contrat doit être une date valide.',
                'contract_end_date.after' => 'La date de fin du contrat doit être après la date de début du contrat.',
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
                'phone'    => 'required|string|unique:users,phone,' . $user->id . ',id,profil,' . $request->profil,
                'profil'   => 'required|in:admin,client',
                'role'     => 'nullable|exists:roles,name',
                'adresse'  => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',

                // Véhicule existant
                'vehicle_id' => 'nullable|exists:vehicles,id',

                // Nouveau véhicule
                'new_vehicle_number' => 'nullable|string|unique:vehicles,vehicle_number',
                'new_vehicle_type'   => 'nullable|in:moto,tricycle,car',
                'new_vehicle_color'  => 'nullable|string|max:100',

                // Contrat
                'contract_total_amount'    => 'nullable|numeric|min:1',
                'contract_monthly_payment' => 'nullable|numeric|min:0',
                'contract_start_date'      => 'nullable|date',
                'contract_end_date'        => 'nullable|date|after:contract_start_date',
                'contract_notes'           => 'nullable|string|max:1000',
            ], [
                'name.required'   => 'Le nom est obligatoire.',
                'email.unique'    => 'Cette adresse email est déjà utilisée.',
                'phone.required'  => 'Le téléphone est obligatoire.',
                'phone.unique'    => 'Ce numéro est déjà utilisé.',
                'profil.in'       => 'Le profil doit être admin ou client.',

                'vehicle_id.exists' => 'Le véhicule sélectionné n\'existe pas.',
                'new_vehicle_number.unique' => 'Ce numéro de véhicule est déjà utilisé.',
                'new_vehicle_type.in' => 'Le type de véhicule doit être moto, tricycle ou car.',

                'contract_total_amount.numeric' => 'Le montant total doit être un nombre.',
                'contract_monthly_payment.numeric' => 'Le paiement mensuel doit être un nombre.',
                'contract_start_date.date' => 'La date de début du contrat doit être une date valide.',
                'contract_end_date.date' => 'La date de fin du contrat doit être une date valide.',
                'contract_end_date.after' => 'La date de fin du contrat doit être après la date de début du contrat.',
            ]);

            $this->userService->update($user, $validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur mis à jour avec succès.');
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

            return redirect()->route('admin.users.index')
                ->with('success', 'Mot de passe mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour mot de passe : ' . $e->getMessage());
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
