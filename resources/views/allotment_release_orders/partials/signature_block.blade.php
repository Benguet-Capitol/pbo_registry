{{--
    Amount In Words / certification / Signatories / closing footer — shared markup so it
    can render either inline on the last table page (when it fits) or on its own
    dedicated page (when it doesn't). See preview.blade.php's $canCombineSignaturePage.

    Note: `use` imports in the parent view don't carry over to an @include'd partial
    (each render() call is its own scope), hence the fully-qualified class name below.
--}}
<div class="mt-4 text-sm">
    <div class="font-semibold">AMOUNT IN WORDS:</div>
    <div class="underline">{{ strtoupper(\App\Helpers\NumberToWords::convert((float) $total)) }}</div>
</div>

<div class="mt-4 text-xs">
    The allotments herein released shall be used solely for the purposes indicated, and disbursements thereto shall be made in accordance with existing budgeting, accounting, and auditing rules and regulations. It is primary responsibility of the head of the Department/Office or Unit concerned to keep expenditures within the limits of the amount alloted.
</div>

<div class="grid grid-cols-2 gap-8 mt-6 text-sm">
    <div>
        <div class="font-semibold ml-6 mb-6">Recommended by:</div>
        <div class="text-center font-bold uppercase">{{ $aro->provincialBudgetOfficer->name ?? 'N/A' }}</div>
        <div class="text-center">{{ $aro->provincial_budget_officer_title }}</div>
    </div>
    <div>
        <div class="font-semibold ml-6 mb-6">Approved by:</div>
        <div class="text-center font-bold uppercase">{{ $aro->provincialGovernor->name ?? $aro->provincial_governor_name ?? 'N/A' }}</div>
        <div class="text-center">{{ $aro->provincial_governor_title }}</div>
        <div class="text-center border-t border-black pt-1">Local Chief Executive</div>
    </div>
</div>

<div class="mt-3 text-xs grid grid-cols-3 items-start">
    <div></div>
    <div class="text-center space-y-0.5">
        <div>ARO No: {{ $aro->aro_no }}</div>
        <div>Date of Issue: {{ $aro->date_of_issue->format('F j, Y') }}</div>
        <div>Page {{ $totalPages }} of {{ $totalPages }} Page{{ $totalPages > 1 ? 's' : '' }}</div>
    </div>
    <div class="text-[10px] text-right text-gray-500 space-y-0.5">
        <div>Generated on: {{ now()->format('F j, Y g:i A') }}</div>
        <div class="font-semibold">PBO|Registry</div>
    </div>
</div>
