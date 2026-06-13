<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentimentController;

Route::get('/', function () {
    return view('home');
});

Route::post('/analyze-sentiment', [SentimentController::class, 'handleSentiment']);
Route::post('/filter-product', [ProductController::class, 'getProductMetrics'])->name('product.metrics');
