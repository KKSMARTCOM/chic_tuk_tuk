<?php

// Client Routes

use App\Http\Controllers\Client\OwnerLeaveController;
use App\Http\Controllers\Client\OwnerPaymentController;
use App\Http\Controllers\Client\OwnerVehicleController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'profil:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard')->middleware('permission:view-dashboard');
    Route::get('/payments/history', [DashboardController::class, 'paymentHistory'])->name('payments.history')->middleware('permission:view-payments');
    Route::get('/leaves/history', [DashboardController::class, 'leaveHistory'])->name('leaves.history')->middleware('permission:view-leaves');
    // Ajoutez d'autres routes client ici

});

Route::middleware(['auth:sanctum', 'profil:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerVehicleController::class, 'index'])->name('dashboard')->middleware('permission:view-dashboard');
    Route::get('/vehicles/{vehicle}', [OwnerVehicleController::class, 'show'])->name('vehicles.show');

    Route::get('payments/{vehicle}', [OwnerVehicleController::class, 'payments'])->name('payments.show');

    Route::get('leaves/{vehicle}', [OwnerVehicleController::class, 'leaves'])->name('leaves.show');
});
