<div>
    {{-- NUEVO Contenedor con Padding General --}}
    <div class="p-6 text-gray-900 space-y-4">

        {{-- INICIO DE LOS CONTROLES --}}
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">

            @if (auth()->user()->hasRole('manager'))
                <a href="{{ route('orders.createIndex') }}"
                    class="bg-[#874ab3] hover:bg-[#623579] text-white font-bold py-2 px-4 rounded">
                    Nuevo Pedido
                </a>
            @else
                <div></div>
            @endif

            <div class="w-full sm:w-1/3">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por ID, sucursal, gerente o estado..."
                    class="border-gray-300 rounded-md shadow-sm w-full text-sm">
            </div>
        </div>
        {{-- FIN DE LOS CONTROLES --}}

        {{-- INICIO DE TU TABLA PERSONALIZADA (Basada en tu imagen) --}}
        <div class="overflow-x-auto border-gray-200 rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#522d6d]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">ID
                                Pedido</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                Sucursal / Gerente</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Fecha
                                Solicitud</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">
                                Items</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">
                                Estado</th>
                            <th class="px-6 py-3 text-xs text-white uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-indigo-700">{{ $order->branch->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-900">por {{ $order->user->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm text-gray-900">
                                    {{ $order->orderItems ? $order->orderItems->sum('quantity') : 0 }} unidades
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $color = match ($order->status) {
                                            'Pendiente' => 'bg-yellow-100 text-yellow-800',
                                            'Confirmado' => 'bg-green-100 text-green-800',
                                            'Anulado' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span
                                        class="px-3 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="{{ route('orders.show', $order) }}"
                                        class="text-indigo-600 hover:text-indigo-900">Ver Detalles</a>
                                    <a href="{{ route('orders.download', $order) }}"
                                        class="inline-flex items-center px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-900 transition ease-in-out duration-150">
                                         Descargar PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-900">
                                    No se encontraron pedidos que coincidan con la búsqueda "{{ $search }}".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- FIN DE TU TABLA --}}

        {{-- PAGINACIÓN Y SELECTOR --}}
        <div class="flex flex-col sm:flex-row justify-between items-center pt-4">

            <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                @if ($orders->total() > 0)
                    {{ trans('pagination.showing') }} {{ $orders->firstItem() }}
                    {{ trans('pagination.to') }} {{ $orders->lastItem() }}
                    {{ trans('pagination.of') }} {{ $orders->total() }}
                    {{ trans('pagination.results') }}
                @else
                    No se encontraron resultados
                @endif
            </div>

            <div class="flex items-center mb-4 sm:mb-0">
                <span class="text-sm text-gray-700 mr-2">Mostrar</span>
                <select wire:model.live="perPage" class="border-gray-300 rounded-md shadow-sm text-sm"
                    style="width: 75px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-gray-700 ml-2">registros</span>
            </div>

            <div>
                {{ $orders->links() }}
            </div>
        </div>
    </div>
    {{-- FIN DEL CONTENEDOR CON PADDING --}}
</div>
