<?php

use Illuminate\Support\Facades\Route;
use Plugins\Promotions\src\Controllers\PromotionController;

Route::middleware(['auth:admin', 'admin.permission:promotions,auto'])
    ->prefix('admin/promotions')
    ->name('admin.promotions.')
    ->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('index');
        Route::get('/create', [PromotionController::class, 'create'])->name('create');
        Route::post('/', [PromotionController::class, 'store'])->name('store');
        Route::get('/coupons', [PromotionController::class, 'coupons'])->name('coupons');
        Route::post('/coupons', [PromotionController::class, 'storeCoupon'])->name('coupons.store');
        Route::get('/{promotion}/edit', [PromotionController::class, 'edit'])->name('edit');
        Route::put('/{promotion}', [PromotionController::class, 'update'])->name('update');
        Route::delete('/{promotion}', [PromotionController::class, 'destroy'])->name('destroy');
    });
