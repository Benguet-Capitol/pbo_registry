<x-app-layout>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

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

    /* Mobile: smaller base font for the dense accounts table */
    @media (max-width: 640px) {
        #accountsTable { font-size: 10px; }
    }

    /* JS-built obligations table inside the Obligations Modal: shrink on small screens */
    .obligations-inner-scroll { max-height: calc(90vh - 340px); }
    @media (max-width: 640px) {
        .obligations-inner-scroll { max-height: calc(95vh - 320px); }
    }

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

    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            @php
            $filters = [];
            if (request('from_date') || request('to_date')) {
                $fromDate = request('from_date') ? date('M d, Y', strtotime(request('from_date'))) : 'Start';
                $toDate = request('to_date') ? date('M d, Y', strtotime(request('to_date'))) : 'End';
                $filters[] = "$fromDate - $toDate";
            }
            @endphp
            <h2 class="min-w-0 text-lg sm:text-xl font-semibold leading-snug sm:leading-tight break-words">
                {{ __('Current Balances > Accounts') }} |
                <span class="text-blue-800 dark:text-blue-400">
                    {{ $officeAllotmentClasses->offices->office_name ?? 'Office N/A' }} -
                    {{ $officeAllotmentClasses->class ?? 'Class N/A' }}
                    @if(count($filters) > 0)
                    / {{ implode(' / ', $filters) }}
                    @endif
                    (CY {{ $officeAllotmentClasses->year ?? 'Year N/A' }})
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

    @include('dashboard.partials.account-overview')

    @include('dashboard.partials.account-summary-cards')

    <!-- Right-Click Context Menu for Accounts Table -->
    <div id="accountContextMenu" class="hidden fixed text-xs bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg shadow-2xl z-[10000] text-blue-900 dark:text-blue-100 min-w-max max-w-[calc(100vw-1rem)] border-2 border-blue-400 dark:border-blue-600">
        @role('Administrator|Developer|Obligation')
        <a href="#" id="contextObligate" class="flex items-center px-4 py-2 text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-t-lg transition-colors duration-150">
            <i class="fas fa-plus-circle mr-2 text-blue-600 dark:text-blue-400"></i> Obligate
        </a>
        @endrole
        <a href="#" onclick="event.preventDefault(); handleAccountMenuOption('obligations')" class="block px-4 py-2 text-blue-900 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-700 rounded-b-lg transition-colors duration-150 cursor-pointer">
            <i class="fas fa-list mr-2 text-blue-600 dark:text-blue-400"></i>Obligations
        </a>
    </div>
    @include('dashboard.partials.account-obligations-modal')

    @include('dashboard.partials.account-chart-animations')

<!-- Include the Create Obligations Modal -->
@include('obligations.modal.create')
<!-- Include the Edit Obligations Modal -->
@include('obligations.modal.edit')

    <!-- Include Obligation Adjustments Create Modal -->
    @include('obligation_adjustments.modal.create')

    <!-- Include Purchase Order Modal -->
    @include('obligations.modal.purchase_order', ['obligation' => (object)['id' => null]])

    <!-- Obligation Details Modal -->
    @include('obligations.modal.obligation_details')

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

        // Auto-reopen obligation and purchase order modals after creating PO from accounts
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('reopen_obligation_id') && session('reopen_po_modal'))
                const obligationId = {{ session('reopen_obligation_id') }};
                
                // Delay slightly to ensure page is fully loaded
                setTimeout(function() {
                    // Fetch and display success message in accounts context
                    // The obligation should refresh with updated PO data
                    
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

        // Date range validation - ensure To Date >= From Date
        const fromDateInput = document.getElementById('fromDate');
        const toDateInput = document.getElementById('toDate');

        if (fromDateInput) {
            fromDateInput.addEventListener('change', function() {
                if (toDateInput && this.value) {
                    toDateInput.setAttribute('min', this.value);
                    // If toDate is less than fromDate, clear it
                    if (toDateInput.value && toDateInput.value < this.value) {
                        toDateInput.value = '';
                    }
                }
            });
        }

        if (toDateInput) {
            toDateInput.addEventListener('change', function() {
                if (fromDateInput && fromDateInput.value && this.value && this.value < fromDateInput.value) {
                    alert('To Date must be greater than or equal to From Date');
                    this.value = '';
                }
            });
        }
    </script>
</x-app-layout>