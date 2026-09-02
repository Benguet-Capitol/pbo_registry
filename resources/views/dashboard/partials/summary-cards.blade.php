{{-- Dashboard summary/ratio cards row: Approved/Supplemental Appropriations, Reversions,
     Realignments, Purchase Orders, Disbursements, and the four utilization-ratio cards
     (Appropriations, Allotments, Disbursements/Obligations, Disbursements/Appropriations).
     Extracted from dashboard.blade.php to keep that file's length manageable. --}}
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
                <div class="edge-tooltip absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
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
                <div class="edge-tooltip absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
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
                <div class="edge-tooltip absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
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
                <div class="edge-tooltip absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-xs rounded shadow-xl whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
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
