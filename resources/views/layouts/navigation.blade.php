<nav x-data="{ open: false }" class="navbar" style="background-color: #1B365D; border-bottom: 3px solid #C59D42;">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img src="{{ asset('images/Logo-cropped.svg') }}" alt="Sinergia Financiera" style="height: 50px; width: auto;">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex">
                    @if(auth()->check())
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" style="color: rgba(255,255,255,0.9);">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('procesos-ingreso.index')" :active="request()->routeIs('procesos-ingreso.*')" style="color: rgba(255,255,255,0.9);">
                        {{ __('Procesos') }}
                    </x-nav-link>
                    <x-nav-link :href="route('solicitudes.index')" :active="request()->routeIs('solicitudes.*')" style="color: rgba(255,255,255,0.9);">
                        {{ __('Solicitudes') }}
                    </x-nav-link>
                    <x-nav-link :href="route('checkin-acceso.mostrar-bienvenida')" :active="request()->routeIs('checkin-acceso.*')" style="color: rgba(255,255,255,0.9);">
                        {{ __('📍 Check-in') }}
                    </x-nav-link>
                    @if(auth()->user()->hasAnyRole(['Admin', 'Root']))
                    <x-nav-link :href="route('auditoria.dashboard')" :active="request()->routeIs('auditoria.*')" style="color: rgba(255,255,255,0.9);">
                        {{ __('🔒 Auditoría') }}
                    </x-nav-link>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white hover:text-yellow-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content" style="background-color: #F8F9FA;">
                        <x-dropdown-link :href="route('profile.edit')" style="color: #333;">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" style="color: #333;"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-yellow-300 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden" style="background-color: #0f2438;">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->check())
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" style="color: rgba(255,255,255,0.9);">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('procesos-ingreso.index')" :active="request()->routeIs('procesos-ingreso.*')" style="color: rgba(255,255,255,0.9);">
                {{ __('Procesos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('solicitudes.index')" :active="request()->routeIs('solicitudes.*')" style="color: rgba(255,255,255,0.9);">
                {{ __('Solicitudes') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('checkin-acceso.mostrar-bienvenida')" :active="request()->routeIs('checkin-acceso.*')" style="color: rgba(255,255,255,0.9);">
                {{ __('📍 Check-in') }}
            </x-responsive-nav-link>
            @if(auth()->user()->hasAnyRole(['Admin', 'Root']))
            <x-responsive-nav-link :href="route('auditoria.dashboard')" :active="request()->routeIs('auditoria.*')" style="color: rgba(255,255,255,0.9);">
                {{ __('🔒 Auditoría') }}
            </x-responsive-nav-link>
            @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200" style="border-color: rgba(255,255,255,0.1);">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm" style="color: rgba(255,255,255,0.7);">{{ Auth::user()->email }}</div>
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
