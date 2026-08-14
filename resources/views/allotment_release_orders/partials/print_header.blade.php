@php
    // Inline styles (not Tailwind utility classes) so the filled/unfilled state is
    // guaranteed to render — independent of whether the compiled CSS bundle happens
    // to include bg-black/bg-white, and forced to survive printing regardless of the
    // browser's "print background graphics" setting via *-print-color-adjust.
    $checkboxStyle = fn (bool $filled) => 'display:inline-block;width:10px;height:10px;border:1px solid #000;flex-shrink:0;'
        .($filled ? 'background-color:#000;-webkit-print-color-adjust:exact;print-color-adjust:exact;color-adjust:exact;' : 'background-color:#fff;');

    // office_allotment_classes has BOTH a `fund` column (the fund name string) and a
    // fund() relationship of the same name — Eloquent's magic property access always
    // returns the column value in that case, never the relation, so getRelation()
    // must be used explicitly here to reach the eager-loaded Fund model.
    $fundCode = optional($aro->officeAllotmentClass->getRelation('fund'))->fund_code;
@endphp
{{-- Repeated at the top of every printed page (Preview) so the form header stays "stuck" across pages --}}
<div class="flex justify-between items-start mb-2">
    <div class="text-xs">LBE Form No. 1</div>
    <div class="text-[10px] space-y-0.5">
        <div class="flex items-center gap-1.5">
            <span style="{{ $checkboxStyle($aro->fund_source === 'Annual Budget') }}"></span>
            <span>Annual Budget</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span style="{{ $checkboxStyle($aro->fund_source === 'Supplemental Budget') }}"></span>
            <span>Supplemental Budget{{ $aro->supplemental_no ? ' No. '.$aro->supplemental_no : '' }}</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span style="{{ $checkboxStyle($aro->fund_source === 'Reenacted Budget') }}"></span>
            <span>Reenacted Budget</span>
        </div>
    </div>
</div>

<div class="text-center mb-1">
    <h2 class="font-bold text-base uppercase">{{ $formTitle }}</h2>
    <div class="text-sm font-bold">FY {{ $aro->year }}</div>
</div>

<div class="flex justify-between items-start text-sm mt-4">
    <div class="space-y-1">
        <div><span>Local Government Unit:</span> BENGUET</div>
        <div><span>Department/Office:</span> {{ strtoupper($office->office_name ?? $office->office_abbreviation) }}</div>
        <div><span>Purpose:</span></div>
    </div>
    @if ($fundCode)
        <div class="mr-16"><span>Fund Code:</span> <span class="underline">{{ $fundCode }}</span></div>
    @endif
</div>
