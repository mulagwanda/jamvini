<?php

use Illuminate\Support\Facades\Route;
use Plugins\CMS\src\Controllers\PageController;
use Plugins\CMS\src\Controllers\PostController;
use Plugins\CMS\src\Controllers\CategoryController;
use Plugins\CMS\src\Controllers\FrontendController;
use Plugins\Media\src\Controllers\MediaController;

Route::middleware(['auth:admin', 'admin.permission:cms,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('cms')->name('cms.')->group(function () {
        Route::get('/pages/{page}/preview', [PageController::class, 'preview'])->name('pages.preview');
        Route::get('/posts/{post}/preview', [PostController::class, 'preview'])->name('posts.preview');
        Route::resource('pages', PageController::class);
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class);

        // Compatibility routes. Media Library now lives at /admin/media.
        Route::resource('media', MediaController::class);
        Route::get('/media-picker', [MediaController::class, 'picker'])->name('media.picker');
    });
});

Route::get('/blog', [FrontendController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [FrontendController::class, 'post'])->name('blog.post');
