<?php

use Illuminate\Support\Facades\Route;
use Plugins\OfflinePayments\src\Controllers\SettingsController;

Route::middleware(['auth:admin', 'admin.permission:offline-payments,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/offline-payments/settings', [SettingsController::class, 'index'])->name('offline-payments.settings');
    Route::post('/offline-payments/settings', [SettingsController::class, 'save'])->name('offline-payments.settings.save');
});
