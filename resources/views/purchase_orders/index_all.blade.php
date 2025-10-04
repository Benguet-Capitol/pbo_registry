<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('All Purchase Orders') }}
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
        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>

        <form id="filterForm" method="GET" action="{{ route('purchase_orders.all') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                <!-- Year Filter -->
                <div class="flex items-center space-x-2">
                    <label for="year1" class="sr-only">Year</label>
                    <x-form.select name="year1" id="year1" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        @foreach($availableYears as $year1)
                        <option value="{{ $year1 }}" {{ request('year1') == $year1 ? 'selected' : '' }}>{{ $year1 }}</option>
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
            <div class="flex justify-between items-center mb-4">
                <!-- @can('create purchase orders')
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Add Purchase Order') }}
                </button>
                @endcan -->
                <!-- Search Input -->
                <div class="flex items-center space-x-2">
                    <form method="GET" action="{{ route('purchase_orders.all') }}" class="flex items-center">
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-72 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                    </form>
                </div>
            </div>
            <table id="purchaseOrdersTable" class="text-center w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-center text-xs border-b-2 border-gray-700 text-gray-700 bg-gray-50 border-t-2 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        @php
                            $columns = [
                                'po_date' => 'PO Date',
                                'office_class' => 'Office & Class',
                                'obr_no' => 'OBR No.',
                                'program' => 'Program',
                                'account_code' => 'Account Code',
                                'description' => 'Description',
                                'po_number' => 'PO Number',
                                'pr_no' => 'PR Number',
                                'supplier' => 'Supplier',
                                'delivery_period' => 'Delivery Period',
                                'po_amount' => 'PO Amount',
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
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-2 py-3 text-left text-gray-600 dark:text-gray-300">{{ $purchaseOrder->po_date ?? '-' }}</td>
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
                                    $accountCodes = $purchaseOrder->obligation->obligationAmounts->pluck('appropriation.account_code')->unique()->filter()->implode(', ');
                                @endphp
                                {{ $accountCodes ?: '-' }}
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300 max-w-xs">
                                @php
                                    $descriptions = $purchaseOrder->obligation->obligationAmounts->pluck('appropriation.description')->unique()->filter()->implode(', ');
                                @endphp
                                {{ $descriptions ?: '-' }}
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $purchaseOrder->po_number }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $purchaseOrder->pr_no ?? '-' }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300 max-w-xs">{{ $purchaseOrder->supplier ?? '-' }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $purchaseOrder->delivery_period ?? '-' }}</td>
                            <td class="px-2 py-3 text-right text-gray-600 dark:text-gray-300">{{ number_format($purchaseOrder->po_amount, 2) }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300 max-w-xs">{{ $purchaseOrder->po_remarks ?? '-' }}</td>
                            <!-- @canany(['edit purchase orders', 'delete purchase orders'])
                                <td class="px-2 py-3 text-gray-600 dark:text-gray-300">
                                </td>
                            @endcanany -->
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-4 text-center text-gray-500">No purchase orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr id="purchaseOrdersFooter" class="bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-b-2 border-gray-700 dark:border-gray-600">
                        <td colspan="8" class="text-right text-xs font-bold px-1 py-3 text-gray-700 dark:text-gray-300">

                        </td>
                        <td colspan="3" class="text-right text-xs font-bold px-1 py-3 text-gray-700 dark:text-gray-300">
                            Total Purchase Order Amount:
                            <span id="totalPOAmountFooter" class="px-2 py-1 rounded text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300 font-semibold ml-2">
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

    <script>
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
    </script>



</x-app-layout>
