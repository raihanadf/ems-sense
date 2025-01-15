<?php

use App\Http\Controllers\TreatmentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->name('dashboard');

    Route::view('profile', 'profile')
        ->name('profile');

    Route::get('treatments', [TreatmentController::class, 'index'])
        ->name('treatment');

    Route::view('model-settings', 'livewire.pages.model.index')
        ->middleware(['can:manage-model-settings'])
        ->name('model-settings');

    Route::view('browse', 'livewire.pages.browse.index')
        ->name('browse');
});


require __DIR__ . '/auth.php';
