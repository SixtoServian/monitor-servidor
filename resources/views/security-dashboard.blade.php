<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad de Accesos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Seguridad de accesos</h1>
                <p class="text-gray-500 text-sm mt-1">Actividad de autenticación - registro en Base de Datos</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Actualizado: <span id="last-update">{{ now()->format('d/m/Y H:i:s') }}</span></p>
                <a href="{{ route('security.dashboard') }}" class="text-sm text-blue-600 hover:underline">Actualizar ahora</a>
            </div>
        </div>

        <!-- Tarjetas de resumen -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg border-l-4 border-green-500 shadow-sm p-6">
                <h3 class="text-gray-500 text-sm font-semibold uppercase">Ingresos exitosos</h3>
                <p class="text-4xl font-bold text-gray-800 mt-2">{{ $stats['success'] }}</p>
                <p class="text-xs text-gray-400 mt-2">Últimas 24 horas</p>
            </div>
            <div class="bg-white rounded-lg border-l-4 border-yellow-400 shadow-sm p-6">
                <h3 class="text-gray-500 text-sm font-semibold uppercase">Intentos fallidos</h3>
                <p class="text-4xl font-bold text-gray-800 mt-2">{{ $stats['failed'] }}</p>
                <p class="text-xs text-gray-400 mt-2">Últimas 24 horas</p>
            </div>
            <div class="bg-white rounded-lg border-l-4 border-red-500 shadow-sm p-6">
                <h3 class="text-gray-500 text-sm font-semibold uppercase">Rechazos por bloqueo</h3>
                <p class="text-4xl font-bold text-gray-800 mt-2">{{ $stats['blocked'] }}</p>
                <p class="text-xs text-gray-400 mt-2">Últimas 24 horas</p>
            </div>
        </div>

        <!-- Tabla de IPs Sospechosas -->
        <div class="bg-white rounded-lg shadow-sm mb-8 overflow-hidden">
            <div class="bg-red-50 px-6 py-3 border-b border-red-100">
                <h2 class="text-sm font-bold text-red-800 uppercase">IPs con actividad sospechosa (últimos 5 minutos)</h2>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-6 py-3 font-semibold">IP</th>
                        <th class="px-6 py-3 font-semibold">Fallos en 5 minutos</th>
                        <th class="px-6 py-3 font-semibold">Clasificación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suspiciousIps as $item)
                        <tr class="border-b last:border-0 hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono">{{ $item->ip_address }}</td>
                            <td class="px-6 py-4">{{ $item->total }}</td>
                            <td class="px-6 py-4">
                                @if($item->total >= 5)
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">CRÍTICO</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">ALERTA</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">No se detectaron patrones sospechosos en los últimos 5 minutos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tabla de Eventos de Acceso con Filtros -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                <h2 class="text-sm font-bold text-gray-700 uppercase">Eventos de acceso</h2>
                <span class="text-xs text-gray-500">Actualización automática cada 10 segundos</span>
            </div>

            <!-- Formulario de Filtros -->
            <div class="p-4 bg-white border-b">
                <form method="GET" action="{{ route('security.dashboard') }}" class="flex gap-4 items-center">
                    <select name="status" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos los resultados</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Éxito</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Fallido</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                    <input type="text" name="ip" placeholder="Filtrar por IP" value="{{ request('ip') }}" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded text-sm font-semibold hover:bg-pink-700 transition">Filtrar</button>
                    @if(request()->has('status') || request()->has('ip'))
                        <a href="{{ route('security.dashboard') }}" class="text-sm text-gray-500 hover:underline">Limpiar</a>
                    @endif
                </form>
            </div>

            <!-- Tabla de Eventos -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Fecha</th>
                            <th class="px-6 py-3 font-semibold">Resultado</th>
                            <th class="px-6 py-3 font-semibold">Cuenta</th>
                            <th class="px-6 py-3 font-semibold">Dirección IP</th>
                            <th class="px-6 py-3 font-semibold">Navegador / Cliente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-3 font-mono text-xs">{{ \Carbon\Carbon::parse($event->created_at)->format('d/m/Y H:i:s') }}</td>
                                <td class="px-6 py-3">
                                    @if($event->status == 'success')
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Éxito</span>
                                    @elseif($event->status == 'failed')
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">Fallido</span>
                                    @else
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Bloqueado</span>
                                    @endif
                                </td>
                                <!-- Enmascaramos el correo para que se vea como la captura del compañero -->
                                <td class="px-6 py-3 text-purple-600 font-mono text-xs">
                                    {{ $event->identifier ? substr(hash('sha256', $event->identifier), 0, 12) : 'Desconocido' }}
                                </td>
                                <td class="px-6 py-3 text-red-500 font-mono text-xs">{{ $event->ip_address }}</td>
                                <td class="px-6 py-3 text-gray-500 truncate max-w-xs" title="{{ $event->user_agent }}">
                                    {{ $event->user_agent }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">No hay eventos registrados con estos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script para actualización automática (Polling cada 10 segundos) -->
    <script>
        setInterval(function() {
            // Solo recarga si el usuario no está escribiendo en un input
            if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'SELECT') {
                window.location.reload();
            }
        }, 10000); // 10 segundos
    </script>
</body>
</html>