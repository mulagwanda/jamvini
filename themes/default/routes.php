<?php

use Illuminate\Support\Facades\Route;
use Themes\DefaultTheme\ThemeController;

Route::get('/theme', [ThemeController::class, 'index'])->name('admin.theme.index');
Route::post('/theme', [ThemeController::class, 'update'])->name('admin.theme.update');