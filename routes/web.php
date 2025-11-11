<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\ImageController;

// Home Route - direkt zu Ausstellungen
Route::get('/', [ExhibitionController::class, 'index'])->name('home');

// Öffentliche Routen (readonly)
Route::prefix('exhibitions')->name('exhibitions.')->group(function () {
    Route::get('/', [ExhibitionController::class, 'index'])->name('index');
    Route::get('/{exhibition}', [ExhibitionController::class, 'show'])->name('show');
});

Route::prefix('images')->name('images.')->group(function () {
    Route::get('/', [ImageController::class, 'index'])->name('index');
    Route::get('/{image}', [ImageController::class, 'show'])->name('show');
});

// Auth-geschützte Routen
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::view('dashboard', 'dashboard')->name('dashboard');
    
    // Admin/Management Routen
    Route::prefix('admin')->name('admin.')->group(function () {
        // Exhibitions Management
        Route::resource('exhibitions', ExhibitionController::class)
            ->except(['index', 'show']); // nur create, store, edit, update, destroy
        
        // Images Management  
        Route::resource('images', ImageController::class)
            ->except(['index', 'show']);
    });
    
    // Settings Routen
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', 'settings/profile');
        
        Volt::route('profile', 'settings.profile')->name('profile.edit');
        Volt::route('password', 'settings.password')->name('user-password.edit');
        Volt::route('appearance', 'settings.appearance')->name('appearance.edit');
        
        if (Features::canManageTwoFactorAuthentication()) {
            Volt::route('two-factor', 'settings.two-factor')
                ->middleware(
                    when(
                        Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                        ['password.confirm'],
                        [],
                    ),
                )
                ->name('two-factor.show');
        }
    });
});
