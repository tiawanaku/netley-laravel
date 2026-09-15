<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

require __DIR__.'/admin.php';
require __DIR__.'/staff.php';
require __DIR__.'/portal.php';
