<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehiclePause;
use App\Services\VehicleService;
use Illuminate\Http\Request;

class VehiclePauseController extends Controller
{
    public function __construct(private VehicleService $vehicleService) {}

    public function index()
    {
        $pauses = VehiclePause::with([
            'vehicle.owner',
            'vehicleContract',
            'driverContract.driver.user',
        ])->latest('start_date')->get();

        return view('pages.admin.vehicle-pauses.index', compact('pauses'));
    }

    public function create()
    {
        $vehicles = Vehicle::with(['owner', 'activeVehicleContract'])
            ->where('is_active', true)
            ->get();

        return view('pages.admin.vehicle-pauses.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'   => 'required|exists:vehicles,id',
            'start_date'   => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'reason_type'  => 'required|in:agent_leave,agent_change,technical,accident,legal,other',
            'reason_notes' => 'nullable|string|max:1000',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $this->vehicleService->pauseVehicle($vehicle, $validated);

        return redirect()->route('admin.vehicle-pauses.index')
            ->with('success', 'Pause véhicule enregistrée avec succès.');
    }

    // Terminer une pause en cours
    public function end(Request $request, VehiclePause $vehiclePause)
    {
        $validated = $request->validate([
            'end_date' => 'required|date|after_or_equal:' . $vehiclePause->start_date->toDateString(),
        ]);

        $this->vehicleService->endPause($vehiclePause, $validated['end_date']);

        return back()->with('success', 'Pause terminée avec succès.');
    }

    public function destroy(VehiclePause $vehiclePause)
    {
        $vehiclePause->delete();
        return back()->with('success', 'Pause supprimée.');
    }
}
