<?php

// Admin Routes

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\TouristCircuitController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    // Drivers
    Route::get('drivers/export/excel', [DriverController::class, 'export'])->name('drivers.export');
    Route::get('drivers/import/form', [DriverController::class, 'importForm'])->name('drivers.import.form');
    Route::post('drivers/import', [DriverController::class, 'import'])->name('drivers.import');
    Route::get('drivers/template/download', [DriverController::class, 'downloadTemplate'])->name('drivers.template.download');
    Route::resource('drivers', DriverController::class);
    Route::post('drivers/{driver}/toggle-availability', [DriverController::class, 'toggleAvailability'])->name('drivers.toggle-availability');
    Route::post('drivers/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');

    // Bookings
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');

    Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');

    Route::post('bookings/{booking}/assign-driver', [BookingController::class, 'assignDriver'])->name('bookings.assign-driver');
    Route::post('bookings/{booking}/remove-driver', [BookingController::class, 'removeDriver'])->name('bookings.remove-driver');
    Route::post('bookings/{booking}/update-status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');

    // Pricing
    Route::resource('pricing', PricingController::class);

    //Circuits
    Route::resource('circuits', TouristCircuitController::class);
    Route::post('circuits/{circuit}/toggle-status', [TouristCircuitController::class, 'toggleStatus'])->name('circuits.toggle-status');

    // Promo Codes
    Route::resource('promo-codes', PromoCodeController::class);
    Route::post('promo-codes/{promo_code}/toggle-status', [PromoCodeController::class, 'toggleStatus'])->name('promo-codes.toggle-status');

    // Leaves
    Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('leaves/{driver}', [LeaveController::class, 'show'])->name('leaves.show');
    Route::get('leave/requests', [LeaveController::class, 'requests'])->name('leave.requests.index');
    Route::post('leave/requests/{leaveRequest}/approve', [LeaveController::class, 'approveRequest'])->name('leave.requests.approve');
    Route::post('leave/requests/{leaveRequest}/reject', [LeaveController::class, 'rejectRequest'])->name('leave.requests.reject');
    Route::post('leaves/{driver}/revoke', [LeaveController::class, 'revokeLeave'])->name('leaves.revoke');

    // Commissions
    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::get('commissions/{commission}', [CommissionController::class, 'show'])->name('commissions.show');
    Route::patch('commissions/{commission}/mark-paid', [CommissionController::class, 'markAsPaid'])->name('commissions.mark-paid');
    Route::patch('commissions/{commission}/mark-unpaid', [CommissionController::class, 'markAsUnpaid'])->name('commissions.mark-unpaid');
});
