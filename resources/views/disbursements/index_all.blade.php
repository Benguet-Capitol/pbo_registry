<x-app-layout>
    <!-- Load SheetJS Library for Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('All Disbursements') }}
                 @php
                $filters = [];

                if (request('office_allotment_class_filter')) {
                    $officeClass = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_filter'));
                    if ($officeClass) {
                        $filters[] = $officeClass->offices->office_abbreviation . ' - ' . $officeClass->allotmentClass->class;
                    }
                }
                @endphp

                @if (count($filters) > 0)
                    <span class="text-lg"> | </span>
                    <span class="text-blue-800 dark:text-blue-400">{{ implode(' / ', $filters) }}</span>
                @endif
                <span class="text-blue-800 dark:text-blue-400">
                    (CY {{ request('year1', date('Y')) }})
                </span>
            </h3>
        </div>
    </x-slot>

    <!-- Page Content Wrapper with Transition -->
    <div class="page-transition">

    <!-- Display Success Message -->
    @if(session('status'))
    @php
    $status = session('status');
    $color = match ($status['type'] ?? 'info') {
    'delete' => 'red',
    'update' => 'blue',
    default => 'green'
    };
    @endphp

    <div class="bg-{{ $color }}-100 border border-{{ $color }}-400 text-{{ $color }}-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{!! $status['message'] ?? $status !!}</span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
            <span class="text-{{ $color }}-700">&times;</span>
        </button>
    </div>
    @endif

    <!-- Display Error Message -->
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{!! session('error') !!}</span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
            <span class="text-red-700">&times;</span>
        </button>
    </div>
    @endif

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
            Filters
        </h4>

        <form id="filterForm" method="GET" action="{{ route('disbursements.all') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                <!-- Year Filter -->
                <div class="flex items-center space-x-2">
                    <label for="year1" class="sr-only">Year</label>
                    <x-form.select name="year1" id="year1" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        @foreach($availableYears as $year1)
                        <option value="{{ $year1 }}" {{ $selectedYear == $year1 ? 'selected' : '' }}>{{ $year1 }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <!-- Office and Allotment Class Filter -->
                <div class="flex items-center space-x-2">
                    <label for="officeAllotmentClass" class="sr-only">Office & Class</label>
                    <x-form.select name="office_allotment_class_filter" id="officeAllotmentClass" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        <option value="">All Allotment Classes per Office</option>
                        @foreach($officeAllotmentClasses as $officeAllotmentClass)
                        <option value="{{ $officeAllotmentClass->id }}" {{ request('office_allotment_class_filter') == $officeAllotmentClass->id ? 'selected' : '' }}>
                            {{ $officeAllotmentClass->offices->office_abbreviation }} - {{ $officeAllotmentClass->allotmentClass->class }}
                        </option>
                        @endforeach
                    </x-form.select>
                </div>

                <!-- Per Page Dropdown -->
                <div class="flex items-center space-x-2">
                    <label for="perPage" class="sr-only">Show per page</label>
                    <x-form.select name="per_page" id="perPage" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-white" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page', '10') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', '10') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', '10') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', '10') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page', '10') == 'all' ? 'selected' : '' }}>All</option>
                    </x-form.select>
                </div>
            </div>
        </form>
    </div>

    <!-- Disbursements Table -->
     <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-6 dark:bg-gray-800">
        <div class="p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-end items-center mb-4">
                <!-- @can('create purchase orders')
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Add Purchase Order') }}
                </button>
                @endcan -->
                <!-- Right: Total Records and Search Input -->
                <div class="flex items-center space-x-4">
                    <!-- Export Button -->
                    <button type="button" onclick="exportDisbursementsToExcel()" class="text-green-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900" title="Export filtered data to Excel">
                        <i class="fas fa-download text-lg mr-2 -ml-1 w-4 h-4"></i>
                        Export to Excel
                    </button>
                    <!-- Total Records -->
                    <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $totalRecords }}</span>
                    </div>
                    <!-- Search Form -->
                    <form id="searchForm" method="GET" action="{{ route('disbursements.all') }}" class="flex items-center space-x-2 min-w-96">
                        <!-- Hidden inputs to preserve filters -->
                        <input type="hidden" name="year1" value="{{ $selectedYear }}">
                        <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 'all') }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_order" value="{{ $sortOrder }}">
                        
                        <x-form.select name="search_column" id="searchColumn" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-40 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Columns</option>
                            <option value="dv_no" {{ request('search_column') == 'dv_no' ? 'selected' : '' }}>DV No.</option>
                            <option value="dv_date" {{ request('search_column') == 'dv_date' ? 'selected' : '' }}>DV Date</option>
                            <option value="payee" {{ request('search_column') == 'payee' ? 'selected' : '' }}>Payee</option>
                            <option value="address" {{ request('search_column') == 'address' ? 'selected' : '' }}>Address</option>
                            <option value="dv_remarks" {{ request('search_column') == 'dv_remarks' ? 'selected' : '' }}>Remarks</option>
                        </x-form.select>
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search disbursements" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                        <button type="submit" class="text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
            <div class="max-h-[720px] overflow-y-auto">
            <table id="disbursementsTable" class="text-center w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-center text-xs border-b-2 border-gray-700 text-gray-700 bg-green-100 border-t-2 dark:bg-green-700 dark:text-gray-400 sticky top-0 z-10">
                    <tr>
                        @php
                            $columns = [
                                'office_class' => 'Office & Class',
                                'obr_no' => 'OBR No.',
                                'particulars' => 'Particulars',
                                'dv_no' => 'DV / Check Number',
                                'disbursement_date' => 'DV / Check Date',
                                'program' => 'Program',
                                'account_code' => 'Account Code',
                                'description' => 'Description',
                                'status' => 'Status',
                                'disbursement_amount' => 'DV / Check Amount',
                                'remarks' => 'Remarks',
                            ];
                            $sortable = ['disbursement_date', 'dv_no', 'status', 'disbursement_amount', 'remarks'];
                        @endphp
                        @foreach($columns as $key => $label)
                            <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">
                                @if(in_array($key, $sortable))
                                    @php
                                        $isCurrent = request('sort_by') === $key;
                                        $sortOrder = $isCurrent && request('sort_order', 'desc') === 'asc' ? 'desc' : 'asc';
                                        $icon = $isCurrent ? (request('sort_order', 'desc') === 'asc' ? '▲' : '▼') : '';
                                        $query = array_merge(request()->except(['page', 'sort_by', 'sort_order']), ['sort_by' => $key, 'sort_order' => $sortOrder]);
                                    @endphp
                                    <a href="?{{ http_build_query($query) }}" class="flex items-center justify-center">
                                        {{ $label }}
                                        <span class="ml-1">{!! $icon !!}</span>
                                    </a>
                                @else
                                    {{ $label }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($disbursements as $disbursement)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-600 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                            data-dv-no="{{ $disbursement->dv_no }}"
                            data-obligation-id="{{ $disbursement->obligation_id }}"
                            oncontextmenu="showDisbursementContextMenu(event, this)">
                            <td class="px-2 py-3 font-bold text-left text-gray-700 dark:text-gray-300">
                                {{ optional($disbursement->obligation->officeAllotmentClass->offices)->office_abbreviation ?? '-' }} -
                                {{ optional($disbursement->obligation->officeAllotmentClass->allotmentClass)->class ?? '-' }}
                            </td>
                            <td class="px-2 py-3 font-bold text-left text-gray-700 dark:text-gray-300">{{ $disbursement->obligation->obr_no ?? '-' }}</td>
                            <td class="px-2 py-3 text-gray-600 text-left dark:text-gray-300 max-w-xs">{{ $disbursement->obligation->particulars ?? '-' }}</td>
                            <td class="px-2 py-3 font-bold text-left text-green-700 dark:text-green-300">{{ $disbursement->dv_no }}</td>
                            <td class="px-2 py-3 font-bold text-left text-green-700 dark:text-green-300">{{ $disbursement->disbursement_date ?? '-' }}</td>
                            <td class="px-2 py-3 font-bold text-left text-gray-700 dark:text-gray-300 max-w-xs">
                                @php
                                    $programs = $disbursement->obligationAmount?->appropriation?->programs ?? '-';
                                @endphp
                                {{ $programs }}
                            </td>
                            <td class="px-2 py-3 font-semibold text-left text-gray-700 dark:text-gray-300">
                                @php
                                    $accountCode = $disbursement->obligationAmount?->appropriation?->account_code ?? '-';
                                @endphp
                                {{ $accountCode }}
                            </td>
                            <td class="px-2 py-3 text-left text-gray-600 dark:text-gray-300 max-w-xs">
                                @php
                                    $descriptions = $disbursement->obligationAmount?->appropriation?->description ?? '-';
                                @endphp
                                {{ $descriptions }}
                            </td>
                            <td class="px-2 py-3 text-left text-gray-600 dark:text-gray-300">
                            @if($disbursement->status === 'Full Payment')
                                    <span class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold">
                                        {{ ucfirst($disbursement->status) }}
                                    </span>
                                @elseif($disbursement->status === 'Partial Payment')
                                    <span class="px-2 py-1 rounded text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300 font-semibold">
                                        {{ ucfirst($disbursement->status) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-300">{{ ucfirst($disbursement->status) }}</span>
                                @endif
                            </td>
                            <td class="px-2 py-3 font-bold text-right text-green-600 dark:text-green-300">{{ number_format($disbursement->disbursement_amount, 2) }}</td>
                            <td class="px-2 py-3 text-left max-w-xs text-gray-600 dark:text-gray-300">{{ $disbursement->remarks ?? '-' }}</td>
                            <!-- <td class="px-2 py-3 text-gray-600 dark:text-gray-300">
                            </td> -->
                        </tr>
                    @empty
                        <tr>1
                            <td colspan="10" class="py-4 text-center text-gray-500">No disbursements found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr id="disbursementsFooter" class="bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-b-2 border-gray-700 dark:border-gray-600">
                        <td colspan="7" class="text-right text-xs font-bold px-1 py-3 text-gray-700 dark:text-gray-300">

                        </td>
                        <td colspan="3" class="text-right text-xs font-bold px-1 py-3 text-gray-700 dark:text-gray-300">
                            Total DV / Check Amount:
                            <span id="totalDVAmountFooter" class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold ml-2">
                                0.00
                            </span>
                        </td>
                        <td colspan="1" class="text-right text-xs font-bold px-1 py-3 text-gray-700 dark:text-gray-300">

                        </td>
                    </tr>
                </tfoot>
            </table>
            </div>
            </div>
        </div>
    </div>

    <!-- Include Modal Files -->
    @include('obligations.modal.obligation_details')
    <div id="disbursementContextMenu" 
        class="fixed hidden w-48 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-400 rounded-lg shadow-2xl z-50 dark:from-blue-900 dark:to-blue-800 dark:border-blue-600"
        style="display: none;">
        @role('Developer|Administrator|Obligation|Disbursement')
        <button id="contextViewObligation"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-eye mr-2 text-blue-600"></i>View Obligation Details
        </button>
        @endrole
    </div>

<script>
    function updateDisbursementFooterTotal() {
        let table = document.getElementById("disbursementsTable");
        let tr = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
        let totalDV = 0;
        for (let i = 0; i < tr.length; i++) {
            if (tr[i].style.display === "none") continue;
            let amountCell = tr[i].getElementsByTagName("td")[9]; // 10th column is DV / Check Amount
            if (!amountCell) continue;
            let amountText = amountCell.textContent.replace(/[^\d.-]/g, '');
            let amount = parseFloat(amountText);
            if (isNaN(amount)) amount = 0;
            totalDV += amount;
        }
        document.getElementById('totalDVAmountFooter').textContent = totalDV.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        // Always show the footer
        document.getElementById('disbursementsFooter').style.display = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateDisbursementFooterTotal();
        updateTotalRecordsCount();
    });
    function filterTable() {
            // Declare variables
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toLowerCase();
            table = document.getElementById("disbursementsTable");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
            updateTotalRecordsCount();
        }

        /**
         * Update total records count based on visible rows (counted per dv_no)
         */
        function updateTotalRecordsCount() {
            const rows = document.querySelectorAll('#disbursementsTable tbody tr');
            let dvNumbers = new Set();

            rows.forEach(row => {
                // Check if row is visible (display is not 'none')
                if (row.style.display !== 'none' && row.dataset.dvNo) {
                    dvNumbers.add(row.dataset.dvNo);
                }
            });

            const totalRecordsElement = document.getElementById('totalRecordsCount');
            if (totalRecordsElement) {
                totalRecordsElement.textContent = dvNumbers.size;
            }
        }

        // Add event listener for input event to filter table as you type
        document.getElementById('searchInput').addEventListener('input', function() {
            filterTable();
            updateDisbursementFooterTotal();
        });

        /**
         * Show toast notification
         */
        function showToast(message, type = 'success') {
            const toastId = 'toast_' + Date.now();
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-slideInRight`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                const element = document.getElementById(toastId);
                if (element) {
                    element.classList.add('animate-slideOutRight');
                    setTimeout(() => element.remove(), 300);
                }
            }, 4000);
        }

        /**
         * Create toast container if it doesn't exist
         */
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'fixed top-4 right-4 z-50 space-y-3';
            document.body.appendChild(container);
            return container;
        }

        /**
         * Export filtered disbursements data to Excel XLSX
         */
        // Context Menu Handler for Disbursements
        const dvMenu = document.getElementById('disbursementContextMenu');

        window.showDisbursementContextMenu = function(event, row) {
            event.preventDefault();
            event.stopPropagation();
            
            // Remove highlight from previously selected row
            document.querySelectorAll('table tbody tr.context-menu-active').forEach(r => {
                r.classList.remove('context-menu-active');
            });
            
            // Highlight the current row
            row.classList.add('context-menu-active');
            window.currentContextMenuRow = row;

            if (!dvMenu) return;

            // Get element positions
            const menuHeight = 60; // Approximate menu height
            const viewportHeight = window.innerHeight;
            const mouseY = event.clientY;
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            // Determine if menu should appear above or below the cursor
            let top, verticalAlignment;
            const spaceBelow = viewportHeight - mouseY;
            const spaceAbove = mouseY;

            if (spaceBelow > menuHeight + 20) {
                // Show below cursor, tight to cursor position
                top = mouseY + scrollTop;
                verticalAlignment = 'below';
            } else if (spaceAbove > menuHeight + 20) {
                // Show above cursor, positioned lower so it's beside cursor
                top = mouseY + scrollTop - menuHeight + 40;
                verticalAlignment = 'above';
            } else {
                // Default to below
                top = mouseY + scrollTop;
                verticalAlignment = 'below';
            }

            // Calculate left position (tight to cursor, with right edge collision detection)
            let left = event.clientX + scrollLeft + 2;
            const menuWidth = 192; // w-48 = 12rem = 192px
            const viewportWidth = window.innerWidth;
            
            // Check if menu goes off screen to the right
            if (left + menuWidth > viewportWidth + scrollLeft) {
                left = event.clientX + scrollLeft - menuWidth - 2;
            }
            
            // Ensure menu doesn't go off screen to the left
            if (left < scrollLeft) {
                left = scrollLeft + 2;
            }

            // Position menu
            dvMenu.style.position = 'fixed';
            dvMenu.style.top = `${top}px`;
            dvMenu.style.left = `${left}px`;
            dvMenu.style.display = 'block';
            dvMenu.classList.remove('hidden');

            // Get obligation data and set up menu items
            const obligationId = row.dataset.obligationId;
            if (obligationId) {
                // View Obligation Details button
                const viewObligationBtn = dvMenu.querySelector('#contextViewObligation');
                if (viewObligationBtn) {
                    viewObligationBtn.onclick = () => {
                        hideDisbursementContextMenu();
                        displayObligationDetailsModal(obligationId);
                    };
                }
            }

            // Add event listeners with delay
            setTimeout(() => {
                document.addEventListener('click', hideDisbursementContextMenu);
                window.addEventListener('resize', hideDisbursementContextMenu);
                window.addEventListener('scroll', hideDisbursementContextMenu, { passive: true });
                
                // Add scroll listener to container
                const container = document.querySelector('.overflow-x-auto');
                if (container) {
                    container.addEventListener('scroll', hideDisbursementContextMenu, { passive: true });
                }
            }, 30);
        };

        function hideDisbursementContextMenu() {
            if (!dvMenu) return;
            dvMenu.classList.add('hidden');
            dvMenu.style.display = 'none';
            
            // Remove highlight when menu is closed
            if (window.currentContextMenuRow) {
                window.currentContextMenuRow.classList.remove('context-menu-active');
                window.currentContextMenuRow = null;
            }
            
            // Clean up event listeners
            document.removeEventListener('click', hideDisbursementContextMenu);
            window.removeEventListener('resize', hideDisbursementContextMenu);
            window.removeEventListener('scroll', hideDisbursementContextMenu);
            
            // Clean up container listeners
            const container = document.querySelector('.overflow-x-auto');
            if (container) {
                container.removeEventListener('scroll', hideDisbursementContextMenu);
            }
        }

        function exportDisbursementsToExcel() {
            const table = document.getElementById('disbursementsTable');
            const rows = table.querySelectorAll('tbody tr');
            
            // Collect visible rows data
            const data = [];
            const headers = [];
            
            // Get headers from table
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.textContent.trim());
            });
            data.push(headers);
            
            // Get visible rows
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const rowData = [];
                    row.querySelectorAll('td').forEach(td => {
                        rowData.push(td.textContent.trim());
                    });
                    data.push(rowData);
                }
            });
            
            // Create workbook and worksheet
            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Disbursements');
            
            // Set column widths
            const colWidths = headers.map(() => 15);
            ws['!cols'] = colWidths.map(w => ({ wch: w }));
            
            // Generate Excel file with current date
            const today = new Date().toISOString().split('T')[0];
            XLSX.writeFile(wb, `disbursements_${today}.xlsx`);
            
            // Show success toast
            showToast('Disbursements data exported successfully!', 'success');
        }

        /**
         * Display Obligation Details Modal
         */
        function displayObligationDetailsModal(obligationId) {
            // Fetch obligation details
            fetch(`/obligations/${obligationId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch obligation details');
                    }
                    return response.json();
                })
                .then(data => {
                    const modalBody = document.getElementById('modalContent');
                    if (!modalBody) return;
                    
                    const {
                        obligation,
                        obligation_amounts,
                        obligation_adjustments,
                        total_po_amount,
                        purchase_orders,
                        disbursements = [],
                        total_disbursement_amount = 0
                    } = data;

                    const buildCurrencyDisplay = (val) => {
                        if (!val || val == 0) return '-';
                        const numVal = parseFloat(val);
                        const formatted = Math.abs(numVal).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        return numVal < 0 ? `(${formatted})` : formatted;
                    };

                    const showPO = obligation.obr_type === 'Purchase Request';
                    
                    // Build the details HTML
                    let html = `
                        <div class="space-y-4">
                            <!-- Obligation Summary Info -->
                            <table class="w-full text-xs text-left text-gray-500 dark:text-gray-300">
                                <tbody>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Office:</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${obligation.office || 'N/A'}</td>
                                    </tr>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Allotment Class:</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${obligation.allotment_class || 'N/A'}</td>
                                    </tr>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR No:</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${obligation.obr_no || 'N/A'}</td>
                                    </tr>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR Type:</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${obligation.obr_type || 'N/A'}</td>
                                    </tr>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Particulars:</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${obligation.particulars || 'N/A'}</td>
                                    </tr>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Remarks:</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${obligation.remarks || '-'}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Programs Table -->
                            <div class="mt-2">
                                <table class="w-full text-xs text-center border-t mt-3 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 bg-gray-100 dark:bg-gray-900 border-b dark:text-gray-300">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-center">Programs</th>
                                            <th scope="col" class="px-4 py-2 text-center">Account Code</th>
                                            <th scope="col" class="px-4 py-2 text-center">Description</th>
                                            <th scope="col" class="px-4 py-2 text-center">Original Obligation</th>
                                            <th scope="col" class="px-4 py-2 text-center">Adjustment</th>
                                            <th scope="col" class="px-4 py-2 text-center">Adjusted Obligation</th>
                                            ${showPO ? '<th scope="col" class="px-4 py-2 text-center">Purchase Order</th>' : ''}
                                            <th scope="col" class="px-4 py-2 text-center">Disbursement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    
                    if (obligation_amounts && obligation_amounts.length > 0) {
                        obligation_amounts.forEach(amount => {
                            const originalObligation = parseFloat(amount.obr_amount || 0);
                            const adjustment = parseFloat(amount.adjustments || 0);
                            const adjustedObligation = originalObligation + adjustment;
                            const poAmount = parseFloat(amount.po_total || 0);
                            const disbursementAmount = parseFloat(amount.disbursement_total || 0);
                            
                            html += `
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-3 py-2 text-center">${amount.programs || '-'}</td>
                                    <td class="px-3 py-2 text-center">${amount.account_code || '-'}</td>
                                    <td class="px-3 py-2 text-center">${amount.description || '-'}</td>
                                    <td class="px-3 py-2 text-center">${buildCurrencyDisplay(originalObligation)}</td>
                                    <td class="px-3 py-2 text-center">${buildCurrencyDisplay(adjustment)}</td>
                                    <td class="px-3 py-2 text-center">${buildCurrencyDisplay(adjustedObligation)}</td>
                                    ${showPO ? `<td class="px-3 py-2 text-center">${buildCurrencyDisplay(poAmount)}</td>` : ''}
                                    <td class="px-3 py-2 text-center">${buildCurrencyDisplay(disbursementAmount)}</td>
                                </tr>
                            `;
                        });
                    }
                    
                    // Calculate totals for summary row
                    const totalObr = obligation_amounts.reduce((sum, r) => sum + parseFloat(r.obr_amount || 0), 0);
                    const totalAdj = obligation_amounts.reduce((sum, r) => sum + parseFloat(r.adjustments || 0), 0);
                    const totalAdjusted = totalObr + totalAdj;

                    html += `
                            <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                                <td colspan="3" class="text-right px-3 py-2">Total:</td>
                                <td class="text-right px-3 py-2">${buildCurrencyDisplay(totalObr)}</td>
                                <td class="text-right px-3 py-2">${buildCurrencyDisplay(totalAdj)}</td>
                                <td class="text-right px-3 py-2">${buildCurrencyDisplay(totalAdjusted)}</td>
                                ${showPO ? `<td class="text-right px-3 py-2">${buildCurrencyDisplay(total_po_amount)}</td>` : ''}
                                <td class="text-right px-3 py-2">${buildCurrencyDisplay(total_disbursement_amount)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                            <!-- Adjustments Table -->
                            <div class="mt-4">
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Adjustments:</h3>
                                <table class="w-full text-xs text-center border-t mt-3 text-gray-600 dark:text-gray-400">
                                    <thead class="text-xs text-gray-600 bg-gray-100 dark:bg-gray-900 border-b dark:text-gray-300">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-center">Date</th>
                                            <th scope="col" class="px-4 py-2 text-center">Programs</th>
                                            <th scope="col" class="px-4 py-2 text-center">Account Code</th>
                                            <th scope="col" class="px-4 py-2 text-center">Description</th>
                                            <th scope="col" class="px-4 py-2 text-center">Remarks</th>
                                            <th scope="col" class="px-4 py-2 text-center">Adjustment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    if (obligation_adjustments && obligation_adjustments.length > 0) {
                        let lastRemarks = null;
                        obligation_adjustments.forEach(row => {
                            const showCells = row.remarks !== lastRemarks;
                            lastRemarks = row.remarks;
                            html += `
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-3 py-2 text-center">${showCells ? (row.adjustment_date || 'N/A') : '-'}</td>
                                    <td class="px-3 py-2 text-center">${row.programs || '-'}</td>
                                    <td class="px-3 py-2 text-center">${row.account_code || 'N/A'}</td>
                                    <td class="px-3 py-2 text-center">${row.description || 'N/A'}</td>
                                    <td class="px-3 py-2 text-center">${showCells ? (row.remarks || 'N/A') : '-'}</td>
                                    <td class="px-3 py-2 text-right">${buildCurrencyDisplay(row.adjustment_amount)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html += `<tr><td colspan="6" class="px-3 py-3 text-center text-gray-500">No adjustments found.</td></tr>`;
                    }

                    const totalAdjAmount = obligation_adjustments ? obligation_adjustments.reduce((sum, r) => sum + parseFloat(r.adjustment_amount || 0), 0) : 0;
                    html += `
                        <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                            <td colspan="5" class="text-right px-3 py-2">Total Adjustment:</td>
                            <td class="text-right px-3 py-2">${buildCurrencyDisplay(totalAdjAmount)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
                    `;

                    // Purchase Orders Table (if applicable)
                    if (showPO) {
                        html += `
                            <div class="mt-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-300">Purchase Orders:</h3>
                                <table class="w-full text-xs text-center border-t mt-3 text-gray-600 dark:text-gray-400">
                                    <thead class="text-xs text-gray-600 bg-gray-100 dark:bg-gray-900 border-b dark:text-gray-300">
                                        <tr>
                                            <th class="px-3 py-2">PO Number</th>
                                            <th class="px-3 py-2">PO Date</th>
                                            <th class="px-3 py-2">PR Number</th>
                                            <th class="px-3 py-2">Supplier</th>
                                            <th class="px-3 py-2">Programs</th>
                                            <th class="px-3 py-2">Account Code</th>
                                            <th class="px-3 py-2">Description</th>
                                            <th class="px-3 py-2">Remarks</th>
                                            <th class="px-3 py-2">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        if (purchase_orders && purchase_orders.length > 0) {
                            purchase_orders.sort((a, b) => a.po_number.localeCompare(b.po_number));
                            let shownPoNumbers = new Set();
                            purchase_orders.forEach(po => {
                                const isFirst = !shownPoNumbers.has(po.po_number);
                                shownPoNumbers.add(po.po_number);
                                html += `
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-2 py-2">${isFirst ? po.po_number : ''}</td>
                                        <td class="px-2 py-2">${isFirst ? po.po_date : ''}</td>
                                        <td class="px-2 py-2">${isFirst ? po.pr_no : ''}</td>
                                        <td class="px-2 py-2">${isFirst ? po.supplier : ''}</td>
                                        <td class="px-2 py-2">${po.programs || '-'}</td>
                                        <td class="px-2 py-2">${po.account_code}</td>
                                        <td class="px-2 py-2">${po.description}</td>
                                        <td class="px-2 py-2">${po.po_remarks || '-'}</td>
                                        <td class="px-2 py-2 text-right">${buildCurrencyDisplay(po.po_amount)}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            html += `<tr><td colspan="9" class="px-3 py-3 text-center text-gray-500">No purchase orders found.</td></tr>`;
                        }

                        html += `
                                        <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                                            <td colspan="8" class="text-right px-3 py-2">Total PO Amount:</td>
                                            <td class="text-right px-3 py-2">${buildCurrencyDisplay(total_po_amount)}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }

                    // Disbursements Table
                    html += `
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-300">Disbursements:</h3>
                            <table class="w-full text-xs text-center border-t mt-3 text-gray-600 dark:text-gray-400">
                                <thead class="text-xs text-gray-600 bg-gray-100 dark:bg-gray-900 border-b dark:text-gray-300">
                                    <tr>
                                        <th class="px-2 py-2 text-center">DV / Check No.</th>
                                        <th class="px-2 py-2 text-center">Date</th>
                                        <th class="px-2 py-2 text-center">Status</th>
                                        <th class="px-2 py-2 text-center">Program</th>
                                        <th class="px-2 py-2 text-center">Account Code</th>
                                        <th class="px-2 py-2 text-center">Description</th>
                                        <th class="px-3 py-2 text-center">Remarks</th>
                                        <th class="px-3 py-2 text-center">DV / Check Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    if (disbursements && disbursements.length > 0) {
                        disbursements.forEach(dv => {
                            html += `
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-2 py-2">${dv.dv_no || '-'}</td>
                                    <td class="px-2 py-2">${dv.disbursement_date || '-'}</td>
                                    <td class="px-2 py-2">${dv.status || '-'}</td>
                                    <td class="px-2 py-2">${dv.programs || '-'}</td>
                                    <td class="px-2 py-2">${dv.account_code || '-'}</td>
                                    <td class="px-2 py-2">${dv.description || '-'}</td>
                                    <td class="px-2 py-2">${dv.remarks || '-'}</td>
                                    <td class="px-2 py-2 text-right">${buildCurrencyDisplay(dv.disbursement_amount)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html += `<tr><td colspan="8" class="px-3 py-3 text-center text-gray-500">No disbursements found.</td></tr>`;
                    }

                    html += `
                        <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                            <td colspan="7" class="text-right px-3 py-2">Total DV / Check Amount:</td>
                            <td class="text-right px-3 py-2">${buildCurrencyDisplay(total_disbursement_amount)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
                    `;
                    
                    modalBody.innerHTML = html;
                    const modal = document.getElementById('obligationModal');
                    if (modal) {
                        modal.style.display = 'flex';
                        modal.setAttribute('aria-hidden', 'false');
                    }
                })
                .catch(error => {
                    console.error('Error fetching obligation details:', error);
                    showToast('Error loading obligation details', 'error');
                });
        }

        /**
         * Close the obligation modal
         */
        function closeModal() {
            const modal = document.getElementById('obligationModal');
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
        }

        /**
         * Print the obligation modal content
         */
        function printModal() {
            const modalContent = document.getElementById('modalContent').innerHTML;

            const printWindow = window.open('', '', 'width=1000,height=800');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Obligation Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px; }
                        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
                        th { background-color:rgb(114, 114, 114); }
                        h3 { margin-top: 20px; margin-bottom: 5px; }
                    </style>
                </head>
                <body onload="window.print(); window.close();">
                    <h2>Obligation Details</h2>
                    ${modalContent}
                </body>
                </html>
            `);
            printWindow.document.close();
        }
</script>

<style>
    @keyframes pageSlideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .page-transition {
        animation: pageSlideUp 0.4s ease-in-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100px);
        }
    }

    .animate-slideInRight {
        animation: slideInRight 0.3s ease-out;
    }

    .animate-slideOutRight {
        animation: slideOutRight 0.3s ease-out;
    }

    .context-menu-active {
        background-color: rgba(59, 130, 246, 0.1) !important;
        border-left: 3px solid rgb(59, 130, 246) !important;
    }
</style>
    </div>
</x-app-layout>
<!-- SheetJS Library for Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js"></script>