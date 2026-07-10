<?php

use Illuminate\Support\Facades\Route;
use Plugins\BannerStudio\src\Controllers\BannerController;

Route::middleware(['auth:admin', 'admin.permission:banner-studio,auto'])
    ->prefix('admin/banner-studio')
    ->name('admin.banner-studio.')
    ->group(function () {
        Route::get('/', [BannerController::class, 'index'])->name('index');
        Route::get('/create', [BannerController::class, 'create'])->name('create');
        Route::post('/', [BannerController::class, 'store'])->name('store');
        Route::get('/{banner}/edit', [BannerController::class, 'edit'])->name('edit');
        Route::put('/{banner}', [BannerController::class, 'update'])->name('update');
        Route::delete('/{banner}', [BannerController::class, 'destroy'])->name('destroy');
        Route::get('/{banner}/studio', [BannerController::class, 'studio'])->name('studio');
        Route::post('/{banner}/studio', [BannerController::class, 'saveStudio'])->name('studio.save');
    });

Route::get('/banner-studio/{slug}', [BannerController::class, 'render'])->name('banner-studio.render');
