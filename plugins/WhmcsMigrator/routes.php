<?php

use Illuminate\Support\Facades\Route;
use Plugins\WhmcsMigrator\src\Controllers\WhmcsMigratorController;

Route::middleware(['auth:admin', 'admin.permission:whmcs-migrator,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/whmcs-migrator', [WhmcsMigratorController::class, 'index'])->name('whmcs-migrator.index');
    Route::post('/whmcs-migrator/upload', [WhmcsMigratorController::class, 'upload'])->name('whmcs-migrator.upload');
    Route::post('/whmcs-migrator/{batch}/analyze', [WhmcsMigratorController::class, 'analyze'])->name('whmcs-migrator.analyze');
});
