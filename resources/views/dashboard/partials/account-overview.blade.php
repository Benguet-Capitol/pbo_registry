{{-- Accounts overview section: date filters, the account distribution graph with
     legend, activity metrics cards, and the Obligation Distribution table. Extracted
     from dashboard/accounts.blade.php to keep that file's length manageable. --}}
    <!-- Filter and Search Section -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <form method="GET" action="" id="filterForm">
            <!-- Date Range Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-end mb-3">
                <!-- From Date Filter -->
                <div class="flex flex-col md:col-span-1">
                    <label for="fromDate" class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-2">From Date</label>
                    <x-form.input type="date" name="from_date" id="fromDate" value="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
                </div>

                <!-- To Date Filter -->
                <div class="flex flex-col md:col-span-1">
                    <label for="toDate" class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-2">To Date</label>
                    <x-form.input type="date" name="to_date" id="toDate" value="{{ request('to_date') }}" min="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
                </div>

                <!-- Apply Date Filter Button -->
                <div class="flex">
                    <button type="submit" class="w-full text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-filter mr-2"></i>Apply Date Filter
                    </button>
                </div>
            </div>

            <!-- Search Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-center">
                <!-- Search Input -->
                <div class="flex items-center space-x-2 lg:col-span-3">
                    <x-form.input type="text" name="search" id="searchInput" value="{{ session('search') ?? request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>
            </div>
        </form>
    </div>

    {{-- Analytics & Insights Panel (for Accounts) --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 mb-4">
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                Insights & Analytics
            </h3>
            <button onclick="toggleWidget('accountAnalyticsPanel')" class="text-blue-500 hover:text-gray-700 dark:text-blue-400 dark:hover:text-gray-200">
                <i class="fas fa-circle-chevron-down" id="accountAnalyticsPanelToggle"></i>
            </button>
        </div>

        <div id="accountAnalyticsPanelContent" style="display: none;">

            <!-- 0. Account Distribution Graph -->
            <div class="mb-4">
                <h4 class="text-md font-semibold text-gray-800 dark:text-gray-100 mb-3">Account Distribution by Authorized Appropriations</h4>
                
                <!-- Stacked Bar -->
                <div id="stackedBarContainer" class="mb-4 relative">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 overflow-visible flex relative">
                        @php
                            // Calculate total authorized appropriations for percentage calculation
                            $totalForPercentage = $officeAllotmentClasses->appropriations->sum('authorized_appropriations');
                            
                            // Group by account code and sum authorized appropriations
                            $accountDistribution = $officeAllotmentClasses->appropriations->groupBy('account_code')->map(function($items) {
                                return [
                                    'description' => $items->first()->description ?? 'Unknown',
                                    'total' => $items->sum('authorized_appropriations'),
                                    'account_code' => $items->first()->account_code
                                ];
                            })->sortByDesc('total');
                            
                            // Fixed color assignments for common accounts
                            $fixedColors = [
                                '5010101000' => ['color' => 'bg-blue-500', 'hover' => 'hover:bg-blue-600'],      // Salaries
                                '5010201000' => ['color' => 'bg-green-500', 'hover' => 'hover:bg-green-600'],    // Other Compensation
                                '5010301000' => ['color' => 'bg-cyan-500', 'hover' => 'hover:bg-cyan-600'],      // Personnel Benefits
                                '5010402000' => ['color' => 'bg-purple-500', 'hover' => 'hover:bg-purple-600'],  // Traveling Expenses
                                '5010499000' => ['color' => 'bg-orange-500', 'hover' => 'hover:bg-orange-600'],  // Other MOOE
                                '5020101000' => ['color' => 'bg-red-500', 'hover' => 'hover:bg-red-600'],        // Office Equipment
                                '5020321000' => ['color' => 'bg-violet-500', 'hover' => 'hover:bg-violet-600'],  // ICT Equipment
                            ];
                            
                            // Fallback colors for unknown accounts
                            $fallbackColors = [
                                ['color' => 'bg-pink-600', 'hover' => 'hover:bg-pink-700'],
                                ['color' => 'bg-indigo-600', 'hover' => 'hover:bg-indigo-700'],
                                ['color' => 'bg-teal-600', 'hover' => 'hover:bg-teal-700'],
                                ['color' => 'bg-lime-600', 'hover' => 'hover:bg-lime-700'],
                                ['color' => 'bg-amber-600', 'hover' => 'hover:bg-amber-700'],
                                ['color' => 'bg-rose-600', 'hover' => 'hover:bg-rose-700'],
                            ];
                            
                            function getAccountColors($accountCode, $fixedColors, $fallbackColors) {
                                if (isset($fixedColors[$accountCode])) {
                                    return $fixedColors[$accountCode];
                                }
                                $index = abs(crc32($accountCode)) % count($fallbackColors);
                                return $fallbackColors[$index];
                            }
                        @endphp

                        @foreach($accountDistribution as $index => $account)
                            @php
                                $percentage = $totalForPercentage > 0 ? ($account['total'] / $totalForPercentage) * 100 : 0;
                                $colors = getAccountColors($account['account_code'], $fixedColors, $fallbackColors);
                                $barColor = $colors['color'];
                                $hoverColor = $colors['hover'];
                            @endphp
                            
                            <div 
                                class="{{ $barColor }} {{ $hoverColor }} h-8 transition-all duration-200 ease-out flex items-center justify-center relative stacked-segment cursor-pointer"
                                style="width: {{ $percentage }}%"
                                data-account="{{ $account['account_code'] }}"
                                data-description="{{ $account['description'] }}"
                                data-total="{{ $account['total'] }}"
                                data-percentage="{{ $percentage }}"
                                onmouseenter="showTooltip(this)"
                                onmouseleave="hideTooltip(this)"
                            >
                                @if($percentage > 8)
                                    <span class="text-white text-xs font-semibold px-1 text-center truncate pointer-events-none">
                                        {{ Str::limit($account['description'], 25, '...') }}
                                    </span>
                                @endif
                                
                                <!-- Tooltip -->
                                <div class="tooltip-box absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap shadow-xl" style="display: none; z-index: 9999;">
                                    <div class="font-semibold">{{ $account['account_code'] }}</div>
                                    <div class="text-[10px] max-w-xs truncate">{{ $account['description'] }}</div>
                                    <div>{{ number_format($percentage, 2) }}%</div>
                                    <div>{{ number_format($account['total'], 2) }}</div>
                                    <!-- Arrow -->
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Legend with amounts added -->
                <div id="graphLegend" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    @foreach($accountDistribution->take(5) as $index => $accountItem)
                        @php
                            $percentage = $totalForPercentage > 0 ? ($accountItem['total'] / $totalForPercentage) * 100 : 0;
                            $colors = getAccountColors($accountItem['account_code'], $fixedColors, $fallbackColors);
                            $barColor = $colors['color'];
                        @endphp
                        
                        <div class="flex items-center space-x-2 text-xs">
                            <div class="w-4 h-4 {{ $barColor }} rounded flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-700 dark:text-gray-300 truncate" title="{{ $accountItem['account_code'] }} - {{ $accountItem['description'] }}">
                                    {{ $accountItem['account_code'] }} - {{ Str::limit($accountItem['description'], 40, '...') }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    {{ number_format($percentage, 1) }}%
                                </div>
                                <div class="text-gray-600 dark:text-gray-400 text-[10px]">
                                    {{ number_format($accountItem['total'], 2) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($accountDistribution->count() > 5)
                        <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 italic">
                            +{{ $accountDistribution->count() - 5 }} more accounts
                        </div>
                    @endif
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700 mb-4">

            <!-- 1. Activity Metrics Cards -->
            <div class="mb-4">
                <h4 class="text-md font-semibold text-gray-800 dark:text-gray-100 mb-3">Activity Metrics</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Total Obligations Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg p-4 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-300 text-sm font-semibold">Total Obligations Created</p>
                                <p class="text-3xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($totalObligationCount) }}</p>
                            </div>
                            <div class="text-5xl text-blue-200 dark:text-blue-700">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Purchase Orders Card -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 rounded-lg p-4 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-300 text-sm font-semibold">Total Purchase Orders Created</p>
                                <p class="text-3xl font-bold text-green-700 dark:text-green-300">{{ number_format($totalPurchaseOrderCount) }}</p>
                            </div>
                            <div class="text-5xl text-green-200 dark:text-green-700">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Disbursements Card -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900 dark:to-red-800 rounded-lg p-4 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-300 text-sm font-semibold">Total Disbursements Created</p>
                                <p class="text-3xl font-bold text-red-700 dark:text-red-300">{{ number_format($totalDisbursementCount) }}</p>
                            </div>
                            <div class="text-5xl text-red-200 dark:text-red-700">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Average Obligation Count Per Day Card -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 rounded-lg p-4 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-300 text-sm font-semibold">Average Obligations Created/Day</p>
                                <p class="text-3xl font-bold text-purple-700 dark:text-purple-300">{{ number_format($averageObligationCountPerDay, 2) }}</p>
                            </div>
                            <div class="text-5xl text-purple-200 dark:text-purple-700">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Average Disbursement Count Per Day Card -->
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900 dark:to-orange-800 rounded-lg p-4 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 dark:text-gray-300 text-sm font-semibold">Average Disbursements Created/Day</p>
                                <p class="text-3xl font-bold text-orange-700 dark:text-orange-300">{{ number_format($averageDisbursementCountPerDay, 2) }}</p>
                            </div>
                            <div class="text-5xl text-orange-200 dark:text-orange-700">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700 mb-6">

            <!-- 2. Charts Row -->
            <div class="mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Obligation Distribution by Amount Range (Histogram) -->
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Obligation Distribution by Amount Range</h4>
                        <div id="accountObligationHistogram" class="h-64"></div>
                    </div>

                    <!-- Obligations by Quarter (Line Chart) -->
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Obligations Created by Quarter</h4>
                        <div id="accountObligationsByQuarter" class="h-64"></div>
                    </div>
                </div>
            </div>

            <!-- 3. Obligation Distribution Table -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Obligation Amount Ranges Breakdown</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-200 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 border-l-4 border-l-gray-500">Amount Range</th>
                                <th class="px-4 py-2 text-center">Count</th>
                                <th class="px-4 py-2 text-center">Percentage</th>
                                <th class="px-4 py-2 text-center">Visual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalRange = array_sum(array_column($obligationRanges, 'count')); @endphp
                            @foreach($obligationRanges as $range)
                            @php $percentage = $totalRange > 0 ? ($range['count'] / $totalRange) * 100 : 0; @endphp
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold border-l-4 border-l-gray-500">{{ $range['label'] }}</td>
                                <td class="px-4 py-2 text-center font-semibold text-blue-600 dark:text-blue-400">{{ $range['count'] }}</td>
                                <td class="px-4 py-2 text-center text-gray-700 dark:text-gray-300">{{ number_format($percentage, 1) }}%</td>
                                <td class="px-4 py-2">
                                    <div class="w-full bg-gray-300 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4 mb-4 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <label for="dashboardTable" class="ml-4 block text-md font-semibold text-blue-800 dark:text-blue-400">
                    {{ $officeAllotmentClasses->offices->office_name ?? 'Office N/A' }} -
                    {{ $officeAllotmentClasses->allotmentClass->description ?? 'Class N/A' }} Accounts
                </label>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400 mb-2 sm:hidden">
                <i class="fas fa-arrows-left-right"></i> Swipe left/right to see more columns. The first column stays pinned.
            </div>
            <div class="overflow-x-auto max-h-[60vh] sm:max-h-[720px] border border-gray-300 dark:border-gray-700 rounded-lg">
                <table id="accountsTable" class="w-full text-[11px] text-gray-700 dark:text-gray-300 text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-200 border-t-2 border-b-2 border-gray-700">
                        <tr>
                            <th class="sticky left-0 z-20 px-2 py-2 w-[200px] text-center bg-gray-200 dark:bg-gray-900 border-r-2 border-gray-400 dark:border-gray-600 border-l-4 border-l-gray-500">Programs</th>
                            <th class="px-2 py-2 w-[150px] text-center">Account Code</th>
                            <th class="px-2 py-2 w-[200px] text-center">Description</th>
                            @role('Disbursement|Administrator|Developer|Obligation')
                            <th class="px-2 py-2 w-[100px] text-center">FPP Code</th>
                            @endrole
                            <th class="px-2 py-2 w-[100px] text-center">Approved Appropriations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Supplemental Appropriations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Reversions</th>
                            <th class="px-2 py-2 w-[100px] text-center">Realignments</th>
                            <th class="px-2 py-2 w-[100px] text-center">Authorized Appropriations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Allotments</th>
                            @role('Disbursement|Administrator|Developer|Obligation')
                            <th class="px-2 py-2 w-[100px] text-center">For Later Release</th>
                            @endrole
                            <th class="px-2 py-2 w-[100px] text-center">Obligations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Authorized Approp. Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Appropriations Utilization</th>
                            <th class="px-2 py-2 w-[100px] text-center">Allotments Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Allotments Utilization</th>
                            @role('Disbursement|Administrator|Developer|Guest')
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements</th>
                            <th class="px-2 py-2 w-[100px] text-center">Obligations Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements / Oblgations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements / Approp.</th>
                            @endrole
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($officeAllotmentClasses->appropriations as $appropriation)
                        <tr data-appropriations="{{ $appropriation->appropriation_sum }}"
                            data-supplementals="{{ $appropriation->supplemental_sum }}"
                            data-reversions="{{ $appropriation->reversion_sum }}"
                            data-realignments="{{ $appropriation->realignments_sum }}"
                            data-authorized-appropriations="{{ $appropriation->authorized_appropriations }}"
                            data-allotments="{{ $appropriation->allotments_sum }}"
                            data-for-later-release="{{ $appropriation->for_later_release }}"
                            data-obligations="{{ $appropriation->obligations_sum }}"
                            data-balance-appropriations="{{ $appropriation->balance_appropriations }}"
                            data-balance-allotments="{{ $appropriation->balance_allotments }}"
                            data-disbursements="{{ $appropriation->disbursements }}"
                            data-disbursement-balance="{{ $appropriation->disbursement_balance }}"
                            data-appropriation-accomplishment="{{ $appropriation->appropriation_accomplishment }}"
                            data-allotment-accomplishment="{{ $appropriation->allotment_accomplishment }}"
                            data-disbursements-to-obligations="{{ $appropriation->disbursements_to_obligations }}"
                            data-disbursements-to-appropriations="{{ $appropriation->disbursements_to_appropriations }}"
                            data-account-code="{{ $appropriation->account_code }}"
                            data-description="{{ $appropriation->description }}"
                            data-appropriation-id="{{ $appropriation->id }}"
                            class="group {{ $loop->odd ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900/40' }} border dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
                            <td class="sticky left-0 z-[1] px-1 py-2 text-center text-gray-900 dark:text-white font-bold {{ $loop->odd ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900/40' }} group-hover:bg-gray-100 dark:group-hover:bg-gray-600 border-r-2 border-gray-300 dark:border-gray-600 border-l-4 border-l-gray-500">{{ $appropriation->programs }}</td>
                            <td class="px-1 py-2 text-center text-gray-900 dark:text-white font-bold">{{ $appropriation->account_code }}</td>
                            <td class="px-1 py-2 text-center text-gray-900 dark:text-white font-bold">{{ $appropriation->description }}</td>
                            @role('Disbursement|Administrator|Developer|Obligation')
                            <td class="px-1 py-2 text-center">{{ $appropriation->fpp_code }}</td>
                            @endrole
                            <td class="px-1 py-2 text-right text-blue-700 dark:text-blue-300 font-semibold">{{ number_format($appropriation->appropriation_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right {{ $appropriation->supplemental_sum != 0 ? 'text-green-600 font-semibold' : 'text-gray-600 dark:text-gray-300' }}">
                                {{ number_format($appropriation->supplemental_sum, 2) }}
                            </td>
                            <td class="px-1 py-2 text-right {{ $appropriation->reversion_sum != 0 ? 'text-red-600 font-semibold' : 'text-gray-600 dark:text-gray-300' }}">
                                {{ number_format($appropriation->reversion_sum, 2) }}
                            </td>
                            <td class="px-1 py-2 text-right 
                                    {{ $appropriation->realignments_sum < 0 ? 'text-red-600 font-semibold' : '' }} 
                                    {{ $appropriation->realignments_sum > 0 ? 'text-green-600 font-semibold' : '' }} 
                                    {{ $appropriation->realignments_sum == 0 ? 'text-gray-600 dark:text-gray-300' : '' }}">
                                {{ number_format($appropriation->realignments_sum, 2) }}
                            </td>
                            <td class="px-1 py-2 text-right text-blue-700 dark:text-blue-300 font-semibold">{{ number_format($appropriation->authorized_appropriations, 2) }}</td>
                            <td class="px-1 py-2 text-right text-blue-700 dark:text-blue-300 font-semibold">{{ number_format($appropriation->allotments_sum, 2) }}</td>
                            @role('Disbursement|Administrator|Developer|Obligation')
                            <td class="px-1 py-2 text-right">{{ number_format($appropriation->for_later_release, 2) }}</td>
                            @endrole
                            <td class="px-1 py-2 text-right">{{ number_format($appropriation->obligations_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right text-blue-700 dark:text-blue-300 font-semibold">{{ number_format($appropriation->balance_appropriations, 2) }}</td>
                            <!-- For Appropriations Utilization column -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($appropriation->appropriation_accomplishment, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($appropriation->appropriation_accomplishment, 2) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="edge-tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Appropriation Utilization</div>
                                        <div>{{ number_format($appropriation->appropriation_accomplishment, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($appropriation->obligations_sum, 2) }} / {{ number_format($appropriation->authorized_appropriations, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-1 py-2 text-right text-blue-700 dark:text-blue-300 font-semibold">{{ number_format($appropriation->balance_allotments, 2) }}</td>
                            <!-- For Allotments Utilization column -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($appropriation->allotment_accomplishment, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($appropriation->allotment_accomplishment, 2) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="edge-tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Allotments Utilization</div>
                                        <div>{{ number_format($appropriation->allotment_accomplishment, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($appropriation->obligations_sum, 2) }} / {{ number_format($appropriation->allotments_sum, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            @role('Disbursement|Administrator|Developer|Guest')
                            <td class="px-1 py-2 text-right">{{ number_format($appropriation->disbursements, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($appropriation->disbursement_balance, 2) }}</td>
                            <!-- For Disbursements / Obligations column -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($appropriation->disbursements_to_obligations, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($appropriation->disbursements_to_obligations, 2) }}%</span>
                                    </div>
                                    <!-- Tooltip: opens below the value (not .edge-tooltip — that class's JS assumes
                                         centered positioning, but this one is right-anchored) so it stacks clear
                                         of the Disbursements / Approp. tooltip beside it instead of overlapping -->
                                    <div class="absolute top-full right-0 mt-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50 text-left">
                                        <!-- Arrow -->
                                        <div class="absolute bottom-full right-4 border-4 border-transparent border-b-gray-900"></div>
                                        <div class="font-semibold">Disbursements / Obligations</div>
                                        <div>{{ number_format($appropriation->disbursements_to_obligations, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300 mt-1">Disbursements: {{ number_format($appropriation->disbursements, 2) }}</div>
                                        <div class="text-[10px] text-gray-300">Obligations: {{ number_format($appropriation->obligations_sum, 2) }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- For Disbursements / Appropriations column -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($appropriation->disbursements_to_appropriations, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($appropriation->disbursements_to_appropriations, 2) }}%</span>
                                    </div>
                                    <!-- Tooltip: right-anchored, not .edge-tooltip (that class's JS assumes centered positioning) -->
                                    <div class="absolute bottom-full right-0 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50 text-left">
                                        <div class="font-semibold">Disbursements / Appropriations</div>
                                        <div>{{ number_format($appropriation->disbursements_to_appropriations, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300 mt-1">Disbursements: {{ number_format($appropriation->disbursements, 2) }}</div>
                                        <div class="text-[10px] text-gray-300">Authorized Approp.: {{ number_format($appropriation->authorized_appropriations, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full right-4 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            @endrole
                        </tr>
                        @empty
                        <tr class="bg-white border dark:bg-gray-800 dark:border-gray-700">
                            <td colspan="20" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                No accounts and appropriations found for this Office Allotment Class.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
