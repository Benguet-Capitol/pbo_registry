<table id="dashboardTable" style="width: 100%; font-family: Arial Narrow; font-size: 11px; color: #111827; text-align: left; border-collapse: collapse;">
    <thead style="background-color: #1e293b; color: #f1f5f9; border: 2px solid #e5e7eb;">
        <tr>
            <th colspan="15" style="text-align:center; padding: 10px; font-size: 11px;">Republic of the Philippines</th>
        </tr>
        <tr>
            <th colspan="15" style="text-align:center; font-size: 12px; font-weight: bold; text-transform: uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</th>
        </tr>
        <tr>
            <th colspan="15" style="text-align:center; font-size: 11px;">La Trinidad, Benguet</th>
        </tr>
        <tr>
            <th colspan="15" style="text-align:center; font-size: 11px;">Provincial Budget Office</th>
        </tr>
        <tr>
            <th colspan="15"> </th>
        </tr>
        <tr>
            <th colspan="15" style="text-align:center; font-size: 14px; font-weight: bold; margin-top:10px; text-transform: uppercase;">
                STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES
            </th>
        </tr>
        <tr>
            <th colspan="15" style="text-align:center; font-size: 11px; margin-top:5px; font-weight: bold; text-transform: uppercase;">
                @if($isSEFConsolidated ?? false)
                    Special Education Fund
                @else
                    {{ isset($selectedOffice) && $selectedOffice ? ($offices->firstWhere('id', $selectedOffice)?->office_name ?? 'All Offices') : 'All Offices' }}
                @endif
                @if(!empty($accounts)) ({{ $accountCodeDisplay }}) @endif
            </th>
        </tr>
        <tr>
            <th colspan="15" style="text-align:center; font-size: 11px;">Current</th>
        </tr>
        <tr>
            <th colspan="15" style="text-align:center; font-size: 11px;">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F j, Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="15"> </th>
        </tr>
        <tr>
            <th style="padding: 4px; width: 220px; text-align: center;">Functions / Programs / Projects / Activities</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Account Code</th>
            <th style="padding: 4px; width: 70px; text-align: center;">FPP</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Approved Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Supplemental Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Reversions</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Realignments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Allotments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">For Later Release</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Obligations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Balances from Authorized Appropriations</th>
            <th style="padding: 4px; width: 80px; text-align: center;">Percent of Utilization</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Balances from Allotments</th>
            <th style="padding: 4px; width: 80px; text-align: center;">Percent of Utilization</th>
        </tr>
        <tr>
            <th colspan="15"> </th>
        </tr>
    </thead>

    <tbody style="border: 1px solid #d1d5db; font-size: 10px;">
        @foreach($offices as $office)
        <tr id="officeNameRow-{{ $office->id }}">
            <td colspan="15" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc;">{{ strtoupper($office->office_name) }}</td>
        </tr>

        @foreach ($office->officeAllotmentClasses as $oac)
        <tr id="allotmentClassRow-{{ $oac->id }}">
            <td colspan="15" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc; text-indent: 2px;">{{ strtoupper($oac->allotmentClass->description) }}</td>
        </tr>

        {{-- Appropriations WITHOUT a Program --}}
        @php
            // Check if there are any programs (non-empty keys)
            $hasPrograms = collect($oac->groupedAppropriations)->keys()->filter(fn($key) => $key !== '')->isNotEmpty();
        @endphp

        @if (isset($oac->groupedAppropriations['']) && count($oac->groupedAppropriations['']) > 0)
        @foreach ($oac->groupedAppropriations[''] as $appropriation)
        <tr class="content-row-without-program" data-rowtype="content">
            <td style="padding: 4px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                {{ $appropriation->description }}
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->account_code }}
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->fpp_code }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->appropriation }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->sb_appropriation }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->reversion }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->realignment }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->for_later_release }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->obligation }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
        </tr>
        @endforeach

        {{-- Subtotal row - only shows if there are also programmed appropriations --}}
        @if ($hasPrograms)
        <tr class="subtotal-row-without-program" data-rowtype="subtotal">
            <td colspan="3" style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">Subtotal:</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        @endif
        @endif

        {{-- Appropriations GROUPED BY Program --}}
        @foreach ($oac->groupedAppropriations as $program => $appropriations)
        @if ($program !== '')

        <tr id="programRow-{{ $loop->index }}-{{ $oac->id }}">
            <td colspan="15" style="padding: 8px 32px; font-weight: bold; text-indent: 3px; border: 1px solid #ccc; font-style: italic; font-size: 14px;">{{ strtoupper($program) }}</td>
        </tr>

        @foreach ($appropriations as $appropriation)
        <tr class="content-row-with-program" data-rowtype="content">
            <td style="padding: 4px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                {{ $appropriation->description }}
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->account_code }}
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->fpp_code }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->appropriation }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->sb_appropriation }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->reversion }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->realignment }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->for_later_release }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->obligation }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
        </tr>
        @endforeach

        <tr class="subtotal-row-with-program" data-rowtype="subtotal">
            <td colspan="3" style="padding: 8px 32px; text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word; overflow-wrap: break-word;">Subtotal:</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        @endif
        @endforeach

        <tr class="total-row" data-rowtype="total">
            <td colspan="3" style="padding: 4px; text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total {{ $oac->allotmentClass->description }}: </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        <tr>
            <td colspan="15"></td> {{-- spacing --}}
        </tr>
        @endforeach

        {{-- Per-Office Grand Total Row --}}
        <tr class="grand-total-row" data-rowtype="grand-total">
            <td colspan="3" style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">
                @if($isSEFConsolidated ?? false)
                    Grand Total {{ $office->office_name }}: 
                @else
                    Grand Total Current Operating Expenditures: 
                @endif
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        <tr>
            <td colspan="15"></td> {{-- spacing --}}
        </tr>
        @endforeach

        @if((empty($selectedOffice) || ($isSEFConsolidated ?? false)) && $overallTotal)
        <tr class="bg-blue-900 dark:bg-blue-800 text-white dark:text-gray-100 font-bold border-t-4 border-b-2 text-[11px]">
            <td colspan="3" style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">
                @if($isSEFConsolidated ?? false)
                    Grand Total Current Operating Expenditures (SEF):
                @else
                    Overall Total:
                @endif
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        @endif
        <tr>
            <td colspan="15"></td> {{-- spacing --}}
        </tr>

        <tr class="certified-correct-row" data-rowtype="certified">
            <td colspan="8" style="padding-top: 30px; font-size: 12px; text-align: right;"><strong>Certified correct:</strong></td>
            <td colspan="7">

            </td>
        </tr>
        <tr>
            <td colspan="15"></td> {{-- spacing --}}
        </tr>
        <tr>
            <td colspan="8"></td>
            <td colspan="7" style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 12px;">
                {{ $signatoryName ? strtoupper($signatoryName) : '_____________________' }}
            </td>
        </tr>
        <tr>
            <td colspan="8"></td>
            <td colspan="7" style="text-align: center; font-size: 12px;">
                {{ $signatoryDesignation ? $signatoryDesignation : '_____________________' }}
            </td>
        </tr>
       @if($isGuest)
        <tr>
            <td colspan="15"></td> {{-- spacing --}}
        </tr>
        <tr class="generated-footnote-row" data-rowtype="footnote">
            <td colspan="15" style="padding-top: 20px; font-size: 9px; font-style: italic; text-align: right; font-weight: bold;">
                Generated from PBO | Registry as of {{ now()->setTimezone('Asia/Manila')->format('F d, Y g:i A') }}
            </td>
        </tr>
        @endif
    </tbody>
</table>