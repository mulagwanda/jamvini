<?php

use Illuminate\Support\Facades\Route;
use Plugins\Support\src\Controllers\AdminSupportController;
use Plugins\Support\src\Controllers\ClientSupportController;

Route::middleware(['auth:admin', 'admin.permission:support,auto'])->prefix('admin/support')->name('admin.support.')->group(function () {
    Route::get('/', [AdminSupportController::class, 'index'])->name('index');

    Route::get('/tickets', [AdminSupportController::class, 'tickets'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [AdminSupportController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [AdminSupportController::class, 'replyTicket'])->name('tickets.reply');
    Route::patch('/tickets/{ticket}', [AdminSupportController::class, 'updateTicket'])->name('tickets.update');

    Route::get('/announcements', [AdminSupportController::class, 'announcements'])->name('announcements.index');
    Route::get('/announcements/create', [AdminSupportController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [AdminSupportController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}/edit', [AdminSupportController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [AdminSupportController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AdminSupportController::class, 'destroy'])->name('announcements.destroy');
});

Route::get('/announcements', [ClientSupportController::class, 'announcements'])->name('support.announcements');
Route::get('/announcements/{announcement:slug}', [ClientSupportController::class, 'announcement'])->name('support.announcements.show');

Route::middleware(['auth'])->prefix('client/support')->name('client.support.')->group(function () {
    Route::get('/', [ClientSupportController::class, 'index'])->name('index');
    Route::get('/tickets/create', [ClientSupportController::class, 'createTicket'])->name('tickets.create');
    Route::post('/tickets', [ClientSupportController::class, 'storeTicket'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [ClientSupportController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [ClientSupportController::class, 'replyTicket'])->name('tickets.reply');
});
