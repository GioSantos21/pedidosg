@props(['active'])

@php
$classes = ($active ?? false)
            // --- ESTILOS DEL ENLACE ACTIVO ---
            // Cambié: text-gray-900 -> text-white
            // Cambié: border-indigo-400 -> border-white (para que la línea de abajo sea blanca)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-white font-bold text-lg font-bold leading-5 text-white focus:outline-none focus:border-gray-100 transition duration-150 ease-in-out'

            // --- ESTILOS DEL ENLACE INACTIVO ---
            // Cambié: text-gray-500 -> text-gray-200 (un blanco suave)
            // Cambié: hover:text-gray-700 -> hover:text-white (blanco al pasar el mouse)
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-lg font-bold font-bold leading-5 text-gray-200 hover:text-white hover:border-gray-100 focus:outline-none focus:text-white focus:border-gray-100 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
