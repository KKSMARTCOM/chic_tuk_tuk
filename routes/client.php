<?php

// Client Routes

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard')->middleware('permission:view-dashboard');
    Route::get('/payments/history', [DashboardController::class, 'paymentHistory'])->name('payments.history')->middleware('permission:view-payments');
    Route::get('/leaves/history', [DashboardController::class, 'leaveHistory'])->name('leaves.history')->middleware('permission:view-leaves');
    // Ajoutez d'autres routes client ici
});
