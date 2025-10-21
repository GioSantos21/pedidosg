<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Editar Pedido #') . $order->id . ' | Sucursal: ' . $branchName }}</h2></x-slot><div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- Mensajes de Sesión --}}
        @if (session('success'))
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                <span class="font-medium">Éxito:</span> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <span class="font-medium">Error:</span> {{ session('error') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8">

            {{-- El formulario de edición utiliza el método PUT para actualizar --}}
            <form method="POST" action="{{ route('orders.update', $order) }}" x-data="orderForm({{ $currentItems }})">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    {{-- NOTAS DEL PEDIDO --}}
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notas Adicionales (Opcional):</label>
                        <textarea name="notes" id="notes" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" maxlength="500">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- SECCIÓN DE ITEMS --}}
                    <h3 class="text-xl font-semibold text-gray-700 border-t pt-4">Productos Solicitados</h3>

                    <div id="item-list" class="space-y-4">
                        {{-- Contenedor de Items (Manejado por Alpine.js) --}}
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex flex-col sm:flex-row gap-3 p-4 bg-gray-50 border rounded-lg items-end">
                                {{-- Selector de Producto --}}
                                <div class="flex-1 w-full">
                                    <label :for="'product_id_' + index" class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                                    <select :name="'items[' + index + '][product_id]'"
                                            :id="'product_id_' + index"
                                            x-model.number="item.product_id"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                                        <option value="">Seleccione un producto</option>
                                        {{-- Recorremos la lista de productos estáticamente para que Alpine.js pueda usarla --}}
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Cantidad --}}
                                <div class="w-full sm:w-32">
                                    <label :for="'quantity_' + index" class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                                    <input type="number"
                                           :name="'items[' + index + '][quantity]'"
                                           :id="'quantity_' + index"
                                           x-model.number="item.quantity"
                                           min="1"
                                           class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required />
                                </div>

                                {{-- Botón de Eliminar Item --}}
                                <button type="button" @click="removeItem(index)"
                                        class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-150 w-full sm:w-auto flex justify-center items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 6h6v10H7V6z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        {{-- Botón para Añadir Item --}}
                        <div class="flex justify-start pt-2">
                            <button type="button" @click="addItem"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Añadir Producto
                            </button>
                        </div>

                        {{-- Mensaje general de error para la validación de items --}}
                        @error('items')
                            <p class="text-sm text-red-600 mt-1">Error en los productos solicitados: {{ $message }}</p>
                        @enderror
                        {{-- Muestra errores de ítems específicos si los hay, como un listado. Esto es un fallback. --}}
                        @foreach ($errors->get('items.*') as $messages)
                            @foreach ($messages as $message)
                                <p class="text-sm text-red-600 mt-1">- {{ $message }}</p>
                            @endforeach
                        @endforeach
                    </div>

                </div>

                {{-- Botón de Guardar y Regreso --}}
                <div class="flex justify-between items-center mt-8 pt-6 border-t">
                    <a href="{{ route('orders.show', $order) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Cancelar y Volver</a>

                    <x-primary-button class="ml-4 bg-indigo-600 hover:bg-indigo-700">
                        Actualizar Pedido
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</div>
</x-app-layout>{{-- Script de Alpine.js para gestionar los items --}}<script>function orderForm(initialItems) {// Mapea los items existentes para asegurar que el formato sea el esperado por Alpine.const mappedItems = initialItems.map(item => ({product_id: item.product_id,quantity: item.quantity,}));    return {
        // Inicializa los items con los datos del pedido existente
        items: mappedItems.length &gt; 0 ? mappedItems : [{ product_id: &#39;&#39;, quantity: 1 }],

        addItem() {
            this.items.push({
                product_id: &#39;&#39;,
                quantity: 1
            });
        },

        removeItem(index) {
            if (this.items.length &gt; 1) {
                this.items.splice(index, 1);
            } else {
                // Usar un modal o mensaje en lugar de alert() si tienes un componente de mensaje
                console.error(&#39;Debe haber al menos un producto en el pedido.&#39;);
            }
        }
    }
}
</script>
