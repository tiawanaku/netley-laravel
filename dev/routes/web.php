<?php

use App\Http\Controllers\PublicoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicoController::class, 'index'])->name('home');
Route::post('/consultas', [PublicoController::class, 'store'])->name('consultas.store');

require __DIR__.'/admin.php';
require __DIR__.'/staff.php';
require __DIR__.'/portal.php';
