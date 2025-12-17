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

    <div class="bg-white p-4 rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 items-center">
            <!-- Search Input -->
                <div class="flex items-center space-x-2 lg:col-span-3">
                    <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>
        </div>
    </div>

    {{-- Account Distribution Graph --}}
    <div class="mb-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">
                Account Distribution by Authorized Appropriations
            </h3>
            
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
                        Disbursements Balance
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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4 mb-4 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <label for="dashboardTable" class="ml-4 block text-md font-semibold text-blue-800 dark:text-blue-400">
                    {{ $officeAllotmentClasses->offices->office_name ?? 'Office N/A' }} -
                    {{ $officeAllotmentClasses->class ?? 'Class N/A' }} Accounts
                </label>
            </div>
            <div class="overflow-x-auto max-h-[720px] border border-gray-300 dark:border-gray-700 rounded-lg">
                <table id="accountsTable" class="w-full text-[11px] text-gray-700 dark:text-gray-300 text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-gray-200 border-t-2 border-b-2 border-gray-700">
                        <tr>
                            <th class="px-2 py-2 w-[120px] text-center">Programs</th>
                            <th class="px-2 py-2 w-[120px] text-center">Account Code</th>
                            <th class="px-2 py-2 w-[130px] text-center">Description</th>
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
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-1"
                                            style="width: {{ min($appropriation->appropriation_accomplishment, 100) }}%">
                                            @if($appropriation->appropriation_accomplishment > 15)
                                                <span class="text-white text-[9px] font-semibold">{{ number_format($appropriation->appropriation_accomplishment, 1) }}%</span>
                                            @endif
                                        </div>
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
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-1"
                                            style="width: {{ min($appropriation->allotment_accomplishment, 100) }}%">
                                            @if($appropriation->allotment_accomplishment > 15)
                                                <span class="text-white text-[9px] font-semibold">{{ number_format($appropriation->allotment_accomplishment, 1) }}%</span>
                                            @endif
                                        </div>
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
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-1"
                                            style="width: {{ min($appropriation->disbursements_to_obligations, 100) }}%">
                                            @if($appropriation->disbursements_to_obligations > 15)
                                                <span class="text-white text-[9px] font-semibold">{{ number_format($appropriation->disbursements_to_obligations, 1) }}%</span>
                                            @endif
                                        </div>
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
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-1"
                                            style="width: {{ min($appropriation->disbursements_to_appropriations, 100) }}%">
                                            @if($appropriation->disbursements_to_appropriations > 15)
                                                <span class="text-white text-[9px] font-semibold">{{ number_format($appropriation->disbursements_to_appropriations, 1) }}%</span>
                                            @endif
                                        </div>
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
    </script>

</x-app-layout>