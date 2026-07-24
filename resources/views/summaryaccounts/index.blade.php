<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                @php
                $selectedYear = request('year1', date('Y'));
                $selectedFundKey = request('fund_filter', 'all');
                $selectedFundLabel = $availableFunds[$selectedFundKey] ?? 'ALL FUNDS';
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Summary of Appropriations, Allotments, Obligations and Balances') }}
                |
                <span class="text-blue-800 dark:text-blue-400">
                   {{ $selectedFundLabel }} (CY {{ $selectedYear }})
                </span>
            </h3>
            </div>
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
            <div class="flex justify-between items-center mb-4">
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
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
                <!-- Fund Filter -->
                <div class="flex flex-col space-y-1">
                    <label for="fund_filter" class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fund</label>
                    <x-form.select
                        name="fund_filter"
                        id="fund_filter"
                        aria-label="Fund"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                        onchange="this.form.submit()">
                        @foreach($availableFunds as $fundKey => $fundLabel)
                        <option value="{{ $fundKey }}" {{ request('fund_filter', 'all') == $fundKey ? 'selected' : '' }}>
                            {{ $fundLabel }}
                        </option>
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
                if(request('fund_filter') && request('fund_filter') !== 'all') $activeChips[] = ['label' => 'Fund', 'value' => $selectedFundLabel, 'param' => 'fund_filter'];
                if(request('signatory_name')) $activeChips[] = ['label' => 'Signatory', 'value' => request('signatory_name'), 'param' => 'signatory_name'];
                if(request('signatory_designation')) $activeChips[] = ['label' => 'Designation', 'value' => request('signatory_designation'), 'param' => 'signatory_designation'];
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
                    type="button"
                    onclick="exportSummaryAccountsExcel()"
                    class="text-green-700 inline-flex leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                    id="excel-export-btn">
                    <i class="fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4" id="excel-btn-icon"></i>
                    <span id="excel-btn-label">Generate Excel</span>
                </button>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md relative dark:bg-gray-800 transition-colors duration-300 ease-in-out">
            @if($allotmentClassTotals->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-3"></i>
                <p class="text-sm font-medium">No data available for the selected filters</p>
                <button type="button" onclick="clearAllFilters()" class="mt-3 text-xs text-blue-600 hover:underline dark:text-blue-400">Clear filters and try again</button>
            </div>
            @else
            <div class="overflow-x-auto overflow-y-auto max-h-[700px] border border-gray-300 dark:border-gray-600 rounded-md">
            <table id="dashboardTable" class="min-w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                <thead class="sticky top-0 z-10 border border-blue-400 bg-gradient-to-r from-blue-200 to-blue-300 text-blue-900 dark:bg-gradient-to-r dark:from-blue-700 dark:to-blue-600 dark:text-blue-200 transition-colors duration-300 ease-in-out">
                    <tr>
                        <th rowspan="2" class="px-1 py-1 w-[150px] text-center align-middle border-r border-blue-400 dark:border-blue-300">Functions / Programs / Projects / Activities</th>
                        <th rowspan="2" class="px-1 py-1 w-[140px] text-center align-middle border-r border-blue-400 dark:border-blue-300">Account Code</th>
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
                        <th class="px-1 py-1 w-[70px] text-center">% Used</th>
                    </tr>
                </thead>
                <tbody class="border border-gray-300 dark:border-gray-600 text-[10px]">
                    @foreach ($allotmentClassTotals as $className => $items)
                        {{-- Allotment Class Header --}}
                        <tr class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900 text-blue-900 dark:text-blue-200 uppercase font-bold border-t border-b border-blue-300 dark:border-blue-800 transition-colors duration-300 ease-in-out">
                            <td colspan="14" class="px-2 py-2 text-xs">{{ $className }}</td>
                        </tr>

                        {{-- Individual Accounts with Appropriations --}}
                        @if(isset($items['accounts']) && !empty($items['accounts']))
                            @php $rowIndex = 0; @endphp
                            @foreach ($items['accounts'] as $app)
                            @php $rowIndex++; @endphp
                            <tr class="{{ $rowIndex % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800/60' : 'bg-white dark:bg-gray-800' }} border-b border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                                <td class="px-2 py-2 font-semibold text-left">{{ $app['description'] }}</td>
                                <td class="px-2 py-2 font-semibold text-center">{{ $app['account_code'] }}</td>
                                <td class="px-2 py-2 font-semibold text-right" data-key="appropriation">
                                    @if ($app['appropriation'] == 0 || is_null($app['appropriation']))
                                        -
                                    @elseif ($app['appropriation'] < 0)
                                        ({{ number_format(abs($app['appropriation']), 2) }})
                                    @else
                                        {{ number_format($app['appropriation'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                    @if ($app['sb_appropriation'] == 0 || is_null($app['sb_appropriation']))
                                        -
                                    @elseif ($app['sb_appropriation'] < 0)
                                        ({{ number_format(abs($app['sb_appropriation']), 2) }})
                                    @else
                                        {{ number_format($app['sb_appropriation'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-right" data-key="reversion">
                                    @if ($app['reversion'] == 0 || is_null($app['reversion']))
                                        -
                                    @elseif ($app['reversion'] < 0)
                                        ({{ number_format(abs($app['reversion']), 2) }})
                                    @else
                                        {{ number_format($app['reversion'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-right" data-key="realignment">
                                    @if ($app['realignment'] == 0 || is_null($app['realignment']))
                                        -
                                    @elseif ($app['realignment'] < 0)
                                        ({{ number_format(abs($app['realignment']), 2) }})
                                    @else
                                        {{ number_format($app['realignment'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 font-semibold text-right">
                                    @if ($app['authorized_appropriation'] == 0 || is_null($app['authorized_appropriation']))
                                        -
                                    @elseif ($app['authorized_appropriation'] < 0)
                                        ({{ number_format(abs($app['authorized_appropriation']), 2) }})
                                    @else
                                        {{ number_format($app['authorized_appropriation'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 font-semibold text-right">
                                    @if ($app['allotment'] == 0 || is_null($app['allotment']))
                                        -
                                    @elseif ($app['allotment'] < 0)
                                        ({{ number_format(abs($app['allotment']), 2) }})
                                    @else
                                        {{ number_format($app['allotment'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-right">
                                    @if ($app['for_later_release'] == 0 || is_null($app['for_later_release']))
                                        -
                                    @elseif ($app['for_later_release'] < 0)
                                        ({{ number_format(abs($app['for_later_release']), 2) }})
                                    @else
                                        {{ number_format($app['for_later_release'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 font-semibold text-right">
                                    @if ($app['obligation'] == 0 || is_null($app['obligation']))
                                        -
                                    @elseif ($app['obligation'] < 0)
                                        ({{ number_format(abs($app['obligation']), 2) }})
                                    @else
                                        {{ number_format($app['obligation'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                    @if ($app['appropriation_balance'] == 0 || is_null($app['appropriation_balance']))
                                        -
                                    @elseif ($app['appropriation_balance'] < 0)
                                        ({{ number_format(abs($app['appropriation_balance']), 2) }})
                                    @else
                                        {{ number_format($app['appropriation_balance'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                    @include('saaob._utilization-badge', ['pct' => $app['appropriation_accomplishment'] ?? 0])
                                </td>
                                <td class="px-2 py-2 text-right">
                                    @if ($app['allotment_balance'] == 0 || is_null($app['allotment_balance']))
                                        -
                                    @elseif ($app['allotment_balance'] < 0)
                                        ({{ number_format(abs($app['allotment_balance']), 2) }})
                                    @else
                                        {{ number_format($app['allotment_balance'], 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @include('saaob._utilization-badge', ['pct' => $app['allotment_accomplishment'] ?? 0])
                                </td>
                            </tr>
                        @endforeach
                        @endif

                        {{-- Total Per Allotment Class --}}
                        @if(isset($items['subtotals']))
                        <tr class="bg-gradient-to-r from-gray-300 to-gray-400 text-gray-900 dark:from-gray-700 dark:to-gray-800 dark:text-gray-100 font-bold border-t-2 border-b-2 border-gray-500 dark:border-gray-600 text-[10px] transition-colors duration-300 ease-in-out">
                            <td colspan="2" class="px-2 py-2 text-right">Total {{ $className }}:</td>
                            <td class="px-2 py-2 text-right" data-key="appropriation">{{ number_format($items['subtotals']['appropriation'], 2) }}</td>
                            <td class="px-2 py-2 text-right" data-key="sb_appropriation">{{ number_format($items['subtotals']['sb_appropriation'], 2) }}</td>
                            <td class="px-2 py-2 text-right" data-key="reversion">{{ number_format($items['subtotals']['reversion'], 2) }}</td>
                            <td class="px-2 py-2 text-right" data-key="realignment">{{ number_format($items['subtotals']['realignment'], 2) }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format($items['subtotals']['authorized_appropriation'], 2) }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format($items['subtotals']['allotment'], 2) }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format($items['subtotals']['for_later_release'], 2) }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format($items['subtotals']['obligation'], 2) }}</td>
                            <td class="px-2 py-2 text-right" data-key="appropriation_balance">{{ number_format($items['subtotals']['appropriation_balance'], 2) }}</td>
                            <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                @include('saaob._utilization-badge', ['pct' => $items['subtotals']['utilization_percent'] ?? 0])
                            </td>
                            <td class="px-2 py-2 text-right">{{ number_format($items['subtotals']['allotment_balance'], 2) }}</td>
                            <td class="px-2 py-2 text-center">
                                @include('saaob._utilization-badge', ['pct' => $items['subtotals']['allotment_utilization_percent'] ?? 0])
                            </td>
                        </tr>
                        @endif
                    @endforeach

                    {{-- Grand Total Row --}}
                    @php
                        $grandTotal = [
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

                        foreach ($allotmentClassTotals as $class => $items) {
                            if (isset($items['subtotals'])) {
                                foreach ($grandTotal as $key => $val) {
                                    $grandTotal[$key] += $items['subtotals'][$key] ?? 0;
                                }
                            }
                        }

                        $grandUtilization = $grandTotal['authorized_appropriation'] > 0
                            ? ($grandTotal['obligation'] / $grandTotal['authorized_appropriation']) * 100
                            : 0;

                        $grandAllotmentUtilization = $grandTotal['allotment'] > 0
                            ? ($grandTotal['obligation'] / $grandTotal['allotment']) * 100
                            : 0;
                    @endphp
                    <tr class="bg-blue-200 dark:bg-blue-950 text-blue-900 dark:text-blue-100 font-bold border-t-4 border-blue-500 dark:border-blue-800 border-b-2 text-[10px]">
                        <td colspan="2" class="px-2 py-3 text-right">GRAND TOTAL:</td>
                        <td class="px-1 py-3 text-right" data-key="appropriation">{{ number_format($grandTotal['appropriation'], 2) }}</td>
                        <td class="px-1 py-3 text-right" data-key="sb_appropriation">{{ number_format($grandTotal['sb_appropriation'], 2) }}</td>
                        <td class="px-1 py-3 text-right" data-key="reversion">{{ number_format($grandTotal['reversion'], 2) }}</td>
                        <td class="px-1 py-3 text-right" data-key="realignment">{{ number_format($grandTotal['realignment'], 2) }}</td>
                        <td class="px-1 py-3 text-right">{{ number_format($grandTotal['authorized_appropriation'], 2) }}</td>
                        <td class="px-1 py-3 text-right">{{ number_format($grandTotal['allotment'], 2) }}</td>
                        <td class="px-1 py-3 text-right">{{ number_format($grandTotal['for_later_release'], 2) }}</td>
                        <td class="px-1 py-3 text-right">{{ number_format($grandTotal['obligation'], 2) }}</td>
                        <td class="px-1 py-3 text-right" data-key="appropriation_balance">{{ number_format($grandTotal['appropriation_balance'], 2) }}</td>
                        <td class="px-1 py-3 text-center" data-key="appropriation_accomplishment">
                            @include('saaob._utilization-badge', ['pct' => $grandUtilization])
                        </td>
                        <td class="px-1 py-3 text-right">{{ number_format($grandTotal['allotment_balance'], 2) }}</td>
                        <td class="px-1 py-3 text-center">
                            @include('saaob._utilization-badge', ['pct' => $grandAllotmentUtilization])
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        </div>
    </div>
</form>

<style>
    .filter-select {
        transition: all 0.2s ease-in-out;
    }

    .filter-select:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 0 0 2px rgba(59, 130, 246, 0.5);
    }

    tbody tr {
        transition: all 0.2s ease-in-out;
    }

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

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

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

    .bg-white.overflow-hidden.shadow-md {
        animation: slideUp 0.4s ease-in-out 0.1s both;
    }

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

    .dark .overflow-y-auto::-webkit-scrollbar-track {
        background: #1f2937;
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #4b5563;
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #6b7280;
    }

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

    .filter-select:focus-visible {
        outline: 2px solid transparent;
        outline-offset: 2px;
    }

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
    async function exportSummaryAccountsExcel() {
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
                fund_filter: document.getElementById('fund_filter').value,
                as_of_filter: document.getElementById('as_of_filter').value,
                signatory_name: document.getElementById('signatory_name').value,
                signatory_designation: document.getElementById('signatory_designation').value
            });

            const response = await fetch(`{{ route('summaryaccounts.exportExcel') }}?${params}`, {
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

            // Get year and fund for filename
            const year = document.getElementById('year1').value;
            const fundFilter = document.getElementById('fund_filter').value;
            const fundSelectElement = document.getElementById('fund_filter');
            const fundLabel = fundSelectElement.options[fundSelectElement.selectedIndex].text;

            let filename = 'SAAOB_Summary_';
            if (fundFilter === 'all' || !fundFilter) {
                filename += year + '.xlsx';
            } else if (fundLabel.includes('General Fund')) {
                filename = 'SAAOB_GF_Summary_' + year + '.xlsx';
            } else if (fundLabel.includes('Benguet General Hospital')) {
                filename = 'SAAOB_BEGH_Summary_' + year + '.xlsx';
            } else if (fundLabel.includes('Special Education Fund')) {
                filename = 'SAAOB_SEF_Summary_' + year + '.xlsx';
            } else {
                filename += year + '.xlsx';
            }

            a.download = filename;
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
</script>

</x-app-layout>