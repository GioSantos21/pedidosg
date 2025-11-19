@props(['active'])

@php

// Clases para el enlace INACTIVO (texto claro)
$inactiveClasses = 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-gray-300 hover:text-white hover:bg-purple-700 focus:outline-none focus:text-white focus:bg-purple-700 focus:border-purple-700 transition duration-150 ease-in-out';

// Clases para el enlace ACTIVO (texto oscuro sobre fondo claro)
// (Esto es lo que se ve en "Categorías" en tu foto, lo cual es legible)
$activeClasses = 'block w-full pl-3 pr-4 py-2 border-l-4 border-indigo-400 text-left text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out';

$classes = ($active ?? false) ? $activeClasses : $inactiveClasses;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
