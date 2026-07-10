<?php

use Illuminate\Support\Facades\Route;
use Plugins\Invoices\src\Controllers\InvoiceController;

Route::middleware(['auth:admin', 'admin.permission:invoices,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/invoices/refresh-overdue', [InvoiceController::class, 'refreshOverdue'])->name('invoices.refresh-overdue');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-sent');
    Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
    Route::post('/invoices/{invoice}/apply-credit', [InvoiceController::class, 'applyCredit'])->name('invoices.apply-credit');
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/record-payment', [InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');
});
