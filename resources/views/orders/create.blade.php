<x-app-layout>
    {{-- La variable $header se define aquí usando x-slot --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Pedido Masivo: ') . $categoryName }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="orderForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                    Crear Pedido: {{ $categoryName }}
                </h2>
                <p class="text-gray-600 mb-6">
                    Selecciona las cantidades necesarias de los productos de esta línea.
                </p>

                <!-- Formulario de Pedido Masivo -->
                <form method="POST" action="{{ route('orders.store') }}">
                    @csrf

                    <!-- Campo oculto para la línea de producción -->
                    <input type="hidden" name="line_number" value="{{ $categoryId }}">

                    <!-- 1. Buscador de Productos -->
                    <div class="mb-4">
                        <label for="search" class="block text-sm font-medium text-gray-700">Buscar Producto (Código / Nombre)</label>
                        <input type="text"
                                id="search"
                                x-model="searchText"
                                @input="filterProducts"
                                placeholder="Escribe el código o nombre para filtrar..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Mensajes de Error Generales -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">¡Error!</strong>
                            <span class="block sm:inline">Por favor, revisa los campos marcados.</span>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- 2. Tabla de Productos y Cantidades -->
                    <div class="overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del Producto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Existencias Actuales</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Cantidad a Solicitar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Alpine.js se encarga de mostrar la lista filtrada -->
                                <template x-for="(product, index) in filteredProducts" :key="product.code">
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900" x-text="product.code"></td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800" x-text="product.name"></td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500" x-text="product.stock"></td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <!-- Campo de Cantidad con el nombre del array -->
                                            <input type="number"
                                                    :name="'quantities[' + product.code + '][quantity]'"
                                                    min="0"
                                                    placeholder="0"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2 text-center"
                                                    :class="{'border-red-500': hasErrorForProduct(product.code)}">

                                            <!-- Campo oculto para asegurar que el código de producto se envíe incluso si la cantidad es 0 -->
                                            <input type="hidden"
                                                    :name="'quantities[' + product.code + '][product_code]'"
                                                    :value="product.code">
                                        </td>
                                    </tr>
                                </template>

                                    <!-- Fila cuando no hay resultados de búsqueda -->
                                <template x-if="filteredProducts.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                            No se encontraron productos que coincidan con la búsqueda.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Crear Pedido Masivo
                        </button>
                        <a href="{{ route('orders.createIndex') }}" class="ml-4 text-sm text-gray-600 hover:text-gray-900">
                            Cancelar y Volver a Categorías
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        function orderForm() {
            return {
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
                            return product.code.toLowerCase().includes(search) ||
                                    product.name.toLowerCase().includes(search);
                        });
                    }
                },

                // Función para marcar errores (opcional, si hay errores en la sesión)
                hasErrorForProduct(productCode) {
                    // Nota: Esta parte solo verifica si hay un error general en 'quantities'.
                    // Para una validación más precisa por producto, necesitarías pasar el objeto $errors
                    // de forma más explícita a Alpine.
                    @error('quantities')
                        const errors = {!! json_encode($errors->get('quantities')) !!};
                        if (errors.length > 0 && errors[0].includes(productCode)) {
                            return true;
                        }
                    @enderror
                    return false;
                }
            }
        }
    </script>
</x-app-layout>
