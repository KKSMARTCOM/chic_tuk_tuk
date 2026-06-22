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
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        return view('pages.admin.drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email,NULL,id,profil,driver',
                'phone' => 'required|string|unique:users,phone,NULL,id,profil,driver',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/',
                ],
                'adresse' => 'nullable|string|max:255',
                'license_number' => 'required|string',
                'vehicle_number' => 'required|string',
                'vehicle_type' => 'required|string|in:moto,tricycle,car',
                'agent_code' => 'nullable|string|max:255',
                'agent_id' => 'nullable|string|max:255',
                'contract_type' => 'nullable|string|max:255',
                'start_date' => 'nullable|date',
                'tricycle_owner' => 'nullable|string|max:255',
                'owner_phone' => 'nullable|string|max:255',
            ],
            [
                'name.required' => 'Le nom est requis.',
                //'email.required' => 'L\'email est requis.',
                'email.email' => 'L\'email doit être valide.',
                'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
                'phone.required' => 'Le téléphone est requis.',
                'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
                'password.required' => 'Le mot de passe est requis.',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (@$!%*#?&).',
                'license_number.required' => 'Le numéro de permis est requis.',
                //'license_number.unique' => 'Ce numéro de permis est déjà utilisé.',
                'vehicle_number.required' => 'Le numéro de véhicule est requis.',
                'vehicle_type.required' => 'Le type de véhicule est requis.',
                'vehicle_type.in' => 'Le type de véhicule sélectionné est invalide.',
                'start_date.date' => 'La date de début doit être une date valide.',
            ]
        );

        try {
            $this->driverService->createDriver($validated);

            return redirect()->route('admin.drivers.index')->with('success', 'Agent créé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(User $driver)
    {
        try {
            $driverData = $this->driverService->getDriverById($driver->id);

            $bookingStats = $this->driverService->getDriverBookingStats($driver->driver->id);

            $commissionStats = $this->commissionService->getDriverCommissions($driver->driver->id);

            return view('pages.admin.drivers.show', compact('driverData', 'bookingStats', 'commissionStats'));
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(User $driver)
    {
        try {
            $driver->load('driver');

            return view('pages.admin.drivers.edit', compact('driver'));
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, User $driver)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $driver->id . ',id,profil,driver',
                'phone' => 'required|string|unique:users,phone,' . $driver->id . ',id,profil,driver',
                'is_active' => 'boolean',
                'adresse' => 'nullable|string|max:255',
                'license_number' => 'required|string|',
                'vehicle_number' => 'required|string',
                'vehicle_type' => 'required|string',
                'is_available' => 'boolean',
                'agent_code' => 'nullable|string|max:255',
                'agent_id' => 'nullable|string|max:255',
                'contract_type' => 'nullable|string|max:255',
                'start_date' => 'nullable|date',
                'tricycle_owner' => 'nullable|string|max:255',
                'owner_phone' => 'nullable|string|max:255',
            ],
            [
                'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
                'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
                //'license_number.unique' => 'Ce numéro de permis est déjà utilisé.',
                'start_date.date' => 'La date de début doit être une date valide.',
            ]
        );

        $this->driverService->updateDriver($driver->id, $validated);

        return redirect()->route('admin.drivers.show', $driver)
            ->with('success', 'Agent mis à jour avec succès');
    }

    public function destroy(User $driver)
    {
        try {
            $this->driverService->deleteDriver($driver->id);

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Agent supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
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
