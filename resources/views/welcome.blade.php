<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Anthony's | Sistema de Pedidos</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-gray-100 selection:bg-red-500 selection:text-white">

            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        {{-- Si el usuario está logeado, va al Dashboard --}}
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                    @else
                        {{-- Si no está logeado, ve a Login y Register --}}
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Iniciar Sesión</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Registrarse</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-7xl mx-auto p-6 lg:p-8 text-center">

                <h1 class="text-6xl font-bold text-gray-800 tracking-tight mb-4">
                   Pasteleria Anthony's
                </h1>

                <h2 class="text-3xl font-semibold text-gray-600 mb-8">
                    Sistema Centralizado de Pedidos
                </h2>

                <div class="mt-10 flex justify-center space-x-6">
                <a href="{{ route('login') }}" class="px-8 py-3 text-lg font-medium text-white
                    bg-[#522d6d] rounded-lg shadow-lg hover:bg-[#412457]
                    transition duration-150 ease-in-out transform hover:scale-105">Ingresar al Sistema
                    </a>


                </div>

                <footer class="mt-20 text-sm text-gray-500">
                    &copy; {{ date('Y') }} Pasteleria Anthony's. Todos los derechos reservados.
                </footer>
            </div>
        </div>
    </body>
</html>
