<div>
    <div class="p-6 text-gray-900 space-y-4">

        {{-- CONTROLES --}}
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <a href="{{ route('admin.correlatives.create') }}"
                class="bg-[#874ab3] hover:bg-[#623579] text-white font-bold py-2 px-4 rounded">
                Nuevo Correlativo
            </a>

            <div class="w-full sm:w-1/3">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por sucursal o prefijo..."
                    class="border-gray-300 rounded-md shadow-sm w-full text-sm">
            </div>
        </div>

        {{-- TABLA --}}
        <div class="overflow-x-auto border-gray-200 rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#522d6d]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                Sucursal</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                Prefijo</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">
                                Último Generado (Actual)</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">
                                Rango (Inicio - Fin)</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($correlatives as $correlative)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $correlative->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $correlative->branch->name ?? 'Sin Sucursal' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="bg-purple-100 text-purple-800 py-1 px-2 rounded-md font-mono">
                                        {{ $correlative->prefix }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-indigo-600">
                                    {{-- Mostramos cómo se ve el número real --}}
                                    {{ $correlative->prefix }}{{ str_pad($correlative->counter, 6, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    {{ $correlative->initial }} al {{ $correlative->final }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold space-x-2">
                                    <a href="{{ route('admin.correlatives.edit', $correlative) }}"
                                        class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">
                                        Editar
                                    </a>

                                    <form action="{{ route('admin.correlatives.toggle', $correlative->id) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if ($correlative->is_active)
                                            <button type="submit"
                                                class="inline-block bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Desactivar</button>
                                        @else
                                            <button type="submit"
                                                class="inline-block bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Activar</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-900">
                                    No hay configuraciones de correlativos encontradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINACIÓN Y SELECTOR (El layout de 3 columnas que hicimos) --}}
        <div class="flex flex-col sm:flex-row justify-between items-center pt-4">

            <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                @if ($correlatives->total() > 0)
                    {{ trans('pagination.showing') }} {{ $correlatives->firstItem() }}
                    {{ trans('pagination.to') }} {{ $correlatives->lastItem() }}
                    {{ trans('pagination.of') }} {{ $correlatives->total() }}
                    {{ trans('pagination.results') }}
                @else
                    No se encontraron resultados
                @endif
            </div>

            <div class="flex items-center mb-4 sm:mb-0">
                <span class="text-sm text-gray-700 mr-2">Mostrar</span>
                <select wire:model.live="perPage" class="border-gray-300 rounded-md shadow-sm text-sm" style="width: 75px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-gray-700 ml-2">registros</span>
            </div>

            <div>
                {{ $correlatives->links() }}
            </div>
        </div>
    </div>
</div>
