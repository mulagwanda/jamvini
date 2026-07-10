<?php

use App\Http\Controllers\Api\V1\CoreApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('jamvini.api')->group(function () {
    Route::get('/clients', [CoreApiController::class, 'clients']);
    Route::post('/clients', [CoreApiController::class, 'createClient']);
    Route::get('/invoices', [CoreApiController::class, 'invoices']);
    Route::get('/services', [CoreApiController::class, 'services']);
    Route::get('/domains', [CoreApiController::class, 'domains']);
    Route::get('/tickets', [CoreApiController::class, 'tickets']);
    Route::post('/local', [CoreApiController::class, 'action']);
});
