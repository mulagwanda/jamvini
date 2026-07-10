<?php

use Illuminate\Support\Facades\Route;
use Plugins\Reports\src\Controllers\ReportController;

Route::middleware(['auth:admin', 'admin.permission:reports,auto'])
    ->prefix('admin/reports')
    ->name('admin.reports.')
    ->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/{key}/export/csv', [ReportController::class, 'exportCsv'])->where('key', '[A-Za-z0-9._-]+')->name('export.csv');
        Route::get('/{key}', [ReportController::class, 'show'])->where('key', '[A-Za-z0-9._-]+')->name('show');
    });
