<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Statement of Appropriations, Allotments, Obligations and Balances') }} |
                <span class="text-blue-800 dark:text-blue-400">
                    General Fund - Current
                    (CY {{ $selectedYear }})
                </span>
            </h3>
        </div>
    </x-slot>

    <!-- Success Toast Notification -->
    <div id="success-toast" class="fixed top-6 right-6 z-50 transform transition-all duration-300 ease-in-out opacity-0 translate-x-96 pointer-events-none">
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
    <div id="loading-toast" class="fixed top-6 right-6 z-50 transform transition-all duration-300 ease-in-out opacity-0 translate-x-96 pointer-events-none">
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

    <!-- Unified Filter Section -->
    <form method="GET" action="" class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-6 dark:bg-gray-800 transition-all duration-300 ease-in-out" id="filterForm">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
                    Filters
                </h4>
                <button
                    type="button"
                    onclick="clearAllFilters()"
                    class="text-xs text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 inline-flex items-center transition-colors duration-150"
                >
                    <i class="fas fa-rotate-left mr-1"></i>
                    Clear filters
                </button>
            </div>
            <!-- Shared validation message -->
            <span id="signatory_error" class="text-red-600 text-sm font-semibold mb-3 hidden block px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900 border-l-4 border-red-600 dark:border-red-400 transition-opacity duration-300 ease-in-out"></span>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <!-- Year Filter -->
                <div class="flex flex-col space-y-1">
                    <label for="year1" class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Year</label>
                    <x-form.select
                        name="year1"
                        id="year1"
                        aria-label="Fiscal Year"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                        onchange="this.form.submit()">
                        @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ request('year1', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <!-- As of Filter -->
                <div class="flex flex-col space-y-1">
                    <label for="as_of_filter" class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">As of</label>
                    <x-form.input
                        name="as_of_filter"
                        type="date"
                        autocomplete="off"
                        id="as_of_filter"
                        aria-label="As of Date"
                        value="{{ request('as_of_filter', now()->format('Y-m-d')) }}"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                        onchange="this.form.submit()">
                    </x-form.input>
                </div>
                <!-- Signatory Name Filter -->
                <div class="flex flex-col space-y-1">
                    <label for="signatory_name" class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Signatory <span class="text-red-500">*</span>
                    </label>
                    <x-form.select
                        name="signatory_name"
                        id="signatory_name"
                        aria-label="Signatory Name"
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
                <div class="flex flex-col space-y-1">
                    <label for="signatory_designation" class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Designation <span class="text-red-500">*</span>
                    </label>
                    <x-form.select
                        name="signatory_designation"
                        id="signatory_designation"
                        aria-label="Signatory Designation"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                        onchange="this.form.submit()">
                        <option value="">Select Designation</option>
                        <option value="Provincial Budget Officer" {{ request('signatory_designation') == 'Provincial Budget Officer' ? 'selected' : '' }}>Provincial Budget Officer</option>
                        <option value="Acting Provincial Budget Officer" {{ request('signatory_designation') == 'Acting Provincial Budget Officer' ? 'selected' : '' }}>Acting Provincial Budget Officer</option>
                        <option value="OIC, Provincial Budget Officer" {{ request('signatory_designation') == 'OIC, Provincial Budget Officer' ? 'selected' : '' }}>OIC, Provincial Budget Officer</option>
                    </x-form.select>
                </div>
            </div>

            @php
                // Year and As-of-date are excluded here: both always carry a default value
                // (current year, today), so treating them as removable "active" filters would
                // leave two permanent chips cluttering this row on every page load.
                $activeChips = [];
                if(request('signatory_name')) $activeChips[] = ['label' => 'Signatory', 'value' => request('signatory_name'), 'param' => 'signatory_name'];
                if(request('signatory_designation')) $activeChips[] = ['label' => 'Designation', 'value' => request('signatory_designation'), 'param' => 'signatory_designation'];
                if(request('year1')) $activeChips[] = ['label' => 'Year', 'value' => request('year1'), 'param' => 'year1'];
                if(request('as_of_filter')) $activeChips[] = ['label' => 'As of', 'value' => request('as_of_filter'), 'param' => 'as_of_filter'];
            @endphp
            @if(count($activeChips) > 0)
            <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <span class="text-[11px] text-gray-400 dark:text-gray-500 uppercase tracking-wide">Active:</span>
                @foreach($activeChips as $chip)
                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200 text-[11px] font-medium px-2 py-1 rounded-full">
                    {{ $chip['label'] }}: {{ $chip['value'] }}
                    <button type="button" onclick="removeFilter('{{ $chip['param'] }}')" class="hover:text-red-600 dark:hover:text-red-400" aria-label="Remove {{ $chip['label'] }} filter">
                        <i class="fas fa-times text-[9px]"></i>
                    </button>
                </span>
                @endforeach
            </div>
            @endif

            <!-- Buttons Row -->
            <div class="flex items-center space-x-2 mt-4">
                <button
                    onclick="printSAAOBGFCurrentTable()"
                    class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                    type="button"
                    id="print-btn">
                    <i class="fas fa-print text-lg mr-2 -ml-1 w-4 h-4" id="print-btn-icon"></i>
                    <span id="print-btn-label">Print Report</span>
                </button>

                <button
                    type="button"
                    onclick="exportSAAOBGFCurrentExcel()"
                    class="text-green-700 inline-flex leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                    id="excel-export-btn">
                    <i class="fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4" id="excel-btn-icon"></i>
                    <span id="excel-btn-label">Generate Excel</span>
                </button>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            @if($sectors->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-3"></i>
                <p class="text-sm font-medium">No records found for the selected filters</p>
                <button type="button" onclick="clearAllFilters()" class="mt-3 text-xs text-blue-600 hover:underline dark:text-blue-400">Clear filters and try again</button>
            </div>
            @else
            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
            <div class="max-h-[720px] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <table id="saaobGFCurrentTable" class="w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 border border-blue-400 bg-gradient-to-r from-blue-200 to-blue-300 text-blue-900 dark:bg-gradient-to-r dark:from-blue-700 dark:to-blue-600 dark:text-blue-200 transition-colors duration-300 ease-in-out">
                        <tr>
                            <th rowspan="2" class="px-1 py-1 w-[60px] text-center align-middle border-r border-blue-400 dark:border-blue-300">Code</th>
                            <th rowspan="2" class="px-1 py-1 w-[170px] text-center align-middle border-r border-blue-400 dark:border-blue-300">Function / Program / Project</th>
                            <th colspan="5" class="px-1 py-1 text-center border-r border-blue-400 dark:border-blue-300">Appropriations</th>
                            <th colspan="2" class="px-1 py-1 text-center border-r border-blue-400 dark:border-blue-300">Allotments</th>
                            <th rowspan="2" class="px-1 py-1 w-[100px] text-center align-middle border-r border-blue-400 dark:border-blue-300">Obligations</th>
                            <th colspan="2" class="px-1 py-1 text-center border-r border-blue-400 dark:border-blue-300">Balance from Appropriation</th>
                            <th colspan="2" class="px-1 py-1 text-center">Balance from Allotment</th>
                        </tr>
                        <tr class="text-[10px]">
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation">Approved</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="sb_appropriation">Supplemental</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="reversion">Reversions</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="realignment">Realignments</th>
                            <th class="px-1 py-1 w-[100px] text-center border-r border-blue-400 dark:border-blue-300">Authorized</th>
                            <th class="px-1 py-1 w-[100px] text-center">Allotments</th>
                            <th class="px-1 py-1 w-[100px] text-center border-r border-blue-400 dark:border-blue-300">For Later Release</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation_balance">Balance</th>
                            <th class="px-1 py-1 w-[70px] text-center border-r border-blue-400 dark:border-blue-300" data-key="appropriation_accomplishment">% Used</th>
                            <th class="px-1 py-1 w-[100px] text-center">Balance</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="allotment_accomplishment">% Used</th>
                        </tr>
                    </thead>
                    <tbody class="border border-gray-300 dark:border-gray-600 text-[10px]">
                        @foreach($sectors as $sector)
                        <tr id="sectorRow" class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900 text-blue-900 dark:text-blue-200 uppercase font-bold border-t border-b border-blue-300 dark:border-blue-800 text-center text-sm transition-colors duration-300 ease-in-out">
                            <td colspan="14" class="px-2 py-3">{{ $sector->sector }}</td>
                        </tr>
                        @foreach($sector->offices as $office)
                        @php
                        $fppCode = $office->fpp_code;

                        // Override for special offices
                        if (in_array($office->office_abbreviation, ['PEO', 'PDF'])) {
                        $fppCode = $sector->sector_code;
                        }

                        $rowIndex = 0;
                        @endphp
                        <tr id="officeRow" class="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 text-xs italic font-semibold border-t border-b border-gray-300 dark:border-gray-600 transition-colors duration-300 ease-in-out">
                            <td class="px-6 py-2 text-xs text-left">{{ $fppCode }}</td>
                            <td colspan="13" class="px-6 py-2 text-xs text-left">{{ $office->office_name }}</td>
                        </tr>
                        @foreach ($office->officeAllotmentClasses as $oac)
                        @php $rowIndex++; @endphp
                        <tr class="{{ $rowIndex % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800/60' : 'bg-white dark:bg-gray-800' }} border-b border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                            <td class="px-2 py-2 font-semibold text-left"></td>
                            <td class="px-2 py-2 font-semibold text-left">{{ $oac->allotmentClass->description }}</td>
                            <td class="px-2 py-2 font-semibold text-right" data-key="appropriation">
                                {{ $oac->appropriation > 0
                                    ? number_format($oac->appropriation, 2)
                                    : ($oac->appropriation < 0
                                        ? '(' . number_format(abs($oac->appropriation), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                {{ $oac->sb_appropriation > 0
                                    ? number_format($oac->sb_appropriation, 2)
                                    : ($oac->sb_appropriation < 0
                                        ? '(' . number_format(abs($oac->sb_appropriation), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right" data-key="reversion">
                                {{ $oac->reversion > 0
                                    ? number_format($oac->reversion, 2)
                                    : ($oac->reversion < 0
                                        ? '(' . number_format(abs($oac->reversion), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right" data-key="realignment">
                                {{ $oac->realignment > 0
                                    ? number_format($oac->realignment, 2)
                                    : ($oac->realignment < 0
                                        ? '(' . number_format(abs($oac->realignment), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 font-semibold text-right">
                                {{ $oac->authorized_appropriation > 0
                                    ? number_format($oac->authorized_appropriation, 2)
                                    : ($oac->authorized_appropriation < 0
                                        ? '(' . number_format(abs($oac->authorized_appropriation), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 font-semibold text-right">
                                {{ $oac->allotment > 0
                                    ? number_format($oac->allotment, 2)
                                    : ($oac->allotment < 0
                                        ? '(' . number_format(abs($oac->allotment), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right">
                                {{ $oac->for_later_release > 0
                                    ? number_format($oac->for_later_release, 2)
                                    : ($oac->for_later_release < 0
                                        ? '(' . number_format(abs($oac->for_later_release), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 font-semibold text-right">
                                {{ $oac->obligations > 0
                                    ? number_format($oac->obligations, 2)
                                    : ($oac->obligations < 0
                                        ? '(' . number_format(abs($oac->obligations), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                {{ $oac->authorized_balance > 0
                                    ? number_format($oac->authorized_balance, 2)
                                    : ($oac->authorized_balance < 0
                                        ? '(' . number_format(abs($oac->authorized_balance), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $oac->authorized_accomplishment ?? 0])
                            </td>
                            <td class="px-2 py-2 text-right">
                                {{ $oac->allotment_balance > 0
                                    ? number_format($oac->allotment_balance, 2)
                                    : ($oac->allotment_balance < 0
                                        ? '(' . number_format(abs($oac->allotment_balance), 2) . ')'
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-center" data-key="allotment_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $oac->allotment_accomplishment ?? 0])
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-300 text-gray-900 dark:bg-gray-700 dark:text-gray-100 font-semibold border-t-2 border-b-2 border-gray-400 dark:border-gray-600 transition-colors duration-300 ease-in-out">
                            <td colspan="2" class="px-2 py-2 text-right">Subtotal:</td>
                            <td class="px-2 py-2 text-right" data-key="appropriation">
                                {{ $office->totals['appropriation'] > 0
                                    ? number_format($office->totals['appropriation'], 2)
                                    : ($office->totals['appropriation'] < 0
                                        ? '(' . number_format(abs($office->totals['appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                {{ $office->totals['sb_appropriation'] > 0
                                    ? number_format($office->totals['sb_appropriation'], 2)
                                    : ($office->totals['sb_appropriation'] < 0
                                        ? '(' . number_format(abs($office->totals['sb_appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="reversion">
                                {{ $office->totals['reversion'] > 0
                                    ? number_format($office->totals['reversion'], 2)
                                    : ($office->totals['reversion'] < 0
                                        ? '(' . number_format(abs($office->totals['reversion']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="realignment">
                                {{ $office->totals['realignment'] > 0
                                    ? number_format($office->totals['realignment'], 2)
                                    : ($office->totals['realignment'] < 0
                                        ? '(' . number_format(abs($office->totals['realignment']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $office->totals['authorized_appropriation'] > 0
                                    ? number_format($office->totals['authorized_appropriation'], 2)
                                    : ($office->totals['authorized_appropriation'] < 0
                                        ? '(' . number_format(abs($office->totals['authorized_appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $office->totals['allotment'] > 0
                                    ? number_format($office->totals['allotment'], 2)
                                    : ($office->totals['allotment'] < 0
                                        ? '(' . number_format(abs($office->totals['allotment']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $office->totals['for_later_release'] > 0
                                    ? number_format($office->totals['for_later_release'], 2)
                                    : ($office->totals['for_later_release'] < 0
                                        ? '(' . number_format(abs($office->totals['for_later_release']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $office->totals['obligations'] > 0
                                    ? number_format($office->totals['obligations'], 2)
                                    : ($office->totals['obligations'] < 0
                                        ? '(' . number_format(abs($office->totals['obligations']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                {{ $office->totals['authorized_balance'] > 0
                                    ? number_format($office->totals['authorized_balance'], 2)
                                    : ($office->totals['authorized_balance'] < 0
                                        ? '(' . number_format(abs($office->totals['authorized_balance']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $office->totals['authorized_accomplishment'] ?? 0])
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $office->totals['allotment_balance'] > 0
                                    ? number_format($office->totals['allotment_balance'], 2)
                                    : ($office->totals['allotment_balance'] < 0
                                        ? '(' . number_format(abs($office->totals['allotment_balance']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-center" data-key="allotment_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $office->totals['allotment_accomplishment'] ?? 0])
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gradient-to-r from-gray-300 to-gray-400 text-gray-900 dark:from-gray-700 dark:to-gray-800 dark:text-gray-100 font-bold border-t-2 border-b-2 border-gray-500 dark:border-gray-600">
                            <td colspan="2" class="px-2 py-2 text-right">Total ({{ $sector->sector }}):</td>
                            <td class="px-2 py-2 text-right" data-key="appropriation">
                                {{ $sector->totals['appropriation'] > 0
                                    ? number_format($sector->totals['appropriation'], 2)
                                    : ($sector->totals['appropriation'] < 0
                                        ? '(' . number_format(abs($sector->totals['appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                {{ $sector->totals['sb_appropriation'] > 0
                                    ? number_format($sector->totals['sb_appropriation'], 2)
                                    : ($sector->totals['sb_appropriation'] < 0
                                        ? '(' . number_format(abs($sector->totals['sb_appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="reversion">
                                {{ $sector->totals['reversion'] > 0
                                    ? number_format($sector->totals['reversion'], 2)
                                    : ($sector->totals['reversion'] < 0
                                        ? '(' . number_format(abs($sector->totals['reversion']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="realignment">
                                {{ $sector->totals['realignment'] > 0
                                    ? number_format($sector->totals['realignment'], 2)
                                    : ($sector->totals['realignment'] < 0
                                        ? '(' . number_format(abs($sector->totals['realignment']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $sector->totals['authorized_appropriation'] > 0
                                    ? number_format($sector->totals['authorized_appropriation'], 2)
                                    : ($sector->totals['authorized_appropriation'] < 0
                                        ? '(' . number_format(abs($sector->totals['authorized_appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $sector->totals['allotment'] > 0
                                    ? number_format($sector->totals['allotment'], 2)
                                    : ($sector->totals['allotment'] < 0
                                        ? '(' . number_format(abs($sector->totals['allotment']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $sector->totals['for_later_release'] > 0
                                    ? number_format($sector->totals['for_later_release'], 2)
                                    : ($sector->totals['for_later_release'] < 0
                                        ? '(' . number_format(abs($sector->totals['for_later_release']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $sector->totals['obligations'] > 0
                                    ? number_format($sector->totals['obligations'], 2)
                                    : ($sector->totals['obligations'] < 0
                                        ? '(' . number_format(abs($sector->totals['obligations']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                {{ $sector->totals['authorized_balance'] > 0
                                    ? number_format($sector->totals['authorized_balance'], 2)
                                    : ($sector->totals['authorized_balance'] < 0
                                        ? '(' . number_format(abs($sector->totals['authorized_balance']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $sector->totals['authorized_accomplishment'] ?? 0])
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $sector->totals['allotment_balance'] > 0
                                    ? number_format($sector->totals['allotment_balance'], 2)
                                    : ($sector->totals['allotment_balance'] < 0
                                        ? '(' . number_format(abs($sector->totals['allotment_balance']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-center" data-key="allotment_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $sector->totals['allotment_accomplishment'] ?? 0])
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-blue-200 dark:bg-blue-950 text-blue-900 dark:text-blue-100 font-bold text-right uppercase border-t-4 border-blue-500 dark:border-blue-800 border-b-2">
                            <td colspan="2" class="px-1 py-3 text-right">Grand Total:</td>
                            <td class="px-1 py-3 text-right" data-key="appropriation">
                                {{ $grandTotals['appropriation'] > 0
                                    ? number_format($grandTotals['appropriation'], 2)
                                    : ($grandTotals['appropriation'] < 0
                                        ? '(' . number_format(abs($grandTotals['appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right" data-key="sb_appropriation">
                                {{ $grandTotals['sb_appropriation'] > 0
                                    ? number_format($grandTotals['sb_appropriation'], 2)
                                    : ($grandTotals['sb_appropriation'] < 0
                                        ? '(' . number_format(abs($grandTotals['sb_appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right" data-key="reversion">
                                {{ $grandTotals['reversion'] > 0
                                    ? number_format($grandTotals['reversion'], 2)
                                    : ($grandTotals['reversion'] < 0
                                        ? '(' . number_format(abs($grandTotals['reversion']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right" data-key="realignment">
                                {{ $grandTotals['realignment'] > 0
                                    ? number_format($grandTotals['realignment'], 2)
                                    : ($grandTotals['realignment'] < 0
                                        ? '(' . number_format(abs($grandTotals['realignment']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotals['authorized_appropriation'] > 0
                                    ? number_format($grandTotals['authorized_appropriation'], 2)
                                    : ($grandTotals['authorized_appropriation'] < 0
                                        ? '(' . number_format(abs($grandTotals['authorized_appropriation']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotals['allotment'] > 0
                                    ? number_format($grandTotals['allotment'], 2)
                                    : ($grandTotals['allotment'] < 0
                                        ? '(' . number_format(abs($grandTotals['allotment']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotals['for_later_release'] > 0
                                    ? number_format($grandTotals['for_later_release'], 2)
                                    : ($grandTotals['for_later_release'] < 0
                                        ? '(' . number_format(abs($grandTotals['for_later_release']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotals['obligations'] > 0
                                    ? number_format($grandTotals['obligations'], 2)
                                    : ($grandTotals['obligations'] < 0
                                        ? '(' . number_format(abs($grandTotals['obligations']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right" data-key="appropriation_balance">
                                {{ $grandTotals['authorized_balance'] > 0
                                    ? number_format($grandTotals['authorized_balance'], 2)
                                    : ($grandTotals['authorized_balance'] < 0
                                        ? '(' . number_format(abs($grandTotals['authorized_balance']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-center" data-key="appropriation_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $grandTotals['authorized_accomplishment'] ?? 0])
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotals['allotment_balance'] > 0
                                    ? number_format($grandTotals['allotment_balance'], 2)
                                    : ($grandTotals['allotment_balance'] < 0
                                        ? '(' . number_format(abs($grandTotals['allotment_balance']), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-center" data-key="allotment_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $grandTotals['allotment_accomplishment'] ?? 0])
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    </form>

    <style>
        .filter-select {
            transition: all 0.2s ease-in-out;
        }

        .filter-select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        /* Table row hover animations */
        tbody tr {
            transition: all 0.2s ease-in-out;
        }

        /* Header sticky effect with smooth animation */
        thead {
            animation: slideDown 0.3s ease-in-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Gradient text for section headers */
        tr[id^="sectorRow"] {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Form container entrance animation */
        form {
            animation: slideUp 0.3s ease-in-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Table container entrance animation */
        .bg-white.overflow-hidden.shadow-md {
            animation: slideUp 0.4s ease-in-out 0.1s both;
        }

        /* Enhanced scrollbar styling for table */
        .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
            transition: background 0.2s ease-in-out;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Dark mode scrollbar */
        .dark .overflow-y-auto::-webkit-scrollbar-track {
            background: #1f2937;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        /* Smooth staggered table rendering */
        tbody tr {
            opacity: 1;
            animation: tableRowFade 0.3s ease-in-out;
        }

        @keyframes tableRowFade {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Focus indicator enhancement */
        .filter-select:focus-visible {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

        /* Toast notification animations */
        @keyframes slideInToast {
            from {
                opacity: 0;
                transform: translateX(400px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutToast {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(400px);
            }
        }

        #success-toast.show {
            animation: slideInToast 0.3s ease-out forwards;
            pointer-events: auto;
        }

        #success-toast.hide {
            animation: slideOutToast 0.3s ease-in forwards;
            pointer-events: none;
        }

        #loading-toast.show {
            animation: slideInToast 0.3s ease-out forwards;
            pointer-events: auto;
        }

        #loading-toast.hide {
            animation: slideOutToast 0.3s ease-in forwards;
            pointer-events: none;
        }
    </style>

    <script>
        // Validation for the signatory fields
        function validateSignatories() {
            const name = document.getElementById('signatory_name').value.trim();
            const designation = document.getElementById('signatory_designation').value.trim();
            const errorSpan = document.getElementById('signatory_error');

            let errorMessage = '';
            if (!name && !designation) {
                errorMessage = 'Please select both Signatory Name and Designation.';
            } else if (!name) {
                errorMessage = 'Please select a Signatory Name.';
            } else if (!designation) {
                errorMessage = 'Please select a Designation.';
            }

            if (errorMessage) {
                errorSpan.textContent = errorMessage;
                errorSpan.classList.remove('hidden');
                errorSpan.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                return false;
            } else {
                errorSpan.classList.add('hidden');
                return true;
            }
        }

        // Clear filters (keeps year, resets everything else)
        function clearAllFilters() {
            const url = new URL(window.location.href);
            const keep = ['year1'];
            [...url.searchParams.keys()].forEach(key => {
                if (!keep.includes(key)) url.searchParams.delete(key);
            });
            window.location.href = url.toString();
        }

        // Remove a single filter param and resubmit
        function removeFilter(param) {
            const url = new URL(window.location.href);
            url.searchParams.delete(param);
            window.location.href = url.toString();
        }

        // Toast notification functions
        function showSuccessToast(message = 'File downloaded successfully') {
            const toast = document.getElementById('success-toast');
            const messageEl = document.getElementById('toast-message');
            if (messageEl) messageEl.textContent = message;

            toast.classList.remove('hide', 'pointer-events-none');
            toast.classList.add('show');

            setTimeout(closeSuccessToast, 4000);
        }

        function closeSuccessToast() {
            const toast = document.getElementById('success-toast');
            toast.classList.remove('show');
            toast.classList.add('hide', 'pointer-events-none');
        }

        function showErrorToast(message) {
            const toast = document.getElementById('success-toast');
            const messageEl = document.getElementById('toast-message');
            const box = toast.querySelector('div');
            if (messageEl) messageEl.textContent = message;
            box.classList.remove('from-green-50', 'to-green-100', 'border-green-300', 'dark:from-green-900', 'dark:to-green-800', 'dark:border-green-700');
            box.classList.add('from-red-50', 'to-red-100', 'border-red-300', 'dark:from-red-900', 'dark:to-red-800', 'dark:border-red-700');
            toast.classList.remove('hide', 'pointer-events-none');
            toast.classList.add('show');
            setTimeout(() => {
                closeSuccessToast();
                box.classList.remove('from-red-50', 'to-red-100', 'border-red-300', 'dark:from-red-900', 'dark:to-red-800', 'dark:border-red-700');
                box.classList.add('from-green-50', 'to-green-100', 'border-green-300', 'dark:from-green-900', 'dark:to-green-800', 'dark:border-green-700');
            }, 4000);
        }

        function showLoadingToast() {
            const toast = document.getElementById('loading-toast');
            toast.classList.remove('hide', 'pointer-events-none');
            toast.classList.add('show');
        }

        function closeLoadingToast() {
            const toast = document.getElementById('loading-toast');
            toast.classList.remove('show');
            toast.classList.add('hide', 'pointer-events-none');
        }

        // Excel export function using Fetch API
        async function exportSAAOBGFCurrentExcel() {
            if (!validateSignatories()) return;

            const btn = document.getElementById('excel-export-btn');
            const icon = document.getElementById('excel-btn-icon');
            const label = document.getElementById('excel-btn-label');
            btn.disabled = true;
            icon.className = 'fas fa-spinner fa-spin text-lg mr-2 -ml-1 w-4 h-4';
            label.textContent = 'Generating...';

            showLoadingToast();

            try {
                const params = new URLSearchParams({
                    year1: document.getElementById('year1').value,
                    as_of_filter: document.getElementById('as_of_filter').value,
                    signatory_name: document.getElementById('signatory_name').value,
                    signatory_designation: document.getElementById('signatory_designation').value
                });

                const response = await fetch(`{{ route('saaobGFCurrent.exportExcel') }}?${params}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Export failed');
                }

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const year = document.getElementById('year1')?.value || new Date().getFullYear();
                a.download = `SAAOB_GF_Current_${year}.xlsx`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();

                closeLoadingToast();
                showSuccessToast('Excel report generated successfully');
            } catch (error) {
                console.error('Export error:', error);
                closeLoadingToast();
                showErrorToast('Could not generate the Excel report. Please try again.');
            } finally {
                btn.disabled = false;
                icon.className = 'fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4';
                label.textContent = 'Generate Excel';
            }
        }

        // Intercept PDF generation
        window.printSAAOBGFCurrentTable = function() {
            if (!validateSignatories()) return;

            const btn = document.getElementById('print-btn');
            const icon = document.getElementById('print-btn-icon');
            const label = document.getElementById('print-btn-label');
            btn.disabled = true;
            icon.className = 'fas fa-spinner fa-spin text-lg mr-2 -ml-1 w-4 h-4';
            label.textContent = 'Preparing...';

            // Give the browser a tick to paint the loading state before the print window blocks the thread
            setTimeout(() => {
                try {
                    runPrintSAAOBGFCurrentTable();
                } finally {
                    btn.disabled = false;
                    icon.className = 'fas fa-print text-lg mr-2 -ml-1 w-4 h-4';
                    label.textContent = 'Print Report';
                }
            }, 50);
        };

        function runPrintSAAOBGFCurrentTable() {
            const table = document.getElementById('saaobGFCurrentTable').cloneNode(true);
            const hiddenKeys = [
                'appropriation', 'sb_appropriation', 'reversion', 'realignment',
                'appropriation_balance', 'appropriation_accomplishment'
            ];

            table.querySelectorAll('thead th[data-key], tbody td[data-key]').forEach(cell => {
                const key = cell.getAttribute('data-key');
                if (hiddenKeys.includes(key)) {
                    cell.remove();
                }
            });

            // Adjust the grouped colspans in the first header row since some sub-columns were removed
            const firstHeaderRow = table.querySelector('thead tr:first-child');
            if (firstHeaderRow) {
                const groupCells = firstHeaderRow.querySelectorAll('th[colspan]');
                groupCells.forEach(th => {
                    const text = th.textContent.trim();
                    if (text === 'Appropriations') th.setAttribute('colspan', '1');
                    if (text === 'Balance from Appropriation') th.remove();
                });
            }

            // Styling rows
            table.querySelectorAll('[id^="sectorRow"]').forEach(tr => {
                tr.style.textTransform = 'uppercase';
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
            });

            table.querySelectorAll('[id^="officeRow"]').forEach(tr => {
                tr.style.fontWeight = 'bold';
                tr.style.fontStyle = 'italic';
                tr.style.fontSize = '10px';
                const firstCell = tr.querySelector('td');
                if (firstCell) firstCell.style.paddingLeft = '32px';
            });

            table.querySelectorAll('tbody').forEach(tbody => {
                tbody.style.fontSize = '10px';
            });

            table.querySelectorAll('tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                for (let i = 2; i < cells.length; i++) {
                    cells[i].style.textAlign = 'right';
                }
            });

            table.querySelectorAll('tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (cells.length > 2) {
                    const text = cells[0].textContent.trim().toUpperCase();
                    if (text.startsWith('SUBTOTAL') || text.startsWith('TOTAL') || text.startsWith('GRAND TOTAL')) {
                        tr.style.fontWeight = 'bold';
                        cells[0].style.textAlign = 'right';
                        cells[1].style.textAlign = 'right';
                        cells[2].style.textAlign = 'right';
                    }
                }
            });

            table.querySelectorAll('thead th').forEach(th => {
                th.style.textAlign = 'center';
                th.style.fontWeight = 'bold';
                th.style.fontSize = '10px';
            });

            // Format As of date
            const asOfInput = document.getElementById('as_of_filter');
            let asOfDate = '';
            if (asOfInput && asOfInput.value) {
                const dateObj = new Date(asOfInput.value);
                const monthNames = [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ];
                const month = monthNames[dateObj.getMonth()];
                const day = dateObj.getDate();
                const year = dateObj.getFullYear();
                asOfDate = `${month} ${day}, ${year}`;
            }

            const signatoryName = document.getElementById('signatory_name').value.trim();
            const signatoryDesignation = document.getElementById('signatory_designation').value.trim();

            const yearSelect = document.getElementById('year1');
            const selectedYear = yearSelect?.value || new Date().getFullYear();

            // Get screen dimensions
            const screenW = window.screen.availWidth;
            const screenH = window.screen.availHeight;

            const newWin = window.open('', '', `width=${screenW},height=${screenH},left=0,top=0,scrollbars=yes,resizable=yes`);
            newWin.document.write('<html><head><title>SAAOB</title>');
            newWin.document.write('<style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:4px;} td:nth-child(2), th:nth-child(2) { width: 200px; } </style>');
            newWin.document.write('</head><body>');
            newWin.document.write(`
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:12px;">Republic of the Philippines</div>
                <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</div>
                <div style="font-size:12px;">La Trinidad, Benguet</div>
                <div style="font-size:12px;">Provincial Budget Office</div>
                <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES</div>
                <div style="font-size:12px;">Statement of Appropriations, Allotments, Obligations, and Balances - CY ${selectedYear}</div>
                <div style="font-size:12px;">GENERAL FUND (Current)</div>
                <div style="font-size:12px;">As of ${asOfDate}</div>
            </div>
        `);
            newWin.document.write(table.outerHTML);
            newWin.document.write(`
        <div style="margin-top: 30px; margin-left: 60%; font-size: 12px; text-align: left;">
            <strong>Certified Correct:</strong>
            <br><br><br>
            <div style="text-align: center;">
                <span style="font-weight: bold; text-decoration: underline;">
                    ${signatoryName ? signatoryName.toUpperCase() : '_____________________'}
                </span><br>
                <span>
                    ${signatoryDesignation ? signatoryDesignation : '_____________________'}
                </span>
            </div>
        </div>
    `);
            newWin.document.write('</body></html>');
            newWin.document.close();
            newWin.focus();
            newWin.print();

        }
    </script>
</x-app-layout>