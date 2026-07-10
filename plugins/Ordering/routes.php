<?php

use Illuminate\Support\Facades\Route;
use Plugins\Ordering\src\Controllers\OrderController;

// Public ordering pages
Route::get('/domains', [OrderController::class, 'domains'])->name('order.domains');
Route::post('/check-domain', [OrderController::class, 'checkDomain'])->name('order.check');
Route::get('/hosting', [OrderController::class, 'services'])->name('order.services');
Route::get('/services', [OrderController::class, 'services'])->name('order.services.catalog');
Route::post('/cart/add', [OrderController::class, 'addToCart'])->name('order.cart.add');
Route::get('/cart', [OrderController::class, 'cart'])->name('order.cart');
Route::post('/cart/update', [OrderController::class, 'updateCart'])->name('order.cart.update');
Route::post('/cart/coupon', [OrderController::class, 'applyCoupon'])->name('order.cart.coupon');
Route::post('/cart/coupon/remove', [OrderController::class, 'removeCoupon'])->name('order.cart.coupon.remove');
Route::post('/cart/remove', [OrderController::class, 'removeFromCart'])->name('order.cart.remove');
Route::get('/checkout', [OrderController::class, 'checkout'])->name('order.checkout');
Route::post('/checkout', [OrderController::class, 'placeOrder'])->name('order.place');
Route::get('/order/confirmation/{order}', [OrderController::class, 'confirmation'])->name('order.confirmation');
