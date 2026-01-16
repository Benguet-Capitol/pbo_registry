@props([
'isActive' => false,
'title' => '',
'collapsible' => false
])

@php
$isActiveClasses = $isActive ? 'text-white bg-gradient-to-r from-blue-500 to-blue-600 shadow-md hover:from-blue-600 hover:to-blue-700' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-dark-eval-3 hover:shadow-sm';

$classes = 'flex-shrink-0 flex items-center gap-3 px-3 py-2.5 transition-all rounded-lg overflow-hidden ' . $isActiveClasses;

if($collapsible) $classes .= ' w-full';
@endphp

@if ($collapsible)
<button type="button" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon ?? false)
    {{ $icon }}
    @else
    <x-icons.empty-circle class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
    @endif

    <span
        class="text-sm font-medium whitespace-nowrap"
        x-show="isSidebarOpen || isSidebarHovered">
        {{ $title }}
    </span>

    <span
        x-show="isSidebarOpen || isSidebarHovered"
        aria-hidden="true"
        class="relative block ml-auto w-6 h-6">
        <span
            :class="open ? '-rotate-45' : 'rotate-45'"
            class="absolute right-[9px] bg-gray-400 mt-[-5px] h-2 w-[2px] top-1/2 transition-all duration-200"></span>

        <span
            :class="open ? 'rotate-45' : '-rotate-45'"
            class="absolute left-[9px] bg-gray-400 mt-[-5px] h-2 w-[2px] top-1/2 transition-all duration-200"></span>
    </span>
</button>
@else
<a {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon ?? false)
    {{ $icon }}
    @else
    <x-icons.empty-circle class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
    @endif

    <span
        class="text-sm font-medium whitespace-nowrap"
        x-show="isSidebarOpen || isSidebarHovered">
        {{ $title }}
    </span>
</a>
@endif