<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PromoCode;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $pricingService;
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

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

                'week_days'       => 'required_if:days,>,1|nullable|in:lun_ven,lun_sam,lun_dim',
                'round_trip'      => 'nullable|boolean',
                'return_time'     => 'required_if:round_trip,1|nullable|date_format:H:i|after:pickup_time',
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

                'round_trip.boolean' => 'Le type de trajet est invalide.',
                'return_time.after' => 'L\'heure de retour doit être postérieure à l\'heure de prise en charge.',
                'return_time.required_if' => 'L\'heure de retour est requise pour les trajets aller-retour.',
                'week_days.in' => 'Les jours de la semaine sont invalides.',
                'week_days.required_if' => 'Les jours de la semaine sont requis pour les réservations multi-jours.',

                'phone.required' => 'Le numéro de téléphone est obligatoire.',
            ]
        );

        try {

            $bookingData = [
                'from_location' => $request->from_location,
                'to_location' => $request->to_location,
                'from_lng' => $request->from_lng,
                'from_lat' => $request->from_lat,
                'to_lng' => $request->to_lng,
                'to_lat' => $request->to_lat,
                'phone' => $request->phone,
                'days' => $request->days ? $request->days : 1,
                'pickup_date' => $request->pickup_date,
                'pickup_time' => $request->pickup_time,
                'special_requests' => $request->special_requests,
                'promo_code' => $request->promo_code,
                'week_days' => $request->week_days,
                'round_trip' => $request->round_trip,
                'return_time' => $request->return_time,
            ];

            $this->bookingService->create($bookingData);

            return redirect()->back()->with('success', 'Réservation créée avec succès!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue. Veuillez réessayer ! ' . $e->getMessage()])->withInput();
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
                $request->input('cancellation_reason') ?? 'Annulée par le Agent'
            );

            return back()->with(
                'success',
                'Réservation annulée avec succès.'
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revokeSubscription(Booking $booking)
    {
        try {
            $driver = Auth::user()->driver;
            $this->bookingService->revokeFromSubscription($booking->id, $driver->id);

            return back()->with('success', 'Course révoquée. Elle est maintenant accessible aux autres agents.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
