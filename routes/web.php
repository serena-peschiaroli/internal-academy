<?php

use App\Http\Controllers\Admin\WorkshopController;
use App\Http\Controllers\Admin\WorkshopStatsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\SecurePasswordController;
use App\Http\Controllers\Employee\WorkshopCatalogController;
use App\Http\Controllers\Employee\WorkshopRegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('account-inactive', function (Request $request) {
    return Inertia::render('auth/AccountInactive', [
        'message' => $request->string('msg')->toString() ?: 'Your account is inactive. Please contact support.',
    ]);
})->name('account-inactive');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('secure-password', [SecurePasswordController::class, 'edit'])->name('secure-password.edit');
    Route::put('secure-password', [SecurePasswordController::class, 'update'])->name('secure-password.update');
});

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::resource('workshops', WorkshopController::class)->except('show');
        Route::resource('users', AdminUserController::class)->except('destroy');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('stats/workshops', WorkshopStatsController::class)->name('stats.workshops');
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
