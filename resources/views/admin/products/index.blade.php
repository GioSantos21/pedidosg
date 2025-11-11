<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Productos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        {{-- ... código del mensaje de éxito ... --}}
                    @endif
                    <a href="{{ route('admin.products.create') }}" class="bg-[#874ab3] hover:bg-[#623579]
                     text-white font-bold py-2 px-4 rounded mb-4 inline-block"> Nuevo Producto
                    </a>
                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                 <thead class="bg-[#522d6d]">
                                    <tr>
                                        <th class="px-6 py-3  text-left text-xs fond-bold text-white uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3  text-left text-xs fond-bold text-white uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3  text-left text-xs fond-bold text-white uppercase tracking-wider">Categoría</th>
                                        <th class="px-6 py-3  text-left text-xs fond-bold text-white uppercase tracking-wider">Unidad</th>
                                        <th class="px-6 py-3  text-left text-xs fond-bold text-white uppercase tracking-wider">Costo</th>
                                        <th class="px-6 py-3  text-left text-xs fond-bold text-white uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-xs text-white uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($products as $product)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->category->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->unit }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->cost }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm ">
                                                @if ($product->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-center whitespace-nowrap text-sm fond-bold space-x-2">
                                                {{-- Botón Editar --}}
                                                <a href="{{ route('admin.products.edit', $product) }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Editar</a>

                                                {{-- Botón Activar/Desactivar --}}
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
