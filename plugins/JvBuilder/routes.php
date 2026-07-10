<?php

use Illuminate\Support\Facades\Route;
use Plugins\JvBuilder\src\Controllers\BuilderController;

Route::middleware(['auth:admin', 'admin.permission:cms,auto'])->prefix('admin/jv-builder')->name('admin.jv-builder.')->group(function () {
    Route::get('/', [BuilderController::class, 'index'])->name('index');
    Route::get('/pages/{page}', [BuilderController::class, 'edit'])->name('pages.edit');
    Route::post('/pages/{page}', [BuilderController::class, 'save'])->name('pages.save');
});
