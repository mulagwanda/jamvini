<?php

use Illuminate\Support\Facades\Route;
use Plugins\ClientPortal\src\Controllers\ClientPortalController;

Route::middleware(['auth'])->group(function () {
    Route::get('/client/dashboard', [ClientPortalController::class, 'dashboard'])->name('client.dashboard');
    Route::get('/client/services', [ClientPortalController::class, 'services'])->name('client.services');
    Route::get('/client/services/{service}', [ClientPortalController::class, 'serviceDetail'])->name('client.services.show');
    Route::post('/client/services/{service}/cpanel-login', [ClientPortalController::class, 'cpanelLogin'])->name('client.services.cpanel-login');
    Route::get('/client/domains', [ClientPortalController::class, 'domains'])->name('client.domains');
    Route::get('/client/orders', [ClientPortalController::class, 'orders'])->name('client.orders');
    Route::get('/client/orders/{order}', [ClientPortalController::class, 'orderDetail'])->name('client.orders.show');
    Route::get('/client/invoices', [ClientPortalController::class, 'invoices'])->name('client.invoices');
    Route::get('/client/invoices/{invoice}', [ClientPortalController::class, 'invoiceDetail'])->name('client.invoices.show');
    Route::get('/client/account', [ClientPortalController::class, 'account'])->name('client.account');
Route::post('/client/account', [ClientPortalController::class, 'updateAccount'])->name('client.account.update');
    Route::post('/client/support-access/end', [ClientPortalController::class, 'endSupportAccess'])->name('client.support-access.end');
});
