<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;

class SecurityDashboardController extends Controller
{
    public function index(Request $request)
    {
        $since = now()->subHours(24);
        $stats = [];

        // 1. Contadores de las últimas 24hs
        foreach (['success', 'failed', 'blocked'] as $status) {
            $stats[$status] = LoginAttempt::where('status', $status)
                ->where('created_at', '>=', $since)
                ->count();
        }

        // 2. IPs sospechosas (3+ fallos en 5 min)
        $suspiciousIps = LoginAttempt::select('ip_address')
            ->selectRaw('COUNT(*) as total')
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('total')
            ->get();

        // 3. Listado de eventos con filtros
        $query = LoginAttempt::query();

        // Filtro por resultado (success, failed, blocked)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por dirección IP
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->ip . '%');
        }

        // Traemos los últimos 50 eventos ordenados por fecha descendente
        $events = $query->orderByDesc('created_at')->limit(50)->get();

        return view('security-dashboard', compact('stats', 'suspiciousIps', 'events'));
    }
}