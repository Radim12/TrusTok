<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentimentController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('home');
});

Route::post('/analyze-sentiment', [SentimentController::class, 'handleSentiment']);

Route::post(
    '/filter-product',
    [ProductController::class, 'getProductMetrics']
)->name('product.metrics');


Route::get('/test-api', function () {

    $response = Http::post(
        'https://trusttok-api-260465484877.asia-southeast2.run.app/get-metrics',
        [
            'product_name' => 'wardah'
        ]
    );

    dd($response->json());
});
