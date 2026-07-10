<?php

use Illuminate\Support\Facades\Route;
use Plugins\Orders\src\Controllers\OrderController;

Route::middleware(['auth:admin', 'admin.permission:orders,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{order}/generate-invoice', [OrderController::class, 'generateInvoiceForOrder'])->name('orders.generate-invoice');
    Route::post('/orders/{order}/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order}/retry-provisioning', [OrderController::class, 'retryProvisioning'])->name('orders.retry-provisioning');
});
