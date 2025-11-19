<x-app-layout>
    {{-- La variable $header se define aquí usando x-slot --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agregar Productos a Cesta: ') . $categoryName }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="orderForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Bloque de Mensajes de Sesión (Asegúrate de que tu app.blade.php también los muestre) -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            <!-- Fin Bloque de Mensajes de Sesión -->

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 flex justify-between items-center">
                    <span>Línea: {{ $categoryName }}</span>
                    <!-- Indicador de Cesta que usa la variable $cartCount pasada desde el controlador -->
                    <span class="text-sm font-bold text-indigo-600 border border-indigo-200 rounded-full py-1 px-3">
                        Cesta: {{ $cartCount ?? 0 }} ítems guardados
                    </span>
                </h2>
                <p class="text-gray-600 mb-6">
                    Añade las cantidades necesarias. Al guardar, podrás seleccionar productos de otra línea para un solo pedido.
                </p>

                <!-- 🚨 FORMULARIO DE AÑADIR A CESTA: Ahora apunta a orders.addItem -->
                <form method="POST" action="{{ route('orders.addItem') }}">
                    @csrf

                    <!-- Campo oculto para la línea de producción -->
                    <input type="hidden" name="line_number" value="{{ $lineNumber }}">

                    <!-- 1. Buscador de Productos -->
                    <div class="mb-4">
                        <label for="search" class="block text-sm font-medium text-gray-700">Buscar Producto (Código / Nombre)</label>
                        <input type="text"
                                id="search"
                                x-model="searchText"
                                @input="filterProducts"
                                placeholder="Escribe el código o nombre para filtrar..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-lg">
                    </div>

                    <!-- Mensajes de Error Generales -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 rounded-lg">
                            <strong class="font-bold">¡Error!</strong>
                            <span class="block sm:inline">Por favor, revisa los campos marcados o si la cesta está vacía.</span>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- 2. Tabla de Productos y Cantidades -->
                    <div class="overflow-x-auto border-gray-200 rounded-lg shadow-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-[#522d6d]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nombre del Producto</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Existencias Actuales</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider w-1/5">Cantidad a Solicitar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(product, index) in filteredProducts" :key="product.code">
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900" x-text="product.code"></td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800" x-text="product.name"></td>
                                        <td class="px-4 py-2 text-center whitespace-nowrap text-sm text-gray-800" x-text="product.stock"></td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <!-- Campo de Cantidad: Usa x-bind:value para prellenar si hay data en la sesión -->
                                            <input type="number"
                                                :name="'quantities[' + product.code + '][quantity]'"
                                                min="0"
                                                placeholder="0"
                                                x-model.number="product.quantity"
                                                class="w-full text-center border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                                :class="{'border-red-500': hasErrorForProduct(product.code)}">

                                            <!-- Campo oculto para asegurar que el código de producto se envíe -->
                                            <input type="hidden"
                                                     :name="'quantities[' + product.code + '][product_code]'"
                                                     :value="product.code">
                                        </td>
                                    </tr>
                                </template>

                                    <!-- Fila cuando no hay resultados de búsqueda -->
                                <template x-if="filteredProducts.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-800">
                                            No se encontraron productos que coincidan con la búsqueda.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-between items-center">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Guardar Línea y Volver a Categorías
                        </button>



                        <a href="{{ route('orders.createIndex') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                           Volver a Categorías
                        </a>
                    </div>
                </form>

                <!-- 🚨 FORMULARIO DE FINALIZACIÓN: Solo se muestra si hay ítems en la cesta ($cartCount > 0) -->
                @if ($cartCount > 0)
                <div class="mt-10 pt-4 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        Revisión Final del Pedido Unificado ({{ $cartCount }} productos)
                    </h3>

                    <form method="POST" action="{{ route('orders.store') }}" class="space-y-4">
                        @csrf

                        <!-- Campo de Notas Opcional -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notas Adicionales para el Pedido (Opcional)</label>
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-lg"></textarea>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            FINALIZAR Y ENVIAR PEDIDO ÚNICO
                        </button>
                    </form>
                </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        function orderForm() {
            return {
                // products ahora incluye la cantidad pre-seleccionada (si existe en la cesta)
                products: @json($products),
                filteredProducts: @json($products),
                searchText: '',

                // Función para filtrar la tabla
                filterProducts() {
                    const search = this.searchText.toLowerCase();
                    if (search === '') {
                        this.filteredProducts = this.products;
                    } else {
                        this.filteredProducts = this.products.filter(product => {
                            // Filtra por código o nombre
                            return product.code.toLowerCase().includes(search) ||
                                    product.name.toLowerCase().includes(search);
                        });
                    }
                },

                // Esta función se mantiene para evitar errores de Alpine, pero la validación principal
                // ocurre ahora en el backend (orders.addItem).
                hasErrorForProduct(productCode) {
                    return false;
                }
            }
        }
    </script>
</x-app-layout>
