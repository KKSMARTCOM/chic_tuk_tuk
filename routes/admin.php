<?php

// Admin Routes

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // Pricing
    Route::resource('pricing', PricingController::class);

    // Promo Codes
    Route::resource('promo-codes', PromoCodeController::class);

    // Drivers
    Route::resource('drivers', DriverController::class);
    Route::post('drivers/{driver}/toggle-availability', [DriverController::class, 'toggleAvailability'])->name('drivers.toggle-availability');
    Route::post('drivers/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');

    //Circuits
    // Route::resource('circuits', \App\Http\Controllers\Admin\TouristCircuitController::class);
    // Bookings
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');

    Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');

    Route::post('bookings/{booking}/assign-driver', [BookingController::class, 'assignDriver'])->name('bookings.assign-driver');
    Route::post('bookings/{booking}/remove-driver', [BookingController::class, 'removeDriver'])->name('bookings.remove-driver');
    Route::post('bookings/{booking}/update-status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
});
