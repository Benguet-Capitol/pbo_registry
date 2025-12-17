<x-app-layout>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
                @endphp

                @if (count($filters) > 0)
                | <span class="text-blue-800 dark:text-blue-400">{{ implode(' / ', $filters) }}
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

    <!-- Unified Filter Section -->
     <div class="bg-white p-4 rounded-lg shadow-md mb-2 dark:bg-gray-800">
    <form method="GET" action=""  id="filterForm">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-center mb-2">

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
            <!-- Office Filter -->
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
           
            <!-- Per Page Dropdown -->
            <!-- <div class="flex items-center space-x-2">
                <x-form.select
                    name="per_page"
                    id="perPage"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    data-default="all"
                    onchange="this.form.submit()"
                >
                    <option value="10" {{ request('per_page', 'all') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 'all') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 'all') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 'all') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page', 'all') == 'all' ? 'selected' : '' }}>Show All</option>
                </x-form.select>
            </div> -->
        </div>
    </form>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-center">
     <!-- Search Input -->
            <div class="flex items-center space-x-2 lg:col-span-3">
                <x-form.input type="text" name="search" id="searchInput" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
            </div>
    </div>
    </div>

    {{-- Allotment Class Distribution Graph --}}
    <div class="mb-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">
                Allotment Class Distribution by Authorized Appropriations
            </h3>
            
            <!-- Stacked Bar - Reduced height -->
            <div id="stackedBarContainer" class="mb-2 relative">
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
            @role('Disbursement|Administrator|Developer')
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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4 mb-4 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <label for="dashboardTable" class="ml-4 block text-md font-semibold text-gray-700 dark:text-gray-200">Office Allotment Classes</label>
            </div>
            <div class="overflow-auto max-h-[720px] rounded-lg border border-gray-300 dark:border-gray-700">
                <table id="dashboardTable" class="w-full text-[11px] text-gray-700 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-200 border-t-2 border-b-2 border-gray-700">
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
                            @role('Disbursement|Administrator|Developer')
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements</th>
                            <th class="px-2 py-2 w-[100px] text-center">Obligations Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements / Oblgations</th>
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
                                    <div class="absolute top-full left-0 mt-1 w-48 z-50 hidden dropdown-menu bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-md origin-top-right">
                                        <a href="{{ route('dashboard.accounts', $class->id) }}" class="flex items-center px-4 py-2 text-xs hover:bg-gray-200 dark:hover:bg-gray-600 group">
                                            <i class="fas fa-stream mr-2"></i> Accounts
                                        </a>
                                        <!-- <a href="#" class="block px-4 py-2 text-xs hover:bg-gray-200 dark:hover:bg-gray-600">
                                            <i class="fas fa-list-check mr-2"></i>Obligations
                                        </a>
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
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-1"
                                            style="width: {{ min($class->appropriation_accomplishment, 100) }}%">
                                            @if($class->appropriation_accomplishment > 15)
                                                <span class="text-white text-[9px] font-semibold">{{ number_format($class->appropriation_accomplishment, 1) }}%</span>
                                            @endif
                                        </div>
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
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-1"
                                            style="width: {{ min($class->allotment_accomplishment, 100) }}%">
                                            @if($class->allotment_accomplishment > 15)
                                                <span class="text-white text-[9px] font-semibold">{{ number_format($class->allotment_accomplishment, 1) }}%</span>
                                            @endif
                                        </div>
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
                            @role('Disbursement|Administrator|Developer')
                            <td class="px-1 py-2 text-right">{{ number_format($class->disbursements_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->disbursement_balance, 2) }}</td>
                            <!-- Disbursements / Obligations Cell (if role allowed) -->
                            <td class="px-1 py-2">
                                <div class="relative group">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-1"
                                            style="width: {{ min($class->disbursements_to_obligations, 100) }}%">
                                            @if($class->disbursements_to_obligations > 15)
                                                <span class="text-white text-[9px] font-semibold">{{ number_format($class->disbursements_to_obligations, 1) }}%</span>
                                            @endif
                                        </div>
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
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-1"
                                            style="width: {{ min($class->disbursements_to_appropriations, 100) }}%">
                                            @if($class->disbursements_to_appropriations > 15)
                                                <span class="text-white text-[9px] font-semibold">{{ number_format($class->disbursements_to_appropriations, 1) }}%</span>
                                            @endif
                                        </div>
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

    <style>
    /* Circular progress animation */
    .circular-progress-bar {
        transition: stroke-dasharray 0.6s ease-in-out;
    }

    /* Dark mode stroke color adjustments */
    .dark .circular-progress-bg {
        stroke: #374151;
    }
    </style>

    <script>
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
            searchInput.addEventListener('input', function() {
                filterTable(this.value);
            });
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

                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('searchInput');
                    searchInput.addEventListener('input', function() {
                        filterTable(this.value);
                    });

                });

                document.addEventListener('click', () => {
                document.getElementById('classContextMenu').classList.add('hidden');
                
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
                        class="${color} ${hoverColor} h-8 transition-all duration-200 ease-out flex items-center justify-center relative cursor-pointer"
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
    </script>

</x-app-layout>