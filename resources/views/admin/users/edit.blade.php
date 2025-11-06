{{-- resources/views/admin/users/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario: ') . $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="mb-4">
                        <p><strong>Usuario:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                    </div>

                    <hr class="my-4">

                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="role" :value="__('Asignar Rol')" />
                            <select id="role" name="role" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                <option value="manager" @selected(old('role', $user->role) == 'manager')>Gerente (Manager)</option>
                                <option value="production" @selected(old('role', $user->role) == 'production')>Producción (Production)</option>
                                <option value="admin" @selected(old('role', $user->role) == 'admin')>Administrador (Admin)</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="branch_id" :value="__('Asignar Sucursal (Solo para Gerentes)')" />
                            <select id="branch_id" name="branch_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">-- Ninguna (Si no es Gerente) --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id', $user->branch_id) == $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-sm text-gray-600 mt-1">El rol 'Gerente' debe tener una sucursal asignada para poder crear pedidos.</p>
                            <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-4 gap-x-4">
                             <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                Cancelar
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Usuario') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
