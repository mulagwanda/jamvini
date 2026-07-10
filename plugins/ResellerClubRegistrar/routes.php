<?php

use Illuminate\Support\Facades\Route;
use Plugins\ResellerClubRegistrar\src\Controllers\SettingsController;

Route::middleware(['auth:admin', 'admin.permission:resellerclub-registrar,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/resellerclub/settings', [SettingsController::class, 'index'])->name('resellerclub.settings');
    Route::post('/resellerclub/settings', [SettingsController::class, 'save'])->name('resellerclub.settings.save');
    Route::post('/resellerclub/test', [SettingsController::class, 'testApi'])->name('resellerclub.test');
});
