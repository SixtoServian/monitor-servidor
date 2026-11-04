<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Seguridad</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Panel de Monitoreo de Seguridad</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Éxitos (24h)</h3>
                <p class="text-3xl font-bold text-green-600">{{ $stats['success'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-yellow-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Fallidos (24h)</h3>
                <p class="text-3xl font-bold text-yellow-600">{{ $stats['failed'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-500">
                <h3 class="text-gray-500 text-sm font-bold uppercase">Bloqueados (24h)</h3>
                <p class="text-3xl font-bold text-red-600">{{ $stats['blocked'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">IPs Sospechosas (3+ fallos en 5 min)</h2>
            </div>
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 font-semibold text-gray-600">Dirección IP</th>
                        <th class="p-4 font-semibold text-gray-600">Total Fallos</th>
                        <th class="p-4 font-semibold text-gray-600">Clasificación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suspiciousIps as $item)
                        <tr class="border-b last:border-0">
                            <td class="p-4 font-mono text-sm">{{ $item->ip_address }}</td>
                            <td class="p-4">{{ $item->total }}</td>
                            <td class="p-4">
                                @if($item->total >= 5)
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">CRÍTICO</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">ALERTA</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-6 text-center text-gray-500">No hay IPs sospechosas registradas en este momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-8 text-center">
            <a href="/" class="text-blue-500 hover:underline">Volver al inicio</a>
        </div>
    </div>
</body>
</html>