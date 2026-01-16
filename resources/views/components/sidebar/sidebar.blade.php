<x-sidebar.overlay />

<aside
    class="fixed inset-y-0 z-20 flex flex-col py-4 space-y-6 bg-gradient-to-b from-white via-gray-50 to-white shadow-xl dark:from-dark-eval-1 dark:via-dark-eval-2 dark:to-dark-eval-1"
    :class="{
        'translate-x-0 w-64': isSidebarOpen || isSidebarHovered,
        '-translate-x-full w-64 md:w-16 md:translate-x-0': !isSidebarOpen && !isSidebarHovered,
    }"
    style="transition-property: width, transform; transition-duration: 200ms; box-shadow: rgba(0, 0, 0, 0.1) 8px 0 16px;"
    x-on:mouseenter="handleSidebarHover(true)"
    x-on:mouseleave="handleSidebarHover(false)">
    <x-sidebar.header />

    <x-sidebar.content />

    <x-sidebar.footer />
</aside>