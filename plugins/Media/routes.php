<?php

use Illuminate\Support\Facades\Route;
use Plugins\Media\src\Controllers\MediaController;

Route::middleware(['auth:admin', 'admin.permission:media,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/media/unsplash/search', [MediaController::class, 'searchUnsplash'])->name('media.unsplash.search');
    Route::post('/media/unsplash/import', [MediaController::class, 'importUnsplash'])->name('media.unsplash.import');
    Route::post('/media/generate', [MediaController::class, 'generateImage'])->name('media.generate');
    Route::resource('media', MediaController::class);
    Route::get('/media-picker', [MediaController::class, 'picker'])->name('media.picker');
});
