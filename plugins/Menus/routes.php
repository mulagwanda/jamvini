<?php

use Illuminate\Support\Facades\Route;
use Plugins\Menus\src\Controllers\MenuController;

Route::middleware(['auth:admin', 'admin.permission:menus,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
    Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
    Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');

    Route::post('/menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
    Route::post('/menus/{menu}/items/reorder', [MenuController::class, 'reorderItems'])->name('menus.items.reorder');
    Route::put('/menus/{menu}/items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
    Route::delete('/menus/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');
});
