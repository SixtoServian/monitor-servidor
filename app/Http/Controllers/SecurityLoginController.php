<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityLoginController extends Controller
{
    public function login(Request $request)
    {
        // Validación básica
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $cacheKey = 'security:block:' . hash('sha256', $ip);

        // 1. Verificar si la IP ya está bloqueada
        if (Cache::has($cacheKey)) {
            $this->record($request, 'blocked');
            Log::channel('security')->warning('Intento desde IP bloqueada', ['ip' => $ip]);
            
            return response()->json([
                'message' => 'Acceso temporalmente restringido. Intente más tarde.'
            ], 429);
        }

        // 2. Intentar autenticar
        if (Auth::attempt($data)) {
            $request->session()->regenerate();
            $this->record($request, 'success');
            
            Log::channel('security')->info('Inicio de sesión exitoso', [
                'user_id' => Auth::id(), 
                'ip' => $ip
            ]);
            
            // Redirigimos al panel de control o monitor
            return redirect()->intended('/servidor')->with('success', 'Autenticación correcta');
        }

        // 3. Si falló, registrar el fallo
        $this->record($request, 'failed');

        // 4. Contar fallos de esta IP en los últimos 5 minutos
        $count = LoginAttempt::where('ip_address', $ip)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        Log::channel('security')->warning('Autenticación fallida', [
            'ip' => $ip, 
            'failed_attempts' => $count
        ]);

        // 5. Aplicar reglas de seguridad según la cantidad de fallos
        if ($count >= 5) {
            // Bloqueo de 10 minutos
            Cache::put($cacheKey, true, now()->addMinutes(10));
            Log::channel('security')->critical('Bloqueo temporal por intentos fallidos', [
                'ip' => $ip, 
                'failed_attempts' => $count, 
                'duration_minutes' => 10
            ]);
        } elseif ($count >= 3) {
            // Alerta por posible ataque
            Log::channel('security')->alert('Posible ataque de fuerza bruta', [
                'ip' => $ip, 
                'failed_attempts' => $count
            ]);
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    /**
     * Guarda el intento en la base de datos.
     */
    private function record(Request $request, string $status): void
    {
        LoginAttempt::create([
            'identifier' => mb_strtolower($request->input('email', '')),
            'ip_address' => $request->ip(),
            'status' => $status,
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            'created_at' => now(),
        ]);
    }
}