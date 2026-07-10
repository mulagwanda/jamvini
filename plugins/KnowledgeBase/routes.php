<?php

use Illuminate\Support\Facades\Route;
use Plugins\KnowledgeBase\src\Controllers\ArticleController;

Route::middleware(['auth:admin', 'admin.permission:knowledge-base,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('kb')->name('kb.')->group(function () {
        Route::get('/', [ArticleController::class, 'index'])->name('index');
        Route::get('/create', [ArticleController::class, 'create'])->name('create');
        Route::post('/', [ArticleController::class, 'store'])->name('store');
        Route::get('/{article}/edit', [ArticleController::class, 'edit'])->name('edit');
        Route::put('/{article}', [ArticleController::class, 'update'])->name('update');
        Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy');
    });
});

Route::get('/kb', [ArticleController::class, 'publicIndex'])->name('kb.index');
Route::get('/kb/{slug}', [ArticleController::class, 'publicShow'])->name('kb.show');
