<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">

            <div>
                @php
                $selectedYear = request('year1', date('Y'));
                $selectedOfficeAllotmentClassId = request('office_allotment_class_filter');
                $selectedOfficeAllotmentClass = $officeAllotmentClasses
                    ->firstWhere('id', $selectedOfficeAllotmentClassId);
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Registry of Appropriations and Obligations') }}
                @if($selectedOfficeAllotmentClass)
                    |
                    <span class="text-blue-800 dark:text-blue-400">
                        {{ $selectedOfficeAllotmentClass->offices->office_name }}
                        -
                        {{ $selectedOfficeAllotmentClass->allotmentClass->description }}
                    </span>
                @endif
                <span class="text-blue-800 dark:text-blue-400">
                    (CY {{ $selectedYear }})
                </span>
            </h3>
            </div>
        </div>
    </x-slot>

    <!-- Unified Filter Section -->
    <form method="GET" action="" class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800" id="filterForm">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>
        <!-- Shared validation message -->
        <span id="signatory_error" class="text-red-500 text-xs mb-2 hidden"></span>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 items-center">
            <!-- Year Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="year1"
                    id="year1"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ request('year1', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Office and Allotment Class Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select 
                    name="office_allotment_class_filter" 
                    id="officeAllotmentClass" 
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" 
                    onchange="this.form.submit()">
                    <option value="">Office Allotment Classes</option>
                    @foreach($officeAllotmentClasses as $officeAllotmentClass)
                    <option value="{{ $officeAllotmentClass->id }}" {{ request('office_allotment_class_filter') == $officeAllotmentClass->id ? 'selected' : '' }}>
                        {{ $officeAllotmentClass->offices->office_abbreviation }} - {{ $officeAllotmentClass->allotmentClass->class }}
                    </option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- As of Filter -->
            <div class="flex items-center space-x-2">
                <x-form.input
                    name="as_of_filter"
                    type="date"
                    autocomplete="off"
                    id="as_of_filter"
                    value="{{ request('as_of_filter', now()->format('Y-m-d')) }}"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                </x-form.input>
            </div>
            <!-- Signatory Name Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="signatory_name"
                    id="signatory_name"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    <option value="">Select Signatory</option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->name }}" {{ request('signatory_name') == $employee->name ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Signatory Designation Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="signatory_designation"
                    id="signatory_designation"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    <option value="">Select Designation</option>
                    <option value="Provincial Budget Officer" {{ request('signatory_designation') == 'Provincial Budget Officer' ? 'selected' : '' }}>Provincial Budget Officer</option>
                    <option value="Acting Provincial Budget Officer" {{ request('signatory_designation') == 'Acting Provincial Budget Officer' ? 'selected' : '' }}>Acting Provincial Budget Officer</option>
                    <option value="OIC, Provincial Budget Officer" {{ request('signatory_designation') == 'OIC, Provincial Budget Officer' ? 'selected' : '' }}>OIC, Provincial Budget Officer</option>
                </x-form.select>
            </div>
            
    </form>
    <form method="GET" action="{{ route('rao.exportExcel') }}" style="display:inline;">
        <input type="hidden" name="year1" value="{{ request('year1') }}">
        <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
        <input type="hidden" name="as_of_filter" value="{{ request('as_of_filter') }}">
        <input type="hidden" name="signatory_name" value="{{ request('signatory_name') }}">
        <input type="hidden" name="signatory_designation" value="{{ request('signatory_designation') }}">

        <button type="submit" class="text-green-700 border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
            Generate Excel
        </button>
    </form>
    </div>


    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mt-6 mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="overflow-x-auto overflow-y-auto max-h-[700px]">
                <table id="dashboardTable" class="min-w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-gray-100 dark:bg-gray-200 dark:text-gray-900 border border-gray-300 dark:border-gray-600">
                        <tr>
                            <th class="px-1 py-1 min-w-[100px] text-center border border-gray-300 dark:border-gray-600" rowspan="2">Date</th>
                            <th class="px-1 py-1 min-w-[100px] text-center border border-gray-300 dark:border-gray-600" rowspan="2">OBR No.</th>
                            <th class="px-1 py-1 min-w-[300px] text-center border border-gray-300 dark:border-gray-600" rowspan="2">Particulars</th>
                            <th class="px-1 py-1 min-w-[150px] text-center border border-gray-300 dark:border-gray-600" rowspan="2">Total</th>
                            @if(request('office_allotment_class_filter') && isset($appropriations) && $appropriations->count() > 0)
                                @foreach($appropriations as $appropriation)
                                <th class="px-1 py-1 min-w-[150px] text-center border border-gray-300 dark:border-gray-600">{{ $appropriation->description }}</th>
                                @endforeach
                            @endif
                        </tr>
                        <tr>
                            @if(request('office_allotment_class_filter') && isset($appropriations) && $appropriations->count() > 0)
                                @foreach($appropriations as $appropriation)
                                <th class="px-1 py-1 text-center border border-gray-300 dark:border-gray-600">{{ $appropriation->account_code }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    @php
                        $formatAmount = function ($amount) {
                            if (is_null($amount) || $amount == 0) {
                                return '-';
                            }

                            return $amount < 0
                                ? '(' . number_format(abs($amount), 2) . ')'
                                : number_format($amount, 2);
                        };
                    @endphp
                    <tbody>
                    @if(request('office_allotment_class_filter') && isset($appropriations) && $appropriations->count() > 0)
                        {{-- First Row: Empty Space --}}
                        <tr>
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                        </tr>

                        {{-- Second Row: Appropriations --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600 font-semibold">
                                Appropriations
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalAppropriations, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ $formatAmount($appropriation->appropriation, 2) }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Third Row: Supplemental Appropriations --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600 font-semibold">
                                Supplemental Appropriations
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalSupplemental, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['supplemental']) && $appropriationData[$appropriation->id]['supplemental'] > 0 ? $formatAmount($appropriationData[$appropriation->id]['supplemental'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Fourth Row: Reversions --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600 font-semibold">
                                Reversions
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalReversions, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['reversion']) && $appropriationData[$appropriation->id]['reversion'] != 0 ? $formatAmount($appropriationData[$appropriation->id]['reversion'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Fifth Row: Realignments --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600 font-semibold">
                                Realignments
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalRealignments, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['realignment']) && $appropriationData[$appropriation->id]['realignment'] != 0 ? $formatAmount($appropriationData[$appropriation->id]['realignment'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Sixth Row: Total Appropriations (Sum of all above) --}}
                        <tr class="bg-gray-200 dark:bg-gray-600 font-bold">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Total Appropriations
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($grandTotal, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ $formatAmount($appropriationData[$appropriation->id]['total'], 2) }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Empty Row Before Quarters --}}
                        <tr>
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                        </tr>

                       @php
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
                            <tr class="bg-gray-100 dark:bg-gray-600 font-semibold">
                                <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600">{{ $quarterLabel }} Quarter</td>
                            </tr>
                            
                            {{-- Released Appropriation Row (only show if there's a value) --}}
                            @if($totalQuarter > 0)
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                        Released Appropriation
                                    </td>
                                    <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                        {{ $formatAmount($totalQuarter) }}
                                    </td>
                                    @foreach($appropriations as $appropriation)
                                        <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                            {{ isset($appropriationData[$appropriation->id][$quarterField]) && $appropriationData[$appropriation->id][$quarterField] > 0 ? $formatAmount($appropriationData[$appropriation->id][$quarterField]) : '-' }}
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
                                    <tr class="bg-gray-50 dark:bg-gray-800">
                                        <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                            Supplemental {{ $reference }} dated {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                                        </td>
                                        <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                            {{ $formatAmount($totalAmount) }}
                                        </td>
                                        @foreach($appropriations as $appropriation)
                                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                                {{ isset($amountsByAppropriationId[$appropriation->id]) && $amountsByAppropriationId[$appropriation->id] != 0 ? $formatAmount($amountsByAppropriationId[$appropriation->id]) : '-' }}
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
                                    <tr class="bg-gray-50 dark:bg-gray-800">
                                        <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                            Reversion {{ $reference }} dated {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                                        </td>
                                        <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                            {{ $formatAmount($totalAmount) }}
                                        </td>
                                        @foreach($appropriations as $appropriation)
                                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                                {{ isset($amountsByAppropriationId[$appropriation->id]) && $amountsByAppropriationId[$appropriation->id] != 0 ? $formatAmount($amountsByAppropriationId[$appropriation->id]) : '-' }}
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
                                    <tr class="bg-gray-50 dark:bg-gray-800">
                                        <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                            Realignment {{ $reference }} dated {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                                        </td>
                                        <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                            {{ $formatAmount($totalAmount) }}
                                        </td>
                                        @foreach($appropriations as $appropriation)
                                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                                {{ isset($amountsByAppropriationId[$appropriation->id]) && $amountsByAppropriationId[$appropriation->id] != 0 ? $formatAmount($amountsByAppropriationId[$appropriation->id]) : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                
                                {{-- Total Row for Quarter (Released + Adjustments + Previous Quarter Total) --}}
                                <tr class="bg-gray-200 dark:bg-gray-600 font-bold">
                                    <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                        Total Released Appropriations
                                    </td>
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
                                    <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                        {{ $formatAmount($grandTotalForQuarter) }}
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
                                        <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                            {{ $formatAmount($appTotal) }}
                                        </td>
                                    @endforeach
                                </tr>
                            
                            @else
                                {{-- If no adjustments, still calculate and display Total Released Appropriations --}}
                                @php
                                    // Calculate current quarter released (no adjustments)
                                    $currentQuarterTotal = $totalQuarter;
                                    
                                    // Add previous quarter's cumulative total
                                    $grandTotalForQuarter = $cumulativeTotalPerQuarter + $currentQuarterTotal;
                                    
                                    // Update cumulative total for next quarter
                                    $cumulativeTotalPerQuarter = $grandTotalForQuarter;
                                @endphp
                                
                                <tr class="bg-gray-200 dark:bg-gray-600 font-bold">
                                    <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                        Total Released Appropriations
                                    </td>
                                    <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                        {{ $formatAmount($grandTotalForQuarter) }}
                                    </td>
                                    @foreach($appropriations as $appropriation)
                                        @php
                                            // Calculate current quarter for this appropriation (no adjustments)
                                            $appCurrentQuarter = $appropriationData[$appropriation->id][$quarterField] ?? 0;
                                            
                                            // Add previous quarter's cumulative total for this appropriation
                                            $appTotal = $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id] + $appCurrentQuarter;
                                            
                                            // Update cumulative total for this appropriation for next quarter
                                            $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id] = $appTotal;
                                        @endphp
                                        <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                            {{ $formatAmount($appTotal) }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                            
                            {{-- Obligations Section --}}
                            @if($hasObligations)
                                {{-- Empty Row Before Obligations --}}
                                <tr>
                                    <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                                </tr>
                                
                                {{-- Obligations Header --}}
                                <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                                    <td colspan="{{ 4 + $appropriations->count() }}" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                        Obligations & Adjustments
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
                                        <tr class="bg-white dark:bg-gray-800">
                                            <td class="px-2 py-1 text-center border border-gray-300 dark:border-gray-600">
                                                {{ \Carbon\Carbon::parse($item['date'])->format('m/d/Y') }}
                                            </td>
                                            <td class="px-2 py-1 text-center border border-gray-300 dark:border-gray-600">
                                                {{ $item['obr_no'] }}
                                            </td>
                                            <td class="px-2 py-1 text-left border border-gray-300 dark:border-gray-600">
                                                {{ $item['particulars'] }}
                                            </td>
                                            <td class="px-2 py-1 text-right border border-gray-300 dark:border-gray-600">
                                                {{ $formatAmount($item['total_amount']) }}
                                            </td>
                                            @foreach($appropriations as $appropriation)
                                                <td class="px-2 py-1 text-right border border-gray-300 dark:border-gray-600">
                                                    {{ isset($item['amounts_by_appropriation'][$appropriation->id]) && $item['amounts_by_appropriation'][$appropriation->id] > 0 ? $formatAmount($item['amounts_by_appropriation'][$appropriation->id]) : '-' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @else
                                        {{-- Obligation Adjustment Row --}}
                                        <tr class="bg-yellow-50 dark:bg-yellow-900">
                                            <td class="px-2 py-1 text-center border border-gray-300 dark:border-gray-600">
                                                {{ \Carbon\Carbon::parse($item['date'])->format('m/d/Y') }}
                                            </td>
                                            <td class="px-2 py-1 text-center border border-gray-300 dark:border-gray-600">
                                                {{ $item['obr_no'] }}
                                            </td>
                                            <td class="px-2 py-1 text-left border border-gray-300 dark:border-gray-600">
                                                {{ $item['particulars'] }}
                                            </td>
                                            <td class="px-2 py-1 text-right border border-gray-300 dark:border-gray-600">
                                                {{ $formatAmount($item['total_amount']) }}
                                            </td>
                                            @foreach($appropriations as $appropriation)
                                                <td class="px-2 py-1 text-right border border-gray-300 dark:border-gray-600">
                                                    {{ isset($item['amounts_by_appropriation'][$appropriation->id]) && $item['amounts_by_appropriation'][$appropriation->id] != 0 ? $formatAmount($item['amounts_by_appropriation'][$appropriation->id]) : '-' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                                
                                {{-- Total Obligations Row --}}
                                <tr class="bg-gray-200 dark:bg-gray-800 font-bold">
                                    <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                        Total Expenses ({{ $quarterLabel }} Quarter)
                                    </td>
                                    <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                        {{ $formatAmount($quarterObligationTotal) }}
                                    </td>
                                    @foreach($appropriations as $appropriation)
                                        <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                            {{ $formatAmount($quarterObligationsByAppropriationId[$appropriation->id]) }}
                                        </td>
                                    @endforeach
                                </tr>
                                
                                {{-- Unobligated Balance Row --}}
                                <tr class="bg-gray-100 dark:bg-gray-900 font-bold">
                                    <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                        Balance from Released Appropriations ({{ $quarterLabel }} Quarter)
                                    </td>
                                    @php
                                        // Use the cumulative total (grandTotalForQuarter already includes previous quarters)
                                        $unobligatedBalance = $grandTotalForQuarter - $quarterObligationTotal;
                                    @endphp
                                    <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                        {{ $formatAmount($unobligatedBalance) }}
                                    </td>
                                    @foreach($appropriations as $appropriation)
                                        @php
                                            // Use the cumulative total for this appropriation (already stored in $cumulativeTotalPerAppropriationPerQuarter)
                                            $appCumulativeTotal = $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id];
                                            $appUnobligated = $appCumulativeTotal - ($quarterObligationsByAppropriationId[$appropriation->id] ?? 0);
                                        @endphp
                                        <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                            {{ $formatAmount($appUnobligated) }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        {{-- Empty Row Before Next Section --}}
                        <tr>
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                        </tr>
                        @endforeach

                        {{-- Grand Total Expenses Section --}}

                        <tr class="bg-gray-300 dark:bg-gray-700 font-bold">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-400 dark:border-gray-600">
                                Grand Total Expenses
                            </td>
                            <td class="px-2 py-2 text-right border border-gray-400 dark:border-gray-600">
                                {{ $formatAmount($grandTotalObligations) }}
                            </td>
                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-400 dark:border-gray-600">
                                    {{ $formatAmount($grandTotalObligationsByAppropriationId[$appropriation->id]) }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Balance from Released Appropriation Section --}}
                        <tr class="bg-gray-300 dark:bg-gray-700 font-bold">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-400 dark:border-gray-600">
                                Balance from Released Appropriations
                            </td>
                            @php
                                // Use the final cumulative total (from Q4) minus grand total obligations
                                $grandUnobligatedBalance = $cumulativeTotalPerQuarter - $grandTotalObligations;
                            @endphp
                            <td class="px-2 py-2 text-right border border-gray-400 dark:border-gray-600">
                                {{ $formatAmount($grandUnobligatedBalance) }}
                            </td>
                            @foreach($appropriations as $appropriation)
                                @php
                                    $appGrandUnobligated = $cumulativeTotalPerAppropriationPerQuarter[$appropriation->id] - $grandTotalObligationsByAppropriationId[$appropriation->id];
                                @endphp
                                <td class="px-2 py-2 text-right border border-gray-400 dark:border-gray-600">
                                    {{ $formatAmount($appGrandUnobligated) }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Balance from Authorized Appropriation Section --}}
                        <tr class="bg-gray-300 dark:bg-gray-700 font-bold">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-400 dark:border-gray-600">
                                Balance from Authorized Appropriations
                            </td>
                            @php
                                // Use the final cumulative total (from Q4) minus grand total obligations
                                $grandTotalBalance = $grandTotal - $grandTotalObligations;
                            @endphp
                            <td class="px-2 py-2 text-right border border-gray-400 dark:border-gray-600">
                                {{ $formatAmount($grandTotalBalance) }}
                            </td>
                            @foreach($appropriations as $appropriation)
                                @php
                                    // Use Total Appropriations per appropriation minus expenses per appropriation
                                    $appTotalAppropriation = $appropriationData[$appropriation->id]['total'] ?? 0;
                                    $appGrandBalance = $appTotalAppropriation - $grandTotalObligationsByAppropriationId[$appropriation->id];
                                @endphp
                                <td class="px-2 py-2 text-right border border-gray-400 dark:border-gray-600">
                                    {{ $formatAmount($appGrandBalance) }}
                                </td>
                            @endforeach
                        </tr>

                    @else
                        {{-- No office allotment class selected --}}
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-info-circle mr-2"></i>
                                Please select an Office Allotment Class to view appropriations and obligations.
                            </td>
                        </tr>
                    @endif
                    
                </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Validation for the signatory fields and office allotment class
    function validateSignatories() {
        const name = document.getElementById('signatory_name').value.trim();
        const designation = document.getElementById('signatory_designation').value.trim();
        const officeAllotmentClass = document.getElementById('officeAllotmentClass').value.trim();
        const errorSpan = document.getElementById('signatory_error');

        let errorMessage = '';
        
        // Check if office allotment class is selected
        if (!officeAllotmentClass) {
            errorMessage = 'Please select an Office Allotment Class.';
        }
        // Check signatory fields
        else if (!name && !designation) {
            errorMessage = 'Please select both Signatory Name and Designation.';
        } else if (!name) {
            errorMessage = 'Please select a Signatory Name.';
        } else if (!designation) {
            errorMessage = 'Please select a Designation.';
        }

        if (errorMessage) {
            errorSpan.textContent = errorMessage;
            errorSpan.classList.remove('hidden');
            return false;
        } else {
            errorSpan.classList.add('hidden');
            return true;
        }
    }

        // Intercept Excel Export Submit
        document.querySelector(`form[action="{{ route('rao.exportExcel') }}"]`)
            .addEventListener('submit', function(e) {
                if (!validateSignatories()) {
                    e.preventDefault();
                }
            });
    </script>

</x-app-layout>