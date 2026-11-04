<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;

class SecurityDashboardController extends Controller
{
    public function index()
    {
        $since = now()->subHours(24);
        $stats = [];

        // Contamos éxitos, fallos y bloqueos en las últimas 24hs
        foreach (['success', 'failed', 'blocked'] as $status) {
            $stats[$status] = LoginAttempt::where('status', $status)
                ->where('created_at', '>=', $since)
                ->count();
        }

        // Buscamos IPs con 3 o más fallos en los últimos 5 minutos
        $suspiciousIps = LoginAttempt::select('ip_address')
            ->selectRaw('COUNT(*) as total')
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('total')
            ->get();

        return view('security-dashboard', compact('stats', 'suspiciousIps'));
    }
}