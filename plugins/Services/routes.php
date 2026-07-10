<?php

use Illuminate\Support\Facades\Route;
use Plugins\Services\src\Controllers\ServiceController;

Route::middleware(['auth:admin', 'admin.permission:services,auto'])->prefix('admin')->name('admin.')->group(function () {
    // Service catalog
    Route::resource('services', ServiceController::class);
    
    // Service groups
    Route::get('/service-groups', [ServiceController::class, 'groups'])->name('services.groups');
    Route::post('/service-groups', [ServiceController::class, 'storeGroup'])->name('services.groups.store');
    Route::put('/service-groups/{group}', [ServiceController::class, 'updateGroup'])->name('services.groups.update');
    Route::get('/service-groups/{group}/edit', [ServiceController::class, 'editGroup'])->name('services.groups.edit');
    Route::delete('/service-groups/{group}', [ServiceController::class, 'destroyGroup'])->name('services.groups.destroy');

    // Servers
    Route::get('/servers', [ServiceController::class, 'servers'])->name('services.servers');
    Route::post('/servers', [ServiceController::class, 'storeServer'])->name('services.servers.store');
    Route::put('/servers/{server}', [ServiceController::class, 'updateServer'])->name('services.servers.update');
    Route::post('/servers/{server}/test', [ServiceController::class, 'testServer'])->name('services.servers.test');
    Route::post('/servers/{server}/sync-packages', [ServiceController::class, 'syncServerPackages'])->name('services.servers.sync-packages');
    Route::get('/servers/{server}/packages', [ServiceController::class, 'serverPackages'])->name('services.servers.packages');
    Route::delete('/servers/{server}', [ServiceController::class, 'destroyServer'])->name('services.servers.destroy');
    
    // Module config
    Route::get('/module-config/{module}', [ServiceController::class, 'moduleConfig'])->name('services.module-config');
});
