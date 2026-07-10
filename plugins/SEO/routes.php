<?php

use Illuminate\Support\Facades\Route;
use Plugins\SEO\src\Controllers\SeoController;

// Admin routes
Route::middleware(['auth:admin', 'admin.permission:seo,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/seo', [SeoController::class, 'index'])->name('seo.index');
    Route::post('/seo', [SeoController::class, 'update'])->name('seo.update');
});

// Public sitemap
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/seo/track.js', [SeoController::class, 'trackingScript'])->name('seo.track.script');
Route::post('/seo/track', [SeoController::class, 'track'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('seo.track');
