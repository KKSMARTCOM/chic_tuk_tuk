<?php

// Client Routes

use App\Http\Controllers\Client\OwnerVehicleController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard')->middleware('permission:view-dashboard');
    Route::get('/payments/history', [DashboardController::class, 'paymentHistory'])->name('payments.history')->middleware('permission:view-payments');
    Route::get('/leaves/history', [DashboardController::class, 'leaveHistory'])->name('leaves.history')->middleware('permission:view-leaves');
    // Ajoutez d'autres routes client ici
});

Route::middleware(['auth:sanctum', 'role:proprietaire'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard')->middleware('permission:view-dashboard');

    //Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/vehicles', [OwnerVehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', [OwnerVehicleController::class, 'show'])->name('vehicles.show');
    Route::get('/vehicles/{vehicle}/payments', [OwnerVehicleController::class, 'payments'])->name('vehicles.payments');
    Route::get('/vehicles/{vehicle}/pauses', [OwnerVehicleController::class, 'pauses'])->name('vehicles.pauses');
});
