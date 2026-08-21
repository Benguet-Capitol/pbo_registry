<nav
    aria-label="secondary"
    x-data="{ open: false }"
    class="sticky top-0 z-10 flex items-center justify-between px-4 py-4 sm:px-6 transition-all duration-300 ease-in-out bg-gradient-to-r from-white to-gray-50 dark:from-dark-eval-1 dark:to-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-lg"
    :class="{
        '-translate-y-full': scrollingDown,
        'translate-y-0': scrollingUp,
    }">

    <div class="flex items-center gap-3">
        <x-button
            type="button"
            class="md:hidden transform transition-all duration-200 hover:scale-110 active:scale-95"
            icon-only
            variant="secondary"
            sr-text="Toggle dark mode"
            x-on:click="toggleTheme">
            <x-heroicon-o-moon
                x-show="!isDarkMode"
                aria-hidden="true"
                class="w-6 h-6 transition-transform duration-300 transform" />

            <x-heroicon-o-sun
                x-show="isDarkMode"
                aria-hidden="true"
                class="w-6 h-6 transition-transform duration-300 transform" />
        </x-button>
    </div>

    <div class="flex items-center gap-3">
        <x-button
            type="button"
            class="hidden md:inline-flex transform transition-all duration-200 hover:scale-110 active:scale-95"
            icon-only
            variant="secondary"
            sr-text="Toggle dark mode"
            x-on:click="toggleTheme">
            <x-heroicon-o-moon
                x-show="!isDarkMode"
                aria-hidden="true"
                class="w-6 h-6 transition-transform duration-300 transform" />

            <x-heroicon-o-sun
                x-show="isDarkMode"
                aria-hidden="true"
                class="w-6 h-6 transition-transform duration-300 transform" />
        </x-button>

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 rounded-lg transition-all duration-200 ease-in-out bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-dark-eval-1 hover:shadow-md transform hover:scale-105 active:scale-95">
                    <span class="truncate">{{ Auth::user()->name }}</span>

                    <svg
                        class="w-4 h-4 fill-current transition-transform duration-300"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <!-- Profile -->
                <x-dropdown-link
                    :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-dropdown-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</nav>

<!-- Mobile bottom bar -->
<div
    class="fixed inset-x-0 bottom-0 z-30 flex items-center justify-between px-4 py-3 sm:px-6 transition-all duration-300 ease-in-out bg-white md:hidden dark:bg-dark-eval-1 border-t border-gray-200 dark:border-gray-800 shadow-lg dark:shadow-2xl"
    :class="{
        'translate-y-full': scrollingDown,
        'translate-y-0': scrollingUp,
    }">
    <x-button
        type="button"
        icon-only
        class="transform transition-all duration-200 hover:scale-110 active:scale-95"
        variant="secondary"
        sr-text="Search">
        <x-heroicon-o-search aria-hidden="true" class="w-6 h-6 transition-transform duration-300" />
    </x-button>

    <a href="{{ route('dashboard') }}" class="flex items-center justify-center transform transition-all duration-200 hover:scale-110 active:scale-95">
        <i class="fas fa-code-compare text-2xl leading-none text-gray-700 dark:text-white" aria-hidden="true"></i>

        <span class="sr-only">Dashboard</span>
    </a>

    <x-button
        type="button"
        icon-only
        class="transform transition-all duration-200 hover:scale-110 active:scale-95"
        variant="secondary"
        sr-text="Open main menu"
        x-on:click="isSidebarOpen = !isSidebarOpen">
        <x-heroicon-o-menu
            x-show="!isSidebarOpen"
            aria-hidden="true"
            class="w-6 h-6 transition-transform duration-300" />

        <x-heroicon-o-x
            x-show="isSidebarOpen"
            aria-hidden="true"
            class="w-6 h-6 transition-transform duration-300" />
    </x-button>
</div>