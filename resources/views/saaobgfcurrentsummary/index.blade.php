<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Statement of Appropriations, Allotments, Obligations and Balances') }} |
                <span class="text-blue-800 dark:text-blue-400">
                    General Fund - Current (CY {{ $selectedYear }}) Summary
                </span>
            </h3>
        </div>
    </x-slot>

    <!-- Unified Filter Section -->
    <form method="GET" action="" class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800" id="filterForm">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>
        <!-- Shared validation message -->
        <span id="signatory_error" class="text-red-500 text-xs mb-2 hidden"></span>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 items-center">
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
            <div class="flex space-x-2 flex-col items-start">
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
            <div class="flex space-x-2 flex-col items-start">
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
            <div class="flex items-center space-x-2">
                <button
                    onclick="printSAAOBGFCurrentSummaryTable()"
                    class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900"
                    type="button">
                    Print Report
                </button>
    </form>
    <form method="GET" action="{{ route('saaobGFCurrentSummary.exportExcel') }}" style="display:inline;">
        <input type="hidden" name="year1" value="{{ request('year1') }}">
        <input type="hidden" name="as_of_filter" value="{{ request('as_of_filter') }}">
        <input type="hidden" name="signatory_name" value="{{ request('signatory_name') }}">
        <input type="hidden" name="signatory_designation" value="{{ request('signatory_designation') }}">

        <button type="submit" class="text-green-700 border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
            Generate Excel
        </button>
    </form>
    </div>
    </div>

    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mt-6 mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
            <div class="max-h-[720px] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <table id="saaobGFCurrentSummaryTable" class="w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-white dark:bg-gray-200 dark:text-gray-900">
                        <tr>
                            <th class="px-1 py-1 w-[180px] text-center">Class</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation">Approved Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="sb_appropriation">Supplemental Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="reversion">Reversions</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="realignment">Realignments</th>
                            <th class="px-1 py-1 w-[100px] text-center">Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center">Allotments</th>
                            <th class="px-1 py-1 w-[100px] text-center">For Later Release</th>
                            <th class="px-1 py-1 w-[100px] text-center">Obligations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation_balance">Balance from Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="appropriation_accomplishment">Percent of Utilization</th>
                            <th class="px-1 py-1 w-[100px] text-center">Balance from Allotments</th>
                            <th class="px-1 py-1 w-[70px] text-center">Percent of Utilization</th>
                        </tr>
                    </thead>
                    <tbody class="border border-gray-300 dark:border-gray-600 text-[10px]">
                        @foreach ($funds as $fund)
                        <tr id="fundRow" class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-center text-sm">
                            <td colspan="14" class="px-2 py-3">{{ $fund->fund }}</td>
                        </tr>
                        @foreach ($fund->presentAllotmentClasses as $allotmentClass)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="px-1 py-2 text-left">{{ $allotmentClass->description }}</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $allotmentClass->approved_appropriations > 0 
                                    ? number_format($allotmentClass->approved_appropriations, 2) 
                                    : ($allotmentClass->approved_appropriations < 0 
                                        ? '(' . number_format(abs($allotmentClass->approved_appropriations), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $allotmentClass->sb_appropriation > 0 
                                    ? number_format($allotmentClass->sb_appropriation, 2) 
                                    : ($allotmentClass->sb_appropriation < 0 
                                        ? '(' . number_format(abs($allotmentClass->sb_appropriation), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $allotmentClass->reversion > 0 
                                    ? number_format($allotmentClass->reversion, 2) 
                                    : ($allotmentClass->reversion < 0 
                                        ? '(' . number_format(abs($allotmentClass->reversion), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $allotmentClass->realignment > 0 
                                    ? number_format($allotmentClass->realignment, 2) 
                                    : ($allotmentClass->realignment < 0 
                                        ? '(' . number_format(abs($allotmentClass->realignment), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->authorized_appropriation > 0 
                                    ? number_format($allotmentClass->authorized_appropriation, 2) 
                                    : ($allotmentClass->authorized_appropriation < 0 
                                        ? '(' . number_format(abs($allotmentClass->authorized_appropriation), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->allotment > 0 
                                    ? number_format($allotmentClass->allotment, 2) 
                                    : ($allotmentClass->allotment < 0 
                                        ? '(' . number_format(abs($allotmentClass->allotment), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->for_later_release > 0 
                                    ? number_format($allotmentClass->for_later_release, 2) 
                                    : ($allotmentClass->for_later_release < 0 
                                        ? '(' . number_format(abs($allotmentClass->for_later_release), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->obligation > 0 
                                    ? number_format($allotmentClass->obligation, 2) 
                                    : ($allotmentClass->obligation < 0 
                                        ? '(' . number_format(abs($allotmentClass->obligation), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->appropriation_balance > 0 
                                    ? number_format($allotmentClass->appropriation_balance, 2) 
                                    : ($allotmentClass->appropriation_balance < 0 
                                        ? '(' . number_format(abs($allotmentClass->appropriation_balance), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($allotmentClass->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->allotment_balance > 0 
                                    ? number_format($allotmentClass->allotment_balance, 2) 
                                    : ($allotmentClass->allotment_balance < 0 
                                        ? '(' . number_format(abs($allotmentClass->allotment_balance), 2) . ')' 
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($allotmentClass->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-500 text-white dark:bg-gray-600 dark:text-white font-semibold border-t-2 border-b-2 border-gray-700 dark:border-gray-100">
                            <td class="px-1 py-2 text-right">Total {{ $fund->fund }}:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $fund->total->approved_appropriations > 0
                                    ? number_format($fund->total->approved_appropriations, 2)
                                    : ($fund->total->approved_appropriations < 0
                                        ? '(' . number_format(abs($fund->total->approved_appropriations), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $fund->total->sb_appropriation > 0
                                    ? number_format($fund->total->sb_appropriation, 2)
                                    : ($fund->total->sb_appropriation < 0
                                        ? '(' . number_format(abs($fund->total->sb_appropriation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $fund->total->reversion > 0
                                    ? number_format($fund->total->reversion, 2)
                                    : ($fund->total->reversion < 0
                                        ? '(' . number_format(abs($fund->total->reversion), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $fund->total->realignment > 0
                                    ? number_format($fund->total->realignment, 2)
                                    : ($fund->total->realignment < 0
                                        ? '(' . number_format(abs($fund->total->realignment), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->total->authorized_appropriation > 0
                                    ? number_format($fund->total->authorized_appropriation, 2)
                                    : ($fund->total->authorized_appropriation < 0
                                        ? '(' . number_format(abs($fund->total->authorized_appropriation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->total->for_later_release > 0
                                    ? number_format($fund->total->for_later_release, 2)
                                    : ($fund->total->for_later_release < 0
                                        ? '(' . number_format(abs($fund->total->for_later_release), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->total->allotment > 0
                                    ? number_format($fund->total->allotment, 2)
                                    : ($fund->total->allotment < 0
                                        ? '(' . number_format(abs($fund->total->allotment), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->total->obligation > 0
                                    ? number_format($fund->total->obligation, 2)
                                    : ($fund->total->obligation < 0
                                        ? '(' . number_format(abs($fund->total->obligation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->total->appropriation_balance > 0
                                    ? number_format($fund->total->appropriation_balance, 2)
                                    : ($fund->total->appropriation_balance < 0
                                        ? '(' . number_format(abs($fund->total->appropriation_balance), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($fund->total->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->total->allotment_balance > 0
                                    ? number_format($fund->total->allotment_balance, 2)
                                    : ($fund->total->allotment_balance < 0
                                        ? '(' . number_format(abs($fund->total->allotment_balance), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($fund->total->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-700 text-white dark:bg-gray-700 font-bold text-right uppercase border-t-2 border-b-2 border-white dark:border-gray-100">
                            <td class="px-1 py-3 text-right">Grand Total:</td>
                            <td class="px-1 py-3 text-right" data-key="appropriation">
                                {{ $grandTotal->approved_appropriations > 0
                                    ? number_format($grandTotal->approved_appropriations, 2)
                                    : ($grandTotal->approved_appropriations < 0
                                        ? '(' . number_format(abs($grandTotal->approved_appropriations), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right" data-key="sb_appropriation">
                                {{ $grandTotal->sb_appropriation > 0
                                    ? number_format($grandTotal->sb_appropriation, 2)
                                    : ($grandTotal->sb_appropriation < 0
                                        ? '(' . number_format(abs($grandTotal->sb_appropriation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right" data-key="reversion">
                                {{ $grandTotal->reversion > 0
                                    ? number_format($grandTotal->reversion, 2)
                                    : ($grandTotal->reversion < 0
                                        ? '(' . number_format(abs($grandTotal->reversion), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right" data-key="realignment">
                                {{ $grandTotal->realignment > 0
                                    ? number_format($grandTotal->realignment, 2)
                                    : ($grandTotal->realignment < 0
                                        ? '(' . number_format(abs($grandTotal->realignment), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotal->authorized_appropriation > 0
                                    ? number_format($grandTotal->authorized_appropriation, 2)
                                    : ($grandTotal->authorized_appropriation < 0
                                        ? '(' . number_format(abs($grandTotal->authorized_appropriation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotal->for_later_release > 0
                                    ? number_format($grandTotal->for_later_release, 2)
                                    : ($grandTotal->for_later_release < 0
                                        ? '(' . number_format(abs($grandTotal->for_later_release), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotal->allotment > 0
                                    ? number_format($grandTotal->allotment, 2)
                                    : ($grandTotal->allotment < 0
                                        ? '(' . number_format(abs($grandTotal->allotment), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotal->obligation > 0
                                    ? number_format($grandTotal->obligation, 2)
                                    : ($grandTotal->obligation < 0
                                        ? '(' . number_format(abs($grandTotal->obligation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotal->appropriation_balance > 0
                                    ? number_format($grandTotal->appropriation_balance, 2)
                                    : ($grandTotal->appropriation_balance < 0
                                        ? '(' . number_format(abs($grandTotal->appropriation_balance), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-center">
                                {{ number_format($grandTotal->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-3 text-right">
                                {{ $grandTotal->allotment_balance > 0
                                    ? number_format($grandTotal->allotment_balance, 2)
                                    : ($grandTotal->allotment_balance < 0
                                        ? '(' . number_format(abs($grandTotal->allotment_balance), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-3 text-center">
                                {{ number_format($grandTotal->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        <tr class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-left text-sm">
                            <td class="px-1 py-3 text-right"> </td>
                        </tr>
                        <tr class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t-2 border-b border-gray-700 dark:border-gray-100 text-left text-sm">
                            <td id="fundRow" colspan="13" class="px-2 py-3 text-center">GENERAL FUND AND PROVINCIAL DEVELOPMENT FUND</td>
                        </tr>
                        @foreach ($sectors as $sector)
                        <tr id="sectorRow" class="bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-white text-xs italic font-semibold border-t border-b border-gray-400 dark:border-gray-100">
                            <td colspan="13" class="px-2 py-2 text-xs text-left uppercase">{{ $sector->sector }}</td>
                        </tr>
                        @foreach ($sector->presentAllotmentClasses as $allotmentClass)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="px-1 py-2 text-left">{{ $allotmentClass->description }}</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $allotmentClass->approved_appropriations > 0
                                    ? number_format($allotmentClass->approved_appropriations, 2)
                                    : ($allotmentClass->approved_appropriations < 0
                                        ? '(' . number_format(abs($allotmentClass->approved_appropriations), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $allotmentClass->sb_appropriation > 0
                                    ? number_format($allotmentClass->sb_appropriation, 2)
                                    : ($allotmentClass->sb_appropriation < 0
                                        ? '(' . number_format(abs($allotmentClass->sb_appropriation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $allotmentClass->reversion > 0
                                    ? number_format($allotmentClass->reversion, 2)
                                    : ($allotmentClass->reversion < 0
                                        ? '(' . number_format(abs($allotmentClass->reversion), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $allotmentClass->realignment > 0
                                    ? number_format($allotmentClass->realignment, 2)
                                    : ($allotmentClass->realignment < 0
                                        ? '(' . number_format(abs($allotmentClass->realignment), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->authorized_appropriation > 0
                                    ? number_format($allotmentClass->authorized_appropriation, 2)
                                    : ($allotmentClass->authorized_appropriation < 0
                                        ? '(' . number_format(abs($allotmentClass->authorized_appropriation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->allotment > 0
                                    ? number_format($allotmentClass->allotment, 2)
                                    : ($allotmentClass->allotment < 0
                                        ? '(' . number_format(abs($allotmentClass->allotment), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->for_later_release > 0
                                    ? number_format($allotmentClass->for_later_release, 2)
                                    : ($allotmentClass->for_later_release < 0
                                        ? '(' . number_format(abs($allotmentClass->for_later_release), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->obligation > 0
                                    ? number_format($allotmentClass->obligation, 2)
                                    : ($allotmentClass->obligation < 0
                                        ? '(' . number_format(abs($allotmentClass->obligation), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->appropriation_balance > 0
                                    ? number_format($allotmentClass->appropriation_balance, 2)
                                    : ($allotmentClass->appropriation_balance < 0
                                        ? '(' . number_format(abs($allotmentClass->appropriation_balance), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($allotmentClass->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->allotment_balance > 0
                                    ? number_format($allotmentClass->allotment_balance, 2)
                                    : ($allotmentClass->allotment_balance < 0
                                        ? '(' . number_format(abs($allotmentClass->allotment_balance), 2) . ')'
                                        : '-') }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($allotmentClass->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        {{-- Sector Totals --}}
                        <tr class="bg-gray-500 text-white dark:bg-gray-600 dark:text-white font-semibold border-t-2 border-b-2 border-gray-700 dark:border-gray-100">
                            <td class="px-1 py-2 text-right">Total:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $sector->totals->approved_appropriations == 0 ? '-' : ($sector->totals->approved_appropriations < 0 ? '(' . number_format(abs($sector->totals->approved_appropriations), 2) . ')' : number_format($sector->totals->approved_appropriations, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $sector->totals->sb_appropriation == 0 ? '-' : ($sector->totals->sb_appropriation < 0 ? '(' . number_format(abs($sector->totals->sb_appropriation), 2) . ')' : number_format($sector->totals->sb_appropriation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $sector->totals->reversion == 0 ? '-' : ($sector->totals->reversion < 0 ? '(' . number_format(abs($sector->totals->reversion), 2) . ')' : number_format($sector->totals->reversion, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $sector->totals->realignment == 0 ? '-' : ($sector->totals->realignment < 0 ? '(' . number_format(abs($sector->totals->realignment), 2) . ')' : number_format($sector->totals->realignment, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->authorized_appropriation == 0 ? '-' : ($sector->totals->authorized_appropriation < 0 ? '(' . number_format(abs($sector->totals->authorized_appropriation), 2) . ')' : number_format($sector->totals->authorized_appropriation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->allotment == 0 ? '-' : ($sector->totals->allotment < 0 ? '(' . number_format(abs($sector->totals->allotment), 2) . ')' : number_format($sector->totals->allotment, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->for_later_release == 0 ? '-' : ($sector->totals->for_later_release < 0 ? '(' . number_format(abs($sector->totals->for_later_release), 2) . ')' : number_format($sector->totals->for_later_release, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->obligation == 0 ? '-' : ($sector->totals->obligation < 0 ? '(' . number_format(abs($sector->totals->obligation), 2) . ')' : number_format($sector->totals->obligation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->appropriation_balance == 0 ? '-' : ($sector->totals->appropriation_balance < 0 ? '(' . number_format(abs($sector->totals->appropriation_balance), 2) . ')' : number_format($sector->totals->appropriation_balance, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-center">
                                {{ number_format($sector->totals->appropriation_accomplishment, 2) }}%
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->allotment_balance == 0 ? '-' : ($sector->totals->allotment_balance < 0 ? '(' . number_format(abs($sector->totals->allotment_balance), 2) . ')' : number_format($sector->totals->allotment_balance, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-center">
                                {{ number_format($sector->totals->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        {{-- Grand Totals --}}
                        <tr class="bg-gray-700 text-white dark:bg-gray-700 font-bold text-right uppercase border-t-2 border-b-2 border-white dark:border-gray-100">
                            <td class="px-1 py-2 text-right">Grand Total:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $grandTotals->approved_appropriations == 0 ? '-' : ($grandTotals->approved_appropriations < 0 ? '(' . number_format(abs($grandTotals->approved_appropriations), 2) . ')' : number_format($grandTotals->approved_appropriations, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $grandTotals->sb_appropriation == 0 ? '-' : ($grandTotals->sb_appropriation < 0 ? '(' . number_format(abs($grandTotals->sb_appropriation), 2) . ')' : number_format($grandTotals->sb_appropriation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $grandTotals->reversion == 0 ? '-' : ($grandTotals->reversion < 0 ? '(' . number_format(abs($grandTotals->reversion), 2) . ')' : number_format($grandTotals->reversion, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $grandTotals->realignment == 0 ? '-' : ($grandTotals->realignment < 0 ? '(' . number_format(abs($grandTotals->realignment), 2) . ')' : number_format($grandTotals->realignment, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $grandTotals->authorized_appropriation == 0 ? '-' : ($grandTotals->authorized_appropriation < 0 ? '(' . number_format(abs($grandTotals->authorized_appropriation), 2) . ')' : number_format($grandTotals->authorized_appropriation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $grandTotals->allotment == 0 ? '-' : ($grandTotals->allotment < 0 ? '(' . number_format(abs($grandTotals->allotment), 2) . ')' : number_format($grandTotals->allotment, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $grandTotals->for_later_release == 0 ? '-' : ($grandTotals->for_later_release < 0 ? '(' . number_format(abs($grandTotals->for_later_release), 2) . ')' : number_format($grandTotals->for_later_release, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $grandTotals->obligation == 0 ? '-' : ($grandTotals->obligation < 0 ? '(' . number_format(abs($grandTotals->obligation), 2) . ')' : number_format($grandTotals->obligation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $grandTotals->appropriation_balance == 0 ? '-' : ($grandTotals->appropriation_balance < 0 ? '(' . number_format(abs($grandTotals->appropriation_balance), 2) . ')' : number_format($grandTotals->appropriation_balance, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-center">
                                {{ number_format($grandTotals->appropriation_accomplishment, 2) }}%
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $grandTotals->allotment_balance == 0 ? '-' : ($grandTotals->allotment_balance < 0 ? '(' . number_format(abs($grandTotals->allotment_balance), 2) . ')' : number_format($grandTotals->allotment_balance, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-center">
                                {{ number_format($grandTotals->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        <tr class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-left text-sm">
                            <td class="px-1 py-3 text-right"> </td>
                        </tr>
                        <tr class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t-2 border-b border-gray-700 dark:border-gray-100 text-left text-sm">
                            <td id="fundRow" colspan="13" class="px-2 py-3 text-center">OVERALL TOTAL OF GENERAL FUND AND PROVINCIAL DEVELOPMENT FUND</td>
                        </tr>
                        @foreach ($computedAllotmentClasses as $allotmentClass)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="px-1 py-2 text-left">{{ $allotmentClass->allotment_class }}</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $allotmentClass->approved_appropriations == 0 ? '-' : ($allotmentClass->approved_appropriations < 0 ? '(' . number_format(abs($allotmentClass->approved_appropriations), 2) . ')' : number_format($allotmentClass->approved_appropriations, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $allotmentClass->sb_appropriation == 0 ? '-' : ($allotmentClass->sb_appropriation < 0 ? '(' . number_format(abs($allotmentClass->sb_appropriation), 2) . ')' : number_format($allotmentClass->sb_appropriation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $allotmentClass->reversion == 0 ? '-' : ($allotmentClass->reversion < 0 ? '(' . number_format(abs($allotmentClass->reversion), 2) . ')' : number_format($allotmentClass->reversion, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $allotmentClass->realignment == 0 ? '-' : ($allotmentClass->realignment < 0 ? '(' . number_format(abs($allotmentClass->realignment), 2) . ')' : number_format($allotmentClass->realignment, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->authorized_appropriation == 0 ? '-' : ($allotmentClass->authorized_appropriation < 0 ? '(' . number_format(abs($allotmentClass->authorized_appropriation), 2) . ')' : number_format($allotmentClass->authorized_appropriation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->allotment == 0 ? '-' : ($allotmentClass->allotment < 0 ? '(' . number_format(abs($allotmentClass->allotment), 2) . ')' : number_format($allotmentClass->allotment, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->for_later_release == 0 ? '-' : ($allotmentClass->for_later_release < 0 ? '(' . number_format(abs($allotmentClass->for_later_release), 2) . ')' : number_format($allotmentClass->for_later_release, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->obligation == 0 ? '-' : ($allotmentClass->obligation < 0 ? '(' . number_format(abs($allotmentClass->obligation), 2) . ')' : number_format($allotmentClass->obligation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->appropriation_balance == 0 ? '-' : ($allotmentClass->appropriation_balance < 0 ? '(' . number_format(abs($allotmentClass->appropriation_balance), 2) . ')' : number_format($allotmentClass->appropriation_balance, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-center">
                                {{ number_format($allotmentClass->appropriation_accomplishment, 2) }}%
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $allotmentClass->allotment_balance == 0 ? '-' : ($allotmentClass->allotment_balance < 0 ? '(' . number_format(abs($allotmentClass->allotment_balance), 2) . ')' : number_format($allotmentClass->allotment_balance, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-center">
                                {{ number_format($allotmentClass->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        {{-- Over all Grand Totals --}}
                        <tr class="bg-gray-700 text-white dark:bg-gray-700 font-bold text-right uppercase border-t-2 border-b-2 border-white dark:border-gray-100">
                            <td class="px-1 py-2 text-right">GRAND TOTAL:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $overAllGrandTotal->approved_appropriations == 0 ? '-' : ($overAllGrandTotal->approved_appropriations < 0 ? '(' . number_format(abs($overAllGrandTotal->approved_appropriations), 2) . ')' : number_format($overAllGrandTotal->approved_appropriations, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $overAllGrandTotal->sb_appropriation == 0 ? '-' : ($overAllGrandTotal->sb_appropriation < 0 ? '(' . number_format(abs($overAllGrandTotal->sb_appropriation), 2) . ')' : number_format($overAllGrandTotal->sb_appropriation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $overAllGrandTotal->reversion == 0 ? '-' : ($overAllGrandTotal->reversion < 0 ? '(' . number_format(abs($overAllGrandTotal->reversion), 2) . ')' : number_format($overAllGrandTotal->reversion, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $overAllGrandTotal->realignment == 0 ? '-' : ($overAllGrandTotal->realignment < 0 ? '(' . number_format(abs($overAllGrandTotal->realignment), 2) . ')' : number_format($overAllGrandTotal->realignment, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $overAllGrandTotal->authorized_appropriation == 0 ? '-' : ($overAllGrandTotal->authorized_appropriation < 0 ? '(' . number_format(abs($overAllGrandTotal->authorized_appropriation), 2) . ')' : number_format($overAllGrandTotal->authorized_appropriation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $overAllGrandTotal->allotment == 0 ? '-' : ($overAllGrandTotal->allotment < 0 ? '(' . number_format(abs($overAllGrandTotal->allotment), 2) . ')' : number_format($overAllGrandTotal->allotment, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $overAllGrandTotal->for_later_release == 0 ? '-' : ($overAllGrandTotal->for_later_release < 0 ? '(' . number_format(abs($overAllGrandTotal->for_later_release), 2) . ')' : number_format($overAllGrandTotal->for_later_release, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $overAllGrandTotal->obligation == 0 ? '-' : ($overAllGrandTotal->obligation < 0 ? '(' . number_format(abs($overAllGrandTotal->obligation), 2) . ')' : number_format($overAllGrandTotal->obligation, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $overAllGrandTotal->appropriation_balance == 0 ? '-' : ($overAllGrandTotal->appropriation_balance < 0 ? '(' . number_format(abs($overAllGrandTotal->appropriation_balance), 2) . ')' : number_format($overAllGrandTotal->appropriation_balance, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-center">
                                {{ number_format($overAllGrandTotal->appropriation_accomplishment, 2) }}%
                            </td>
                            <td class="px-1 py-2 text-right">
                                {{ $overAllGrandTotal->allotment_balance == 0 ? '-' : ($overAllGrandTotal->allotment_balance < 0 ? '(' . number_format(abs($overAllGrandTotal->allotment_balance), 2) . ')' : number_format($overAllGrandTotal->allotment_balance, 2)) }}
                            </td>
                            <td class="px-1 py-2 text-center">
                                {{ number_format($overAllGrandTotal->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    </div>
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
        window.printSAAOBGFCurrentSummaryTable = function() {
            if (!validateSignatories()) return;
            runPrintSAAOBGFCurrentSummaryTable(); // call actual print function
        };

        // Intercept Excel Export Submit
        document.querySelector(`form[action="{{ route('saaobGFCurrentSummary.exportExcel') }}"]`)
            .addEventListener('submit', function(e) {
                if (!validateSignatories()) {
                    e.preventDefault();
                }
            });

        function runPrintSAAOBGFCurrentSummaryTable() {
            const table = document.getElementById('saaobGFCurrentSummaryTable').cloneNode(true);
            const hiddenKeys = [
                'appropriation', 'sb_appropriation', 'reversion', 'realignment'
            ];

            table.querySelectorAll('thead th[data-key], tbody td[data-key]').forEach(cell => {
                const key = cell.getAttribute('data-key');
                if (hiddenKeys.includes(key)) {
                    cell.remove();
                }
            });

            // Styling rows
            table.querySelectorAll('[id^="fundRow"]').forEach(tr => {
                tr.style.textTransform = 'uppercase';
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
            });
            table.querySelectorAll('[id^="fundSourceRow"]').forEach(tr => {
                tr.style.textTransform = 'uppercase';
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
            });
            table.querySelectorAll('[id^="sectorRow"]').forEach(tr => {
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
                for (let i = 1; i < cells.length; i++) {
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

            // Get selected fund
            const fundSelect = document.getElementById('fund_filter');
            let fundText = 'ALL FUNDS';
            if (fundSelect && fundSelect.selectedIndex > 0) {
                const selectedOption = fundSelect.options[fundSelect.selectedIndex];
                fundText = (selectedOption.getAttribute('data-fund-name') || selectedOption.text).toUpperCase();
            }

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