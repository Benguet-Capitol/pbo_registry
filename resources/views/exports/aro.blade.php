@php
    use App\Helpers\NumberToWords;
    use App\Models\AllotmentReleaseOrder;

    $office = $aro->officeAllotmentClass->offices;
    $allotmentClass = $aro->officeAllotmentClass->allotmentClass;
    $classLabel = AllotmentReleaseOrder::displayClassLabel(optional($allotmentClass)->class, optional($allotmentClass)->description);
    $formTitle = strtoupper("Allotment Release Order for {$classLabel}");
    $total = $aro->items->sum('this_release');

    // office_allotment_classes has BOTH a `fund` column (the fund name string) and a
    // fund() relationship of the same name — Eloquent's magic property access always
    // returns the column value in that case, never the relation, so getRelation()
    // must be used explicitly here to reach the eager-loaded Fund model.
    $fundCode = optional($aro->officeAllotmentClass->getRelation('fund'))->fund_code;
    $lbeFormNumber = AllotmentReleaseOrder::lbeFormNumber(optional($allotmentClass)->class);
    // A SEF-consolidated ARO spans every Special Education Fund office for this
    // Allotment Class, so the single-office name no longer applies here.
    $departmentOfficeLabel = $aro->isSefConsolidated()
        ? 'SPECIAL EDUCATION FUND'
        : strtoupper($office->office_name ?? $office->office_abbreviation);

    // Same row-level pagination the Preview/Print view uses (see
    // AllotmentReleaseOrder::paginatedPrintUnits()), so the Excel sheet's page
    // breaks / repeated headers / Page Subtotal vs TOTAL rows land in the exact
    // same places as the printed PDF.
    $lineBudget = 12;
    $pages = $aro->paginatedPrintUnits($lineBudget);
    $signatureBlockLineCost = 7;
    $lastPageLines = $pages ? array_sum(array_column(end($pages), 'lines')) : 0;
    $canCombineSignaturePage = ($lineBudget - $lastPageLines) >= $signatureBlockLineCost;
    $totalPages = count($pages) + ($canCombineSignaturePage ? 0 : 1);

    // AllotmentReleaseOrderExport@afterSheet reads this hidden column to insert a
    // real PhpSpreadsheet page break and repeat the header block on every Excel
    // print page at the exact same boundaries computed above — column H is outside
    // the visible A:G layout so it never shows on screen or on paper.
    $pageBreakMarker = 'ARO_PAGE_BREAK';
