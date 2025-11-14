<div>
    {{-- NUEVO Contenedor con Padding General --}}
    <div class="p-6 text-gray-900 space-y-4">

        {{-- INICIO DE LOS CONTROLES --}}
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">

            <a href="{{ route('admin.categories.create') }}" class="bg-[#874ab3] hover:bg-[#623579] text-white font-bold py-2 px-4 rounded">
                Nueva Categoría
            </a>

            <div class="w-full sm:w-1/3">
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Buscar por categorías o por descripción..."
                       class="border-gray-300 rounded-md shadow-sm w-full text-sm">
            </div>
        </div>
        {{-- FIN DE LOS CONTROLES --}}

        {{-- INICIO DE TU TABLA PERSONALIZADA --}}
        <div class="overflow-x-auto border-gray-200 rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                     <thead class="bg-[#522d6d]"> <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-xs text-white uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($category->description, 50) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($category->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activa</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactiva</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-bold space-x-2">
                                    {{-- Botón Editar --}}
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Editar</a>
                                    {{-- Botón Activar/Desactivar --}}
                                    <form action="{{ route('admin.categories.toggle-status', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres cambiar el estado de esta categoría?');">
                                        @csrf
                                        @method('PATCH')
                                        @if ($category->is_active)
                                            <button type="submit" class="inline-block bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Desactivar</button>
                                        @else
                                            <button type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Activar</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No se encontraron categorías que coincidan con la búsqueda "{{ $search }}".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- FIN DE TU TABLA --}}

        {{-- PAGINACIÓN Y SELECTOR (El layout de 3 columnas que hicimos) --}}
        <div class="flex flex-col sm:flex-row justify-between items-center pt-4">

            <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                @if ($categories->total() > 0)
                    {{ trans('pagination.showing') }} {{ $categories->firstItem() }}
                    {{ trans('pagination.to') }} {{ $categories->lastItem() }}
                    {{ trans('pagination.of') }} {{ $categories->total() }}
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
                {{ $categories->links() }}
            </div>
        </div>
    </div>
    {{-- FIN DEL CONTENEDOR CON PADDING --}}
</div>
