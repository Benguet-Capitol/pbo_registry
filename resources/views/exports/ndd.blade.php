<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align:center; font-size: 11px;">Republic of the Philippines</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align:center; font-size: 12px; font-weight: bold;">PROVINCIAL GOVERNMENT OF BENGUET</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align:center; font-size: 11px;">La Trinidad, Benguet</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align:center; font-size: 11px;">Provincial Budget Office</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr>
            <th colspan="6" style="text-align:center; font-size: 14px; font-weight: bold;">LIST OF BUDGET UTILIZATIONS NOT YET DUE AND DEMANDABLE</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align:center; font-size: 11px;">Maintenance and Other Operating Expenses and Capital Outlay</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align:center; font-size: 11px;">{{ $selectedOffice->office_name }}</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align:center; font-size: 11px;">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F j, Y') }}</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>

        <tr>
            <th style="text-align: center;">Payee / Supplier / Particulars</th>
            <th style="text-align: center;">Budget Control No.</th>
            <th style="text-align: center;">PO Number</th>
            <th style="text-align: center;">PO Date</th>
            <th style="text-align: center;">Amount</th>
            <th style="text-align: center;">Remarks</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
    </thead>

    <tbody>
        @php
            $hasObligations = is_countable($obligations) ? count($obligations) > 0 : !empty($obligations);
        @endphp
        
        @if($hasObligations)
            @foreach($obligations as $officeName => $officeObligations)
                {{-- Office Header Row --}}
                <tr>
                    <td colspan="6" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc;">{{ $officeName }}</td>
                </tr>
                
                {{-- Obligations for this office --}}
                @foreach($officeObligations as $obligation)
                    <tr>
                        <td style="padding: 4px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">{{ $obligation['payee'] }}</td>
                        <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $obligation['budget_control_no'] }}</td>
                        <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $obligation['po_number'] }}</td>
                        <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $obligation['po_date'] }}</td>
                        <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ str_replace(',', '', $obligation['amount']) }}</td>
                        <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $obligation['remarks'] }}</td>
                    </tr>
                @endforeach
                
                {{-- Office Total Row --}}
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold; border: 1px solid #ccc;">Total ({{ $officeName }}):</td>
                    <td style="text-align: right; font-weight: bold; border: 1px solid #ccc;">{{ str_replace(',', '', $totals[$officeName]) }}</td>
                    <td style="text-align: right; font-weight: bold; border: 1px solid #ccc;"></td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                </tr>
            @endforeach
            
            {{-- Grand Total Row --}}
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold; border: 1px solid #ccc;">Grand Total:</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #ccc;">{{ str_replace(',', '', $totals['GRAND_TOTAL']) }}</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #ccc;"></td>
            </tr>
        @else
            <tr>
                <td colspan="6" style="text-align: center;">No obligations found matching the criteria.</td>
            </tr>
        @endif
        
        {{-- Spacing rows --}}
        <tr>
            <td colspan="6"></td>
        </tr>
        
        {{-- Certified Correct Section --}}
        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">Certified correct:</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="3" style="text-align: center; font-weight: bold; text-decoration: underline;">
                {{ $signatoryName ? strtoupper($signatoryName) : '_____________________' }}
            </td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="3" style="text-align: center;">
                {{ $signatoryDesignation ?? '_____________________' }}
            </td>
        </tr>
    </tbody>
</table>