<?php

use Illuminate\Support\Facades\Route;
use Plugins\MaintenanceMode\src\Controllers\MaintenanceController;

Route::middleware(['auth:admin', 'admin.permission:maintenance-mode,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'update'])->name('maintenance.update');
});

Route::get('/maintenance/preview', [MaintenanceController::class, 'preview'])->name('maintenance.preview');
