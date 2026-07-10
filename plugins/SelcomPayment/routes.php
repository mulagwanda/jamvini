<?php

use Illuminate\Support\Facades\Route;
use Plugins\SelcomPayment\src\PaymentController;

Route::middleware(['auth:admin', 'admin.permission:selcom-payment,auto'])->prefix('admin/selcom')->name('admin.selcom.')->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::match(['get', 'post'], '/settings', [PaymentController::class, 'settings'])->name('settings');
});
Route::get('/selcom/pay/{invoice}', [PaymentController::class, 'processPayment'])->name('selcom.pay');
Route::post('/selcom/callback', [PaymentController::class, 'callback'])->name('selcom.callback');
