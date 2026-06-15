<?php

// Admin Routes

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\TouristCircuitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard')->middleware('permission:view-dashboard');

    // Drivers
    Route::get('drivers/export/excel', [DriverController::class, 'export'])->name('drivers.export')->middleware('permission:export-drivers');
    Route::get('drivers/import/form', [DriverController::class, 'importForm'])->name('drivers.import.form')->middleware('permission:import-drivers');
    Route::post('drivers/import', [DriverController::class, 'import'])->name('drivers.import')->middleware('permission:import-drivers');
    Route::get('drivers/template/download', [DriverController::class, 'downloadTemplate'])->name('drivers.template.download')->middleware('permission:import-drivers');
    Route::resource('drivers', DriverController::class)->middleware('permission:view-drivers');
    Route::post('drivers/{driver}/toggle-availability', [DriverController::class, 'toggleAvailability'])->name('drivers.toggle-availability')->middleware('permission:edit-drivers');
    Route::post('drivers/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status')->middleware('permission:edit-drivers');

    // Bookings
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index')->middleware('permission:view-bookings');
    Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create')->middleware('permission:create-bookings');
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store')->middleware('permission:create-bookings');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show')->middleware('permission:view-bookings');
    Route::get('bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit')->middleware('permission:edit-bookings');

    Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update')->middleware('permission:edit-bookings');
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy')->middleware('permission:delete-bookings');

    Route::post('bookings/{booking}/assign-driver', [BookingController::class, 'assignDriver'])->name('bookings.assign-driver')->middleware('permission:edit-bookings');
    Route::post('bookings/{booking}/remove-driver', [BookingController::class, 'removeDriver'])->name('bookings.remove-driver')->middleware('permission:edit-bookings');
    Route::post('bookings/{booking}/update-status', [BookingController::class, 'updateStatus'])->name('bookings.update-status')->middleware('permission:edit-bookings');

    // Pricing
    Route::resource('pricing', PricingController::class);

    //Circuits
    Route::resource('circuits', TouristCircuitController::class);
    Route::post('circuits/{circuit}/toggle-status', [TouristCircuitController::class, 'toggleStatus'])->name('circuits.toggle-status')->middleware('permission:edit-circuits');

    // Promo Codes
    Route::resource('promo-codes', PromoCodeController::class);
    Route::post('promo-codes/{promo_code}/toggle-status', [PromoCodeController::class, 'toggleStatus'])->name('promo-codes.toggle-status')->middleware('permission:edit-promo-codes');

    // Leaves
    Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index')->middleware('permission:view-leaves');
    Route::get('leaves/{driver}', [LeaveController::class, 'show'])->name('leaves.show')->middleware('permission:view-leaves');
    Route::post('leaves/{driver}/add-instant', [LeaveController::class, 'addInstantLeave'])->name('leaves.add-instant')->middleware('permission:create-leaves');
    Route::get('leave/requests', [LeaveController::class, 'requests'])->name('leave.requests.index')->middleware('permission:view-leave-requests');
    Route::post('leave/requests/{leaveRequest}/approve', [LeaveController::class, 'approveRequest'])->name('leave.requests.approve')->middleware('permission:approve-leave-requests');
    Route::post('leave/requests/{leaveRequest}/reject', [LeaveController::class, 'rejectRequest'])->name('leave.requests.reject')->middleware('permission:reject-leave-requests');
    Route::post('leaves/{driver}/revoke', [LeaveController::class, 'revokeLeave'])->name('leaves.revoke')->middleware('permission:delete-leaves');

    // Commissions
    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index')->middleware('permission:view-commissions');
    Route::get('commissions/{commission}', [CommissionController::class, 'show'])->name('commissions.show')->middleware('permission:view-commissions');

    // Payments
    Route::resource('payments', PaymentController::class);
    Route::get('payments/driver/{driverId}/details', [PaymentController::class, 'driverPaymentDetails'])->name('payments.driver-details')->middleware('permission:view-payments');

    // Roles & Permissions Management - API routes first (more specific)
    Route::get('roles/{role}/data', [RoleController::class, 'getData'])->name('roles.data')->middleware('permission:view-roles');
    Route::post('roles/{role}/assign-users', [RoleController::class, 'assignUsers'])->name('roles.assign-users')->middleware('permission:edit-roles');
    Route::post('roles/{role}/remove-users', [RoleController::class, 'removeUsers'])->name('roles.remove-users')->middleware('permission:edit-roles');
    Route::resource('roles', RoleController::class)->middleware('permission:view-roles');

    Route::get('permissions/{permission}/data', [PermissionController::class, 'getData'])->name('permissions.data')->middleware('permission:view-permissions');
    Route::post('permissions/{permission}/assign-roles', [PermissionController::class, 'assignRoles'])->name('permissions.assign-roles')->middleware('permission:edit-permissions');
    Route::get('permissions/by-role/{role}', [PermissionController::class, 'getByRole'])->name('permissions.by-role')->middleware('permission:view-permissions');
    Route::resource('permissions', PermissionController::class)->middleware('permission:view-permissions');

    // User Roles Management
    Route::get('users/generate-password', [UserController::class, 'generatePassword'])->name('users.generate-password')->middleware('permission:create-users');
    Route::post('users/{user}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password')->middleware('permission:edit-users');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status')->middleware('permission:edit-users');
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit'])->middleware('permission:view-users');
});
