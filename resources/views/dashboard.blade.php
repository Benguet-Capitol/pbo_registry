<x-app-layout>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none; }
        .dashboard-content {
            animation: fadeIn 0.6s ease-out;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                Dashboard | Current Balances

                @php
                $filters = [];

                if (!empty($selectedOfficeName)) $filters[] = $selectedOfficeName;
                if (!empty($selectedAllotmentClassDesc)) $filters[] = $selectedAllotmentClassDesc;
                if (!empty($selectedGroup)) $filters[] = $selectedGroup;
                if (!empty($selectedFundType)) $filters[] = $selectedFundType;
                if (!empty($selectedFund)) $filters[] = $selectedFund;
                if (request('from_date') || request('to_date')) {
                    $fromDate = request('from_date') ? date('M d, Y', strtotime(request('from_date'))) : 'Start';
                    $toDate = request('to_date') ? date('M d, Y', strtotime(request('to_date'))) : 'End';
                    $filters[] = "$fromDate - $toDate";
                }
                @endphp

                @if (count($filters) > 0)
                | <span class="text-blue-800 dark:text-blue-400">{{ implode(' / ', $filters) }}</span>
                    @endif
                    <span class="text-blue-800 dark:text-blue-400">
                        (CY {{ $selectedYear }})
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

    <div class="dashboard-content">
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

    <!-- Unified Filter Section -->
     <div class="bg-white p-4 rounded-lg shadow-md mb-4 dark:bg-gray-800">
    <form method="GET" action=""  id="filterForm">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100">Filters</h4>
            <button type="button" onclick="toggleFilters()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-chevron-down" id="filterToggle"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-center mb-2" id="filterContent" style="display: none;">

            <!-- Year Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="year1"
                    id="year1"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    data-default="{{ date('Y') }}"
                    onchange="this.form.submit()">
                    @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ request('year1', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Office Filter - Hidden for Guest role -->
            @if(!$isGuest)
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="office_filter"
                    id="office_filter"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    data-default=""
                    onchange="this.form.submit()">
                    <option value="">All Office</option>

                    @foreach($offices as $office)
                    <option value="{{ $office->id }}" {{ request('office_filter') == $office->id ? 'selected' : '' }}>{{ $office->office_abbreviation }}</option>
                    @endforeach

                </x-form.select>
            </div>
            @endif
            <!-- Allotment Class Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="allotment_class_filter"
                    id="allotment_class_filter"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    data-default=""
                    onchange="this.form.submit()">
                    <option value="">All Allotment Class</option>
                    @foreach($allotmentClasses as $class)
                    <option value="{{ $class->class }}" {{ request('allotment_class_filter') == $class->class ? 'selected' : '' }}>{{ $class->allotmentClass->description }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Group Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="group_filter"
                    id="group_filter"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    data-default=""
                    onchange="this.form.submit()">
                    <option value="">All Group</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch }}" {{ request('group_filter') == $branch ? 'selected' : '' }}>{{ $branch }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Fund Type Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="fund_type_filter"
                    id="fund_type_filter"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    data-default=""
                    onchange="this.form.submit()">
                    <option value="">All Fund Type</option>
                    @foreach($fundTypes as $fundType)
                    <option value="{{ $fundType }}" {{ request('fund_type_filter') == $fundType ? 'selected' : '' }}>{{ $fundType }}</option>
                    @endforeach

                </x-form.select>
            </div>
            <!-- Fund Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="fund_filter"
                    id="fund_filter"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    data-default=""
                    onchange="this.form.submit()">
                    <option value="">All Fund</option>
                    @foreach($funds as $fund)
                    <option value="{{ $fund }}" {{ request('fund_filter') == $fund ? 'selected' : '' }}>{{ $fund }}</option>
                    @endforeach

                </x-form.select>
            </div>

            <!-- Date Range Filter Row -->
            <!-- From Date Filter -->
            <div class="flex items-center space-x-2">
                <label for="fromDate" class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-2">From Date</label>
                <x-form.input type="date" name="from_date" id="fromDate" value="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
            </div>

            <!-- To Date Filter -->
            <div class="flex items-center space-x-2">
                <label for="toDate" class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-2">To Date</label>
                <x-form.input type="date" name="to_date" id="toDate" value="{{ request('to_date') }}" min="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
            </div>

            <!-- Apply Filter Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-filter mr-2"></i>Apply Date Filter
                </button>
            </div>
        </div>
    </form>
    <!-- Search Input - Always Visible -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-center">
     <!-- Search Input -->
            <div class="flex items-center space-x-2 lg:col-span-3">
                <x-form.input type="text" name="search" id="searchInput" value="{{ session('search') ?? request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
            </div>
    </div>
    </div>

    {{-- Insights & Analytics Panel --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 mb-4">
        <div class="flex justify-between items-center mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                Insights & Analytics
            </h3>
            <button onclick="toggleWidget('analyticsPanel')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-chevron-down" id="analyticsPanelToggle"></i>
            </button>
        </div>

        <div id="analyticsPanelContent" style="display: none;">

            <!-- 1. Allotment Class Distribution Graph -->
            <div class="mb-6">
                <h4 class="text-md font-semibold text-gray-800 dark:text-gray-100 mb-3">Allotment Class Distribution by Authorized Appropriations</h4>
                
                <!-- Stacked Bar - Reduced height -->
                <div id="stackedBarContainer" class="mb-4 relative">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 overflow-visible flex relative">
                        @php
                            // Calculate total authorized appropriations for percentage calculation
                            $totalForPercentage = $officeAllotmentClasses->sum('authorized_appropriations');
                            
                            // Group by allotment class and sum authorized appropriations
                            $classDistribution = $officeAllotmentClasses->groupBy('class')->map(function($items) {
                                return [
                                    'description' => $items->first()->allotmentClass->description ?? 'Unknown',
                                    'total' => $items->sum('authorized_appropriations'),
                                    'class_code' => $items->first()->class
                                ];
                            })->sortByDesc('total');
                            
                            // Fixed color assignments
                            $fixedColors = [
                                'PS' => ['color' => 'bg-blue-500', 'hover' => 'hover:bg-blue-600'],
                                'MOOE' => ['color' => 'bg-green-500', 'hover' => 'hover:bg-green-600'],
                                'CO' => ['color' => 'bg-cyan-500', 'hover' => 'hover:bg-cyan-600'],
                                'FE' => ['color' => 'bg-red-500', 'hover' => 'hover:bg-red-600'],
                                'CCO' => ['color' => 'bg-violet-500', 'hover' => 'hover:bg-violet-600'],
                            ];
                            
                            // Fallback colors for unknown classes
                            $fallbackColors = [
                                ['color' => 'bg-pink-600', 'hover' => 'hover:bg-pink-700'],
                                ['color' => 'bg-indigo-600', 'hover' => 'hover:bg-indigo-700'],
                                ['color' => 'bg-orange-600', 'hover' => 'hover:bg-orange-700'],
                                ['color' => 'bg-teal-600', 'hover' => 'hover:bg-teal-700'],
                                ['color' => 'bg-lime-600', 'hover' => 'hover:bg-lime-700'],
                                ['color' => 'bg-amber-600', 'hover' => 'hover:bg-amber-700'],
                            ];
                            
                            function getClassColors($classCode, $fixedColors, $fallbackColors) {
                                if (isset($fixedColors[$classCode])) {
                                    return $fixedColors[$classCode];
                                }
                                $index = abs(crc32($classCode)) % count($fallbackColors);
                                return $fallbackColors[$index];
                            }
                        @endphp

                        @foreach($classDistribution as $index => $class)
                            @php
                                $percentage = $totalForPercentage > 0 ? ($class['total'] / $totalForPercentage) * 100 : 0;
                                $colors = getClassColors($class['class_code'], $fixedColors, $fallbackColors);
                                $barColor = $colors['color'];
                                $hoverColor = $colors['hover'];
                            @endphp
                            
                            <div 
                                class="{{ $barColor }} {{ $hoverColor }} h-8 transition-all duration-200 ease-out flex items-center justify-center relative stacked-segment cursor-pointer"
                                style="width: {{ $percentage }}%"
                                data-class="{{ $class['class_code'] }}"
                                data-description="{{ $class['description'] }}"
                                data-total="{{ $class['total'] }}"
                                data-percentage="{{ $percentage }}"
                                onmouseenter="showTooltip(this)"
                                onmouseleave="hideTooltip(this)"
                            >
                                @if($percentage > 5)
                                    <span class="text-white text-xs font-semibold px-1 text-center truncate pointer-events-none">
                                        {{ $class['class_code'] }}
                                    </span>
                                @endif
                                
                                <!-- Tooltip -->
                                <div class="tooltip-box absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap shadow-xl" style="display: none; z-index: 9999;">
                                    <div class="font-semibold">{{ $class['class_code'] }} - {{ $class['description'] }}</div>
                                    <div>{{ number_format($percentage, 2) }}%</div>
                                    <div>{{ number_format($class['total'], 2) }}</div>
                                    <!-- Arrow -->
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Legend with amounts added -->
                <div id="graphLegend" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mx-auto justify-items-center">
                    @foreach($classDistribution as $index => $classItem)
                        @php
                            $percentage = $totalForPercentage > 0 ? ($classItem['total'] / $totalForPercentage) * 100 : 0;
                            $colors = getClassColors($classItem['class_code'], $fixedColors, $fallbackColors);
                            $barColor = $colors['color'];
                        @endphp
                        
                        <div class="flex items-center space-x-2 text-xs">
                            <div class="w-4 h-4 {{ $barColor }} rounded flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-gray-700 dark:text-gray-300 truncate">
                                    {{ $classItem['class_code'] }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    {{ number_format($percentage, 2) }}%
                                </div>
                                <div class="text-gray-600 dark:text-gray-400 text-[10px]">
                                    {{ number_format($classItem['total'], 2) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700 mb-4">

            <!-- 2. Activity Metrics Cards -->
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

            <!-- 3. Top 5 Highest Utilization -->
            @role('Disbursement|Administrator|Developer|Obligation')
            <div id="topPerformersWidget" class="mb-6">
                <h4 class="text-md font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center">
                    Top 5 Highest Utilization
                </h4>
                <div id="topPerformersContent" class="space-y-2">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700 mb-6">
            @endrole

            <!-- 4. Charts Row -->
            <div class="mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Obligation Distribution by Amount Range (Histogram) -->
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Obligation Distribution by Amount Range</h4>
                        <div id="obligationHistogram" class="h-64"></div>
                    </div>

                    <!-- Obligations by Quarter (Line Chart) -->
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Obligations Created by Quarter</h4>
                        <div id="obligationsByQuarter" class="h-64"></div>
                    </div>
                </div>
            </div>

            <!-- 5. Obligation Distribution Table -->
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
                <label for="dashboardTable" class="ml-4 block text-md font-semibold text-blue-800 dark:text-blue-400">Office Allotment Classes</label>
            </div>
            <div class="overflow-auto max-h-[720px] rounded-lg border border-gray-300 dark:border-gray-700">
                <table id="dashboardTable" class="w-full text-[11px] text-gray-700 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-200 border-t-2 border-b-2 border-gray-700">
                        <tr>
                            <th class="px-2 py-2 w-[70px] text-center">View Details</th>
                            <th class="px-2 py-2 w-[100px] text-center">Office</th>
                            <th class="px-2 py-2 w-[120px] text-center">Allotment Class</th>
                            @role('Disbursement|Administrator|Developer|Obligation')
                            <th class="px-2 py-2 w-[120px] text-center">Fund Type</th>
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
                            <th class="px-2 py-2 w-[100px] text-center">Authorized Appropriations Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Appropriation Utilization</th>
                            <th class="px-2 py-2 w-[100px] text-center">Allotments Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Allotments Utilization</th>
                            @role('Disbursement|Administrator|Developer|Guest')
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements</th>
                            <th class="px-2 py-2 w-[100px] text-center">Obligations Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements / Obligations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements / Approp.</th>
                            @endrole
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($officeAllotmentClasses as $class)
                        <tr 
                            class="bg-white border dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                            ondblclick="window.location.href='{{ route('dashboard.accounts', $class->id) }}'"
                            data-class-id="{{ $class->id }}"
                            data-year="{{ $selectedYear }}"
                            data-appropriations="{{ $class->appropriations_sum }}"
                            data-supplementals="{{ $class->supplemental_sum }}"
                            data-reversions="{{ $class->reversion_sum }}"
                            data-realignments="{{ $class->realignments_sum }}"
                            data-authorized-appropriations="{{ $class->authorized_appropriations }}"
                            data-allotments="{{ $class->allotments_sum }}"
                            data-for-later-release="{{ $class->for_later_release }}"
                            data-obligations="{{ $class->obligations_sum }}"
                            data-balance-appropriations="{{ $class->balance_appropriations }}"
                            data-balance-allotments="{{ $class->balance_allotments }}"
                            data-disbursements="{{ $class->disbursements_sum }}"
                            data-disbursement-balance="{{ $class->disbursement_balance }}"
                            data-appropriation-accomplishment="{{ $class->appropriation_accomplishment }}"
                            data-allotment-accomplishment="{{ $class->allotment_accomplishment }}"
                            data-disbursements-to-obligations="{{ $class->disbursements_to_obligations }}"
                            data-disbursements-to-appropriations="{{ $class->disbursements_to_appropriations }}"
                        >
                            <td class="px-1 py-2 text-center">
                                <div class="relative inline-block text-left">
                                    <!-- Dropdown Button -->
                                    <button onclick="toggleDropdown(this)"
                                        class="relative text-xs group px-2 py-1.5">
                                        <span class="fas fa-forward"></span>
                                        <!-- Tooltip -->
                                        <span class="absolute left-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                            {{ $class->offices->office_abbreviation ?? 'No Office' }} - {{ $class->allotmentClass->description ?? 'No Class' }}
                                        </span>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div class="absolute top-full left-0 mt-1 w-48 z-50 hidden dropdown-menu bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 border-2 border-blue-400 dark:border-blue-600 rounded-lg shadow-2xl origin-top-right">
                                        <a href="{{ route('dashboard.accounts', $class->id) }}" class="flex items-center px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-t-lg transition-colors duration-150 group">
                                            <i class="fas fa-stream mr-2 text-blue-600 dark:text-blue-400"></i> Accounts
                                        </a>
                                        <a href="#" onclick="openObligationsModalFromDropdown(event, {{ $class->id }})" class="flex items-center px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-b-lg transition-colors duration-150 cursor-pointer">
                                            <i class="fas fa-list-check mr-2 text-blue-600 dark:text-blue-400"></i> Obligations
                                        </a>
                                        <!--
                                        <a href="#" class="block px-4 py-2 text-xs hover:bg-gray-200 dark:hover:bg-gray-600">
                                            <i class="fas fa-file-contract mr-2"></i>Purchase Orders
                                        </a> -->
                                    </div>
                                </div>
                            </td>
                            <td class="px-1 py-2 text-center text-gray-900 dark:text-white font-bold">{{ $class->office_abbreviation }}</td>
                            <td class="px-1 py-2 text-center text-gray-900 dark:text-white font-bold">{{ $class->class }}</td>
                            @role('Disbursement|Administrator|Developer|Obligation')
                            <td class="px-1 py-2 text-center">{{ $class->fundSourceRelation->category ?? '-' }}</td>
                            <td class="px-1 py-2 text-center">{{ $class->fpp_code }}</td>
                            @endrole
                            <td class="px-1 py-2 text-right font-semibold text-blue-700 dark:text-blue-300">{{ number_format($class->appropriations_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right {{ $class->supplemental_sum != 0 ? 'text-green-600 font-semibold' : 'text-gray-600 dark:text-gray-300' }}">{{ number_format($class->supplemental_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right {{ $class->reversion_sum != 0 ? 'text-red-600 font-semibold' : 'text-gray-600 dark:text-gray-300' }}">{{ number_format($class->reversion_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right 
                                    {{ $class->realignments_sum < 0 ? 'text-red-600 font-semibold' : ($class->realignments_sum > 0 ? 'text-green-600 font-semibold' : 'text-gray-700 dark:text-gray-300') }}">
                                {{ number_format($class->realignments_sum, 2) }}
                            </td>
                            <td class="px-1 py-2 text-right font-semibold text-blue-700 dark:text-blue-300">{{ number_format($class->authorized_appropriations, 2) }}</td>
                            <td class="px-1 py-2 text-right font-semibold text-blue-700 dark:text-blue-300">{{ number_format($class->allotments_sum, 2) }}</td>
                            @role('Disbursement|Administrator|Developer|Obligation')
                            <td class="px-1 py-2 text-right">{{ number_format($class->for_later_release, 2) }}</td>
                            @endrole
                            <td class="px-1 py-2 text-right">{{ number_format($class->obligations_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right font-semibold text-blue-700 dark:text-blue-300">{{ number_format($class->balance_appropriations, 2) }}</td>
                            <!-- Appropriation Utilization -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($class->appropriation_accomplishment, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($class->appropriation_accomplishment, 2) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Appropriation Utilization</div>
                                        <div>{{ number_format($class->appropriation_accomplishment, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($class->obligations_sum, 2) }} / {{ number_format($class->authorized_appropriations, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-1 py-2 text-right font-semibold text-blue-700 dark:text-blue-300">{{ number_format($class->balance_allotments, 2) }}</td>
                            <!-- Allotments Utilization Cell -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($class->allotment_accomplishment, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($class->allotment_accomplishment, 2) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Allotments Utilization</div>
                                        <div>{{ number_format($class->allotment_accomplishment, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($class->obligations_sum, 2) }} / {{ number_format($class->allotments_sum, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            @role('Disbursement|Administrator|Developer|Guest')
                            <td class="px-1 py-2 text-right">{{ number_format($class->disbursements_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->disbursement_balance, 2) }}</td>
                            <!-- Disbursements / Obligations Cell (if role allowed) -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($class->disbursements_to_obligations, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($class->disbursements_to_obligations, 2) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Disbursements / Obligations</div>
                                        <div>{{ number_format($class->disbursements_to_obligations, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($class->disbursements_sum, 2) }} / {{ number_format($class->obligations_sum, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            <!-- Disbursements / Appropriations Cell (if role allowed) -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="relative w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-visible">
                                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-4 rounded-full transition-all duration-300"
                                            style="width: {{ min($class->disbursements_to_appropriations, 100) }}%">
                                        </div>
                                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[9px] font-semibold whitespace-nowrap pointer-events-none">{{ number_format($class->disbursements_to_appropriations, 2) }}%</span>
                                    </div>
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                                        <div class="font-semibold">Disbursements / Appropriations</div>
                                        <div>{{ number_format($class->disbursements_to_appropriations, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300">{{ number_format($class->disbursements_sum, 2) }} / {{ number_format($class->authorized_appropriations, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </td>
                            @endrole
                        </tr>
                        @empty
                            <tr>
                                <td colspan="21" class="px-1 py-2 text-center text-gray-500 dark:text-gray-400 italic">
                                    No Office Allotment Classes found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                @if ($perPage != 'all')
                {{ $officeAllotmentClasses->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- Dashboard Cards Row --}}
    <div class="mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-3 md:p-4 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="approved_appropriations">
                <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-file-circle-check text-blue-600 dark:text-blue-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Approved Appropriations
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalAppropriations, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="supplemental_appropriations">
                <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-plus-circle text-purple-600 dark:text-purple-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Supplemental Appropriations
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold break-words card-value
                        {{ $totalSupplementals > 0 
                            ? 'text-green-600 dark:text-green-400' 
                            : 'text-gray-800 dark:text-gray-100' }}">
                        {{ number_format($totalSupplementals, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="reversions">
                <div class="flex-shrink-0 bg-red-100 dark:bg-red-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-undo-alt text-red-600 dark:text-red-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Reversions
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold break-words card-value
                        {{ $totalReversions > 0 
                            ? 'text-red-600 dark:text-red-400' 
                            : 'text-gray-800 dark:text-gray-100' }}">
                        {{ number_format($totalReversions, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="realignments">
                <div class="flex-shrink-0 bg-orange-100 dark:bg-orange-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-random text-orange-600 dark:text-orange-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Realignments
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold break-words card-value
                        {{ $totalRealignments > 0 
                            ? 'text-green-600 dark:text-green-400' 
                            : ($totalRealignments < 0 
                                ? 'text-red-600 dark:text-red-400' 
                                : 'text-gray-800 dark:text-gray-100') }}">
                        {{ number_format($totalRealignments, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="authorized_appropriations">
                <div class="flex-shrink-0 bg-sky-100 dark:bg-sky-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-file-signature text-sky-600 dark:text-sky-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Authorized Appropriations
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalAuthorizedAppropriations, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="for_later_release">
                <div class="flex-shrink-0 bg-fuchsia-100 dark:bg-fuchsia-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-lock text-fuchsia-600 dark:text-fuchsia-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Unreleased Appropriations
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalForLaterRelease, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="allotments">
                <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-layer-group text-green-600 dark:text-green-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Allotments
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalAllotments, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="obligations">
                <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-shopping-cart text-yellow-600 dark:text-yellow-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Obligations
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalObligations, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="balance_appropriations">
                <div class="flex-shrink-0 bg-cyan-100 dark:bg-cyan-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-credit-card text-cyan-600 dark:text-cyan-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Authorized Appropriations Balance
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalAuthorizedAppropriationsBalance, 2) }}
                    </div>
                </div>
            </div>
            <!-- Authorized Appropriations Utilization Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center justify-between transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer relative group" data-card="appropriation_accomplishment">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-full p-2 sm:p-3">
                        <i class="fas fa-percent text-indigo-600 dark:text-indigo-300 text-lg sm:text-xl md:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                        <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                            Authorized Appropriations Utilization
                        </div>
                        <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                            {{ number_format($totalAuthorizedAppropriationsAccomplishment, 2) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Circular Progress -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="circular-progress" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="circular-progress-bg" cx="30" cy="30" r="24" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle class="circular-progress-bar" cx="30" cy="30" r="24" fill="none" stroke="#6366f1" stroke-width="6"
                                stroke-dasharray="{{ min($totalAuthorizedAppropriationsAccomplishment, 100) * 1.507 }} 150.7"
                                stroke-linecap="round"
                                transform="rotate(-90 30 30)"
                                data-percentage="{{ $totalAuthorizedAppropriationsAccomplishment }}"/>
                        <text x="30" y="35" text-anchor="middle" class="text-xs font-bold fill-gray-800 dark:fill-gray-100">
                            {{ number_format(min($totalAuthorizedAppropriationsAccomplishment, 100), 0) }}%
                        </text>
                    </svg>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="font-semibold">Appropriation Utilization</div>
                    <div class="text-indigo-300">{{ number_format($totalAuthorizedAppropriationsAccomplishment, 2) }}%</div>
                    <div class="text-gray-300 text-[10px] mt-1">Obligations: <span class="card-tooltip-obligations">{{ number_format($totalObligations, 2) }}</span></div>
                    <div class="text-gray-300 text-[10px]">Authorized Appropriations: <span class="card-tooltip-auth-approp">{{ number_format($totalAuthorizedAppropriations, 2) }}</span></div>
                    <!-- Arrow -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="balance_allotments">
                <div class="flex-shrink-0 bg-pink-100 dark:bg-pink-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-stream text-pink-600 dark:text-pink-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                        Allotments Balance
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($allotmentBalance, 2) }}
                    </div>
                </div>
            </div>
            <!-- Allotments Utilization Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center justify-between transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer relative group" data-card="allotment_accomplishment">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 bg-teal-100 dark:bg-teal-900 rounded-full p-2 sm:p-3">
                        <i class="fas fa-percentage text-teal-600 dark:text-teal-300 text-lg sm:text-xl md:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                        <div class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-400 font-semibold uppercase">
                            Allotments Utilization
                        </div>
                        <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                            {{ number_format($allotmentAccomplishment, 2) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Circular Progress -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="circular-progress" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="circular-progress-bg" cx="30" cy="30" r="24" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle class="circular-progress-bar" cx="30" cy="30" r="24" fill="none" stroke="#14b8a6" stroke-width="6"
                                stroke-dasharray="{{ min($allotmentAccomplishment, 100) * 1.507 }} 150.7"
                                stroke-linecap="round"
                                transform="rotate(-90 30 30)"
                                data-percentage="{{ $allotmentAccomplishment }}"/>
                        <text x="30" y="35" text-anchor="middle" class="text-xs font-bold fill-gray-800 dark:fill-gray-100">
                            {{ number_format(min($allotmentAccomplishment, 100), 0) }}%
                        </text>
                    </svg>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="font-semibold">Allotments Utilization</div>
                    <div class="text-teal-300">{{ number_format($allotmentAccomplishment, 2) }}%</div>
                    <div class="text-gray-300 text-[10px] mt-1">Obligations: <span class="card-tooltip-obligations-allot">{{ number_format($totalObligations, 2) }}</span></div>
                    <div class="text-gray-300 text-[10px]">Allotments: <span class="card-tooltip-allotments">{{ number_format($totalAllotments, 2) }}</span></div>
                    <!-- Arrow -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                </div>
            </div>
            @role('Disbursement|Administrator|Developer|Guest')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="disbursements">
                <div class="flex-shrink-0 bg-emerald-100 dark:bg-emerald-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-tasks text-emerald-600 dark:text-emerald-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Disbursements
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalDisbursements, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="disbursement_balance">
                <div class="flex-shrink-0 bg-rose-100 dark:bg-rose-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-shopping-basket text-rose-600 dark:text-rose-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Obligations Balance
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($disbursementBalance, 2) }}
                    </div>
                </div>
            </div>
            <!-- Disbursements / Obligations Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center justify-between transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer relative group" data-card="disbursements_to_obligations">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 bg-lime-100 dark:bg-lime-900 rounded-full p-2 sm:p-3">
                        <i class="fas fa-percentage text-lime-600 dark:text-lime-300 text-lg sm:text-xl md:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                        <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                            Disbursements / Obligations
                        </div>
                        <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                            {{ number_format($totalDisbursementsToObligations, 2) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Circular Progress -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="circular-progress" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="circular-progress-bg" cx="30" cy="30" r="24" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle class="circular-progress-bar" cx="30" cy="30" r="24" fill="none" stroke="#84cc16" stroke-width="6"
                                stroke-dasharray="{{ min($totalDisbursementsToObligations, 100) * 1.507 }} 150.7"
                                stroke-linecap="round"
                                transform="rotate(-90 30 30)"
                                data-percentage="{{ $totalDisbursementsToObligations }}"/>
                        <text x="30" y="35" text-anchor="middle" class="text-xs font-bold fill-gray-800 dark:fill-gray-100">
                            {{ number_format(min($totalDisbursementsToObligations, 100), 0) }}%
                        </text>
                    </svg>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="font-semibold">Disbursements / Obligations</div>
                    <div class="text-lime-300">{{ number_format($totalDisbursementsToObligations, 2) }}%</div>
                    <div class="text-gray-300 text-[10px] mt-1">Disbursements: <span class="card-tooltip-disbursements-ob">{{ number_format($totalDisbursements, 2) }}</span></div>
                    <div class="text-gray-300 text-[10px]">Obligations: <span class="card-tooltip-obligations-disb">{{ number_format($totalObligations, 2) }}</span></div>
                    <!-- Arrow -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                </div>
            </div>
            <!-- Disbursements / Authorized Appropriations Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center justify-between transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer relative group" data-card="disbursements_to_appropriations">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900 rounded-full p-2 sm:p-3">
                        <i class="fas fa-percentage text-amber-600 dark:text-amber-300 text-lg sm:text-xl md:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                        <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                            Disbursements / Authorized Appropriations
                        </div>
                        <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                            {{ number_format($totalDisbursementsToAppropriations, 2) }}%
                        </div>
                    </div>
                </div>
                
                <!-- Circular Progress -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="circular-progress" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="circular-progress-bg" cx="30" cy="30" r="24" fill="none" stroke="#e5e7eb" stroke-width="6"/>
                        <circle class="circular-progress-bar" cx="30" cy="30" r="24" fill="none" stroke="#f59e0b" stroke-width="6"
                                stroke-dasharray="{{ min($totalDisbursementsToAppropriations, 100) * 1.507 }} 150.7"
                                stroke-linecap="round"
                                transform="rotate(-90 30 30)"
                                data-percentage="{{ $totalDisbursementsToAppropriations }}"/>
                        <text x="30" y="35" text-anchor="middle" class="text-xs font-bold fill-gray-800 dark:fill-gray-100">
                            {{ number_format(min($totalDisbursementsToAppropriations, 100), 0) }}%
                        </text>
                    </svg>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="font-semibold">Disbursements / Appropriations</div>
                    <div class="text-amber-300">{{ number_format($totalDisbursementsToAppropriations, 2) }}%</div>
                    <div class="text-gray-300 text-[10px] mt-1">Disbursements: <span class="card-tooltip-disbursements-ap">{{ number_format($totalDisbursements, 2) }}</span></div>
                    <div class="text-gray-300 text-[10px]">Authorized Approp.: <span class="card-tooltip-auth-approp-disb">{{ number_format($totalAuthorizedAppropriations, 2) }}</span></div>
                    <!-- Arrow -->
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                </div>
            </div>
            @endrole

            {{-- Add more cards here if needed --}}
        </div>
    </div>

    <!-- Right-Click Context Menu -->
    <div id="contextMenu" class="hidden fixed bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 border-2 border-blue-400 dark:border-blue-600 rounded-lg shadow-2xl z-[9999] text-xs">
        <a href="#" id="contextAccounts" class="flex items-center px-4 py-2 text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-t-lg transition-colors duration-150 cursor-pointer">
            <i class="fas fa-stream mr-2 text-blue-600 dark:text-blue-400"></i> Accounts
        </a>
        @role('Administrator|Developer|Obligation')
        <a href="#" id="contextObligate" onclick="openCreateModalWithClass(event)" class="flex items-center px-4 py-2 text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150 cursor-pointer">
            <i class="fas fa-plus-circle mr-2 text-blue-600 dark:text-blue-400"></i> Obligate
        </a>
        @endrole
        <a href="#" id="contextObligations" onclick="showObligationsModal(event)" class="flex items-center px-4 py-2 text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-b-lg transition-colors duration-150 cursor-pointer">
            <i class="fas fa-list-check mr-2 text-blue-600 dark:text-blue-400"></i> Obligations
        </a>
    </div>

    <!-- Obligations Modal -->
    <div id="obligationsModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-screen-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal Header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-b-2 border-blue-200 dark:border-blue-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-list-check text-blue-600 dark:text-blue-300 text-xl"></i>
                    <div>
                        <h3 class="text-base leading-6 font-semibold text-blue-900 dark:text-blue-100">
                            Obligations
                        </h3>
                        <span id="obligationsHeaderInfo" class="text-xs text-blue-700 dark:text-blue-300"></span>
                    </div>
                </div>
                <button onclick="closeObligationsModal()" class="text-blue-600 dark:text-blue-300 hover:text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-full p-2 transition-colors duration-200">
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
                        <span id="obligationsTotalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">0</span>
                    </div>
                    <!-- Date Range Filter -->
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex-shrink-0">Date Range:</span>
                    <input 
                        type="date" 
                        id="obligationsDateFrom" 
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                        onchange="refreshObligationsModal()"
                    >
                    <span class="text-gray-600 dark:text-gray-400 text-xs">to</span>
                    <input 
                        type="date" 
                        id="obligationsDateTo" 
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                        onchange="refreshObligationsModal()"
                    >
                    <button 
                        onclick="document.getElementById('obligationsDateFrom').value = ''; document.getElementById('obligationsDateTo').value = ''; refreshObligationsModal()"
                        class="px-2 py-2 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors flex-shrink-0"
                        title="Clear date range"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                    <!-- Search Input -->
                    <input 
                        type="text" 
                        id="obligationsSearchInput" 
                        placeholder="Search obligations..." 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                        oninput="filterObligationsTable(this.value, 'dashboard')"
                    >
                </div>
            </div>

            <!-- Modal Body -->
            <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)] px-6 py-4">
                <div id="obligationsContent" class="space-y-4">
                    <!-- Loading spinner -->
                    <div id="obligationsLoading" class="flex justify-center items-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg flex-shrink-0">
                <button onclick="printObligationsModal('dashboard')" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-print mr-2"></i>
                    Print
                </button>
                <button onclick="closeObligationsModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Context Menu for Obligation Rows in Dashboard Modal -->
    @role('Administrator|Developer|Obligation')
    <div id="dashboardObligationContextMenu" 
        class="absolute hidden w-48 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 border-2 border-blue-400 dark:border-blue-600 rounded-lg shadow-2xl"
        style="display: none; z-index: 10001; position: fixed;">
        <button id="contextObligationDetails"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-t-lg transition-colors duration-150">
            <i class="fas fa-eye mr-2 text-blue-600 dark:text-blue-400"></i>View Details
        </button>
        @can('edit obligations')
        <button id="contextObligationEdit"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-edit mr-2 text-blue-600 dark:text-blue-400"></i>Edit Obligation
        </button>
        @endcan
        @can('view obligation adjustments')
        <button id="contextObligationAdjustment"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-file-edit mr-2 text-blue-600 dark:text-blue-400"></i>Add Adjustment
        </button>
        @endcan
        @can('view purchase orders')
        <button id="contextObligationPO"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-file-invoice mr-2 text-blue-600 dark:text-blue-400"></i>Add Purchase Order
        </button>
        @endcan
        @can('cancel obligations')
        <button id="contextObligationCancellation"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-window-close mr-2 text-blue-600 dark:text-blue-400"></i>Cancellation
        </button>
        @endcan
        <button id="contextObligationHistory"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-b-lg transition-colors duration-150">
            <i class="fas fa-history mr-2 text-blue-600 dark:text-blue-400"></i>Status/History
        </button>
    </div>
    @endrole

    <!-- Obligation History Modal -->
    <div id="obligationHistoryModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-900 dark:to-slate-900 border-b-2 border-gray-200 dark:border-gray-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-history text-gray-600 dark:text-gray-300 text-xl"></i>
                    <div>
                        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
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
                <button type="button" onclick="closeObligationHistoryModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal for Dashboard -->
    <form id="dashboardCancelObligationForm" method="POST">
        <div id="dashboardCancellationModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10004] flex items-center justify-center bg-black bg-opacity-50">
            <div class="flex flex-col max-h-[90vh] w-full max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
                <!-- Modal header -->
                <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900 dark:to-violet-900 border-b-2 border-purple-200 dark:border-purple-700 rounded-t-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-ban text-purple-600 dark:text-purple-300 text-xl"></i>
                        <h3 class="text-base leading-6 font-semibold text-purple-900 dark:text-purple-100">
                            Cancel Obligation
                        </h3>
                    </div>
                    <button type="button" onclick="closeDashboardCancellationModal()" class="text-purple-600 dark:text-purple-300 hover:text-white hover:bg-purple-600 dark:hover:bg-purple-700 rounded-full p-2 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Modal body (scrollable) -->
                <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)] p-6">
                    <input type="hidden" id="dashboardHiddenObligationId" name="obligation_id" value="">
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
                        <label for="dashboardCancellationRemarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remarks:</label>
                        <textarea id="dashboardCancellationRemarks" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="3" placeholder="Enter remarks..."></textarea>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                    <button type="button" onclick="proceedDashboardCancellation()" class="text-red-600 dark:text-red-400 inline-flex leading-4 tracking-wider hover:text-white border border-red-600 dark:border-red-500 hover:bg-red-600 dark:hover:bg-red-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                        <i class="fas fa-window-close mr-2"></i>
                        Proceed
                    </button>
                    <button type="button" onclick="closeDashboardCancellationModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>
    </div>

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

    /* Stacked bar animation */
    @keyframes barSlideIn {
        from {
            opacity: 0;
            transform: scaleX(0);
            transform-origin: left;
        }
        to {
            opacity: 1;
            transform: scaleX(1);
            transform-origin: left;
        }
    }

    .stacked-segment {
        animation: barSlideIn 0.6s ease-out forwards;
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
    #dashboardTable tbody tr.context-menu-active {
        background-color: rgba(59, 130, 246, 0.15);
        transition: background-color 0.2s ease-in-out;
    }

    .dark #dashboardTable tbody tr.context-menu-active {
        background-color: rgba(59, 130, 246, 0.25);
    }
    </style>

    <script>
        // Date Range Validation: Set minimum to date based on from date
        const dashboardFromDateInput = document.getElementById('fromDate');
        const dashboardToDateInput = document.getElementById('toDate');

        if (dashboardFromDateInput && dashboardToDateInput) {
            dashboardFromDateInput.addEventListener('change', function() {
                if (this.value) {
                    dashboardToDateInput.min = this.value;
                    // If the current to_date is before the new from_date, clear it
                    if (dashboardToDateInput.value && dashboardToDateInput.value < this.value) {
                        dashboardToDateInput.value = '';
                    }
                } else {
                    dashboardToDateInput.min = '';
                }
            });
        }

        // Store all card information for dynamic updates
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

        // updateCardValues function to also update circular progress bars
        function updateCardValues() {
            const rows = document.querySelectorAll('#dashboardTable tbody tr');
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
                            cardValue.textContent = percentage.toLocaleString('en-US', {
                                minimumFractionDigits: 2, 
                                maximumFractionDigits: 2
                            }) + '%';
                        } else if (cardKey === 'allotment_accomplishment') {
                            percentage = allotmentAccomplishment;
                            cardValue.textContent = percentage.toLocaleString('en-US', {
                                minimumFractionDigits: 2, 
                                maximumFractionDigits: 2
                            }) + '%';
                        } else if (cardKey === 'disbursements_to_obligations') {
                            percentage = disbursementsToObligations;
                            cardValue.textContent = percentage.toLocaleString('en-US', {
                                minimumFractionDigits: 2, 
                                maximumFractionDigits: 2
                            }) + '%';
                        } else if (cardKey === 'disbursements_to_appropriations') {
                            percentage = disbursementsToAppropriations;
                            cardValue.textContent = percentage.toLocaleString('en-US', {
                                minimumFractionDigits: 2, 
                                maximumFractionDigits: 2
                            }) + '%';
                        } else {
                            // Handle regular number cards
                            cardValue.textContent = total.toLocaleString('en-US', {
                                minimumFractionDigits: 2, 
                                maximumFractionDigits: 2
                            });
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
                        
                        // Update color classes for supplementals and reversions
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
                            if (total > 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-red-600', 'dark:text-red-400');
                                cardValue.classList.add('text-green-600', 'dark:text-green-400');
                            } else if (total < 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-green-600', 'dark:text-green-400');
                                cardValue.classList.add('text-red-600', 'dark:text-red-400');
                            } else {
                                cardValue.classList.remove('text-green-600', 'dark:text-green-400', 'text-red-600', 'dark:text-red-400');
                                cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                            }
                        }
                    }
                }
            }
        }

        function toggleDropdown(button) {
            const dropdown = button.nextElementSibling;
            const isOpen = !dropdown.classList.contains('hidden'); // true if already open

            CloseAllDropdowns(); // close all menus first

            if (!isOpen) {
                dropdown.classList.remove('hidden'); // open only if it wasn't already open
            }
        }

        function filterTable(searchValue) {
            const rows = document.querySelectorAll('#dashboardTable tbody tr');
            const lowerSearch = String(searchValue).toLowerCase(); // Ensure it's a string

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(lowerSearch)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Update card values after filtering
            updateCardValues();
            
            // Update graph after filtering
            updateGraph();
        }

        // Also update your DOMContentLoaded event listener
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            
            // Use a small delay to ensure DOM is fully rendered
            setTimeout(function() {
                // Apply filter if there's an initial search value
                if (searchInput && searchInput.value.trim()) {
                    filterTable(searchInput.value);
                }
            }, 100);
            
            // Listen for search input changes
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    filterTable(this.value);
                });
            }
        });

                function CloseAllDropdowns() {
                    const dropdowns = document.querySelectorAll('.dropdown-menu');
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.add('hidden');
                    });
                }

                // Close dropdown if click happens outside
                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.relative.inline-block')) {
                        CloseAllDropdowns();
                    }
                });

                // Right-click context menu handler
                document.addEventListener('contextmenu', function(event) {
                    const row = event.target.closest('#dashboardTable tbody tr');
                    if (row) {
                        event.preventDefault();
                        const contextMenu = document.getElementById('contextMenu');
                        const classId = row.getAttribute('data-class-id');
                        const year = row.getAttribute('data-year');
                        
                        // Store classId and year globally for use in modal functions
                        window.currentClassId = classId;
                        window.currentYear = year;
                        
                        // Remove highlight from previously selected row
                        document.querySelectorAll('#dashboardTable tbody tr.context-menu-active').forEach(r => {
                            r.classList.remove('context-menu-active');
                        });
                        
                        // Highlight the current row
                        row.classList.add('context-menu-active');
                        window.currentContextMenuRow = row;
                        
                        // Position the context menu
                        contextMenu.style.left = event.clientX + 'px';
                        contextMenu.style.top = event.clientY + 'px';
                        contextMenu.classList.remove('hidden');
                        
                        // Set the href for context menu items
                        document.getElementById('contextAccounts').href = '{{ route("dashboard.accounts", ":id") }}'.replace(':id', classId);
                    }
                });

                // Hide context menu on click
                document.addEventListener('click', function(event) {
                    const contextMenu = document.getElementById('contextMenu');
                    if (!event.target.closest('#contextMenu')) {
                        contextMenu.classList.add('hidden');
                        // Remove highlight when menu is closed
                        if (window.currentContextMenuRow) {
                            window.currentContextMenuRow.classList.remove('context-menu-active');
                            window.currentContextMenuRow = null;
                        }
                    }
                });

                /**
                 * Show obligations modal from dropdown with class ID
                 */
                function openObligationsModalFromDropdown(event, classId) {
                    if (event) {
                        event.preventDefault();
                    }
                    
                    // Set the class ID globally
                    window.currentClassId = classId;
                    
                    // Call the main modal function
                    showObligationsModal(event);
                }

                /**
                 * Refresh obligations modal with updated data (used when date filters change)
                 */
                function refreshObligationsModal() {
                    const modal = document.getElementById('obligationsModal');
                    if (modal && modal.style.display !== 'none') {
                        showObligationsModal();
                    }
                }

                /**
                 * Show obligations modal and fetch data
                 */
                function showObligationsModal(event) {
                    if (event) {
                        event.preventDefault();
                    }
                    
                    const classId = window.currentClassId;
                    if (!classId) {
                        console.error('No class selected');
                        return;
                    }

                    const modal = document.getElementById('obligationsModal');
                    const content = document.getElementById('obligationsContent');
                    const loading = document.getElementById('obligationsLoading');
                    const headerInfo = document.getElementById('obligationsHeaderInfo');

                    // Log missing elements for debugging
                    if (!modal || !content || !loading || !headerInfo) {
                        console.warn('Some modal elements not found:', {
                            modal: !!modal,
                            content: !!content,
                            loading: !!loading,
                            headerInfo: !!headerInfo
                        });
                    }

                    // Try to clear and show modal if elements exist
                    if (headerInfo) {
                        headerInfo.textContent = '';
                    }
                    if (content) {
                        content.innerHTML = '<div id="obligationsLoading" class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div></div>';
                    }
                    
                    if (modal) {
                        modal.offsetHeight;
                        modal.style.display = 'flex';
                        modal.setAttribute('aria-hidden', 'false');
                    }
                    
                    // Re-query the loading element after it's been recreated
                    const loadingElement = document.getElementById('obligationsLoading');
                    if (loadingElement) {
                        loadingElement.style.display = 'flex';
                    }

                    // Build API URL with date parameters
                    let apiUrl = `{{ route('obligations.api.byOfficeAllotmentClass', ':classId') }}`.replace(':classId', classId);
                    const dateFrom = document.getElementById('obligationsDateFrom')?.value;
                    const dateTo = document.getElementById('obligationsDateTo')?.value;
                    const params = new URLSearchParams();
                    if (dateFrom) params.append('from_date', dateFrom);
                    if (dateTo) params.append('to_date', dateTo);
                    if (params.toString()) {
                        apiUrl += '?' + params.toString();
                    }

                    // Fetch obligations
                    fetch(apiUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            const loadingSpinner = document.getElementById('obligationsLoading');
                            if (loadingSpinner) {
                                loadingSpinner.style.display = 'none';
                            }
                            
                            // Update header with office and allotment class info and year
                            if (data.success && headerInfo) {
                                const year = data.cy_year || window.currentYear || '';
                                headerInfo.textContent = ` | ${data.office} - ${data.allotmentClass} (CY ${year})`;
                                // Store in global for print function
                                window.currentObligationsInfo = {
                                    office: data.office,
                                    allotmentClass: data.allotmentClass,
                                    cyYear: year
                                };
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
                                            <thead class="bg-gray-200 dark:bg-gray-700 border-t border-b border-gray-400 dark:border-gray-600 text-center">
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
                                data.data.forEach((obligation, index) => {
                                    // Check if obligation is cancelled (amount is 0)
                                    const amountValue = parseFloat(obligation.amount.replace(/,/g, ''));
                                    const isCancelled = amountValue === 0;
                                    
                                    // Check if obligation has adjustments
                                    const hasAdjustments = obligation.appropriations && 
                                        obligation.appropriations.some(app => {
                                            const adjAmount = parseFloat(app.adjustment_amount.replace(/,/g, ''));
                                            return adjAmount !== 0;
                                        });
                                    
                                    const amountDisplay = isCancelled ? 
                                        '<span class="text-red-600 dark:text-red-400 font-semibold">Cancelled</span>' : 
                                        (hasAdjustments ? 
                                            `<span class="text-green-600 dark:text-green-400 font-semibold">${obligation.amount}</span>` :
                                            obligation.amount);
                                    
                                    tableHTML += `
                                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer obligation-row" 
                                            data-obligation-index="${index}"
                                            data-obligation-id="${obligation.id}"
                                            data-obligation='${JSON.stringify(obligation)}'
                                            oncontextmenu="showDashboardObligationContextMenu(event, this)">
                                            <td class="px-3 py-2 text-center"><i class="fas fa-chevron-right text-gray-500 transition-transform duration-200 expand-icon"></i></td>
                                            <td class="px-3 py-2">${obligation.obr_no}</td>
                                            <td class="px-3 py-2">${obligation.obr_date}</td>
                                            <td class="px-3 py-2">${obligation.obr_type}</td>
                                            <td class="px-3 py-2">${obligation.payee}</td>
                                            <td class="px-3 py-2">${obligation.remarks || '-'}</td>
                                            <td class="px-3 py-2 text-right font-semibold">${amountDisplay}</td>
                                            <td class="px-3 py-2 text-right">${obligation.purchase_order}</td>
                                            <td class="px-3 py-2 text-right">${obligation.disbursement}</td>
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
                                    
                                    // Add amount to total (remove commas and convert to float)
                                    totalAmount += parseFloat(obligation.amount.replace(/,/g, ''));
                                    // Only add to PO total if it's not "-" (i.e., valid number)
                                    if (obligation.purchase_order !== '-') {
                                        totalPurchaseOrder += parseFloat(obligation.purchase_order.replace(/,/g, ''));
                                    }
                                    // Only add to Disbursement total if it's not "-" (i.e., valid number)
                                    if (obligation.disbursement !== '-') {
                                        totalDisbursement += parseFloat(obligation.disbursement.replace(/,/g, ''));
                                    }
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
                                                <col style="width: 300px;">
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
                                    const headerCountElement = document.getElementById('obligationsTotalRecordsCount');
                                    if (headerCountElement) {
                                        headerCountElement.textContent = data.data.length;
                                    }
                                    
                                    // Add click event listeners to obligation rows
                                    document.querySelectorAll('.obligation-row').forEach(row => {
                                        row.addEventListener('click', function(e) {
                                            // Don't expand if right-clicking
                                            if (e.button === 2) return;
                                            
                                            const obligationIndex = this.dataset.obligationIndex;
                                            const appRow = document.querySelector(`.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                                            const expandIcon = this.querySelector('.expand-icon');
                                            
                                            if (appRow) {
                                                appRow.classList.toggle('hidden');
                                                expandIcon.style.transform = appRow.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
                                            }
                                        });
                                    });
                                }
                            } else if (data.success && content) {
                                content.innerHTML = '<div class="text-center py-8 text-gray-500 italic dark:text-gray-400">No Obligations found for this Office Allotment Class.</div>';
                            } else if (content) {
                                content.innerHTML = '<div class="text-center py-8 text-red-500">Error: ' + (data.message || 'Unknown error') + '</div>';
                            }
                        })
                        .catch(error => {
                            if (loading) {
                                loading.style.display = 'none';
                            }
                            if (content) {
                                content.innerHTML = '<div class="text-center py-8 text-red-500">Error loading obligations: ' + error.message + '</div>';
                            }
                            console.error('Error fetching obligations:', error);
                        });
                }

                /**
                 * Close obligations modal
                 */
                function closeObligationsModal() {
                    const modal = document.getElementById('obligationsModal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.setAttribute('aria-hidden', 'true');
                    }
                    // Clear search input
                    const searchInput = document.getElementById('obligationsSearchInput');
                    if (searchInput) {
                        searchInput.value = '';
                    }
                }

                /**
                 * Print obligations table, respecting the currently selected date range and search filter
                 */
                function printObligationsModal(source = 'dashboard') {
                    const modalId = source === 'dashboard' ? 'obligationsModal' : 'accountObligationsModal';
                    const modal = document.getElementById(modalId);

                    if (!modal || !window.currentObligationsInfo) {
                        alert('No data to print');
                        return;
                    }

                    // Get date range filter values based on source
                    const dateFromId = source === 'dashboard' ? 'obligationsDateFrom' : 'accountObligationsDateFrom';
                    const dateToId = source === 'dashboard' ? 'obligationsDateTo' : 'accountObligationsDateTo';
                    const dateFromValue = document.getElementById(dateFromId)?.value || '';
                    const dateToValue = document.getElementById(dateToId)?.value || '';

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

                    // Parse date range boundaries once
                    const checkFrom = dateFromValue ? new Date(dateFromValue) : null;
                    const checkTo = dateToValue ? new Date(dateToValue) : null;

                    // Walk every obligation row: drop it if it's hidden by search OR falls outside
                    // the selected date range, and recompute totals from only what's kept
                    const allObligationRows = printableContent.querySelectorAll('tbody tr.obligation-row');
                    const rowsToRemove = [];
                    let printTotalAmount = 0;
                    let printTotalPurchaseOrder = 0;
                    let printTotalDisbursement = 0;
                    let printedCount = 0;

                    allObligationRows.forEach(row => {
                        const obligationIndex = row.dataset.obligationIndex;
                        const cells = row.querySelectorAll('td');
                        const dateCellText = cells[2]?.textContent || '';

                        let rowDate = null;
                        try {
                            rowDate = new Date(dateCellText);
                        } catch (e) {
                            rowDate = null;
                        }

                        let withinDateRange = true;
                        if (rowDate && (checkFrom || checkTo)) {
                            if (checkFrom && rowDate < checkFrom) withinDateRange = false;
                            if (checkTo && rowDate > checkTo) withinDateRange = false;
                        }

                        const isHiddenBySearch = row.style.display === 'none';

                        if (isHiddenBySearch || !withinDateRange) {
                            rowsToRemove.push(row);
                            const appRow = printableContent.querySelector(`tr.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                            if (appRow) rowsToRemove.push(appRow);
                        } else {
                            printedCount++;

                            const amountText = (cells[6]?.textContent || '0').replace(/,/g, '').replace(/Cancelled/g, '0').trim();
                            const amountValue = parseFloat(amountText);
                            if (!isNaN(amountValue)) printTotalAmount += amountValue;

                            const poText = (cells[7]?.textContent || '0').replace(/,/g, '').trim();
                            const poValue = parseFloat(poText);
                            if (!isNaN(poValue)) printTotalPurchaseOrder += poValue;

                            const disbText = (cells[8]?.textContent || '0').replace(/,/g, '').trim();
                            const disbValue = parseFloat(disbText);
                            if (!isNaN(disbValue)) printTotalDisbursement += disbValue;

                            // Make visible appropriations rows display properly in print
                            const appRow = printableContent.querySelector(`tr.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                            if (appRow) {
                                appRow.classList.remove('hidden');
                                appRow.style.display = '';
                            }
                        }
                    });

                    // Remove rows that don't belong in the printed set
                    rowsToRemove.forEach(row => row.remove());

                    // Recompute the footer totals so they match exactly what's printed
                    const footerRow = printableContent.querySelector('tfoot tr');
                    if (footerRow) {
                        const footerCells = footerRow.querySelectorAll('td');
                        if (footerCells.length >= 4) {
                            footerCells[1].textContent = printTotalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            footerCells[2].textContent = printTotalPurchaseOrder > 0 ? printTotalPurchaseOrder.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                            footerCells[3].textContent = printTotalDisbursement > 0 ? printTotalDisbursement.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                        }
                    }

                    // Get the HTML of the cloned, filtered container
                    const tableHTML = printableContent.outerHTML;

                    // Build a readable date range string for the printed header
                    let dateRangeText = '';
                    if (dateFromValue || dateToValue) {
                        const fromDisplay = dateFromValue
                            ? new Date(dateFromValue).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
                            : 'Start';
                        const toDisplay = dateToValue
                            ? new Date(dateToValue).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
                            : 'Present';
                        dateRangeText = ` | Date Range: ${fromDisplay} - ${toDisplay}`;
                    }

                    const noResultsMessage = printedCount === 0
                        ? `<p style="text-align:center; padding: 20px; color: #999; font-style: italic;">No obligations found for the selected filters.</p>`
                        : '';

                    // Create a comprehensive print document
                    const printWindow = window.open('', '', 'height=800,width=1200');
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
                                .text-right { text-align: right; }
                                .text-center { text-align: center; }
                                .print-date {
                                    text-align: right;
                                    font-size: 10px;
                                    margin-top: 15px;
                                    color: #666;
                                }
                                @media print {
                                    body { margin: 0; }
                                    .print-date { display: none; }
                                }
                            </style>
                        </head>
                        <body onload="window.print()">
                            <div class="header">
                                <h1>OBLIGATIONS REPORT</h1>
                                <p>Generated on ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                            </div>

                            <div class="office-info">
                                Office: ${headerInfo.office} | Allotment Class: ${headerInfo.allotmentClass} | CY ${headerInfo.cyYear}${dateRangeText}
                            </div>

                            <div class="table-container">
                                ${noResultsMessage}
                                ${tableHTML}
                                <div class="print-date">
                                    Printed on: ${new Date().toLocaleString()}
                                </div>
                            </div>
                        </body>
                        </html>
                    `;

                    printWindow.document.write(printContent);
                    printWindow.document.close();
                }

                /**
                 * Filter obligations table by search text and date range
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
                    let totalDisbursement = 0;
                    
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
                            const cells = row.querySelectorAll('td');
                            if (cells.length >= 8) {
                                // Dashboard: Amount in index 6, PO in index 7, Disbursement in index 8
                                // Accounts: Amount in index 6, PO in index 7, Disbursement in index 8
                                let amountIndex = 6;
                                let poIndex = 7;
                                let disbursementIndex = 8;
                                
                                // Extract and clean amount values
                                const amountText = (cells[amountIndex]?.textContent || '0').replace(/,/g, '').replace(/Cancelled/g, '0').trim();
                                const amountValue = parseFloat(amountText);
                                if (!isNaN(amountValue)) {
                                    totalAmount += amountValue;
                                }
                                
                                const poText = (cells[poIndex]?.textContent || '0').replace(/,/g, '').trim();
                                const poValue = parseFloat(poText);
                                if (!isNaN(poValue)) {
                                    totalPurchaseOrder += poValue;
                                }
                                
                                const disbursementText = (cells[disbursementIndex]?.textContent || '0').replace(/,/g, '').trim();
                                const disbursementValue = parseFloat(disbursementText);
                                if (!isNaN(disbursementValue)) {
                                    totalDisbursement += disbursementValue;
                                }
                            }
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    // Update the footer with new totals
                    const footerRow = content.querySelector('tfoot tr');
                    if (footerRow) {
                        const footerCells = footerRow.querySelectorAll('td');
                        if (footerCells.length >= 4) {
                            // FooterRow structure: [0: "Total:" with colspan=6, 1: Amount, 2: PO, 3: Disbursement]
                            
                            // Update Amount total (preserve the original td element with its classes)
                            if (footerCells[1]) {
                                footerCells[1].textContent = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            
                            // Update PO total (preserve the original td element with its classes)
                            if (footerCells[2]) {
                                footerCells[2].textContent = totalPurchaseOrder > 0 ? totalPurchaseOrder.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                            }
                            
                            // Update Disbursement total (preserve the original td element with its classes)
                            if (footerCells[3]) {
                                footerCells[3].textContent = totalDisbursement > 0 ? totalDisbursement.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                            }
                        }
                    }

                    // Update header Total Records count
                    const headerCountElement = source === 'dashboard' ? 
                        document.getElementById('obligationsTotalRecordsCount') : 
                        document.getElementById('accountObligationsTotalRecordsCount');
                    if (headerCountElement) {
                        headerCountElement.textContent = visibleCount;
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

                /**
                 * Show context menu for obligation rows in dashboard modal
                 */
                function showDashboardObligationContextMenu(event, row) {
                    event.preventDefault();
                    event.stopPropagation();

                    const menu = document.getElementById('dashboardObligationContextMenu');
                    if (!menu) {
                        console.error('Context menu element not found');
                        return;
                    }

                    // Position the menu at the cursor
                    menu.style.position = 'fixed';
                    menu.style.top = `${event.clientY}px`;
                    menu.style.left = `${event.clientX}px`;
                    menu.style.zIndex = '10001';
                    menu.style.display = 'block';
                    menu.classList.remove('hidden');

                    // Get obligation data from row
                    const obligation = row.dataset.obligation ? JSON.parse(row.dataset.obligation) : null;
                    if (!obligation) {
                        console.error('No obligation data found in row');
                        hideDashboardObligationContextMenu();
                        return;
                    }

                    // View Details button
                    const detailsBtn = menu.querySelector('#contextObligationDetails');
                    if (detailsBtn) {
                        detailsBtn.onclick = () => {
                            hideDashboardObligationContextMenu();
                            openModal(obligation.id);
                        };
                    }

                    // Edit button
                    const editBtn = menu.querySelector('#contextObligationEdit');
                    if (editBtn) {
                        editBtn.onclick = () => {
                            hideDashboardObligationContextMenu();
                            
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
                                
                                // Get office and allotment class details
                                const officeAbbr = obligation.office || data.obligation.office || '';
                                const classDesc = obligation.class || data.obligation.allotment_class || '';
                                
                                // Build complete obligation object with all required fields
                                const fullObligation = {
                                    id: obligation.id,
                                    office_allotment_class_id: window.currentClassId,
                                    office_allotment_class: {
                                        id: window.currentClassId,
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
                                
                                // Set flag to indicate this is from dashboard
                                window.isFromDashboard = true;
                                // Open the edit modal with the complete obligation data
                                openEditObligationsModal(fullObligation);
                            })
                            .catch(error => {
                                console.error('Error fetching obligation:', error);
                                alert('Error loading obligation: ' + error.message);
                            });
                        };
                    }

                    // Adjustment button
                    const adjustBtn = menu.querySelector('#contextObligationAdjustment');
                    if (adjustBtn) {
                        adjustBtn.onclick = () => {
                            hideDashboardObligationContextMenu();
                            // Set the obligation_id in the create form
                            const obligationIdInput = document.querySelector('#createObligationAdjustmentForm input[name="obligation_id"]');
                            if (obligationIdInput) {
                                obligationIdInput.value = obligation.id;
                            }
                            // Set flag to indicate adjustment is from dashboard
                            window.isFromDashboard = true;
                            window.isFromAccounts = false;
                            // Open the create adjustment modal with obligation ID
                            if (typeof openCreateObligationAdjustmentModal === 'function') {
                                openCreateObligationAdjustmentModal(obligation.id);
                            }
                        };
                    }

                    // Purchase Order button - only show for Purchase Request type
                    const poBtn = menu.querySelector('#contextObligationPO');
                    if (poBtn) {
                        // Show/hide based on obligation type
                        if (obligation.obr_type === 'Purchase Request') {
                            poBtn.style.display = 'block';
                            poBtn.onclick = () => {
                                hideDashboardObligationContextMenu();
                                openDashboardObligationPurchaseOrderModal(obligation);
                            };
                        } else {
                            poBtn.style.display = 'none';
                        }
                    }

                    // Status/History button
                    const historyBtn = menu.querySelector('#contextObligationHistory');
                    if (historyBtn) {
                        historyBtn.onclick = () => {
                            hideDashboardObligationContextMenu();
                            openObligationHistoryModal(obligation);
                        };
                    }

                    // Cancellation button
                    const cancelBtn = menu.querySelector('#contextObligationCancellation');
                    if (cancelBtn) {
                        cancelBtn.onclick = () => {
                            hideDashboardObligationContextMenu();
                            openDashboardCancellationModal(obligation.id, obligation);
                        };
                    }

                    // Add event listeners to hide menu
                    setTimeout(() => {
                        document.addEventListener('click', hideDashboardObligationContextMenu);
                        window.addEventListener('resize', hideDashboardObligationContextMenu);
                    }, 0);
                }

                /**
                 * Hide context menu for obligation rows
                 */
                function hideDashboardObligationContextMenu() {
                    const menu = document.getElementById('dashboardObligationContextMenu');
                    if (menu) {
                        menu.style.display = 'none';
                        menu.classList.add('hidden');
                    }
                    document.removeEventListener('click', hideDashboardObligationContextMenu);
                    window.removeEventListener('resize', hideDashboardObligationContextMenu);
                }

                function closeAllDropdowns() {
                    // Close context menu
                    const contextMenu = document.getElementById('dashboardObligationContextMenu');
                    if (contextMenu) {
                        contextMenu.style.display = 'none';
                        contextMenu.classList.add('hidden');
                    }
                }

                /**
                 * Open purchase order modal for selected obligation
                 */
                function openDashboardObligationPurchaseOrderModal(obligation) {
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

                /**
                 * Close purchase order modal
                 */
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
                 * Open cancellation modal from dashboard obligations modal
                 */
                function openDashboardCancellationModal(obligationId, obligationData) {
                    CloseAllDropdowns();
                    const modal = document.getElementById('dashboardCancellationModal');
                    modal.style.display = 'flex';
                    modal.setAttribute('aria-hidden', 'false');

                    modal.dataset.obligationId = obligationId;

                    // Set the hidden input
                    document.getElementById('dashboardHiddenObligationId').value = obligationId;

                    // Get office and allotment class from the obligations modal header
                    const obligationsModal = document.getElementById('obligationsModal');
                    const headerInfo = obligationsModal ? obligationsModal.querySelector('#obligationsHeaderInfo') : null;
                    let officeAbbr = 'N/A';
                    let allotmentClass = 'N/A';
                    
                    if (headerInfo && headerInfo.textContent) {
                        // Header format: " | Office - Class (CY Year)"
                        const headerText = headerInfo.textContent.trim();
                        const match = headerText.match(/\|\s*([^\-]+)\s*-\s*([^\(]+)/);
                        if (match) {
                            officeAbbr = match[1].trim();
                            allotmentClass = match[2].trim();
                        }
                    }

                    // Fill modal data - use correct field names from API response
                    document.querySelector('#dashboardCancellationModal td[data-field="obr_date"]').textContent = obligationData.obr_date || 'N/A';
                    document.querySelector('#dashboardCancellationModal td[data-field="office_abbreviation"]').textContent = officeAbbr;
                    document.querySelector('#dashboardCancellationModal td[data-field="allotment_class"]').textContent = allotmentClass;
                    document.querySelector('#dashboardCancellationModal td[data-field="obr_no"]').textContent = obligationData.obr_no || 'N/A';
                    document.querySelector('#dashboardCancellationModal td[data-field="obr_type"]').textContent = obligationData.obr_type || 'N/A';
                    document.querySelector('#dashboardCancellationModal td[data-field="particulars"]').textContent = obligationData.payee || 'N/A';
                    document.querySelector('#dashboardCancellationModal td[data-field="obr_amount"]').textContent = Number(obligationData.amount.replace(/,/g, '')).toLocaleString(undefined, {
                        minimumFractionDigits: 2
                    });

                    const proceedBtn = modal.querySelector('button[onclick="proceedDashboardCancellation()"]');
                    const remarksBox = document.getElementById('dashboardCancellationRemarks');
                    const messageContainerId = 'dashboardCancelNotice';

                    // Remove any previous message
                    const oldMessage = document.getElementById(messageContainerId);
                    if (oldMessage) oldMessage.remove();

                    // Check if obligation is already cancelled
                    if (Number(obligationData.amount.replace(/,/g, '')) === 0) {
                        // Disable button and textarea
                        proceedBtn.disabled = true;
                        remarksBox.disabled = true;

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
                        proceedBtn.disabled = false;
                        remarksBox.disabled = false;
                    }
                }

                /**
                 * Close dashboard cancellation modal
                 */
                function closeDashboardCancellationModal() {
                    const modal = document.getElementById('dashboardCancellationModal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.setAttribute('aria-hidden', 'true');
                    }
                }

                /**
                 * Proceed with dashboard cancellation
                 */
                function proceedDashboardCancellation() {
                    const modal = document.getElementById('dashboardCancellationModal');
                    const obligationId = modal.dataset.obligationId;
                    const remarks = document.getElementById('dashboardCancellationRemarks').value.trim();

                    if (!remarks) {
                        let errorSpan = document.getElementById('dashboardRemarksError');
                        if (!errorSpan) {
                            errorSpan = document.createElement('span');
                            errorSpan.id = 'dashboardRemarksError';
                            errorSpan.className = 'text-sm text-red-600 mt-1 block';
                            document.getElementById('dashboardCancellationRemarks').parentNode.appendChild(errorSpan);
                        }
                        errorSpan.textContent = 'Remarks is required.';
                        return;
                    }

                    // Prepare the form
                    const form = document.getElementById('dashboardCancelObligationForm');
                    form.action = `/obligations/${obligationId}/cancel`;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                        <input type="hidden" name="remarks" value="${remarks}">
                        <input type="hidden" name="from" value="dashboard">
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

                /**
                 * Validate PO amount against balance
                 */
                function validateAmountPO(inputElement) {
                    const maxBalance = parseFloat(inputElement.dataset.balance || "0");
                    const inputValue = parseFloat(inputElement.value || "0");

                    if (inputValue > maxBalance) {
                        inputElement.value = maxBalance.toFixed(2);
                        inputElement.title = `Max allowed is ₱${maxBalance.toFixed(2)}`;
                    }
                    updatePOAmountTotal();
                }

                /**
                 * Update PO amount total
                 */
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

                /**
                 * Validate PO form before submission
                 */
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

                // Hide menu on Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') hideDashboardObligationContextMenu();
                });

                /**
                 * Open the create modal for obligations with pre-selected office allotment class
                 */
                function openCreateModalWithClass(event) {
                    if (event) {
                        event.preventDefault();
                    }
                    
                    const classId = window.currentClassId;
                    if (!classId) {
                        console.error('No office allotment class selected');
                        return;
                    }
                    
                    // Hide the context menu
                    const contextMenu = document.getElementById('contextMenu');
                    if (contextMenu) {
                        contextMenu.classList.add('hidden');
                    }
                    
                    // Set flag indicating this is from dashboard
                    window.isFromDashboard = true;
                    
                    // Open the create modal and pass the class ID
                    if (typeof openCreateModal === 'function') {
                        openCreateModal(classId);
                    } else {
                        console.error('openCreateModal function not found');
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('searchInput');
                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            filterTable(this.value);
                        });
                    }
                });

                document.addEventListener('click', () => {
                    const classContextMenu = document.getElementById('classContextMenu');
                    if (classContextMenu) {
                        classContextMenu.classList.add('hidden');
                    }
                });

                function updateSelectColors() {
                    document.querySelectorAll('.filter-select').forEach(select => {
                        const defaultValue = select.getAttribute('data-default') ?? "";
                        const selectedValue = select.value;

                        if (selectedValue === defaultValue) {
                            select.classList.remove('text-gray-900');
                            select.classList.add('text-gray-500');
                        } else {
                            select.classList.remove('text-gray-500');
                            select.classList.add('text-gray-900');
                        }
                    });
                }

                document.addEventListener('DOMContentLoaded', updateSelectColors);

                document.querySelectorAll('.filter-select').forEach(select => {
                    select.addEventListener('change', updateSelectColors);
                });

        // BAR GRAPH LOGIC

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

    // Fixed color palette with hover colors
    const fixedColorAssignments = {
        'PS': { color: 'bg-blue-500', hover: 'hover:bg-blue-600' },
        'MOOE': { color: 'bg-green-500', hover: 'hover:bg-green-600' },
        'CO': { color: 'bg-cyan-500', hover: 'hover:bg-cyan-600' },
        'FE': { color: 'bg-red-500', hover: 'hover:bg-red-600' },
        'CCO': { color: 'bg-violet-500', hover: 'hover:bg-violet-600' },
    };

    const fallbackColorPalette = [
        { color: 'bg-pink-600', hover: 'hover:bg-pink-700' },
        { color: 'bg-indigo-600', hover: 'hover:bg-indigo-700' },
        { color: 'bg-cyan-600', hover: 'hover:bg-cyan-700' },
        { color: 'bg-orange-600', hover: 'hover:bg-orange-700' },
        { color: 'bg-teal-600', hover: 'hover:bg-teal-700' },
        { color: 'bg-lime-600', hover: 'hover:bg-lime-700' },
        { color: 'bg-amber-600', hover: 'hover:bg-amber-700' },
        { color: 'bg-rose-600', hover: 'hover:bg-rose-700' },
        { color: 'bg-emerald-600', hover: 'hover:bg-emerald-700' },
        { color: 'bg-fuchsia-600', hover: 'hover:bg-fuchsia-700' },
        { color: 'bg-sky-600', hover: 'hover:bg-sky-700' },
        { color: 'bg-violet-600', hover: 'hover:bg-violet-700' }
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

    function getColorForClass(classCode) {
        if (fixedColorAssignments[classCode]) {
            return fixedColorAssignments[classCode].color;
        }
        const index = hashCode(classCode) % fallbackColorPalette.length;
        return fallbackColorPalette[index].color;
    }

    function getHoverColorForClass(classCode) {
        if (fixedColorAssignments[classCode]) {
            return fixedColorAssignments[classCode].hover;
        }
        const index = hashCode(classCode) % fallbackColorPalette.length;
        return fallbackColorPalette[index].hover;
    }

    function updateGraph() {
        const rows = document.querySelectorAll('#dashboardTable tbody tr');
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        
        const classData = {};
        let totalAmount = 0;
        
        visibleRows.forEach(row => {
            const authorizedApprop = parseFloat(row.getAttribute('data-authorized-appropriations')) || 0;
            const classCode = row.cells[2].textContent.trim();
            
            if (!classData[classCode]) {
                classData[classCode] = {
                    total: 0,
                    code: classCode
                };
            }
            classData[classCode].total += authorizedApprop;
            totalAmount += authorizedApprop;
        });
        
        const sortedClasses = Object.values(classData).sort((a, b) => b.total - a.total);
        
        const stackedBarContainer = document.getElementById('stackedBarContainer');
        if (!stackedBarContainer) return;
        
        let barHTML = '<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 overflow-visible flex relative">';
        
        if (sortedClasses.length === 0 || totalAmount === 0) {
            barHTML = `
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 flex items-center justify-center">
                    <span class="text-gray-500 dark:text-gray-400 text-sm italic">No data available</span>
                </div>
            `;
        } else {
            sortedClasses.forEach(classItem => {
                const percentage = totalAmount > 0 ? (classItem.total / totalAmount) * 100 : 0;
                const color = getColorForClass(classItem.code);
                const hoverColor = getHoverColorForClass(classItem.code);
                
                barHTML += `
                    <div 
                        class="${color} ${hoverColor} h-8 transition-all duration-200 ease-out flex items-center justify-center relative cursor-pointer stacked-segment"
                        style="width: ${percentage}%"
                        onmouseenter="showTooltip(this)"
                        onmouseleave="hideTooltip(this)"
                    >
                        ${percentage > 5 ? `<span class="text-white text-xs font-semibold px-1 text-center truncate pointer-events-none">${classItem.code}</span>` : ''}
                        
                        <div class="tooltip-box absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap shadow-xl" style="display: none; z-index: 9999;">
                            <div class="font-semibold">${classItem.code}</div>
                            <div>${percentage.toFixed(2)}%</div>
                            <div>${classItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                `;
            });
        }
        
        barHTML += '</div>';
        stackedBarContainer.innerHTML = barHTML;
        
        // Animate the bar segments
        animateStackedBar();
        
        updateLegend(sortedClasses, totalAmount);
    }

    function updateLegend(sortedClasses, totalAmount) {
        const legendContainer = document.getElementById('graphLegend');
        if (!legendContainer) return;
        
        let legendHTML = '';
        sortedClasses.forEach(classItem => {
            const percentage = totalAmount > 0 ? (classItem.total / totalAmount) * 100 : 0;
            const color = getColorForClass(classItem.code);
            
            legendHTML += `
                <div class="flex items-center space-x-2 text-xs">
                    <div class="w-4 h-4 ${color} rounded flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-gray-700 dark:text-gray-300 truncate">
                            ${classItem.code}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            ${percentage.toFixed(1)}%
                        </div>
                        <div class="text-gray-600 dark:text-gray-400 text-[10px]">
                            ${classItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </div>
                    </div>
                </div>
            `;
        });
        
        legendContainer.innerHTML = legendHTML;
    }

    // ============================================
    // ANIMATED COUNTER ENHANCEMENT
    // ============================================

    /**
     * Animates a number from start to end value
     * @param {HTMLElement} element - The element containing the number
     * @param {number} start - Starting value
     * @param {number} end - Ending value
     * @param {number} duration - Animation duration in milliseconds
     * @param {boolean} isPercentage - Whether the value is a percentage
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

    /**
     * Animate numeric values in dashboardTable cells
     */


    // ============================================
    // HEATMAP TABLE ENHANCEMENT
    // ============================================

    /**
     * Apply heatmap coloring to numeric table cells
     */
    function applyHeatmap() {
        const table = document.getElementById('dashboardTable');
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
        const table = document.getElementById('dashboardTable');
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
    // STACKED BAR GRAPH ANIMATION
    // ============================================

    /**
     * Animate stacked bar segments on update
     */
    function animateStackedBar() {
        const stackedSegments = document.querySelectorAll('.stacked-segment');
        
        stackedSegments.forEach((segment, index) => {
            // Reset animation by removing and re-adding the class
            segment.style.animation = 'none';
            
            // Trigger reflow to restart animation
            void segment.offsetWidth;
            
            // Apply animation with staggered timing
            setTimeout(() => {
                segment.style.animation = `barSlideIn 0.6s ease-out ${index * 0.1}s forwards`;
            }, 10);
        });
    }

    // ============================================
    // ENHANCED UPDATE FUNCTION
    // ============================================

    /**
     * Enhanced version of updateCardValues with animation
     */
    function updateCardValuesAnimated() {
        const rows = document.querySelectorAll('#dashboardTable tbody tr');
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');

        // Card configuration (same as original)
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

        // Calculate totals
        const totals = {};
        for (const [cardKey, config] of Object.entries(cardConfig)) {
            let total = 0;
            visibleRows.forEach(row => {
                const value = parseFloat(row.getAttribute(config.column)) || 0;
                total += value;
            });
            totals[cardKey] = total;
        }

        // Calculate percentages
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
                    } else if (cardKey === 'allotment_accomplishment') {
                        targetValue = allotmentAccomplishment;
                        isPercentage = true;
                    } else if (cardKey === 'disbursements_to_obligations') {
                        targetValue = disbursementsToObligations;
                        isPercentage = true;
                    } else if (cardKey === 'disbursements_to_appropriations') {
                        targetValue = disbursementsToAppropriations;
                        isPercentage = true;
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
                    
                    // Update color classes (same as original)
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
                        if (total > 0) {
                            cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-red-600', 'dark:text-red-400');
                            cardValue.classList.add('text-green-600', 'dark:text-green-400');
                        } else if (total < 0) {
                            cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-green-600', 'dark:text-green-400');
                            cardValue.classList.add('text-red-600', 'dark:text-red-400');
                        } else {
                            cardValue.classList.remove('text-green-600', 'dark:text-green-400', 'text-red-600', 'dark:text-red-400');
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

    // ============================================
    // INITIALIZATION
    // ============================================

    // Add heatmap toggle button to the page
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Add heatmap toggle button
        addHeatmapToggle();
        
        // Initial animation
        setTimeout(() => {
            animateAllCards();
            if (heatmapEnabled) {
                applyHeatmap();
            }
        }, 300);
        
        // Make toggleHeatmap available globally
        window.toggleHeatmap = toggleHeatmap;
    });

    // Override the original updateCardValues function
    if (typeof updateCardValues === 'function') {
        const originalUpdateCardValues = updateCardValues;
        updateCardValues = function() {
            updateCardValuesAnimated();
        };
    }

    // Override filterTable to include heatmap update
    const originalFilterTable = window.filterTable;
    if (typeof originalFilterTable === 'function') {
        window.filterTable = function(searchValue) {
            originalFilterTable(searchValue);
            if (heatmapEnabled) {
                setTimeout(() => applyHeatmap(), 100);
            }
        };
    }

    // ============================================
    // MICRO-INTERACTIONS & CARD ANIMATIONS
    // ============================================

    /**
     * Add pulse animation to cards on data update
     */
    function pulseCard(card) {
        card.style.transform = 'scale(1.02)';
        card.style.transition = 'transform 0.3s ease';
        
        setTimeout(() => {
            card.style.transform = 'scale(1)';
        }, 300);
    }

    /**
     * Add ripple effect on card click
     */
    function createRipple(event) {
        const card = event.currentTarget;
        const ripple = document.createElement('span');
        const rect = card.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        card.style.position = 'relative';
        card.style.overflow = 'hidden';
        card.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }

    // ============================================
    // TOP PERFORMERS WIDGET
    // ============================================

    /**
     * Create and display top performers widget
     */
    function createTopPerformersWidget() {
    // Check if widget exists (role-based)
    const widget = document.getElementById('topPerformersWidget');
    if (!widget) {
        // User doesn't have permission
        return;
    }
    
    const rows = document.querySelectorAll('#dashboardTable tbody tr');
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    
    // Collect data grouped by office
    const officeData = {};
    
    visibleRows.forEach(row => {
        const office = row.cells[1]?.textContent.trim() || 'N/A';
        const obligations = parseFloat(row.getAttribute('data-obligations')) || 0;
        const authorized = parseFloat(row.getAttribute('data-authorized-appropriations')) || 0;
        
        if (!officeData[office]) {
            officeData[office] = {
                office: office,
                totalObligations: 0,
                totalAuthorized: 0
            };
        }
        
        officeData[office].totalObligations += obligations;
        officeData[office].totalAuthorized += authorized;
    });
    
    // Calculate utilization per office and sort
    const performanceData = Object.values(officeData).map(data => ({
        office: data.office,
        utilization: data.totalAuthorized > 0 ? (data.totalObligations / data.totalAuthorized) * 100 : 0,
        obligations: data.totalObligations,
        authorized: data.totalAuthorized
    }));
    
    // Sort by utilization (top 5)
    const topPerformers = performanceData
        .sort((a, b) => b.utilization - a.utilization)
        .slice(0, 5);
    
    // Build ONLY the content HTML (not the container)
    let html = '';
    
    topPerformers.forEach((item, index) => {
        const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `${index + 1}.`;
        const barWidth = Math.min(item.utilization, 100);
        
        // Color based on performance
        let barColor = 'bg-green-500';
        if (item.utilization < 50) barColor = 'bg-yellow-500';
        if (item.utilization < 25) barColor = 'bg-red-500';
        
        html += `
            <div class="performance-item bg-gray-50 dark:bg-gray-700 rounded-lg p-3 hover:shadow-md transition-shadow duration-200">
                <div class="flex justify-between items-center mb-1">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">${medal}</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-100">${item.office}</span>
                    </div>
                    <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">${item.utilization.toFixed(2)}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 overflow-hidden">
                    <div class="${barColor} h-2 rounded-full transition-all duration-500" style="width: ${barWidth}%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mt-1">
                    <span>Obligations: ${item.obligations.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                    <span>Authorized Appropriations: ${item.authorized.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                </div>
            </div>
        `;
    });
    
    // Update only the content div
    const contentDiv = document.getElementById('topPerformersContent');
    if (contentDiv) {
        contentDiv.innerHTML = html;
    }
}

    /**
     * Toggle widget visibility
     */
    function toggleWidget(widgetId) {
        let content, toggle;
        
        if (widgetId === 'analyticsPanel') {
            content = document.getElementById('analyticsPanelContent');
            toggle = document.getElementById('analyticsPanelToggle');
        } else if (widgetId === 'topPerformersWidget') {
            content = document.getElementById('topPerformersContent');
            toggle = document.getElementById('topPerformersToggle');
        } else if (widgetId === 'allotmentDistributionWidget') {
            content = document.getElementById('allotmentDistributionContent');
            toggle = document.getElementById('allotmentDistributionToggle');
        } else if (widgetId === 'volumeMetricsWidget') {
            content = document.getElementById('volumeMetricsContent');
            toggle = document.getElementById('volumeMetricsToggle');
        }
        
        if (content && toggle) {
            const isHidden = content.style.display === 'none';
            content.style.display = isHidden ? 'block' : 'none';
            toggle.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            
            // If opening the analytics panel, trigger animation
            if (isHidden && widgetId === 'analyticsPanel') {
                setTimeout(() => {
                    animateStackedBar();
                }, 100);
            }
        }
    }

    /**
     * Toggle filters visibility
     */
    function toggleFilters() {
        const filterContent = document.getElementById('filterContent');
        const filterToggle = document.getElementById('filterToggle');
        
        if (filterContent && filterToggle) {
            const isHidden = filterContent.style.display === 'none';
            filterContent.style.display = isHidden ? 'grid' : 'none';
            filterToggle.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
        }
    }

    // Budget Alerts Widget removed - redundant with Top Performers

    // ============================================
    // COMPARISON YEAR-OVER-YEAR (YOY) INDICATORS
    // ============================================

    /**
     * Add YoY comparison indicators to cards (simulated - would need actual previous year data)
     */
    function addComparisonIndicators() {
        const cards = document.querySelectorAll('[data-card]');
        
        cards.forEach(card => {
            const valueElement = card.querySelector('.card-value');
            if (!valueElement || card.querySelector('.comparison-indicator')) return;
            
            // Simulate YoY change (in real scenario, fetch from backend)
            const change = (Math.random() - 0.5) * 20; // -10% to +10%
            const isIncrease = change > 0;
            
            const indicator = document.createElement('div');
            indicator.className = 'comparison-indicator flex items-center text-xs mt-1 space-x-1';
            indicator.innerHTML = `
                <i class="fas fa-arrow-${isIncrease ? 'up' : 'down'} ${isIncrease ? 'text-green-500' : 'text-red-500'}"></i>
                <span class="${isIncrease ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'} font-semibold">
                    ${Math.abs(change).toFixed(1)}%
                </span>
                <span class="text-gray-500 dark:text-gray-400">vs last year</span>
            `;
            
            valueElement.parentElement.appendChild(indicator);
        });
    }

    // ============================================
    // SKELETON LOADER
    // ============================================

    /**
     * Show skeleton loader during data updates
     */
    function showSkeletonLoader() {
        const cards = document.querySelectorAll('[data-card]');
        
        cards.forEach(card => {
            const valueElement = card.querySelector('.card-value');
            if (valueElement && !card.querySelector('.skeleton')) {
                const skeleton = document.createElement('div');
                skeleton.className = 'skeleton animate-pulse bg-gray-300 dark:bg-gray-600 h-6 w-32 rounded';
                valueElement.style.display = 'none';
                valueElement.parentElement.appendChild(skeleton);
            }
        });
    }

    function hideSkeletonLoader() {
        document.querySelectorAll('.skeleton').forEach(skeleton => {
            const valueElement = skeleton.previousElementSibling;
            if (valueElement) {
                valueElement.style.display = 'block';
            }
            skeleton.remove();
        });
    }

    // ============================================
    // SPARKLINE TREND CHARTS
    // ============================================

    /**
     * Add mini sparkline charts to cards showing trend
     */
    function addSparklines() {
        const cards = document.querySelectorAll('[data-card]');
        
        cards.forEach(card => {
            if (card.querySelector('.sparkline-container')) return;
            
            const sparklineContainer = document.createElement('div');
            sparklineContainer.className = 'sparkline-container mt-2';
            
            // Generate sample data (in real scenario, fetch from backend)
            const dataPoints = Array.from({length: 12}, () => Math.random() * 100);
            const max = Math.max(...dataPoints);
            const min = Math.min(...dataPoints);
            const range = max - min;
            
            // Create SVG sparkline
            const svgHeight = 30;
            const svgWidth = 100;
            const points = dataPoints.map((value, index) => {
                const x = (index / (dataPoints.length - 1)) * svgWidth;
                const y = svgHeight - ((value - min) / range) * svgHeight;
                return `${x},${y}`;
            }).join(' ');
            
            sparklineContainer.innerHTML = `
                <svg width="${svgWidth}" height="${svgHeight}" class="sparkline">
                    <polyline
                        points="${points}"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="text-indigo-500 dark:text-indigo-400"
                    />
                </svg>
            `;
            
            const mlDiv = card.querySelector('.ml-3, .ml-4');
            if (mlDiv) {
                mlDiv.appendChild(sparklineContainer);
            }
        });
    }

    // ============================================
    // SMOOTH SCROLL TO ROW
    // ============================================

    /**
     * Smooth scroll and highlight when clicking card
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
            'balance_appropriations': 'Authorized Appropriations Balance',
            'appropriation_accomplishment': 'Appropriation Utilization',
            'balance_allotments': 'Allotments Balance',
            'allotment_accomplishment': 'Allotments Utilization',
            'disbursements': 'Disbursements',
            'disbursement_balance': 'Obligations Balance',
            'disbursements_to_obligations': 'Disbursements / Obligations',
            'disbursements_to_appropriations': 'Disbursements / Approp.'
        };

        const cards = document.querySelectorAll('[data-card]');
        
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking toggle button
                if (e.target.closest('button')) return;
                
                createRipple(e);
                
                // Scroll to table
                const table = document.getElementById('dashboardTable');
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
        // Add ripple CSS
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .performance-item {
                animation: slideIn 0.3s ease-out;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            .alert-item {
                animation: fadeIn 0.3s ease-out;
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .sparkline {
                opacity: 0.7;
                transition: opacity 0.3s ease;
            }
            
            .sparkline:hover {
                opacity: 1;
            }
            
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
        
        // Initialize progress bar animations
        setTimeout(() => {
            animateProgressBars();
        }, 200);
        
        // Initialize all enhancements
        setTimeout(() => {
            createTopPerformersWidget();
            // addComparisonIndicators(); // Uncomment when you have real YoY data
            // addSparklines(); // Uncomment if you want sparklines
            setupCardClickHandlers();
        }, 500);
        
        // Make functions available globally
        window.toggleWidget = toggleWidget;
        window.toggleFilters = toggleFilters;
        window.createTopPerformersWidget = createTopPerformersWidget;
        window.openObligationsModalFromDropdown = openObligationsModalFromDropdown;
        window.showObligationsModal = showObligationsModal;
        window.closeObligationsModal = closeObligationsModal;
        window.openDashboardCancellationModal = openDashboardCancellationModal;
        window.closeDashboardCancellationModal = closeDashboardCancellationModal;
        window.proceedDashboardCancellation = proceedDashboardCancellation;
        window.openCreateModalWithClass = openCreateModalWithClass;
    });

    // Update widgets when filtering
    const originalFilterTable2 = window.filterTable;
    if (typeof originalFilterTable2 === 'function') {
        window.filterTable = function(searchValue) {
            originalFilterTable2(searchValue);
            setTimeout(() => {
                createTopPerformersWidget();
            }, 200);
        };
    }

    // Close success alert and fade out
    function closeSuccessAlert() {
        const alert = document.getElementById('successAlert');
        if (alert) {
            alert.classList.remove('animate-slide-in');
            alert.classList.add('animate-fade-out');
            setTimeout(() => {
                alert.remove();
            }, 300);
        }
    }

    // Auto-fade success alert after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                closeSuccessAlert();
            }, 5000);
        }

        // Initialize Volume Metrics Charts
        initializeVolumeMetricsCharts();
    });

    // Function to initialize volume metrics charts
    function initializeVolumeMetricsCharts() {
        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#d1d5db' : '#6b7280';
        const gridColor = isDarkMode ? '#4b5563' : '#e5e7eb';
        const bgColor = isDarkMode ? '#111827' : '#ffffff';
        
        // Obligation Distribution Histogram
        const obligationRanges = @json($obligationRanges);
        const ranges = obligationRanges.map(r => r.label);
        const counts = obligationRanges.map(r => r.count);

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
                categories: ranges,
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

        const histogramSeries = [{
            name: 'Obligations',
            data: counts
        }];

        new ApexCharts(document.querySelector('#obligationHistogram'), { 
            ...histogramOptions, 
            series: histogramSeries 
        }).render();

        // Obligations by Quarter Chart
        const obligationsByQuarter = @json($obligationsByQuarter);
        const quarters = obligationsByQuarter.map(q => q.quarter);
        const quarterCounts = obligationsByQuarter.map(q => q.count);

        const quarterOptions = {
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
                categories: quarters,
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

        const quarterSeries = [{
            name: 'Obligations Created',
            data: quarterCounts
        }];

        new ApexCharts(document.querySelector('#obligationsByQuarter'), { 
            ...quarterOptions, 
            series: quarterSeries 
        }).render();
    }

    // Auto-reopen obligation and purchase order modals after creating PO from dashboard
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('reopen_obligation_id') && session('reopen_po_modal'))
                const obligationId = {{ session('reopen_obligation_id') }};
                
                // Delay slightly to ensure page is fully loaded
                setTimeout(function() {
                    // Fetch and open the obligation modal first
                    fetch(`/obligations/${obligationId}/obligation-modal`)
                        .then(response => response.text())
                        .then(html => {
                            const existingModal = document.getElementById('editObligationModal');
                            if (existingModal && existingModal.parentNode) {
                                const temp = document.createElement('div');
                                temp.innerHTML = html;
                                const newModal = temp.querySelector('#editObligationModal');
                                if (newModal) {
                                    existingModal.replaceWith(newModal);
                                    // Show the obligation modal
                                    setTimeout(() => {
                                        const modal = document.getElementById('editObligationModal');
                                        if (modal) {
                                            modal.offsetHeight;
                                            modal.style.display = 'flex';
                                            modal.setAttribute('aria-hidden', 'false');
                                        }
                                    }, 50);
                                }
                            }
                        })
                        .catch(err => console.error('Error loading obligation modal:', err));
                    
                    // Then fetch and open the purchase order modal
                    setTimeout(function() {
                        fetch(`/obligations/${obligationId}/purchase-order-modal`)
                            .then(response => response.text())
                            .then(html => {
                                const existingForm = document.getElementById('CreatePurchaseOrderForm');
                                if (existingForm && existingForm.parentNode) {
                                    const temp = document.createElement('div');
                                    temp.innerHTML = html;
                                    const newForm = temp.querySelector('form');
                                    if (newForm) {
                                        existingForm.replaceWith(newForm);
                                        // Show the PO modal
                                        setTimeout(() => {
                                            const modal = document.getElementById('createPOModal');
                                            if (modal) {
                                                modal.offsetHeight;
                                                modal.style.display = 'flex';
                                                modal.setAttribute('aria-hidden', 'false');
                                            }
                                        }, 50);
                                    }
                                }
                            })
                            .catch(err => console.error('Error loading PO modal:', err));
                    }, 100);
                }, 300);
            @endif
        });
    
    </script>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }

        .animate-fade-out {
            animation: fadeOut 0.3s ease-out forwards;
        }

        /* ApexCharts Dark Mode Styling */
        .dark #obligationHistogram,
        .dark #obligationsByQuarter {
            background-color: #111827 !important;
        }

        .dark #obligationHistogram .apexcharts-canvas {
            background-color: #111827 !important;
        }

        .dark #obligationsByQuarter .apexcharts-canvas {
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

    <!-- Include Obligations Modals -->
    @include('obligations.modal.create')
    @include('obligations.modal.edit')
    
    <!-- Include Obligation Adjustments Create Modal -->
    @include('obligation_adjustments.modal.create')
    
    <!-- Include Purchase Order Modal -->
    @include('obligations.modal.purchase_order', ['obligation' => (object)['id' => null]])

    <!-- Obligation Details Modal -->
    @include('obligations.modal.obligation_details')

</x-app-layout>