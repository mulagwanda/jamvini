<?php

use Illuminate\Support\Facades\Route;
use Plugins\CMS\src\Controllers\FrontendController;

Route::get('/{slug}', [FrontendController::class, 'show'])
    ->where('slug', '^(?!admin$|api$|install$|login$|logout$|register$|password$|cron$|home$|blog$).+')
    ->name('page.show');
