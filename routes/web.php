<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SecurityLoginController;
use App\Http\Controllers\SecurityDashboardController;

// Ruta principal (Menú con los 3 botones)
Route::get('/', function () {
    return view('welcome');
});

// --- Rutas del Monitor de Servidor ---
Route::get('/servidor', [ServerController::class, 'index']);

// --- Rutas del TP de Logging/Seguridad ---
Route::get('/security-login', function () {
    return view('security-login');
})->middleware('guest');

Route::post('/security-login', [SecurityLoginController::class, 'login'])
    ->middleware('guest');

// Panel de seguridad. 
// Nota: Para la entrega final, se recomienda agregar el middleware de auth y permisos.
Route::get('/security-dashboard', [SecurityDashboardController::class, 'index'])->name('security.dashboard');