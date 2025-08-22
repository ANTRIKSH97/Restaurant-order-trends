<?php

use Illuminate\Support\Facades\Route;

Route::get('/restaurants', function () {
    $path = storage_path('app/restaurants.json');
    return response()->json(json_decode(file_get_contents($path), true));
});

Route::get('/orders', function () {
    $path = storage_path('app/orders.json');
    return response()->json(json_decode(file_get_contents($path), true));
});
