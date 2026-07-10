<?php

use Illuminate\Support\Facades\Route;
use Plugins\SmsNotifications\src\SmsController;

Route::middleware(['auth:admin', 'admin.permission:sms-notifications,auto'])->prefix('admin/sms')->name('admin.sms.')->group(function () {
    Route::get('/', [SmsController::class, 'index'])->name('index');
    Route::match(['get', 'post'], '/settings', [SmsController::class, 'settings'])->name('settings');
});
