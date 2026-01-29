<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('All Purchase Orders') }}

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

        <form id="filterForm" method="GET" action="{{ route('purchase_orders.all') }}">
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
                        <option value="10" {{ request('per_page', 'all') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 'all') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 'all') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 'all') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page', 'all') == 'all' ? 'selected' : '' }}>All</option>
                    </x-form.select>
                </div>
            </div>
        </form>
    </div>

    <!-- Purchase Orders Table -->
     <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-6 dark:bg-gray-800">
        <div class="p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-end items-center mb-4">
                <!-- @can('create purchase orders')
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Add Purchase Order') }}
                </button>
                @endcan -->
                <!-- Search Input -->
                <div class="flex items-center space-x-2">
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-72 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
            <div class="max-h-[720px] overflow-y-auto">
            <table id="purchaseOrdersTable" class="text-center font-semibold w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400 mb-8">
                <thead class="text-center text-xs border-b-2 border-gray-700 text-gray-700 bg-gray-50 border-t-2 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10">
                    <tr>
                        @php
                            $columns = [
                                'office_class' => 'Office & Class',
                                'obr_no' => 'OBR No.',
                                'program' => 'Program',
                                'account_code' => 'Account Code',
                                'description' => 'Description',
                                'po_number' => 'PO Number',
                                'po_date' => 'PO Date',
                                'pr_no' => 'PR Number',
                                'supplier' => 'Supplier',
                                'delivery_period' => 'Delivery Period',
                                'po_amount' => 'Purchase Order',
                                'disbursement' => 'Disbursement',
                                'remarks' => 'Remarks',
                            ];
                            $sortable = ['po_date', 'po_number', 'pr_no', 'supplier', 'delivery_period', 'po_amount', 'remarks'];
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
                    @forelse($purchaseOrders as $purchaseOrder)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer relative"
                            oncontextmenu="showPurchaseOrderContextMenu(event, this)"
                            data-po='@json($purchaseOrder)'
                            data-po-id="{{ $purchaseOrder->id }}"
                            data-po-number="{{ $purchaseOrder->po_number }}"
                            data-obligation-id="{{ $purchaseOrder->obligation_id }}">
                            <td class="px-2 py-3 text-left text-gray-600 dark:text-gray-300">
                                {{ optional($purchaseOrder->obligation->officeAllotmentClass->offices)->office_abbreviation ?? '-' }} -
                                {{ optional($purchaseOrder->obligation->officeAllotmentClass->allotmentClass)->class ?? '-' }}
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $purchaseOrder->obligation->obr_no ?? '-' }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300 max-w-xs">
                                @php
                                    $programs = $purchaseOrder->obligation->obligationAmounts->pluck('appropriation.programs')->unique()->filter()->implode(', ');
                                @endphp
                                {{ $programs ?: '-' }}
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">
                                @php
                                    $accountCode = $purchaseOrder->obligation->obligationAmounts
                                        ->where('id', $purchaseOrder->obligation_amounts_id)
                                        ->first()?->account_code ?? '-';
                                @endphp
                                {{ $accountCode }}
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300 max-w-xs">
                                @php
                                    $description = $purchaseOrder->obligation->obligationAmounts
                                        ->where('id', $purchaseOrder->obligation_amounts_id)
                                        ->first()?->appropriation?->description ?? '-';
                                @endphp
                                {{ $description }}
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $purchaseOrder->po_number }}</td>
                            <td class="px-2 py-3 text-left text-gray-600 dark:text-gray-300">{{ $purchaseOrder->po_date ?? '-' }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $purchaseOrder->pr_no ?? '-' }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300 max-w-xs">{{ $purchaseOrder->supplier ?? '-' }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $purchaseOrder->delivery_period ?? '-' }}</td>
                            <td class="px-2 py-3 text-right text-gray-600 dark:text-gray-300">{{ number_format($purchaseOrder->po_amount, 2) }}</td>
                            @php
                                // Get all obligation amounts related to purchase orders with the same po_number
                                $relatedPoIds = \App\Models\PurchaseOrder::where('po_number', $purchaseOrder->po_number)
                                    ->pluck('obligation_amounts_id')
                                    ->toArray();
                                
                                // Get disbursement amount for this specific obligation amount
                                $disbursementAmount = \App\Models\Disbursement::where('obligation_amounts_id', $purchaseOrder->obligation_amounts_id)
                                    ->sum('disbursement_amount');
                            @endphp
                            <td class="px-2 py-3 text-right text-gray-600 dark:text-gray-300">
                                @if($disbursementAmount > 0)
                                    <span class="font-semibold text-green-600 dark:text-green-400">{{ number_format($disbursementAmount, 2) }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300 max-w-xs">{{ $purchaseOrder->po_remarks ?? '-' }}</td>
                            <!-- @canany(['edit purchase orders', 'delete purchase orders'])
                                <td class="px-2 py-3 text-gray-600 dark:text-gray-300">
                                </td>
                            @endcanany -->
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="py-4 text-center text-gray-500">No purchase orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr id="purchaseOrdersFooter" class="bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-b-2 border-gray-700 dark:border-gray-600">
                        <td colspan="8" class="text-right text-xs font-bold px-1 py-3 text-gray-700 dark:text-gray-300">

                        </td>
                        <td colspan="4" class="text-right text-xs font-bold px-1 py-3 text-gray-700 dark:text-gray-300">
                            Total Purchase Order Amount:
                            <span id="totalPOAmountFooter" class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold ml-2">
                                0.00
                            </span>
                        </td>
                        <td colspan="2" class="text-right text-xs font-bold px-1 py-3 text-gray-700 dark:text-gray-300">

                        </td>
                    </tr>
                </tfoot>
            </table>
            </div>
            </div>
        </div>
    </div>

    <!-- Include Modal Files -->
    <div id="createDisbursementModalContainer"></div>
    <div id="purchaseOrderContextMenu" 
        class="fixed hidden w-48 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-400 rounded-lg shadow-2xl z-50 dark:from-blue-900 dark:to-blue-800 dark:border-blue-600"
        style="display: none;">
        @role('Developer|Administrator|Obligation')
        @can('edit purchase orders')
        <button id="contextEditPO"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-edit mr-2 text-blue-600"></i>Edit
        </button>
        @endcan
        @endrole
        
        @role('Developer|Administrator|Disbursement')
        @can('create disbursement')
        <button id="contextAddDisbursement"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-file-medical-alt mr-2 text-blue-600"></i>Add Disbursement
        </button>
        @endcan
        @endrole
    </div>
    

    <script>
        // Prevent multiple submissions
        let isSubmittingDisbursement = false;

        function updatePurchaseOrdersFooterTotal() {
            let table = document.getElementById("purchaseOrdersTable");
            let tr = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
            let totalPO = 0;
            for (let i = 0; i < tr.length; i++) {
                if (tr[i].style.display === "none") continue;
                let amountCell = tr[i].getElementsByTagName("td")[10]; // 10th column is DV / Check Amount
                if (!amountCell) continue;
                let amountText = amountCell.textContent.replace(/[^\d.-]/g, '');
                let amount = parseFloat(amountText);
                if (isNaN(amount)) amount = 0;
                totalPO += amount;
            }
            document.getElementById('totalPOAmountFooter').textContent = totalPO.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            // Always show the footer
            document.getElementById('purchaseOrdersFooter').style.display = '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            updatePurchaseOrdersFooterTotal();
        });
        function filterTable() {
            // Declare variables
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toLowerCase();
            table = document.getElementById("purchaseOrdersTable");
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
        }

        // Add event listener for input event to filter table as you type
        document.getElementById('searchInput').addEventListener('input', function() {
            filterTable();
            updatePurchaseOrdersFooterTotal();
        });

        // Context Menu Handler for Purchase Orders
        const poMenu = document.getElementById('purchaseOrderContextMenu');

        window.showPurchaseOrderContextMenu = function(event, row) {
            event.preventDefault();
            event.stopPropagation();
            
            // Remove highlight from previously selected row
            document.querySelectorAll('table tbody tr.context-menu-active').forEach(r => {
                r.classList.remove('context-menu-active');
            });
            
            // Highlight the current row
            row.classList.add('context-menu-active');
            window.currentContextMenuRow = row;

            if (!poMenu) return;

            // Get element positions
            const menuHeight = 150; // Approximate menu height
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
            poMenu.style.position = 'fixed';
            poMenu.style.top = `${top}px`;
            poMenu.style.left = `${left}px`;
            poMenu.style.display = 'block';
            poMenu.classList.remove('hidden');

            // Get purchase order data and set up menu items
            const purchaseOrder = row.dataset.po ? JSON.parse(row.dataset.po) : null;
            if (purchaseOrder) {
                // Add Disbursement button
                const addDisbursementBtn = poMenu.querySelector('#contextAddDisbursement');
                if (addDisbursementBtn && purchaseOrder.id) {
                    addDisbursementBtn.onclick = () => {
                        hidePurchaseOrderContextMenu();
                        openCreateDisbursementModal(purchaseOrder.obligation_id, purchaseOrder.id);
                    };
                }

                // Edit button
                const editBtn = poMenu.querySelector('#contextEditPO');
                if (editBtn && purchaseOrder.id) {
                    editBtn.onclick = () => {
                        hidePurchaseOrderContextMenu();
                        openEditPurchaseOrderModal(purchaseOrder);
                    };
                }
            }

            // Add event listeners with delay
            setTimeout(() => {
                document.addEventListener('click', hidePurchaseOrderContextMenu);
                window.addEventListener('resize', hidePurchaseOrderContextMenu);
                window.addEventListener('scroll', hidePurchaseOrderContextMenu, { passive: true });
                
                // Add scroll listener to container
                container.addEventListener('scroll', hidePurchaseOrderContextMenu, { passive: true });
            }, 30);
        };

        function hidePurchaseOrderContextMenu() {
            if (!poMenu) return;
            poMenu.classList.add('hidden');
            poMenu.style.display = 'none';
            
            // Remove highlight when menu is closed
            if (window.currentContextMenuRow) {
                window.currentContextMenuRow.classList.remove('context-menu-active');
                window.currentContextMenuRow = null;
            }
            
            // Clean up event listeners
            document.removeEventListener('click', hidePurchaseOrderContextMenu);
            window.removeEventListener('resize', hidePurchaseOrderContextMenu);
            window.removeEventListener('scroll', hidePurchaseOrderContextMenu);
            
            // Clean up container listeners
            const container = document.querySelector('.overflow-x-auto');
            if (container) {
                container.removeEventListener('scroll', hidePurchaseOrderContextMenu);
            }
        }

        // Hide on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hidePurchaseOrderContextMenu();
        });

        // Initialize scroll event listeners
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('.overflow-x-auto');
            if (container) {
                container.addEventListener('scroll', hidePurchaseOrderContextMenu, { passive: true });
            }
        });

        /* Modal Create Disbursement */
        function openCreateDisbursementModal(obligationId, purchaseOrderId = null) {
            closeAllDropdowns();
            const url = purchaseOrderId 
                ? `/obligations/${obligationId}/disbursement-modal?from=purchase_order&purchase_order_id=${purchaseOrderId}`
                : `/obligations/${obligationId}/disbursement-modal?from=purchase_order`;
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('createDisbursementModalContainer').innerHTML = html;
                    const modal = document.getElementById('createDisbursementModal');
                    // Trigger reflow to ensure CSS transitions work
                    modal.offsetHeight;
                    modal.style.display = 'flex';
                    modal.setAttribute('aria-hidden', 'false');

                    // Attach event listener AFTER modal is loaded
                    const statusField = modal.querySelector('#status');
                    if (statusField) {
                        statusField.addEventListener('change', function() {
                            if (statusField.value === 'Full Payment') {
                                modal.querySelectorAll('input[name^="disbursement_amount"]').forEach(function(input) {
                                    input.value = input.dataset.balance || "0";
                                });
                                updateDVAmountTotal();
                            } else if (statusField.value === 'Partial Payment') {
                                modal.querySelectorAll('input[name^="disbursement_amount"]').forEach(function(input) {
                                    input.value = '';
                                });
                                updateDVAmountTotal();
                            }
                        });

                        // If modal opens with "Full Payment" already selected (edit mode)
                        if (statusField.value === 'Full Payment') {
                            modal.querySelectorAll('input[name^="disbursement_amount"]').forEach(function(input) {
                                input.value = input.dataset.balance || "0";
                            });
                            updateDVAmountTotal();
                        }
                    }

                    // Run initial calculation inside modal
                    updateDVAmountTotal();
                });
        }

        function closeCreateDisbursementModal() {
            const modal = document.getElementById('createDisbursementModal');
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
        }

        function closeAllDropdowns() {
            // Close any open dropdowns if this function exists
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => dropdown.classList.add('hidden'));
        }

        /* Edit Purchase Order Modal Functions */
        function openEditPurchaseOrderModal(purchaseOrder) {
            closeAllDropdowns();

            document.querySelector("input[name='purchase_order_id']").value = purchaseOrder.id;
            document.getElementById('EditPurchaseOrderForm').action = `/purchase_orders/${purchaseOrder.id}`;

            document.getElementById('edit_po_date').value = purchaseOrder.po_date ?? '';
            document.getElementById('edit_po_number').value = purchaseOrder.po_number ?? '';
            document.getElementById('edit_pr_no').value = purchaseOrder.pr_no ?? '';
            document.getElementById('edit_delivery_period').value = purchaseOrder.delivery_period ?? '';
            document.getElementById('edit_supplier').value = purchaseOrder.supplier ?? '';
            document.getElementById('edit_po_remarks').value = purchaseOrder.po_remarks ?? '';

            // Fetch and display obligation amounts
            if (purchaseOrder.obligation_id) {
                fetch(`/api/obligations/${purchaseOrder.obligation_id}/amounts`)
                    .then(response => response.json())
                    .then(data => {
                        populateEditProgramsTable(data, purchaseOrder);
                    })
                    .catch(error => console.error('Error fetching obligation amounts:', error));
            }

            const modal = document.getElementById('editPurchaseOrderModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        }

        function populateEditProgramsTable(obligationAmounts, purchaseOrder) {
            const tbody = document.getElementById('edit_programs_tbody');
            tbody.innerHTML = '';

            // Fetch all purchase orders with the same po_number to get all related obligation amounts
            fetch(`/api/purchase-orders/by-number/${purchaseOrder.po_number}`)
                .then(response => response.json())
                .then(purchaseOrdersWithSameNumber => {
                    // Create a map of obligation_amounts_id to po_amount
                    const poAmountMap = {};
                    purchaseOrdersWithSameNumber.forEach(po => {
                        poAmountMap[po.obligation_amounts_id] = parseFloat(po.po_amount) || 0;
                    });

                    // Filter obligation amounts to show all those related to purchase orders with the same po_number
                    const relatedAmountIds = new Set(
                        purchaseOrdersWithSameNumber.map(po => po.obligation_amounts_id)
                    );

                    const relatedAmounts = obligationAmounts.filter(amount => {
                        return relatedAmountIds.has(amount.id);
                    });

                    relatedAmounts.forEach(amount => {
                        const appropriation = amount.appropriation || {};
                        const balance = (parseFloat(amount.obr_amount) || 0) + (parseFloat(amount.adjustment_amount) || 0);
                        const poAmount = poAmountMap[amount.id] || 0;

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${appropriation.programs || 'N/A'}</td>
                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${amount.account_code || 'N/A'}</td>
                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${appropriation.description || 'N/A'}</td>
                            <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">${balance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                <x-form.input type="number" name="edit_po_amount[${amount.id}]" min="0" step="0.01" 
                                       class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs rounded border border-gray-300" 
                                       value="${poAmount.toFixed(2)}" data-balance="${balance}" oninput="updateEditPOTotal()" />
                            </td>
                        `;
                        tbody.appendChild(row);
                    });

                    updateEditPOTotal();
                })
                .catch(error => {
                    console.error('Error fetching purchase orders by number:', error);
                    // Fallback: show only the related amount for this specific PO
                    const relatedAmounts = obligationAmounts.filter(amount => {
                        return amount.id === purchaseOrder.obligation_amounts_id;
                    });

                    relatedAmounts.forEach(amount => {
                        const appropriation = amount.appropriation || {};
                        const balance = (parseFloat(amount.obr_amount) || 0) + (parseFloat(amount.adjustment_amount) || 0);

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${appropriation.programs || 'N/A'}</td>
                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${amount.account_code || 'N/A'}</td>
                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${appropriation.description || 'N/A'}</td>
                            <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">${balance.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                <x-form.input type="number" name="edit_po_amount[${amount.id}]" min="0" step="0.01" 
                                       class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs rounded border border-gray-300" 
                                       value="${parseFloat(purchaseOrder.po_amount).toFixed(2)}" data-balance="${balance}" oninput="updateEditPOTotal()" />
                            </td>
                        `;
                        tbody.appendChild(row);
                    });

                    updateEditPOTotal();
                });
        }

        function updateEditPOTotal() {
            const inputs = document.querySelectorAll("input[name^='edit_po_amount']");
            let total = 0;
            inputs.forEach(input => {
                const val = parseFloat(input.value) || 0;
                total += val;
            });
            const totalCell = document.getElementById('editPurchaseOrderTotalCell');
            if (totalCell) {
                totalCell.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        function closeEditPurchaseOrderModal() {
            const modal = document.getElementById('editPurchaseOrderModal');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }

        // Functions needed for disbursement modal
        function updateDVAmountTotal() {
            const adjustedInputs = document.querySelectorAll("input[name^='disbursement_amount']");
            let total = 0;
            adjustedInputs.forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val) && val > 0) {
                    total += val;
                }
            });
            const totalCell = document.getElementById('dvAmountTotalCell');
            if (totalCell) {
                totalCell.textContent = total.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }

        function validateDisbursementAmount(inputElement) {
            const maxBalance = parseFloat(inputElement.dataset.balance || "0");
            const inputValue = parseFloat(inputElement.value || "0");

            if (inputValue > maxBalance) {
                inputElement.value = maxBalance.toFixed(2);
                inputElement.title = `Max allowed is ₱${maxBalance.toFixed(2)}`;
            }
        }

        function validateFormCreateDisbursement() {
            // Prevent multiple submissions
            if (isSubmittingDisbursement) {
                return false;
            }

            let isValid = true;

            // Clear previous error messages
            const dvNoError = document.getElementById('dv_noError');
            const statusError = document.getElementById('statusError');
            const tableMessageDV = document.getElementById('tableMessageDV');

            if (dvNoError) dvNoError.innerText = '';
            if (statusError) statusError.innerText = '';
            if (tableMessageDV) {
                tableMessageDV.classList.add('hidden');
                tableMessageDV.innerText = '';
            }

            // Validate DV Number
            const poNumber = document.getElementById('dv_no');
            if (poNumber && poNumber.value.trim() === '') {
                if (dvNoError) dvNoError.innerText = 'DV / Check Number is required.';
                isValid = false;
            }

            // Validate Status
            const status = document.getElementById('status');
            if (status && status.value === '') {
                if (statusError) statusError.innerText = 'Status is required.';
                isValid = false;
            }

            // Validate at least one DV Amount is entered and does not exceed balance
            const amountInputs = document.querySelectorAll('input[name^="disbursement_amount"]');
            let atLeastOneAmountEntered = false;

            amountInputs.forEach(input => {
                const value = parseFloat(input.value || "0");
                const maxBalance = parseFloat(input.dataset.balance || "0");

                if (value > 0) {
                    atLeastOneAmountEntered = true;
                    if (value > maxBalance) {
                        const errorSpan = input.nextElementSibling;
                        if (errorSpan) {
                            errorSpan.innerText = `Amount exceeds the available balance of ₱${maxBalance.toFixed(2)}.`;
                        }
                        isValid = false;
                    } else {
                        const errorSpan = input.nextElementSibling;
                        if (errorSpan) {
                            errorSpan.innerText = '';
                        }
                    }
                } else {
                    const errorSpan = input.nextElementSibling;
                    if (errorSpan) {
                        errorSpan.innerText = '';
                    }
                }
            });

            if (!atLeastOneAmountEntered) {
                if (tableMessageDV) {
                    tableMessageDV.innerText = 'Please enter at least one DV / Check Amount.';
                    tableMessageDV.classList.remove('hidden');
                }
                isValid = false;
            }

            // If validations pass so far, check if DV number already exists
            if (isValid && poNumber && poNumber.value.trim() !== '') {
                const dvNo = poNumber.value.trim();
                const obligationId = document.querySelector('input[name="obligation_id"]')?.value;
                
                // Set flag to prevent multiple submissions
                isSubmittingDisbursement = true;
                
                // Fetch the year from the obligation's office allotment class
                fetch(`/api/obligations/${obligationId}/year`)
                    .then(response => response.json())
                    .then(yearData => {
                        // Make AJAX call to check DV uniqueness
                        return fetch('{{ route("disbursements.checkDvNumber") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            },
                            body: JSON.stringify({
                                dv_no: dvNo,
                                year: yearData.year
                            })
                        });
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            if (dvNoError) {
                                dvNoError.innerHTML = data.message;
                                // Scroll error into view and focus on the field
                                dvNoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                poNumber.focus();
                            }
                            // Reset flag since we're not submitting
                            isSubmittingDisbursement = false;
                            return;
                        }

                        // All validations passed, submit the form
                        const form = document.getElementById('CreateDisbursementForm');
                        if (form) {
                            form.submit();
                        }
                    })
                    .catch(error => {
                        console.error('Error checking DV number:', error);
                        // Reset flag and show error
                        isSubmittingDisbursement = false;
                        if (dvNoError) {
                            dvNoError.innerText = 'Error validating DV number. Please try again.';
                            // Scroll error into view and focus on the field
                            dvNoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            poNumber.focus();
                        }
                    });
            } else if (isValid) {
                // If all validations pass, submit the form
                isSubmittingDisbursement = true;
                const form = document.getElementById('CreateDisbursementForm');
                if (form) {
                    form.submit();
                }
            }
            return false;
        }

        document.addEventListener('input', function(event) {
            if (event.target.name && event.target.name.startsWith('disbursement_amount')) {
                updateDVAmountTotal();
            }
        });
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

    /* Row highlight when context menu is open */
    table tbody tr.context-menu-active {
        background-color: rgba(59, 130, 246, 0.15);
        transition: background-color 0.2s ease-in-out;
    }

    .dark table tbody tr.context-menu-active {
        background-color: rgba(59, 130, 246, 0.25);
    }
