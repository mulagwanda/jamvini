<?php

use Illuminate\Support\Facades\Route;
use Plugins\Settings\src\Controllers\SettingsController;

Route::middleware(['auth:admin', 'admin.permission:settings,auto'])->prefix('admin')->name('admin.')->group(function () {
    // General
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    
    // Site
    Route::get('/settings/site', [SettingsController::class, 'site'])->name('settings.site');
    Route::post('/settings/site', [SettingsController::class, 'updateSite'])->name('settings.site.update');
    
    // Invoice
    Route::get('/settings/invoice', [SettingsController::class, 'invoice'])->name('settings.invoice');
    Route::post('/settings/invoice', [SettingsController::class, 'updateInvoice'])->name('settings.invoice.update');

    // Billing & Tax
    Route::get('/settings/billing', [SettingsController::class, 'billing'])->name('settings.billing');
    Route::post('/settings/billing', [SettingsController::class, 'updateBilling'])->name('settings.billing.update');

    // Domain
    Route::get('/settings/domain', [SettingsController::class, 'domain'])->name('settings.domain');
    Route::post('/settings/domain', [SettingsController::class, 'updateDomain'])->name('settings.domain.update');
    
    // Email test
    Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.test-email');

    // Notifications
    Route::get('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotification'])->name('settings.notifications.update');
});
