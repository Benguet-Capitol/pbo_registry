@props(['aros', 'returnTo', 'uid'])

@php
    $aros = collect($aros)->values();
    $returnQuery = http_build_query(request()->query());
@endphp

@if ($aros->count() === 1)
    <a href="{{ route('allotment_release_orders.preview', ['allotment_release_order' => $aros->first()->id, 'return_to' => $returnTo, 'return_query' => $returnQuery]) }}"
        title="Preview {{ $aros->first()->aro_no }}"
        {{ $attributes }}>
        {{ $slot }}
    </a>
@elseif ($aros->count() > 1)
    {{-- More than one ARO matches — let the user pick which one to preview
         instead of only ever surfacing a single (e.g. most recent) one. --}}
    <div class="relative inline-block preview-aro-picker" data-picker-uid="{{ $uid }}">
        {{-- stopPropagation lives in togglePreviewAroPicker() itself, not as a
             separate onclick here — a second onclick passed in via $attributes
             would collide with this one (duplicate HTML attributes silently
             drop everything after the first) rather than combining with it. --}}
        <button type="button" onclick="togglePreviewAroPicker('{{ $uid }}', event)" title="Preview Allotment Release Order"
            {{ $attributes }}>
            {{ $slot }}
            <i class="fas fa-chevron-down ml-1 text-[10px]"></i>
        </button>
        <div id="previewAroDropdown-{{ $uid }}" class="preview-aro-dropdown hidden absolute z-50 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg w-64 text-xs max-h-64 overflow-y-auto">
            @foreach ($aros as $aro)
                <a href="{{ route('allotment_release_orders.preview', ['allotment_release_order' => $aro->id, 'return_to' => $returnTo, 'return_query' => $returnQuery]) }}"
                    class="block px-4 py-2 text-left hover:bg-blue-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0 text-gray-700 dark:text-gray-200">
                    <div class="font-semibold">{{ $aro->aro_no }}</div>
                    <div class="text-gray-500 dark:text-gray-400 text-[11px]">{{ optional($aro->date_of_issue)->format('M d, Y') }}</div>
                </a>
            @endforeach
        </div>
    </div>
    @once
        <script>
            // Shared by every instance of this component on the page — each
            // renders its own dropdown (keyed by a unique uid), but the toggle/
            // click-outside-to-close behavior only needs wiring up once.
            function togglePreviewAroPicker(uid, event) {
                // Harmless when there's no parent click handler to worry about
                // (e.g. on the Appropriations index) — but needed wherever this
                // sits inside a row/card that reacts to clicks of its own (e.g.
                // Supplementals' list/card views).
                if (event) event.stopPropagation();

                document.querySelectorAll('.preview-aro-dropdown').forEach(function (el) {
                    if (el.id !== 'previewAroDropdown-' + uid) el.classList.add('hidden');
                });
                const dropdown = document.getElementById('previewAroDropdown-' + uid);
                if (dropdown) dropdown.classList.toggle('hidden');
            }
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.preview-aro-picker')) {
                    document.querySelectorAll('.preview-aro-dropdown').forEach(function (el) {
                        el.classList.add('hidden');
                    });
                }
            });
        </script>
    @endonce
@endif
