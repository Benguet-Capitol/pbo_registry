@php
    // Calculate total columns: Date(1) + OBR No(1) + Particulars(1) + Total(1) + Appropriations count
    $totalColumns = 4 + $appropriations->count();
    
    // Helper function to group adjustments by unique reference
    $groupAdjustmentsByReference = function($adjustments) {
        return $adjustments->groupBy(function($item) {
            return $item['reference'] . '|' . $item['date'];
        });
    };
    
    // Initialize cumulative totals
    $cumulativeTotalPerQuarter = 0;
    $cumulativeTotalPerAppropriationPerQuarter = [];
    foreach($appropriations as $appropriation) {
        $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id] = 0;
    }
    
    // Initialize grand totals
    $grandTotalObligations = 0;
    $grandTotalObligationsByAppropriationId = [];
    foreach($appropriations as $appropriation) {
        $grandTotalObligationsByAppropriationId[$appropriation->id] = 0;
    }
@endphp

<table id="dashboardTable" style="width: 100%; font-family: Arial Narrow; font-size: 11px; color: #111827; text-align: left; border-collapse: collapse;">
    <thead style="background-color: #1e293b; color: #f1f5f9; border: 2px solid #e5e7eb;">
        <tr>
            <th colspan="{{ $totalColumns }}" style="text-align:center; padding: 10px; font-size: 11px;">Republic of the Philippines</th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}" style="text-align:center; font-size: 12px; font-weight: bold; text-transform: uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}" style="text-align:center; font-size: 11px;">La Trinidad, Benguet</th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}" style="text-align:center; font-size: 11px;">Provincial Budget Office</th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}"> </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}" style="text-align:center; font-size: 14px; font-weight: bold; margin-top:10px; text-transform: uppercase;">
                REGISTRY OF APPROPRIATIONS AND OBLIGATIONS
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}" style="text-align:center; font-size: 11px; margin-top:5px; font-weight: bold; text-transform: uppercase;">
                {{ $officeAllotmentClass ? $officeAllotmentClass->offices->office_name . ' - ' . $officeAllotmentClass->allotmentClass->description : 'N/A' }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}" style="text-align:center; font-size: 11px;">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F j, Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}"> </th>
        </tr>
        <tr>
            <th style="padding: 4px; width: 70px; text-align: center; border: 1px solid #000;" rowspan="2">Date</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #000;" rowspan="2">OBR No.</th>
            <th style="padding: 4px; width: 270px; text-align: center; border: 1px solid #000;" rowspan="2">Particulars</th>
            <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #000;" rowspan="2">Total</th>
            @if($selectedOfficeAllotmentClass && isset($appropriations) && $appropriations->count() > 0)
                @foreach($appropriations as $appropriation)
                <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #000;">{{ $appropriation->description }}</th>
                @endforeach
            @endif
        </tr>
        <tr>
            @if($selectedOfficeAllotmentClass && isset($appropriations) && $appropriations->count() > 0)
                @foreach($appropriations as $appropriation)
                <th style="padding: 4px; width: 100px; text-align: center; border: 1px solid #000;">{{ $appropriation->account_code }}</th>
                @endforeach
            @endif
        </tr>
        <tr>
            <th colspan="{{ $totalColumns }}"> </th>
        </tr>
    </thead>

    <tbody style="border: 1px solid #d1d5db; font-size: 10px;">
    @if($selectedOfficeAllotmentClass && isset($appropriations) && $appropriations->count() > 0)

        {{-- Appropriations Row --}}
        <tr style="background-color: #f9fafb;">
            <td colspan="3" style="padding: 4px 8px; text-align: left; border: 1px solid #000;">
                Appropriations
            </td>
            <td style="padding: 4px 8px; text-align: right; border: 1px solid #000;">
                {{ $totalAppropriations }}
            </td>
            @foreach($appropriations as $appropriation)
                <td style="padding: 4px 8px; text-align: right; border: 1px solid #000;">
                    {{ $appropriation->appropriation }}
                </td>
            @endforeach
        </tr>

        {{-- Supplemental Appropriations Row --}}
        <tr style="background-color: #f9fafb;">
            <td colspan="3" style="padding: 4px 8px; text-align: left; border: 1px solid #000;">
                Supplemental Appropriations
            </td>
            <td style="padding: 4px 8px; text-align: right; border: 1px solid #000;">
                {{ $totalSupplemental }}
            </td>
            @foreach($appropriations as $appropriation)
                <td style="padding: 4px 8px; text-align: right; border: 1px solid #000;">
                    {{ isset($appropriationData[$appropriation->id]['supplemental']) && $appropriationData[$appropriation->id]['supplemental'] > 0 ? $appropriationData[$appropriation->id]['supplemental'] : 0 }}
                </td>
            @endforeach
        </tr>

        {{-- Reversions Row --}}
        <tr style="background-color: #f9fafb;">
            <td colspan="3" style="padding: 4px 8px; text-align: left; border: 1px solid #000;">
                Reversions
            </td>
            <td style="padding: 4px 8px; text-align: right; border: 1px solid #000;">
                {{ $totalReversions }}
            </td>
            @foreach($appropriations as $appropriation)
                <td style="padding: 4px 8px; text-align: right; border: 1px solid #000;">
                    {{ isset($appropriationData[$appropriation->id]['reversion']) && $appropriationData[$appropriation->id]['reversion'] != 0 ? $appropriationData[$appropriation->id]['reversion'] : 0 }}
                </td>
            @endforeach
        </tr>

        {{-- Realignments Row --}}
        <tr style="background-color: #f9fafb;">
            <td colspan="3" style="padding: 4px 8px; text-align: left; border: 1px solid #000;">
                Realignments
            </td>
            <td style="padding: 4px 8px; text-align: right; border: 1px solid #000;">
                {{ $totalRealignments }}
            </td>
            @foreach($appropriations as $appropriation)
                <td style="padding: 4px 8px; text-align: right; border: 1px solid #000;">
                    {{ isset($appropriationData[$appropriation->id]['realignment']) && $appropriationData[$appropriation->id]['realignment'] != 0 ? $appropriationData[$appropriation->id]['realignment'] : 0 }}
                </td>
            @endforeach
        </tr>

        {{-- Total Appropriations Row --}}
        <tr style="background-color: #e5e7eb; font-weight: bold;">
            <td colspan="3" style="padding: 4px 8px; text-align: left; border: 1px solid #000; font-weight: bold;">
                Total Appropriations
            </td>
            <td style="padding: 4px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                {{ $grandTotal }}
            </td>
            @foreach($appropriations as $appropriation)
                <td style="padding: 4px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                    {{ $appropriationData[$appropriation->id]['total'] }}
                </td>
            @endforeach
        </tr>

        {{-- Empty Row Before Quarters --}}
        <tr>
            <td colspan="{{ $totalColumns }}"></td>
        </tr>

        {{-- Quarterly Data --}}
        @foreach([1, 2, 3, 4] as $quarter)
            @php
                $quarterLabel = ['1st', '2nd', '3rd', '4th'][$quarter - 1];
                $quarterField = 'quarter' . $quarter;
                $totalQuarter = ${'totalQuarter' . $quarter};
                
                // Group adjustments by reference
                $supplementalGroups = $groupAdjustmentsByReference($quarterlyAdjustments[$quarter]['supplementals']);
                $reversionGroups = $groupAdjustmentsByReference($quarterlyAdjustments[$quarter]['reversions']);
                $realignmentGroups = $groupAdjustmentsByReference($quarterlyAdjustments[$quarter]['realignments']);
                
                $hasAdjustments = $supplementalGroups->count() > 0 || $reversionGroups->count() > 0 || $realignmentGroups->count() > 0;
                
                // Get obligations for this quarter
                $quarterObligations = $quarterlyObligations[$quarter] ?? collect();
                $hasObligations = $quarterObligations->count() > 0;
            @endphp
            
            {{-- Quarter Header --}}
            <tr style="background-color: #f3f4f6; font-weight: 600;">
                <td colspan="{{ $totalColumns }}" style="padding: 4px 8px; border: 1px solid #000; font-weight: bold;">{{ $quarterLabel }} Quarter</td>
            </tr>
            
            {{-- Released Appropriation Row --}}
            @if($totalQuarter > 0)
                <tr style="background-color: #f9fafb;">
                    <td colspan="3" style="padding: 4px 8px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                        Released Appropriation
                    </td>
                    <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                        {{ $totalQuarter }}
                    </td>
                    @foreach($appropriations as $appropriation)
                        <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                            {{ isset($appropriationData[$appropriation->id][$quarterField]) && $appropriationData[$appropriation->id][$quarterField] > 0 ? $appropriationData[$appropriation->id][$quarterField] : '-' }}
                        </td>
                    @endforeach
                </tr>
            @endif
            
            @if($hasAdjustments)
                {{-- Supplementals --}}
                @foreach($supplementalGroups as $refKey => $items)
                    @php
                        [$reference, $date] = explode('|', $refKey);
                        $totalAmount = 0;
                        $amountsByAppropriationId = [];
                        foreach($items as $item) {
                            $totalAmount += $item['amount'];
                            $amountsByAppropriationId[$item['appropriation_id']] = ($amountsByAppropriationId[$item['appropriation_id']] ?? 0) + $item['amount'];
                        }
                    @endphp
                    <tr style="background-color: #f9fafb;">
                        <td colspan="3" style="padding: 4px 8px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                            Supplemental {{ $reference }} dated {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                        </td>
                        <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                            {{ $totalAmount }}
                        </td>
                        @foreach($appropriations as $appropriation)
                            <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                                {{ isset($amountsByAppropriationId[$appropriation->id]) && $amountsByAppropriationId[$appropriation->id] != 0 ? $amountsByAppropriationId[$appropriation->id] : '-' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                
                {{-- Reversions --}}
                @foreach($reversionGroups as $refKey => $items)
                    @php
                        [$reference, $date] = explode('|', $refKey);
                        $totalAmount = 0;
                        $amountsByAppropriationId = [];
                        foreach($items as $item) {
                            $totalAmount += $item['amount'];
                            $amountsByAppropriationId[$item['appropriation_id']] = ($amountsByAppropriationId[$item['appropriation_id']] ?? 0) + $item['amount'];
                        }
                    @endphp
                    <tr style="background-color: #f9fafb;">
                        <td colspan="3" style="padding: 4px 8px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                            Reversion {{ $reference }} dated {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                        </td>
                        <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                            {{ $totalAmount }}
                        </td>
                        @foreach($appropriations as $appropriation)
                            <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                                {{ isset($amountsByAppropriationId[$appropriation->id]) && $amountsByAppropriationId[$appropriation->id] != 0 ? $amountsByAppropriationId[$appropriation->id] : '-' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                
                {{-- Realignments --}}
                @foreach($realignmentGroups as $refKey => $items)
                    @php
                        [$reference, $date] = explode('|', $refKey);
                        $totalAmount = 0;
                        $amountsByAppropriationId = [];
                        foreach($items as $item) {
                            $totalAmount += $item['amount'];
                            $amountsByAppropriationId[$item['appropriation_id']] = ($amountsByAppropriationId[$item['appropriation_id']] ?? 0) + $item['amount'];
                        }
                    @endphp
                    <tr style="background-color: #f9fafb;">
                        <td colspan="3" style="padding: 4px 8px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                            Realignment {{ $reference }} dated {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                        </td>
                        <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                            {{ $totalAmount }}
                        </td>
                        @foreach($appropriations as $appropriation)
                            <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                                {{ isset($amountsByAppropriationId[$appropriation->id]) && $amountsByAppropriationId[$appropriation->id] != 0 ? $amountsByAppropriationId[$appropriation->id] : '-' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
            
            {{-- Total Released Appropriations Row --}}
            @php
                // Calculate current quarter released and adjustments
                $currentQuarterTotal = $totalQuarter;
                foreach($supplementalGroups as $items) {
                    foreach($items as $item) {
                        $currentQuarterTotal += $item['amount'];
                    }
                }
                foreach($reversionGroups as $items) {
                    foreach($items as $item) {
                        $currentQuarterTotal += $item['amount'];
                    }
                }
                foreach($realignmentGroups as $items) {
                    foreach($items as $item) {
                        $currentQuarterTotal += $item['amount'];
                    }
                }
                
                // Add previous quarter's cumulative total
                $grandTotalForQuarter = $cumulativeTotalPerQuarter + $currentQuarterTotal;
                
                // Update cumulative total for next quarter
                $cumulativeTotalPerQuarter = $grandTotalForQuarter;
            @endphp
            
            <tr style="background-color: #e5e7eb; font-weight: bold;">
                <td colspan="3" style="padding: 4px 8px; text-align: left; border: 1px solid #000; font-weight: bold;">
                    Total Released Appropriations
                </td>
                <td style="padding: 4px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                    {{ $grandTotalForQuarter }}
                </td>
                @foreach($appropriations as $appropriation)
                    @php
                        // Calculate current quarter for this appropriation
                        $appCurrentQuarter = $appropriationData[$appropriation->id][$quarterField] ?? 0;
                        
                        // Add supplementals for this appropriation
                        foreach($quarterlyAdjustments[$quarter]['supplementals'] as $item) {
                            if($item['appropriation_id'] == $appropriation->id) {
                                $appCurrentQuarter += $item['amount'];
                            }
                        }
                        
                        // Add reversions for this appropriation
                        foreach($quarterlyAdjustments[$quarter]['reversions'] as $item) {
                            if($item['appropriation_id'] == $appropriation->id) {
                                $appCurrentQuarter += $item['amount'];
                            }
                        }
                        
                        // Add realignments for this appropriation
                        foreach($quarterlyAdjustments[$quarter]['realignments'] as $item) {
                            if($item['appropriation_id'] == $appropriation->id) {
                                $appCurrentQuarter += $item['amount'];
                            }
                        }
                        
                        // Add previous quarter's cumulative total for this appropriation
                        $appTotal = $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id] + $appCurrentQuarter;
                        
                        // Update cumulative total for this appropriation for next quarter
                        $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id] = $appTotal;
                    @endphp
                    <td style="padding: 4px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                        {{ $appTotal }}
                    </td>
                @endforeach
            </tr>
            
            {{-- Obligations Section --}}
            @if($hasObligations)
                {{-- Obligations Header --}}
                <tr style="background-color: #dbeafe; font-weight: 600;">
                    <td colspan="{{ $totalColumns }}" style="padding: 4px 8px; text-align: left; border: 1px solid #000; font-weight: bold;">
                        Obligations
                    </td>
                </tr>
                
                {{-- Individual Obligations and Adjustments --}}
                @php
                    $quarterObligationTotal = 0;
                    $quarterObligationsByAppropriationId = [];
                    foreach($appropriations as $appropriation) {
                        $quarterObligationsByAppropriationId[$appropriation->id] = 0;
                    }
                @endphp
                
                @foreach($quarterObligations as $item)
                    @php
                        $quarterObligationTotal += $item['total_amount'];
                        foreach($item['amounts_by_appropriation'] as $appId => $amount) {
                            if(isset($quarterObligationsByAppropriationId[$appId])) {
                                $quarterObligationsByAppropriationId[$appId] += $amount;
                            }
                        }
                        
                        // Add to grand totals
                        $grandTotalObligations += $item['total_amount'];
                        foreach($item['amounts_by_appropriation'] as $appId => $amount) {
                            if(isset($grandTotalObligationsByAppropriationId[$appId])) {
                                $grandTotalObligationsByAppropriationId[$appId] += $amount;
                            }
                        }
                    @endphp
                    
                    @if($item['type'] == 'obligation')
                        {{-- Regular Obligation Row --}}
                        <tr style="background-color: #ffffff;">
                            <td style="padding: 4px 8px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                                {{ \Carbon\Carbon::parse($item['obr_date'])->format('m/d/Y') }}
                            </td>
                            <td style="padding: 4px 8px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                                {{ $item['obr_no'] }}
                            </td>
                            <td style="padding: 4px 8px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                                {{ $item['particulars'] }}
                            </td>
                            <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                                {{ $item['total_amount'] }}
                            </td>
                            @foreach($appropriations as $appropriation)
                                <td style="padding: 4px 8px; text-align: right; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                                    {{ isset($item['amounts_by_appropriation'][$appropriation->id]) && $item['amounts_by_appropriation'][$appropriation->id] > 0 ? $item['amounts_by_appropriation'][$appropriation->id] : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @else
                        {{-- Obligation Adjustment Row --}}
                        <tr style="background-color: #fef3c7;">
                            <td style="padding: 4px 8px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                                {{ \Carbon\Carbon::parse($item['adjustment_date'])->format('m/d/Y') }}
                            </td>
                            <td style="padding: 4px 8px; text-align: center; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                                {{ $item['obr_no'] }}
                            </td>
                            <td style="padding: 4px 8px; text-align: left; vertical-align: middle; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999; white-space: normal; word-wrap: break-word;">
                                {{ $item['particulars'] }}
                            </td>
                            <td style="padding: 4px 8px; text-align: right; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                                {{ $item['total_amount'] }}
                            </td>
                            @foreach($appropriations as $appropriation)
                                <td style="padding: 4px 8px; text-align: right; border-left: 1px solid #999; border-right: 1px solid #999; border-top: 1px hair #999; border-bottom: 1px hair #999;">
                                    {{ isset($item['amounts_by_appropriation'][$appropriation->id]) && $item['amounts_by_appropriation'][$appropriation->id] != 0 ? $item['amounts_by_appropriation'][$appropriation->id] : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
                
                {{-- Total Expenses Row --}}
                <tr style="background-color: #bfdbfe; font-weight: bold;">
                    <td colspan="3" style="padding: 4px 8px; text-align: left; border: 1px solid #000; font-weight: bold;">
                        Total Expenses ({{ $quarterLabel }} Quarter)
                    </td>
                    <td style="padding: 4px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                        {{ $quarterObligationTotal }}
                    </td>
                    @foreach($appropriations as $appropriation)
                        <td style="padding: 4px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                            {{ $quarterObligationsByAppropriationId[$appropriation->id] }}
                        </td>
                    @endforeach
                </tr>
                
                {{-- Balance from Released Appropriations Row --}}
                <tr style="background-color: #d1fae5; font-weight: bold;">
                    <td colspan="3" style="padding: 4px 8px; text-align: left; border: 1px solid #000; font-weight: bold;">
                        Balance from Released Appropriations ({{ $quarterLabel }} Quarter)
                    </td>
                    @php
                        // Use the cumulative total (grandTotalForQuarter already includes previous quarters)
                        $unobligatedBalance = $grandTotalForQuarter - $quarterObligationTotal;
                    @endphp
                    <td style="padding: 4px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                        {{ $unobligatedBalance }}
                    </td>
                    @foreach($appropriations as $appropriation)
                        @php
                            // Use the cumulative total for this appropriation (already stored in $cumulativeTotalPerAppropriationPerQuarter)
                            $appCumulativeTotal = $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id];
                            $appUnobligated = $appCumulativeTotal - ($quarterObligationsByAppropriationId[$appropriation->id] ?? 0);
                        @endphp
                        <td style="padding: 4px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                            {{ $appUnobligated }}
                        </td>
                    @endforeach
                </tr>
            @endif
            <tr>
                <td colspan="{{ $totalColumns }}"></td>
            </tr>
        @endforeach

        {{-- Grand Total Expenses Section --}}
        <tr style="background-color: #93c5fd; font-weight: bold; font-size: 11px;">
            <td colspan="3" style="padding: 6px 8px; text-align: left; border: 1px solid #000; font-weight: bold;">
                Grant Total Expenses
            </td>
            <td style="padding: 6px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                {{ $grandTotalObligations }}
            </td>
            @foreach($appropriations as $appropriation)
                <td style="padding: 6px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                    {{ $grandTotalObligationsByAppropriationId[$appropriation->id] }}
                </td>
            @endforeach
        </tr>

        {{-- Balance from Released Appropriation --}}
        <tr style="background-color: #a7f3d0; font-weight: bold; font-size: 11px;">
            <td colspan="3" style="padding: 6px 8px; text-align: left; border: 1px solid #000; font-weight: bold;">
                Balance from Released Appropriations
            </td>
            @php
                // Use the Total Released Appropriations from the quarters minus grand total expenses
                $grandTotalReleasedBalance = $cumulativeTotalPerQuarter - $grandTotalObligations;
            @endphp
            <td style="padding: 6px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                {{ $grandTotalReleasedBalance }}
            </td>
            @foreach($appropriations as $appropriation)
                @php
                    // Use Total Released Appropriations per appropriation minus expenses per appropriation
                    $appTotalReleased = $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id];
                    $appReleasedBalance = $appTotalReleased - $grandTotalObligationsByAppropriationId[$appropriation->id];
                @endphp
                <td style="padding: 6px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                    {{ $appReleasedBalance }}
                </td>
            @endforeach
        </tr>

        {{-- Balance from Authorized Appropriations Section --}}
        <tr style="background-color: #86efac; font-weight: bold; font-size: 11px;">
            <td colspan="3" style="padding: 6px 8px; text-align: left; border: 1px solid #000; font-weight: bold;">
                Balance from Authorized Appropriations
            </td>
            @php
                // Use the Total Appropriations from the top section minus grand total expenses
                $grandTotalBalance = $grandTotal - $grandTotalObligations;
            @endphp
            <td style="padding: 6px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                {{ $grandTotalBalance }}
            </td>
            @foreach($appropriations as $appropriation)
                @php
                    // Use Total Appropriations per appropriation minus expenses per appropriation
                    $appTotalAppropriation = $appropriationData[$appropriation->id]['total'] ?? 0;
                    $appGrandBalance = $appTotalAppropriation - $grandTotalObligationsByAppropriationId[$appropriation->id];
                @endphp
                <td style="padding: 6px 8px; text-align: right; border: 1px solid #000; font-weight: bold;">
                    {{ $appGrandBalance }}
                </td>
            @endforeach
        </tr>

    @else
        {{-- No office allotment class selected --}}
        <tr>
            <td colspan="4" style="padding: 20px; text-align: center;">
                Please select an Office Allotment Class to view the RAO report.
            </td>
        </tr>
    @endif

    {{-- Empty Row Before Signatory --}}
        <tr>
            <td colspan="{{ $totalColumns }}"></td>
        </tr>
        
        {{-- Certified Correct Label --}}
        <tr>
            <td colspan="{{ $totalColumns }}" style="padding: 8px; text-align: center; font-weight: bold;">
                CERTIFIED CORRECT:
            </td>
        </tr>
        
        {{-- Empty Row --}}
        <tr>
            <td colspan="{{ $totalColumns }}"></td>
        </tr>
        
        {{-- Signatory Name --}}
        <tr>
            <td colspan="3"></td>
            <td colspan="{{ $totalColumns - 3 }}" style="text-align: center; font-weight: bold; text-decoration: underline; font-size: 12px; padding: 4px;">
                {{ $signatoryName ? strtoupper($signatoryName) : '_____________________' }}
            </td>
        </tr>
        
        {{-- Signatory Designation --}}
        <tr>
            <td colspan="3"></td>
            <td colspan="{{ $totalColumns - 3 }}" style="text-align: center; font-size: 11px; padding: 4px;">
                {{ $signatoryDesignation ? $signatoryDesignation : '_____________________' }}
            </td>
        </tr>
    </tbody>
</table>