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
                {{ __('Supplemental Appropriations | Reversions') }}
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
        
        <form id="filterForm" method="GET" action="{{ route('supplementals.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

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
                    <x-form.select name="office_allotment_class_id" id="officeAllotmentClass" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
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
                    <label for="type" class="sr-only">Type</label>
                    <x-form.select name="supplemental_type_filter" id="supplemental_type_filter" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="Supplemental" {{ request('supplemental_type_filter') == 'Supplemental' ? 'selected' : '' }}>Supplemental</option>
                        <option value="Reversion" {{ request('supplemental_type_filter') == 'Reversion' ? 'selected' : '' }}>Reversion</option>
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


    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <div>
                    @can('create supplementals')
                    <button onclick="openCreateSupplementalsModal()" class="text-blue-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Create Supplemental Appropriation | Reversion') }}
                    </button>
                    @endcan
                </div>
                <div class="flex items-center">
                    <form method="GET" action="{{ route('supplementals.index') }}" class="flex items-center">
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search" class="form-control border border-gray-300 rounded-lg px-4 py-2 mr-2 text-xs w-72 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                        <button type="submit" class="hidden"></button>
                    </form>
                </div>
            </div>
            <table id="supplementalTable" class="text-center w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-center border-b-2 border-t-2 border-gray-700 text-xs text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('supplementals.index', ['sort_by' => 'supplemental_date', 'sort_order' => $sortBy == 'supplemental_date' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Date
                                @if($sortBy == 'supplemental_date')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('supplementals.index', ['sort_by' => 'office_allotment_class', 'sort_order' => $sortBy == 'office_allotment_class' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Office & Class
                                @if($sortBy == 'office_allotment_class')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('supplementals.index', ['sort_by' => 'type', 'sort_order' => $sortBy == 'type' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Type
                                @if($sortBy == 'type')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('supplementals.index', ['sort_by' => 'supplemental_no', 'sort_order' => $sortBy == 'supplemental_no' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                No.
                                @if($sortBy == 'supplemental_no')
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
                            <a href="{{ route('supplementals.index', ['sort_by' => 'basis', 'sort_order' => $sortBy == 'basis' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Basis
                                @if($sortBy == 'basis')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                Amount
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                1st Qtr
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                2nd Qtr
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                3rd Qtr
                        </th>
                        <th class="px-1 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                4th Qtr
                        </th>
                        <th class="px-6 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplementals as $supplemental)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $supplemental->supplemental_date }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                {{ $supplemental->officeAllotmentClass->office_abbreviation ?? '-' }} - {{ $supplemental->officeAllotmentClass->class ?? '-' }}
                            </td>
                            <td class="px-3 py-3 border-b border-gray-300">
                                @if($supplemental->type === 'Supplemental')
                                    <span class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold">
                                        {{ ucfirst($supplemental->type) }}
                                    </span>
                                @elseif($supplemental->type === 'Reversion')
                                    <span class="px-2 py-1 rounded text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300 font-semibold">
                                        {{ ucfirst($supplemental->type) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-300">{{ ucfirst($supplemental->type) }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $supplemental->supplemental_no }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300 max-w-xs">{{ $supplemental->appropriation->programs ?? '-' }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $supplemental->appropriation->account_code ?? '-' }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300 max-w-xs">{{ $supplemental->appropriation->description ?? '-' }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $supplemental->basis }}</td>
                            <td class="px-3 py-3 border-b border-gray-300">
                                @if($supplemental->type === 'Supplemental')
                                    <span class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold">
                                        {{ number_format($supplemental->amount, 2) }}
                                    </span>
                                @elseif($supplemental->type === 'Reversion')
                                    <span class="px-2 py-1 rounded text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300 font-semibold">
                                        {{ number_format($supplemental->amount, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-300">{{ number_format($supplemental->amount, 2) }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $supplemental->quarter1 }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $supplemental->quarter2 }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $supplemental->quarter3 }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $supplemental->quarter4 }}</td>
                            <td class="px-3 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                                <div class="relative inline-block text-left">
                                    <button onclick="toggleDropdown(this)" 
                                            class="relative text-xs group px-2 py-1.5">
                                        <span class="fas fa-ellipsis-v"></span>
                                        <!-- Tooltip -->
                                        <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                            {{ $supplemental->officeAllotmentClass->office_abbreviation ?? '-' }} - {{ $supplemental->officeAllotmentClass->class ?? '-' }} | {{ $supplemental->supplemental_no }} | {{ number_format($supplemental->amount, 2) }}
                                        </span>
                                    </button>
                                    <div class="absolute right-0 mt-1 w-32 bg-white border border-gray-300 rounded-lg shadow-lg hidden dropdown-menu z-10 dark:bg-gray-700 dark:border-gray-600">
                                        @can('edit supplementals')
                                        <button onclick='openEditSupplementalModal(@json($supplemental))' class="w-full text-left px-4 py-2 text-xs text-gray-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                            <i class="fas fa-edit mr-2"></i>Edit
                                        </button>
                                        @endcan
                                        @can('delete supplementals')
                                        <button onclick="openDeleteSupplementalModal({{ $supplemental->id }}, '{{ $supplemental->supplemental_no }}', '{{ $supplemental->type }}', '{{ $supplemental->amount }}', '{{ $supplemental->appropriations_id }}')" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-600">
                                            <i class="fas fa-trash mr-2"></i>Delete
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                No Supplemental Appropriations or Reversions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr id="realignmentsFooter" class="bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-b-2 border-gray-700 dark:border-gray-600">
                        <td colspan="7" class="text-center text-sm font-bold px-1 py-3 text-gray-700 dark:text-gray-300">
                            Total Supplemental Appropriations:
                            <span id="totalSourceFooter" class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-semibold ml-2">
                                0.00
                            </span>
                        </td>
                        <td colspan="7" class="text-center text-sm font-bold px-1 py-3 text-gray-700 dark:text-gray-300">
                            Total Reversions:
                            <span id="totalRecipientFooter" class="px-2 py-1 rounded text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300 font-semibold ml-2">
                                0.00
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @include('supplementals.modal.create')
    @include('supplementals.modal.edit')
    @include('supplementals.modal.delete')


</x-app-layout>

<script>
    function filterTable() {
        // Declare variables
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toLowerCase();
        table = document.getElementById("supplementalTable");
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

    function updateFooterTotals() {
        let table = document.getElementById("supplementalTable");
        let tr = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
        let totalSource = 0;
        let totalRecipient = 0;
        for (let i = 0; i < tr.length; i++) {
            if (tr[i].style.display === "none") continue;
            let typeCell = tr[i].getElementsByTagName("td")[2];
            let amountCell = tr[i].getElementsByTagName("td")[8];
            if (!typeCell || !amountCell) continue;
            let type = typeCell.textContent.trim();
            let amountText = amountCell.textContent.replace(/[^\d.-]/g, '');
            let amount = parseFloat(amountText);
            if (isNaN(amount)) amount = 0;
            if (type === 'Supplemental') {
                totalSource += amount;
            } else if (type === 'Reversion') {
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