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

    {{-- Allotment Class Distribution Graph --}}
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
                Allotment Class Distribution by Authorized Appropriations
            </h3>
            
            <!-- Stacked Bar -->
            <div id="stackedBarContainer" class="mb-6">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-12 overflow-hidden flex">
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
                        
                        // Assign colors
                        $colors = [
                            'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 
                            'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-cyan-500',
                            'bg-orange-500', 'bg-teal-500', 'bg-lime-500', 'bg-amber-500'
                        ];
                    @endphp

                    @foreach($classDistribution as $index => $class)
                        @php
                            $percentage = $totalForPercentage > 0 ? ($class['total'] / $totalForPercentage) * 100 : 0;
                            $colorIndex = crc32($class['class_code']) % count($colors);
                            $barColor = $colors[$colorIndex];
                        @endphp
                        
                        <div 
                            class="{{ $barColor }} h-12 transition-all duration-500 ease-out flex items-center justify-center relative group stacked-segment"
                            style="width: {{ $percentage }}%"
                            data-class="{{ $class['class_code'] }}"
                            data-description="{{ $class['description'] }}"
                            data-total="{{ $class['total'] }}"
                            data-percentage="{{ $percentage }}"
                        >
                            @if($percentage > 3)
                                <span class="text-white text-xs font-semibold px-1 text-center truncate">
                                    {{ $class['class_code'] }}
                                </span>
                            @endif
                            
                            <!-- Tooltip on hover -->
                            <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 hidden group-hover:block bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap z-20 shadow-lg">
                                <div class="font-semibold">{{ $class['class_code'] }} - {{ $class['description'] }}</div>
                                <div>{{ number_format($percentage, 2) }}%</div>
                                <div>₱{{ number_format($class['total'], 2) }}</div>
                                <!-- Arrow -->
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Legend -->
            <div id="graphLegend" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
                @foreach($classDistribution as $index => $class)
                    @php
                        $percentage = $totalForPercentage > 0 ? ($class['total'] / $totalForPercentage) * 100 : 0;
                        $colorIndex = crc32($class['class_code']) % count($colors);
                        $barColor = $colors[$colorIndex];
                    @endphp
                    
                    <div class="flex items-center space-x-2 text-xs">
                        <div class="w-4 h-4 {{ $barColor }} rounded flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-700 dark:text-gray-300 truncate">
                                {{ $class['class_code'] }}
                            </div>
                            <div class="text-gray-500 dark:text-gray-400">
                                {{ number_format($percentage, 1) }}%
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary Stats -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <div id="graphSummaryStats" class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total Classes</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $classDistribution->count() }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total Amount</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            ₱{{ number_format($totalForPercentage, 2) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Largest Class</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $classDistribution->first()['class_code'] ?? 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Largest %</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ $classDistribution->isNotEmpty() && $totalForPercentage > 0 ? number_format(($classDistribution->first()['total'] / $totalForPercentage) * 100, 2) : '0.00' }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    {{-- Dashboard Cards Row --}}
    <div class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-3 md:p-4 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="approved_appropriations">
                <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-file-signature text-blue-600 dark:text-blue-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
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
                    <i class="fas fa-check-circle text-sky-600 dark:text-sky-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
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
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
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
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Authorized Appropriations Balance
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalAuthorizedAppropriationsBalance, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="appropriation_accomplishment">
                <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-percent text-indigo-600 dark:text-indigo-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Obligation / Authorized Appropriations
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalAuthorizedAppropriationsAccomplishment, 2) }}%
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="balance_allotments">
                <div class="flex-shrink-0 bg-pink-100 dark:bg-pink-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-stream text-pink-600 dark:text-pink-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Allotments Balance
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($allotmentBalance, 2) }}
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="allotment_accomplishment">
                <div class="flex-shrink-0 bg-teal-100 dark:bg-teal-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-percentage text-teal-600 dark:text-teal-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Obligations / Allotments
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($allotmentAccomplishment, 2) }}%
                    </div>
                </div>
            </div>
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="disbursements_to_obligations">
                <div class="flex-shrink-0 bg-lime-100 dark:bg-lime-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-percentage text-lime-600 dark:text-lime-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Disbursements / Obligations
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalDisbursementsToObligations, 2) }}%
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-5 md:p-6 flex items-center transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-card="disbursements_to_appropriations">
                <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900 rounded-full p-2 sm:p-3">
                    <i class="fas fa-percentage text-amber-600 dark:text-amber-300 text-lg sm:text-xl md:text-2xl"></i>
                </div>
                <div class="ml-3 sm:ml-4">
                    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase">
                        Disbursements / Authorized Appropriations
                    </div>
                    <div class="text-base sm:text-lg md:text-lg font-bold text-gray-800 dark:text-gray-100 break-words card-value">
                        {{ number_format($totalDisbursementsToAppropriations, 2) }}%
                    </div>
                </div>
            </div>

            {{-- Add more cards here if needed --}}
        </div>
    </div>

    <!-- Unified Filter Section -->
    <form method="GET" action="" class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800" id="filterForm">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 items-center">

            <!-- Year Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="year1"
                    id="year1"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6 mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <label for="dashboardTable" class="ml-4 block text-md font-semibold text-gray-700 dark:text-gray-200">Allotment Classes</label>

                <!-- Search Input -->
                <div class="flex items-center space-x-2">
                    <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-72 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>
            </div>
            <div class="overflow-auto max-h-[720px] rounded-lg border border-gray-300 dark:border-gray-700">
                <table id="dashboardTable" class="w-full text-[11px] text-gray-700 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-200 border-t-2 border-b-2 border-gray-700">
                        <tr>
                            <th class="px-2 py-2 w-[70px] text-center">View Details</th>
                            <th class="px-2 py-2 w-[100px] text-center">Office</th>
                            <th class="px-2 py-2 w-[120px] text-center">Allotment Class</th>
                            <th class="px-2 py-2 w-[120px] text-center">Fund Type</th>
                            <th class="px-2 py-2 w-[100px] text-center">FPP Code</th>
                            <th class="px-2 py-2 w-[100px] text-center">Approved Appropriations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Supplemental Appropriations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Reversions</th>
                            <th class="px-2 py-2 w-[100px] text-center">Realignments</th>
                            <th class="px-2 py-2 w-[100px] text-center">Authorized Appropriations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Allotments</th>
                            <th class="px-2 py-2 w-[100px] text-center">For Later Release</th>
                            <th class="px-2 py-2 w-[100px] text-center">Obligations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Authorized Appropriations Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Appropriation Accomp.</th>
                            <th class="px-2 py-2 w-[100px] text-center">Allotments Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Allotments Accomp.</th>
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements</th>
                            <th class="px-2 py-2 w-[100px] text-center">Obligations Balance</th>
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements / Oblgations</th>
                            <th class="px-2 py-2 w-[100px] text-center">Disbursements / Approp.</th>
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
                            <td class="px-1 py-2 text-center">{{ $class->office_abbreviation }}</td>
                            <td class="px-1 py-2 text-center">{{ $class->class }}</td>
                            <td class="px-1 py-2 text-center">{{ $class->fundSourceRelation->category ?? '-' }}</td>
                            <td class="px-1 py-2 text-center">{{ $class->fpp_code }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->appropriations_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right {{ $class->supplemental_sum != 0 ? 'text-green-600 font-semibold' : 'text-gray-600 dark:text-gray-300' }}">{{ number_format($class->supplemental_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right {{ $class->reversion_sum != 0 ? 'text-red-600 font-semibold' : 'text-gray-600 dark:text-gray-300' }}">{{ number_format($class->reversion_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right 
                                    {{ $class->realignments_sum < 0 ? 'text-red-600 font-semibold' : ($class->realignments_sum > 0 ? 'text-green-600 font-semibold' : 'text-gray-700 dark:text-gray-300') }}">
                                {{ number_format($class->realignments_sum, 2) }}
                            </td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->authorized_appropriations, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->allotments_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->for_later_release, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->obligations_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->balance_appropriations, 2) }}</td>
                            <td class="px-1 py-2 text-center">{{ number_format($class->appropriation_accomplishment, 2) }}%</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->balance_allotments, 2) }}</td>
                            <td class="px-1 py-2 text-center">{{ number_format($class->allotment_accomplishment, 2) }}%</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->disbursements_sum, 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($class->disbursement_balance, 2) }}</td>
                            <td class="px-1 py-2 text-center">{{ number_format($class->disbursements_to_obligations, 2) }}%</td>
                            <td class="px-1 py-2 text-center">{{ number_format($class->disbursements_to_appropriations, 2) }}%</td>
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

            // Update card values
            for (const [cardKey, total] of Object.entries(totals)) {
                const card = document.querySelector(`[data-card="${cardKey}"]`);
                if (card) {
                    const cardValue = card.querySelector('.card-value');
                    if (cardValue) {
                        cardValue.textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        
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

    // Fixed color palette - each class gets a specific color
    const fixedColorAssignments = {
        'PS': 'bg-blue-500',           // Personnel Services - Blue
        'MOOE': 'bg-green-500',        // Maintenance & Other Operating Expenses - Green
        'CO': 'bg-cyan-500',         // Capital Outlay - Yellow
        'FE': 'bg-red-500',            // Financial Expenses - Red
        'CCO': 'bg-violet-500',        // Capital and Capital Outlay - Purple
    };
    
    // Fallback color palette for any additional unknown classes
    const fallbackColorPalette = [
        'bg-pink-600', 'bg-indigo-600', 'bg-cyan-600', 'bg-orange-600',
        'bg-teal-600', 'bg-lime-600', 'bg-amber-600', 'bg-rose-600', 
        'bg-emerald-600', 'bg-fuchsia-600', 'bg-sky-600', 'bg-violet-600'
    ];

    // Hash function for consistent color assignment for unknown classes
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
        // First check if we have a fixed assignment for this class
        if (fixedColorAssignments[classCode]) {
            return fixedColorAssignments[classCode];
        }
        
        // Otherwise use hash-based assignment from fallback palette
        const index = hashCode(classCode) % fallbackColorPalette.length;
        return fallbackColorPalette[index];
    }

    function updateGraph() {
        const rows = document.querySelectorAll('#dashboardTable tbody tr');
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        
        // Recalculate class distribution based on visible rows
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
        
        // Sort by total amount descending
        const sortedClasses = Object.values(classData).sort((a, b) => b.total - a.total);
        
        // Update stacked bar
        const stackedBarContainer = document.getElementById('stackedBarContainer');
        if (!stackedBarContainer) return;
        
        let barHTML = '<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-12 overflow-hidden flex">';
        
        if (sortedClasses.length === 0 || totalAmount === 0) {
            barHTML = `
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-12 flex items-center justify-center">
                    <span class="text-gray-500 dark:text-gray-400 text-sm italic">No data available</span>
                </div>
            `;
        } else {
            sortedClasses.forEach(classItem => {
                const percentage = totalAmount > 0 ? (classItem.total / totalAmount) * 100 : 0;
                const color = getColorForClass(classItem.code);
                
                barHTML += `
                    <div 
                        class="${color} h-12 transition-all duration-500 ease-out flex items-center justify-center relative group"
                        style="width: ${percentage}%"
                    >
                        ${percentage > 3 ? `<span class="text-white text-xs font-semibold px-1 text-center truncate">${classItem.code}</span>` : ''}
                        
                        <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 hidden group-hover:block bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap z-20 shadow-lg">
                            <div class="font-semibold">${classItem.code}</div>
                            <div>${percentage.toFixed(2)}%</div>
                            <div>₱${classItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                `;
            });
        }
        
        barHTML += '</div>';
        stackedBarContainer.innerHTML = barHTML;
        
        // Update legend
        updateLegend(sortedClasses, totalAmount);
        
        // Update summary stats
        updateSummaryStats(sortedClasses, totalAmount);
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
                        <div class="font-medium text-gray-700 dark:text-gray-300 truncate">
                            ${classItem.code}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            ${percentage.toFixed(3)}%
                        </div>
                    </div>
                </div>
            `;
        });
        
        legendContainer.innerHTML = legendHTML;
    }

    function updateSummaryStats(sortedClasses, totalAmount) {
        const summaryContainer = document.getElementById('graphSummaryStats');
        if (!summaryContainer) return;
        
        const largestClass = sortedClasses[0];
        const largestPercentage = largestClass && totalAmount > 0 
            ? (largestClass.total / totalAmount) * 100 
            : 0;
        
        summaryContainer.innerHTML = `
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total Classes</div>
                <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                    ${sortedClasses.length}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total Amount</div>
                <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                    ₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Largest Class</div>
                <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                    ${largestClass ? largestClass.code : 'N/A'}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Largest %</div>
                <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                    ${largestPercentage.toFixed(2)}%
                </div>
            </div>
        `;
    }

    // Store the original filterTable and updateCardValues functions
    document.addEventListener('DOMContentLoaded', function() {
        // Override filterTable to include graph updates
        const originalFilterTable = window.filterTable;
        
        window.filterTable = function(searchValue) {
            const rows = document.querySelectorAll('#dashboardTable tbody tr');
            const lowerSearch = String(searchValue).toLowerCase();

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(lowerSearch)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update card values
            if (typeof updateCardValues === 'function') {
                updateCardValues();
            }
            
            // Update graph
            updateGraph();
        };
        
        // Also add direct listener to search input as backup
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                updateGraph();
            });
        }
        
        // Trigger initial graph render
        setTimeout(() => {
            updateGraph();
        }, 100);
    });

    </script>

</x-app-layout>