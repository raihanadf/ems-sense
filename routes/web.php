<?php

use App\Http\Controllers\TreatmentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->name('dashboard');

    Route::view('profile', 'profile')
        ->name('profile');

    Route::get('treatments', [TreatmentController::class, 'index'])
        ->name('treatment');

    Route::view('model-settings', 'livewire.pages.model.index')
        ->name('model-settings');
});


require __DIR__ . '/auth.php';
