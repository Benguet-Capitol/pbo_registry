<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                @php
                $selectedFund = null;

                if (request('fund_filter')) {
                if (request('fund_filter') === 'others') {
                $selectedFund = 'BeGHEE and SEF';
                } else {
                $selectedFund = request('fund_filter'); // e.g., 'General Fund'
                }
                }
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                    {{ __('Statement of Appropriations, Allotments, Obligations and Balances') }}
                    |
                    <span class="text-blue-800 dark:text-blue-400">
                        {{ $selectedFund ?? 'All Funds' }}
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
            <!--  Fund Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="fund_filter"
                    id="fund_filter"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                    onchange="this.form.submit()">
                    <option value="">All Funds</option>

                    @foreach($allFunds as $fund)
                    @if($fund === 'General Fund')
                    <option value="{{ $fund }}" data-fund-name="{{ $fund }}" {{ request('fund_filter') == $fund ? 'selected' : '' }}>
                        {{ $fund }}
                    </option>
                    @endif
                    @endforeach

                    <option value="others"
                        data-fund-name="Benguet General Hospital Economic Enterprise and Special Education Fund" {{ request('fund_filter') == 'others' ? 'selected' : '' }}>
                        BeGHEE and SEF
                    </option>
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
            <div class="flex space-x-2 flex-col items-start">
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
            <div class="flex space-x-2 flex-col items-start">
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
                    onclick="printSAAOBFundSectorTable()"
                    class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95"
                    type="button">
                    <i class="fas fa-print text-lg mr-2 -ml-1 w-4 h-4"></i>
                    Print Report
                </button>

                <button 
                    type="button"
                    onclick="exportSAAOBFundSectorExcel()"
                    class="text-green-700 inline-flex leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95"
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
                <table id="saaobFundSectorTable" class="w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gradient-to-r from-gray-700 to-gray-800 text-white dark:bg-gradient-to-r dark:from-gray-200 dark:to-gray-300 dark:text-gray-900 transition-colors duration-300 ease-in-out">
                        <tr>
                            <th class="px-1 py-1 w-[70px] text-center">Code</th>
                            <th class="px-1 py-1 w-[170px] text-center">Function / Program / Project</th>
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
                            <th class="px-1 py-1 w-[100px] text-center">Balance</th>
                            <th class="px-1 py-1 w-[70px] text-center">% of Utilization</th>
                        </tr>
                    </thead>

                    <tbody class="border border-gray-300 dark:border-gray-600 text-[10px]">
                        @foreach($groupedFunds as $fund)
                        <tr id="fundRow" class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-gray-900 dark:to-gray-800 text-gray-700 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-center text-sm transition-colors duration-300 ease-in-out">
                            <td colspan="15" class="px-2 py-3">{{ $fund->fund_type }}</td>
                        </tr>
                        @foreach ($fund->matchedSectorsByCategory as $category => $sectors)
                        <tr id="fundSourceRow" class="bg-gradient-to-r from-gray-600 to-gray-700 text-white dark:from-gray-200 dark:to-gray-300 dark:text-gray-800 font-bold text-xs border-t border-b border-gray-700 dark:border-gray-100 transition-colors duration-300 ease-in-out hover:from-gray-700 hover:to-gray-800 dark:hover:from-gray-300 dark:hover:to-gray-400">
                            <td colspan="15" class="px-4 py-2"> {{ $category }} Appropriations </td>
                        </tr>
                        @foreach($sectors as $sector)
                        @if ($sector->sector_code !== '' && $sector->present_allotment_classes->isNotEmpty())
                        <tr id="sectorRow" class="bg-gradient-to-r from-gray-200 to-gray-300 text-gray-700 dark:from-gray-600 dark:to-gray-700 dark:text-white text-xs font-semibold italic border-t border-b border-gray-400 dark:border-gray-100 transition-all duration-300 ease-in-out hover:from-gray-300 hover:to-gray-400 dark:hover:from-gray-700 dark:hover:to-gray-800">
                            <td class="px-1 py-2 text-center"> {{ $sector->sector_code }}</td>
                            <td colspan="14" class="px-1 py-2 text-left">{{ $sector->sector }}</td>
                        </tr>
                        @foreach ($sector->present_allotment_classes as $aClass)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-150 ease-in-out">
                            <td class="px-1 py-2"></td>
                            <td class="px-1 py-2 text-left">{{ $aClass->description }}</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                @if (is_null($aClass->approved_appropriation) || $aClass->approved_appropriation == 0)
                                    -
                                @elseif ($aClass->approved_appropriation < 0)
                                    ({{ number_format(abs($aClass->approved_appropriation), 2) }})
                                @else
                                    {{ number_format($aClass->approved_appropriation, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                @if (is_null($aClass->supplemental) || $aClass->supplemental == 0)
                                    -
                                @elseif ($aClass->supplemental < 0)
                                    ({{ number_format(abs($aClass->supplemental), 2) }})
                                @else
                                    {{ number_format($aClass->supplemental, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                @if (is_null($aClass->reversion) || $aClass->reversion == 0)
                                    -
                                @elseif ($aClass->reversion < 0)
                                    ({{ number_format(abs($aClass->reversion), 2) }})
                                @else
                                    {{ number_format($aClass->reversion, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                @if (is_null($aClass->realignment) || $aClass->realignment == 0)
                                    -
                                @elseif ($aClass->realignment < 0)
                                    ({{ number_format(abs($aClass->realignment), 2) }})
                                @else
                                    {{ number_format($aClass->realignment, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->authorized_appropriation) || $aClass->authorized_appropriation == 0)
                                    -
                                @elseif ($aClass->authorized_appropriation < 0)
                                    ({{ number_format(abs($aClass->authorized_appropriation), 2) }})
                                @else
                                    {{ number_format($aClass->authorized_appropriation, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->allotment) || $aClass->allotment == 0)
                                    -
                                @elseif ($aClass->allotment < 0)
                                    ({{ number_format(abs($aClass->allotment), 2) }})
                                @else
                                    {{ number_format($aClass->allotment, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->for_later_release) || $aClass->for_later_release == 0)
                                    -
                                @elseif ($aClass->for_later_release < 0)
                                    ({{ number_format(abs($aClass->for_later_release), 2) }})
                                @else
                                    {{ number_format($aClass->for_later_release, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->obligations) || $aClass->obligations == 0)
                                    -
                                @elseif ($aClass->obligations < 0)
                                    ({{ number_format(abs($aClass->obligations), 2) }})
                                @else
                                    {{ number_format($aClass->obligations, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                @if (is_null($aClass->appropriation_balance) || $aClass->appropriation_balance == 0)
                                    -
                                @elseif ($aClass->appropriation_balance < 0)
                                    ({{ number_format(abs($aClass->appropriation_balance), 2) }})
                                @else
                                    {{ number_format($aClass->appropriation_balance, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($aClass->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->allotment_balance) || $aClass->allotment_balance == 0)
                                    -
                                @elseif ($aClass->allotment_balance < 0)
                                    ({{ number_format(abs($aClass->allotment_balance), 2) }})
                                @else
                                    {{ number_format($aClass->allotment_balance, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($aClass->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white dark:from-gray-600 dark:to-gray-700 dark:text-white font-semibold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 transition-colors duration-300 ease-in-out">
                            <td colspan="2" class="px-1 py-2 text-right">Total:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $sector->totals->approved_appropriation == 0 ? '-' : ($sector->totals->approved_appropriation < 0 ? '(' . number_format(abs($sector->totals->approved_appropriation), 2) . ')' : number_format($sector->totals->approved_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $sector->totals->supplemental == 0 ? '-' : ($sector->totals->supplemental < 0 ? '(' . number_format(abs($sector->totals->supplemental), 2) . ')' : number_format($sector->totals->supplemental, 2)) }}
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
                                {{ $sector->totals->obligations == 0 ? '-' : ($sector->totals->obligations < 0 ? '(' . number_format(abs($sector->totals->obligations), 2) . ')' : number_format($sector->totals->obligations, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                {{ $sector->totals->appropriation_balance == 0 ? '-' : ($sector->totals->appropriation_balance < 0 ? '(' . number_format(abs($sector->totals->appropriation_balance), 2) . ')' : number_format($sector->totals->appropriation_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($sector->totals->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->allotment_balance == 0 ? '-' : ($sector->totals->allotment_balance < 0 ? '(' . number_format(abs($sector->totals->allotment_balance), 2) . ')' : number_format($sector->totals->allotment_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($sector->totals->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        @php
                        $hasValidSector = $sectors->contains(fn($s) =>
                        $s->sector_code !== '' && $s->present_allotment_classes->isNotEmpty()
                        );
                        @endphp
                        @if ($hasValidSector)
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 dark:from-gray-200 dark:to-gray-300 dark:text-gray-800 font-bold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 transition-colors duration-300 ease-in-out">
                            <td colspan="2" class="px-1 py-2 text-right">Total {{ $category }} Appropriations:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $fund->categoryTotals[$category]->approved_appropriation == 0 ? '-' : ($fund->categoryTotals[$category]->approved_appropriation < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->approved_appropriation), 2) . ')' : number_format($fund->categoryTotals[$category]->approved_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $fund->categoryTotals[$category]->supplemental == 0 ? '-' : ($fund->categoryTotals[$category]->supplemental < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->supplemental), 2) . ')' : number_format($fund->categoryTotals[$category]->supplemental, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $fund->categoryTotals[$category]->reversion == 0 ? '-' : ($fund->categoryTotals[$category]->reversion < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->reversion), 2) . ')' : number_format($fund->categoryTotals[$category]->reversion, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $fund->categoryTotals[$category]->realignment == 0 ? '-' : ($fund->categoryTotals[$category]->realignment < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->realignment), 2) . ')' : number_format($fund->categoryTotals[$category]->realignment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->authorized_appropriation == 0 ? '-' : ($fund->categoryTotals[$category]->authorized_appropriation < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->authorized_appropriation), 2) . ')' : number_format($fund->categoryTotals[$category]->authorized_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->allotment == 0 ? '-' : ($fund->categoryTotals[$category]->allotment < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->allotment), 2) . ')' : number_format($fund->categoryTotals[$category]->allotment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->for_later_release == 0 ? '-' : ($fund->categoryTotals[$category]->for_later_release < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->for_later_release), 2) . ')' : number_format($fund->categoryTotals[$category]->for_later_release, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->obligations == 0 ? '-' : ($fund->categoryTotals[$category]->obligations < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->obligations), 2) . ')' : number_format($fund->categoryTotals[$category]->obligations, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                {{ $fund->categoryTotals[$category]->appropriation_balance == 0 ? '-' : ($fund->categoryTotals[$category]->appropriation_balance < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->appropriation_balance), 2) . ')' : number_format($fund->categoryTotals[$category]->appropriation_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fund->categoryTotals[$category]->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->allotment_balance == 0 ? '-' : ($fund->categoryTotals[$category]->allotment_balance < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->allotment_balance), 2) . ')' : number_format($fund->categoryTotals[$category]->allotment_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($fund->categoryTotals[$category]->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endif

                        {{-- Display Allotment Class Totals Right After Category Totals --}}
                        @if (
                        $sectors->filter(fn($sector) => $sector->present_allotment_classes->isNotEmpty())->isNotEmpty()
                        && $fund->categoryClassStats[$category]?->isNotEmpty()
                        )
                        <tr id="fundSourceRow" class="bg-gradient-to-r from-gray-700 to-gray-800 text-white text-xs font-bold border-t border-b border-gray-600 transition-colors duration-300 ease-in-out hover:from-gray-800 hover:to-gray-900">
                            <td colspan="15" class="px-2 py-2 text-left">{{ $category }} Appropriations by Allotment Class</td>
                        </tr>

                        @foreach ($fund->categoryClassStats[$category] as $classCode => $row)
                        <tr class="bg-white text-gray-800 border-b dark:bg-gray-900 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-800 transition-colors duration-150 ease-in-out">
                            <td colspan="2" class="px-1 py-2 text-left">{{ $row->description }}</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $row->approved_appropriation == 0 || is_null($row->approved_appropriation) ? '-' : ($row->approved_appropriation < 0 ? '(' . number_format(abs($row->approved_appropriation), 2) . ')' : number_format($row->approved_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $row->supplemental == 0 || is_null($row->supplemental) ? '-' : ($row->supplemental < 0 ? '(' . number_format(abs($row->supplemental), 2) . ')' : number_format($row->supplemental, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $row->reversion == 0 || is_null($row->reversion) ? '-' : ($row->reversion < 0 ? '(' . number_format(abs($row->reversion), 2) . ')' : number_format($row->reversion, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $row->realignment == 0 || is_null($row->realignment) ? '-' : ($row->realignment < 0 ? '(' . number_format(abs($row->realignment), 2) . ')' : number_format($row->realignment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->authorized_appropriation == 0 || is_null($row->authorized_appropriation) ? '-' : ($row->authorized_appropriation < 0 ? '(' . number_format(abs($row->authorized_appropriation), 2) . ')' : number_format($row->authorized_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->allotment == 0 || is_null($row->allotment) ? '-' : ($row->allotment < 0 ? '(' . number_format(abs($row->allotment), 2) . ')' : number_format($row->allotment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->for_later_release == 0 || is_null($row->for_later_release) ? '-' : ($row->for_later_release < 0 ? '(' . number_format(abs($row->for_later_release), 2) . ')' : number_format($row->for_later_release, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->obligations == 0 || is_null($row->obligations) ? '-' : ($row->obligations < 0 ? '(' . number_format(abs($row->obligations), 2) . ')' : number_format($row->obligations, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                {{ $row->appropriation_balance == 0 || is_null($row->appropriation_balance) ? '-' : ($row->appropriation_balance < 0 ? '(' . number_format(abs($row->appropriation_balance), 2) . ')' : number_format($row->appropriation_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($row->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->allotment_balance == 0 || is_null($row->allotment_balance) ? '-' : ($row->allotment_balance < 0 ? '(' . number_format(abs($row->allotment_balance), 2) . ')' : number_format($row->allotment_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($row->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        {{-- Totals per Category after Allotment Class Totals --}}
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 dark:from-gray-200 dark:to-gray-300 dark:text-gray-800 font-bold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 transition-colors duration-300 ease-in-out">
                            <td colspan="2" class="px-1 py-2 text-right">Total {{ $category }} Appropriations:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                @php $val = $fund->categoryTotals[$category]->approved_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                @php $val = $fund->categoryTotals[$category]->supplemental ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                @php $val = $fund->categoryTotals[$category]->reversion ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                @php $val = $fund->categoryTotals[$category]->realignment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->authorized_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->allotment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->for_later_release ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->obligations ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                @php $val = $fund->categoryTotals[$category]->appropriation_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fund->categoryTotals[$category]->appropriation_accomplishment ?? 0, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->allotment_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($fund->categoryTotals[$category]->allotment_accomplishment ?? 0, 2) }}%
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        <tr class="bg-gray-700 text-white dark:bg-gray-700 font-bold text-right border-t-2 border-b-2 border-white dark:border-gray-100">
                            <td colspan="2" class="px-1 py-4 text-right">Grand Total {{ $fund->fund_type }}:</td>
                           <td class="px-1 py-4 text-right" data-key="appropriation">
                                @php $val = $fund->grandTotal->approved_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right" data-key="sb_appropriation">
                                @php $val = $fund->grandTotal->supplemental ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right" data-key="reversion">
                                @php $val = $fund->grandTotal->reversion ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right" data-key="realignment">
                                @php $val = $fund->grandTotal->realignment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->authorized_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->allotment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->for_later_release ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->obligations ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right" data-key="appropriation_balance">
                                @php $val = $fund->grandTotal->appropriation_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            {{-- Percentages stay as-is --}}
                            <td class="px-1 py-4 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fund->grandTotal->appropriation_accomplishment ?? 0, 2) }}%
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->allotment_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-center">
                                {{ number_format($fund->grandTotal->allotment_accomplishment ?? 0, 2) }}%
                            </td>
                        </tr>
                        @endforeach
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
        window.printSAAOBFundSectorTable = function() {
            if (!validateSignatories()) return;
            runPrintSAAOBFundSectorTable(); // call actual print function
        };

        // Intercept Excel Export with AJAX
        async function exportSAAOBFundSectorExcel() {
            if (!validateSignatories()) {
                return;
            }

            const btn = document.getElementById('excel-export-btn');
            btn.disabled = true;

            // Show loading toast
            showLoadingToast();

            const params = new URLSearchParams({
                year1: document.querySelector('[name="year1"]').value,
                fund_filter: document.querySelector('[name="fund_filter"]').value,
                as_of_filter: document.querySelector('[name="as_of_filter"]').value,
                signatory_name: document.querySelector('[name="signatory_name"]').value,
                signatory_designation: document.querySelector('[name="signatory_designation"]').value,
            });

            try {
                const response = await fetch('{{ route('saaobFundSector.exportExcel') }}?' + params);
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = response.headers.get('content-disposition')?.split('filename=')[1]?.replace(/"/g, '') || 'SAAOB_Fund_Sector_Report.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                    
                    // Hide loading toast and show success toast
                    closeLoadingToast();
                    showSuccessToast('Excel report generated successfully');
                }
            } catch (error) {
                console.error('Export failed:', error);
                closeLoadingToast();
            } finally {
                btn.disabled = false;
            }
        }

        // Toast notification functions
        function showSuccessToast(message = 'Excel report generated successfully') {
            const toast = document.getElementById('success-toast');
            const messageEl = document.getElementById('toast-message');
            
            if (messageEl) {
                messageEl.textContent = message;
            }
            
            toast.classList.remove('hide', 'pointer-events-none');
            toast.classList.add('show');
            
            // Auto dismiss after 4 seconds
            setTimeout(closeSuccessToast, 4000);
        }

        function closeSuccessToast() {
            const toast = document.getElementById('success-toast');
            toast.classList.remove('show');
            toast.classList.add('hide', 'pointer-events-none');
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

        function runPrintSAAOBFundSectorTable() {
            const table = document.getElementById('saaobFundSectorTable').cloneNode(true);
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

            // Styling rows
            table.querySelectorAll('[id^="fundRow"]').forEach(tr => {
                tr.style.textTransform = 'uppercase';
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
                tr.style.textAlign = 'center';
            });
            table.querySelectorAll('[id^="fundSourceRow"]').forEach(tr => {
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
                        cells.forEach(cell => {
                            cell.textContent = cell.textContent;
                        });
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
            let fundText = 'All Funds';
            if (fundSelect && fundSelect.selectedIndex > 0) {
                const selectedOption = fundSelect.options[fundSelect.selectedIndex];
                fundText = (selectedOption.getAttribute('data-fund-name') || selectedOption.text);
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
                <div style="font-size:12px; ">(Current Legislative Appropriation)</div>
                <div style="font-size:12px; ">${fundText}</div>
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

    <style>
        /* Smooth transitions for filter inputs */
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
        tr[id="fundRow"] {
            animation: fadeIn 0.4s ease-in-out;
        }

        tr[id="fundSourceRow"] {
            animation: fadeIn 0.4s ease-in-out 0.1s both;
        }

        tr[id="sectorRow"] {
            animation: fadeIn 0.4s ease-in-out 0.2s both;
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
        tr[id="fundSourceRow"]:hover {
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
</x-app-layout>