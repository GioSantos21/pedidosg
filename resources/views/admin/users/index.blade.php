{{-- resources/views/admin/users/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Usuarios y Roles') }}
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

                        <a href="{{ route('admin.users.create') }}" class="bg-[#874ab3] hover:bg-[#623579]
                        text-white font-bold py-2 px-4 rounded mb-4 inline-block">
                             Nuevo Usuario
                        </a>
                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-[#522d6d]">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs fond-bold text-white uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs fond-bold text-white uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs fond-bold text-white uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs fond-bold text-white uppercase tracking-wider">Rol</th>
                                        <th class="px-6 py-3 text-left text-xs fond-bold text-white uppercase tracking-wider">Sucursal Asignada</th>
                                        <th class="px-6 py-3 text-white uppercase text-xs  text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($users as $user)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-grat-900">{{ $user->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-grat-900 capitalize">
                                                {{ $user->role }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-grat-900">
                                                {{-- Usamos ?? 'N/A' por si la sucursal es NULL --}}
                                                {{ $user->branch->name ?? 'N/A' }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <a href="{{ route('admin.users.edit', $user) }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-150">Editar</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-grat-900">No hay usuarios registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
