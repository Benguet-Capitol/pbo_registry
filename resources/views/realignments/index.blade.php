<x-app-layout>
    @if (session('status'))
    @php
    $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200';
    if (str_contains(session('status'), 'updated successfully')) {
        $alertType = 'bg-blue-100 border-blue-400 text-blue-700 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-200';
    } elseif (str_contains(session('status'), 'deleted successfully')) {
        $alertType = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200';
    } elseif (str_contains(session('status'), 'created successfully')) {
        $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200';
    }
    @endphp
    <div class="border-l-4 p-4 mb-4 {{ $alertType }}" role="alert">
        <div class="flex justify-between items-center">
            <div>
                <p>{!! session('status') !!}</p>
            </div>
            <button type="button" class="text-2xl font-semibold leading-none dark:text-gray-200" onclick="this.parentElement.parentElement.remove();">
                &times;
            </button>
        </div>
    </div>
    @endif

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Realignment | Augmentation') }}
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

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800">
        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>
        
        <form id="filterForm" method="GET" action="{{ route('realignments.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <!-- Year Filter -->
                <div class="flex items-center space-x-2">
                    <label for="year1" class="sr-only">Year</label>
                    <x-form.select 
                    name="year1" 
                    id="year1" 
                    class="filter-select text-gray-400 border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" 
                    data-default="{{ date('Y') }}" 
                    onchange="this.form.submit()">
                        @foreach($availableYears as $year1)
                            <option value="{{ $year1 }}" {{ request('year1') == $year1 ? 'selected' : '' }}>{{ $year1 }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <!-- Office and Allotment Class Filter -->
                <div class="flex items-center space-x-2">
                    <label for="officeAllotmentClass" class="sr-only">Office & Class</label>
                    <x-form.select 
                    name="office_allotment_class_id" 
                    id="officeAllotmentClass" 
                    class="filter-select border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" 
                    data-default=""
                    onchange="this.form.submit()">
                        <option value="">All Allotment Classes per Office</option>
                        @foreach($officeAllotmentClasses as $officeAllotmentClass)
                            <option value="{{ $officeAllotmentClass->id }}" {{ request('office_allotment_class_id') == $officeAllotmentClass->id ? 'selected' : '' }}>
                                {{ $officeAllotmentClass->offices->office_abbreviation }} - {{ $officeAllotmentClass->allotmentClass->class }}
                            </option>
                        @endforeach
                    </x-form.select>
                </div>

                <!-- OBR Type Filter -->
                <div class="flex items-center space-x-2">
                    <label for="realignment_type" class="sr-only">Realignment Type</label>
                    <x-form.select 
                    name="realignment_type_filter" 
                    id="realignment_type_filter" 
                    class="filter-select border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" 
                    data-default=""
                    onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="Source" {{ request('realignment_type_filter') == 'Source' ? 'selected' : '' }}>Source</option>
                        <option value="Recipient" {{ request('realignment_type_filter') == 'Recipient' ? 'selected' : '' }}>Recipient</option>
                    </x-form.select>
                </div>

                <!-- Per Page Dropdown -->
                <div class="flex items-center space-x-2">
                    <label for="perPage" class="sr-only">Show per page</label>
                    <x-form.select 
                    name="per_page" 
                    id="perPage" 
                    class="filter-select text-gray-400 border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-white" 
                    onchange="this.form.submit()">
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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <div>
                    @can('create realignments')
                    <button onclick="openCreateRealignmentModal()" class="text-blue-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Create Realignment | Augmentation') }}
                    </button>
                    @endcan
                </div>
                <div class="flex items-center">
                    <form method="GET" action="{{ route('realignments.index') }}" class="flex items-center">
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for realignments" class="form-control border border-gray-300 rounded-lg w-72 px-4 py-2 mr-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                        <button type="submit" class="hidden"></button>
                    </form>
                </div>
            </div>
            <table id="realignmentsTable" class="text-center w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-center border-b-2 border-t-2 border-gray-700 text-xs text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('realignments.index', ['sort_by' => 'office_allotment_class', 'sort_order' => $sortBy == 'office_allotment_class' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Office & Class
                                @if($sortBy == 'office_allotment_class')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('realignments.index', ['sort_by' => 'type', 'sort_order' => $sortBy == 'type' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Type
                                @if($sortBy == 'type')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('realignments.index', ['sort_by' => 'realignment_no', 'sort_order' => $sortBy == 'realignment_no' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Realignment No.
                                @if($sortBy == 'realignment_no')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('realignments.index', ['sort_by' => 'realignment_date', 'sort_order' => $sortBy == 'realignment_date' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Realignment Date
                                @if($sortBy == 'realignment_date')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('realignments.index', ['sort_by' => 'basis', 'sort_order' => $sortBy == 'basis' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Basis
                                @if($sortBy == 'basis')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                Programs
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                Account Code
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                Description
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('realignments.index', ['sort_by' => 'amount', 'sort_order' => $sortBy == 'amount' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Amount
                                @if($sortBy == 'amount')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalSource = 0;
                        $totalRecipient = 0;
                    @endphp
                    @foreach ($realignments as $realignment)
                        @php
                            if ($realignment->type === 'Source') {
                                $totalSource += $realignment->amount;
                            } elseif ($realignment->type === 'Recipient') {
                                $totalRecipient += $realignment->amount;
                            }
                        @endphp
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $realignment->officeAllotmentClass->office_abbreviation ?? '-' }} - {{ $realignment->officeAllotmentClass->class ?? '-' }}
                            </td>
                            <td class="px-3 py-3 border-b border-gray-300">
                                @if($realignment->type === 'Recipient')
                                    <span class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold">
                                        {{ ucfirst($realignment->type) }}
                                    </span>
                                @elseif($realignment->type === 'Source')
                                    <span class="px-2 py-1 rounded text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300 font-semibold">
                                        {{ ucfirst($realignment->type) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-300">{{ ucfirst($realignment->type) }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $realignment->realignment_no }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $realignment->realignment_date }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $realignment->basis }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $realignment->appropriation->programs ?? '-' }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $realignment->appropriation->account_code ?? '-' }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $realignment->appropriation->description ?? '-' }}</td>
                            <td class="px-3 py-3 border-b border-gray-300">
                                @if($realignment->type === 'Recipient')
                                    <span class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold">
                                        {{ number_format($realignment->amount, 2) }}
                                    </span>
                                @elseif($realignment->type === 'Source')
                                    <span class="px-2 py-1 rounded text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300 font-semibold">
                                        {{ number_format($realignment->amount, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-300">{{ number_format($realignment->amount, 2) }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                <div class="relative inline-block text-left">
                                    <button onclick="toggleDropdown(this)" 
                                            class="relative text-xs group px-2 py-1.5">
                                            <span class="fas fa-ellipsis-v"></span>
                                            <!-- Tooltip -->
                                            <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                {{ $realignment->officeAllotmentClass->office_abbreviation ?? '-' }} - {{ $realignment->officeAllotmentClass->class ?? '-' }} | {{ $realignment->realignment_no }} | {{ number_format($realignment->amount, 2) }}
                                            </span>
                                        </button>
                                    <div class="absolute right-0 mt-1 w-32 bg-white border border-gray-300 rounded-lg shadow-lg hidden dropdown-menu z-10 dark:bg-gray-700 dark:border-gray-600">
                                        @can('edit realignments')
                                        <button onclick='openEditRealignmentModal(@json($realignment))' class="w-full text-left px-4 py-2 text-xs text-gray-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                            <i class="fas fa-edit mr-2"></i>Edit
                                        </button>
                                        @endcan
                                        @can('delete realignments')
                                        <button onclick="openDeleteRealignmentModal({{ $realignment->id }}, '{{ $realignment->realignment_no }}', '{{ $realignment->type }}', '{{ $realignment->amount }}', '{{ $realignment->appropriations_id }}')" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-600">
                                            <i class="fas fa-trash mr-2"></i>Delete
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr id="realignmentsFooter" class="bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-b-2 border-gray-700 dark:border-gray-600" style="display: none;">
                        <td colspan="5" class="text-center text-sm font-bold px-1 py-3 text-gray-700 dark:text-gray-300">
                            Total Source:
                            <span id="totalSourceFooter" class="px-2 py-1 rounded text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300 font-semibold ml-2">
                                0.00
                            </span>
                        </td>
                        <td colspan="5" class="text-center text-sm font-bold px-1 py-3 text-gray-700 dark:text-gray-300">
                            Total Recipient:
                            <span id="totalRecipientFooter" class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold ml-2">
                                0.00
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-4">
                @if ($perPage != 'all')
                {{ $realignments->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>

    @include('realignments.modal.create')
    @include('realignments.modal.edit')
    @include('realignments.modal.delete')

</x-app-layout>

<script>
    function filterTable() {
        // Declare variables
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toLowerCase();
        table = document.getElementById("realignmentsTable");
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
        updateFooterTotals();
    }

    // Add event listener for input event to filter table as you type
    document.getElementById('searchInput').addEventListener('input', filterTable);

    // Function to toggle dropdown menu
    function toggleDropdown(button) {
        let dropdown = button.nextElementSibling;
        let isOpen = !dropdown.classList.contains("hidden"); // true if already visible

        closeAllDropdowns(); // close all first

        if (!isOpen) {
            dropdown.classList.remove("hidden"); // open only if it wasn't open
        }
    }

    function closeAllDropdowns() {
        document.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.add("hidden"));
    }

    // Close dropdown if click happens outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative.inline-block')) {
            closeAllDropdowns();
        }
    });

    function closeDropdown() {
        // Example: hide any elements with a class of 'dropdown' or 'autocomplete-dropdown'
        document.querySelectorAll('.dropdown, .autocomplete-dropdown').forEach(drop => {
            drop.classList.add('hidden');
        });
    }

    function updateFooterTotals() {
        let table = document.getElementById("realignmentsTable");
        let tr = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
        let totalSource = 0;
        let totalRecipient = 0;
        for (let i = 0; i < tr.length; i++) {
            if (tr[i].style.display === "none") continue;
            let typeCell = tr[i].getElementsByTagName("td")[1];
            let amountCell = tr[i].getElementsByTagName("td")[8];
            if (!typeCell || !amountCell) continue;
            let type = typeCell.textContent.trim();
            let amountText = amountCell.textContent.replace(/[^\d.-]/g, '');
            let amount = parseFloat(amountText);
            if (isNaN(amount)) amount = 0;
            if (type === 'Source') {
                totalSource += amount;
            } else if (type === 'Recipient') {
                totalRecipient += amount;
            }
        }
        document.getElementById('totalSourceFooter').textContent = totalSource.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('totalRecipientFooter').textContent = totalRecipient.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        // Always show the footer
        document.getElementById('realignmentsFooter').style.display = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateFooterTotals();
    });
</script>