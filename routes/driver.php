<?php

// Driver Routes

use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'driver'])->name('dashboard');
    Route::get('/bookings/available', [PageController::class, 'availableBookings'])->name('bookings.available');
    Route::get('/bookings/accepting', [PageController::class, 'acceptingBookings'])->name('bookings.accepting');
    Route::get('/bookings/histories', [PageController::class, 'historiesBookings'])->name('bookings.histories');

    Route::post('/bookings/{booking}/accept', [BookingController::class, 'acceptBooking'])->name('bookings.accept');
    Route::post('/bookings/{booking}/start', [BookingController::class, 'startBooking'])->name('bookings.start');
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'completeBooking'])->name('bookings.complete');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancelBooking'])->name('bookings.cancel');
    //A retoucher
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
});
