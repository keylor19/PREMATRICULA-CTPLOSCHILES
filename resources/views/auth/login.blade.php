<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio de Sesión Matrícula CTP Los Chiles</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-green-800 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Card principal --}}
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

            {{-- Encabezado con logo --}}
            <div class="bg-gradient-to-br from-blue-900 to-green-800 px-8 py-8 text-center">
                <img
                    src="{{ asset('images/logo-ctp.jpg') }}"
                    alt="Logo CTP Los Chiles"
                    class="w-28 h-28 mx-auto mb-4 rounded-full border-4 border-white shadow-lg object-cover"
                >
                <h1 class="text-white text-xl font-bold leading-tight">
                    Colegio Técnico Profesional
                </h1>
                <h2 class="text-white text-xl font-bold">
                    de Los Chiles
                </h2>
                <p class="text-blue-200 text-sm mt-2">Sistema de Matrícula</p>
            </div>

            {{-- Formulario --}}
            <div class="px-8 py-8">
                <h3 class="text-gray-800 text-lg font-semibold text-center mb-6">
                    Bienvenido — Iniciá sesión
                </h3>

                {{-- Errores de sesión --}}
                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4"
                            placeholder="correo@colegio.ed.cr"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4"
                            placeholder="••••••••"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            Recordarme
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 px-4 rounded-lg text-sm transition-colors duration-150">
                        Iniciar sesión
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="px-8 pb-6 text-center">
                <p class="text-xs text-gray-400">
                    © {{ date('Y') }} CTP Los Chiles — Sistema de Matrícula
                </p>
            </div>
        </div>
    </div>

</body>
</html>