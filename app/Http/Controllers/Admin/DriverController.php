<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    protected $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'is_active', 'is_available']);
        $drivers = $this->driverService->getAllDrivers($filters);
        $stats = $this->driverService->getDriverStats();

        if ($request->wantsJson()) {
            return response()->json($drivers);
        }

        return view('pages.admin.drivers.index', compact('drivers', 'stats'));
    }

    public function show(User $driver)
    {
        $driverData = $this->driverService->getDriverById($driver->id);

        $bookingStats = $this->driverService->getDriverBookingStats($driver->driver->id);

        return view('pages.admin.drivers.show', compact('driverData', 'bookingStats'));
    }

    public function edit(User $driver)
    {
        $driver->load('driver');

        return view('pages.admin.drivers.edit', compact('driver'));
    }

    public function update(Request $request, User $driver)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $driver->id,
                'phone' => 'required|string|unique:users,phone,' . $driver->id,
                'is_active' => 'boolean',
                'license_number' => 'required|string|unique:drivers,license_number,' . $driver->driver->id,
                'vehicle_number' => 'required|string',
                'vehicle_type' => 'required|string',
                'is_available' => 'boolean',
            ],
            [
                'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
                'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
                'license_number.unique' => 'Ce numéro de permis est déjà utilisé.',
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
}
