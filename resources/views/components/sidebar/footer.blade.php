<!-- User Details Section -->
<div class="px-3 py-4 mt-auto border-t border-gray-200 dark:border-gray-700 bg-gradient-to-b from-transparent to-gray-50 dark:to-gray-900">
    <div class="flex items-center gap-3">
        <!-- Avatar -->
        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 flex items-center justify-center shadow-lg hover:shadow-xl transition-shadow duration-200">
            <span class="text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
        </div>
        
        <!-- User Info -->
        <div class="flex-1 min-w-0" x-show="isSidebarOpen || isSidebarHovered" x-transition>
            <div class="flex flex-col gap-0.5">
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate leading-tight">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400 truncate leading-tight">
                    {{ auth()->user()->username }}
                </p>
                <span class="inline-block w-fit mt-1 bg-gradient-to-r from-blue-100 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 text-blue-700 dark:text-blue-300 px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ auth()->user()->roles->first()?->name ?? 'User' }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar Toggle Button -->
<div class="px-3 flex-shrink-0 lg:hidden">
    <x-button
        type="button"
        icon-only
        variant="secondary"
        x-show="!isSidebarOpen"
        x-on:click="isSidebarOpen = !isSidebarOpen"
        sr-text="Toggle sidebar">
        <x-icons.menu-fold-left
            x-show="isSidebarOpen"
            class="w-6 h-6" />

        <x-icons.menu-fold-right
            x-show="!isSidebarOpen"
            class="w-6 h-6" />
    </x-button>
</div>