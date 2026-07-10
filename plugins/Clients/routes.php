<?php

use Illuminate\Support\Facades\Route;
use Plugins\Clients\src\Controllers\ClientController;

Route::middleware(['auth:admin', 'admin.permission:clients,auto'])->prefix('admin')->name('admin.')->group(function () {
    // Custom routes MUST be before resource
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::get('/clients/export', [ClientController::class, 'export'])->name('clients.export');
    Route::post('/clients/bulk', [ClientController::class, 'bulk'])->name('clients.bulk');
    Route::post('/clients/{client}/support-access', [ClientController::class, 'supportAccess'])->name('clients.support-access');
    Route::post('/clients/{client}/tickets', [ClientController::class, 'openTicket'])->name('clients.tickets.open');
    Route::patch('/clients/{client}/notes', [ClientController::class, 'updateNotes'])->name('clients.notes');
    
    // Resource last
    Route::resource('clients', ClientController::class);
});
