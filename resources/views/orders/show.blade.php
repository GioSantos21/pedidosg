<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Pedido') }} #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg">
                <div class="p-6">

                    <!-- Mensajes de Sesión (Éxito o Error) -->
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

                    <!-- CABECERA Y ESTADO DEL PEDIDO -->
                    <div class="flex justify-between items-start border-b pb-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Solicitado Por:</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $order->user->name }}</p>
                            <p class="text-sm text-gray-500 mt-2">Sucursal:</p>
                            <p class="text-2xl font-bold text-indigo-700">{{ $order->branch->name ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Fecha de Solicitud:</p>
                            <p class="text-lg font-medium text-gray-800">{{ $order->requested_at->format('d/m/Y H:i') }}</p>
                            <p class="text-sm text-gray-500 mt-2">Estado:</p>
                            @php
                                $colors = [
                                    'pending' => 'bg-yellow-500',
                                    'processing' => 'bg-blue-500',
                                    'completed' => 'bg-green-500',
                                    'canceled' => 'bg-red-500',
                                ];
                            @endphp
                            <span class="inline-block px-4 py-1 text-white text-xl font-bold rounded-full {{ $colors[$order->status] ?? 'bg-gray-500' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                            @if ($order->status === 'completed' && $order->completed_at)
                                <p class="text-xs text-gray-400 mt-1">Completado: {{ $order->completed_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- ACCIONES (EDITAR y ELIMINAR - Solo si está pendiente) -->
                    @if ($order->status === 'pending' && (auth()->user()->id === $order->user_id || auth()->user()->role === 'admin'))
                        <div class="flex space-x-4 mb-6">
                            <a href="{{ route('orders.edit', $order) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-150">
                                Editar Pedido
                            </a>

                            <form action="{{ route('orders.destroy', $order) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este pedido? Esta acción es irreversible.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                                    Eliminar Pedido
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- DETALLE DE PRODUCTOS -->
                    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Productos Solicitados</h3>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-3/5">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Unidad</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->product->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item->product->unit }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-center">{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- NOTAS -->
                    <div class="mt-6 pt-4 border-t">
                        <p class="text-lg font-semibold text-gray-700 mb-2">Notas para Producción:</p>
                        <div class="p-3 bg-gray-50 border rounded-lg text-gray-600 italic">
                            {{ $order->notes ?: 'No se dejaron notas adicionales.' }}
                        </div>
                    </div>

                    <!-- Botón de regreso -->
                    <div class="mt-8">
                        <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver al Listado de Pedidos
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