</style>
    </div>

    <!-- Edit Purchase Order Modal -->
    <form id="EditPurchaseOrderForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div id="editPurchaseOrderModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
            <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
                <!-- Modal header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                        {{ __('Edit Purchase Order') }}
                    </h3>
                    <button type="button" onclick="closeEditPurchaseOrderModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                    <div class="grid gap-3">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                                <input type="hidden" name="purchase_order_id" id="purchase_order_id">
                                <input type="hidden" name="redirect" id="redirect" value="all">

                                <!-- PO Date -->
                                <div class="sm:col-span-3">
                                    <x-form.label for="edit_po_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('PO Date')" />
                                    <div class="mt-2">
                                        <x-form.input-with-icon-wrapper>
                                            <x-slot name="icon">
                                                <i class="fas fa-calendar"></i>
                                            </x-slot>
                                            <x-form.input withicon type='date' name="edit_po_date" autocomplete="off" id="edit_po_date" placeholder="{{ __('Date') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                        </x-form.input-with-icon-wrapper>
                                    </div>
                                </div>

                                <!-- PO Number -->
                                <div class="sm:col-span-3">
                                    <x-form.label for="edit_po_number" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('PO Number')" />
                                    <div class="mt-2">
                                        <x-form.input-with-icon-wrapper>
                                            <x-slot name="icon">
                                                <i class="fas fa-hashtag"></i>
                                            </x-slot>
                                            <x-form.input withicon name="edit_po_number" autocomplete="off" id="edit_po_number" placeholder="{{ __('PO Number') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                        </x-form.input-with-icon-wrapper>
                                        <span id="edit_po_numberError" class="text-red-500 text-sm"></span>
                                    </div>
                                </div>

                                <!-- PR Number -->
                                <div class="sm:col-span-3">
                                    <x-form.label for="edit_pr_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('PR Number')" />
                                    <div class="mt-2">
                                        <x-form.input-with-icon-wrapper>
                                            <x-slot name="icon">
                                                <i class="fas fa-list-ol"></i>
                                            </x-slot>
                                            <x-form.input withicon name="edit_pr_no" autocomplete="off" id="edit_pr_no" placeholder="{{ __('PR Number') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                        </x-form.input-with-icon-wrapper>
                                        <span id="edit_pr_noError" class="text-red-500 text-sm"></span>
                                    </div>
                                </div>

                                <!-- Delivery Period -->
                                <div class="sm:col-span-3">
                                    <x-form.label for="edit_delivery_period" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Delivery Period')" />
                                    <div class="mt-2">
                                        <x-form.input-with-icon-wrapper>
                                            <x-slot name="icon">
                                                <i class="fas fa-calendar-day"></i>
                                            </x-slot>
                                            <x-form.input withicon name="edit_delivery_period" autocomplete="off" id="edit_delivery_period" placeholder="{{ __('Delivery Period') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                        </x-form.input-with-icon-wrapper>
                                        <span id="edit_delivery_periodError" class="text-red-500 text-sm"></span>
                                    </div>
                                </div>

                                <!-- Supplier -->
                                <div class="sm:col-span-6">
                                    <x-form.label for="edit_supplier" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Supplier')" />
                                    <div class="mt-2">
                                        <x-form.input-with-icon-wrapper>
                                            <x-slot name="icon">
                                                <i class="fas fa-store"></i>
                                            </x-slot>
                                            <x-form.input withicon name="edit_supplier" autocomplete="off" id="edit_supplier" placeholder="{{ __('Supplier') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                        </x-form.input-with-icon-wrapper>
                                        <span id="edit_supplierError" class="text-red-500 text-sm"></span>
                                    </div>
                                </div>

                                <!-- Remarks -->
                                <div class="sm:col-span-6">
                                    <x-form.label for="edit_po_remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                    <div class="mt-2">
                                        <x-form.input-with-icon-wrapper>
                                            <x-slot name="icon">
                                                <i class="fas fa-circle-info"></i>
                                            </x-slot>
                                            <x-form.textarea withicon name="edit_po_remarks" autocomplete="off" id="edit_po_remarks" placeholder="{{ __('Remarks') }}" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                        </x-form.input-with-icon-wrapper>
                                    </div>
                                </div>

                                <!-- Programs Table -->
                                <div class="sm:col-span-6">
                                    <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                    <!-- Message Placeholder -->
                                    <div id="tableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                    <div class="mt-2 overflow-x-auto">
                                        <table id="edit_programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-800">
                                                <tr>
                                                    <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                        {{ __('Program') }}
                                                    </th>
                                                    <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                        {{ __('Account Code') }}
                                                    </th>
                                                    <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                        {{ __('Description') }}
                                                    </th>
                                                    <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                        {{ __('Balance from Obligations') }}
                                                    </th>
                                                    <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                        {{ __('Purchase Order Amount') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="edit_programs_tbody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                <!-- Will be populated by JavaScript -->
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-gray-50 dark:bg-gray-900">
                                                    <td colspan="4" class="px-2 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-200">
                                                        Total Purchase Order Amount:
                                                    </td>
                                                    <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400" id="editPurchaseOrderTotalCell">
                                                        0.00
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <button type="submit" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditPurchaseOrderModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <style>
        @keyframes scaleInUp {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>


</x-app-layout>
