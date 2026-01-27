<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'driver', 'fromZone', 'toZone']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhereHas('fromZone', function ($zoneQuery) use ($search) {
                        $zoneQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('toZone', function ($zoneQuery) use ($search) {
                        $zoneQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $bookings = $query->latest()->paginate(10);

        return view('pages.admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'driver', 'touristCircuit', 'promoCode']);

        $availableDrivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->with('driver')
            ->get();

        return view('pages.admin.bookings.show', compact('booking', 'availableDrivers'));
    }

    public function assignDriver(Request $request, Booking $booking)
    {
        try {
            $validated = $request->validate(
                [
                    'driver_id' => 'required|exists:drivers,id',
                ],
                [
                    'driver_id.exists' => 'Le conducteur sélectionné n\'existe pas.',
                ]
            );

            $driver = Driver::findOrFail($validated['driver_id']);

            $user = $driver->user;

            if ($user->role !== 'driver' || !$user->is_active) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Ce conducteur n\'est pas disponible'], 400);
                }

                return back()->with('error', 'Ce conducteur n\'est pas disponible.');
            }

            $this->bookingService->take($booking->id, $driver->id);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Conducteur assigné avec succès']);
            }

            return back()->with('success', 'Conducteur assigné avec succès');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de l\'assignation du conducteur: ' . $e->getMessage()], 400);
            }

            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function removeDriver(Booking $booking)
    {
        try {
            if (!$booking->driver_id) {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Aucun conducteur assigné à cette réservation'], 400);
                }

                return back()->with('error', 'Aucun conducteur assigné à cette réservation');
            }

            if ($booking->status !== 'confirmed' && $booking->status !== 'in_progress') {
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Impossible de retirer le conducteur pour ce statut de réservation'], 400);
                }

                return back()->with('error', 'Impossible de retirer le conducteur pour ce statut de réservation');
            }

            $data = [
                'driver_id' => null,
                'status' => 'pending',
            ];

            $this->bookingService->update($booking->id, $data);

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Conducteur retiré avec succès']);
            }

            return back()->with('success', 'Conducteur retiré avec succès');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors du retrait du conducteur: ' . $e->getMessage()], 400);
            }

            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        try {
            $validated = $request->validate(
                [
                    'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
                    'cancellation_reason' => 'nullable|string|max:1000',
                ],
                [
                    'status.in' => 'Le statut sélectionné est invalide.',
                ]
            );

            $updateData = ['status' => $validated['status']];

            if ($validated['status'] === 'cancelled') {
                if (in_array($booking->status, ['completed', 'in_progress'])) {
                    throw new \Exception('Impossible d\'annuler la réservation.');
                }

                $updateData['cancelled_at'] = now();
                if (!empty($validated['cancellation_reason'])) {
                    $updateData['cancellation_reason'] = $validated['cancellation_reason'];
                }
            }

            $this->bookingService->update($booking->id, $updateData);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Statut mis à jour avec succès']);
            }

            return back()->with('success', 'Statut mis à jour avec succès');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut: ' . $e->getMessage()], 400);
            }

            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function edit(Booking $booking)
    {
        $booking->load(['user', 'driver', 'touristCircuit', 'promoCode', 'fromZone', 'toZone']);
        $zones = \App\Models\Zone::all();
        $touristCircuits = \App\Models\TouristCircuit::all();

        return view('pages.admin.bookings.edit', compact('booking', 'zones', 'touristCircuits'));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate(
            [
                'user_id' => 'nullable|exists:users,id',
                'phone' => 'required|string|max:20',
                'from_zone_id' => 'required|exists:zones,id',
                'to_zone_id' => 'required|exists:zones,id',
                'pickup_datetime' => 'required|date',
                'days' => 'nullable|integer|min:1',
                'total_price' => 'required|numeric|min:0',
                'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
                'tourist_circuit_id' => 'nullable|exists:tourist_circuits,id',
                'special_requests' => 'nullable|string|max:1000',
            ],
            [
                'user_id.exists' => 'L\'utilisateur sélectionné n\'existe pas.',
                'from_zone_id.exists' => 'La zone de départ sélectionnée n\'existe pas.',
                'to_zone_id.exists' => 'La zone d\'arrivée sélectionnée n\'existe pas.',
                'tourist_circuit_id.exists' => 'Le circuit touristique sélectionné n\'existe pas.',
            ]
        );

        $this->bookingService->update($booking->id, $validated);

        return redirect()->route('admin.bookings.show', $booking)->with('success', 'Réservation mise à jour avec succès');
    }
}
