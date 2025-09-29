<div class="flex items-center justify-between flex-shrink-0 px-3">
    <!-- Logo -->
    <a
        href="{{ route('dashboard') }}"
        class="inline-flex items-center gap-2">
        <!-- Finance/Budget Logo using FontAwesome -->
        <i class="ml-1 mr-1 fas fa-code-compare text-3xl text-gray-700 dark:text-white"></i>

        <span x-show="isSidebarOpen || isSidebarHovered" class="text-xl font-semibold text-gray-900 dark:text-white">PBO|REGISTRY</span>

        <span class="sr-only">Dashboard</span>
    </a>

    <!-- Toggle button -->
    <x-button
        type="button"
        icon-only
        sr-text="Toggle sidebar"
        variant="secondary"
        x-show="isSidebarOpen || isSidebarHovered"
        x-on:click="isSidebarOpen = !isSidebarOpen">
        <x-icons.menu-fold-right
            x-show="!isSidebarOpen"
            aria-hidden="true"
            class="hidden w-6 h-6 lg:block" />

        <x-icons.menu-fold-left
            x-show="isSidebarOpen"
            aria-hidden="true"
            class="hidden w-6 h-6 lg:block" />

        <x-heroicon-o-x
            aria-hidden="true"
            class="w-6 h-6 lg:hidden" />
    </x-button>
</div>