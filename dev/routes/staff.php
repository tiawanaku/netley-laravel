<?php

use App\Http\Controllers\Staff\Auth\LoginController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\PasswordController;
use App\Http\Controllers\Staff\ProcesoController;
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
            Route::get('agenda/eventos', [DashboardController::class, 'eventos'])->name('agenda.eventos');

            Route::get('casos', [ProcesoController::class, 'index'])->name('procesos.index');
            Route::get('casos/{proceso}', [ProcesoController::class, 'show'])->name('procesos.show');
            Route::post('casos/{proceso}/etapa', [ProcesoController::class, 'agregarEtapa'])->name('procesos.etapa.store');
        });
    });
});
