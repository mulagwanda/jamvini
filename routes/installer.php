<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Installer\InstallController;

Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/installed', [InstallController::class, 'installed'])->name('installed');
    Route::get('/{step}', [InstallController::class, 'step'])->name('step');
    Route::post('/{step}', [InstallController::class, 'step']);
});
