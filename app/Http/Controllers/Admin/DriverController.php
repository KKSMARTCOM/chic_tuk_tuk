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
                'email' => 'nullable|email|unique:users,email',
                'phone' => 'required|string|unique:users,phone',
                'password' => 'required|string|min:8',
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
                'license_number.required' => 'Le numéro de permis est requis.',
                'license_number.unique' => 'Ce numéro de permis est déjà utilisé.',
                'vehicle_number.required' => 'Le numéro de véhicule est requis.',
                'vehicle_type.required' => 'Le type de véhicule est requis.',
                'vehicle_type.in' => 'Le type de véhicule sélectionné est invalide.',
                'start_date.date' => 'La date de début doit être une date valide.',
            ]
        );

        try {
            $this->driverService->createDriver($validated);

            return redirect()->route('admin.drivers.index')->with('success', 'Conducteur créé avec succès');
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
                'email' => 'nullable|email|unique:users,email,' . $driver->id,
                'phone' => 'required|string|unique:users,phone,' . $driver->id,
                'is_active' => 'boolean',
                'adresse' => 'nullable|string|max:255',
                'license_number' => 'required|string|unique:drivers,license_number,' . $driver->driver->id,
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
                'license_number.unique' => 'Ce numéro de permis est déjà utilisé.',
                'start_date.date' => 'La date de début doit être une date valide.',
            ]
        );

        $this->driverService->updateDriver($driver->id, $validated);

        return redirect()->route('admin.drivers.show', $driver)
            ->with('success', 'Conducteur mis à jour avec succès');
    }

    public function destroy(User $driver)
    {
        try {
            $this->driverService->deleteDriver($driver->id);

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Conducteur supprimé avec succès');
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

    public function export(Request $request)
    {
        try {
            $filters = $request->only(['search', 'is_active', 'is_available']);
            $fileName = 'conducteurs_' . now()->format('Y_m_d_His') . '.xlsx';

            return Excel::download(new DriversExport($filters), $fileName);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    public function importForm()
    {
        $stats = $this->driverService->getDriverStats();
        return view('pages.admin.drivers.import', compact('stats'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ], [
            'file.required' => 'Veuillez sélectionner un fichier',
            'file.mimes' => 'Le fichier doit être au format Excel ou CSV',
        ]);

        try {
            $import = new DriversImport();
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errors = $import->getErrors();

            if ($errors) {
                $errorMessage = "Import terminé avec " . $successCount . " conducteur(s) importé(s) avec succès. ";
                $errorMessage .= count($errors) . " ligne(s) ont échoué.";

                return redirect()->route('admin.drivers.index')
                    ->with('warning', $errorMessage)
                    ->with('errors', $errors);
            }

            return redirect()->route('admin.drivers.index')
                ->with('success', $successCount . ' conducteur(s) importé(s) avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'import: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function downloadTemplate()
    {
        try {
            $fileName = 'template_conducteurs_' . now()->format('Y_m_d_His') . '.xlsx';

            // Créer un fichier template
            $headers = [
                'Nom',
                'Email',
                'Téléphone',
                'Adresse',
                'Numéro Permis',
                'Numéro Véhicule',
                'Type Véhicule',
                'Disponible',
                'Actif',
            ];

            return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                public function array(): array
                {
                    return [
                        ['Jean Dupont', 'jean@example.com', '+22699999999', '123 Rue de la Paix', 'PERM123456', 'VEH001', 'car', 'Oui', 'Oui'],
                        ['Marie Martin', 'marie@example.com', '+22688888888', '456 Avenue des Champs', 'PERM789012', 'VEH002', 'moto', 'Non', 'Oui'],
                    ];
                }

                public function headings(): array
                {
                    return [
                        'nom',
                        'email',
                        'telephone',
                        'adresse',
                        'numero_permis',
                        'numero_vehicule',
                        'type_vehicule',
                        'disponible',
                        'actif',
                    ];
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    return [
                        1 => [
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => '8B5CF6'],
                            ],
                        ],
                    ];
                }
            }, $fileName);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors du téléchargement du template: ' . $e->getMessage());
        }
    }
}
