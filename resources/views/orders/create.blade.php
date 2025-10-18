<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Pedido a Producción') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">

                    <!-- Cabecera de Contexto -->
                    <div class="mb-6 pb-4 border-b border-gray-200">
                        <p class="text-lg font-semibold text-gray-700">Sucursal Solicitante:</p>
                        <!-- NOTE: Assuming $branchName is passed from the controller -->
                        <p class="text-2xl font-bold text-indigo-700">{{ $branchName ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500 mt-1">Gerente: {{ auth()->user()->name }}</p>
                    </div>

                    <!-- Alpine Data: Inicializa la lógica del formulario dinámico -->
                    <form method="POST" action="{{ route('orders.store') }}"
                          x-data="{
                                // Inicializa con un ítem vacío para empezar
                                items: [{ product_id: '', quantity: 1 }],
                                products: @js($products), // Pasa la lista de productos de Laravel a Alpine

                                addItem() {
                                    this.items.push({ product_id: '', quantity: 1 });
                                },
                                removeItem(index) {
                                    // Solo permite eliminar si quedan al menos 2 ítems
                                    if (this.items.length > 1) {
                                        this.items.splice(index, 1);
                                    }
                                }
                          }">
                        @csrf

                        <!-- Errores de Validación Global -->
                        @if ($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- ITEMS DEL PEDIDO -->
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold mb-4 border-b pb-2">Productos a Solicitar</h3>

                            <!-- Bucle de Items Dinámicos -->
                            <template x-for="(item, index) in items" :key="index">
                                <div class="flex items-start space-x-4 border border-gray-200 p-4 rounded-lg bg-gray-50">

                                    <!-- Selector de Producto -->
                                    <div class="flex-1">
                                        <!-- CORRECCIÓN CLAVE: Usamos x-bind:for y comillas simples para evitar la evaluación de Blade -->
                                        <x-input-label x-bind:for="'product_' + index" :value="__('Producto')" />
                                        <select x-bind:name="'items[' + index + '][product_id]'"
                                                x-bind:id="'product_' + index"
                                                x-model="item.product_id"
                                                required
                                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full mt-1">
                                            <option value="">Selecciona un Producto</option>
                                            <template x-for="product in products" :key="product.id">
                                                <option :value="product.id" x-text="product.name + ' (' + product.unit + ')'"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="w-28">
                                        <!-- CORRECCIÓN CLAVE: Usamos x-bind:for y comillas simples para evitar la evaluación de Blade -->
                                        <x-input-label x-bind:for="'quantity_' + index" :value="__('Cantidad')" />
                                        <x-text-input x-bind:id="'quantity_' + index"
                                                      x-bind:name="'items[' + index + '][quantity]'"
                                                      type="number"
                                                      min="1"
                                                      x-model="item.quantity"
                                                      required
                                                      class="block w-full mt-1 text-center" />
                                    </div>

                                    <!-- Botón de Eliminar Item -->
                                    <div class="pt-7">
                                        <button type="button"
                                                @click="removeItem(index)"
                                                class="text-red-500 hover:text-red-700 p-2 rounded-full transition duration-150"
                                                x-show="items.length > 1"
                                                title="Eliminar ítem">
                                            <!-- Icono de Bote de Basura -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.86 11.66A2 2 0 0116.14 21H7.86a2 2 0 01-1.99-2.34L5 7m4 0V5a2 2 0 012-2h2a2 2 0 012 2v2M8 7h8"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Botón para Añadir Ítem -->
                            <button type="button" @click="addItem()" class="mt-4 flex items-center text-indigo-600 hover:text-indigo-900 font-semibold transition duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Añadir Producto
                            </button>
                        </div>

                        <!-- Notas Adicionales -->
                        <div class="mt-8">
                            <x-input-label for="notes" :value="__('Notas Adicionales para Producción (Opcional)')" />
                            <textarea id="notes" name="notes" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <!-- Botón de Envío -->
                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Enviar Pedido') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
