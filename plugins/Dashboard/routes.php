<?php

use Illuminate\Support\Facades\Route;
use Plugins\Dashboard\src\Controllers\DashboardController;
use Plugins\Dashboard\src\Controllers\ThemeController;

Route::middleware(['auth:admin', 'admin.permission:dashboard,read'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/widgets', [DashboardController::class, 'saveWidgets'])->middleware('admin.permission:dashboard,write')->name('dashboard.widgets.save');
});
