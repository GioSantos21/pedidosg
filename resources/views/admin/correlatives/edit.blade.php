<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Correlativo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('admin.correlatives.update', $correlative) }}" class="space-y-4">
                    @csrf
                    @method('PUT') <div>
                        <x-input-label for="branch_id" :value="__('Sucursal')" />
                        <select id="branch_id" name="branch_id" class="border-gray-300 rounded-md w-full shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $correlative->branch_id) == $branch->id)>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="prefix" :value="__('Prefijo (Ej: PED-TERM-)')" />
                        <x-text-input id="prefix" class="block w-full" type="text" name="prefix"
                                      :value="old('prefix', $correlative->prefix)" required />
                        <x-input-error :messages="$errors->get('prefix')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="initial" :value="__('Inicio')" />
                            <x-text-input id="initial" class="block w-full" type="number" name="initial"
                                          :value="old('initial', $correlative->initial)" required />
                        </div>

                        <div>
                            <x-input-label for="final" :value="__('Límite Final')" />
                            <x-text-input id="final" class="block w-full" type="number" name="final"
                                          :value="old('final', $correlative->final)" required />
                        </div>

                        <div>
                            <x-input-label for="counter" :value="__('Contador Actual')" />
                            <x-text-input id="counter" class="block w-full bg-yellow-50" type="number" name="counter"
                                          :value="old('counter', $correlative->counter)" required />
                            <p class="text-xs text-gray-500 mt-1">Último número usado.</p>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 gap-x-4">

                        <a href="{{ route('admin.correlatives.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Cancelar
                        </a>

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
