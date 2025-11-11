{{-- resources/views/admin/categories/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Categorías') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

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

                    <a href="{{ route('admin.categories.create') }}" class="bg-[#874ab3] hover:bg-[#623579]
                     text-white font-bold py-2 px-4 rounded mb-4 inline-block">
                        Nueva Categoría
                    </a>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-[#522d6d]">
                                    <tr>
                                        <th class="px-6 py-3  text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3  text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3  text-left text-xs font-medium text-white uppercase tracking-wider">Descripción</th>
                                        <th class="px-6 py-3  text-left text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-xs  uppercase text-white">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($categories as $category)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->name }}</td>
                                            <td class="px-6 py-4 text-sm text-grat-900">{{ Str::limit($category->description, 50) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if ($category->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activa</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactiva</span>
                                                @endif
                                            </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium space-x-2">
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
