<?php

use Illuminate\Support\Facades\Route;
use Plugins\Domains\src\Controllers\DomainController;

Route::middleware(['auth:admin', 'admin.permission:domains,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('domains', DomainController::class);
});
