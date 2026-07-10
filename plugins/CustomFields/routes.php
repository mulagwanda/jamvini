<?php

use Illuminate\Support\Facades\Route;
use Plugins\CustomFields\src\Controllers\CustomFieldController;

Route::middleware(['auth:admin', 'admin.permission:custom-fields,auto'])
    ->prefix('admin/custom-fields')
    ->name('admin.custom-fields.')
    ->group(function () {
        Route::get('/', [CustomFieldController::class, 'index'])->name('index');
        Route::get('/create', [CustomFieldController::class, 'create'])->name('create');
        Route::post('/', [CustomFieldController::class, 'store'])->name('store');
        Route::get('/{field}/edit', [CustomFieldController::class, 'edit'])->name('edit');
        Route::put('/{field}', [CustomFieldController::class, 'update'])->name('update');
        Route::delete('/{field}', [CustomFieldController::class, 'destroy'])->name('destroy');
    });
