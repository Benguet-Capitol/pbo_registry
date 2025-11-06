<table id="dashboardTable" style="width: 100%; font-family: Arial Narrow; font-size: 11px; color: #111827; text-align: left; border-collapse: collapse;">
    <thead style="background-color: #1e293b; color: #f1f5f9; border: 2px solid #e5e7eb;">
        <tr>
            <th colspan="17" style="text-align:center; padding: 10px; font-size: 11px;">Republic of the Philippines</th>
        </tr>
        <tr>
            <th colspan="17" style="text-align:center; font-size: 12px; font-weight: bold; text-transform: uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</th>
        </tr>
        <tr>
            <th colspan="17" style="text-align:center; font-size: 11px;">La Trinidad, Benguet</th>
        </tr>
        <tr>
            <th colspan="17" style="text-align:center; font-size: 11px;"> </th>
        </tr>
        <tr>
            <th colspan="17" style="text-align:center; font-size: 14px; font-weight: bold; margin-top:10px; text-transform: uppercase;">
                STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS, DISBURSEMENTS AND BALANCES
            </th>
        </tr>
        <tr>
            <th colspan="17" style="text-align:center; font-size: 11px; font-weight: bold; margin-top:5px;">
                GENERAL FUND
            </th>
        </tr>
        <tr>
            <th colspan="17" style="text-align:center; font-size: 11px; margin-top:5px;">
                Current and Continuing Appropriations
            </th>
        </tr>
        <tr>
            <th colspan="17" style="text-align:center; font-size: 11px;">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F j, Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="17"> </th>
        </tr>
        <tr>
            <th style="padding: 4px; width: 190px; text-align: center;">Allotment Class</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Approved Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Supplemental Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Reversions</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Realignments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Allotments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">For Later Release</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Obligations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Percent of Accomplishment (Obligations vs Authorized Appropriation)</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Unobligated Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Allotments Balance</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Percent of Obligations / Allotments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Actual Disbursements</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Percent of Accomplishment (Disbursements vs Authorized Appropriation)</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Unpaid Obligations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Percent of Accomplishment (Disbursements vs Obligations)</th>
        </tr>
        <tr>
            <th colspan="17"> </th>
        </tr>
    </thead>

    <tbody style="border: 1px solid #d1d5db; font-size: 10px;">
        @foreach($offices as $office)
        <tr>
            <td colspan="17" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc;">{{ $office->office_name }}</td>
        </tr>

        @php
            // Separate allotment classes into current and continuing (CCO)
            $currentClasses = $office->allotmentClasses->filter(fn($c) => !str_contains(strtoupper($c->class), 'CCO'));
            $continuingClasses = $office->allotmentClasses->filter(fn($c) => str_contains(strtoupper($c->class), 'CCO'));
        @endphp


            @foreach($currentClasses as $index => $class)
            <tr class="content-row-current">
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->class }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->approved_appropriation }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->supplemental }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->reversion }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->realignment }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->for_later_release }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->obligation }}
                </td>
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->disbursement }}
                </td>
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
            </tr>
            {{-- Insert total after the last current class --}}
                @if ($loop->last)
                <tr class="subtotal-row-current">
                    <td style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total Current Appropriation: </td>
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
                    <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                </tr>
                @endif
            @endforeach

            {{-- --- CONTINUING (CCO) CLASSES --- --}}
            @foreach($continuingClasses as $index => $class)
            <tr class="content-row-continuing">
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->class }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->approved_appropriation }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->supplemental }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->reversion }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->realignment }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->for_later_release }}
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->obligation }}
                </td>
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                    {{ $class->disbursement }}
                </td>
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
                <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                </td>
            </tr>
            {{-- Insert total after the last continuing class --}}
                @if ($loop->last)
                <tr class="subtotal-row-continuing">
                    <td style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total Continuing Capital Outlay: </td>
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
                    <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                    <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
                </tr>
                @endif
            @endforeach

        <tr class="total-row-current-continuing">
            <td style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total Current and Continuing: </td>
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
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        <tr>
            <td colspan="17"></td>
        </tr>
    @endforeach

        <tr class="grand-total-row" data-rowtype="grand-total">
            <td style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Grand Total General Fund Proper: </td>
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
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        <tr>
            <td colspan="17"></td>
        </tr>

        {{-- Summary --}}
        <tr>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Summary</td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total Appropriation</td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total Obligations</td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Percent of Accomplishment (Obligations vs Authorized Appropriation)</td>
            <td></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total Disbursements</td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Percent of Accomplishment (Disbursements vs Authorized Appropriation)</td>
            <td></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Percent of Accomplishment (Disbursements vs Obligations)</td>
        </tr>

        @foreach($allAllotmentClasses as $allotmentClass)
            @php
                $className = $allotmentClass->class;
            @endphp
            
            <tr>
                <td style="padding: 6px; text-align: center; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">{{ $className }}</td>
                <td style="padding: 6px; text-align: right; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
                <td style="padding: 6px; text-align: right; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
                <td style="padding: 6px; text-align: center; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
                <td></td>
                <td style="padding: 6px; text-align: right; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
                <td style="padding: 6px; text-align: center; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
                <td></td>
                <td style="padding: 6px; text-align: center; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
                <td colspan="9"></td>
            </tr>
        @endforeach

        {{-- Summary Grand Total Row --}}
        <tr style="background-color: #f3f4f6; font-weight: bold;">
            <td style="padding: 8px; text-align: right; font-weight: bold; border: 1px solid #999;">Grand Total General Fund Proper:</td>
            <td style="padding: 8px; text-align: right; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 8px; text-align: right; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 8px; text-align: center; font-weight: bold; border: 1px solid #999;"></td>
            <td></td>
            <td style="padding: 8px; text-align: right; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 8px; text-align: center; font-weight: bold; border: 1px solid #999;"></td>
            <td></td>
            <td style="padding: 8px; text-align: center; font-weight: bold; border: 1px solid #999;"></td>
            <td colspan="9"></td>
        </tr>
        <tr>
            <td colspan="17" style="padding-top: 10px;"></td>
        </tr>


        {{-- Prepared By (Left) + Certified Correct (Right) --}}
        <tr>
            <td colspan="3" style="padding-top: 30px; font-size: 12px; text-align: left;">
            </td>
            <td colspan="6" style="padding-top: 30px; font-size: 12px; text-align: left;">
                Prepared by:
            </td>
            <td colspan="1" style="padding-top: 30px; font-size: 12px; text-align: left;">
            </td>
            <td colspan="7" style="padding-top: 30px; font-size: 12px; text-align: left;">
                Certified correct:
            </td>
        </tr>
        <tr>
            <td colspan="17"></td>
        </tr>

        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold; font-size: 12px;">
            </td>
            <td colspan="7" style="text-align: center; font-weight: bold; font-size: 12px;">
                {{ $preparedSignatoryName ? strtoupper($preparedSignatoryName) : '_____________________' }}
            </td>
            <td colspan="7" style="text-align: center; font-weight: bold; font-size: 12px;">
                {{ $certifiedSignatoryName ? strtoupper($certifiedSignatoryName) : '_____________________' }}
            </td>
        </tr>

        <tr>
            <td colspan="3" style="text-align: center; font-size: 12px;">
            </td>
            <td colspan="7" style="text-align: center; font-size: 12px;">
                {{ $preparedSignatoryDesignation ? $preparedSignatoryDesignation : '_____________________' }}
            </td>
            <td colspan="7" style="text-align: center; font-size: 12px;">
                {{ $certifiedSignatoryDesignation ? $certifiedSignatoryDesignation : '_____________________' }}
            </td>
        </tr>
    </tbody>
</table>