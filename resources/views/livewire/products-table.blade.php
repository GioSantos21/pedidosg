<div>
    {{-- NUEVO Contenedor con Padding General --}}
    <div class="p-6 text-gray-900 space-y-4">

        {{-- INICIO DE LOS CONTROLES (sin padding ni bordes) --}}
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">

            <a href="{{ route('admin.products.create') }}" class="bg-[#874ab3] hover:bg-[#623579] text-white font-bold py-2 px-4 rounded">
                Nuevo Producto
            </a>

            <div class="w-full sm:w-1/3">
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Buscar productos o productos por categorías..."
                       class="border-gray-300 rounded-md shadow-sm w-full text-sm">
            </div>
        </div>
        {{-- FIN DE LOS CONTROLES --}}

        {{-- INICIO DE TU TABLA PERSONALIZADA (sin cambios) --}}
        <div class="overflow-x-auto border-gray-200 rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                     <thead class="bg-[#522d6d]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Unidad</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Costo</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-xs text-white uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr>
                                {{-- ... tus celdas <td> ... --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->category->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->unit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->cost }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($product->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold space-x-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Editar</a>
                                    <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres cambiar el estado de este producto?');">
                                        @csrf
                                        @method('PATCH')
                                        @if ($product->is_active)
                                            <button type="submit" class="inline-block bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Desactivar</button>
                                        @else
                                            <button type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Activar</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No se encontraron productos que coincidan con la búsqueda "{{ $search }}".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- FIN DE TU TABLA --}}

        {{-- PAGINACIÓN Y SELECTOR (sin padding ni bordes) --}}
        <div class="flex flex-col sm:flex-row justify-between items-center pt-4">

            <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                @if ($products->total() > 0)
                    {{ trans('pagination.showing') }} {{ $products->firstItem() }}
                    {{ trans('pagination.to') }} {{ $products->lastItem() }}
                    {{ trans('pagination.of') }} {{ $products->total() }}
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
                {{ $products->links() }}
            </div>
        </div>

    </div>
    {{-- FIN DEL CONTENEDOR CON PADDING --}}
</div>
