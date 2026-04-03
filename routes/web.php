<?php

use App\Http\Controllers\Admin\WorkshopController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Employee\WorkshopCatalogController;
use App\Http\Controllers\Employee\WorkshopRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (! auth()->user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return auth()->user()->isAdmin()
        ? redirect()->route('admin.workshops.index')
        : redirect()->route('workshops.index');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::resource('workshops', WorkshopController::class)->except('show');
        Route::get('users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
    });

Route::middleware(['auth', 'verified', 'role:employee'])
    ->group(function () {
        Route::get('workshops', [WorkshopCatalogController::class, 'index'])->name('workshops.index');
        Route::post('workshops/{workshop}/registrations', [WorkshopRegistrationController::class, 'store'])
            ->name('workshops.registrations.store');
        Route::delete('workshops/{workshop}/registrations', [WorkshopRegistrationController::class, 'destroy'])
            ->name('workshops.registrations.destroy');
    });

require __DIR__.'/settings.php';
