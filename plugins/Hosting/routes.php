<?php

use Illuminate\Support\Facades\Route;
use Plugins\Hosting\src\Controllers\HostingController;

Route::middleware(['auth:admin', 'admin.permission:hosting,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/hosting/settings', [HostingController::class, 'settings'])->name('hosting.settings');
    Route::post('/hosting/settings', [HostingController::class, 'saveSettings'])->name('hosting.settings.save');
});
