<?php

use Illuminate\Support\Facades\Route;
use Plugins\Slider\src\Controllers\SliderController;

Route::middleware(['auth:admin', 'admin.permission:slider,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/slider/{slider}/studio', [SliderController::class, 'studio'])->name('slider.studio');
    Route::post('/slider/{slider}/studio/settings', [SliderController::class, 'studioSettings'])->name('slider.studio.settings');
    Route::post('/slider/{slider}/studio/slides', [SliderController::class, 'studioSlide'])->name('slider.studio.slides');
    Route::post('/slider/{slider}/slides/{slide}/layers', [SliderController::class, 'saveLayers'])->name('slider.slides.layers');
    Route::resource('slider', SliderController::class);
    Route::post('/slider/{slider}/slides', [SliderController::class, 'addSlide'])->name('slider.slides.store');
    Route::put('/slider/{slider}/slides/{slide}', [SliderController::class, 'updateSlide'])->name('slider.slides.update');
    Route::delete('/slider/{slider}/slides/{slide}', [SliderController::class, 'deleteSlide'])->name('slider.slides.delete');
});

// Public slider render
Route::get('/slider/{slug}.js', [SliderController::class, 'render'])->name('slider.render');
