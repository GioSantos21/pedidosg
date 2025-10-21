<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Lista de Pedidos a Producción') }}</h2></x-slot><div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

            {{-- Mensajes de Sesión (Éxito o Error) --}}
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

            <div class="p-6">
                @if ($orders->isEmpty())
                    <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-lg">
                        No se encontraron pedidos en este momento.
                        @if (auth()->user()->role === 'manager')
                            <div class="mt-2">
                                <a href="{{ route('orders.createIndex') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                    ¡Crea el primer pedido!
                                </a>
                            </div>
                        @endif
                    </div>
                @else

                    <div class="overflow-x-auto">
                        @if (Auth::user()->hasRole('manager'))
                        <div class="flex justify-start mb-4">
                            <a href="{{ route('orders.createIndex') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                + Crear Nuevo Pedido
                            </a>
                        </div>
                    @endif
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID Pedido
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sucursal / Gerente
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha Solicitud
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Items
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #{{ $order->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-indigo-700">{{ $order->branch->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">por {{ $order->user->name ?? 'Usuario Eliminado' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->items->sum('quantity') }} unidades
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $color = match($order->status) {
                                                    'Pendiente' => 'bg-yellow-100 text-yellow-800',
                                                    'Confirmado' => 'bg-green-100 text-green-800',
                                                    'Anulado' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span class="px-3 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">Ver Detalles</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-app-layout>
