<table id="dashboardTable" style="width: 100%; font-family: Arial Narrow; font-size: 11px; color: #111827; text-align: left; border-collapse: collapse;">
    <thead style="background-color: #1e293b; color: #f1f5f9; border: 2px solid #e5e7eb;">
        <tr>
            <th colspan="13" style="text-align:center; padding: 10px; font-size: 11px;">Republic of the Philippines</th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center; font-size: 12px; font-weight: bold; text-transform: uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center; font-size: 11px;">La Trinidad, Benguet</th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center; font-size: 11px;">Provincial Budget Office</th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center; font-size: 14px; font-weight: bold; margin-top:10px; text-transform: uppercase;">
                STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES
            </th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center; font-size: 11px; font-weight: bold;">Summary of Appropriations, Allotments, Obligations and Balances - CY {{ $selectedYear }}</th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center; font-size: 11px; margin-top:5px; text-transform: uppercase; font-weight: bold;">
                {{ $selectedFundSource ?? 'Current and Continuing' }}
            </th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center; font-size: 11px;">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F j, Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="13"> </th>
        </tr>
        <tr>
            <th style="padding: 4px; width: 150px; text-align: center;">Fund</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Approved Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Supplemental Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Reversions</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Realignments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Allotment</th>
            <th style="padding: 4px; width: 100px; text-align: center;">For Later Release</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Obligations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Balance from Authorized Appropriations</th>
            <th style="padding: 4px; width: 80px; text-align: center;">Percent of Utilization</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Balance from Allotments</th>
            <th style="padding: 4px; width: 80px; text-align: center;">Percent of Utilization</th>
        </tr>
        <tr>
            <th colspan="13"> </th>
        </tr>
    </thead>

    <tbody style="border: 1px solid #d1d5db; font-size: 10px;">
        @foreach($fundSources as $fundSource)
        <tr>
            <td colspan="13" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc; text-align: center;">{{ strtoupper($fundSource['category']) }}</td>
        </tr>
        @foreach($fundSource['fund_types'] as $fundType)
        @foreach($fundType['funds'] as $fund)
        <tr>
            <td style="padding: 4px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">{{ $fund['fund'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;">{{ $fund['approved_appropriation'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;">{{ $fund['sb_appropriation'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;">{{ $fund['reversion'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;">{{ $fund['realignment'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;">{{ $fund['for_later_release'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;">{{ $fund['obligation'] }}</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; 
                                border-bottom: 1px hair #999;"></td>
        </tr>
        @endforeach
        <tr class="total" data-rowtype="total">
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">Total:</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;">%</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;">%</td>
        </tr>
        <tr>
            <td colspan="13"></td> {{-- spacing --}}
        </tr>
        @endforeach
        <tr class="grand-total-row" data-rowtype="grand-total">
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">Grand Total {{ $fundSource['category'] }}:</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;">%</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;">%</td>
        </tr>
        <tr>
            <td colspan="13"></td> {{-- spacing --}}
        </tr>
        @endforeach

        <tr class="certified-correct-row" data-rowtype="certified">
            <td colspan="7" style="padding-top: 30px; font-size: 12px; text-align: right;"><strong>Certified correct:</strong></td>
            <td colspan="6">

            </td>
        </tr>
        <tr>
            <td colspan="13"></td> {{-- spacing --}}
        </tr>
        <tr>
            <td colspan="7"></td>
            <td colspan="6" style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 12px;">
                {{ $signatoryName ? strtoupper($signatoryName) : '_____________________' }}
            </td>
        </tr>
        <tr>
            <td colspan="7"></td>
            <td colspan="6" style="text-align: center; font-size: 12px;">
                {{ $signatoryDesignation ? $signatoryDesignation : '_____________________' }}
            </td>
        </tr>
    </tbody>
</table>