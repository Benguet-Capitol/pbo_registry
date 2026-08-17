@php
    use App\Helpers\NumberToWords;
    use App\Models\AllotmentReleaseOrder;

    $office = $aro->officeAllotmentClass->offices;
    $allotmentClass = $aro->officeAllotmentClass->allotmentClass;
    $classLabel = AllotmentReleaseOrder::displayClassLabel(optional($allotmentClass)->class, optional($allotmentClass)->description);
    $formTitle = "Allotment Release Order for {$classLabel}";
    $total = $aro->items->sum('this_release');

    // Row-level pagination — splits even a single oversized PPA Code group
    // across pages (see AllotmentReleaseOrder::paginatedPrintUnits()), unlike
    // grouping whole PPA Code groups onto pages which fails when every
    // account code shares one PPA Code. Budget is deliberately lower than a
    // naive "rows that fit" estimate: every page also gets one more physical
    // row appended after this budget (PAGE SUBTOTAL, or TOTAL on the last
    // page) that paginatedPrintUnits() has no visibility into, each table
    // row's real printed height (borders + cell padding + line-height) runs
    // closer to ~0.33in than a bare line-height alone would suggest, and the
    // thead's column-number row ("(1)" "(2)" ...) below the labels takes a
    // bit more of the fixed header space than a single-row thead would.
    $lineBudget = 12;
    $pages = $aro->paginatedPrintUnits($lineBudget);

    // Amount In Words / certification paragraph / Signatories / closing footer only
    // get their OWN dedicated final page when they wouldn't fit under the table on
    // the last table page — otherwise (the common case: a short ARO with room to
    // spare) they render right below the table, avoiding a near-empty trailing page.
    // signatureBlockLineCost is a conservative estimate of how many table-row-
    // equivalent "lines" of vertical space that block needs (~0.46in/line at this
    // budget, so 7 lines ≈ 3.2in — roughly what the block's own AMOUNT IN WORDS +
    // paragraph + signature grid + footer actually take once laid out).
    $signatureBlockLineCost = 7;
    $lastPageLines = $pages ? array_sum(array_column(end($pages), 'lines')) : 0;
    $canCombineSignaturePage = ($lineBudget - $lastPageLines) >= $signatureBlockLineCost;
    $totalPages = count($pages) + ($canCombineSignaturePage ? 0 : 1);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('ARO Preview') }} <span class="text-blue-800 dark:text-blue-400">{{ $aro->aro_no }}</span>
            </h3>
            <div class="grid grid-cols-2 gap-2 no-print sm:flex sm:flex-wrap sm:items-center">
                <select id="aroPageSizeSelect" onchange="setAroPageSize(this.value)" class="col-span-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-2 w-full sm:w-36 dark:bg-gray-800 dark:text-gray-200">
                    <option value="long" selected>Long (8.5&quot; x 13&quot;)</option>
                    <option value="legal">Legal (8.5&quot; x 14&quot;)</option>
                </select>
                <a href="{{ route('allotment_release_orders.index') }}" class="text-gray-600 inline-flex items-center justify-center gap-1 hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-gray-500 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-colors">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="button" onclick="window.print()" class="text-blue-600 inline-flex items-center justify-center gap-1 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-colors">
                    <i class="fas fa-print"></i> Print
                </button>
                <a id="aroExportLink" href="{{ route('allotment_release_orders.exportExcel', $aro) }}?paper_size=long" class="col-span-2 sm:col-span-1 text-green-600 inline-flex items-center justify-center gap-1 hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-colors">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </div>
    </x-slot>

    {{--
        On screen, .aro-page-surface mocks up the WHOLE physical sheet (full page
        width, padding standing in visually for the print margin) so the Preview
        looks like a sheet of paper. But @page's own `margin` already reserves that
        same space at print time — stacking our padding on top of it would double
        the margin and push the table 0.4in past the actual printable width, clipping
        it. So the print-only rule below re-targets the *content* width (pageWidth −
        margin×2) with zero extra padding, matching what @page actually leaves us.
        Both rules are rewritten together by setAroPageSize() so they never drift.
    --}}
    <style id="aroPrintPageStyle">
        @page { size: 13in 8.5in; margin: 0.4in; }
        @media print {
            .aro-print-page { width: 12.2in !important; padding: 0 !important; }
        }
    </style>
    <style id="aroPageSurfaceStyle">
        .aro-page-surface { width: 13in; padding: 0.4in; box-sizing: border-box; }
    </style>
    <style>
        @media print {
            body * { visibility: hidden; }
            #aroPrintArea, #aroPrintArea * { visibility: visible; }
            #aroPrintArea { position: absolute; left: 0; top: 0; width: 100%; max-width: none !important; margin: 0 !important; }
            .no-print { display: none !important; }
            .aro-print-page {
                page-break-after: always;
                /* @page margin already provides the page's whitespace — the on-screen
                   preview's shadow/rounding would otherwise print a gray shadow box
                   around each page. */
                margin: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
            .aro-print-page:last-child { page-break-after: auto; }
        }
    </style>

    <script>
        function setAroPageSize(size) {
            const pageWidthIn = size === 'legal' ? 14 : 13;
            const margin = 0.4;
            const contentWidth = (pageWidthIn - margin * 2).toFixed(3);

            document.getElementById('aroPrintPageStyle').textContent =
                `@page { size: ${pageWidthIn}in 8.5in; margin: ${margin}in; }\n` +
                `@media print { .aro-print-page { width: ${contentWidth}in !important; padding: 0 !important; } }`;
            document.getElementById('aroPageSurfaceStyle').textContent =
                `.aro-page-surface { width: ${pageWidthIn}in; padding: ${margin}in; box-sizing: border-box; }`;

            const exportLink = document.getElementById('aroExportLink');
            const [exportBase] = exportLink.href.split('?');
            exportLink.href = `${exportBase}?paper_size=${size}`;
        }
    </script>

    {{-- The page mockup below is a fixed physical-paper width (~1200-1300px for
         Long/Legal) so it stays print-accurate — on narrower screens this wrapper
         lets it scroll horizontally instead of breaking the surrounding layout. --}}
    <div class="overflow-x-auto">
        <div id="aroPrintArea" class="mx-auto" style="max-width: 1400px;">
            @foreach ($pages as $pageIndex => $pageUnits)
            @php
                // "Last page of table data" — not necessarily the true last printed
                // page; see $canCombineSignaturePage above for when the certification
                // block gets its own dedicated page instead of rendering right here.
                $isLastTablePage = $pageIndex === count($pages) - 1;
                $showSignaturesInline = $isLastTablePage && $canCombineSignaturePage;
                $pageItemUnits = collect($pageUnits)->where('type', 'item');
                $pageSubtotal = [
                    'authorized_appropriation' => $pageItemUnits->sum(fn ($u) => (float) $u['item']->authorized_appropriation),
                    'for_later_release' => $pageItemUnits->sum(fn ($u) => (float) $u['item']->for_later_release),
                    'previously_released_amount' => $pageItemUnits->sum(fn ($u) => (float) $u['item']->previously_released_amount),
                    'this_release' => $pageItemUnits->sum(fn ($u) => (float) $u['item']->this_release),
                ];
            @endphp
            <div class="aro-print-page aro-page-surface bg-white dark:bg-white text-black rounded-lg shadow-md mb-4">
                @include('allotment_release_orders.partials.print_header', ['aro' => $aro, 'formTitle' => $formTitle, 'office' => $office])

                {{--
                    .aro-page-surface (on the page <div> above) is sized to exactly the
                    selected paper's width with matching padding, so this table just needs
                    to stretch to fill it — no separate inch-tracking for the table itself.
                    Percentages here stay proportional to whatever that surface width is.
                --}}
                <table class="w-full text-xs border-collapse mt-4" style="table-layout: fixed;">
                    <colgroup>
                        <col style="width: 11%;">
                        <col style="width: 34%;">
                        <col style="width: 10%;">
                        <col style="width: 11.25%;">
                        <col style="width: 11.25%;">
                        <col style="width: 11.25%;">
                        <col style="width: 11.25%;">
                    </colgroup>
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border-t border-l border-r border-black px-2 py-1">PPA CODE</th>
                            <th class="border-t border-l border-r border-black px-2 py-1">PPA DESCRIPTION</th>
                            <th class="border-t border-l border-r border-black px-2 py-1">OBJECT CLASS/<br>ACCOUNT CODE</th>
                            <th class="border-t border-l border-r border-black px-2 py-1">AUTHORIZED<br>APPROPRIATION</th>
                            <th class="border-t border-l border-r border-black px-2 py-1">FOR LATER<br>RELEASE</th>
                            <th class="border-t border-l border-r border-black px-2 py-1">PREVIOUSLY<br>RELEASED AMOUNT</th>
                            <th class="border-t border-l border-r border-black px-2 py-1">THIS RELEASE</th>
                        </tr>
                        <tr class="bg-gray-100">
                            <th class="border-b border-l border-r border-black">(1)</th>
                            <th class="border-b border-l border-r border-black">(2)</th>
                            <th class="border-b border-l border-r border-black">(3)</th>
                            <th class="border-b border-l border-r border-black">(4)</th>
                            <th class="border-b border-l border-r border-black">(5)</th>
                            <th class="border-b border-l border-r border-black">(6)</th>
                            <th class="border-b border-l border-r border-black">(7)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pageUnits as $unit)
                            @if ($unit['type'] === 'office')
                                {{-- SEF-consolidated ARO: bold header marking a new office's account codes --}}
                                <tr class="bg-gray-200">
                                    <td class="border border-black px-2 py-1"></td>
                                    <td class="border border-black px-2 py-1 font-bold uppercase" colspan="6">{{ $unit['text'] }}</td>
                                </tr>
                            @elseif ($unit['type'] === 'program')
                                <tr>
                                    <td class="border border-black px-2 py-1"></td>
                                    <td class="border border-black px-2 py-1 font-bold" colspan="6">{{ $unit['text'] }}</td>
                                </tr>
                            @elseif ($unit['type'] === 'item')
                                @php
                                    $item = $unit['item'];
                                    // Lighter top/bottom border between individual account-code rows
                                    // (matching the reference form), while the left/right column
                                    // rules and every other row type (headers, program rows,
                                    // Subtotal/Page Subtotal/TOTAL) keep the full dark border.
                                    $itemCellBorder = 'border-l border-r border-t border-b border-l-black border-r-black border-t-gray-300 border-b-gray-300';
                                @endphp
                                <tr>
                                    @if ($unit['rowspan'] > 0)
                                        <td class="{{ $itemCellBorder }} px-2 py-1 text-center" rowspan="{{ $unit['rowspan'] }}">{{ $unit['ppa_code'] }}</td>
                                    @endif
                                    <td class="{{ $itemCellBorder }} px-2 py-1">{{ $item->ppa_description }}</td>
                                    <td class="{{ $itemCellBorder }} px-2 py-1 text-center">{{ $item->account_code }}</td>
                                    <td class="{{ $itemCellBorder }} px-2 py-1 text-right">{{ number_format($item->authorized_appropriation, 2) }}</td>
                                    <td class="{{ $itemCellBorder }} px-2 py-1 text-right">{{ $item->for_later_release > 0 ? number_format($item->for_later_release, 2) : '-' }}</td>
                                    <td class="{{ $itemCellBorder }} px-2 py-1 text-right">{{ $item->previously_released_amount > 0 ? number_format($item->previously_released_amount, 2) : '-' }}</td>
                                    <td class="{{ $itemCellBorder }} px-2 py-1 text-right">{{ number_format($item->this_release, 2) }}</td>
                                </tr>
                            @elseif ($unit['type'] === 'subtotal')
                                <tr class="font-semibold">
                                    <td class="border border-black px-2 py-1"></td>
                                    <td class="border border-black px-2 py-1 text-right" colspan="2">Subtotal</td>
                                    <td class="border border-black px-2 py-1 text-right">{{ number_format($unit['subtotal']['authorized_appropriation'], 2) }}</td>
                                    <td class="border border-black px-2 py-1 text-right">{{ $unit['subtotal']['for_later_release'] > 0 ? number_format($unit['subtotal']['for_later_release'], 2) : '-' }}</td>
                                    <td class="border border-black px-2 py-1 text-right">{{ $unit['subtotal']['previously_released_amount'] > 0 ? number_format($unit['subtotal']['previously_released_amount'], 2) : '-' }}</td>
                                    <td class="border border-black px-2 py-1 text-right">{{ number_format($unit['subtotal']['this_release'], 2) }}</td>
                                </tr>
                            @endif
                        @endforeach
                        @if ($isLastTablePage)
                            <tr class="font-bold">
                                <td colspan="3" class="border border-black px-2 py-1 text-right">TOTAL</td>
                                <td class="border border-black px-2 py-1 text-right">{{ number_format($aro->items->sum('authorized_appropriation'), 2) }}</td>
                                <td class="border border-black px-2 py-1 text-right">{{ $aro->items->sum('for_later_release') > 0 ? number_format($aro->items->sum('for_later_release'), 2) : '-' }}</td>
                                <td class="border border-black px-2 py-1 text-right">{{ $aro->items->sum('previously_released_amount') > 0 ? number_format($aro->items->sum('previously_released_amount'), 2) : '-' }}</td>
                                <td class="border border-black px-2 py-1 text-right">{{ number_format($total, 2) }}</td>
                            </tr>
                        @else
                            <tr class="font-bold bg-gray-50">
                                <td colspan="3" class="border border-black px-2 py-1 text-right">Page Subtotal</td>
                                <td class="border border-black px-2 py-1 text-right">{{ number_format($pageSubtotal['authorized_appropriation'], 2) }}</td>
                                <td class="border border-black px-2 py-1 text-right">{{ $pageSubtotal['for_later_release'] > 0 ? number_format($pageSubtotal['for_later_release'], 2) : '-' }}</td>
                                <td class="border border-black px-2 py-1 text-right">{{ $pageSubtotal['previously_released_amount'] > 0 ? number_format($pageSubtotal['previously_released_amount'], 2) : '-' }}</td>
                                <td class="border border-black px-2 py-1 text-right">{{ number_format($pageSubtotal['this_release'], 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                @unless ($showSignaturesInline)
                    <!-- ARO No. / Date of Issue / Page — stuck at the bottom of every table page
                         that isn't rendering the certification block below (that block has its
                         own closing footer with the same info). -->
                    <div class="mt-2 pt-1 border-t border-gray-300 text-[10px] flex justify-between">
                        <span>ARO No. {{ $aro->aro_no }}</span>
                        <span>Date of Issue: {{ $aro->date_of_issue->format('F j, Y') }}</span>
                        <span>Page {{ $pageIndex + 1 }} of {{ $totalPages }}</span>
                    </div>
                @endunless

                @if ($showSignaturesInline)
                    @include('allotment_release_orders.partials.signature_block', ['aro' => $aro, 'total' => $total, 'totalPages' => $totalPages])
                @endif
            </div>
        @endforeach

        @unless ($canCombineSignaturePage)
            {{-- Didn't fit under the last table page — its own dedicated final page. --}}
            <div class="aro-print-page aro-page-surface bg-white dark:bg-white text-black rounded-lg shadow-md mb-4">
                @include('allotment_release_orders.partials.print_header', ['aro' => $aro, 'formTitle' => $formTitle, 'office' => $office])
                @include('allotment_release_orders.partials.signature_block', ['aro' => $aro, 'total' => $total, 'totalPages' => $totalPages])
            </div>
        @endunless
        </div>
    </div>
</x-app-layout>
