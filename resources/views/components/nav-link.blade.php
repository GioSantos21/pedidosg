@props(['active'])

@php

$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 sm:px-2 pt-1 border-b-2 border-white text-white focus:outline-none focus:border-gray-100 transition duration-150 ease-in-out whitespace-nowrap'
            : 'inline-flex items-center px-1 sm:px-2 pt-1 border-b-2 border-transparent text-gray-200 hover:text-white hover:border-gray-100 focus:outline-none focus:text-white focus:border-gray-100 transition duration-150 ease-in-out whitespace-nowrap';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>

    @if (isset($icon))
        <span class="text-lg sm:text-xl md:text-xl inline-flex items-center"> {{ $icon }}
        </span>
    @endif

    <span class="hidden md:inline ml-2 text-xs sm:text-sm md:text-base font-bold leading-4 sm:leading-5">
        {{ $slot }}
    </span>
</a>
