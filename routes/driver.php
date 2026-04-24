<?php

// Driver Routes

use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\DriverLeaveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'driver'])->name('dashboard');
    Route::get('/bookings/available', [PageController::class, 'availableBookings'])->name('bookings.available');
    Route::get('/bookings/accepting', [PageController::class, 'acceptingBookings'])->name('bookings.accepting');

    Route::post('/bookings/{booking}/accept', [BookingController::class, 'acceptBooking'])->name('bookings.accept');
    Route::post('/bookings/{booking}/start', [BookingController::class, 'startBooking'])->name('bookings.start');
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'completeBooking'])->name('bookings.complete');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancelBooking'])->name('bookings.cancel');
    //A retoucher
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');

    // Leaves
    Route::get('/leaves', [DriverLeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/request', [DriverLeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [DriverLeaveController::class, 'store'])->name('leaves.store');
});
