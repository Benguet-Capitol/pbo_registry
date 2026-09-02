<x-app-layout>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

    <style>
        [x-cloak] { display: none; }
        .dashboard-content {
            animation: fadeIn 0.6s ease-out;
        }

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

        /* Mobile: fixed-width data tables scroll horizontally within their card */
        @media (max-width: 640px) {
            #dashboardTable { font-size: 10px; }
        }

        /* JS-built obligations table inside the Obligations Modal: shrink on small screens */
        .obligations-inner-scroll { max-height: calc(90vh - 340px); }
        @media (max-width: 640px) {
            .obligations-inner-scroll { max-height: calc(95vh - 320px); }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="min-w-0 text-lg sm:text-xl font-semibold leading-snug sm:leading-tight break-words">
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

                $dateFilterParams = array_filter(request()->only(['from_date', 'to_date']));
                $dateFilterQuery  = http_build_query($dateFilterParams);
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
            <nav class="text-xs text-gray-600 dark:text-gray-300 shrink-0" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex items-center space-x-1 rtl:space-x-reverse flex-wrap">
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
    <div id="successAlert" class="fixed top-4 right-4 left-4 sm:left-auto sm:top-6 sm:right-6 max-w-2xl z-50 animate-slide-in">
        <div class="bg-green-50 border-2 border-green-300 text-green-800 px-4 py-4 sm:px-6 sm:py-5 rounded-xl shadow-2xl dark:bg-green-900 dark:border-green-600 dark:text-green-100 flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400 mt-1 flex-shrink-0 text-2xl"></i>
                <div class="flex-1">
                    <p class="font-semibold text-sm sm:text-base leading-relaxed">{!! $status['message'] ?? $status !!}</p>
                </div>
            </div>
            <button type="button" class="text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 flex-shrink-0" onclick="closeSuccessAlert()">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
    </div>
    @endif

    @include('dashboard.partials.overview')

    @include('dashboard.partials.summary-cards')

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

    @include('dashboard.partials.obligations-modal')
    </div>

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

                // This button's sticky first-column <td> normally sits at z-[1] so every
                // row's sticky cell stacks consistently — but that traps the dropdown menu
                // (which lives inside it) below any LATER row's sticky cell, since z-index
                // only competes within the same DOM order at equal values. Temporarily raise
                // just this cell above the others while its menu is open, mirroring how the
                // right-click context menu (a top-level, non-nested element) is never subject
                // to this at all.
                const stickyCell = button.closest('td.sticky');
                if (stickyCell) {
                    stickyCell.classList.remove('z-[1]');
                    stickyCell.classList.add('z-40');
                }
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

                    // Reset every sticky first-column cell back to its normal stacking
                    // level (see toggleDropdown()) now that no dropdown is open.
                    document.querySelectorAll('#dashboardTable td.sticky.z-40').forEach(cell => {
                        cell.classList.remove('z-40');
                        cell.classList.add('z-[1]');
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
                        
                        // Set the href for context menu items, carrying over the current date filter
                        const accountsParams = new URLSearchParams();
                        if (pageDateFrom) accountsParams.set('from_date', pageDateFrom);
                        if (pageDateTo) accountsParams.set('to_date', pageDateTo);
                        const accountsQuery = accountsParams.toString();
                        document.getElementById('contextAccounts').href = '{{ route("dashboard.accounts", ":id") }}'.replace(':id', classId) + (accountsQuery ? '?' + accountsQuery : '');
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

    @include('dashboard.partials.chart-animations')

    @include('dashboard.partials.saaob-export')

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