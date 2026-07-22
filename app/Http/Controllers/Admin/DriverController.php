<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DriversExport;
use App\Http\Controllers\Controller;
use App\Imports\DriversImport;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DriverController extends Controller
{
    protected $driverService;
    protected $commissionService;

    public function __construct(DriverService $driverService, CommissionService $commissionService)
    {
        $this->driverService = $driverService;
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'is_active', 'is_available']);
            $drivers = $this->driverService->getAllDrivers($filters);
            $stats = $this->driverService->getDriverStats();

            if ($request->wantsJson()) {
                return response()->json($drivers);
            }

            return view('pages.admin.drivers.index', compact('drivers', 'stats'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l’affichage des agents : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        $owners = User::whereHas('roles', fn($q) => $q->where('name', 'proprietaire'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('pages.admin.drivers.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $mode = $request->input('_owner_mode', 'existing');

        // ── Règles communes ──────────────────────────────────────
        $rules = [
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|unique:users,email',
            'phone'           => 'required|string|unique:users,phone',
            'password'        => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            'adresse'         => 'nullable|string|max:255',
            'license_number'  => 'required|string',
            'agent_code'      => 'nullable|string|max:255',
            'agent_id'        => 'nullable|string|max:255',
            'contract_months' => 'nullable|integer|in:24,30,36',
            'start_date'      => 'nullable|date',
        ];

        // ── Règles spécifiques au mode ───────────────────────────
        if ($mode === 'existing') {
            $rules['owner_id']   = 'required|exists:users,id';
            $rules['vehicle_id'] = 'required|exists:vehicles,id';
        } else {
            $rules['new_owner_name']           = 'required|string|max:255';
            $rules['new_owner_phone']          = 'required|string|unique:users,phone';
            $rules['new_owner_email']          = 'nullable|email|unique:users,email';
            $rules['new_owner_password']       = ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/',];
            $rules['new_vehicle_number']       = 'required|string|unique:vehicles,vehicle_number';
            $rules['new_vehicle_type']         = 'required|in:moto,tricycle,car';
            //$rules['new_vehicle_color']        = 'nullable|string|max:100';
            $rules['contract_total_amount']    = 'nullable|numeric|min:1';
            $rules['contract_monthly_payment'] = 'nullable|numeric|min:0';
            $rules['contract_start_date']      = 'nullable|date';
            $rules['contract_end_date']        = 'nullable|date|after:contract_start_date';
        }

        $messages = [
            'name.required'           => 'Le nom est requis.',
            'email.required'          => 'L\'email est requis.',
            'email.unique'            => 'Cet email est déjà utilisé.',
            'phone.required'          => 'Le téléphone est requis.',
            'phone.unique'            => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min'            => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.regex'          => 'Le mot de passe doit contenir une majuscule, un chiffre et un caractère spécial.',
            'license_number.required' => 'La catégorie de permis est requise.',
            'owner_id.required'       => 'Le propriétaire est requis.',
            'vehicle_id.required'     => 'Le véhicule est requis.',
            'new_owner_name.required' => 'Le nom du propriétaire est requis.',
            'new_owner_phone.required' => 'Le téléphone du propriétaire est requis.',
            'new_owner_phone.unique'  => 'Ce numéro de téléphone est déjà utilisé.',
            'new_owner_email.unique'  => 'Cette adresse e-mail est déjà utilisée.',
            'new_owner_password.min'  => 'Le mot de passe du propriétaire doit contenir au moins 8 caractères.',
            'new_owner_password.regex' => 'Le mot de passe du propriétaire doit contenir une majuscule, un chiffre et un caractère spécial.',
            'new_vehicle_number.required' => 'Le numéro d\'immatriculation est requis.',
            'new_vehicle_number.unique'   => 'Ce numéro de véhicule existe déjà.',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $data = array_merge($validated, ['_owner_mode' => $mode]);

            $this->driverService->createDriver($data);

            return redirect()->route('admin.drivers.index')->with('success', 'Agent créé avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l’agent : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(User $driver)
    {
        try {
            $driverData = $this->driverService->getDriverById($driver->id);

            $bookingStats = $this->driverService->getDriverBookingStats($driver->driver->id);

            $commissionStats = $this->commissionService->getDriverCommissions($driver->driver->id);

            $owners   = User::whereHas('roles', fn($q) => $q->where('name', 'proprietaire'))
                ->orderBy('name')
                ->get(['id', 'name', 'phone']);

            return view('pages.admin.drivers.show', compact('driverData', 'bookingStats', 'commissionStats', 'owners'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l’affichage du profil agent : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(User $driver)
    {
        try {
            $driver->load(['driver.activeDriverContract.vehicle.owner', 'driver.activeDriverContract.vehicleContract']);

            $owners = User::whereHas('roles', fn($q) => $q->where('name', 'proprietaire'))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'phone']);

            return view('pages.admin.drivers.edit', compact('driver', 'owners'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l’affichage du formulaire d’édition de l’agent : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, User $driver)
    {
        $hasActiveContract = $driver->driver?->activeDriverContract !== null;
        $mode = $request->input('_owner_mode', 'existing');

        // ── Règles communes ──────────────────────────────────────
        $rules = [
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|unique:users,email,' . $driver->id,
            'phone'          => 'required|string|unique:users,phone,' . $driver->id,
            'is_active'      => 'nullable|boolean',
            'adresse'        => 'nullable|string|max:255',
            'license_number' => 'required|string',
            'is_available'   => 'nullable|boolean',
            'agent_code'     => 'nullable|string|max:255',
            'agent_id'       => 'nullable|string|max:255',
        ];

        // ── Règles du nouveau contrat (si pas de contrat actif) ─
        if (!$hasActiveContract) {
            if ($mode === 'existing') {
                $rules['owner_id']        = 'required|exists:users,id';
                $rules['vehicle_id']      = 'required|exists:vehicles,id';
                $rules['existing_contract_months'] = 'required|integer|in:24,30,36';
                $rules['existing_start_date']      = 'required|date';
            } else {
                $rules['new_owner_name']           = 'required|string|max:255';
                $rules['new_owner_phone']          = 'required|string|unique:users,phone';
                $rules['new_owner_email']          = 'nullable|email|unique:users,email';
                $rules['new_owner_password']       = ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'];
                $rules['new_vehicle_number']       = 'required|string|unique:vehicles,vehicle_number';
                $rules['new_vehicle_type']         = 'required|in:moto,tricycle,car';
                $rules['new_contract_months']      = 'required|integer|in:24,30,36';
                $rules['new_start_date']               = 'required|date';
                $rules['contract_total_amount']    = 'nullable|numeric|min:1';
                $rules['contract_monthly_payment'] = 'nullable|numeric|min:0';
                $rules['contract_start_date']      = 'nullable|date';
                $rules['contract_end_date']        = 'nullable|date|after:contract_start_date';
            }
        }

        $messages = [
            'phone.unique'               => 'Ce numéro de téléphone est déjà utilisé.',
            'email.unique'               => 'Cet email est déjà utilisé.',
            'license_number.required'    => 'La catégorie de permis est requise.',
            'owner_id.required'          => 'Le propriétaire est requis.',
            'vehicle_id.required'        => 'Le véhicule est requis.',
            'existing_contract_months.required'   => 'La durée du contrat est requise.',
            'new_contract_months.required'      => 'La durée du contrat est requise.',
            'existing_start_date.required'        => 'La date de début est requise.',
            'new_start_date.required'               => 'La date de début est requise.',
            'new_owner_name.required'    => 'Le nom du propriétaire est requis.',
            'new_owner_phone.required'   => 'Le téléphone du propriétaire est requis.',
            'new_owner_phone.unique'     => 'Ce numéro est déjà utilisé.',
            'new_owner_password.required' => 'Le mot de passe du propriétaire est requis.',
            'new_owner_password.min'      => 'Le mot de passe du propriétaire doit contenir au moins 8 caractères.',
            'new_owner_password.regex'    => 'Le mot de passe du propriétaire doit contenir une majuscule, un chiffre et un caractère spécial.',
            'new_vehicle_number.required' => 'Le numéro d\'immatriculation est requis.',
            'new_vehicle_number.unique'  => 'Ce numéro de véhicule existe déjà.',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $data = array_merge($validated, [
                '_owner_mode'       => $mode,
                '_has_active_contract' => $hasActiveContract,
            ]);

            $this->driverService->updateDriver($driver->id, $data);

            return redirect()->route('admin.drivers.show', $driver)->with('success', 'Agent mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l’agent : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(User $driver)
    {
        try {
            $this->driverService->deleteDriver($driver->id);

            return redirect()->route('admin.drivers.index')->with('success', 'Agent supprimé avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l’agent : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleAvailability(Request $request, User $driver)
    {
        $validated = $request->validate([
            "is_available" => "required|boolean",
        ]);

        $driver->driver->update(["is_available" => $validated["is_available"]]);

        return response()->json([
            "success" => true,
            "message" => "Disponibilité mise à jour avec succès"
        ]);
    }

    public function toggleStatus(Request $request, User $driver)
    {
        $validated = $request->validate([
            "is_active" => "required|boolean",
        ]);

        $driver->update(["is_active" => $validated["is_active"]]);

        return response()->json([
            "success" => true,
            "message" => "Statut du compte mis à jour avec succès"
        ]);
    }

    public function updatePassword(Request $request, User $driver)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
        ], [
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (@$!%*#?&).',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $this->driverService->updateDriverPassword($driver->id, $validated['password']);

        return redirect()->back()->with('success', 'Mot de passe mis à jour avec succès');
    }

    // Export Excel
    public function export()
    {
        $filename = 'agents_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new DriversExport(), $filename);
    }

    // Formulaire d'import
    public function importForm()
    {
        return view('pages.admin.drivers.import');
    }

    // Traitement de l'import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5 Mo max
        ], [
            'file.required' => 'Veuillez sélectionner un fichier.',
            'file.mimes'    => 'Le fichier doit être au format Excel (.xlsx, .xls) ou CSV.',
            'file.max'      => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $import = new DriversImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l’import des agents : ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Erreur lors de la lecture du fichier : ' . $e->getMessage());
        }

        $message = "{$import->imported} agent(s) importé(s) avec succès.";
        if ($import->skipped > 0) {
            $message .= " {$import->skipped} ligne(s) ignorée(s).";
        }

        return redirect()
            ->route('admin.drivers.index')
            ->with('success', $message)
            ->with('import_errors', $import->errors);
    }

    // Télécharger le template CSV
    public function downloadTemplate()
    {
        $filename = 'template_agents.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'nom',
            'email',
            'telephone',
            'mot_de_passe',
            'n_permis',
            'n_vehicule',
            'type_vehicule',
            'statut_compte',
            'disponibilite',
            'total_courses'
        ];

        // Ligne d'exemple
        $example = [
            'Jean Dupont',
            'jean@exemple.com',
            '0123456789',
            'MotDePasse@123',
            'PERM-001',
            'VEH-001',
            'tricycle',
            'Actif',
            'Disponible',
            '0'
        ];

        $callback = function () use ($columns, $example) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns, ';');
            fputcsv($file, $example, ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
