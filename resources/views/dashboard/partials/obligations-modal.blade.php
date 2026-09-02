{{-- Obligations Modal (dashboard): listing modal, its row context menu, the obligation
     history modal, and the cancellation modal, plus all of their JS. Extracted from
     dashboard.blade.php to keep that file's length manageable. --}}
    <!-- Obligations Modal -->
    <div id="obligationsModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50 p-2 sm:p-4">
        <div class="flex flex-col max-h-[95vh] sm:max-h-[90vh] w-full max-w-screen-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal Header -->
            <div class="flex justify-between items-start sm:items-center gap-2 px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-b-2 border-blue-200 dark:border-blue-700 rounded-t-lg">
                <div class="flex items-start sm:items-center gap-2 sm:gap-3 min-w-0">
                    <i class="fas fa-list-check text-blue-600 dark:text-blue-300 text-lg sm:text-xl mt-0.5 sm:mt-0"></i>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base leading-6 font-semibold text-blue-900 dark:text-blue-100">
                            Obligations
                        </h3>
                        <span id="obligationsHeaderInfo" class="block text-[11px] sm:text-xs text-blue-700 dark:text-blue-300 break-words"></span>
                    </div>
                </div>
                <button onclick="closeObligationsModal()" class="flex-shrink-0 text-blue-600 dark:text-blue-300 hover:text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>

            <!-- Search and Total Records Section -->
            <div class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-blue-50/50 dark:bg-blue-950/20 space-y-2.5">
                <!-- Row 1: Records summary + Clear Filters -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center space-x-2 px-3 sm:px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 flex-shrink-0">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Records:</span>
                        <span id="obligationsTotalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">0</span>
                        <span id="obligationsTotalRecordsOfWrap" class="hidden text-xs text-blue-500 dark:text-blue-400">of <span id="obligationsTotalRecordsAll">0</span></span>
                    </div>
                    <!-- Clear All Filters -->
                    <button
                        type="button"
                        id="obligationsClearFiltersBtn"
                        onclick="clearObligationsFilters('dashboard')"
                        class="hidden items-center gap-1.5 px-3 py-2 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors flex-shrink-0"
                    >
                        <i class="fas fa-filter-circle-xmark"></i>
                        <span>Clear Filters</span>
                        <span id="obligationsActiveFilterCount" class="inline-flex items-center justify-center w-4 h-4 text-[10px] rounded-full bg-red-600 text-white">0</span>
                    </button>
                </div>

                <!-- Row 2: Filter controls -->
                <div class="flex flex-wrap items-center gap-2 pt-2.5 border-t border-blue-100 dark:border-blue-900">
                    <!-- Date Range Filter -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex-shrink-0"><i class="far fa-calendar-alt mr-1"></i>Date:</span>
                        <select
                            id="obligationsDatePreset"
                            onchange="applyObligationsDatePreset(this.value, 'dashboard')"
                            class="px-2 py-2 border border-gray-300 rounded-lg text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                        >
                            <option value="">Custom</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_quarter">This Quarter</option>
                            <option value="ytd">This Year (YTD)</option>
                        </select>
                        <input
                            type="date"
                            id="obligationsDateFrom"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                            onchange="document.getElementById('obligationsDatePreset').value = ''; refreshObligationsModal(); updateObligationsFilterIndicator('dashboard')"
                        >
                        <span class="text-gray-600 dark:text-gray-400 text-xs">to</span>
                        <input
                            type="date"
                            id="obligationsDateTo"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                            onchange="document.getElementById('obligationsDatePreset').value = ''; refreshObligationsModal(); updateObligationsFilterIndicator('dashboard')"
                        >
                    </div>
                    <!-- Vertical divider (desktop only) -->
                    <div class="hidden sm:block w-px self-stretch bg-blue-200 dark:bg-blue-900 mx-1"></div>
                    <!-- Search Input -->
                    <div class="relative w-full sm:flex-1 sm:min-w-[180px]">
                        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        <input
                            type="text"
                            id="obligationsSearchInput"
                            placeholder="Search obligations..."
                            class="w-full pl-8 pr-8 py-2 border border-gray-300 rounded-lg text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400"
                            oninput="filterObligationsTable(this.value, 'dashboard'); toggleObligationsSearchClear(this.value, 'dashboard'); updateObligationsFilterIndicator('dashboard')"
                        >
                        <i id="obligationsSearchSpinner" class="hidden fas fa-circle-notch fa-spin absolute right-8 top-1/2 -translate-y-1/2 text-blue-400 text-xs"></i>
                        <button
                            type="button"
                            id="obligationsSearchClearBtn"
                            onclick="clearObligationsSearch('dashboard')"
                            class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                            title="Clear search"
                        >
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="overflow-y-auto flex-1 max-h-[calc(95vh-260px)] sm:max-h-[calc(90vh-280px)] px-3 sm:px-6 py-3 sm:py-4">
                <div id="obligationsContent" class="space-y-4">
                    <!-- Loading spinner -->
                    <div id="obligationsLoading" class="flex justify-center items-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 p-3 sm:p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg flex-shrink-0">
                <span id="obligationsExportHint" class="text-[11px] text-gray-500 dark:text-gray-400 text-center sm:text-left order-2 sm:order-1"></span>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 order-1 sm:order-2">
                    <button id="obligationsExportBtn" onclick="exportObligationsModal('dashboard')" disabled class="w-full sm:w-auto justify-center bg-green-600 dark:bg-green-600 text-white inline-flex leading-4 tracking-wider items-center hover:bg-green-700 dark:hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-green-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 disabled:hover:scale-100 disabled:active:scale-100 rounded-lg">
                        <i id="obligationsExportIcon" class="fas fa-file-excel mr-2"></i>
                        <span id="obligationsExportLabel">Export</span>
                    </button>
                    <button onclick="closeObligationsModal()" class="w-full sm:w-auto justify-center text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                        <i class="fas fa-times mr-2"></i>
                        Close
                        <kbd class="ml-2 hidden sm:inline text-[10px] font-mono bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-300 px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-500">Esc</kbd>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Context Menu for Obligation Rows in Dashboard Modal -->
    @role('Administrator|Developer|Obligation')
    <div id="dashboardObligationContextMenu" 
        class="absolute hidden w-48 max-w-[calc(100vw-1rem)] bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 border-2 border-blue-400 dark:border-blue-600 rounded-lg shadow-2xl"
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
    <div id="obligationHistoryModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50 p-2 sm:p-4">
        <div class="flex flex-col max-h-[95vh] sm:max-h-[90vh] w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal header -->
            <div class="flex justify-between items-start sm:items-center gap-2 px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-900 dark:to-slate-900 border-b-2 border-gray-200 dark:border-gray-700 rounded-t-lg">
                <div class="flex items-start sm:items-center gap-2 sm:gap-3 min-w-0">
                    <i class="fas fa-history text-gray-600 dark:text-gray-300 text-lg sm:text-xl mt-0.5 sm:mt-0"></i>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
                            Obligation Status/History
                        </h3>
                        <span id="historyObligationInfo" class="block text-[11px] sm:text-xs text-gray-600 dark:text-gray-400 break-words"></span>
                    </div>
                </div>
                <button type="button" onclick="closeObligationHistoryModal()" class="flex-shrink-0 text-gray-600 dark:text-gray-300 hover:text-white hover:bg-gray-600 dark:hover:bg-gray-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>
            <!-- Modal body (scrollable) -->
            <div id="historyContent" class="overflow-y-auto flex-1 max-h-[calc(95vh-220px)] sm:max-h-[calc(90vh-240px)] p-3 sm:p-6 space-y-3">
                <div class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-500"></div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex flex-col sm:flex-row sm:justify-end gap-2 sm:gap-3 p-3 sm:p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                <button type="button" onclick="closeObligationHistoryModal()" class="w-full sm:w-auto justify-center text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal for Dashboard -->
    <form id="dashboardCancelObligationForm" method="POST">
        <div id="dashboardCancellationModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10004] flex items-center justify-center bg-black bg-opacity-50 p-2 sm:p-4">
            <div class="flex flex-col max-h-[95vh] sm:max-h-[90vh] w-full max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
                <!-- Modal header -->
                <div class="flex justify-between items-center px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900 dark:to-violet-900 border-b-2 border-purple-200 dark:border-purple-700 rounded-t-lg">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <i class="fas fa-ban text-purple-600 dark:text-purple-300 text-lg sm:text-xl"></i>
                        <h3 class="text-sm sm:text-base leading-6 font-semibold text-purple-900 dark:text-purple-100">
                            Cancel Obligation
                        </h3>
                    </div>
                    <button type="button" onclick="closeDashboardCancellationModal()" class="flex-shrink-0 text-purple-600 dark:text-purple-300 hover:text-white hover:bg-purple-600 dark:hover:bg-purple-700 rounded-full p-2 transition-colors duration-200">
                        <i class="fas fa-times text-lg sm:text-xl"></i>
                    </button>
                </div>
                <!-- Modal body (scrollable) -->
                <div class="overflow-y-auto flex-1 max-h-[calc(95vh-260px)] sm:max-h-[calc(90vh-280px)] p-3 sm:p-6">
                    <input type="hidden" id="dashboardHiddenObligationId" name="obligation_id" value="">
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">
                        Do you want to proceed with the cancellation of this Obligation? If cancelled, the obligation amount will be set to zero.
                    </p>

                    <div class="mt-4 overflow-x-auto">
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
                <div class="flex flex-col sm:flex-row sm:justify-end gap-2 sm:gap-3 p-3 sm:p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                    <button type="button" onclick="proceedDashboardCancellation()" class="w-full sm:w-auto justify-center text-red-600 dark:text-red-400 inline-flex leading-4 tracking-wider hover:text-white border border-red-600 dark:border-red-500 hover:bg-red-600 dark:hover:bg-red-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                        <i class="fas fa-window-close mr-2"></i>
                        Proceed
                    </button>
                    <button type="button" onclick="closeDashboardCancellationModal()" class="w-full sm:w-auto justify-center text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
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
                 * Apply a quick date-range preset to the Obligations modal filters
                 */
                function applyObligationsDatePreset(preset, source) {
                    if (!preset) return;

                    const fromId = source === 'dashboard' ? 'obligationsDateFrom' : 'accountObligationsDateFrom';
                    const toId = source === 'dashboard' ? 'obligationsDateTo' : 'accountObligationsDateTo';
                    const fromInput = document.getElementById(fromId);
                    const toInput = document.getElementById(toId);
                    if (!fromInput || !toInput) return;

                    const fmt = (d) => d.toISOString().split('T')[0];
                    const now = new Date();
                    let from, to;

                    if (preset === 'this_month') {
                        from = new Date(now.getFullYear(), now.getMonth(), 1);
                        to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    } else if (preset === 'last_month') {
                        from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                        to = new Date(now.getFullYear(), now.getMonth(), 0);
                    } else if (preset === 'this_quarter') {
                        const q = Math.floor(now.getMonth() / 3);
                        from = new Date(now.getFullYear(), q * 3, 1);
                        to = new Date(now.getFullYear(), q * 3 + 3, 0);
                    } else if (preset === 'ytd') {
                        from = new Date(now.getFullYear(), 0, 1);
                        to = now;
                    } else {
                        return;
                    }

                    fromInput.value = fmt(from);
                    toInput.value = fmt(to);

                    if (source === 'dashboard') {
                        refreshObligationsModal();
                    } else {
                        refreshAccountObligationsModal();
                    }
                    updateObligationsFilterIndicator(source);
                }

                /**
                 * Toggle the visibility of the search input's clear (x) button
                 */
                function toggleObligationsSearchClear(value, source) {
                    const btnId = source === 'dashboard' ? 'obligationsSearchClearBtn' : 'accountObligationsSearchClearBtn';
                    const btn = document.getElementById(btnId);
                    if (btn) btn.classList.toggle('hidden', !value);
                }

                /**
                 * Clear only the search term in the Obligations modal
                 */
                function clearObligationsSearch(source) {
                    const inputId = source === 'dashboard' ? 'obligationsSearchInput' : 'accountObligationsSearchInput';
                    const input = document.getElementById(inputId);
                    if (input) {
                        input.value = '';
                        filterObligationsTable('', source);
                    }
                    toggleObligationsSearchClear('', source);
                    updateObligationsFilterIndicator(source);
                }

                /**
                 * Clear all active filters (search + date range) in the Obligations modal
                 */
                function clearObligationsFilters(source) {
                    const searchId = source === 'dashboard' ? 'obligationsSearchInput' : 'accountObligationsSearchInput';
                    const fromId = source === 'dashboard' ? 'obligationsDateFrom' : 'accountObligationsDateFrom';
                    const toId = source === 'dashboard' ? 'obligationsDateTo' : 'accountObligationsDateTo';
                    const presetId = source === 'dashboard' ? 'obligationsDatePreset' : 'accountObligationsDatePreset';

                    const searchInput = document.getElementById(searchId);
                    const fromInput = document.getElementById(fromId);
                    const toInput = document.getElementById(toId);
                    const presetSelect = document.getElementById(presetId);

                    if (searchInput) searchInput.value = '';
                    if (fromInput) fromInput.value = '';
                    if (toInput) toInput.value = '';
                    if (presetSelect) presetSelect.value = '';

                    toggleObligationsSearchClear('', source);
                    updateObligationsFilterIndicator(source);

                    if (source === 'dashboard') {
                        refreshObligationsModal();
                    } else {
                        refreshAccountObligationsModal();
                    }
                }

                /**
                 * Show/hide the "Clear Filters" button and update its active-filter count badge
                 */
                function updateObligationsFilterIndicator(source) {
                    const searchId = source === 'dashboard' ? 'obligationsSearchInput' : 'accountObligationsSearchInput';
                    const fromId = source === 'dashboard' ? 'obligationsDateFrom' : 'accountObligationsDateFrom';
                    const toId = source === 'dashboard' ? 'obligationsDateTo' : 'accountObligationsDateTo';
                    const btnId = source === 'dashboard' ? 'obligationsClearFiltersBtn' : 'accountObligationsClearFiltersBtn';
                    const countId = source === 'dashboard' ? 'obligationsActiveFilterCount' : 'accountObligationsActiveFilterCount';

                    const search = document.getElementById(searchId)?.value || '';
                    const from = document.getElementById(fromId)?.value || '';
                    const to = document.getElementById(toId)?.value || '';

                    let count = 0;
                    if (search) count++;
                    if (from || to) count++;

                    const countEl = document.getElementById(countId);
                    if (countEl) countEl.textContent = count;

                    const btn = document.getElementById(btnId);
                    if (btn) {
                        btn.classList.toggle('hidden', count === 0);
                        btn.classList.toggle('inline-flex', count > 0);
                    }
                }

                // Page-level date filter values, used to seed the Obligations modal on first open
                const pageDateFrom = '{{ request('from_date') }}';
                const pageDateTo = '{{ request('to_date') }}';
                let obligationsModalDateInitialized = false;

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

                    // Carry over the page-level date filter into the modal, but only the first
                    // time the modal is opened — after that, respect whatever the user set/cleared inside the modal
                    if (!obligationsModalDateInitialized) {
                        const dateFromInput = document.getElementById('obligationsDateFrom');
                        const dateToInput = document.getElementById('obligationsDateTo');
                        if (dateFromInput && pageDateFrom) {
                            dateFromInput.value = pageDateFrom;
                        }
                        if (dateToInput && pageDateTo) {
                            dateToInput.value = pageDateTo;
                        }
                        obligationsModalDateInitialized = true;
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
                    const searchSpinner = document.getElementById('obligationsSearchSpinner');
                    if (searchSpinner) searchSpinner.classList.remove('hidden');

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
                            const searchSpinnerDone = document.getElementById('obligationsSearchSpinner');
                            if (searchSpinnerDone) searchSpinnerDone.classList.add('hidden');

                            // Update header with office and allotment class info and year
                            if (data.success && headerInfo) {
                                const year = data.cy_year || window.currentYear || '';
                                headerInfo.innerHTML = ` | <span class="font-semibold">${data.office} - ${data.allotmentClass}</span> (CY ${year})`;
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
                                    <div class="overflow-x-auto flex flex-col obligations-inner-scroll">
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
                                            <thead class="bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 border-t-2 border-b-2 border-blue-700 dark:border-blue-800 text-center">
                                                <tr>
                                                    <th class="px-3 py-2 border-l-4 border-l-blue-500"></th>
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
                                
                                // Color-codes the OBR Type badge by obligation kind, same as the Obligations page.
                                const obrTypeColors = {
                                    'Regular': 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-400',
                                    'Purchase Request': 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-400',
                                    'Project/Contract': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-400',
                                };
                                const obrTypeBadge = (type) => `<span class="px-2 py-1 rounded font-semibold ${obrTypeColors[type] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'}">${type}</span>`;
                                // Strips commas and handles '-' placeholders so sub-table balances can be computed.
                                const parseAmt = (v) => (!v || v === '-') ? 0 : parseFloat(String(v).replace(/,/g, '')) || 0;

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
                                        '<span class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 px-2 py-1 rounded font-semibold">Cancelled</span>' :
                                        (hasAdjustments ?
                                            `<span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold">${obligation.amount}</span>` :
                                            obligation.amount);

                                    const rowBandClass = index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900/40';

                                    tableHTML += `
                                        <tr class="${rowBandClass} hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer obligation-row"
                                            data-obligation-index="${index}"
                                            data-obligation-id="${obligation.id}"
                                            data-obligation='${JSON.stringify(obligation)}'
                                            oncontextmenu="showDashboardObligationContextMenu(event, this)">
                                            <td class="px-3 py-2 text-center border-l-4 border-l-blue-500"><i class="fas fa-chevron-right text-gray-500 transition-transform duration-200 expand-icon"></i></td>
                                            <td class="px-3 py-2 font-bold text-blue-700 dark:text-blue-300"><i class="fas fa-hashtag mr-1 text-blue-500 text-[10px]"></i>${obligation.obr_no}</td>
                                            <td class="px-3 py-2">${obligation.obr_date}</td>
                                            <td class="px-3 py-2">${obrTypeBadge(obligation.obr_type)}</td>
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
                                                            <col style="width: 100px;">
                                                        </colgroup>
                                                        <thead class="bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 border border-blue-700 dark:border-blue-800 text-center">
                                                            <tr>
                                                                <th class="px-3 py-2 border-l-4 border-l-blue-500">Programs</th>
                                                                <th class="px-3 py-2">Account Code</th>
                                                                <th class="px-3 py-2">Description</th>
                                                                <th class="px-3 py-2">Amount</th>
                                                                <th class="px-3 py-2">Adjustment Amount</th>
                                                                <th class="px-3 py-2">Adjusted Amount</th>
                                                                <th class="px-3 py-2">Purchase Order</th>
                                                                <th class="px-3 py-2">Disbursement</th>
                                                                <th class="px-3 py-2">Balance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-400 dark:border-gray-600">
                    `;

                                    obligation.appropriations.forEach((app, appIndex) => {
                                        const balance = parseAmt(app.adjusted_amount) - parseAmt(app.purchase_order_amount) - parseAmt(app.disbursement_amount);
                                        const balanceColor = balance < 0 ? 'text-red-700 dark:text-red-400 font-bold' : (balance === 0 ? 'text-green-700 dark:text-green-400 font-bold' : 'text-orange-700 dark:text-orange-400 font-bold');
                                        const appRowBand = appIndex % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900/40';
                                        tableHTML += `
                                                            <tr class="${appRowBand}">
                                                                <td class="px-3 py-2">${app.programs}</td>
                                                                <td class="px-3 py-2">${app.code}</td>
                                                                <td class="px-3 py-2">${app.description}</td>
                                                                <td class="px-3 py-2 text-right font-semibold">${app.amount}</td>
                                                                <td class="px-3 py-2 text-right">${app.adjustment_amount}</td>
                                                                <td class="px-3 py-2 text-right font-semibold text-blue-700 dark:text-blue-400">${app.adjusted_amount}</td>
                                                                <td class="px-3 py-2 text-right text-sky-700 dark:text-sky-400">${app.purchase_order_amount}</td>
                                                                <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-400">${app.disbursement_amount}</td>
                                                                <td class="px-3 py-2 text-right ${balanceColor}">${balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
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
                                            <tfoot class="bg-blue-100 dark:bg-blue-950 font-semibold border-t-2 border-blue-700 dark:border-blue-800">
                                                <tr>
                                                    <td colspan="6" class="px-3 py-2 text-right border-l-4 border-l-blue-500">Total:</td>
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
                                    const ofWrap = document.getElementById('obligationsTotalRecordsOfWrap');
                                    if (ofWrap) ofWrap.classList.add('hidden');

                                    // Re-apply any in-progress search now that fresh rows exist
                                    const searchInputEl = document.getElementById('obligationsSearchInput');
                                    if (searchInputEl && searchInputEl.value) {
                                        filterObligationsTable(searchInputEl.value, 'dashboard');
                                    }
                                    if (typeof updateObligationsFilterIndicator === 'function') {
                                        updateObligationsFilterIndicator('dashboard');
                                    }

                                    // Add click event listeners to obligation rows
                                    document.querySelectorAll('.obligation-row').forEach(row => {
                                        row.addEventListener('click', function(e) {
                                            // Don't expand if right-clicking
                                            if (e.button === 2) return;
                                            
                                            const obligationIndex = this.dataset.obligationIndex;
                                            const appRow = document.querySelector(`.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                                            const expandIcon = this.querySelector('.expand-icon');
                                            const wasHidden = appRow ? appRow.classList.contains('hidden') : true;

                                            // Only one appropriations panel may be open at a time.
                                            document.querySelectorAll('.appropriations-row:not(.hidden)').forEach(openRow => {
                                                openRow.classList.add('hidden');
                                                const openRowIcon = document.querySelector(`.obligation-row[data-obligation-index="${openRow.dataset.obligationIndex}"] .expand-icon`);
                                                if (openRowIcon) openRowIcon.style.transform = 'rotate(0deg)';
                                            });

                                            if (appRow && wasHidden) {
                                                appRow.classList.remove('hidden');
                                                expandIcon.style.transform = 'rotate(90deg)';
                                            }
                                        });
                                    });
                                }
                            } else if (data.success && content) {
                                content.innerHTML = '<div class="text-center py-8 text-gray-500 italic dark:text-gray-400">No Obligations found for this Office Allotment Class.</div>';
                            } else if (content) {
                                content.innerHTML = '<div class="text-center py-8 text-red-500">Error: ' + (data.message || 'Unknown error') + '</div>';
                            }
                            updateObligationsExportState('dashboard');
                        })
                        .catch(error => {
                            if (loading) {
                                loading.style.display = 'none';
                            }
                            const searchSpinnerErr = document.getElementById('obligationsSearchSpinner');
                            if (searchSpinnerErr) searchSpinnerErr.classList.add('hidden');
                            if (content) {
                                content.innerHTML = '<div class="text-center py-8 text-red-500">Error loading obligations: ' + error.message + '</div>';
                            }
                            updateObligationsExportState('dashboard');
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

                // Close the Obligations modal on Escape, matching the "Esc" hint on its Close button
                document.addEventListener('keydown', function(e) {
                    if (e.key !== 'Escape') return;
                    const modal = document.getElementById('obligationsModal');
                    if (modal && modal.style.display !== 'none') {
                        closeObligationsModal();
                    }
                });

                /**
                 * Export the obligations table to Excel, respecting the currently selected date range and search filter
                 */
                async function exportObligationsModal(source = 'dashboard') {
                    const modalId = source === 'dashboard' ? 'obligationsModal' : 'accountObligationsModal';
                    const modal = document.getElementById(modalId);

                    if (!modal || !window.currentObligationsInfo) {
                        alert('No data to export');
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
                    const exportableContent = tableContainer.cloneNode(true);

                    // Parse date range boundaries once
                    const checkFrom = dateFromValue ? new Date(dateFromValue) : null;
                    const checkTo = dateToValue ? new Date(dateToValue) : null;

                    // Walk every obligation row: drop it if it's hidden by search OR falls outside
                    // the selected date range, and recompute totals from only what's kept
                    const allObligationRows = exportableContent.querySelectorAll('tbody tr.obligation-row');
                    const rowsToRemove = [];
                    let exportTotalAmount = 0;
                    let exportTotalPurchaseOrder = 0;
                    let exportTotalDisbursement = 0;
                    let exportedCount = 0;

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
                            const appRow = exportableContent.querySelector(`tr.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                            if (appRow) rowsToRemove.push(appRow);
                        } else {
                            exportedCount++;

                            const amountText = (cells[6]?.textContent || '0').replace(/,/g, '').replace(/Cancelled/g, '0').trim();
                            const amountValue = parseFloat(amountText);
                            if (!isNaN(amountValue)) exportTotalAmount += amountValue;

                            const poText = (cells[7]?.textContent || '0').replace(/,/g, '').trim();
                            const poValue = parseFloat(poText);
                            if (!isNaN(poValue)) exportTotalPurchaseOrder += poValue;

                            const disbText = (cells[8]?.textContent || '0').replace(/,/g, '').trim();
                            const disbValue = parseFloat(disbText);
                            if (!isNaN(disbValue)) exportTotalDisbursement += disbValue;

                            // Make visible appropriations rows display properly in the export
                            const appRow = exportableContent.querySelector(`tr.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                            if (appRow) {
                                appRow.classList.remove('hidden');
                                appRow.style.display = '';
                            }
                        }
                    });

                    // Remove rows that don't belong in the exported set
                    rowsToRemove.forEach(row => row.remove());

                    if (exportedCount === 0) {
                        alert('No obligations found for the selected filters.');
                        return;
                    }

                    if (typeof ExcelJS === 'undefined') {
                        alert('Export library failed to load. Please check your connection and try again.');
                        return;
                    }

                    // Show a loading state on the Export button while the workbook is built —
                    // this can take a moment for larger datasets, so give visible feedback
                    // instead of leaving the button clickable with no indication it's working.
                    const exportBtnId = source === 'dashboard' ? 'obligationsExportBtn' : 'accountObligationsExportBtn';
                    const exportIconId = source === 'dashboard' ? 'obligationsExportIcon' : 'accountObligationsExportIcon';
                    const exportLabelId = source === 'dashboard' ? 'obligationsExportLabel' : 'accountObligationsExportLabel';
                    const exportBtn = document.getElementById(exportBtnId);
                    const exportIcon = document.getElementById(exportIconId);
                    const exportLabel = document.getElementById(exportLabelId);
                    if (exportBtn) exportBtn.disabled = true;
                    if (exportIcon) exportIcon.className = 'fas fa-circle-notch fa-spin mr-2';
                    if (exportLabel) exportLabel.textContent = 'Exporting...';

                    try {

                    // Describe the filters that were actually applied to this export, so the
                    // file is self-documenting about what subset of data it contains.
                    const searchInputId = source === 'dashboard' ? 'obligationsSearchInput' : 'accountObligationsSearchInput';
                    const searchValue = document.getElementById(searchInputId)?.value?.trim() || '';
                    const filterParts = [];
                    if (dateFromValue || dateToValue) {
                        const fromDisplay = dateFromValue
                            ? new Date(dateFromValue).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
                            : 'Start';
                        const toDisplay = dateToValue
                            ? new Date(dateToValue).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
                            : 'Present';
                        filterParts.push(`Date Range: ${fromDisplay} - ${toDisplay}`);
                    }
                    if (searchValue) {
                        filterParts.push(`Search: "${searchValue}"`);
                    }
                    const filtersText = filterParts.length ? filterParts.join(' | ') : 'No filters applied';

                    // Turn a formatted display value ("1,500.00", "-", "Cancelled") into a real
                    // number for Excel where possible, so totals/sorting work natively.
                    const toCell = (text) => {
                        const cleaned = (text || '').replace(/,/g, '').trim();
                        if (cleaned === '' || cleaned === '-') return '';
                        const n = parseFloat(cleaned);
                        return isNaN(n) ? text.trim() : n;
                    };

                    const MAIN_COLS = 8;
                    const rows = [];
                    const headerRowIndexes = [];
                    const groupRowIndexes = [];
                    const subHeaderRowIndexes = [];

                    rows.push([`Office: ${headerInfo.office} | Allotment Class: ${headerInfo.allotmentClass} | CY ${headerInfo.cyYear}`]);
                    rows.push([`Filters Applied: ${filtersText}`]);
                    rows.push([]);

                    const headerRowIndex = rows.length;
                    rows.push(['OBR No.', 'Date', 'OBR Type', 'Particulars', 'Remarks', 'Obligation', 'Purchase Order', 'Disbursement']);
                    headerRowIndexes.push(headerRowIndex);

                    exportableContent.querySelectorAll('tbody > tr.obligation-row').forEach(row => {
                        const cells = Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim());
                        groupRowIndexes.push(rows.length);
                        rows.push([
                            cells[1], cells[2], cells[3], cells[4], cells[5],
                            toCell(cells[6]), toCell(cells[7]), toCell(cells[8]),
                        ]);

                        const obligationIndex = row.dataset.obligationIndex;
                        const appRow = exportableContent.querySelector(`tr.appropriations-row[data-obligation-index="${obligationIndex}"]`);
                        const subTable = appRow ? appRow.querySelector('table') : null;
                        if (subTable) {
                            const subHeaderCells = Array.from(subTable.querySelectorAll('thead th')).map(th => th.textContent.trim());
                            subHeaderRowIndexes.push(rows.length);
                            rows.push(['', ...subHeaderCells]);

                            subTable.querySelectorAll('tbody tr').forEach(subRow => {
                                const subCells = Array.from(subRow.querySelectorAll('td')).map((td, i) => i === 0 ? td.textContent.trim() : toCell(td.textContent));
                                rows.push(['', ...subCells]);
                            });
                        }

                        rows.push([]);
                    });

                    const totalRowIndex = rows.length;
                    rows.push(['Total:', '', '', '', '', exportTotalAmount, exportTotalPurchaseOrder || '', exportTotalDisbursement || '']);
                    rows.push([]);
                    rows.push([`Exported on: ${new Date().toLocaleString()}`]);

                    const maxCols = Math.max(MAIN_COLS, ...rows.map(r => r.length), 10);

                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet('Obligations');
                    worksheet.columns = [
                        { width: 22 }, { width: 26 }, { width: 16 }, { width: 14 },
                        { width: 14 }, { width: 14 }, { width: 14 }, { width: 14 },
                        { width: 14 }, { width: 14 },
                    ].slice(0, maxCols);

                    rows.forEach(r => worksheet.addRow(r));

                    // Shade a row across every used column — light tints only, no solid/dark fills.
                    const shadeRow = (rowIndex, fillArgb, fontArgb, bold = true) => {
                        const row = worksheet.getRow(rowIndex + 1);
                        for (let c = 1; c <= maxCols; c++) {
                            const cell = row.getCell(c);
                            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: fillArgb } };
                            cell.font = { bold, color: { argb: fontArgb } };
                        }
                    };

                    // Office line / applied-filters line — merged banners, styled before merging
                    // so the tint fills the whole row (Excel only keeps the master cell's style otherwise).
                    [0, 1].forEach(r => shadeRow(r, 'FFF8FAFC', 'FF334155', r === 0));
                    worksheet.mergeCells(1, 1, 1, maxCols);
                    worksheet.getCell(1, 1).font = { bold: true, color: { argb: 'FF1E293B' } };
                    worksheet.getCell(1, 1).alignment = { horizontal: 'center' };
                    worksheet.mergeCells(2, 1, 2, maxCols);
                    worksheet.getCell(2, 1).font = { italic: true, color: { argb: 'FF64748B' } };
                    worksheet.getCell(2, 1).alignment = { horizontal: 'center' };

                    // Column header row — medium-light gray tint
                    headerRowIndexes.forEach(r => shadeRow(r, 'FFE2E8F0', 'FF334155'));
                    // Obligation group row — slightly deeper gray tint, reads as the parent of its
                    // appropriations breakdown beneath it
                    groupRowIndexes.forEach(r => shadeRow(r, 'FFCBD5E1', 'FF1E293B'));
                    // Appropriations sub-table header — lightest tint, a tier below the group row
                    subHeaderRowIndexes.forEach(r => shadeRow(r, 'FFF1F5F9', 'FF334155'));
                    // Totals row — neutral gray tint with a double rule above it
                    shadeRow(totalRowIndex, 'FFE2E8F0', 'FF1E293B');
                    worksheet.getRow(totalRowIndex + 1).eachCell({ includeEmpty: true }, cell => {
                        cell.border = { top: { style: 'double', color: { argb: 'FF475569' } } };
                    });

                    // Thin borders + number formatting + a uniform Arial 10pt base font on every
                    // populated cell (existing bold/italic/color from the shading above is kept).
                    worksheet.eachRow((row) => {
                        row.eachCell({ includeEmpty: false }, cell => {
                            cell.border = cell.border || {
                                top: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                                left: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                                bottom: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                                right: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                            };
                            if (typeof cell.value === 'number') {
                                cell.numFmt = '#,##0.00';
                                cell.alignment = { horizontal: 'right' };
                            }
                            cell.font = Object.assign({ name: 'Arial', size: 9 }, cell.font);
                        });
                    });

                    const officeSlug = (headerInfo.office || 'Obligations').replace(/[^a-zA-Z0-9]+/g, '_');
                    const buffer = await workbook.xlsx.writeBuffer();
                    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = `Obligations_${officeSlug}_${new Date().toISOString().slice(0, 10)}.xlsx`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(link.href);

                    } finally {
                        if (exportIcon) exportIcon.className = 'fas fa-file-excel mr-2';
                        if (exportLabel) exportLabel.textContent = 'Export';
                        updateObligationsExportState(source);
                    }
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

                    // Show "X of Y" when the filters have narrowed the visible rows
                    const ofWrapId = source === 'dashboard' ? 'obligationsTotalRecordsOfWrap' : 'accountObligationsTotalRecordsOfWrap';
                    const allId = source === 'dashboard' ? 'obligationsTotalRecordsAll' : 'accountObligationsTotalRecordsAll';
                    const ofWrap = document.getElementById(ofWrapId);
                    const allSpan = document.getElementById(allId);
                    if (ofWrap && allSpan) {
                        if (visibleCount !== rows.length) {
                            allSpan.textContent = rows.length;
                            ofWrap.classList.remove('hidden');
                        } else {
                            ofWrap.classList.add('hidden');
                        }
                    }

                    // Show "no results" message if nothing found
                    let noResultsDiv = content.querySelector('.no-search-results');
                    const tableContainer = content.querySelector('div.overflow-x-auto');

                    if (visibleCount === 0 && searchValue && tableContainer) {
                        if (!noResultsDiv) {
                            noResultsDiv = document.createElement('div');
                            noResultsDiv.className = 'no-search-results text-center py-8 text-gray-500 dark:text-gray-400';
                            noResultsDiv.innerHTML = '<i class="fas fa-magnifying-glass block text-2xl mb-2 opacity-50"></i><p class="italic">No obligations found matching your search.</p>';
                            tableContainer.parentElement.insertBefore(noResultsDiv, tableContainer.nextSibling);
                        }
                        noResultsDiv.style.display = 'block';
                        if (tableContainer) tableContainer.style.display = 'none';
                    } else {
                        if (noResultsDiv) noResultsDiv.style.display = 'none';
                        if (tableContainer) tableContainer.style.display = 'block';
                    }

                    updateObligationsExportState(source);
                }

                /**
                 * Reflect how many obligation rows are currently visible (after search/date
                 * filtering) on the Export button — updates the hint text and disables the
                 * button entirely when there's nothing to export.
                 */
                function updateObligationsExportState(source = 'dashboard') {
                    const contentId = source === 'dashboard' ? 'obligationsContent' : 'accountObligationsContent';
                    const hintId = source === 'dashboard' ? 'obligationsExportHint' : 'accountObligationsExportHint';
                    const btnId = source === 'dashboard' ? 'obligationsExportBtn' : 'accountObligationsExportBtn';

                    const content = document.getElementById(contentId);
                    const hint = document.getElementById(hintId);
                    const btn = document.getElementById(btnId);
                    if (!hint || !btn) return;

                    const visibleRows = content
                        ? Array.from(content.querySelectorAll('tbody > tr.obligation-row')).filter(r => r.style.display !== 'none')
                        : [];
                    const count = visibleRows.length;

                    hint.textContent = count > 0 ? `Exports ${count} record${count === 1 ? '' : 's'}` : 'No records to export';
                    btn.disabled = count === 0;
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
    </script>
