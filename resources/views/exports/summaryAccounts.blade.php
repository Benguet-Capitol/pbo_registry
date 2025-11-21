<table id="dashboardTable" style="width: 100%; font-family: Arial Narrow; font-size: 11px; color: #111827; text-align: left; border-collapse: collapse;">
    <thead style="background-color: #1e293b; color: #f1f5f9; border: 2px solid #e5e7eb;">
        <tr>
            <th colspan="14" style="text-align:center; padding: 10px; font-size: 11px;">Republic of the Philippines</th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 12px; font-weight: bold; text-transform: uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 11px;">La Trinidad, Benguet</th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 11px;">Provincial Budget Office</th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 11px;"> </th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 14px; font-weight: bold; margin-top:10px; text-transform: uppercase;">
                SUMMARY OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES
            </th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 11px; font-weight: bold; text-transform: uppercase;">{{ $displayFund }}</th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 11px;">Current</th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 11px;">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F j, Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="14"> </th>
        </tr>
        <tr style="background-color: #1e293b; color: white; font-weight: bold;">
            <th style="padding: 4px; width: 200px; text-align: center; border: 1px solid #ccc;">Functions / Programs / Projects / Activities</th>
            <th style="padding: 4px; width: 85px; text-align: center; border: 1px solid #ccc;">Account Code</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Approved Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Supplemental Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Reversions</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Realignments</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Allotments</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">For Later Release</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Obligations</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Balance from Authorized Appropriation</th>
            <th style="padding: 4px; width: 70px; text-align: center; border: 1px solid #ccc;">% Utilization (Auth.)</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #ccc;">Balance from Allotment</th>
            <th style="padding: 4px; width: 70px; text-align: center; border: 1px solid #ccc;">% Utilization (Allotment)</th>
        </tr>
        <tr>
            <td colspan="14"></td>
        </tr>
    </thead>

    <tbody style="border: 1px solid #d1d5db; font-size: 10px;">
        @forelse ($allotmentClassTotals as $className => $items)
            {{-- Allotment Class Header --}}
            <tr style="background-color: #4b5563; color: white; font-weight: bold;">
                <td colspan="14" style="padding: 8px 4px; text-transform: uppercase; border: 1px solid #ccc; font-weight: bold;">{{ $className }}</td>
            </tr>

            {{-- Individual Accounts with Appropriations --}}
            @if(isset($items['accounts']) && !empty($items['accounts']))
                @foreach ($items['accounts'] as $app)
                    <tr class="content-row" data-rowtype="content">
                        <td style="padding: 4px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">{{ $app['description'] }}</td>
                        <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['account_code'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['appropriation'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['sb_appropriation'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['reversion'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['realignment'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['authorized_appropriation'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['allotment'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['for_later_release'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['obligation'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['appropriation_balance'] }}</td>
                        <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['appropriation_accomplishment'] }}</td>
                        <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['allotment_balance'] }}</td>
                        <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $app['allotment_accomplishment'] }}</td>
                    </tr>
                @endforeach
            @endif

            {{-- Total Per Allotment Class --}}
            @if(isset($items['subtotals']))
                <tr class="total-row" data-rowtype="total" style="background-color: #d1d5db; font-weight: bold;">
                    <td colspan="2" style="padding: 4px; text-align: right; border: 1px solid #ccc;">Total {{ $className }}:</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['appropriation'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['sb_appropriation'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['reversion'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['realignment'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['authorized_appropriation'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['allotment'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['for_later_release'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['obligation'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['appropriation_balance'] }}</td>
                    <td style="padding: 4px; text-align: center; border: 1px solid #ccc;">{{ $items['subtotals']['utilization_percent'] }}</td>
                    <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $items['subtotals']['allotment_balance'] }}</td>
                    <td style="padding: 4px; text-align: center; border: 1px solid #ccc;">{{ $items['subtotals']['allotment_utilization_percent'] }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="14"></td>
            </tr>
        @empty
            <tr>
                <td colspan="14" style="padding: 4px; text-align: center; border: 1px solid #ccc;">No data available</td>
            </tr>
        @endforelse

        {{-- Grand Total Row --}}
        @php
            $grandTotals = [
                'appropriation' => 0,
                'sb_appropriation' => 0,
                'reversion' => 0,
                'realignment' => 0,
                'authorized_appropriation' => 0,
                'allotment' => 0,
                'for_later_release' => 0,
                'obligation' => 0,
                'appropriation_balance' => 0,
                'allotment_balance' => 0,
            ];
            
            foreach ($allotmentClassTotals as $items) {
                if (isset($items['subtotals'])) {
                    foreach ($grandTotals as $key => $val) {
                        $grandTotals[$key] += $items['subtotals'][$key] ?? 0;
                    }
                }
            }
            
            $grandUtilization = $grandTotals['authorized_appropriation'] > 0 
                ? ($grandTotals['obligation'] / $grandTotals['authorized_appropriation']) * 100 
                : 0;
            
            $grandAllotmentUtilization = $grandTotals['allotment'] > 0 
                ? ($grandTotals['obligation'] / $grandTotals['allotment']) * 100 
                : 0;
        @endphp

        <tr class="grand-total-row" data-rowtype="grand-total" style="background-color: #1a1a1a; color: white; font-weight: bold;">
            <td colspan="2" style="padding: 4px; text-align: right; border: 1px solid #ccc;">GRAND TOTAL:</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['appropriation'] }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['sb_appropriation'] }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['reversion'] }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['realignment'] }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['authorized_appropriation'] }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['allotment'] }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['for_later_release'] }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['obligation'] }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['appropriation_balance'] }}</td>
            <td style="padding: 4px; text-align: center; border: 1px solid #ccc;">{{ number_format($grandUtilization, 2) }}</td>
            <td style="padding: 4px; text-align: right; border: 1px solid #ccc;">{{ $grandTotals['allotment_balance'] }}</td>
            <td style="padding: 4px; text-align: center; border: 1px solid #ccc;">{{ number_format($grandAllotmentUtilization, 2) }}</td>
        </tr>

        <tr>
            <td colspan="14"></td> {{-- spacing --}}
        </tr>

        <tr class="certified-correct-row" data-rowtype="certified">
            <td colspan="9" style="padding-top: 30px; font-size: 12px; text-align: right;"><strong>Certified correct:</strong></td>
            <td colspan="5">

            </td>
        </tr>
        <tr>
            <td colspan="14"></td> {{-- spacing --}}
        </tr>
        <tr>
            <td colspan="9"></td>
            <td colspan="5" style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 12px;">
                {{ $signatoryName ? strtoupper($signatoryName) : '_____________________' }}
            </td>
        </tr>
        <tr>
            <td colspan="9"></td>
            <td colspan="5" style="text-align: center; font-size: 12px;">
                {{ $signatoryDesignation ? $signatoryDesignation : '_____________________' }}
            </td>
        </tr>
    </tbody>
</table>