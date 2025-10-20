<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detalle del Pedido #') . $order->id }}</h2></x-slot><div class="py-12">
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

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8 space-y-8">

            {{-- Bloque de Estado y Acciones (Sólo para Producción/Admin) --}}
            @if (auth()->user()->hasRole(['admin', 'production']))
                <div class="bg-indigo-50 p-6 rounded-lg border-l-4 border-indigo-500">
                    <h3 class="text-xl font-bold text-indigo-700 mb-4 flex items-center">
                        Gestión de Producción
                    </h3>
                    <form method="POST" action="{{ route('orders.updateStatus', $order) }}" class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        @csrf
                        @method('PUT')

                        <div class="flex-1 w-full">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Cambiar Estado:</label>
                            <select id="status" name="status" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                <option value="Pendiente" @selected($order->status === 'Pendiente')>Pendiente</option>
                                <option value="Confirmado" @selected($order->status === 'Confirmado')>Confirmado (Producción Terminada)</option>
                                <option value="Anulado" @selected($order->status === 'Anulado')>Anulado</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:pt-6 w-full sm:w-auto">
                            <x-primary-button class="w-full justify-center">
                                Actualizar Estado
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Sección de Resumen del Pedido --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-b pb-6">
                <div>
                    <p class="text-sm font-medium text-gray-500">ID de Pedido</p>
                    <p class="text-2xl font-bold text-gray-900">#{{ $order->id }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Fecha de Solicitud</p>
                    <p class="text-lg font-medium text-gray-800">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Estado Actual</p>
                    @php
                        $color = match($order->status) {
                            'Pendiente' => 'bg-yellow-500',
                            'Confirmado' => 'bg-green-500',
                            'Anulado' => 'bg-red-500',
                            default => 'bg-gray-500',
                        };
                    @endphp
                    <span class="text-xl font-bold text-white px-4 py-1 rounded-full {{ $color }}">
                        {{ $order->status }}
                    </span>
                </div>
            </div>

            {{-- Sección de Información de la Sucursal --}}
            <div class="border-b pb-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-3">Información del Solicitante</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sucursal Solicitante</p>
                        <p class="text-lg font-semibold text-indigo-600">{{ $order->branch->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Gerente Responsable</p>
                        <p class="text-lg text-gray-800">{{ $order->user->name ?? 'Usuario Desconocido' }}</p>
                    </div>
                </div>
                @if ($order->completed_at)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-500">Completado el</p>
                        <p class="text-lg text-gray-800">{{ $order->completed_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
            </div>

            {{-- Tabla de Ítems del Pedido --}}
            <div class="space-y-4">
                <h3 class="text-xl font-semibold text-gray-700">Productos Solicitados</h3>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->product->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">{{ $item->product->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-base font-semibold text-gray-700">{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right text-base font-bold text-gray-700">Total Items:</td>
                                <td class="px-6 py-3 text-right text-base font-bold text-indigo-600">{{ $order->items->sum('quantity') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Notas Adicionales --}}
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm font-medium text-gray-500">Notas Adicionales:</p>
                <p class="text-gray-800 mt-1 italic">{{ $order->notes ?? 'N/A' }}</p>
            </div>

            {{-- Botones de Acción para Gerente --}}
            @if (auth()->user()->role === 'manager' && $order->status === 'Pendiente')
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="{{ route('orders.edit', $order) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zm-3.182 3.182l-5.3 5.3-.787 3.01 3.01-.787 5.3-5.3-2.23-2.23z"/></svg>
                        Editar Pedido
                    </a>
                    {{-- NOTA: El botón de Eliminar necesitaría un modal de confirmación, por simplicidad solo se pone el enlace a 'edit' por ahora. --}}
                </div>
            @endif

            {{-- Botón de Regreso --}}
            <div class="mt-6">
                <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Volver al Listado</a>
            </div>

        </div>
    </div>
</div>
</x-app-layout>
