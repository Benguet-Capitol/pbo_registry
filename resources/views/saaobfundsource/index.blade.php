<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">

            <div>
                @php
                $selectedOffice = null;
                if(request('office_filter')) {
                $selectedOffice = $offices->firstWhere('id', request('office_filter'));
                }
                $selectedYear = request('year1', date('Y'));
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                    {{ __('Statement of Appropriations, Allotments, Obligations and Balances') }}
                    |
                    <span class="text-blue-800 dark:text-blue-400">
                        {{ $selectedFundSource ?? 'All Fund Source' }}
                        (CY {{ $selectedYear }})
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
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-100">Success</h3>
                    <p class="mt-1 text-sm text-green-700 dark:text-green-200" id="toast-message">Excel report generated successfully</p>
                </div>
                <button onclick="closeSuccessToast()" class="ml-3 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
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
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
                Filters
            </h4>
            <!-- Shared validation message -->
            <span id="signatory_error" class="text-red-600 text-sm font-semibold mb-3 hidden block px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900 border-l-4 border-red-600 dark:border-red-400 animate-pulse transition-opacity duration-300 ease-in-out"></span>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 items-center">
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
                <!-- Fund Source Filter -->
                <div class="flex items-center space-x-2">
                    <x-form.select
                        name="fund_source_filter"
                        id="fund_source_filter"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                        onchange="this.form.submit()">
                        <option value="">All Fund Sources</option>
                        @foreach($allFundSources as $category)
                        <option value="{{ $category }}" data-fund-source-name="{{ $category }}" {{ request('fund_source_filter') == $category ? 'selected' : '' }}>
                            {{ $category }}
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
                    onclick="printSAAOBFundSourceTable()"
                    class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95"
                    type="button">
                    <i class="fas fa-print text-lg mr-2 -ml-1 w-4 h-4"></i>
                    Print Report
                </button>

                <button 
                    type="button"
                    onclick="exportSAAOBFundSourceExcel()"
                    class="text-green-700 inline-flex leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95"
                    id="excel-export-btn">
                    <i class="fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4"></i>
                    Generate Excel
                </button>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
            <div class="max-h-[720px] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <table id="SAAOBFundSourceTable" class="w-full text-[10px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-gray-100 dark:bg-gray-200 dark:text-gray-900">
                        <tr>
                            <th class="px-1 py-1 w-[200px] text-center">Funds</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation">Approved Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="sb_appropriation">Supplemental Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="reversion">Reversions</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="realignment">Realignments</th>
                            <th class="px-1 py-1 w-[100px] text-center">Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center">Allotments</th>
                            <th class="px-1 py-1 w-[100px] text-center">For Later Release</th>
                            <th class="px-1 py-1 w-[100px] text-center">Obligations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation_balance">Authorized Appropriation Balance</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="appropriation_accomplishment">% of Utilization</th>
                            <th class="px-1 py-1 w-[100px] text-center">Allotment Balance</th>
                            <th class="px-1 py-1 w-[70px] text-center">% of Utilization</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fundSources as $fundSource)
                        <tr id="fundSourceRow" class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-center text-sm">
                            <td colspan="13" class="px-2 py-3">{{ $fundSource['category'] }}</td>
                        </tr>
                        @foreach($fundSource['fund_types'] as $fundType)
                        @foreach($fundType['funds'] as $fund)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="px-2 py-2 text-left">{{ $fund['fund'] }}</td>
                            <td class="px-2 py-2 text-right" data-key="appropriation">
                                {{ $fund['approved_appropriation'] > 0 
                                    ? number_format($fund['approved_appropriation'], 2) 
                                    : ($fund['approved_appropriation'] < 0 
                                        ? '(' . number_format(abs($fund['approved_appropriation']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                {{ $fund['sb_appropriation'] > 0 
                                    ? number_format($fund['sb_appropriation'], 2) 
                                    : ($fund['sb_appropriation'] < 0 
                                        ? '(' . number_format(abs($fund['sb_appropriation']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="reversion">
                                {{ $fund['reversion'] > 0 
                                    ? number_format($fund['reversion'], 2) 
                                    : ($fund['reversion'] < 0 
                                        ? '(' . number_format(abs($fund['reversion']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="realignment">
                                {{ $fund['realignment'] > 0 
                                    ? number_format($fund['realignment'], 2) 
                                    : ($fund['realignment'] < 0 
                                        ? '(' . number_format(abs($fund['realignment']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fund['authorized'] > 0 
                                    ? number_format($fund['authorized'], 2) 
                                    : ($fund['authorized'] < 0 
                                        ? '(' . number_format(abs($fund['authorized']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fund['allotment'] > 0 
                                    ? number_format($fund['allotment'], 2) 
                                    : ($fund['allotment'] < 0 
                                        ? '(' . number_format(abs($fund['allotment']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fund['for_later_release'] > 0 
                                    ? number_format($fund['for_later_release'], 2) 
                                    : ($fund['for_later_release'] < 0 
                                        ? '(' . number_format(abs($fund['for_later_release']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fund['obligation'] > 0 
                                    ? number_format($fund['obligation'], 2) 
                                    : ($fund['obligation'] < 0 
                                        ? '(' . number_format(abs($fund['obligation']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                {{ $fund['appropriation_balance'] > 0 
                                    ? number_format($fund['appropriation_balance'], 2) 
                                    : ($fund['appropriation_balance'] < 0 
                                        ? '(' . number_format(abs($fund['appropriation_balance']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fund['appropriation_accomplishment'], 2) }}%
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fund['allotment_balance'] > 0 
                                    ? number_format($fund['allotment_balance'], 2) 
                                    : ($fund['allotment_balance'] < 0 
                                        ? '(' . number_format(abs($fund['allotment_balance']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-center">
                                {{ number_format($fund['allotment_accomplishment'], 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-300 text-gray-700 dark:bg-gray-900 dark:text-white font-bold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 text-[10px]">
                            <td class="px-2 py-2 text-right">{{ $fundType['totals']['fund'] }}:</td>
                            <td class="px-2 py-2 text-right" data-key="appropriation">
                                {{ $fundType['totals']['approved_appropriation'] > 0 
                                    ? number_format($fundType['totals']['approved_appropriation'], 2) 
                                    : ($fundType['totals']['approved_appropriation'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['approved_appropriation']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                {{ $fundType['totals']['sb_appropriation'] > 0 
                                    ? number_format($fundType['totals']['sb_appropriation'], 2) 
                                    : ($fundType['totals']['sb_appropriation'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['sb_appropriation']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="reversion">
                                {{ $fundType['totals']['reversion'] > 0 
                                    ? number_format($fundType['totals']['reversion'], 2) 
                                    : ($fundType['totals']['reversion'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['reversion']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="realignment">
                                {{ $fundType['totals']['realignment'] > 0 
                                    ? number_format($fundType['totals']['realignment'], 2) 
                                    : ($fundType['totals']['realignment'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['realignment']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fundType['totals']['authorized'] > 0 
                                    ? number_format($fundType['totals']['authorized'], 2) 
                                    : ($fundType['totals']['authorized'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['authorized']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fundType['totals']['allotment'] > 0 
                                    ? number_format($fundType['totals']['allotment'], 2) 
                                    : ($fundType['totals']['allotment'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['allotment']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fundType['totals']['for_later_release'] > 0 
                                    ? number_format($fundType['totals']['for_later_release'], 2) 
                                    : ($fundType['totals']['for_later_release'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['for_later_release']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fundType['totals']['obligation'] > 0 
                                    ? number_format($fundType['totals']['obligation'], 2) 
                                    : ($fundType['totals']['obligation'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['obligation']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                {{ $fundType['totals']['appropriation_balance'] > 0 
                                    ? number_format($fundType['totals']['appropriation_balance'], 2) 
                                    : ($fundType['totals']['appropriation_balance'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['appropriation_balance']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fundType['totals']['appropriation_accomplishment'], 2) }}%
                            </td>

                            <td class="px-2 py-2 text-right">
                                {{ $fundType['totals']['allotment_balance'] > 0 
                                    ? number_format($fundType['totals']['allotment_balance'], 2) 
                                    : ($fundType['totals']['allotment_balance'] < 0 
                                        ? '(' . number_format(abs($fundType['totals']['allotment_balance']), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-2 py-2 text-center">
                                {{ number_format($fundType['totals']['allotment_accomplishment'], 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-500 text-white dark:bg-gray-700 dark:text-gray-100 font-bold border-t-4 border-gray-800 text-[10px]">
                            <td class="px-2 py-2 text-right">Grand Total ({{ $fundSource['category'] }}):</td>
                            <td class="px-2 py-2 text-right" data-key="appropriation">
                                {{ $fundSource['grand_totals']['approved_appropriation'] > 0 
                                    ? number_format($fundSource['grand_totals']['approved_appropriation'], 2) 
                                    : ($fundSource['grand_totals']['approved_appropriation'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['approved_appropriation']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                {{ $fundSource['grand_totals']['sb_appropriation'] > 0 
                                    ? number_format($fundSource['grand_totals']['sb_appropriation'], 2) 
                                    : ($fundSource['grand_totals']['sb_appropriation'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['sb_appropriation']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right" data-key="reversion">
                                {{ $fundSource['grand_totals']['reversion'] > 0 
                                    ? number_format($fundSource['grand_totals']['reversion'], 2) 
                                    : ($fundSource['grand_totals']['reversion'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['reversion']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right" data-key="realignment">
                                {{ $fundSource['grand_totals']['realignment'] > 0 
                                    ? number_format($fundSource['grand_totals']['realignment'], 2) 
                                    : ($fundSource['grand_totals']['realignment'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['realignment']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right">
                                {{ $fundSource['grand_totals']['authorized'] > 0 
                                    ? number_format($fundSource['grand_totals']['authorized'], 2) 
                                    : ($fundSource['grand_totals']['authorized'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['authorized']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right">
                                {{ $fundSource['grand_totals']['allotment'] > 0 
                                    ? number_format($fundSource['grand_totals']['allotment'], 2) 
                                    : ($fundSource['grand_totals']['allotment'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['allotment']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right">
                                {{ $fundSource['grand_totals']['for_later_release'] > 0 
                                    ? number_format($fundSource['grand_totals']['for_later_release'], 2) 
                                    : ($fundSource['grand_totals']['for_later_release'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['for_later_release']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right">
                                {{ $fundSource['grand_totals']['obligation'] > 0 
                                    ? number_format($fundSource['grand_totals']['obligation'], 2) 
                                    : ($fundSource['grand_totals']['obligation'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['obligation']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                {{ $fundSource['grand_totals']['appropriation_balance'] > 0 
                                    ? number_format($fundSource['grand_totals']['appropriation_balance'], 2) 
                                    : ($fundSource['grand_totals']['appropriation_balance'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['appropriation_balance']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fundSource['grand_totals']['appropriation_accomplishment'], 2) }}%
                            </td>
                            <td class="px-2 py-2 text-right">
                                {{ $fundSource['grand_totals']['allotment_balance'] > 0 
                                    ? number_format($fundSource['grand_totals']['allotment_balance'], 2) 
                                    : ($fundSource['grand_totals']['allotment_balance'] < 0 
                                        ? '(' . number_format(abs($fundSource['grand_totals']['allotment_balance']), 2) . ')' 
                                        : '-') }}
                            </td>
                            <td class="px-2 py-2 text-center">
                                {{ number_format($fundSource['grand_totals']['allotment_accomplishment'], 2) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </form>

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
                    return false;
                } else {
                    errorSpan.classList.add('hidden');
                    return true;
                }
            }

            // Intercept PDF generation
            window.printSAAOBFundSourceTable = function() {
                if (!validateSignatories()) return;
                runPrintSAAOBFundSourceTable(); // call actual print function
            };

            // Intercept Excel Export Submit
            document.querySelector(`form[action="{{ route('saaobFundSource.exportExcel') }}"]`)
                .addEventListener('submit', function(e) {
                    if (!validateSignatories()) {
                        e.preventDefault();
                    }
                });

            function runPrintSAAOBFundSourceTable() {
                const table = document.getElementById('SAAOBFundSourceTable').cloneNode(true);

                const hiddenKeys = [
                    'appropriation',
                    'sb_appropriation',
                    'reversion',
                    'realignment'
                ];

                // Remove only <td> and <th> elements matching these keys
                table.querySelectorAll('thead th[data-key], tbody td[data-key]').forEach(cell => {
                    const key = cell.getAttribute('data-key');
                    if (hiddenKeys.includes(key)) {
                        cell.remove();
                    }
                });

                // Style allotment class rows
                table.querySelectorAll('[id^="CCOYear"]').forEach(tr => {
                    tr.style.textTransform = 'uppercase';
                    tr.style.fontWeight = 'bold';
                    tr.style.fontSize = '10px';
                    tr.style.paddingLeft = '12px'; // Indent the row
                    // Also indent the first cell if needed
                    const firstCell = tr.querySelector('td');
                    if (firstCell) {
                        firstCell.style.paddingLeft = '12px';
                    }
                });
                // Style program rows
                table.querySelectorAll('[id^="programRow-"]').forEach(tr => {
                    tr.style.textTransform = 'uppercase';
                    tr.style.fontWeight = 'bold';
                    tr.style.fontStyle = 'italic';
                    tr.style.fontSize = '10px';
                    tr.style.paddingLeft = '32px'; // Indent the row
                    // Also indent the first cell if needed
                    const firstCell = tr.querySelector('td');
                    if (firstCell) {
                        firstCell.style.paddingLeft = '32px';
                    }
                });
                // Set tbody font size to 10px
                Array.from(table.querySelectorAll('tbody')).forEach(tbody => {
                    tbody.style.fontSize = '10px';
                });

                // Style fund source rows first
                table.querySelectorAll('[id^="fundSourceRow"]').forEach(tr => {
                    tr.style.textTransform = 'uppercase';
                    tr.style.fontWeight = 'bold';
                    tr.style.fontSize = '10px';

                    // Center-align all cells in this row
                    tr.querySelectorAll('td, th').forEach(cell => {
                        cell.style.textAlign = 'center';
                    });
                });

                // Style all other rows (excluding fund source rows)
                table.querySelectorAll('tbody tr:not([id^="fundSourceRow"])').forEach(tr => {
                    const cells = tr.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        if (index === 0) {
                            cell.style.textAlign = 'left';
                        } else {
                            cell.style.textAlign = 'right';
                        }
                    });
                });


                // Make subtotal, total, and grand total rows bold and 1st/2nd/3rd column right-aligned, values uppercase
                table.querySelectorAll('tr').forEach(tr => {
                    const cells = tr.querySelectorAll('td');
                    if (cells.length > 2) {
                        const text = cells[0].textContent.trim().toUpperCase();
                        if (
                            text.startsWith('SUBTOTAL') ||
                            text.startsWith('TOTAL') ||
                            text.startsWith('GRAND TOTAL')
                        ) {
                            tr.style.fontWeight = 'bold';
                            cells[0].style.textAlign = 'right';
                            cells[1].style.textAlign = 'right';
                            cells[2].style.textAlign = 'right';
                        }
                    }
                });

                // Center align thead text
                Array.from(table.querySelectorAll('thead th')).forEach(th => {
                    th.style.textAlign = 'center';
                    th.style.fontWeight = 'bold';
                    th.style.fontSize = '10px';
                });
                // Get selected office name (full name)
                const fundSourceSelect = document.getElementById('fund_source_filter');
                let fundSourceText = 'Current and Continuing';
                if (fundSourceSelect && fundSourceSelect.selectedIndex > 0) {
                    const selectedOption = fundSourceSelect.options[fundSourceSelect.selectedIndex];
                    fundSourceText = (selectedOption.getAttribute('data-fund-source-name') || selectedOption.text);
                }
                // Get as of date and format it
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

                // Get signatory name and designation
                const signatoryNameInput = document.getElementById('signatory_name');
                let signatoryName = '';
                if (signatoryNameInput && signatoryNameInput.value) {
                    signatoryName = signatoryNameInput.value;
                }
                const signatoryDesignationInput = document.getElementById('signatory_designation');
                let signatoryDesignation = '';
                if (signatoryDesignationInput && signatoryDesignationInput.value) {
                    signatoryDesignation = signatoryDesignationInput.value;
                }

                const yearSelect = document.getElementById('year_filter');
                const selectedYear = yearSelect?.value || new Date().getFullYear();

                // Get screen dimensions
                const screenW = window.screen.availWidth;
                const screenH = window.screen.availHeight;

                const newWin = window.open('', '', `width=${screenW},height=${screenH},left=0,top=0,scrollbars=yes,resizable=yes`);
                newWin.document.write('<html><head><title>SAAOB</title>');
                newWin.document.write('<style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:4px;} </style>');
                newWin.document.write('</head><body>');
                newWin.document.write(`
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:12px;">Republic of the Philippines</div>
                <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</div>
                <div style="font-size:12px;">La Trinidad, Benguet</div>
                <div style="font-size:12px;">Provincial Budget Office</div>
                <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">
                    STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES
                </div>
                <div style="font-size:12px;">Summary of Appropriations, Allotments, Obligations and Balances - CY ${selectedYear}</div>
                <div style="font-size:12px;">${fundSourceText}</div>
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

            // Toast notification functions
            function showSuccessToast() {
                const toast = document.getElementById('success-toast');
                toast.classList.remove('hide');
                toast.classList.add('show');
                
                setTimeout(() => {
                    closeSuccessToast();
                }, 4000);
            }

            function closeSuccessToast() {
                const toast = document.getElementById('success-toast');
                toast.classList.remove('show');
                toast.classList.add('hide');
            }

            function showLoadingToast() {
                const toast = document.getElementById('loading-toast');
                toast.classList.remove('hide');
                toast.classList.add('show');
            }

            function closeLoadingToast() {
                const toast = document.getElementById('loading-toast');
                toast.classList.remove('show');
                toast.classList.add('hide');
            }

            // Export Excel via Fetch API
            async function exportSAAOBFundSourceExcel() {
                if (!validateSignatories()) return;

                showLoadingToast();

                try {
                    const params = new URLSearchParams({
                        'year1': document.getElementById('year1').value,
                        'fund_source_filter': document.getElementById('fund_source_filter').value,
                        'as_of_filter': document.getElementById('as_of_filter').value,
                        'signatory_name': document.getElementById('signatory_name').value,
                        'signatory_designation': document.getElementById('signatory_designation').value
                    });

                    const response = await fetch(`{{ route('saaobFundSource.exportExcel') }}?${params}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Export failed');
                    }

                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    const year = document.querySelector('select[name="year1"]')?.value || new Date().getFullYear();
                    const fundSourceFilter = document.querySelector('select[name="fund_source_filter"]')?.value || '';
                    
                    let filename = 'SAAOB_';
                    if (fundSourceFilter) {
                        filename += `${fundSourceFilter}_${year}.xlsx`;
                    } else {
                        filename += `All_Fund_Sources_${year}.xlsx`;
                    }
                    
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    setTimeout(() => {
                        closeLoadingToast();
                        showSuccessToast();
                    }, 1000);
                } catch (error) {
                    closeLoadingToast();
                    console.error('Export error:', error);
                    alert('Failed to generate Excel file. Please try again.');
                }
            }
        </script>

        <style>
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
            tr[id^="officeNameRow"] {
                animation: fadeIn 0.4s ease-in-out;
            }

            tr[id^="allotmentClassRow"] {
                animation: fadeIn 0.4s ease-in-out 0.1s both;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            /* Button press animation */
            button {
                transition: all 0.2s ease-in-out;
            }

            button:active {
                transition: all 0.1s ease-in-out;
            }

            /* Smooth color transition for hover states */
            tr[id^="allotmentClassRow"]:hover {
                transition: background 0.3s ease-in-out;
            }

            /* Table cell transition */
            td, th {
                transition: background-color 0.2s ease-in-out;
            }

            /* Error message pulse animation */
            #signatory_error {
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.5;
                }
            }

            /* Form transition on focus */
            #filterForm {
                animation: slideUp 0.3s ease-in-out;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Table row fade animation */
            @keyframes tableRowFade {
                from {
                    opacity: 0;
                    transform: translateY(-5px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
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
</x-app-layout>
