<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


use App\Http\Controllers\ServerController;

// Endpoint de métricas con límite de 60 peticiones por minuto
Route::get('/metrics', [ServerController::class, 'api'])->middleware('throttle:60,1');