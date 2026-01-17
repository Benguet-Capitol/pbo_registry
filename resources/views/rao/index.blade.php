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

    <!-- Success Toast Notification -->
    <div id="success-toast" class="toast fixed top-6 right-6 z-50 transform transition-all duration-300 ease-in-out opacity-0 translate-x-96 pointer-events-none hide">
        <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-300 rounded-lg shadow-lg p-4 dark:bg-gradient-to-r dark:from-green-900 dark:to-green-800 dark:border-green-700">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-100" id="toast-message">File downloaded successfully</h3>
                    <p class="mt-1 text-sm text-green-700 dark:text-green-200">Your Excel report is ready.</p>
                </div>
                <button onclick="closeSuccessToast()" class="ml-4 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Toast Notification -->
    <div id="loading-toast" class="toast fixed top-6 right-6 z-50 transform transition-all duration-300 ease-in-out opacity-0 translate-x-96 pointer-events-none hide">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-300 rounded-lg shadow-lg p-4 dark:bg-gradient-to-r dark:from-blue-900 dark:to-blue-800 dark:border-blue-700">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.581 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-100">Generating Excel Report</h3>
                    <p class="mt-1 text-sm text-blue-700 dark:text-blue-200">Please wait while your file is being generated...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content Wrapper with Transition -->
    <div class="page-transition">

    <!-- Unified Filter Section -->
    <form method="GET" action="" class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-6 dark:bg-gray-800 transition-all duration-300 ease-in-out" id="filterForm">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
                Filters
            </h4>
            <!-- Shared validation message -->
            <span id="signatory_error" class="text-red-600 text-sm font-semibold mb-3 hidden block px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900 border-l-4 border-red-600 dark:border-red-400 animate-pulse transition-opacity duration-300 ease-in-out"></span>
            <!-- Shared validation message -->
            <span id="signatory_error" class="text-red-600 text-sm font-semibold mb-3 hidden block px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900 border-l-4 border-red-600 dark:border-red-400 animate-pulse transition-opacity duration-300 ease-in-out"></span>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 items-center mb-3">
                <!-- Year Filter -->
                <div class="flex items-center space-x-2">
                    <x-form.select
                        name="year1"
                        id="year1"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
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
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500" 
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
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                        onchange="this.form.submit()">
                    </x-form.input>
                </div>
                <!-- Signatory Name Filter -->
                <div class="flex items-center space-x-2">
                    <x-form.select
                        name="signatory_name"
                        id="signatory_name"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
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
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                        onchange="this.form.submit()">
                        <option value="">Select Designation</option>
                        <option value="Provincial Budget Officer" {{ request('signatory_designation') == 'Provincial Budget Officer' ? 'selected' : '' }}>Provincial Budget Officer</option>
                        <option value="Acting Provincial Budget Officer" {{ request('signatory_designation') == 'Acting Provincial Budget Officer' ? 'selected' : '' }}>Acting Provincial Budget Officer</option>
                        <option value="OIC, Provincial Budget Officer" {{ request('signatory_designation') == 'OIC, Provincial Budget Officer' ? 'selected' : '' }}>OIC, Provincial Budget Officer</option>
                    </x-form.select>
                </div>
            </div>

            <!-- Buttons Row -->
            <div class="flex items-center space-x-2 mt-4">
                <button
                    type="button"
                    onclick="exportRAOExcel()"
                    class="text-green-700 inline-flex leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4"></i>Generate Excel
                </button>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            <div class="overflow-x-auto overflow-y-auto max-h-[700px] border border-gray-300 dark:border-gray-600 rounded-md">
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

    </script>

    <script>
        function showSuccessToast(message = 'Success!') {
            const toast = document.getElementById('success-toast');
            const successMessage = document.getElementById('toast-message');
            if (successMessage) successMessage.textContent = message;
            if (toast) {
                toast.classList.remove('hide');
                toast.classList.add('show');
                setTimeout(() => closeSuccessToast(), 4000);
            }
        }

        function closeSuccessToast() {
            const toast = document.getElementById('success-toast');
            if (toast) {
                toast.classList.remove('show');
                toast.classList.add('hide');
            }
        }

        function showLoadingToast() {
            const toast = document.getElementById('loading-toast');
            if (toast) {
                toast.classList.remove('hide');
                toast.classList.add('show');
            }
        }

        function closeLoadingToast() {
            const toast = document.getElementById('loading-toast');
            if (toast) {
                toast.classList.remove('show');
                toast.classList.add('hide');
            }
        }

        async function exportRAOExcel() {
            if (!validateSignatories()) return;
            
            showLoadingToast();
            
            const params = new URLSearchParams({
                year1: document.getElementById('year1').value,
                office_allotment_class_filter: document.getElementById('officeAllotmentClass').value,
                as_of_filter: document.getElementById('as_of_filter').value,
                signatory_name: document.getElementById('signatory_name').value,
                signatory_designation: document.getElementById('signatory_designation').value
            });

            try {
                const response = await fetch(`/rao/export-excel?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Export failed');
                
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                
                // Generate filename based on selected office allotment class
                const officeSelect = document.getElementById('officeAllotmentClass');
                const selectedText = officeSelect.options[officeSelect.selectedIndex].text;
                const year = document.getElementById('year1').value;
                const filename = selectedText && selectedText !== 'Office Allotment Classes' 
                    ? `RAO_${selectedText.replace(' - ', '_')}_${year}.xlsx`
                    : `RAO_${year}.xlsx`;
                
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);

                closeLoadingToast();
                setTimeout(() => showSuccessToast('Excel file downloaded successfully!'), 500);
            } catch (error) {
                closeLoadingToast();
                console.error('Export error:', error);
                alert('Error exporting file: ' + error.message);
            }
        }


    </script>

    <!-- CSS Animations -->
    <style>
        @keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}@keyframes fadeIn{from{opacity:0}to{opacity:1}}@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}@keyframes pageSlideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}form{animation:slideUp .3s ease-in-out}.toast{opacity:0;transform:translateX(400px);pointer-events:none;transition:all .3s ease-in-out}.toast.show{opacity:1;transform:translateX(0);pointer-events:auto}.toast.hide{opacity:0;transform:translateX(400px);pointer-events:none}.page-transition{animation:pageSlideUp .4s ease-in-out}
    </style>

</x-app-layout>