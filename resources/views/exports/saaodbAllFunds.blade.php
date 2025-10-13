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
                ALL FUNDS
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
            <th style="padding: 4px; width: 100px; text-align: center;">Allotment Class</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Approved Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Supplemental Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Reversions</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Realignments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Allotments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">For Later Release</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Obligations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Balances from Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Percent of Obligations / Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Allotments Balance</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Percent of Obligations / Allotments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Disbursements</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Percent of Disbursements / Obligations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Percent of Disbursements / Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Obligations - Disbursements</th>
        </tr>
        <tr>
            <th colspan="17"> </th>
        </tr>
    </thead>

    <tbody style="border: 1px solid #d1d5db; font-size: 10px;">
        @foreach($funds as $fund)
        <tr>
            <td colspan="17" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc;">{{ $fund->fund }}</td>
        </tr>

        @php
            // Separate allotment classes into current and continuing (CCO)
            $currentClasses = $fund->allotmentClasses->filter(fn($c) => !str_contains(strtoupper($c->class), 'CCO'));
            $continuingClasses = $fund->allotmentClasses->filter(fn($c) => str_contains(strtoupper($c->class), 'CCO'));
        @endphp


         @foreach($currentClasses as $index => $class)
        <tr>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $class->class }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
                {{ number_format($class->approved_appropriation, 2) }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
                {{ number_format($class->supplemental, 2) }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
                {{ number_format($class->reversion, 2) }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
                {{ number_format($class->realignment, 2) }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
                {{ number_format($class->for_later_release, 2) }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
                {{ number_format($class->obligation, 2) }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
                {{ number_format($class->disbursement, 2) }}
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #999;">
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border: 1px solid #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border: 1px solid #999;">
            </td>
        </tr>
        @endforeach

       

        <tr class="grand-total-row" data-rowtype="grand-total">
            <td style="text-align: right; font-weight: bold; vertical-align: middle; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Grand Total: </td>
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
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;"></td>
        </tr>
        <tr>
            <td colspan="17"></td>
        </tr>
        @endforeach

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
            <td style="text-align: center; font-weight: bold; font-size: 12px;">
            </td>
            <td colspan="8" style="text-align: center; font-weight: bold; font-size: 12px;">
                {{ $preparedSignatoryName ? strtoupper($preparedSignatoryName) : '_____________________' }}
            </td>
            <td colspan="8" style="text-align: center; font-weight: bold; font-size: 12px;">
                {{ $certifiedSignatoryName ? strtoupper($certifiedSignatoryName) : '_____________________' }}
            </td>
        </tr>

        <tr>
            <td style="text-align: center; font-size: 12px;">
            </td>
            <td colspan="8" style="text-align: center; font-size: 12px;">
                {{ $preparedSignatoryDesignation ? $preparedSignatoryDesignation : '_____________________' }}
            </td>
            <td colspan="8" style="text-align: center; font-size: 12px;">
                {{ $certifiedSignatoryDesignation ? $certifiedSignatoryDesignation : '_____________________' }}
            </td>
        </tr>
    </tbody>
</table>