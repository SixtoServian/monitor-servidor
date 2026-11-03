<?php

namespace App\Http\Controllers;

use App\Services\ServerMetrics;

class ServerController extends Controller
{
    /**
     * Devuelve la vista web con los datos del servidor.
     * Laravel inyecta automáticamente ServerMetrics.
     */
    public function index(ServerMetrics $metrics)
    {
        return view('servidor', ['data' => $metrics->snapshot()]);
    }

    /**
     * Devuelve los datos en formato JSON (lo usaremos en el Paso 4).
     */
    public function api(ServerMetrics $metrics)
    {
        return response()->json($metrics->snapshot());
    }
}