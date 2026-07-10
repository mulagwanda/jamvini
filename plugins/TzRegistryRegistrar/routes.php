<?php

use Illuminate\Support\Facades\Route;
use Plugins\TzRegistryRegistrar\src\Controllers\SettingsController;

Route::middleware(['auth:admin', 'admin.permission:tznic-registrar,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tznic/settings', [SettingsController::class, 'index'])->name('tznic.settings');
    Route::post('/tznic/settings', [SettingsController::class, 'save'])->name('tznic.settings.save');
    Route::post('/tznic/test', [SettingsController::class, 'test'])->name('tznic.test');
    Route::post('/tznic/sync-domains', [SettingsController::class, 'syncDomains'])->name('tznic.sync-domains');
    Route::post('/tznic/sync-pricing', [SettingsController::class, 'syncPricing'])->name('tznic.sync-pricing');
});
