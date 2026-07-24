@php
    // Color thresholds for utilization badges: adjust to match PBO reporting norms if needed.
    $pctValue = round($pct, 2);
    if ($pctValue >= 75) {
        $badgeClasses = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
    } elseif ($pctValue >= 50) {
        $badgeClasses = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    } else {
        $badgeClasses = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    }
@endphp
<span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeClasses }}">
    {{ number_format($pctValue, 2) }}%
</span>