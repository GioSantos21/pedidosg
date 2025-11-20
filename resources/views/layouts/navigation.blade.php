<nav x-data="{ open: false }" class="w-full bg-[#522d6d]">
    <!-- Primary Navigation Menu -->
    <div class="w-full mx-auto px-2 sm:px-4 lg:px-8">
        <div class="flex justify-between items-center h-16 flex-nowrap">
            <div class="flex flex-1 items-center min-w-0">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                       <img src="{{ asset('images/logo-anthonys-sinFondo.png') }}" alt="Logo de la Aplicación" class="block h-10 sm:h-12 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
               <div class="flex space-x-1 sm:space-x-2 md:space-x-4 md:-my-px md:ml-4 lg:ml-6 overflow-x-auto overflow-y-hidden">

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <x-slot name="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </x-slot>
                        {{ __('Menu') }}
                    </x-nav-link>

                    <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                        <x-slot name="icon">
                            <img src="{{ asset('images/order.png') }}"
                                alt="Icono de Pedidos"
                                class="w-7 h-7" style="">
                        </x-slot>
                        {{ __('Pedidos') }}
                    </x-nav-link>

                    @if (Auth::user()->hasRole(['admin', 'production']))
                        <x-nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">
                            <x-slot name="icon">
                                <img src="{{ asset('images/category.png') }}"
                                    alt="Icono de category"
                                    class="w-6 h-6" style="">
                            </x-slot>
                            {{ __('Categorías') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">
                            <x-slot name="icon">
                                <img src="{{ asset('images/product.png') }}"
                                    alt="Icono de product"
                                    class="w-7 h-7" style="">
                            </x-slot>
                            {{ __('Productos') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasRole('admin'))
                        <x-nav-link :href="route('admin.branches.index')" :active="request()->routeIs('admin.branches.*')">
                            <x-slot name="icon">
                                <img src="{{ asset('images/branch.png') }}"
                                    alt="Icono de branch"
                                    class="w-6 h-6" style="">
                            </x-slot>
                            {{ __('Sucursales') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            <x-slot name="icon">
                                <img src="{{ asset('images/users.png') }}"
                                    alt="Icono de users"
                                    class="w-6 h-6" style="">
                            </x-slot>
                            {{ __('Usuarios') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden md:flex md:items-center md:ml-2 lg:ml-6 flex-shrink-0">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-2 sm:px-3 py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 whitespace-nowrap">
                            <div class="hidden sm:block">{{ Auth::user()->name }}</div>
                            <div class="block sm:hidden">👤</div>

                            <div class="ml-1 hidden sm:block">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Menu') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                {{ __('Pedidos') }}
            </x-responsive-nav-link>

            @if (Auth::user()->hasRole(['admin', 'production']))
                <x-responsive-nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">
                    {{ __('Categorías') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">
                    {{ __('Productos') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasRole('admin'))
                <x-responsive-nav-link :href="route('admin.branches.index')" :active="request()->routeIs('admin.branches.*')">
                    {{ __('Sucursales') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Usuarios') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-white">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
