<x-app-layout>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Current Balances > Accounts') }} |
                <span class="text-blue-800 dark:text-blue-400">
                    {{ $officeAllotmentClasses->offices->office_name ?? 'Office N/A' }} -
                    {{ $officeAllotmentClasses->class ?? 'Class N/A' }} (CY
                    {{ $officeAllotmentClasses->year ?? 'Year N/A' }})
                </span>
            </h2>

            <!-- Right: Breadcrumb Navigation -->
            @if(isset($breadcrumb))
            <nav class="text-xs text-gray-600 dark:text-gray-300" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex items-center space-x-1 rtl:space-x-reverse">
                    @foreach ($breadcrumb as $index => $item)
                    <li>
                        @if (!empty($item['route']) && $index < count($breadcrumb) - 1)
                            <a href="{{ $item['route'] }}" class="text-gray-600 hover:underline dark:text-blue-400">
                            {{ $item['label'] }}
                            </a>
                            <span class="mx-2">/</span>
                            @else
                            <span class="text-gray-500 dark:text-gray-400">{{ $item['label'] }}</span>
                            @endif
                    </li>
                    @endforeach
                </ol>
            </nav>
            @endif
        </div>
    </x-slot>

    <!-- Success Alert Toast - Right Side -->
    @if(session('status'))
    @php
    $status = session('status');
    @endphp
    <div id="successAlert" class="fixed top-6 right-6 max-w-2xl z-50 animate-slide-in">
        <div class="bg-green-50 border-2 border-green-300 text-green-800 px-6 py-5 rounded-xl shadow-2xl dark:bg-green-900 dark:border-green-600 dark:text-green-100 flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400 mt-1 flex-shrink-0 text-2xl"></i>
                <div class="flex-1">
                    <p class="font-semibold text-base leading-relaxed">{!! $status['message'] ?? $status !!}</p>
                </div>
            </div>
            <button type="button" class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 flex-shrink-0" onclick="closeSuccessAlert()">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
    </div>
    @endif

    <div class="bg-white p-4 rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-center">
            <!-- Search Input -->
                <div class="flex items-center space-x-2 lg:col-span-3">
                    <x-form.input type="text" name="search" id="searchInput" value="{{ session('search') ?? request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>
        </div>
    </div>

    {{-- Analytics & Insights Panel (for Accounts) --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 mb-4">
        <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                Insights & Analytics
            </h3>
            <button onclick="toggleWidget('accountAnalyticsPanel')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-chevron-down" id="accountAnalyticsPanelToggle"></i>
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
                                <th class="px-4 py-2">Amount Range</th>
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
                                <td class="px-4 py-2 font-semibold">{{ $range['label'] }}</td>
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
            <div class="overflow-x-auto max-h-[720px] border border-gray-300 dark:border-gray-700 rounded-lg">
                <table id="accountsTable" class="w-full text-[11px] text-gray-700 dark:text-gray-300 text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-200 border-t-2 border-b-2 border-gray-700">
                        <tr>
                            <th class="px-2 py-2 w-[200px] text-center">Programs</th>
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
                            @role('Disbursement|Administrator|Developer')
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
                            class="bg-white border dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-1 py-2 text-center text-gray-900 dark:text-white font-bold">{{ $appropriation->programs }}</td>
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
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($appropriation->appropriation_accomplishment, 1) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
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
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($appropriation->allotment_accomplishment, 1) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Allotments Utilization</div>
                                        <div>{{ number_format($appropriation->allotment_accomplishment, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($appropriation->obligations_sum, 2) }} / {{ number_format($appropriation->allotments_sum, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            @role('Disbursement|Administrator|Developer')
                            <td class="px-1 py-2 text-right">{{ number_format($appropriation->disbursements, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($appropriation->disbursement_balance, 2) }}</td>
                            <!-- For Disbursements / Obligations column -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($appropriation->disbursements_to_obligations, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($appropriation->disbursements_to_obligations, 1) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Disbursements / Obligations</div>
                                        <div>{{ number_format($appropriation->disbursements_to_obligations, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($appropriation->disbursements, 2) }} / {{ number_format($appropriation->obligations_sum, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
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
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($appropriation->disbursements_to_appropriations, 1) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Disbursements / Appropriations</div>
                                        <div>{{ number_format($appropriation->disbursements_to_appropriations, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($appropriation->disbursements, 2) }} / {{ number_format($appropriation->authorized_appropriations, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
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

    {{-- Dashboard Cards Row --}}
    <div class="mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div data-card="approved_appropriations" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-file-circle-check text-blue-600 dark:text-blue-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Approved Appropriations
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->appropriations_sum, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="supplemental_appropriations" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-plus-circle text-purple-600 dark:text-purple-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Supplemental Appropriations
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold break-words
                        {{ $officeAllotmentClasses->supplemental_sum > 0 
                            ? 'text-green-600 dark:text-green-400' 
                            : 'text-gray-800 dark:text-gray-100' }}">
                        {{ number_format($officeAllotmentClasses->supplemental_sum, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="reversions" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-red-100 dark:bg-red-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-undo-alt text-red-600 dark:text-red-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Reversions
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold break-words
                        {{ $officeAllotmentClasses->reversion_sum > 0 
                            ? 'text-red-600 dark:text-red-400' 
                            : 'text-gray-800 dark:text-gray-100' }}">
                        {{ number_format($officeAllotmentClasses->reversion_sum, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="realignments" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-orange-100 dark:bg-orange-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-random text-orange-600 dark:text-orange-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Realignments
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold break-words
                        {{ $officeAllotmentClasses->realignments_sum < 0 
                            ? 'text-red-600 dark:text-red-400' 
                            : ($officeAllotmentClasses->realignments_sum > 0 
                                ? 'text-green-600 dark:text-green-400' 
                                : 'text-gray-800 dark:text-gray-100') }}">
                        {{ number_format($officeAllotmentClasses->realignments_sum, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="authorized_appropriations" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-sky-100 dark:bg-sky-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-file-signature text-sky-600 dark:text-sky-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Authorized Appropriations
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->authorized_appropriations, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="for_later_release" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-fuchsia-100 dark:bg-fuchsia-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-lock text-fuchsia-600 dark:text-fuchsia-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Unreleased Appropriations
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->for_later_release, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="allotments" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-layer-group text-green-600 dark:text-green-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Allotments
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->allotments_sum, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="obligations" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-tasks text-yellow-600 dark:text-yellow-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Obligations
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->obligations_sum, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="balance_appropriations" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-cyan-100 dark:bg-cyan-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-credit-card text-cyan-600 dark:text-cyan-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Authorized Appropriations Balance
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->balance_appropriations, 2) }}
                    </div>
                </div>
            </div>
            <!-- Authorized Appropriations Utilization Card with Circular Progress -->
            <div data-card="appropriation_accomplishment" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center justify-between transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer relative group">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-full p-2 sm:p-3">
                        <i class="fas fa-percent text-indigo-600 dark:text-indigo-300 text-lg sm:text-xl md:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                        <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                            Authorized Appropriations Utilization
                        </div>
                        <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                            {{ number_format($officeAllotmentClasses->appropriation_accomplishment, 2) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Circular Progress -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="circular-progress" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="circular-progress-bg" cx="30" cy="30" r="24" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle class="circular-progress-bar" cx="30" cy="30" r="24" fill="none" stroke="#6366f1" stroke-width="6"
                                stroke-dasharray="{{ min($officeAllotmentClasses->appropriation_accomplishment, 100) * 1.507 }} 150.7"
                                stroke-linecap="round"
                                transform="rotate(-90 30 30)"
                                data-percentage="{{ $officeAllotmentClasses->appropriation_accomplishment }}"/>
                        <text x="30" y="35" text-anchor="middle" class="text-xs font-bold fill-gray-800 dark:fill-gray-100">
                            {{ number_format(min($officeAllotmentClasses->appropriation_accomplishment, 100), 0) }}%
                        </text>
                    </svg>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="font-semibold">Appropriation Utilization</div>
                    <div class="text-indigo-300">{{ number_format($officeAllotmentClasses->appropriation_accomplishment, 2) }}%</div>
                    <div class="text-gray-300 text-[10px] mt-1">Obligations: <span class="card-tooltip-obligations">{{ number_format($officeAllotmentClasses->obligations_sum, 2) }}</span></div>
                    <div class="text-gray-300 text-[10px]">Authorized Approp.: <span class="card-tooltip-auth-approp">{{ number_format($officeAllotmentClasses->authorized_appropriations, 2) }}</span></div>
                    <!-- Arrow -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                </div>
            </div>
            <div data-card="balance_allotments" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-pink-100 dark:bg-pink-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-stream text-pink-600 dark:text-pink-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Allotments Balance
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->balance_allotments, 2) }}
                    </div>
                </div>
            </div>
            <!-- Allotments Utilization Card with Circular Progress -->
            <div data-card="allotment_accomplishment" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center justify-between transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer relative group">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 bg-teal-100 dark:bg-teal-900 rounded-full p-2 sm:p-3">
                        <i class="fas fa-percentage text-teal-600 dark:text-teal-300 text-lg sm:text-xl md:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                        <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Allotments Utilization
                        </div>
                        <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                            {{ number_format($officeAllotmentClasses->allotment_accomplishment, 2) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Circular Progress -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="circular-progress" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="circular-progress-bg" cx="30" cy="30" r="24" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle class="circular-progress-bar" cx="30" cy="30" r="24" fill="none" stroke="#14b8a6" stroke-width="6"
                                stroke-dasharray="{{ min($officeAllotmentClasses->allotment_accomplishment, 100) * 1.507 }} 150.7"
                                stroke-linecap="round"
                                transform="rotate(-90 30 30)"
                                data-percentage="{{ $officeAllotmentClasses->allotment_accomplishment }}"/>
                        <text x="30" y="35" text-anchor="middle" class="text-xs font-bold fill-gray-800 dark:fill-gray-100">
                            {{ number_format(min($officeAllotmentClasses->allotment_accomplishment, 100), 0) }}%
                        </text>
                    </svg>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="font-semibold">Allotments Utilization</div>
                    <div class="text-teal-300">{{ number_format($officeAllotmentClasses->allotment_accomplishment, 2) }}%</div>
                    <div class="text-gray-300 text-[10px] mt-1">Obligations: <span class="card-tooltip-obligations-allot">{{ number_format($officeAllotmentClasses->obligations_sum, 2) }}</span></div>
                    <div class="text-gray-300 text-[10px]">Allotments: <span class="card-tooltip-allotments">{{ number_format($officeAllotmentClasses->allotments_sum, 2) }}</span></div>
                    <!-- Arrow -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                </div>
            </div>
            @role('Disbursement|Administrator|Developer')
            <div data-card="disbursements" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-emerald-100 dark:bg-emerald-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-tasks text-emerald-600 dark:text-emerald-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Disbursements
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->disbursements_sum, 2) }}
                    </div>
                </div>
            </div>
            <div data-card="disbursement_balance" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="flex-shrink-0 bg-rose-100 dark:bg-rose-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-shopping-basket text-rose-600 dark:text-rose-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Obligations Balance
                    </div>
                    <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                        {{ number_format($officeAllotmentClasses->disbursement_balance, 2) }}
                    </div>
                </div>
            </div>
            <!-- Disbursements / Obligations Card with Circular Progress -->
            <div data-card="disbursements_to_obligations" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center justify-between transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer relative group">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 bg-lime-100 dark:bg-lime-900 rounded-full p-2 sm:p-3">
                        <i class="fas fa-percentage text-lime-600 dark:text-lime-300 text-lg sm:text-xl md:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                        <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                            Disbursements / Obligations
                        </div>
                        <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                            {{ number_format($officeAllotmentClasses->disbursements_to_obligations, 2) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Circular Progress -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="circular-progress" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="circular-progress-bg" cx="30" cy="30" r="24" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle class="circular-progress-bar" cx="30" cy="30" r="24" fill="none" stroke="#84cc16" stroke-width="6"
                                stroke-dasharray="{{ min($officeAllotmentClasses->disbursements_to_obligations, 100) * 1.507 }} 150.7"
                                stroke-linecap="round"
                                transform="rotate(-90 30 30)"
                                data-percentage="{{ $officeAllotmentClasses->disbursements_to_obligations }}"/>
                        <text x="30" y="35" text-anchor="middle" class="text-xs font-bold fill-gray-800 dark:fill-gray-100">
                            {{ number_format(min($officeAllotmentClasses->disbursements_to_obligations, 100), 0) }}%
                        </text>
                    </svg>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="font-semibold">Disbursements / Obligations</div>
                    <div class="text-lime-300">{{ number_format($officeAllotmentClasses->disbursements_to_obligations, 2) }}%</div>
                    <div class="text-gray-300 text-[10px] mt-1">Disbursements: <span class="card-tooltip-disbursements-ob">{{ number_format($officeAllotmentClasses->disbursements_sum, 2) }}</span></div>
                    <div class="text-gray-300 text-[10px]">Obligations: <span class="card-tooltip-obligations-disb">{{ number_format($officeAllotmentClasses->obligations_sum, 2) }}</span></div>
                    <!-- Arrow -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                </div>
            </div>
            <!-- Disbursements / Authorized Appropriations Card with Circular Progress -->
            <div data-card="disbursements_to_appropriations" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center justify-between transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer relative group">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900 rounded-full p-2 sm:p-3">
                        <i class="fas fa-percentage text-amber-600 dark:text-amber-300 text-lg sm:text-xl md:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                        <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                            Disbursements / Authorized Appropriations
                        </div>
                        <div class="card-value text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words">
                            {{ number_format($officeAllotmentClasses->disbursements_to_appropriations, 2) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Circular Progress -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="circular-progress" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="circular-progress-bg" cx="30" cy="30" r="24" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle class="circular-progress-bar" cx="30" cy="30" r="24" fill="none" stroke="#f59e0b" stroke-width="6"
                                stroke-dasharray="{{ min($officeAllotmentClasses->disbursements_to_appropriations, 100) * 1.507 }} 150.7"
                                stroke-linecap="round"
                                transform="rotate(-90 30 30)"
                                data-percentage="{{ $officeAllotmentClasses->disbursements_to_appropriations }}"/>
                        <text x="30" y="35" text-anchor="middle" class="text-xs font-bold fill-gray-800 dark:fill-gray-100">
                            {{ number_format(min($officeAllotmentClasses->disbursements_to_appropriations, 100), 0) }}%
                        </text>
                    </svg>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="font-semibold">Disbursements / Appropriations</div>
                    <div class="text-amber-300">{{ number_format($officeAllotmentClasses->disbursements_to_appropriations, 2) }}%</div>
                    <div class="text-gray-300 text-[10px] mt-1">Disbursements: <span class="card-tooltip-disbursements-ap">{{ number_format($officeAllotmentClasses->disbursements_sum, 2) }}</span></div>
                    <div class="text-gray-300 text-[10px]">Authorized Approp.: <span class="card-tooltip-auth-approp-disb">{{ number_format($officeAllotmentClasses->authorized_appropriations, 2) }}</span></div>
                    <!-- Arrow -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                </div>
            </div>
            @endrole
        </div>
    </div>

    <!-- Right-Click Context Menu for Accounts Table -->
    <div id="accountContextMenu" class="hidden fixed text-xs bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg shadow-2xl z-[10000] text-blue-900 dark:text-blue-100 min-w-max border-2 border-blue-400 dark:border-blue-600">
        @role('Administrator|Developer|Obligation')
        <a href="#" id="contextObligate" class="flex items-center px-4 py-2 text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-t-lg transition-colors duration-150">
            <i class="fas fa-plus-circle mr-2 text-blue-600 dark:text-blue-400"></i> Obligate
        </a>
        @endrole
        <a href="#" onclick="event.preventDefault(); handleAccountMenuOption('obligations')" class="block px-4 py-2 text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-b-lg transition-colors duration-150 cursor-pointer">
            <i class="fas fa-list mr-2 text-blue-600 dark:text-blue-400"></i>Obligations
        </a>
    </div>

    <!-- Obligations Modal for Accounts -->
    <div id="accountObligationsModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-screen-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal Header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-b-2 border-blue-200 dark:border-blue-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-list-check text-blue-600 dark:text-blue-300 text-xl"></i>
                    <div>
                        <h3 class="text-lg leading-6 font-semibold text-blue-900 dark:text-blue-100">
                            Obligations
                        </h3>
                        <span id="accountObligationsHeaderInfo" class="text-xs text-blue-700 dark:text-blue-300"></span>
                    </div>
                </div>
                <button onclick="closeAccountObligationsModal()" class="text-blue-600 dark:text-blue-300 hover:text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Search and Total Records Section -->
            <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <div class="flex items-center space-x-3">
                    <!-- Total Records -->
                    <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 flex-shrink-0">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="accountObligationsTotalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">0</span>
                    </div>
                    <!-- Date Range Filter -->
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex-shrink-0">Date Range:</span>
                    <input 
                        type="date" 
                        id="accountObligationsDateFrom" 
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                        onchange="filterObligationsTable(document.getElementById('accountObligationsSearchInput').value, 'accounts')"
                    >
                    <span class="text-gray-600 dark:text-gray-400 text-xs">to</span>
                    <input 
                        type="date" 
                        id="accountObligationsDateTo" 
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                        onchange="filterObligationsTable(document.getElementById('accountObligationsSearchInput').value, 'accounts')"
                    >
                    <button 
                        onclick="document.getElementById('accountObligationsDateFrom').value = ''; document.getElementById('accountObligationsDateTo').value = ''; filterObligationsTable(document.getElementById('accountObligationsSearchInput').value, 'accounts')"
                        class="px-2 py-2 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors flex-shrink-0"
                        title="Clear date range"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                    <!-- Search Input -->
                    <input 
                        type="text" 
                        id="accountObligationsSearchInput" 
                        placeholder="Search obligations..." 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                        oninput="filterObligationsTable(this.value, 'accounts')"
                    >
                </div>
            </div>

            <!-- Modal Body -->
            <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)] px-6 py-4">
                <div id="accountObligationsContent" class="space-y-4">
                    <!-- Loading spinner -->
                    <div id="accountObligationsLoading" class="flex justify-center items-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg flex-shrink-0">
                <button onclick="printObligationsModal('accounts')" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-print mr-2"></i>
                    Print
                </button>
                <button onclick="closeAccountObligationsModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Context Menu for Account Obligations -->
     @role('Administrator|Developer|Obligation')
    <div id="accountObligationsContextMenu" class="hidden fixed w-48 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 border-2 border-blue-400 dark:border-blue-600 rounded-lg shadow-2xl" style="z-index: 10001; position: fixed;">
        <button id="contextAccountObligationDetails" class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-t-lg transition-colors duration-150">
            <i class="fas fa-eye mr-2 text-blue-600 dark:text-blue-400"></i>View Details
        </button>
        <button id="contextAccountObligationEdit" class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-edit mr-2 text-blue-600 dark:text-blue-400"></i>Edit Obligation
        </button>
        <button id="contextAccountObligationAdjustment" class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-file-edit mr-2 text-blue-600 dark:text-blue-400"></i>Add Adjustment
        </button>
        <button id="contextAccountObligationPO" class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-file-invoice mr-2 text-blue-600 dark:text-blue-400"></i>Add Purchase Order
        </button>
        @can('cancel obligations')
        <button id="contextAccountObligationCancellation" class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-window-close mr-2 text-blue-600 dark:text-blue-400"></i>Cancellation
        </button>
        @endcan
        <button id="contextAccountObligationHistory" class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-b-lg transition-colors duration-150">
            <i class="fas fa-history mr-2 text-blue-600 dark:text-blue-400"></i>Status/History
        </button>
    </div>
    @endrole

    <style>
    /* Circular progress animation */
    .circular-progress-bar {
        transition: stroke-dasharray 0.6s ease-in-out;
        opacity: 0;
        animation: progressBarFadeIn 0.8s ease-out forwards;
    }

    @keyframes progressBarFadeIn {
        from {
            opacity: 0;
            stroke-dasharray: 0 150.7;
        }
        to {
            opacity: 1;
        }
    }

    /* Dark mode stroke color adjustments */
    .dark .circular-progress-bg {
        stroke: #374151;
    }

    /* Linear progress bar fade animation */
    .progress-bar-fade {
        animation: linearProgressFadeIn 0.7s ease-out forwards;
    }

    @keyframes linearProgressFadeIn {
        from {
            opacity: 0;
            width: 0 !important;
        }
        to {
            opacity: 1;
        }
    }

    /* Column highlight animation */
    @keyframes columnHighlight {
        0% {
            background-color: rgba(59, 130, 246, 0.3);
        }
        100% {
            background-color: transparent;
        }
    }

    .highlight-column {
        animation: columnHighlight 1.5s ease-out forwards;
    }

    /* Row highlight when context menu is open */
    #accountsTable tbody tr.context-menu-active {
        background-color: rgba(59, 130, 246, 0.15);
        transition: background-color 0.2s ease-in-out;
    }

    .dark #accountsTable tbody tr.context-menu-active {
        background-color: rgba(59, 130, 246, 0.25);
    }

    #accountObligationsModal tbody tr.context-menu-active {
        background-color: rgba(59, 130, 246, 0.15);
        transition: background-color 0.2s ease-in-out;
    }

    .dark #accountObligationsModal tbody tr.context-menu-active {
        background-color: rgba(59, 130, 246, 0.25);
    }
    </style>

    <script>
        // Close success alert
        function closeSuccessAlert() {
            const alert = document.getElementById('successAlert');
            if (alert) {
                alert.style.display = 'none';
            }
        }

        // Auto-close success alert after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('successAlert');
            if (alert) {
                setTimeout(function() {
                    closeSuccessAlert();
                }, 5000);
            }
        });

        // Tooltip functions
        function showTooltip(element) {
            const tooltip = element.querySelector('.tooltip-box');
            if (tooltip) {
                tooltip.style.display = 'block';
            }
        }

        function hideTooltip(element) {
            const tooltip = element.querySelector('.tooltip-box');
            if (tooltip) {
                tooltip.style.display = 'none';
            }
        }

        // Fixed color assignments for common accounts
        const fixedColorAssignments = {
            '5010101000': { color: 'bg-blue-500', hover: 'hover:bg-blue-600' },
            '5010201000': { color: 'bg-green-500', hover: 'hover:bg-green-600' },
            '5010301000': { color: 'bg-cyan-500', hover: 'hover:bg-cyan-600' },
            '5010402000': { color: 'bg-purple-500', hover: 'hover:bg-purple-600' },
            '5010499000': { color: 'bg-orange-500', hover: 'hover:bg-orange-600' },
            '5020101000': { color: 'bg-red-500', hover: 'hover:bg-red-600' },
            '5020321000': { color: 'bg-violet-500', hover: 'hover:bg-violet-600' },
        };

        const fallbackColorPalette = [
            { color: 'bg-pink-600', hover: 'hover:bg-pink-700' },
            { color: 'bg-indigo-600', hover: 'hover:bg-indigo-700' },
            { color: 'bg-teal-600', hover: 'hover:bg-teal-700' },
            { color: 'bg-lime-600', hover: 'hover:bg-lime-700' },
            { color: 'bg-amber-600', hover: 'hover:bg-amber-700' },
            { color: 'bg-rose-600', hover: 'hover:bg-rose-700' },
        ];

        function hashCode(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            return Math.abs(hash);
        }

        function getColorForAccount(accountCode) {
            if (fixedColorAssignments[accountCode]) {
                return fixedColorAssignments[accountCode].color;
            }
            const index = hashCode(accountCode) % fallbackColorPalette.length;
            return fallbackColorPalette[index].color;
        }

        function getHoverColorForAccount(accountCode) {
            if (fixedColorAssignments[accountCode]) {
                return fixedColorAssignments[accountCode].hover;
            }
            const index = hashCode(accountCode) % fallbackColorPalette.length;
            return fallbackColorPalette[index].hover;
        }

        function updateGraph() {
            const rows = document.querySelectorAll('#accountsTable tbody tr');
            const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
            
            const accountData = {};
            let totalAmount = 0;
            
            visibleRows.forEach(row => {
                const authorizedApprop = parseFloat(row.getAttribute('data-authorized-appropriations')) || 0;
                const accountCode = row.getAttribute('data-account-code');
                const description = row.cells[2].textContent.trim();
                
                if (!accountData[accountCode]) {
                    accountData[accountCode] = {
                        total: 0,
                        code: accountCode,
                        description: description
                    };
                }
                accountData[accountCode].total += authorizedApprop;
                totalAmount += authorizedApprop;
            });
            
            const sortedAccounts = Object.values(accountData).sort((a, b) => b.total - a.total);
            
            const stackedBarContainer = document.getElementById('stackedBarContainer');
            if (!stackedBarContainer) return;
            
            let barHTML = '<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 overflow-visible flex relative">';
            
            if (sortedAccounts.length === 0 || totalAmount === 0) {
                barHTML = `
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 flex items-center justify-center">
                        <span class="text-gray-500 dark:text-gray-400 text-sm italic">No data available</span>
                    </div>
                `;
            } else {
                sortedAccounts.forEach(accountItem => {
                    const percentage = totalAmount > 0 ? (accountItem.total / totalAmount) * 100 : 0;
                    const color = getColorForAccount(accountItem.code);
                    const hoverColor = getHoverColorForAccount(accountItem.code);
                    const truncatedDesc = accountItem.description.length > 25 
                        ? accountItem.description.substring(0, 25) + '...' 
                        : accountItem.description;
                    
                    barHTML += `
                        <div 
                            class="${color} ${hoverColor} h-8 transition-all duration-200 ease-out flex items-center justify-center relative cursor-pointer"
                            style="width: ${percentage}%"
                            onmouseenter="showTooltip(this)"
                            onmouseleave="hideTooltip(this)"
                        >
                            ${percentage > 8 ? `<span class="text-white text-xs font-semibold px-1 text-center truncate pointer-events-none">${truncatedDesc}</span>` : ''}
                            
                            <div class="tooltip-box absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap shadow-xl" style="display: none; z-index: 9999;">
                                <div class="font-semibold">${accountItem.code}</div>
                                <div class="text-[10px] max-w-xs truncate">${accountItem.description}</div>
                                <div>${percentage.toFixed(2)}%</div>
                                <div>${accountItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                    `;
                });
            }
            
            barHTML += '</div>';
            stackedBarContainer.innerHTML = barHTML;
            
            updateLegend(sortedAccounts, totalAmount);
        }

        function updateLegend(sortedAccounts, totalAmount) {
            const legendContainer = document.getElementById('graphLegend');
            if (!legendContainer) return;
            
            let legendHTML = '';
            const displayAccounts = sortedAccounts.slice(0, 5);
            
            displayAccounts.forEach(accountItem => {
                const percentage = totalAmount > 0 ? (accountItem.total / totalAmount) * 100 : 0;
                const color = getColorForAccount(accountItem.code);
                const truncatedDesc = accountItem.description.length > 40 
                    ? accountItem.description.substring(0, 40) + '...' 
                    : accountItem.description;
                const legendText = `${accountItem.code} - ${truncatedDesc}`;
                const fullText = `${accountItem.code} - ${accountItem.description}`;
                
                legendHTML += `
                    <div class="flex items-center space-x-2 text-xs">
                        <div class="w-4 h-4 ${color} rounded flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-700 dark:text-gray-300 truncate" title="${fullText}">
                                ${legendText}
                            </div>
                            <div class="text-gray-500 dark:text-gray-400">
                                ${percentage.toFixed(1)}%
                            </div>
                            <div class="text-gray-600 dark:text-gray-400 text-[10px]">
                                ${accountItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            if (sortedAccounts.length > 5) {
                legendHTML += `
                    <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 italic">
                        +${sortedAccounts.length - 5} more accounts
                    </div>
                `;
            }
            
            legendContainer.innerHTML = legendHTML;
        }

        const cardConfig = {
            'approved_appropriations': { column: 'data-appropriations' },
            'supplemental_appropriations': { column: 'data-supplementals' },
            'reversions': { column: 'data-reversions' },
            'realignments': { column: 'data-realignments' },
            'authorized_appropriations': { column: 'data-authorized-appropriations' },
            'allotments': { column: 'data-allotments' },
            'for_later_release': { column: 'data-for-later-release' },
            'obligations': { column: 'data-obligations' },
            'balance_appropriations': { column: 'data-balance-appropriations' },
            'balance_allotments': { column: 'data-balance-allotments' },
            'disbursements': { column: 'data-disbursements' },
            'disbursement_balance': { column: 'data-disbursement-balance' },
            'appropriation_accomplishment': { column: 'data-appropriation-accomplishment' },
            'allotment_accomplishment': { column: 'data-allotment-accomplishment' },
            'disbursements_to_obligations': { column: 'data-disbursements-to-obligations' },
            'disbursements_to_appropriations': { column: 'data-disbursements-to-appropriations' }
        };

        function updateCardValues() {
            const rows = document.querySelectorAll('#accountsTable tbody tr');
            const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');

            // Calculate totals from visible rows
            const totals = {};
            for (const [cardKey, config] of Object.entries(cardConfig)) {
                let total = 0;
                visibleRows.forEach(row => {
                    const value = parseFloat(row.getAttribute(config.column)) || 0;
                    total += value;
                });
                totals[cardKey] = total;
            }

            // Calculate percentage cards properly based on base values
            const obligations = totals['obligations'] || 0;
            const authorizedAppropriations = totals['authorized_appropriations'] || 0;
            const allotments = totals['allotments'] || 0;
            const disbursements = totals['disbursements'] || 0;

            // Calculate percentages using correct formulas
            const appropriationAccomplishment = authorizedAppropriations > 0 
                ? (obligations / authorizedAppropriations) * 100 
                : 0;
            
            const allotmentAccomplishment = allotments > 0 
                ? (obligations / allotments) * 100 
                : 0;
            
            const disbursementsToObligations = obligations > 0 
                ? (disbursements / obligations) * 100 
                : 0;
            
            const disbursementsToAppropriations = authorizedAppropriations > 0 
                ? (disbursements / authorizedAppropriations) * 100 
                : 0;

            // Helper function to format numbers
            function formatNumber(num) {
                return num.toLocaleString('en-US', {
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2
                });
            }

            // Update card values
            for (const [cardKey, total] of Object.entries(totals)) {
                const card = document.querySelector(`[data-card="${cardKey}"]`);
                if (card) {
                    const cardValue = card.querySelector('.card-value');
                    const circularProgress = card.querySelector('.circular-progress-bar');
                    const progressText = card.querySelector('text');
                    
                    if (cardValue) {
                        let percentage = 0;
                        
                        // Handle percentage cards specially
                        if (cardKey === 'appropriation_accomplishment') {
                            percentage = appropriationAccomplishment;
                            cardValue.textContent = formatNumber(percentage) + '%';
                            
                            // Update tooltip values
                            const tooltipObl = card.querySelector('.card-tooltip-obligations');
                            const tooltipAuthApprop = card.querySelector('.card-tooltip-auth-approp');
                            if (tooltipObl) tooltipObl.textContent = formatNumber(obligations);
                            if (tooltipAuthApprop) tooltipAuthApprop.textContent = formatNumber(authorizedAppropriations);
                            
                        } else if (cardKey === 'allotment_accomplishment') {
                            percentage = allotmentAccomplishment;
                            cardValue.textContent = formatNumber(percentage) + '%';
                            
                            // Update tooltip values
                            const tooltipObl = card.querySelector('.card-tooltip-obligations-allot');
                            const tooltipAllot = card.querySelector('.card-tooltip-allotments');
                            if (tooltipObl) tooltipObl.textContent = formatNumber(obligations);
                            if (tooltipAllot) tooltipAllot.textContent = formatNumber(allotments);
                            
                        } else if (cardKey === 'disbursements_to_obligations') {
                            percentage = disbursementsToObligations;
                            cardValue.textContent = formatNumber(percentage) + '%';
                            
                            // Update tooltip values
                            const tooltipDisb = card.querySelector('.card-tooltip-disbursements-ob');
                            const tooltipObl = card.querySelector('.card-tooltip-obligations-disb');
                            if (tooltipDisb) tooltipDisb.textContent = formatNumber(disbursements);
                            if (tooltipObl) tooltipObl.textContent = formatNumber(obligations);
                            
                        } else if (cardKey === 'disbursements_to_appropriations') {
                            percentage = disbursementsToAppropriations;
                            cardValue.textContent = formatNumber(percentage) + '%';
                            
                            // Update tooltip values
                            const tooltipDisb = card.querySelector('.card-tooltip-disbursements-ap');
                            const tooltipAuthApprop = card.querySelector('.card-tooltip-auth-approp-disb');
                            if (tooltipDisb) tooltipDisb.textContent = formatNumber(disbursements);
                            if (tooltipAuthApprop) tooltipAuthApprop.textContent = formatNumber(authorizedAppropriations);
                            
                        } else {
                            // Handle regular number cards
                            cardValue.textContent = formatNumber(total);
                        }
                        
                        // Update circular progress for percentage cards
                        if (circularProgress && percentage !== undefined) {
                            const cappedPercentage = Math.min(percentage, 100);
                            const dashArray = (cappedPercentage * 1.507).toFixed(2);
                            circularProgress.setAttribute('stroke-dasharray', `${dashArray} 150.7`);
                            circularProgress.setAttribute('data-percentage', percentage);
                            
                            if (progressText) {
                                progressText.textContent = Math.round(cappedPercentage) + '%';
                            }
                        }
                        
                        // Update color classes for supplementals, reversions, and realignments
                        if (cardKey === 'supplemental_appropriations') {
                            if (total > 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                                cardValue.classList.add('text-green-600', 'dark:text-green-400');
                            } else {
                                cardValue.classList.remove('text-green-600', 'dark:text-green-400');
                                cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                            }
                        } else if (cardKey === 'reversions') {
                            if (total > 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                                cardValue.classList.add('text-red-600', 'dark:text-red-400');
                            } else {
                                cardValue.classList.remove('text-red-600', 'dark:text-red-400');
                                cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                            }
                        } else if (cardKey === 'realignments') {
                            if (total < 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                                cardValue.classList.add('text-red-600', 'dark:text-red-400');
                            } else if (total > 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                                cardValue.classList.add('text-green-600', 'dark:text-green-400');
                            } else {
                                cardValue.classList.remove('text-red-600', 'dark:text-red-400', 'text-green-600', 'dark:text-green-400');
                                cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                            }
                        }
                    }
                }
            }
        }

        function filterTable(searchValue) {
            const rows = document.querySelectorAll('#accountsTable tbody tr');
            const lowerSearch = String(searchValue).toLowerCase();

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(lowerSearch)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            updateCardValues();
            updateGraph();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                filterTable(this.value);
            });

            // Initial graph render
            setTimeout(() => {
                updateGraph();
            }, 100);
        });

// ============================================
// ANIMATED COUNTER FOR ACCOUNTS PAGE
// ============================================

/**
 * Animates a number from start to end value
 */
function animateCounter(element, start, end, duration = 1000, isPercentage = false) {
    const startTime = performance.now();
    const difference = end - start;
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation (easeOutExpo)
        const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        
        const currentValue = start + (difference * easeProgress);
        
        // Format the number
        const formattedValue = currentValue.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        element.textContent = formattedValue + (isPercentage ? '%' : '');
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

/**
 * Parse formatted number string to float
 */
function parseFormattedNumber(str) {
    if (!str) return 0;
    return parseFloat(str.replace(/,/g, '').replace('%', '')) || 0;
}

/**
 * Animate all card values with staggered timing
 */
function animateAllCards() {
    const cards = document.querySelectorAll('[data-card]');
    
    cards.forEach((card, index) => {
        const valueElement = card.querySelector('.card-value');
        if (!valueElement) return;
        
        const currentText = valueElement.textContent;
        const isPercentage = currentText.includes('%');
        const endValue = parseFormattedNumber(currentText);
        
        // Stagger animations slightly for visual effect
        setTimeout(() => {
            animateCounter(valueElement, 0, endValue, 1200, isPercentage);
        }, index * 50);
        
        // Also animate circular progress bars
        const circularProgress = card.querySelector('.circular-progress-bar');
        if (circularProgress) {
            const percentage = parseFloat(circularProgress.getAttribute('data-percentage')) || 0;
            animateCircularProgress(circularProgress, percentage);
        }
    });
}

/**
 * Animate circular progress bar
 */
function animateCircularProgress(element, targetPercentage, duration = 1200) {
    const startTime = performance.now();
    const cappedPercentage = Math.min(targetPercentage, 100);
    
    function updateProgress(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        const currentPercentage = cappedPercentage * easeProgress;
        const dashArray = (currentPercentage * 1.507).toFixed(2);
        
        element.setAttribute('stroke-dasharray', `${dashArray} 150.7`);
        
        // Update text inside circle
        const textElement = element.parentElement.querySelector('text');
        if (textElement) {
            textElement.textContent = Math.round(currentPercentage) + '%';
        }
        
        if (progress < 1) {
            requestAnimationFrame(updateProgress);
        }
    }
    
    requestAnimationFrame(updateProgress);
}

// ============================================
// HEATMAP TABLE ENHANCEMENT FOR ACCOUNTS
// ============================================

/**
 * Apply heatmap coloring to numeric table cells
 */
function applyHeatmap() {
    const table = document.getElementById('accountsTable');
    if (!table) return;
    
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
    if (rows.length === 0) return;
    
    // Get header row to identify specific columns
    const headerRow = table.querySelector('thead tr');
    if (!headerRow) return;
    
    const headers = Array.from(headerRow.cells).map(cell => cell.textContent.toLowerCase().trim());
    
    // Define exact column mappings for heatmap
    const columnMappings = [
        { names: ['approved appropriations'], type: 'value', color: 'blue' },
        { names: ['authorized appropriations'], type: 'value', color: 'blue' },
        { names: ['allotments'], type: 'value', color: 'green' },
        { names: ['obligations'], type: 'value', color: 'yellow' },
        { names: ['appropriation utilization'], type: 'percentage', color: 'blue' },
        { names: ['allotments utilization'], type: 'percentage', color: 'green' },
        { names: ['disbursements'], type: 'value', color: 'emerald' },
        { names: ['disbursements / oblgations', 'disbursements / obligations'], type: 'percentage', color: 'purple' },
        { names: ['disbursements / approp.', 'disbursements / appropriations'], type: 'percentage', color: 'amber' }
    ];
    
    // Find matching columns and apply heatmap
    columnMappings.forEach(mapping => {
        const colIndex = headers.findIndex(header => 
            mapping.names.some(name => header.includes(name))
        );
        
        if (colIndex === -1) return;
        
        const values = [];
        rows.forEach(row => {
            const cell = row.cells[colIndex];
            if (cell) {
                let value = 0;
                
                if (mapping.type === 'percentage') {
                    // Try to find progress bar or extract percentage value
                    const progressBar = cell.querySelector('[style*="width"]');
                    if (progressBar) {
                        const widthStr = progressBar.style.width;
                        value = parseFloat(widthStr) || 0;
                    } else {
                        const text = cell.textContent.trim();
                        const match = text.match(/(\d+(?:\.\d+)?)/);
                        if (match) {
                            value = parseFloat(match[1]);
                        }
                    }
                } else {
                    // Parse formatted number for value columns
                    value = parseFormattedNumber(cell.textContent);
                }
                
                values.push({ cell, value });
            }
        });
        
        if (values.length > 0) {
            applyColumnHeatmap(values, mapping.color, mapping.type);
        }
    });
}

function applyColumnHeatmap(values, colorName, type) {
    if (values.length === 0) return;
    
    // Calculate min and max
    const max = Math.max(...values.map(v => v.value));
    const min = Math.min(...values.map(v => v.value));
    const range = max - min;
    
    const colorPalettes = {
        'blue': { light: 'rgba(59, 130, 246, OPACITY)', dark: 'rgba(96, 165, 250, OPACITY)' },
        'green': { light: 'rgba(34, 197, 94, OPACITY)', dark: 'rgba(74, 222, 128, OPACITY)' },
        'red': { light: 'rgba(239, 68, 68, OPACITY)', dark: 'rgba(248, 113, 113, OPACITY)' },
        'yellow': { light: 'rgba(234, 179, 8, OPACITY)', dark: 'rgba(250, 204, 21, OPACITY)' },
        'purple': { light: 'rgba(168, 85, 247, OPACITY)', dark: 'rgba(196, 181, 253, OPACITY)' },
        'teal': { light: 'rgba(20, 184, 166, OPACITY)', dark: 'rgba(45, 212, 191, OPACITY)' },
        'emerald': { light: 'rgba(16, 185, 129, OPACITY)', dark: 'rgba(52, 211, 153, OPACITY)' },
    };
    
    const palette = colorPalettes[colorName] || colorPalettes['blue'];
    
    values.forEach(({ cell, value }, idx) => {
        if (range === 0) return;
        
        const normalized = (value - min) / range;
        const intensity = Math.round(normalized * 100);
        
        const baseOpacity = type === 'percentage' ? 0.15 : 0.1;
        const maxOpacity = type === 'percentage' ? 0.4 : 0.3;
        const opacity = baseOpacity + (intensity / 100) * maxOpacity;
        
        // Apply light mode color
        const lightColor = palette.light.replace('OPACITY', opacity);
        cell.style.backgroundColor = lightColor;
        cell.style.transition = 'background-color 0.3s ease';
        
        // Store dark mode color
        cell.setAttribute('data-dark-bg', palette.dark.replace('OPACITY', opacity * 0.6));
        
        // Apply dark mode if active
        if (document.documentElement.classList.contains('dark')) {
            cell.style.backgroundColor = palette.dark.replace('OPACITY', opacity * 0.6);
        }
        
        cell.classList.add('heatmap-cell-fade');
        cell.style.animationDelay = (idx % 10) * 30 + 'ms';
    });
}

/**
 * Remove heatmap coloring
 */
function removeHeatmap() {
    const table = document.getElementById('accountsTable');
    if (!table) return;
    
    const cells = table.querySelectorAll('tbody td');
    cells.forEach(cell => {
        cell.style.backgroundColor = '';
    });
}

/**
 * Toggle heatmap on/off
 */
let heatmapEnabled = false;
function toggleHeatmap() {
    heatmapEnabled = !heatmapEnabled;
    
    if (heatmapEnabled) {
        applyHeatmap();
    } else {
        removeHeatmap();
    }
    
    // Update toggle button if it exists
    const toggleBtn = document.getElementById('heatmapToggle');
    if (toggleBtn) {
        toggleBtn.textContent = heatmapEnabled ? '🎨 Disable Heatmap' : '🎨 Enable Heatmap';
    }
}

// ============================================
// ANIMATED STACKED GRAPH
// ============================================

/**
 * Animate the stacked bar graph segments
 */
function animateStackedGraph() {
    const container = document.getElementById('stackedBarContainer');
    if (!container) return;
    
    const segments = container.querySelectorAll('[class*="bg-"]');
    
    segments.forEach((segment, index) => {
        // Store the target width from the inline style
        const targetWidth = segment.style.width || '0%';
        
        // Set initial state
        segment.style.width = '0%';
        segment.style.opacity = '0';
        segment.style.transition = 'none';
        
        // Force reflow to ensure initial state is applied
        void segment.offsetWidth;
        
        // Animate to target width with stagger
        setTimeout(() => {
            segment.style.transition = 'width 0.8s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s ease-out';
            segment.style.width = targetWidth;
            segment.style.opacity = '1';
        }, index * 80 + 50);
    });
}

/**
 * Animate graph segments on initial load
 */
function animateGraphOnLoad() {
    const container = document.getElementById('stackedBarContainer');
    if (!container) return;
    
    // Wait for graph to be rendered first
    setTimeout(() => {
        const segments = container.querySelectorAll('[onmouseenter="showTooltip(this)"]');
        
        if (segments.length === 0) return;
        
        segments.forEach((segment, index) => {
            const targetWidth = segment.style.width || '0%';
            
            // Save original state
            const originalTransition = segment.style.transition;
            
            // Set to zero
            segment.style.transition = 'none';
            segment.style.width = '0%';
            segment.style.opacity = '0';
            
            // Force reflow
            void segment.offsetWidth;
            
            // Animate
            setTimeout(() => {
                segment.style.transition = 'width 0.8s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s ease-out';
                segment.style.width = targetWidth;
                segment.style.opacity = '1';
                
                // Restore original transition after animation
                setTimeout(() => {
                    segment.style.transition = originalTransition;
                }, 800);
            }, index * 80 + 100);
        });
    }, 200);
}

// toggleWidget function for accounts blade (handles analytics panel with account distribution and metrics)
function toggleWidget(widgetId) {
    if (widgetId === 'accountAnalyticsPanel') {
        const content = document.getElementById('accountAnalyticsPanelContent');
        const toggle = document.getElementById('accountAnalyticsPanelToggle');
        
        if (content && toggle) {
            const isHidden = content.style.display === 'none';
            content.style.display = isHidden ? 'block' : 'none';
            toggle.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            
            // If opening the analytics panel, initialize charts
            if (isHidden) {
                setTimeout(() => {
                    initializeAccountAnalyticsCharts();
                }, 100);
            }
        }
    }
}

// ============================================
// ENHANCED UPDATE FUNCTIONS WITH ANIMATION
// ============================================

/**
 * Enhanced version of updateCardValues with animation
 */
function updateCardValuesAnimated() {
    const rows = document.querySelectorAll('#accountsTable tbody tr');
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');

    // Calculate totals from visible rows
    const totals = {};
    for (const [cardKey, config] of Object.entries(cardConfig)) {
        let total = 0;
        visibleRows.forEach(row => {
            const value = parseFloat(row.getAttribute(config.column)) || 0;
            total += value;
        });
        totals[cardKey] = total;
    }

    // Calculate percentage cards properly based on base values
    const obligations = totals['obligations'] || 0;
    const authorizedAppropriations = totals['authorized_appropriations'] || 0;
    const allotments = totals['allotments'] || 0;
    const disbursements = totals['disbursements'] || 0;

    const appropriationAccomplishment = authorizedAppropriations > 0 
        ? (obligations / authorizedAppropriations) * 100 
        : 0;
    
    const allotmentAccomplishment = allotments > 0 
        ? (obligations / allotments) * 100 
        : 0;
    
    const disbursementsToObligations = obligations > 0 
        ? (disbursements / obligations) * 100 
        : 0;
    
    const disbursementsToAppropriations = authorizedAppropriations > 0 
        ? (disbursements / authorizedAppropriations) * 100 
        : 0;

    // Update cards with animation
    let delay = 0;
    for (const [cardKey, total] of Object.entries(totals)) {
        const card = document.querySelector(`[data-card="${cardKey}"]`);
        if (card) {
            const cardValue = card.querySelector('.card-value');
            const circularProgress = card.querySelector('.circular-progress-bar');
            
            if (cardValue) {
                let targetValue = 0;
                let isPercentage = false;
                
                // Handle percentage cards specially
                if (cardKey === 'appropriation_accomplishment') {
                    targetValue = appropriationAccomplishment;
                    isPercentage = true;
                    
                    const tooltipObl = card.querySelector('.card-tooltip-obligations');
                    const tooltipAuthApprop = card.querySelector('.card-tooltip-auth-approp');
                    if (tooltipObl) tooltipObl.textContent = obligations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (tooltipAuthApprop) tooltipAuthApprop.textContent = authorizedAppropriations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (cardKey === 'allotment_accomplishment') {
                    targetValue = allotmentAccomplishment;
                    isPercentage = true;
                    
                    const tooltipObl = card.querySelector('.card-tooltip-obligations-allot');
                    const tooltipAllot = card.querySelector('.card-tooltip-allotments');
                    if (tooltipObl) tooltipObl.textContent = obligations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (tooltipAllot) tooltipAllot.textContent = allotments.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (cardKey === 'disbursements_to_obligations') {
                    targetValue = disbursementsToObligations;
                    isPercentage = true;
                    
                    const tooltipDisb = card.querySelector('.card-tooltip-disbursements-ob');
                    const tooltipObl = card.querySelector('.card-tooltip-obligations-disb');
                    if (tooltipDisb) tooltipDisb.textContent = disbursements.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (tooltipObl) tooltipObl.textContent = obligations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (cardKey === 'disbursements_to_appropriations') {
                    targetValue = disbursementsToAppropriations;
                    isPercentage = true;
                    
                    const tooltipDisb = card.querySelector('.card-tooltip-disbursements-ap');
                    const tooltipAuthApprop = card.querySelector('.card-tooltip-auth-approp-disb');
                    if (tooltipDisb) tooltipDisb.textContent = disbursements.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (tooltipAuthApprop) tooltipAuthApprop.textContent = authorizedAppropriations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else {
                    targetValue = total;
                }
                
                // Get current value
                const currentValue = parseFormattedNumber(cardValue.textContent);
                
                // Animate from current to target
                setTimeout(() => {
                    animateCounter(cardValue, currentValue, targetValue, 800, isPercentage);
                }, delay);
                
                delay += 30;
                
                // Update circular progress
                if (circularProgress && isPercentage) {
                    setTimeout(() => {
                        animateCircularProgress(circularProgress, targetValue, 800);
                    }, delay - 30);
                }
                
                // Update color classes
                if (cardKey === 'supplemental_appropriations') {
                    if (total > 0) {
                        cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                        cardValue.classList.add('text-green-600', 'dark:text-green-400');
                    } else {
                        cardValue.classList.remove('text-green-600', 'dark:text-green-400');
                        cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                    }
                } else if (cardKey === 'reversions') {
                    if (total > 0) {
                        cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                        cardValue.classList.add('text-red-600', 'dark:text-red-400');
                    } else {
                        cardValue.classList.remove('text-red-600', 'dark:text-red-400');
                        cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                    }
                } else if (cardKey === 'realignments') {
                    if (total < 0) {
                        cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-green-600', 'dark:text-green-400');
                        cardValue.classList.add('text-red-600', 'dark:text-red-400');
                    } else if (total > 0) {
                        cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-red-600', 'dark:text-red-400');
                        cardValue.classList.add('text-green-600', 'dark:text-green-400');
                    } else {
                        cardValue.classList.remove('text-red-600', 'dark:text-red-400', 'text-green-600', 'dark:text-green-400');
                        cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                    }
                }
            }
        }
    }
    
    // Apply heatmap after update
    if (heatmapEnabled) {
        setTimeout(() => applyHeatmap(), delay + 200);
    }
}

/**
 * Enhanced updateGraph with animation
 */
function updateGraphAnimated() {
    // Get all segments before updating
    const container = document.getElementById('stackedBarContainer');
    if (container) {
        const oldSegments = container.querySelectorAll('[onmouseenter="showTooltip(this)"]');
        
        // Store old widths for animation reference
        const oldWidths = Array.from(oldSegments).map(seg => seg.style.width);
    }
    
    updateGraph(); // Call original function to rebuild graph
    
    // Animate the new segments
    setTimeout(() => {
        const newSegments = container.querySelectorAll('[onmouseenter="showTooltip(this)"]');
        
        newSegments.forEach((segment, index) => {
            const targetWidth = segment.style.width || '0%';
            
            // Set initial state
            segment.style.transition = 'none';
            segment.style.width = '0%';
            segment.style.opacity = '0.5';
            
            // Force reflow
            void segment.offsetWidth;
            
            // Animate to target
            setTimeout(() => {
                segment.style.transition = 'width 0.6s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease-out';
                segment.style.width = targetWidth;
                segment.style.opacity = '1';
            }, index * 60);
        });
    }, 50);
}

// ============================================
// HEATMAP TOGGLE BUTTON
// ============================================

function addHeatmapToggle() {
    const tableHeader = document.querySelector('.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg.mt-4.mb-4 .flex.justify-between.items-center.mb-4');
    if (tableHeader && !document.getElementById('heatmapToggle')) {
        const toggleButton = document.createElement('button');
        toggleButton.id = 'heatmapToggle';
        toggleButton.onclick = toggleHeatmap;
        toggleButton.className = 'px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors duration-200 dark:bg-indigo-500 dark:hover:bg-indigo-600';
        toggleButton.innerHTML = '🎨 Enable Heatmap';
        
        tableHeader.appendChild(toggleButton);
    }
}

    // ============================================
    // PROGRESS BAR ANIMATION
    // ============================================

    /**
     * Apply fade animation to all progress bars on page load
     */
    function animateProgressBars() {
        // Animate circular progress bars
        const circularBars = document.querySelectorAll('.circular-progress-bar');
        circularBars.forEach((bar) => {
            bar.style.animationDelay = '0ms';
        });

        // Animate linear progress bars (percentage columns)
        const linearBars = document.querySelectorAll('.bg-gradient-to-r');
        linearBars.forEach((bar) => {
            bar.classList.add('progress-bar-fade');
            bar.style.animationDelay = '0ms';
        });
    }

    // ============================================
    // INITIALIZATION
    // ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Add heatmap fade animation CSS
    const style = document.createElement('style');
    style.textContent = `
        .heatmap-cell-fade {
            animation: heatmapFadeIn 0.6s ease-out forwards;
        }
        
        @keyframes heatmapFadeIn {
            from {
                opacity: 0;
                background-color: transparent !important;
            }
            to {
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Add heatmap toggle button
    addHeatmapToggle();
    
    // Initialize progress bar animations
    setTimeout(() => {
        animateProgressBars();
    }, 200);
    
    // Initial animations on page load
    setTimeout(() => {
        animateAllCards();
        animateGraphOnLoad(); // Use the specific graph animation function
        if (heatmapEnabled) {
            applyHeatmap();
        }
    }, 300);
    
    // Make functions available globally
    window.toggleHeatmap = toggleHeatmap;
    window.animateStackedGraph = animateStackedGraph;
    window.animateGraphOnLoad = animateGraphOnLoad;
});

// Override the original updateCardValues function
const originalUpdateCardValues = window.updateCardValues;
if (typeof originalUpdateCardValues === 'function') {
    window.updateCardValues = function() {
        updateCardValuesAnimated();
    };
}

    
    // Store current appropriation context
    let currentAccountAppropriation = null;

    // Right-click context menu handler for accounts table
    document.addEventListener('DOMContentLoaded', function() {
        const accountsTable = document.getElementById('accountsTable');
        const contextMenu = document.getElementById('accountContextMenu');

        if (accountsTable) {
            accountsTable.addEventListener('contextmenu', function(event) {
                event.preventDefault();
                
                // Find the closest row
                const row = event.target.closest('tr');
                if (row && row.querySelector('td')) {
                    const appropriationId = row.dataset.appropriationId;
                    const accountCode = row.getAttribute('data-account-code');
                    const programs = row.querySelector('td:nth-child(1)')?.textContent?.trim();
                    const description = row.getAttribute('data-description');
                    
                    // Store context
                    currentAccountAppropriation = {
                        accountCode: accountCode,
                        description: description,
                        appropriationId: appropriationId,
                        programs: programs
                    };
                    
                    // Remove highlight from previously selected row
                    document.querySelectorAll('#accountsTable tbody tr.context-menu-active').forEach(r => {
                        r.classList.remove('context-menu-active');
                    });
                    
                    // Highlight the current row
                    row.classList.add('context-menu-active');
                    window.currentAccountContextMenuRow = row;
                    
                    // Position the context menu
                        contextMenu.style.left = event.clientX + 'px';
                        contextMenu.style.top = event.clientY + 'px';
                    contextMenu.classList.remove('hidden');
                }
            });

            // Hide context menu on click
            document.addEventListener('click', function(e) {
                if (!contextMenu.contains(e.target) && !e.target.closest('tr')) {
                    contextMenu.classList.add('hidden');
                    // Remove highlight when menu is closed
                    if (window.currentAccountContextMenuRow) {
                        window.currentAccountContextMenuRow.classList.remove('context-menu-active');
                        window.currentAccountContextMenuRow = null;
                    }
                }
            });
        }
    });

    function handleAccountMenuOption(option) {
        const contextMenu = document.getElementById('accountContextMenu');
        contextMenu.classList.add('hidden');

        if (option === 'obligations') {
            showAccountObligationsModal();
        }
    }

    function showAccountObligationsModal() {
        if (!currentAccountAppropriation || !currentAccountAppropriation.appropriationId) {
            alert('Could not retrieve appropriation information');
            return;
        }

        const modal = document.getElementById('accountObligationsModal');
        const headerInfo = document.getElementById('accountObligationsHeaderInfo');
        const content = document.getElementById('accountObligationsContent');
        const loading = document.getElementById('accountObligationsLoading');

        modal.offsetHeight;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        
        // Clear previous content
        headerInfo.textContent = ' | ' + (currentAccountAppropriation.accountCode || 'N/A') + ' - ' + (currentAccountAppropriation.description || 'N/A');
        if (loading) loading.style.display = 'flex';

        // Fetch obligations for this appropriation
        fetch(`/api/obligations/by-appropriation/${currentAccountAppropriation.appropriationId}`)
            .then(response => response.json())
            .then(data => {
                if (loading) loading.style.display = 'none';
                
                // Store office and allotment class info for print function
                if (data.success) {
                    window.currentObligationsInfo = {
                        office: data.office || '-',
                        allotmentClass: data.allotmentClass || '-',
                        cyYear: data.cy_year || '-'
                    };
                    // Update header with office and allotment class
                    headerInfo.textContent = ` | ${currentAccountAppropriation.accountCode} - ${currentAccountAppropriation.description} | Office: ${data.office || '-'} - Class: ${data.allotmentClass || '-'} (CY ${data.cy_year || '-'})`;
                }
                
                if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
                    // Create table with obligations
                    let tableHTML = `
                        <div class="overflow-x-auto flex flex-col" style="max-height: calc(90vh - 340px);">
                            <table class="w-full text-xs text-gray-700 dark:text-gray-300" style="table-layout: fixed;">
                                <colgroup>
                                    <col style="width: 50px;">
                                    <col style="width: 120px;">
                                    <col style="width: 100px;">
                                    <col style="width: 100px;">
                                    <col style="width: 300px;">
                                    <col style="width: 150px;">
                                    <col style="width: 130px;">
                                    <col style="width: 130px;">
                                    <col style="width: 130px;">
                                </colgroup>
                                <thead class="bg-gray-200 dark:bg-gray-700 border-b border-t border-gray-400 dark:border-gray-600 text-center">
                                    <tr>
                                        <th class="px-3 py-2"></th>
                                        <th class="px-3 py-2">OBR No.</th>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">OBR Type</th>
                                        <th class="px-3 py-2">Particulars</th>
                                        <th class="px-3 py-2">Remarks</th>
                                        <th class="px-3 py-2">Obligation</th>
                                        <th class="px-3 py-2">Purchase Order</th>
                                        <th class="px-3 py-2">Disbursement</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="overflow-y-auto flex-1">
                                <table class="w-full text-xs text-gray-700 dark:text-gray-300" style="table-layout: fixed;">
                                    <colgroup>
                                        <col style="width: 50px;">
                                        <col style="width: 120px;">
                                        <col style="width: 100px;">
                                        <col style="width: 100px;">
                                        <col style="width: 300px;">
                                        <col style="width: 150px;">
                                        <col style="width: 130px;">
                                        <col style="width: 130px;">
                                        <col style="width: 130px;">
                                    </colgroup>
                                    <tbody class="divide-y divide-gray-300 dark:divide-gray-600">
                    `;
                    
                    let totalAmount = 0;
                    let totalPurchaseOrder = 0;
                    let totalDisbursement = 0;
                    let recordCount = 0;

                    data.data.forEach((obligation, index) => {
                        // Get total obligation amount from API (sum of all obligation_amounts)
                        const totalOblAmount = parseFloat(obligation.amount.replace(/,/g, ''));
                        
                        // Get total purchase order amount
                        const poAmount = obligation.purchase_order !== '-' ? parseFloat(obligation.purchase_order.replace(/,/g, '')) : 0;
                        
                        // Get total disbursement amount
                        const disbAmount = obligation.disbursement !== '-' ? parseFloat(obligation.disbursement.replace(/,/g, '')) : 0;
                        
                        // Check if obligation is cancelled (amount is 0)
                        const isCancelled = totalOblAmount === 0;
                        
                        // Format amounts for display
                        const formattedOblAmount = totalOblAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        const formattedPOAmount = poAmount > 0 ? poAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                        const formattedDisbAmount = disbAmount > 0 ? disbAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                        
                        const amountDisplay = isCancelled ? 
                            '<span class="text-red-600 dark:text-red-400 font-semibold">Cancelled</span>' : 
                            formattedOblAmount;
                        
                        tableHTML += `
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer obligation-row" data-obligation-index="${index}">
                                <td class="px-3 py-2 text-center"><i class="fas fa-chevron-right text-gray-500 transition-transform duration-200 expand-icon"></i></td>
                                <td class="px-3 py-2">${obligation.obr_no}</td>
                                <td class="px-3 py-2">${obligation.obr_date}</td>
                                <td class="px-3 py-2">${obligation.obr_type}</td>
                                <td class="px-3 py-2">${obligation.payee}</td>
                                <td class="px-3 py-2">${obligation.remarks || '-'}</td>
                                <td class="px-3 py-2 text-right font-semibold">${amountDisplay}</td>
                                <td class="px-3 py-2 text-right">${formattedPOAmount}</td>
                                <td class="px-3 py-2 text-right">${formattedDisbAmount}</td>
                            </tr>
                            <tr class="hidden appropriations-row" data-obligation-index="${index}">
                                <td colspan="9" class="px-2 py-2">
                                        <table class="w-full text-xs text-gray-600 dark:text-gray-400" style="table-layout: fixed;">
                                            <colgroup>
                                                <col style="width: 80px;">
                                                <col style="width: 80px;">
                                                <col style="width: 150px;">
                                                <col style="width: 100px;">
                                                <col style="width: 110px;">
                                                <col style="width: 110px;">
                                                <col style="width: 100px;">
                                                <col style="width: 100px;">
                                            </colgroup>
                                            <thead class="border border-gray-400 dark:border-gray-600 text-center">
                                                <tr class="bg-gray-100 dark:bg-gray-800">
                                                    <th class="px-3 py-2">Programs</th>
                                                    <th class="px-3 py-2">Account Code</th>
                                                    <th class="px-3 py-2">Description</th>
                                                    <th class="px-3 py-2">Amount</th>
                                                    <th class="px-3 py-2">Adjustment Amount</th>
                                                    <th class="px-3 py-2">Adjusted Amount</th>
                                                    <th class="px-3 py-2">Purchase Order</th>
                                                    <th class="px-3 py-2">Disbursement</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-400 dark:border-gray-600">
                        `;
                        
                        // Display all appropriations in the detail row
                        obligation.appropriations.forEach(app => {
                            tableHTML += `
                                                <tr>
                                                    <td class="px-3 py-2">${app.programs}</td>
                                                    <td class="px-3 py-2">${app.code}</td>
                                                    <td class="px-3 py-2">${app.description}</td>
                                                    <td class="px-3 py-2 text-right font-semibold">${app.amount}</td>
                                                    <td class="px-3 py-2 text-right">${app.adjustment_amount}</td>
                                                    <td class="px-3 py-2 text-right font-semibold">${app.adjusted_amount}</td>
                                                    <td class="px-3 py-2 text-right">${app.purchase_order_amount}</td>
                                                    <td class="px-3 py-2 text-right">${app.disbursement_amount}</td>
                                                </tr>
                            `;
                        });
                        
                        tableHTML += `
                                            </tbody>
                                        </table>
                                </td>
                            </tr>
                        `;
                        
                        // Add to total (using total obligation amounts from API)
                        totalAmount += totalOblAmount;
                        totalPurchaseOrder += poAmount;
                        totalDisbursement += disbAmount;
                        recordCount++;
                    });
                    
                    tableHTML += `
                                    </tbody>
                                </table>
                            </div>
                            <table class="w-full text-xs text-gray-700 dark:text-gray-300" style="table-layout: fixed;">
                                <colgroup>
                                    <col style="width: 50px;">
                                    <col style="width: 120px;">
                                    <col style="width: 100px;">
                                    <col style="width: 100px;">
                                    <col style="width: auto;">
                                    <col style="width: 150px;">
                                    <col style="width: 130px;">
                                    <col style="width: 130px;">
                                    <col style="width: 130px;">
                                </colgroup>
                                <tfoot class="bg-gray-200 dark:bg-gray-700 font-semibold border-t-2 border-gray-400 dark:border-gray-600">
                                    <tr>
                                        <td colspan="6" class="px-3 py-2 text-right">Total:</td>
                                        <td class="px-3 py-2 text-right text-blue-700 dark:text-blue-300">${totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                        <td class="px-3 py-2 text-right text-green-700 dark:text-green-300">${totalPurchaseOrder > 0 ? totalPurchaseOrder.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}</td>
                                        <td class="px-3 py-2 text-right text-orange-700 dark:text-orange-300">${totalDisbursement > 0 ? totalDisbursement.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `;
                    
                    if (content) {
                        content.innerHTML = tableHTML;
                        
                        // Initialize header total records count
                        const headerCountElement = document.getElementById('accountObligationsTotalRecordsCount');
                        if (headerCountElement) {
                            headerCountElement.textContent = recordCount;
                        }
                        
                        // Add click event listeners to obligation rows
                        document.querySelectorAll('.obligation-row').forEach(row => {
                            row.addEventListener('click', function() {
                                const obligationIndex = this.dataset.obligationIndex;
                                const appRow = document.querySelector(`.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                                const expandIcon = this.querySelector('.expand-icon');
                                
                                if (appRow) {
                                    appRow.classList.toggle('hidden');
                                    expandIcon.style.transform = appRow.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
                                }
                            });

                            // Add right-click context menu listener
                            row.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                                showAccountObligationContextMenu(e, this, data.data[this.dataset.obligationIndex]);
                            });
                        });
                    }
                } else if (data.success && content) {
                    content.innerHTML = '<div class="text-center py-8 text-gray-500 italic dark:text-gray-400">No Obligations found for this Account.</div>';
                } else if (content) {
                    content.innerHTML = '<div class="text-center py-8 text-red-500">Error: ' + (data.message || 'Unknown error') + '</div>';
                }
            })
            .catch(error => {
                if (loading) loading.style.display = 'none';
                if (content) {
                    content.innerHTML = '<div class="text-center py-8 text-red-500">Error loading obligations: ' + error.message + '</div>';
                }
                console.error('Error fetching obligations:', error);
            });
    }

    function closeAccountObligationsModal() {
        const modal = document.getElementById('accountObligationsModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        // Clear search input
        const searchInput = document.getElementById('accountObligationsSearchInput');
        if (searchInput) {
            searchInput.value = '';
        }
    }

    /**
     * Print obligations table
     */
    function printObligationsModal(source = 'dashboard') {
        const modalId = source === 'dashboard' ? 'obligationsModal' : 'accountObligationsModal';
        const modal = document.getElementById(modalId);
        
        if (!modal || !window.currentObligationsInfo) {
            alert('No data to print');
            return;
        }
        
        // Clone the modal content for printing
        const printWindow = window.open('', '', 'height=800,width=1200');
        
        // Get header information
        const headerInfo = window.currentObligationsInfo;
        
        // Get the table content
        const contentDiv = source === 'dashboard' ? 
            document.getElementById('obligationsContent') :
            document.getElementById('accountObligationsContent');
        
        if (!contentDiv) {
            alert('Could not retrieve table data');
            return;
        }
        
        // Find the main table container (overflow-x-auto div)
        const tableContainer = contentDiv.querySelector('div.overflow-x-auto');
        if (!tableContainer) {
            alert('Could not locate table container');
            return;
        }
        
        // Clone the container to avoid modifying the original
        const printableContent = tableContainer.cloneNode(true);
        
        // Remove all hidden obligation rows and their corresponding appropriations rows
        const allObligationRows = printableContent.querySelectorAll('tbody tr.obligation-row');
        const rowsToRemove = [];
        
        allObligationRows.forEach(row => {
            if (row.style.display === 'none') {
                const obligationIndex = row.dataset.obligationIndex;
                // Mark this row for removal
                rowsToRemove.push(row);
                // Also mark its corresponding appropriations row for removal
                const appRow = printableContent.querySelector(`tr.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                if (appRow) {
                    rowsToRemove.push(appRow);
                }
            } else {
                // Make visible appropriations rows display properly
                const obligationIndex = row.dataset.obligationIndex;
                const appRow = printableContent.querySelector(`tr.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                if (appRow) {
                    appRow.classList.remove('hidden');
                    appRow.style.display = '';
                }
            }
        });
        
        // Remove hidden rows from the DOM
        rowsToRemove.forEach(row => {
            row.remove();
        });
        
        // Get the HTML of the cloned container
        const tableHTML = printableContent.outerHTML;
        
        // Create a comprehensive print document
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Obligations Report</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #333;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                        border-bottom: 2px solid #333;
                        padding-bottom: 10px;
                    }
                    .header h1 {
                        margin: 0;
                        font-size: 18px;
                    }
                    .header p {
                        margin: 5px 0;
                        font-size: 12px;
                    }
                    .office-info {
                        text-align: center;
                        margin-bottom: 15px;
                        font-size: 14px;
                        font-weight: bold;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 11px;
                        margin-bottom: 20px;
                    }
                    table th {
                        background-color: #e8e8e8;
                        border: 1px solid #ccc;
                        padding: 8px;
                        text-align: left;
                        font-weight: bold;
                    }
                    table td {
                        border: 1px solid #ccc;
                        padding: 6px;
                        text-align: left;
                    }
                    table tfoot td {
                        background-color: #f0f0f0;
                        font-weight: bold;
                        border: 1px solid #ccc;
                        padding: 8px;
                    }
                    .text-right {
                        text-align: right;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .text-red {
                        color: #dc2626;
                    }
                    .text-green {
                        color: #16a34a;
                    }
                    .text-blue {
                        color: #2563eb;
                    }
                    .text-orange {
                        color: #ea580c;
                    }
                    .print-date {
                        text-align: right;
                        font-size: 10px;
                        margin-top: 15px;
                        color: #666;
                    }
                    @media print {
                        body {
                            margin: 0;
                        }
                        .print-date {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body onload="window.print()">
                <div class="header">
                    <h1>OBLIGATIONS REPORT</h1>
                    <p>Generated on ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                </div>
                
                <div class="office-info">
                    Office: ${headerInfo.office} | Allotment Class: ${headerInfo.allotmentClass} | CY ${headerInfo.cyYear}
                </div>
                
                <div class="table-container">
                    ${tableHTML}
                    <div class="print-date">
                        Printed on: ${new Date().toLocaleString()}
                    </div>
                </div>
            </body>
            </html>
        `;
        
        // Write content and close document to trigger print dialog
        printWindow.document.write(printContent);
        printWindow.document.close();
    }

    // Show context menu for account obligations
    function showAccountObligationContextMenu(event, row, obligation) {
        const menu = document.getElementById('accountObligationsContextMenu');
        if (!menu) return;
        
        // Remove highlight from previously selected row
        document.querySelectorAll('#accountObligationsModal tbody tr.context-menu-active').forEach(r => {
            r.classList.remove('context-menu-active');
        });
        
        // Highlight the current row
        row.classList.add('context-menu-active');
        window.currentAccountObligationContextMenuRow = row;
        
        menu.style.display = 'block';
        menu.style.left = (event.clientX) + 'px';
        menu.style.top = (event.clientY) + 'px';

        // Store obligation data globally
        window.selectedAccountObligation = obligation;

        // View Details button handler
        const detailsBtn = menu.querySelector('#contextAccountObligationDetails');
        if (detailsBtn) {
            detailsBtn.onclick = (e) => {
                e.preventDefault();
                hideAccountObligationContextMenu();
                openModal(obligation.id);
            };
        }

        // Edit button handler
        const editBtn = menu.querySelector('#contextAccountObligationEdit');
        if (editBtn) {
            editBtn.onclick = (e) => {
                e.preventDefault();
                hideAccountObligationContextMenu();
                openAccountObligationEditModal(obligation);
            };
        }

        // Adjustment button handler
        const adjBtn = menu.querySelector('#contextAccountObligationAdjustment');
        if (adjBtn) {
            adjBtn.onclick = (e) => {
                e.preventDefault();
                hideAccountObligationContextMenu();
                openAccountObligationAdjustmentModal(obligation);
            };
        }

        // Purchase Order button handler - only show for Purchase Request type
        const poBtn = menu.querySelector('#contextAccountObligationPO');
        if (poBtn) {
            // Show/hide based on obligation type
            if (obligation.obr_type === 'Purchase Request') {
                poBtn.style.display = 'block';
                poBtn.onclick = (e) => {
                    e.preventDefault();
                    hideAccountObligationContextMenu();
                    openAccountObligationPurchaseOrderModal(obligation);
                };
            } else {
                poBtn.style.display = 'none';
            }
        }

        // Status/History button handler
        const historyBtn = menu.querySelector('#contextAccountObligationHistory');
        if (historyBtn) {
            historyBtn.onclick = (e) => {
                e.preventDefault();
                hideAccountObligationContextMenu();
                openObligationHistoryModal(obligation);
            };
        }

        // Cancellation button handler
        const cancelBtn = menu.querySelector('#contextAccountObligationCancellation');
        if (cancelBtn) {
            cancelBtn.onclick = (e) => {
                e.preventDefault();
                hideAccountObligationContextMenu();
                openAccountsCancellationModal(obligation.id, obligation);
            };
        }
    }

    // Hide context menu
    function hideAccountObligationContextMenu() {
        const menu = document.getElementById('accountObligationsContextMenu');
        if (menu) {
            menu.style.display = 'none';
        }
        // Remove highlight when menu is closed
        if (window.currentAccountObligationContextMenuRow) {
            window.currentAccountObligationContextMenuRow.classList.remove('context-menu-active');
            window.currentAccountObligationContextMenuRow = null;
        }
    }

    function closeAllDropdowns() {
        // Close context menu
        const contextMenu = document.getElementById('accountObligationsContextMenu');
        if (contextMenu) {
            contextMenu.style.display = 'none';
            contextMenu.classList.add('hidden');
        }
    }

    // Open edit modal with obligation data
    function openAccountObligationEditModal(obligation) {
        if (typeof openEditObligationsModal === 'function') {
            // Fetch full obligation data from show endpoint
            fetch(`/obligations/${obligation.id}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Full obligation data from API:', data);
                
                // Get office and allotment class details from API response
                const officeAbbr = data.obligation.office || '';
                const classDesc = data.obligation.allotment_class || '';
                
                // Get office_allotment_class_id from currentAccountAppropriation or the obligation
                let officeAllotmentClassId = obligation.office_allotment_class_id;
                if (!officeAllotmentClassId && currentAccountAppropriation && currentAccountAppropriation.officeAllotmentClassId) {
                    officeAllotmentClassId = currentAccountAppropriation.officeAllotmentClassId;
                }
                
                // Build complete obligation object with all required fields
                const fullObligation = {
                    id: obligation.id,
                    office_allotment_class_id: officeAllotmentClassId,
                    office_allotment_class: {
                        id: officeAllotmentClassId,
                        name: `${officeAbbr} - ${classDesc}`,
                        office_abbreviation: officeAbbr,
                        class: classDesc
                    },
                    obr_date: data.obligation.obr_date,
                    obr_type: data.obligation.obr_type,
                    obr_no: data.obligation.obr_no,
                    particulars: data.obligation.particulars,
                    remarks: data.obligation.remarks,
                    processed_by: data.obligation.processed_by,
                    obligation_amounts: data.obligation_amounts || []
                };
                
                console.log('Complete obligation for edit modal:', fullObligation);
                // Set flag to indicate this is from accounts
                window.isFromAccounts = true;
                if (officeAllotmentClassId) {
                    window.accountsClassId = officeAllotmentClassId;
                }
                // Open the edit modal with the complete obligation data
                openEditObligationsModal(fullObligation);
            })
            .catch(error => {
                console.error('Error fetching obligation:', error);
                alert('Error loading obligation: ' + error.message);
            });
        } else {
            alert('Edit modal not available');
        }
    }

    // Open adjustment modal - navigate to adjustment index with obligation_id
    function openAccountObligationAdjustmentModal(obligation) {
        hideAccountObligationContextMenu();
        // Set the obligation_id in the create form
        const obligationIdInput = document.querySelector('#createObligationAdjustmentForm input[name="obligation_id"]');
        if (obligationIdInput) {
            obligationIdInput.value = obligation.id;
        }
        // Open the create adjustment modal with obligation ID
        if (typeof openCreateObligationAdjustmentModal === 'function') {
            openCreateObligationAdjustmentModal(obligation.id);
        }
    }

    // Open purchase order modal
    function openAccountObligationPurchaseOrderModal(obligation) {
        hideAccountObligationContextMenu();
        if (obligation && obligation.id) {
            // Fetch the complete modal HTML from the server with all data pre-populated
            fetch(`/obligations/${obligation.id}/purchase-order-modal`)
                .then(response => response.text())
                .then(html => {
                    // Find the existing form and replace it entirely with the new HTML
                    const existingForm = document.getElementById('CreatePurchaseOrderForm');
                    if (existingForm) {
                        // Create a temporary container to parse the HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        const newForm = temp.querySelector('form');
                        
                        if (newForm) {
                            // Replace the old form with the new one
                            existingForm.replaceWith(newForm);
                            
                            // Show the modal after replacement
                            setTimeout(() => {
                                const modal = document.getElementById('createPOModal');
                                if (modal) {
                                    modal.offsetHeight;
                                    modal.style.display = 'flex';
                                    modal.setAttribute('aria-hidden', 'false');
                                    console.log('Purchase Order modal opened with pre-populated data');
                                }
                            }, 10);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading purchase order modal:', error);
                    alert('Failed to load purchase order modal. Please try again.');
                });
        }
    }

    // Close purchase order modal
    function closeCreatePOModal() {
        const modal = document.getElementById('createPOModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    /**
     * Open obligation history modal
     */
    function openObligationHistoryModal(obligation) {
        if (!obligation || !obligation.id) {
            alert('Invalid obligation selected');
            return;
        }

        const modal = document.getElementById('obligationHistoryModal');
        const historyContent = document.getElementById('historyContent');
        const historyInfo = document.getElementById('historyObligationInfo');

        // Show modal with loading spinner
        modal.offsetHeight;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        historyInfo.textContent = ` | ${obligation.obr_no || 'Loading...'}`;
        historyContent.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div></div>';

        // Fetch activity history
        fetch(`/obligations/${obligation.id}/activity-history`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    displayActivityHistory(data.data, historyContent);
                } else {
                    historyContent.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">No activity history found</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching activity history:', error);
                historyContent.innerHTML = '<div class="text-center py-8 text-red-500">Failed to load activity history. Please try again.</div>';
            });
    }

    /**
     * Close obligation history modal
     */
    function closeObligationHistoryModal() {
        const modal = document.getElementById('obligationHistoryModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    /**
     * Open cancellation modal from accounts obligations modal
     */
    function openAccountsCancellationModal(obligationId, obligationData) {
        try {
            // Close dropdowns if the function exists
            if (typeof CloseAllDropdowns === 'function') {
                CloseAllDropdowns();
            }
            
            const modal = document.getElementById('accountsCancellationModal');
            
            if (!modal) {
                console.error('accountsCancellationModal not found');
                alert('Cancellation modal not found');
                return;
            }
            
            // Ensure obligation data exists
            if (!obligationData) {
                console.error('No obligation data provided');
                alert('No obligation data available');
                return;
            }
            
            modal.offsetHeight; // Trigger reflow
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');

            modal.dataset.obligationId = obligationId;

            // Set the hidden input
            const hiddenInput = document.getElementById('accountsHiddenObligationId');
            if (hiddenInput) {
                hiddenInput.value = obligationId;
            }

            // Get office and allotment class from page data
            let officeAbbr = currentPageOfficeAllotmentData?.officeAbbr || 'N/A';
            let allotmentClass = currentPageOfficeAllotmentData?.allotmentClass || 'N/A';
            
            // Fill modal data - use correct field names from API response
            const obrDateField = document.querySelector('#accountsCancellationModal td[data-field="obr_date"]');
            if (obrDateField) obrDateField.textContent = obligationData.obr_date || 'N/A';
            
            const officeField = document.querySelector('#accountsCancellationModal td[data-field="office_abbreviation"]');
            if (officeField) officeField.textContent = officeAbbr;
            
            const classField = document.querySelector('#accountsCancellationModal td[data-field="allotment_class"]');
            if (classField) classField.textContent = allotmentClass;
            
            const obrNoField = document.querySelector('#accountsCancellationModal td[data-field="obr_no"]');
            if (obrNoField) obrNoField.textContent = obligationData.obr_no || 'N/A';
            
            const obrTypeField = document.querySelector('#accountsCancellationModal td[data-field="obr_type"]');
            if (obrTypeField) obrTypeField.textContent = obligationData.obr_type || 'N/A';
            
            const particularsField = document.querySelector('#accountsCancellationModal td[data-field="particulars"]');
            if (particularsField) particularsField.textContent = obligationData.payee || 'N/A';
            
            const amountField = document.querySelector('#accountsCancellationModal td[data-field="obr_amount"]');
            if (amountField) {
                amountField.textContent = Number(obligationData.amount.replace(/,/g, '')).toLocaleString(undefined, {
                    minimumFractionDigits: 2
                });
            }

            const proceedBtn = modal.querySelector('button[onclick="proceedAccountsCancellation()"]');
            const remarksBox = document.getElementById('accountsCancellationRemarks');
            const messageContainerId = 'accountsCancelNotice';

            // Clear previous remarks
            if (remarksBox) {
                remarksBox.value = '';
            }

            // Remove any previous message
            const oldMessage = document.getElementById(messageContainerId);
            if (oldMessage) oldMessage.remove();

            // Check if obligation is already cancelled
            if (Number(obligationData.amount.replace(/,/g, '')) === 0) {
                // Disable button and textarea
                if (proceedBtn) proceedBtn.disabled = true;
                if (remarksBox) remarksBox.disabled = true;

                // Add a note below the table
                const message = document.createElement('p');
                message.id = messageContainerId;
                message.className = 'text-red-600 text-sm mt-4 font-semibold';
                message.textContent = 'This obligation is already cancelled.';

                // Append after table
                const bodyDiv = modal.querySelector('div.overflow-y-auto');
                if (bodyDiv) bodyDiv.appendChild(message);
            } else {
                // Enable if it was previously disabled
                if (proceedBtn) proceedBtn.disabled = false;
                if (remarksBox) remarksBox.disabled = false;
            }
        } catch (error) {
            console.error('Error opening cancellation modal:', error);
            alert('Error opening cancellation modal: ' + error.message);
        }
    }

    /**
     * Close accounts cancellation modal
     */
    function closeAccountsCancellationModal() {
        const modal = document.getElementById('accountsCancellationModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    /**
     * Proceed with accounts cancellation
     */
    function proceedAccountsCancellation() {
        const modal = document.getElementById('accountsCancellationModal');
        const obligationId = modal.dataset.obligationId;
        const remarks = document.getElementById('accountsCancellationRemarks').value.trim();

        if (!remarks) {
            let errorSpan = document.getElementById('accountsRemarksError');
            if (!errorSpan) {
                errorSpan = document.createElement('span');
                errorSpan.id = 'accountsRemarksError';
                errorSpan.className = 'text-sm text-red-600 mt-1 block';
                document.getElementById('accountsCancellationRemarks').parentNode.appendChild(errorSpan);
            }
            errorSpan.textContent = 'Remarks is required.';
            return;
        }

        // Prepare the form
        const form = document.getElementById('accountsCancelObligationForm');
        form.action = `/obligations/${obligationId}/cancel`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="remarks" value="${remarks}">
        `;

        form.submit(); // Submit the form (will follow Laravel's redirect)
    }

    /**
     * Display activity history in timeline format
     */
    function displayActivityHistory(activities, container) {
        if (!activities || activities.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">No activity history found</div>';
            return;
        }

        let html = '<div class="space-y-4">';
        
        activities.forEach((activity, index) => {
            const date = new Date(activity.created_at);
            const formattedDate = date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
            const formattedTime = date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });

            // Determine activity color and icon based on event type
            let colorClass = 'bg-blue-500';
            let icon = 'fas fa-circle';
            
            if (activity.event_type === 'created' || activity.description.toLowerCase().includes('created')) {
                colorClass = 'bg-green-500';
                icon = 'fas fa-plus-circle';
            } else if (activity.event_type === 'updated' || activity.description.toLowerCase().includes('updated') || activity.description.toLowerCase().includes('edited')) {
                colorClass = 'bg-blue-500';
                icon = 'fas fa-edit';
            } else if (activity.description.toLowerCase().includes('adjustment')) {
                colorClass = 'bg-yellow-500';
                icon = 'fas fa-file-edit';
            } else if (activity.description.toLowerCase().includes('purchase order')) {
                colorClass = 'bg-purple-500';
                icon = 'fas fa-file-invoice';
            } else if (activity.event_type === 'deleted' || activity.description.toLowerCase().includes('deleted')) {
                colorClass = 'bg-red-500';
                icon = 'fas fa-trash';
            }

            html += `
                <div class="flex gap-3 ${index !== activities.length - 1 ? 'border-l-2 border-gray-300 dark:border-gray-600 ml-2 pb-4' : ''}">
                    <div class="relative">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 ${colorClass} rounded-full flex items-center justify-center">
                            <i class="${icon} text-white text-[8px]"></i>
                        </div>
                    </div>
                    <div class="flex-1 ml-6">
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${activity.description}</p>
                                    ${activity.user ? `<p class="text-xs text-gray-600 dark:text-gray-400 mt-1">by ${activity.user.name}</p>` : ''}
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${formattedDate}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${formattedTime}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }

    // Validate PO amount against balance
    function validateAmountPO(inputElement) {
        const maxBalance = parseFloat(inputElement.dataset.balance || "0");
        const inputValue = parseFloat(inputElement.value || "0");

        if (inputValue > maxBalance) {
            inputElement.value = maxBalance.toFixed(2);
            inputElement.title = `Max allowed is ₱${maxBalance.toFixed(2)}`;
        }
        updatePOAmountTotal();
    }

    // Update PO amount total
    function updatePOAmountTotal() {
        const poInputs = document.querySelectorAll("input[name^='po_amount']");
        let total = 0;
        poInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                total += val;
            }
        });
        const totalCell = document.getElementById('poAmountTotalCell');
        if (totalCell) {
            totalCell.textContent = total.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    // Validate PO form before submission
    function validateFormCreatePO() {
        const po_number = document.getElementById('po_number');
        const pr_no = document.getElementById('pr_no');
        const delivery_period = document.getElementById('delivery_period');
        const supplier = document.getElementById('supplier');
        const poInputs = document.querySelectorAll("input[name^='po_amount']");

        let atLeastOnePOFilled = false;
        let isValid = true;

        // Validate PO Number
        if (!po_number.value.trim()) {
            document.getElementById('po_numberError').innerText = 'PO Number is required.';
            isValid = false;
        } else {
            document.getElementById('po_numberError').innerText = '';
        }
        
        // Validate PR Number
        if (!pr_no.value.trim()) {
            document.getElementById('pr_noError').innerText = 'PR Number is required.';
            isValid = false;
        } else {
            document.getElementById('pr_noError').innerText = '';
        }
        
        // Validate Delivery Period
        if (!delivery_period.value.trim()) {
            document.getElementById('delivery_periodError').innerText = 'Delivery Period is required.';
            isValid = false;
        } else {
            document.getElementById('delivery_periodError').innerText = '';
        }
        
        // Validate Supplier
        if (!supplier.value.trim()) {
            document.getElementById('supplierError').innerText = 'Supplier is required.';
            isValid = false;
        } else {
            document.getElementById('supplierError').innerText = '';
        }

        // Ensure at least one po_amount is filled
        poInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                atLeastOnePOFilled = true;
            }
        });

        // Validate po_amount fields and require at least one valid entry
        if (!atLeastOnePOFilled) {
            document.getElementById('tableMessagePO').classList.remove('hidden');
            document.getElementById('tableMessagePO').innerText = 'Enter at least one valid Purchase Order amount.';
            isValid = false;
        } else {
            document.getElementById('tableMessagePO').classList.add('hidden');
            document.getElementById('tableMessagePO').innerText = '';
        }

        if (isValid) {
            submitPurchaseOrderForm();
        }
    }

    /**
     * Show success alert in modal
     */
    function showPOSuccessMessage(message) {
        const modal = document.getElementById('createPOModal');
        if (!modal) return;

        // Remove any existing success message
        const existingAlert = modal.querySelector('.po-success-alert');
        if (existingAlert) existingAlert.remove();

        // Create success message element
        const alertDiv = document.createElement('div');
        alertDiv.className = 'po-success-alert bg-green-50 border-2 border-green-300 text-green-800 px-4 py-3 rounded-lg mb-4 dark:bg-green-900 dark:border-green-600 dark:text-green-100 flex items-start gap-3';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl flex-shrink-0 mt-0.5"></i>
            <div class="flex-1">
                <p class="font-semibold">${message}</p>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 flex-shrink-0">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Insert at the top of modal content
        const modalContent = modal.querySelector('.relative.bg-white');
        if (modalContent) {
            modalContent.insertBefore(alertDiv, modalContent.firstChild);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.style.transition = 'opacity 0.5s';
                    alertDiv.style.opacity = '0';
                    setTimeout(() => alertDiv.remove(), 500);
                }
            }, 5000);
        }
    }

    /**
     * Submit purchase order form via AJAX
     */
    function submitPurchaseOrderForm() {
        const form = document.getElementById('CreatePurchaseOrderForm');
        const formData = new FormData(form);
        const submitUrl = form.action;

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
        }

        fetch(submitUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message in modal
                showPOSuccessMessage(data.message || 'Purchase Order created successfully!');
                
                // Reset the form
                form.reset();
                
                // Clear any validation messages
                const tableMessage = document.getElementById('tableMessagePO');
                if (tableMessage) {
                    tableMessage.classList.add('hidden');
                    tableMessage.innerText = '';
                }
                
                // Recalculate totals
                updatePOAmountTotal();
            } else {
                // Show error message
                alert(data.message || 'Failed to create Purchase Order');
            }
        })
        .catch(error => {
            console.error('Error submitting purchase order:', error);
            alert('An error occurred while submitting the form. Please try again.');
        })
        .finally(() => {
            // Restore button state
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }

    // Close context menu when clicking elsewhere
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('accountObligationsContextMenu');
        if (menu && !event.target.closest('#accountObligationsContextMenu')) {
            hideAccountObligationContextMenu();
        }
    });

    /**
     * Setup card click handlers to highlight corresponding table columns
     */
    function setupCardClickHandlers() {
        // Mapping of card types to column header text patterns
        const cardToColumnHeader = {
            'approved_appropriations': 'Approved Appropriations',
            'supplemental_appropriations': 'Supplemental Appropriations',
            'reversions': 'Reversions',
            'realignments': 'Realignments',
            'authorized_appropriations': 'Authorized Appropriations',
            'allotments': 'Allotments',
            'for_later_release': 'For Later Release',
            'obligations': 'Obligations',
            'balance_appropriations': 'Authorized Approp. Balance',
            'appropriation_accomplishment': 'Appropriations Utilization',
            'balance_allotments': 'Allotments Balance',
            'allotment_accomplishment': 'Allotments Utilization',
            'disbursements': 'Disbursements',
            'disbursement_balance': 'Obligations Balance',
            'disbursements_to_obligations': 'Disbursements / Oblgations',
            'disbursements_to_appropriations': 'Disbursements / Approp.'
        };

        const cards = document.querySelectorAll('[data-card]');
        
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking toggle button
                if (e.target.closest('button')) return;
                
                // Scroll to table
                const table = document.getElementById('accountsTable');
                if (table) {
                    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Get the card type and find the corresponding header
                    const cardType = this.dataset.card;
                    const headerText = cardToColumnHeader[cardType];
                    
                    // Highlight the corresponding column
                    setTimeout(() => {
                        const headerCells = table.querySelectorAll('thead th');
                        let columnIndex = -1;
                        
                        // Find the column index by matching header text (exact match preferred)
                        headerCells.forEach((header, index) => {
                            const cellText = header.textContent.trim();
                            // Try exact match first
                            if (cellText === headerText) {
                                columnIndex = index;
                            }
                            // If no exact match and it's a partial match, use it as fallback
                            else if (columnIndex === -1 && cellText.includes(headerText)) {
                                columnIndex = index;
                            }
                        });
                        
                        if (columnIndex === -1) {
                            console.warn(`Column header not found for: ${headerText}`);
                            return;
                        }
                        
                        // Highlight header
                        headerCells[columnIndex].classList.add('highlight-column');
                        setTimeout(() => {
                            headerCells[columnIndex].classList.remove('highlight-column');
                        }, 1500);
                        
                        // Highlight all cells in the column
                        const bodyCells = table.querySelectorAll('tbody td');
                        bodyCells.forEach((cell, index) => {
                            if (index % (headerCells.length) === columnIndex) {
                                cell.classList.add('highlight-column');
                                setTimeout(() => {
                                    cell.classList.remove('highlight-column');
                                }, 1500);
                            }
                        });
                    }, 500);
                }
            });
        });
    }

    // Initialize card click handlers when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        setupCardClickHandlers();
        
        // Set up search input listener and apply initial filter
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            // Use a small delay to ensure DOM is fully rendered
            setTimeout(function() {
                // Apply filter if there's an initial search value
                if (searchInput.value.trim()) {
                    filterTable(searchInput.value);
                }
            }, 100);
            
            // Listen for search input changes
            searchInput.addEventListener('input', function() {
                filterTable(this.value);
            });
        }
    });

    window.handleAccountMenuOption = handleAccountMenuOption;
    window.showAccountObligationsModal = showAccountObligationsModal;
    window.closeAccountObligationsModal = closeAccountObligationsModal;
    window.openAccountsCancellationModal = openAccountsCancellationModal;
    window.closeAccountsCancellationModal = closeAccountsCancellationModal;
    window.proceedAccountsCancellation = proceedAccountsCancellation;

// Override filterTable to include animations
const originalFilterTable = window.filterTable;
if (typeof originalFilterTable === 'function') {
    window.filterTable = function(searchValue) {
        const rows = document.querySelectorAll('#accountsTable tbody tr');
        const lowerSearch = String(searchValue).toLowerCase();

        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(lowerSearch)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        updateCardValuesAnimated();
        updateGraphAnimated();
    };
}

    /**
     * Initialize charts for Account Analytics Panel
     */
    function initializeAccountAnalyticsCharts() {
        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#d1d5db' : '#6b7280';
        const gridColor = isDarkMode ? '#4b5563' : '#e5e7eb';
        const bgColor = isDarkMode ? '#111827' : '#ffffff';
        
        // Prepare data from server-side collections
        @php
        $histogramCountData = isset($obligationRanges) ? array_map(function($r) { return $r['count']; }, $obligationRanges) : array_fill(0, 6, 0);
        $quarterCategories = isset($obligationsByQuarter) ? array_map(function($q) { return $q['quarter']; }, $obligationsByQuarter) : array_fill(0, 4, 'Q0');
        $quarterCountData = isset($obligationsByQuarter) ? array_map(function($q) { return $q['count']; }, $obligationsByQuarter) : array_fill(0, 4, 0);
        @endphp

        // Obligation Distribution Histogram
        const histogramData = {
            categories: [
                '< 10K',
                '10K - 50K',
                '50K - 100K',
                '100K - 500K',
                '500K - 1M',
                '> 1M'
            ],
            series: [{
                name: 'Count',
                data: @json($histogramCountData)
            }]
        };

        const histogramOptions = {
            chart: {
                type: 'bar',
                height: 260,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    distributed: true,
                    dataLabels: { position: 'top' }
                }
            },
            colors: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#ec4899'],
            dataLabels: {
                enabled: true,
                formatter: function(val) { return val; },
                style: { colors: [textColor] }
            },
            xaxis: { 
                categories: histogramData.categories,
                labels: {
                    style: { colors: textColor }
                },
                axisBorder: { color: gridColor }
            },
            yaxis: { 
                title: { text: 'Count', style: { color: textColor } },
                labels: {
                    formatter: function(val) { return Math.floor(val); },
                    style: { colors: textColor }
                }
            },
            tooltip: {
                y: { formatter: function(val) { return val + ' obligations'; } },
                theme: isDarkMode ? 'dark' : 'light',
                style: {
                    backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                    color: isDarkMode ? '#f3f4f6' : '#111827'
                }
            },
            grid: { borderColor: gridColor },
            legend: { show: false }
        };

        if (document.getElementById('accountObligationHistogram')) {
            new ApexCharts(document.getElementById('accountObligationHistogram'), { 
                ...histogramOptions, 
                series: [{
                    name: 'Obligations',
                    data: histogramData.series[0].data
                }]
            }).render();
        }

        // Obligations by Quarter Line Chart
        const obligationsByQuarter = @json($obligationsByQuarter);
        const categories = obligationsByQuarter.map(q => q.quarter);
        const counts = obligationsByQuarter.map(q => q.count);
        
        const quarterlyData = {
            categories: categories,
            series: [{
                name: 'Obligations Created',
                data: counts
            }]
        };

        const quarterlyOptions = {
            chart: {
                type: 'line',
                height: 260,
                toolbar: { show: false }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#3b82f6'],
            markers: {
                size: 6,
                colors: ['#3b82f6'],
                strokeWidth: 2,
                fillOpacity: 1
            },
            xaxis: { 
                categories: quarterlyData.categories,
                labels: {
                    style: { colors: textColor }
                },
                axisBorder: { color: gridColor }
            },
            yaxis: {
                title: { text: 'Number of Obligations', style: { color: textColor } },
                labels: {
                    formatter: function(val) { return Math.floor(val); },
                    style: { colors: textColor }
                }
            },
            tooltip: {
                y: { formatter: function(val) { return val + ' obligations created'; } },
                theme: isDarkMode ? 'dark' : 'light',
                style: {
                    backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                    color: isDarkMode ? '#f3f4f6' : '#111827'
                }
            },
            grid: { 
                borderColor: gridColor,
                strokeDashArray: 5
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 0.1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            }
        };

        if (document.getElementById('accountObligationsByQuarter')) {
            new ApexCharts(document.getElementById('accountObligationsByQuarter'), { 
                ...quarterlyOptions, 
                series: quarterlyData.series 
            }).render();
        }
    }
    </script>

    <style>
        /* ApexCharts Dark Mode Styling */
        .dark #accountObligationHistogram,
        .dark #accountObligationsByQuarter {
            background-color: #111827 !important;
        }

        .dark #accountObligationHistogram .apexcharts-canvas {
            background-color: #111827 !important;
        }

        .dark #accountObligationsByQuarter .apexcharts-canvas {
            background-color: #111827 !important;
        }

        .dark svg[data-testid="apexcharts-svg"] {
            background-color: #111827 !important;
        }

        .apexcharts-tooltip {
            background-color: #1f2937 !important;
            border: 1px solid #374151 !important;
        }

        .apexcharts-tooltip-title {
            background-color: #111827 !important;
            color: #f3f4f6 !important;
            border-color: #374151 !important;
        }

        .apexcharts-tooltip-series-group {
            background-color: #1f2937 !important;
        }

        .apexcharts-tooltip-text {
            color: #f3f4f6 !important;
        }

        .apexcharts-tooltip.apexcharts-theme-light {
            background-color: #ffffff !important;
        }

        .dark .apexcharts-tooltip.apexcharts-theme-dark {
            background-color: #1f2937 !important;
            color: #f3f4f6 !important;
        }

        .dark .apexcharts-tooltip-text {
            color: #f3f4f6 !important;
        }
    </style>

<!-- Include the Create Obligations Modal -->
@include('obligations.modal.create')
<!-- Include the Edit Obligations Modal -->
@include('obligations.modal.edit')

    <script>
    // Store office allotment class data for use in modals
    const currentPageOfficeAllotmentData = {
        officeAbbr: '{{ $officeAllotmentClasses->offices->office_abbreviation ?? "N/A" }}',
        officeName: '{{ $officeAllotmentClasses->offices->office_name ?? "N/A" }}',
        allotmentClass: '{{ $officeAllotmentClasses->class ?? "N/A" }}'
    };

    // Update the context menu handler to include the Obligate option
    document.addEventListener('DOMContentLoaded', function() {
        const accountsTable = document.getElementById('accountsTable');
        const contextMenu = document.getElementById('accountContextMenu');

        if (accountsTable) {
            accountsTable.addEventListener('contextmenu', function(event) {
                event.preventDefault();
                
                // Find the closest row
                const row = event.target.closest('tr');
                if (row && row.querySelector('td')) {
                    const appropriationId = row.dataset.appropriationId;
                    const accountCode = row.getAttribute('data-account-code');
                    const programs = row.querySelector('td:nth-child(1)')?.textContent?.trim();
                    const description = row.getAttribute('data-description');
                    
                    // Store context including office allotment class ID from the page
                    currentAccountAppropriation = {
                        accountCode: accountCode,
                        description: description,
                        appropriationId: appropriationId,
                        programs: programs,
                        officeAllotmentClassId: '{{ $officeAllotmentClasses->id }}' // Add this
                    };
                    
                    // Position the context menu
                    contextMenu.style.left = event.clientX + 'px';
                    contextMenu.style.top = event.clientY + 'px';
                    contextMenu.classList.remove('hidden');
                }
            });

            // Hide context menu on click
            document.addEventListener('click', function(e) {
                if (!contextMenu.contains(e.target) && !e.target.closest('tr')) {
                    contextMenu.classList.add('hidden');
                }
            });
        }
        
        // Handle the Obligate context menu option
        const contextObligate = document.getElementById('contextObligate');
        if (contextObligate) {
            contextObligate.addEventListener('click', function(e) {
                e.preventDefault();
                const contextMenu = document.getElementById('accountContextMenu');
                contextMenu.classList.add('hidden');
                
                if (currentAccountAppropriation && currentAccountAppropriation.officeAllotmentClassId) {
                    // Call with appropriation ID and account code to pre-populate first row
                    openCreateModalWithAppropriation(
                        currentAccountAppropriation.officeAllotmentClassId,
                        currentAccountAppropriation.appropriationId,
                        currentAccountAppropriation.accountCode
                    );
                }
            });
        }
    });

    // Handle modal reopening after successful save from accounts page
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('reopen_modal') && session('preselected_class_id'))
            // Check if we have a preselected appropriation from session
            const preselectedAppropriationId = '{{ session("preselected_appropriation_id") }}';
            const preselectedAccountCode = '{{ session("preselected_account_code") }}';
            
            setTimeout(function() {
                // Reopen with appropriation details if available
                if (preselectedAppropriationId && preselectedAccountCode) {
                    openCreateModalWithAppropriation(
                        {{ session('preselected_class_id') }},
                        preselectedAppropriationId,
                        preselectedAccountCode
                    );
                } else {
                    openCreateModal({{ session('preselected_class_id') }});
                }
                
                // Clear form fields except office allotment class and preselected appropriation
                setTimeout(function() {
                    // Clear other table rows except the first one if it has preselected data
                    const tableBody = document.querySelector('#programs_table tbody');
                    const rows = tableBody.querySelectorAll('tr');
                    
                    if (rows.length > 1) {
                        // Keep only the first row if we have a preselected appropriation
                        if (preselectedAppropriationId) {
                            for (let i = 1; i < rows.length; i++) {
                                rows[i].remove();
                            }
                        } else {
                            // Clear all rows
                            rows.forEach((row, index) => {
                                if (index === 0) {
                                    row.querySelectorAll('[name="account_code[]"], [name="amount_of_obligation[]"]').forEach(field => field.value = '');
                                } else {
                                    row.remove();
                                }
                            });
                        }
                    } else if (rows.length === 1 && !preselectedAppropriationId) {
                        // Clear the first row if no preselection
                        rows[0].querySelectorAll('[name="account_code[]"], [name="amount_of_obligation[]"]').forEach(field => field.value = '');
                    }
                    
                    // Clear other fields
                    const obrDateField = document.getElementById('obr_date');
                    if (obrDateField) {
                        const yearFilter = document.getElementById('year1');
                        const selectedYear = yearFilter ? yearFilter.value : new Date().getFullYear();
                        const currentYear = new Date().getFullYear();
                        
                        if (selectedYear == currentYear) {
                            obrDateField.value = new Date().toISOString().split('T')[0];
                        } else {
                            obrDateField.value = selectedYear + '-12-31';
                        }
                    }
                    
                    const particularsField = document.getElementById('particulars');
                    if (particularsField) particularsField.value = '';
                    
                    const remarksField = document.getElementById('remarks');
                    if (remarksField) remarksField.value = '';
                    
                    // Reset OBR type to first valid option for the selected class
                    const obrTypeSelect = document.getElementById('obr_type');
                    if (obrTypeSelect && obrTypeSelect.options.length > 1) {
                        obrTypeSelect.selectedIndex = 1;
                    }
                    
                    // Generate new OBR number
                    if (typeof generateObrNumber === 'function') {
                        generateObrNumber();
                    }
                    
                    // Update text colors
                    if (typeof updateTextColor === 'function') {
                        document.querySelectorAll('input, select, textarea').forEach(element => {
                            updateTextColor(element);
                        });
                    }
                    
                    // Reset total obligation
                    if (typeof calculateTotalObligation === 'function') {
                        calculateTotalObligation();
                    }
                }, 300);
            }, 800);
        @endif
    });

    /**
     * Filter obligations table based on search input
     */
    function filterObligationsTable(searchValue, source = 'dashboard') {
        const lowerSearch = String(searchValue).toLowerCase();
        const contentId = source === 'dashboard' ? 'obligationsContent' : 'accountObligationsContent';
        const content = document.getElementById(contentId);
        
        // Get date range values
        const dateFromSelector = source === 'dashboard' ? 'obligationsDateFrom' : 'accountObligationsDateFrom';
        const dateToSelector = source === 'dashboard' ? 'obligationsDateTo' : 'accountObligationsDateTo';
        const dateFromInput = document.getElementById(dateFromSelector);
        const dateToInput = document.getElementById(dateToSelector);
        
        const dateFrom = dateFromInput ? dateFromInput.value : '';
        const dateTo = dateToInput ? dateToInput.value : '';
        
        if (!content) return;
        
        const rows = content.querySelectorAll('tbody tr.obligation-row');
        let visibleCount = 0;
        let totalAmount = 0;
        let totalPurchaseOrder = 0;
        
        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const cells = row.querySelectorAll('td');
            let dateCell = cells[2]?.textContent || ''; // Date is in column 2
            
            // Parse the date string (format: "Jan 15, 2026" or similar)
            let rowDate = null;
            try {
                rowDate = new Date(dateCell);
            } catch (e) {
                rowDate = null;
            }
            
            // Check search filter
            const matchesSearch = searchValue === '' || rowText.includes(lowerSearch);
            
            // Check date range filter
            let matchesDateRange = true;
            if (rowDate && (dateFrom || dateTo)) {
                const checkFrom = dateFrom ? new Date(dateFrom) : null;
                const checkTo = dateTo ? new Date(dateTo) : null;
                
                if (checkFrom && rowDate < checkFrom) {
                    matchesDateRange = false;
                }
                if (checkTo && rowDate > checkTo) {
                    matchesDateRange = false;
                }
            }
            
            if (matchesSearch && matchesDateRange) {
                row.style.display = '';
                visibleCount++;
                
                // Calculate totals for visible rows
                if (cells.length >= 6) {
                    // Dashboard: Amount in index 4, PO in index 5
                    // Accounts: Amount in index 5, PO in index 6
                    let amountIndex = 4;
                    let poIndex = 5;
                    
                    if (source === 'accounts' && cells.length >= 7) {
                        amountIndex = 5;
                        poIndex = 6;
                    }
                    
                    // Extract and clean amount values
                    const amountText = (cells[amountIndex]?.textContent || '0').replace(/,/g, '').trim();
                    const amountValue = parseFloat(amountText);
                    if (!isNaN(amountValue)) {
                        totalAmount += amountValue;
                    }
                    
                    const poText = (cells[poIndex]?.textContent || '0').replace(/,/g, '').trim();
                    const poValue = parseFloat(poText);
                    if (!isNaN(poValue)) {
                        totalPurchaseOrder += poValue;
                    }
                }
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update the footer with new totals
        const tableContainer = content.querySelector('div.overflow-x-auto');
        if (tableContainer) {
            const table = tableContainer.querySelector('table');
            if (table) {
                const footer = table.querySelector('tfoot');
                if (footer) {
                    const footerCells = footer.querySelectorAll('td');
                    if (footerCells.length >= 4) {
                        // Update Total Records (first cell)
                        footerCells[0].textContent = `Total Records: ${visibleCount} ${visibleCount === 1 ? 'record' : 'records'}`;
                        
                        // Update Amount total (3rd cell, index 2)
                        if (!isNaN(totalAmount)) {
                            footerCells[2].textContent = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                        
                        // Update PO total (4th cell, index 3)
                        if (!isNaN(totalPurchaseOrder)) {
                            footerCells[3].textContent = totalPurchaseOrder > 0 ? totalPurchaseOrder.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                        }
                    }
                }
            }
        }
        
        // Show "no results" message if nothing found
        let noResultsDiv = content.querySelector('.no-search-results');
        
        if (visibleCount === 0 && searchValue && tableContainer) {
            if (!noResultsDiv) {
                noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-search-results text-center py-8 text-gray-500 italic dark:text-gray-400';
                noResultsDiv.textContent = 'No obligations found matching your search.';
                tableContainer.parentElement.insertBefore(noResultsDiv, tableContainer.nextSibling);
            }
            noResultsDiv.style.display = 'block';
            if (tableContainer) tableContainer.style.display = 'none';
        } else {
            if (noResultsDiv) noResultsDiv.style.display = 'none';
            if (tableContainer) tableContainer.style.display = 'block';
        }
    }

    </script>

    @include('obligations.modal.edit')
    
    <!-- Include Obligation Adjustments Create Modal -->
    @include('obligation_adjustments.modal.create')
    
    <!-- Include Purchase Order Modal -->
    @include('obligations.modal.purchase_order', ['obligation' => (object)['id' => null]])
    
    <!-- Obligation History Modal -->
    <div id="obligationHistoryModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-900 dark:to-slate-900 border-b-2 border-gray-200 dark:border-gray-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-history text-gray-600 dark:text-gray-300 text-xl"></i>
                    <div>
                        <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-gray-100">
                            Obligation Status/History
                        </h3>
                        <span id="historyObligationInfo" class="text-xs text-gray-600 dark:text-gray-400"></span>
                    </div>
                </div>
                <button type="button" onclick="closeObligationHistoryModal()" class="text-gray-600 dark:text-gray-300 hover:text-white hover:bg-gray-600 dark:hover:bg-gray-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body (scrollable) -->
            <div id="historyContent" class="overflow-y-auto flex-1 max-h-[calc(90vh-240px)] p-6 space-y-3">
                <div class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-500"></div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                <button type="button" onclick="closeObligationHistoryModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal for Accounts -->
    <form id="accountsCancelObligationForm" method="POST">
        <div id="accountsCancellationModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10004] flex items-center justify-center bg-black bg-opacity-50">
            <div class="flex flex-col max-h-[90vh] w-full max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
                <!-- Modal header -->
                <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900 dark:to-violet-900 border-b-2 border-purple-200 dark:border-purple-700 rounded-t-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-ban text-purple-600 dark:text-purple-300 text-xl"></i>
                        <h3 class="text-lg leading-6 font-semibold text-purple-900 dark:text-purple-100">
                            Cancel Obligation
                        </h3>
                    </div>
                    <button type="button" onclick="closeAccountsCancellationModal()" class="text-purple-600 dark:text-purple-300 hover:text-white hover:bg-purple-600 dark:hover:bg-purple-700 rounded-full p-2 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Modal body (scrollable) -->
                <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)] p-6">
                    <input type="hidden" id="accountsHiddenObligationId" name="obligation_id" value="">
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">
                        Do you want to proceed with the cancellation of this Obligation? If cancelled, the obligation amount will be set to zero.
                    </p>

                    <div class="mt-4">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <tbody>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR Date:</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="obr_date"></td>
                                </tr>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Office Abbreviation:</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="office_abbreviation"></td>
                                </tr>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Allotment Class:</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="allotment_class"></td>
                                </tr>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR No:</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="obr_no"></td>
                                </tr>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR Type:</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="obr_type"></td>
                                </tr>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Particulars:</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="particulars"></td>
                                </tr>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Obligation Amount:</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="obr_amount"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <label for="accountsCancellationRemarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remarks:</label>
                        <textarea id="accountsCancellationRemarks" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="3" placeholder="Enter remarks..."></textarea>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                    <button type="button" onclick="proceedAccountsCancellation()" class="text-red-600 dark:text-red-400 inline-flex leading-4 tracking-wider hover:text-white border border-red-600 dark:border-red-500 hover:bg-red-600 dark:hover:bg-red-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                        <i class="fas fa-window-close mr-2"></i>
                        Proceed
                    </button>
                    <button type="button" onclick="closeAccountsCancellationModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Obligation Details Modal -->
    @include('obligations.modal.obligation_details')
</x-app-layout>