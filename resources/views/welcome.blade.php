<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto - Programación IV</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl p-10 text-center">
        <img src="https://laravel.com/img/notification-logo.png" alt="Laravel Logo" class="h-20 mx-auto mb-6">
        
        <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Trabajo Práctico Integrador</h1>
        <p class="text-gray-500 mb-10">Monitor de Servidor y Logging de Seguridad</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Botón Monitor -->
            <a href="/servidor" class="group block p-6 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition">
                <div class="text-4xl mb-4">💻</div>
                <h2 class="text-lg font-bold text-blue-800 group-hover:text-blue-900">Monitor del Servidor</h2>
                <p class="text-sm text-blue-600 mt-2">Ver métricas de RAM, CPU, Disco y API JSON.</p>
            </a>

            <!-- Botón Login Seguridad -->
            <a href="/security-login" class="group block p-6 bg-purple-50 border border-purple-200 rounded-xl hover:bg-purple-100 transition">
                <div class="text-4xl mb-4">🔒</div>
                <h2 class="text-lg font-bold text-purple-800 group-hover:text-purple-900">Login de Seguridad</h2>
                <p class="text-sm text-purple-600 mt-2">Probar autenticación y bloqueo por fuerza bruta.</p>
            </a>

            <!-- Botón Dashboard -->
            <a href="/security-dashboard" class="group block p-6 bg-green-50 border border-green-200 rounded-xl hover:bg-green-100 transition">
                <div class="text-4xl mb-4">📊</div>
                <h2 class="text-lg font-bold text-green-800 group-hover:text-green-900">Panel de Seguridad</h2>
                <p class="text-sm text-green-600 mt-2">Ver estadísticas de intentos y alertas.</p>
            </a>
        </div>
        
        <div class="mt-12 text-sm text-gray-400">
            Desarrollado para la cátedra de Programación IV - UTN
        </div>
    </div>
</body>
</html>