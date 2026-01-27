<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $pricingService;
    protected $bookingService;

    public function __construct(PricingService $pricingService, BookingService $bookingService)
    {
        $this->pricingService = $pricingService;
        $this->bookingService = $bookingService;
    }

    /* public function calculatePrice(Request $request)
    {
        $request->validate([
            'pickup_location' => 'required',
            'dropoff_location' => 'nullable',
            'tourist_circuit_id' => 'nullable|exists:tourist_circuits,id',
            'promo_code' => 'nullable|string',
        ]);

        if ($request->tourist_circuit_id) {
            $circuit = TouristCircuit::findOrFail($request->tourist_circuit_id);
            $basePrice = $circuit->price;
        } else {
            $pricing = Pricing::where('from_location', $request->pickup_location)
                ->where('to_location', $request->dropoff_location)
                ->first();

            $basePrice = $pricing ? $pricing->base_price : 5000;
        }

        $discount = 0;
        if ($request->promo_code) {
            $promo = PromoCode::where('code', $request->promo_code)->first();
            if ($promo && $promo->isValid()) {
                $discount = $promo->applyDiscount($basePrice);
            }
        }

        return response()->json([
            'base_price' => $basePrice,
            'discount' => $discount,
            'total_price' => $basePrice - $discount,
        ]);
    } */

    public function calculatePrice(Request $request, string $fromZoneId, string $toZoneId)
    {
        try {
            $basePrice = $this->pricingService->getPrice($fromZoneId, $toZoneId);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        $days = (int) $request->input('days');

        if ($days < 1) {
            $days = 1;
        }

        // Placeholder for future promo/discount logic
        $discount = 0;

        $totalPrice = $basePrice /* * $days */ - $discount;
        if ($totalPrice < 0) {
            $totalPrice = 0;
        }

        return response()->json([
            'base_price' => (int) $basePrice,
            'days' => $days,
            'discount' => (int) $discount,
            'total_price' => (int) $totalPrice,
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate(
            [
                'from_zone_id' => 'required',
                'to_zone_id' => 'required',
                'pickup_datetime' => 'required|date|after:' . now()->addDay(),
                //'passengers' => 'required|integer|min:1|max:3',
                'special_requests' => 'nullable|string',
                'tourist_circuit_id' => 'nullable|exists:tourist_circuits,id',
                'promo_code' => 'nullable|string',
            ],
            [
                'from_zone_id.required' => 'La zone de départ est obligatoire.',
                'to_zone_id.required' => 'La zone de destination est obligatoire.',
                'pickup_datetime.required' => 'La date et l’heure de prise en charge sont obligatoires.',
                'pickup_datetime.date' => 'La date de prise en charge n’est pas valide.',
                'pickup_datetime.after' => 'La réservation doit être effectuée au moins 24 heures à l’avance.',
                'passengers.required' => 'Le nombre de passagers est obligatoire.',
                'passengers.integer' => 'Le nombre de passagers doit être un nombre.',
                //'passengers.min' => 'Au moins un passager est requis.',
                //'passengers.max' => 'Le nombre maximum de passagers est de 3.',
                'tourist_circuit_id.exists' => 'Le circuit touristique sélectionné est invalide.',
            ]
        );

        try {
            $priceData = $this->calculatePrice($request, $request->from_zone_id, $request->to_zone_id)->getData();

            $data = [
                'pickup_datetime' => $validated['pickup_datetime'],
                //'passengers' => $validated['passengers'],
                'special_requests' => $validated['special_requests'] ?? null,
                'tourist_circuit_id' => $validated['tourist_circuit_id'] ?? null,
                'base_price' => $priceData->base_price,
                'total_price' => $priceData->total_price,
                'from_zone_id' => $request->from_zone_id,
                'to_zone_id' => $request->to_zone_id,
                'days' => $priceData->days ?? 1,
                'phone' => $request->phone,
            ];

            $this->bookingService->create($data);

            /* $promoCodeId = null;
            if ($request->promo_code) {
                $promo = PromoCode::where('code', $request->promo_code)->first();
                if ($promo && $promo->isValid()) {
                    $promoCodeId = $promo->id;
                    $promo->increment('used_count');
                }
            } */

            return redirect()->back()->with('success', 'Réservation créée avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function acceptBooking(string $bookingId)
    {
        try {
            $driver = Auth::user()->driver;

            $this->bookingService->take($bookingId, $driver->id);

            return redirect()->route('driver.bookings.accepting')->with('success', 'Réservation acceptée avec succès!');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function startBooking(string $bookingId)
    {
        try {
            $driver = Auth::user()->driver;

            $this->bookingService->start($bookingId, $driver->id);

            return back()->with('success', 'Course commencée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function completeBooking(string $bookingId)
    {
        try {
            $driver = Auth::user()->driver;

            $this->bookingService->complete($bookingId, $driver->id);

            return back()->with('success', 'Course marquée comme terminée.');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function cancelBooking(Booking $booking, Request $request)
    {
        try {
            $driver = Auth::user()->driver;

            $this->bookingService->cancel(
                $booking->id,
                $driver->id,
                $request->input('cancellation_reason') ?? 'Annulée par le conducteur'
            );

            return back()->with(
                'success',
                'Réservation annulée avec succès.'
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
