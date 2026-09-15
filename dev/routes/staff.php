<?php

use App\Http\Controllers\Staff\Auth\LoginController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\PasswordController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->group(function () {
    Route::middleware('guest:personal')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store']);
    });

    Route::middleware(['auth:personal', 'guard.default:personal', 'personal.active'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('cambiar-password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('cambiar-password', [PasswordController::class, 'update'])->name('password.update');

        Route::middleware('personal.password')->group(function () {
            Route::get('/', DashboardController::class)->name('dashboard');
        });
    });
});
