<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PromoCode;
use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

        $days = (int) $request->input('days', 1);

        if ($days < 1) {
            $days = 1;
        }

        // Gestion promo
        $discount = 0;
        if ($request->promo_code) {
            $promo = PromoCode::where('code', $request->promo_code)->first();
            if ($promo && $promo->isValid()) {
                $discount = $promo->applyDiscount($basePrice);
            }
        }

        $totalPrice = $basePrice - $discount;
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
        // Validation par étape
        $request->validate(
            [
                'from_location' => 'required',
                'to_location' => 'required',
                'from_lat' => 'required_with:from_location|numeric',
                'from_lng' => 'required_with:from_location|numeric',
                'to_lat' => 'required_with:to_location|numeric',
                'to_lng' => 'required_with:to_location|numeric',

                'pickup_date' => 'required|date|after:today' /* . now()->addDay()->toDateString() */,
                'pickup_time' => 'required|date_format:H:i',
                'days' => 'nullable|integer|min:1',

                'phone' => 'required|string|regex:/^[0-9+\-\s()]+$/|min:10',
                'special_requests' => 'nullable|string|max:500',
            ],
            [
                'from_location.required' => 'Veuillez sélectionner une ville de départ.',
                'to_location.required' => 'Veuillez sélectionner une ville de destination.',
                'from_lat.required_with' => 'Ville de départ manquantes.',
                'to_lat.required_with' => 'Ville de destination manquantes.',

                'pickup_date.required' => 'La date de prise en charge est obligatoire.',
                'pickup_date.after' => 'La réservation doit être effectuée au moins 24 heures à l\'avance.',
                'pickup_time.required' => 'L\'heure de prise en charge est obligatoire.',
                'days.min' => 'Le nombre de jours est obligatoire pour les réservations multi-jours.',

                'phone.required' => 'Le numéro de téléphone est obligatoire.',
            ]
        );

        try {
            // Calcul de la distance
            $distance = $this->pricingService->getDistance($request->from_lng, $request->from_lat, $request->to_lng, $request->to_lat);

            if (!$distance) {
                return redirect()->back()->withErrors('Erreur lors du calcul de l\'itinéraire.');
            }

            $price = $this->pricingService->getPrice($distance);

            // Gestion promo
            $discount = 0;
            $promoCodeId = null;
            if ($request->promo_code) {
                $promo = PromoCode::where('code', $request->promo_code)->first();

                if ($promo && $promo->isValid()) {
                    $discount = $promo->applyDiscount($price);
                    $promoCodeId = $promo->id;
                    $promo->increment('used_count');
                } else {
                    return redirect()->back()->withErrors(['promo_code' => 'Code promo invalide ou expiré.'])->withInput();
                }
            }

            $totalPrice = $price - $discount;

            $bookingData = [
                'from_location' => $request->from_location,
                'to_location' => $request->to_location,
                'from_lng' => $request->from_lng,
                'from_lat' => $request->from_lat,
                'to_lng' => $request->to_lng,
                'to_lat' => $request->to_lat,
                'distance' => $distance,
                'phone' => $request->phone,
                'days' => $request->days ? $request->days : 1,
                'pickup_date' => $request->pickup_date,
                'pickup_time' => $request->pickup_time,
                'special_requests' => $request->special_requests,
                'base_price' => $price,
                'discount' => $discount,
                'total_price' => $totalPrice,
            ];

            $this->bookingService->create($bookingData);

            return redirect()->back()->with('success', 'Réservation créée avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue: ' . $e->getMessage()])->withInput();
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
