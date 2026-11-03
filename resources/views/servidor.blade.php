<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor del Servidor</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800 p-6 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-2 text-gray-900">Monitor del servidor</h1>
        <p class="text-gray-600 mb-8">Última lectura: <span id="at" class="font-mono bg-gray-200 px-2 py-1 rounded text-sm">{{ $data['at'] }}</span></p>

        <!-- Grilla responsiva: 1 columna en celular, 2 en tablet, 4 en PC -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="text-sm text-gray-500 font-medium mb-1">Nombre del equipo</div>
                <div class="text-xl font-bold truncate">{{ $data['hostname'] }}</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="text-sm text-gray-500 font-medium mb-1">Sistema operativo</div>
                <div class="text-xl font-bold truncate">{{ $data['os'] }}</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="text-sm text-gray-500 font-medium mb-1">Versión de PHP</div>
                <div class="text-xl font-bold">{{ $data['php'] }}</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="text-sm text-gray-500 font-medium mb-1">Disco usado (%)</div>
                <div class="text-2xl font-bold text-blue-600" id="disk">{{ $data['disk']['used_pct'] }}%</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="text-sm text-gray-500 font-medium mb-1">Memoria usada (%)</div>
                <div class="text-2xl font-bold text-purple-600" id="memory">
                    {{ $data['memory']['used_pct'] !== null ? $data['memory']['used_pct'] . '%' : 'No disponible' }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="text-sm text-gray-500 font-medium mb-1">Uso de CPU (%)</div>
                <div class="text-2xl font-bold text-orange-500" id="cpu">
                    {{ $data['cpu_pct'] !== null ? $data['cpu_pct'] . '%' : 'No disponible' }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="text-sm text-gray-500 font-medium mb-1">Temperatura CPU (°C)</div>
                <div class="text-2xl font-bold text-red-500" id="temp">
                    {{ $data['cpu_temp_c'] !== null ? $data['cpu_temp_c'] . ' °C' : 'No disponible' }}
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="text-sm text-gray-500 font-medium mb-1">Encendido hace (seg)</div>
                <div class="text-2xl font-bold text-gray-700" id="uptime">
                    {{ $data['uptime_s'] ?? 'No disponible' }}
                </div>
            </div>

        </div>
        
        <!-- En el Paso 5 agregaremos el JS acá -->
    </div>
</body>
</html>