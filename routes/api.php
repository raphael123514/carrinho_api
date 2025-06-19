<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::resources([
    'cart-items' => \App\Http\Controllers\CartItemsController::class
]);

Route::post('payment', [\App\Http\Controllers\PaymentController::class, 'process'])
    ->name('payment.process');
