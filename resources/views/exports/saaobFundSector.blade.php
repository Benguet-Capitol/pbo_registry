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
            <th colspan="14" style="text-align:center; font-size: 14px; font-weight: bold; margin-top:10px; text-transform: uppercase;">
                STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES
            </th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 14px; margin-top:10px; text-transform: uppercase; font-weight: bold;">
                (Current Legislative Appropriation)
            </th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 11px; margin-top:5px; text-transform: uppercase; font-weight: bold;">
                {{
                    $selectedFund === 'others'
                        ? 'Benguet General Hospital Economic Enterprise and Special Education Fund'
                        : ($selectedFund ?: 'All Funds')
                }}
            </th>
        </tr>
        <tr>
            <th colspan="14" style="text-align:center; font-size: 11px;">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F j, Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="14"> </th>
        </tr>
        <tr>
            <th style="padding: 4px; width: 80px; text-align: center;">Code</th>
            <th style="padding: 4px; width: 180px; text-align: center;">Function / Program / Project</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Approved Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Supplemental Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Reversions</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Realignments</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Authorized Appropriations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Allotment</th>
            <th style="padding: 4px; width: 100px; text-align: center;">For Later Release</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Obligations</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Authorized Appropriation Balance</th>
            <th style="padding: 4px; width: 80px; text-align: center;">Percent of Utilization</th>
            <th style="padding: 4px; width: 100px; text-align: center;">Balance</th>
            <th style="padding: 4px; width: 80px; text-align: center;">Percent of Utilization</th>
        </tr>
        <tr>
            <th colspan="14"> </th>
        </tr>
    </thead>

    <tbody style="border: 1px solid #d1d5db; font-size: 10px;">
        @foreach($groupedFunds as $fund)
        <tr>
            <td colspan="14" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc; text-align: center;">{{ strtoupper($fund->fund_type) }}</td>
        </tr>
        @foreach ($fund->matchedSectorsByCategory as $category => $sectors)
        <tr>
            <td colspan="14" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc;"> {{ $category }} Appropriations </td>
        </tr>
        @foreach($sectors as $sector)
        @if ($sector->sector_code !== '' && $sector->present_allotment_classes->isNotEmpty())
        <tr>
            <td style="padding: 8px 4px; font-weight: bold; text-align: center; border: 1px solid #ccc;">{{ $sector->sector_code }}</td>
            <td colspan="13" style="padding: 8px 4px; font-weight: bold; text-align: left; text-indent: 1px; border: 1px solid #ccc;">{{ $sector->sector }}</td>
        </tr>
        @foreach ($sector->present_allotment_classes as $aClass)
        <tr>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;"></td>
            <td style="padding: 4px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                {{ $aClass->description }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $aClass->approved_appropriation }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $aClass->supplemental }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $aClass->reversion }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $aClass->realignment }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $aClass->for_later_release }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $aClass->obligations }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                %</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                %</td>
        </tr>
        @endforeach
        <tr class="subtotal" data-rowtype="subtotal">
            <td colspan="2" style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">Total:</td>
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
            <td style="display: none;">subtotal</td>
        </tr>
        @endif
        @endforeach
        @php
        $hasValidSector = $sectors->contains(fn($s) =>
        $s->sector_code !== '' && $s->present_allotment_classes->isNotEmpty()
        );
        @endphp
        @if ($hasValidSector)
        <tr class="total" data-rowtype="total">
            <td colspan="2" style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total {{ $category }} Appropriations:</td>
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
            <td style="display: none;">total-subtotal</td>
        </tr>
        <tr>
            <td colspan="14"></td> {{-- spacing --}}
        </tr>
        @endif
        {{-- Display Allotment Class Totals Right After Category Totals --}}
        @if ($fund->categoryClassStats[$category]?->isNotEmpty())
        <tr>
            <td colspan="14" style="padding: 8px 4px; font-weight: bold; border: 1px solid #ccc;">{{ $category }} Appropriations by Allotment Class</td>
        </tr>

        @foreach ($fund->categoryClassStats[$category] as $classCode => $row)
        <tr>
            <td colspan="2" style="padding: 4px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                {{ $row->description }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $row->approved_appropriation }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $row->supplemental }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $row->reversion }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $row->realignment }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $row->for_later_release, }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                {{ $row->obligations }}
            </td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                %</td>
            <td style="padding: 4px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
            </td>
            <td style="padding: 4px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                %</td>
        </tr>

        @endforeach
        {{-- Totals per Category after Allotment Class Totals --}}
        <tr class="totalByAllotmentClass" data-rowtype="totalByAllotmentClass">
            <td colspan="2" style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999; white-space: normal; word-wrap: break-word;">Total {{ $category }} Appropriations:</td>
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
            <td style="display: none;">total-by-class</td>
        </tr>
        <tr>
            <td colspan="14"></td> {{-- spacing --}}
        </tr>
        @endif
        @endforeach
        <tr class="grand-total-row" data-rowtype="grand-total">
            <td colspan="2" style="padding: 4px; text-align: right; vertical-align: middle; font-weight: bold; border: 1px solid #999;">Grand Total {{ $fund->fund_type }}:</td>
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
            <td style="display: none;">grand-total</td>
        </tr>
        <tr>
            <td colspan="14"></td> {{-- spacing --}}
        </tr>
        @endforeach
        <tr class="certified-correct-row" data-rowtype="certified">
            <td colspan="7" style="padding-top: 30px; font-size: 12px; text-align: right;"><strong>Certified correct:</strong></td>
            <td colspan="7">

            </td>
        </tr>
        <tr>
            <td colspan="14"></td> {{-- spacing --}}
        </tr>
        <tr>
            <td colspan="7"></td>
            <td colspan="7" style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 12px;">
                {{ $signatoryName ? strtoupper($signatoryName) : '_____________________' }}
            </td>
        </tr>
        <tr>
            <td colspan="7"></td>
            <td colspan="7" style="text-align: center; font-size: 12px;">
                {{ $signatoryDesignation ? $signatoryDesignation : '_____________________' }}
            </td>
        </tr>
    </tbody>

</table>