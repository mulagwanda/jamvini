<?php

use Illuminate\Support\Facades\Route;
use Plugins\Security\src\Controllers\SecurityController;

Route::middleware(['auth:admin', 'admin.permission:security,auto'])->prefix('admin/security')->name('admin.security.')->group(function () {
    Route::get('/', [SecurityController::class, 'index'])->name('index');
    Route::post('/settings', [SecurityController::class, 'update'])->name('settings');
    Route::post('/rules', [SecurityController::class, 'storeRule'])->name('rules.store');
    Route::delete('/rules/{id}', [SecurityController::class, 'deleteRule'])->name('rules.delete');
    Route::post('/scan', [SecurityController::class, 'scan'])->name('scan');
});
