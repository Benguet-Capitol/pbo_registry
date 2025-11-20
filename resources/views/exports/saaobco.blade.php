<table id="dashboardTable" style="width: 100%; font-family: Arial Narrow; font-size: 11px; color: #111827; text-align: left; border-collapse: collapse;">
    <thead style="background-color: #1e293b; color: #f1f5f9; border: 2px solid #e5e7eb;">
        <tr>
            <th colspan="16" style="text-align:center; padding: 10px; font-size: 11px;">Republic of the Philippines</th>
        </tr>
        <tr>
            <th colspan="16" style="text-align:center; font-size: 12px; font-weight: bold; text-transform: uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</th>
        </tr>
        <tr>
            <th colspan="16" style="text-align:center; font-size: 11px;">La Trinidad, Benguet</th>
        </tr>
        <tr>
            <th colspan="16" style="text-align:center; font-size: 11px;">Provincial Budget Office</th>
        </tr>
        <tr>
            <th colspan="16"> </th>
        </tr>
        <tr>
            <th colspan="16" style="text-align:center; font-size: 14px; font-weight: bold; margin-top:10px; text-transform: uppercase;">
                STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES
            </th>
        </tr>
        <tr>
            <th colspan="16" style="text-align:center; font-size: 11px; text-transform: uppercase; font-weight: bold;">
                {{ isset($selectedOffice) && $selectedOffice ? ($offices->firstWhere('id', $selectedOffice)?->office_name ?? 'All Offices') : 'All Offices' }}
                @if(isset($selectedAccountCode) && $selectedAccountCode)
                    ({{ $accountCodeDisplay }})
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="16" style="text-align:center; font-size: 11px;">Continuing</th>
        </tr>
        <tr>
            <th colspan="16" style="text-align:center; font-size: 11px;">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F j, Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="16"> </th>
        </tr>
        <tr>
            <th style="padding: 4px; width: 220px; text-align: center;">Functions / Programs / Projects / Activities</th>
            <th style="padding: 4px; width: 100px; text-align: center;">CCO Year</th>
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
            <th colspan="16"> </th>
        </tr>
    </thead>

    <tbody style="border: 1px solid #d1d5db; font-size: 10px;">
        @foreach($offices as $office)
        <tr id="officeNameRow-{{ $office->id }}">
            <td colspan="16" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc;">{{ strtoupper($office->office_name) }}</td>
        </tr>

        @foreach ($office->ccoYears as $year)
        <tr>
            <td colspan="16" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc; text-indent: 2px; text-align: left;">CY {{ $year }}</td>
        </tr>

        @foreach ($office->appropriationsByYear[$year] as $appropriation)
        <tr class="content-row-with-program" data-rowtype="content">
            <td style="padding: 4px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                {{ $appropriation->description }}
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $appropriation->cco_year }}
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
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
        </tr>
        @endforeach

        <tr class="total-row" data-rowtype="total">
            <td colspan="4" style="padding: 4px; text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total CY {{ $year }}: </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        <tr>
            <td colspan="16"></td> {{-- spacing --}}
        </tr>
        @endforeach

        <tr class="grand-total-row" data-rowtype="grand-total">
            <td colspan="4" style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Grand Total Continuing Capital Outlay: </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        <tr>
            <td colspan="16"></td> {{-- spacing --}}
        </tr>
        @endforeach

        <!-- Overall Total Row (only if all offices are selected) -->
        @if(empty($selectedOffice) && $overallTotal)
        <tr class="overall-total-row" data-rowtype="overall-total">
            <td colspan="4" style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">OVERALL TOTAL: </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['appropriation'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['sb'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['rev'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['realignment'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['authorized'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['allotment'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['for_later_release'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['obligation'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['appropriation_balance'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ number_format($overallTotal['appropriation_accomplishment'], 2) }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ $overallTotal['allotment_balance'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">{{ number_format($overallTotal['allotment_accomplishment'], 2) }}</td>
        </tr>
        <tr>
            <td colspan="16"></td> {{-- spacing --}}
        </tr>
        @endif

        <tr class="certified-correct-row" data-rowtype="certified">
            <td colspan="8" style="padding-top: 30px; font-size: 12px; text-align: right;"><strong>Certified correct:</strong></td>
            <td colspan="8">

            </td>
        </tr>
        <tr>
            <td colspan="16"></td> {{-- spacing --}}
        </tr>
        <tr>
            <td colspan="8"></td>
            <td colspan="8" style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 12px;">
                {{ $signatoryName ? strtoupper($signatoryName) : '_____________________' }}
            </td>
        </tr>
        <tr>
            <td colspan="8"></td>
            <td colspan="8" style="text-align: center; font-size: 12px;">
                {{ $signatoryDesignation ? $signatoryDesignation : '_____________________' }}
            </td>
        </tr>
    </tbody>
</table>