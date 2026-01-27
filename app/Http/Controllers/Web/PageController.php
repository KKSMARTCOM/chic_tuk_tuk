<?php

namespace App\Http\Controllers\Web;

use App\Consts\Status;
use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\TestimonialService;
use App\Services\ZoneService;
use App\Traits\Utils;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    protected $testiomialService;
    protected $zoneService;
    protected $bookingService;

    public function __construct(
        TestimonialService $testiomialService,
        ZoneService $zoneService,
        BookingService $bookingService
    ) {
        $this->testiomialService = $testiomialService;
        $this->zoneService = $zoneService;
        $this->bookingService = $bookingService;
    }

    public function index()
    {

        $testimonials = $this->testiomialService->getTestimonials();

        $zones = $this->zoneService->getZones();

        /* $circuits = TouristCircuit::where('is_active', true)
            ->take(3)
            ->get(); */

        return view('pages.index', compact('testimonials', 'zones'));
    }

    public function availableBookings()
    {
        $bookings = $this->bookingService->get('pending');

        return view('pages.driver.bookings.available', compact('bookings'));
    }

    public function acceptingBookings()
    {
        $driver = Auth::user()->driver;
        $bookings = $this->bookingService->getByDriverId($driver->id, ['confirmed', 'in_progress']);

        return view('pages.driver.bookings.accepting', compact('bookings'));
    }

    public function historiesBookings()
    {
        $driver = Auth::user()->driver;
        $bookings = $this->bookingService->getByDriverId($driver->id, ['completed', 'cancelled']);

        return view('pages.driver.bookings.history', compact('bookings'));
    }
}
