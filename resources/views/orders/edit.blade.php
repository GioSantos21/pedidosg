<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Pedido') }} #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensajes de Sesión -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div x-data="{
                items: @js($orderItems),
                allProducts: @js($products),
                categories: @js($categories),
                notes: '{{ $order->notes }}',

                // Nuevos estados para el filtrado
                selectedCategory: '',
                searchTerm: '',
                selectedProductId: '',

                    // COMPUTED PROPERTY para filtrar productos
                    filteredProducts() {
                        // Limpia el término de búsqueda una sola vez
                        const searchLower = this.searchTerm.toLowerCase().trim();

                        const filtered = this.allProducts
                            .filter(product => {
                                // 1. Filtrar por categoría
                                const categoryMatch = this.selectedCategory === '' || product.category_id == this.selectedCategory;

                                // 2. Filtrar por término de búsqueda (código o nombre) - APLICACIÓN DE .trim()
                                const productCodeLower = product.product_code ? product.product_code.toLowerCase().trim() : '';
                                const productNameLower = product.name ? product.name.toLowerCase().trim() : '';

                                const searchMatch = searchLower === '' ||
                                    productCodeLower.includes(searchLower) ||
                                    productNameLower.includes(searchLower);

                                // 3. Ocultar productos que ya están en el pedido
                                const itemExists = this.items.some(item => item.product_id == product.id);

                                return categoryMatch && searchMatch && !itemExists;
                            })
                            .slice(0, 100);

                        // Lógica de Autoselección (Solución al Problema 1)
                        if (searchLower !== '' && filtered.length === 1) {
                            // Si solo hay un resultado, lo autoselecciona
                            this.selectedProductId = filtered[0].id;
                        } else if (searchLower === '' || filtered.length === 0) {
                            // Si la búsqueda se vacía o no hay resultados, deseleccionar
                            this.selectedProductId = '';
                        } else if (filtered.length > 1) {
                            // Si hay más de uno, asegurarse de que no haya una autoselección incorrecta
                            if (!filtered.some(p => p.id == this.selectedProductId)) {
                                this.selectedProductId = '';
                            }
                        }

                        return filtered;
                    },



                addItem(id) {
                    const product = this.allProducts.find(p => p.id == id);
                    if (!product || this.items.some(item => item.product_id == id)) return;

                    this.items.push({
                        product_id: product.id,
                        name: product.name,
                        unit: product.unit,
                        quantity: 1
                    });

                    // Limpiar y resetear la búsqueda después de añadir
                    this.selectedProductId = '';
                    this.searchTerm = '';
                    this.selectedCategory = '';// Limpiar la selección y la búsqueda
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                validateQuantities() {
                    return this.items.length > 0 && this.items.every(item => item.quantity > 0);
                }
            }" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <!-- INFORMACIÓN DE LA ORDEN -->
                <div class="mb-6 border-b pb-4">
                    <p class="text-sm font-bold text-lg text-gray-900">
                    Sucursal:
                    <span class="text-purple-700">{{ $order->branch->name }}</span> |

                    Solicitante:
                    <span class="text-purple-700">{{ $order->user->name }}</span> |

                    Fecha:
                    <span class="text-purple-700">{{ $order->created_at->format('d/m/Y - H:i') }}</span>
                </p>
                    <p class="text-lg font-bold mt-2">Estado Actual:
                        <span class="px-2 py-1 rounded-full text-sm font-semibold
                            @if($order->status == 'Pendiente') bg-yellow-100 text-yellow-800
                            @elseif($order->status == 'Confirmado') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $order->status }}
                        </span>
                    </p>
                </div>

                <form method="POST" action="{{ route('orders.update', $order) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Mensajes de Error de Validación de Laravel -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <p class="font-bold">¡Error de Validación!</p>
                            <ul class="mt-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- NOTAS ADICIONALES (Etiqueta HTML estándar) -->
                    <div>
                        <label for="notes" class="block font-medium text-sm text-black">
                            {{ __('Notas Adicionales para el Pedido') }}
                        </label>
                        <textarea id="notes" name="notes" x-model="notes" rows="3" class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <h3 class="text-xl font-semibold border-b pb-2">Productos del Pedido</h3>

                    <!-- INTERFAZ DE FILTRADO Y AÑADIDO (CORREGIDA) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 border rounded-lg  bg-[#522d6d] p-6 border-l-4 border-purple-800">

                        <!-- Columna 1: Selección de Categoría -->
                        <div>
                            <label for="category_select" class="block font-medium text-sm text-white">
                                {{ __('Línea / Categoría') }}
                            </label>
                            <select x-model="selectedCategory" @change="searchTerm = ''; selectedProductId = ''" id="category_select" class="w-full h-10 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                <option value="">-- Todas las Categorías --</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Columna 2: Búsqueda (Se añadió @keydown.enter.prevent para evitar envío del formulario) -->
                        <div>
                            <label for="search_term" class="block font-medium text-sm text-white">
                                {{ __('Buscar Producto (Código / Nombre)') }}
                            </label>
                            <input type="text"
                                   x-model.debounce.300ms="searchTerm"
                                   @keydown.enter.prevent="selectedProductId && addItem(selectedProductId)"
                                   id="search_term"
                                   placeholder="Escribe para filtrar..."
                                   class="w-full h-10 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                        </div>

                        <!-- Columna 3: Producto Filtrado y Botón Añadir -->
                        <div>
                            <label for="product_select" class="block font-medium text-sm text-white">
                                {{ __('Selecciona el Producto') }}
                            </label>
                            <div class="flex space-x-2">
                                <select x-model="selectedProductId" id="product_select" class="w-full flex-grow h-10 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                    <option value="">-- Selecciona un producto --</option>
                                    <template x-for="product in filteredProducts()" :key="product.id">
                                        <option :value="product.id" x-text="product.product_code + ' - ' + product.name + ' (' + product.unit + ') [Stock: ' + (product.stock || 0) + ']'"></option>
                                    </template>
                                </select>
                                <button type="button" @click="addItem(selectedProductId)" :disabled="!selectedProductId" class="inline-flex items-center flex-shrink-0 h-10 px-4 py-2 bg-purple-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 active:bg-purple-950 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 disabled:opacity-75 transition ease-in-out duration-150 shadow-md">
                                    {{ __('Añadir') }}
                                </button>
                            </div>
                        </div>
                    </div>


                    <!-- TABLA DE PRODUCTOS ACTUALES -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md">
                        <table class="min-w-full divide-y divide-gray-200 shadow-md sm:rounded-lg">
                            <thead class="bg-[#522d6d] ">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-white uppercase tracking-wider">CÓDIGO</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-white uppercase tracking-wider">NOMBRE DEL PRODUCTO</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-white uppercase tracking-wider">UNIDAD</th>
                                    <th class="px-6 py-3 text-center text-sm font-bold text-white uppercase tracking-wider">EXISTENCIAS</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-white uppercase tracking-wider">CANTIDAD</th>
                                    <th class="px-6 py-3 text-leff text-sm font-bold text-white uppercase tracking-wider">ACCIÓN</th>
                                </tr>
                            </thead>
                           <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(item, index) in items" :key="item.product_id">
                                    <tr>
                                        <input type="hidden" :name="'orderItems[' + index + '][product_id]'" :value="item.product_id">

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="allProducts.find(p => p.id == item.product_id)?.product_code"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="item.name"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="item.unit"></td>

                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold">
                                            <span x-text="allProducts.find(p => p.id == item.product_id)?.stock || 0"></span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <input type="number"
                                                    :name="'orderItems[' + index + '][quantity]'"
                                                    x-model.number="item.quantity"
                                                    min="1"
                                                    class="w-20 text-center border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm p-2 text-sm">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 font-bold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- BOTONES DE ACCIÓN PRINCIPALES (Botones HTML estándar) -->
                    <div class="flex justify-between items-center pt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-50 transition ease-in-out duration-150 shadow-lg" :disabled="!validateQuantities()">
                            {{ __('Guardar Cambios del Pedido') }}
                        </button>

                        <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 hover:text-gray-300 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Cancelar y Volver al Listado') }}
                        </a>
                    </div>
                </form>

                <!-- FORMULARIO DE ANULACIÓN (Separado del formulario de edición principal) -->
                @if (auth()->user()->hasRole(['admin', 'production']) && $order->status !== 'Anulado' && $order->status !== 'Confirmado')
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="text-lg font-semibold text-red-700 mb-3">{{ __('Anular Pedido') }}</h4>
                        <p class="text-sm text-black mb-4">{{ __('Si ya no se requiere este pedido, puede anularlo permanentemente.') }}</p>
                        <form method="POST" action="{{ route('orders.updateStatus', $order) }}" onsubmit="return confirm('¿Estás seguro de que deseas anular este pedido? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="Anulado">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-50 transition ease-in-out duration-150 shadow-lg">
                                {{ __('Anular Pedido') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
