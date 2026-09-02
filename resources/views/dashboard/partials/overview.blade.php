{{-- Dashboard overview section: office allotment class filters, the stacked-bar
     distribution graph with legend, activity metrics cards, Top 5 Highest Utilization
     widget, the histogram/quarter charts row, and the Obligation Distribution table.
     Extracted from dashboard.blade.php to keep that file's length manageable. --}}
    <!-- Unified Filter Section -->
     <div class="bg-white p-4 rounded-lg shadow-md mb-4 dark:bg-gray-800">
    <form method="GET" action=""  id="filterForm">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100">Filters</h4>
            <button type="button" onclick="toggleFilters()" class="text-blue-500 hover:text-gray-700 dark:text-blue-400 dark:hover:text-gray-200">
                <i class="fas fa-circle-chevron-down" id="filterToggle"></i>
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
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 {{ $isGuest ? 'lg:col-span-2' : '' }}">
                <label for="fromDate" class="text-xs font-semibold text-gray-700 dark:text-gray-100">From Date</label>
                <x-form.input type="date" name="from_date" id="fromDate" value="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
            </div>

            <!-- To Date Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 {{ $isGuest ? 'lg:col-span-2' : '' }}">
                <label for="toDate" class="text-xs font-semibold text-gray-700 dark:text-gray-100">To Date</label>
                <x-form.input type="date" name="to_date" id="toDate" value="{{ request('to_date') }}" min="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
            </div>

            <!-- Apply Filter Button -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-filter mr-2"></i>Apply Date Filter
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-center mb-2" id="filterContent" style="display: none;">
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
            <button onclick="toggleWidget('analyticsPanel')" class="text-blue-500 hover:text-gray-700 dark:text-blue-400 dark:hover:text-gray-200">
                <i class="fas fa-circle-chevron-down" id="analyticsPanelToggle"></i>
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
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-4">
                <label for="dashboardTable" class="ml-4 block text-md font-semibold text-blue-800 dark:text-blue-400">Office Allotment Classes</label>

                <div id="tableActionButtons" class="flex flex-wrap items-center gap-2">
                    @role('Guest')
                    <button
                        id="exportSaaobBtn"
                        type="button"
                        onclick="exportSaaob(this, '{{ route('saaob.exportExcel', ['year1' => $selectedYear, 'as_of_filter' => request('to_date')]) }}')"
                        class="text-green-700 inline-flex items-center leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        <i class="fas fa-file-excel mr-2" id="exportSaaobIcon"></i>
                        <span id="exportSaaobLabel">Generate SAAOB</span>
                    </button>
                    @endrole
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400 mb-2 sm:hidden">
                <i class="fas fa-arrows-left-right"></i> Swipe left/right to see more columns. The first column stays pinned.
            </div>
            <div class="overflow-auto max-h-[60vh] sm:max-h-[720px] rounded-lg border border-gray-300 dark:border-gray-700">
                <table id="dashboardTable" class="w-full text-[11px] text-gray-700 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-200 text-gray-900 dark:bg-gray-900 dark:text-gray-200 border-t-2 border-b-2 border-gray-700">
                        <tr>
                            <th class="sticky left-0 z-20 px-2 py-2 w-[70px] text-center bg-gray-200 dark:bg-gray-900 border-r-2 border-gray-400 dark:border-gray-600 border-l-4 border-l-gray-500">View Details</th>
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
                            class="group {{ $loop->odd ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900/40' }} border dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer"
                            ondblclick="window.location.href='{{ route('dashboard.accounts', $class->id) }}{{ $dateFilterQuery ? '?'.$dateFilterQuery : '' }}'"
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
                            <td class="sticky left-0 z-[1] px-1 py-2 text-center {{ $loop->odd ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900/40' }} group-hover:bg-gray-100 dark:group-hover:bg-gray-600 border-r-2 border-gray-300 dark:border-gray-600 border-l-4 border-l-gray-500">
                                <div class="relative inline-block text-left">
                                    <!-- Dropdown Button -->
                                    <button onclick="toggleDropdown(this)"
                                        class="relative text-xs group px-2 py-1.5">
                                        <span class="fas fa-forward"></span>
                                        <!-- Tooltip -->
                                        <span class="absolute left-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-normal break-words max-w-[45vw] sm:max-w-[220px] z-20">
                                            {{ $class->offices->office_abbreviation ?? 'No Office' }} - {{ $class->allotmentClass->description ?? 'No Class' }}
                                        </span>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div class="absolute top-full left-0 mt-1 w-48 z-50 hidden dropdown-menu bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 border-2 border-blue-400 dark:border-blue-600 rounded-lg shadow-2xl origin-top-right">
                                        <a href="{{ route('dashboard.accounts', $class->id) }}{{ $dateFilterQuery ? '?'.$dateFilterQuery : '' }}" class="flex items-center px-4 py-2 text-xs text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-t-lg transition-colors duration-150 group">
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
                            <td class="px-1 py-2 text-center">
                                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold inline-block">{{ $class->office_abbreviation }}</span>
                            </td>
                            <td class="px-1 py-2 text-center text-gray-900 dark:text-white font-bold">{{ $class->class }}</td>
                            @role('Disbursement|Administrator|Developer|Obligation')
                            <td class="px-1 py-2 text-center">
                                @php
                                    $fundTypeCategory = $class->fundSourceRelation->category ?? null;
                                    $fundTypeColor = match ($fundTypeCategory) {
                                        'Current' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400',
                                        'Continuing' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
                                        default => 'text-gray-600 dark:text-gray-300',
                                    };
                                @endphp
                                @if($fundTypeCategory)
                                    <span class="px-2 py-1 rounded {{ $fundTypeColor }} font-semibold inline-block">{{ $fundTypeCategory }}</span>
                                @else
                                    -
                                @endif
                            </td>
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
                                    <div class="edge-tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
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
                                    <div class="edge-tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
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
                                    <!-- Tooltip: opens below the value (not .edge-tooltip — that class's JS assumes
                                         centered positioning, but this one is right-anchored) so it stacks clear
                                         of the Disbursements / Approp. tooltip beside it instead of overlapping -->
                                    <div class="absolute top-full right-0 mt-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50 text-left">
                                        <!-- Arrow -->
                                        <div class="absolute bottom-full right-4 border-4 border-transparent border-b-gray-900"></div>
                                        <div class="font-semibold">Disbursements / Obligations</div>
                                        <div>{{ number_format($class->disbursements_to_obligations, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300 mt-1">Disbursements: {{ number_format($class->disbursements_sum, 2) }}</div>
                                        <div class="text-[10px] text-gray-300">Obligations: {{ number_format($class->obligations_sum, 2) }}</div>
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
                                    <!-- Tooltip: right-anchored, not .edge-tooltip (that class's JS assumes centered positioning) -->
                                    <div class="absolute bottom-full right-0 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50 text-left">
                                        <div class="font-semibold">Disbursements / Appropriations</div>
                                        <div>{{ number_format($class->disbursements_to_appropriations, 2) }}%</div>
                                        <div class="text-[10px] text-gray-300 mt-1">Disbursements: {{ number_format($class->disbursements_sum, 2) }}</div>
                                        <div class="text-[10px] text-gray-300">Authorized Approp.: {{ number_format($class->authorized_appropriations, 2) }}</div>
                                        <!-- Arrow -->
                                        <div class="absolute top-full right-4 border-4 border-transparent border-t-gray-900"></div>
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