@endphp
<table style="width: 100%; font-family: Roboto, Arial, sans-serif; font-size: 10px; color: #111827; border-collapse: collapse;">
    @foreach ($pages as $pageIndex => $pageUnits)
        @php
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

        <tr>
            <td colspan="7"></td>
            <td style="display:none;">{{ $pageIndex > 0 ? $pageBreakMarker : '' }}</td>
        </tr>

        <tr>
            <td colspan="5" style="text-align: left; font-size: 9px;">{{ $lbeFormNumber }}</td>
            <td colspan="2" style="text-align: left; font-size: 9px;">{{ in_array($aro->fund_source, ['Annual Budget', 'Annual Budget (Budget Ordinance)'], true) ? '■' : '□' }} Annual Budget{{ $aro->fund_source === 'Annual Budget (Budget Ordinance)' ? ' (Budget Ordinance'.($aro->realignment_no ? ' No. '.$aro->realignment_no : '').')' : '' }}</td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td colspan="2" style="text-align: left; font-size: 9px;">{{ $aro->fund_source === 'Supplemental Budget' ? '■' : '□' }} Supplemental Budget{{ $aro->supplemental_no ? ' No. '.$aro->supplemental_no : '' }}</td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td colspan="2" style="text-align: left; font-size: 9px;">{{ $aro->fund_source === 'Reenacted Budget' ? '■' : '□' }} Reenacted Budget</td>
        </tr>
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7" style="text-align:center; font-size: 13px; font-weight: bold; text-transform: uppercase;">{{ $formTitle }}</td></tr>
        <tr><td colspan="7" style="text-align:center; font-size: 12px; font-weight: bold;">FY {{ $aro->year }}</td></tr>
        <tr><td colspan="7"></td></tr>
        <tr>
            <td colspan="5" style="text-align: left; font-size: 11px;">Local Government Unit: BENGUET</td>
            @if ($fundCode)
                <td colspan="2" style="text-align: right; font-size: 11px;">Fund Code: {{ $fundCode }}</td>
            @else
                <td colspan="2"></td>
            @endif
        </tr>
        <tr>
            <td colspan="7" style="text-align: left; font-size: 11px;">Department/Office: {{ $departmentOfficeLabel }}</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: left; font-size: 11px;">Purpose:</td>
        </tr>
        <tr><td colspan="7"></td></tr>

        <tr style="background-color: #f3f4f6;">
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: bold;">PPA CODE</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: bold;">PPA DESCRIPTION</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: bold;">OBJECT CLASS/&#10;ACCOUNT CODE</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: bold;">AUTHORIZED&#10;APPROPRIATION</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: bold;">FOR LATER&#10;RELEASE</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: bold;">PREVIOUSLY&#10;RELEASED AMOUNT</td>
            <td style="border-top: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; padding: 4px 8px; text-align: center; font-weight: bold;">THIS RELEASE</td>
        </tr>
        <tr style="background-color: #f3f4f6;">
            <td style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">(1)</td>
            <td style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">(2)</td>
            <td style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">(3)</td>
            <td style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">(4)</td>
            <td style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">(5)</td>
            <td style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">(6)</td>
            <td style="border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center;">(7)</td>
        </tr>

        @foreach ($pageUnits as $unit)
            @if ($unit['type'] === 'office')
                {{-- SEF-consolidated ARO: bold header marking a new office's account codes --}}
                <tr style="background-color: #e5e7eb;">
                    <td style="border: 1px solid #000; padding: 4px;"></td>
                    <td colspan="6" style="border: 1px solid #000; padding: 4px; font-weight: bold; text-transform: uppercase;">{{ strtoupper($unit['text']) }}</td>
                </tr>
            @elseif ($unit['type'] === 'program')
                <tr>
                    <td style="border: 1px solid #000; padding: 4px;"></td>
                    <td colspan="6" style="border: 1px solid #000; padding: 4px; font-weight: bold;">{{ $unit['text'] }}</td>
                </tr>
            @elseif ($unit['type'] === 'item')
                @php
                    $item = $unit['item'];
                    // Lighter top/bottom border between individual account-code rows,
                    // matching the printed form's reference styling.
                    $topBorder = 'border-top: 1px solid #d1d5db;';
                    $bottomBorder = 'border-bottom: 1px solid #d1d5db;';
                    $sideBorder = 'border-left: 1px solid #000; border-right: 1px solid #000;';
                @endphp
                <tr>
                    @if ($unit['rowspan'] > 0)
                        <td style="{{ $sideBorder }} {{ $topBorder }} {{ $bottomBorder }} padding: 4px; text-align: center; vertical-align: top;" rowspan="{{ $unit['rowspan'] }}">{{ $unit['ppa_code'] }}</td>
                    @endif
                    <td style="{{ $sideBorder }} {{ $topBorder }} {{ $bottomBorder }} padding: 4px;">
                        {{ $item->ppa_description }}
                        @if ($aro->isPdfOffice() && $item->project_no)
                            <br><span style="font-size: 8px; color: #4b5563;">Project No: {{ $item->project_no }}</span>
                        @endif
                    </td>
                    <td style="{{ $sideBorder }} {{ $topBorder }} {{ $bottomBorder }} padding: 4px; text-align: center;">{{ $item->account_code }}</td>
                    <td style="{{ $sideBorder }} {{ $topBorder }} {{ $bottomBorder }} padding: 4px; text-align: right;">{{ number_format($item->authorized_appropriation, 2) }}</td>
                    <td style="{{ $sideBorder }} {{ $topBorder }} {{ $bottomBorder }} padding: 4px; text-align: right;">{{ $item->for_later_release > 0 ? number_format($item->for_later_release, 2) : '-' }}</td>
                    <td style="{{ $sideBorder }} {{ $topBorder }} {{ $bottomBorder }} padding: 4px; text-align: right;">{{ $item->previously_released_amount > 0 ? number_format($item->previously_released_amount, 2) : '-' }}</td>
                    <td style="{{ $sideBorder }} {{ $topBorder }} {{ $bottomBorder }} padding: 4px; text-align: right;">{{ number_format($item->this_release, 2) }}</td>
                </tr>
            @elseif ($unit['type'] === 'subtotal')
                <tr style="font-weight: bold;">
                    <td style="border: 1px solid #000; padding: 4px;"></td>
                    <td colspan="2" style="border: 1px solid #000; padding: 4px; text-align: right;">Subtotal</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ number_format($unit['subtotal']['authorized_appropriation'], 2) }}</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ $unit['subtotal']['for_later_release'] > 0 ? number_format($unit['subtotal']['for_later_release'], 2) : '-' }}</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ $unit['subtotal']['previously_released_amount'] > 0 ? number_format($unit['subtotal']['previously_released_amount'], 2) : '-' }}</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ number_format($unit['subtotal']['this_release'], 2) }}</td>
                </tr>
            @endif
        @endforeach

        @if ($isLastTablePage)
            <tr style="font-weight: bold;">
                <td colspan="3" style="border: 1px solid #000; padding: 4px; text-align: right;">TOTAL</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ number_format($aro->items->sum('authorized_appropriation'), 2) }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ $aro->items->sum('for_later_release') > 0 ? number_format($aro->items->sum('for_later_release'), 2) : '-' }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ $aro->items->sum('previously_released_amount') > 0 ? number_format($aro->items->sum('previously_released_amount'), 2) : '-' }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ number_format($total, 2) }}</td>
            </tr>
        @else
            <tr style="font-weight: bold; background-color: #f9fafb;">
                <td colspan="3" style="border: 1px solid #000; padding: 4px; text-align: right;">Page Subtotal</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ number_format($pageSubtotal['authorized_appropriation'], 2) }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ $pageSubtotal['for_later_release'] > 0 ? number_format($pageSubtotal['for_later_release'], 2) : '-' }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ $pageSubtotal['previously_released_amount'] > 0 ? number_format($pageSubtotal['previously_released_amount'], 2) : '-' }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right;">{{ number_format($pageSubtotal['this_release'], 2) }}</td>
            </tr>
        @endif

        @unless ($showSignaturesInline)
            <tr><td colspan="7"></td></tr>
            <tr style="font-size: 8px;">
                <td colspan="3">ARO No. {{ $aro->aro_no }}</td>
                <td colspan="2">Date of Issue: {{ $aro->date_of_issue->format('F j, Y') }}</td>
                <td colspan="2">Page {{ $pageIndex + 1 }} of {{ $totalPages }}</td>
            </tr>
        @endunless

        @if ($showSignaturesInline)
            @include('exports.aro_signature_block', ['aro' => $aro, 'total' => $total, 'totalPages' => $totalPages])
        @endif
    @endforeach

    @unless ($canCombineSignaturePage)
        <tr>
            <td colspan="7"></td>
            <td style="display:none;">{{ $pageBreakMarker }}</td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: left; font-size: 9px;">{{ $lbeFormNumber }}</td>
            <td colspan="2" style="text-align: left; font-size: 9px;">{{ in_array($aro->fund_source, ['Annual Budget', 'Annual Budget (Budget Ordinance)'], true) ? '■' : '□' }} Annual Budget{{ $aro->fund_source === 'Annual Budget (Budget Ordinance)' ? ' (Budget Ordinance'.($aro->realignment_no ? ' No. '.$aro->realignment_no : '').')' : '' }}</td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td colspan="2" style="text-align: left; font-size: 9px;">{{ $aro->fund_source === 'Supplemental Budget' ? '■' : '□' }} Supplemental Budget{{ $aro->supplemental_no ? ' No. '.$aro->supplemental_no : '' }}</td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td colspan="2" style="text-align: left; font-size: 9px;">{{ $aro->fund_source === 'Reenacted Budget' ? '■' : '□' }} Reenacted Budget</td>
        </tr>
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7" style="text-align:center; font-size: 13px; font-weight: bold; text-transform: uppercase;">{{ $formTitle }}</td></tr>
        <tr><td colspan="7" style="text-align:center; font-size: 12px; font-weight: bold;">FY {{ $aro->year }}</td></tr>
        <tr><td colspan="7"></td></tr>
        <tr>
            <td colspan="5" style="text-align: left; font-size: 11px;">Local Government Unit: BENGUET</td>
            @if ($fundCode)
                <td colspan="2" style="text-align: right; font-size: 11px;">Fund Code: {{ $fundCode }}</td>
            @else
                <td colspan="2"></td>
            @endif
        </tr>
        <tr>
            <td colspan="7" style="text-align: left; font-size: 11px;">Department/Office: {{ $departmentOfficeLabel }}</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: left; font-size: 11px;">Purpose:</td>
        </tr>
        <tr><td colspan="7"></td></tr>

        @include('exports.aro_signature_block', ['aro' => $aro, 'total' => $total, 'totalPages' => $totalPages])
    @endunless
</table>
