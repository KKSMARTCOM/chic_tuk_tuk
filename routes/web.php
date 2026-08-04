<?php

use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Web\FcmController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/install', [PageController::class, 'install'])->name('install');

// Prix public: récupère le tarif entre deux zones (option: ?days=)
Route::post('/pricing/price', [PricingController::class, 'calculatePrice'])->name('pricing.get-price');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login-store', [AuthController::class, 'loginStore'])->name('login.store');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/bookings/histories', [PageController::class, 'historiesBookings'])->name('bookings.histories');

    Route::post('/fcm/token', [FcmController::class, 'store'])->middleware('auth:sanctum');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Settings & Profile
    Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
    Route::get('/settings', [SettingsController::class, 'settings'])->name('settings.settings');
    Route::post('/profile/update', [SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotificationSettings'])->name('settings.notifications');
    Route::post('/settings/password', [SettingsController::class, 'changePassword'])->name('settings.password');
});

//Include Admin routes file
require __DIR__ . '/admin.php';
//Include Client routes file
require __DIR__ . '/client.php';
//Include Driver routes file
require __DIR__ . '/driver.php';
