{{-- resources/views/admin/branches/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Sucursal: ') . $branch->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <form method="POST" action="{{ route('admin.branches.update', $branch) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Nombre de la Sucursal')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $branch->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="address" :value="__('Dirección (Opcional)')" />
                            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address', $branch->address)" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Teléfono (Opcional)')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $branch->phone)" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $branch->is_active))>
                                <span class="ml-2 text-sm text-gray-600">{{ __('Sucursal Activa') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4 gap-x-4">
                            <a href="{{ route('admin.branches.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                Cancelar
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Sucursal') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
