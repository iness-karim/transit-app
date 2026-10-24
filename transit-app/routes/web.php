<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\DriverDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Notifications
    Route::post('/notifications/{id}/mark-as-read', function($id) {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
        return back();
    })->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', function() {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.markAllRead');

    Route::middleware(['role:superadmin'])->group(function () {
        Route::resource('transports', \App\Http\Controllers\TransportController::class);
        Route::resource('drivers', \App\Http\Controllers\DriverController::class);
        Route::resource('routes', \App\Http\Controllers\RouteController::class);
        Route::get('routes/{route}/predict', [\App\Http\Controllers\RouteController::class, 'predict'])->name('routes.predict');
        Route::get('/dashboard/briefing', [\App\Http\Controllers\DashboardController::class, 'briefing'])->name('dashboard.briefing');
        Route::get('/transports/{transport}/predict-maintenance', [\App\Http\Controllers\DashboardController::class, 'predictMaintenance'])->name('transports.predict');
        Route::get('/drivers/{driver}/predict-health', [\App\Http\Controllers\DashboardController::class, 'predictDriverHealth'])->name('drivers.predict');
        Route::get('/ai-hub', function() { return Inertia::render('AiHub'); })->name('ai.hub');
        Route::get('/ai-hub/matchmaker', [\App\Http\Controllers\DashboardController::class, 'matchmaker'])->name('ai.matchmaker');
        Route::get('/ai-hub/matchmaker/recommend/{route}', [\App\Http\Controllers\DashboardController::class, 'getRecommendations'])->name('ai.matchmaker.recommend');
        Route::get('/deliveries', [\App\Http\Controllers\RouteController::class, 'deliveries'])->name('deliveries.index');
    });

    // Driver Access Management (Admin Only)
    Route::get('/acces', [AccessController::class, 'index'])->name('access.index');
    Route::post('/acces/{driver}', [AccessController::class, 'store'])->name('access.store');
    Route::delete('/acces/{driver}', [AccessController::class, 'destroy'])->name('access.destroy');

    // Chauffeur Dashboard
    Route::get('/ma-mission', [DriverDashboardController::class, 'index'])->name('driver.dashboard');
    Route::get('/ma-performance', [DriverDashboardController::class, 'performance'])->name('driver.performance');
    Route::post('/ma-mission/{route}/update-time', [DriverDashboardController::class, 'updateTime'])->name('driver.update-time');
});

Route::post('/ai-chat', [\App\Http\Controllers\ChatBotController::class, 'chat'])->name('ai.chat');

require __DIR__.'/auth.php';
