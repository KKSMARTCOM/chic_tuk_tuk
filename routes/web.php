<?php

use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// Prix public: récupère le tarif entre deux zones (option: ?days=)
Route::get('/pricing/price/{fromZoneId}/{toZoneId}', [PricingController::class, 'calculatePrice'])->name('pricing.get-price');

Route::post('/login-store', [AuthController::class, 'loginStore'])->name('login.store');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

Route::middleware(['auth', 'role:admin,driver,client'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/bookings/histories', [PageController::class, 'historiesBookings'])->name('bookings.histories');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

//Include Admin routes file
require __DIR__ . '/admin.php';
//Include Client routes file
require __DIR__ . '/client.php';
//Include Driver routes file
require __DIR__ . '/driver.php';
