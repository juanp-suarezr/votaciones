<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo ?? 'Ruta bloqueada' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-xl rounded-2xl p-10 max-w-md text-center">
        <div class="flex justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M12 9v2m0 4v.01M9 16h6m2 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-2xl font-semibold text-gray-800 mb-2">{{ $titulo ?? 'Ruta bloqueada' }}</h1>
        <p class="text-gray-600 mb-6">{{ $mensaje ?? 'Esta sección está temporalmente inactiva. Intente nuevamente más tarde.' }}</p>
        <a href="{{ url('/') }}" class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
            Volver al inicio
        </a>
    </div>
</body>
</html>
