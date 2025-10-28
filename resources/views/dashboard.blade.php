<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- SECCIÓN DE BIENVENIDA Y MENSAJE DE EMPRESA -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8 p-6 border-b-4 border-yellow-500">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">
                    Bienvenido, {{ Auth::user()->name }}
                </h3>

                @if (Auth::user()->role === 'manager')
                    <p class="text-gray-600">
                        ¡Gracias por gestionar los pedidos de la sucursal **{{ Auth::user()->branch->name ?? 'sin asignar' }}**! Tu labor asegura la calidad y disponibilidad de nuestros productos frescos.
                    </p>
                @elseif (Auth::user()->role === 'production')
                    <p class="text-gray-600">
                        Área de Producción. Tu enfoque en la eficiencia y la excelencia es lo que distingue a Anthony's. ¡Revisa los pedidos pendientes!
                    </p>
                @else
                    <p class="text-gray-600">
                        Administración Central. Mantén el sistema y el catálogo de Anthony's actualizado para todas las sucursales.
                    </p>
                @endif
            </div>

            <!-- SECCIÓN DE ACCESOS RÁPIDOS POR ROL -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- 1. MÓDULO DE PEDIDOS (Visible para todos) -->
                <a href="{{ route('orders.index') }}" class="block p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-[1.02]">
                    <div class="flex items-center space-x-4">
                        <span class="text-4xl text-blue-600">📦</span>
                        <div>
                            <p class="text-xl font-semibold text-blue-800">Gestión de Pedidos</p>
                            <p class="text-sm text-gray-500">
                                @if (Auth::user()->role === 'manager') Crear y consultar mis pedidos. @else Ver y procesar pedidos de sucursales. @endif
                            </p>
                        </div>
                    </div>
                </a>

                <!-- 2. MÓDULO ADMINISTRATIVO (Solo Admin y Production) -->
                @if (in_array(Auth::user()->role, ['admin', 'production']))
                    <a href="{{ route('admin.products.index') }}" class="block p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-[1.02]">
                        <div class="flex items-center space-x-4">
                            <span class="text-4xl text-green-600">🍰</span>
                            <div>
                                <p class="text-xl font-semibold text-green-800">Catálogo de Productos</p>
                                <p class="text-sm text-gray-500">Añadir, editar y desactivar productos e insumos.</p>
                            </div>
                        </div>
                    </a>
                @endif

                <!-- 3. GESTIÓN DE CATEGORÍAS (Solo Admin y Production) -->
                @if (in_array(Auth::user()->role, ['admin', 'production']))
                    <a href="{{ route('admin.categories.index') }}" class="block p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-[1.02]">
                        <div class="flex items-center space-x-4">
                            <span class="text-4xl text-purple-600">📋</span>
                            <div>
                                <p class="text-xl font-semibold text-purple-800">Mantenimiento de Categorías</p>
                                <p class="text-sm text-gray-500">Organizar categorías de productos (Panadería, Repostería, etc.).</p>
                            </div>
                        </div>
                    </a>
                @endif

                <!-- 4. GESTIÓN DE SUCURSALES Y USUARIOS (Solo Admin) -->
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.branches.index') }}" class="block p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-[1.02]">
                        <div class="flex items-center space-x-4">
                            <span class="text-4xl text-red-600">🏢</span>
                            <div>
                                <p class="text-xl font-semibold text-red-800">Sucursales</p>
                                <p class="text-sm text-gray-500">Configurar sucursales.</p>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.users.index') }}" class="block p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-[1.02]">
                        <div class="flex items-center space-x-4">
                                <span class="text-4xl text-cyan-600">🧑</span>
                            <div>
                                <p class="text-xl font-semibold text-cyan-800">Gestión de Usuarios</p>
                                <p class="text-sm text-gray-500">Asignar roles y sucursales a los empleados.</p>
                            </div>
                        </div>
                    </a>
                @endif

                <!-- 5. INFORMACIÓN ADICIONAL (Visible para todos) -->
                 <div class="p-6 bg-white rounded-xl shadow-lg">
                    <div class="flex items-center space-x-4">
                        <span class="text-4xl text-yellow-600">💡</span>
                        <div>
                            <p class="text-xl font-semibold text-yellow-800">Misión Anthony's</p>
                            <p class="text-sm text-gray-500">Asegurar la calidad artesanal y el servicio a cada cliente de nuestra cadena.</p>
                        </div>
                    </div>
                </div>

            </div>
            <!-- FIN DE ACCESOS RÁPIDOS -->

            <div class="mt-8 text-center text-sm text-gray-500">
                Tu rol actual es: <span class="font-semibold capitalize text-gray-700">{{ Auth::user()->role }}</span>
            </div>

        </div>
    </div>
</x-app-layout>
