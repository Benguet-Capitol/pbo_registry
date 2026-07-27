<x-app-layout>
    <!-- Load SheetJS Library for Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <!-- Left: Obligations Title with Filters -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Obligations') }}

                @php
                $filters = [];

                if (request('office_allotment_class_filter')) {
                    $officeClass = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_filter'));
                    if ($officeClass) {
                        $filters[] = $officeClass->offices->office_abbreviation . ' - ' . $officeClass->allotmentClass->class;
                    }
                }
                if (request('fund_filter')) {
                    $filters[] = 'Fund: ' . request('fund_filter');
                }
                if (request('obr_type_filter')) {
                    $filters[] = request('obr_type_filter');
                }
                if (request('from_date') || request('to_date')) {
                    $fromDate = request('from_date') ? date('M d, Y', strtotime(request('from_date'))) : 'Start';
                    $toDate = request('to_date') ? date('M d, Y', strtotime(request('to_date'))) : 'End';
                    $filters[] = "$fromDate - $toDate";
                }
                @endphp

                @if (count($filters) > 0)
                    <span class="text-lg"> > </span>
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

    <!-- Include modals early so functions are available -->
    @include('obligations.modal.obligation_details')
    @include('obligations.modal.cancellation')
    @include('obligations.modal.delete')
    @include('obligations.modal.create')
    @include('obligations.modal.edit')
    @include('obligations.modal.payment_remarks')
    @include('obligations.modal.obligation_files')

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

        <form id="filterForm" method="GET" action="{{ route('obligations.index') }}">
            <!-- Hidden inputs to preserve search parameters -->
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="search_column" value="{{ request('search_column') }}">
            <input type="hidden" name="sort_by" value="{{ $sortBy }}">
            <input type="hidden" name="sort_order" value="{{ $sortOrder }}">
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

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

                <!-- Fund Filter -->
                <div class="flex items-center space-x-2">
                    <label for="fundFilter" class="sr-only">Fund</label>
                    <x-form.select name="fund_filter" id="fundFilter" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        <option value="">All Funds</option>
                        @foreach($funds as $fund)
                        <option value="{{ $fund }}" {{ request('fund_filter') == $fund ? 'selected' : '' }}>
                            {{ $fund }}
                        </option>
                        @endforeach
                    </x-form.select>
                </div>

                <!-- OBR Type Filter -->
                <div class="flex items-center space-x-2">
                    <label for="obr_type" class="sr-only">OBR Type</label>
                    <x-form.select name="obr_type_filter" id="obr_type_filter" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="Regular" {{ request('obr_type_filter') == 'Regular' ? 'selected' : '' }}>Regular</option>
                        <option value="Purchase Request" {{ request('obr_type_filter') == 'Purchase Request' ? 'selected' : '' }}>Purchase Request</option>
                        <option value="Contract" {{ request('obr_type_filter') == 'Contract' ? 'selected' : '' }}>Project/Contract</option>
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

            <!-- Date Range Filter Row -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-2">
                <!-- From Date Filter -->
                <div class="flex flex-col space-x-2">
                    <label for="fromDate" class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-2">From Date</label>
                    <x-form.input type="date" name="from_date" id="fromDate" value="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
                </div>

                <!-- To Date Filter -->
                <div class="flex flex-col space-x-2">
                    <label for="toDate" class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-2">To Date</label>
                    <x-form.input type="date" name="to_date" id="toDate" value="{{ request('to_date') }}" min="{{ request('from_date') }}" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
                </div>

                <!-- Apply Filter Button -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="w-full text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-filter mr-2"></i>Apply Date Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <!-- Left: Action Button -->
                @can('create obligations')
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Create Obligation') }}
                </button>
                @else
                <div></div>
                @endcan
                <!-- Right: Total Records and Search Input -->
                <div class="flex items-center space-x-4">
                    <!-- Export Button -->
                    <button type="button" id="exportObligationsBtn" onclick="exportObligationsToExcel()" class="text-green-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-6 py-2 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-colors">
                        <i id="exportObligationsIcon" class="fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4"></i>
                        <span id="exportObligationsLabel">Export to Excel</span>
                    </button>
                    <!-- Total Records -->
                    <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $totalRecords }}</span>
                    </div>
                    <!-- Search Section -->
                    <form id="searchForm" method="GET" action="{{ route('obligations.index') }}" class="flex items-center space-x-2">
                        <!-- Hidden inputs to preserve filters -->
                        <input type="hidden" name="year1" value="{{ $selectedYear }}">
                        <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
                        <input type="hidden" name="fund_filter" value="{{ request('fund_filter') }}">
                        <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
                        <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                        <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 'all') }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_order" value="{{ $sortOrder }}">
                        
                        <x-form.select name="search_column" id="searchColumn" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-40 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Columns</option>
                            <option value="obr_no" {{ request('search_column') == 'obr_no' ? 'selected' : '' }}>OBR No.</option>
                            <option value="obr_date" {{ request('search_column') == 'obr_date' ? 'selected' : '' }}>OBR Date</option>
                            <option value="obr_type" {{ request('search_column') == 'obr_type' ? 'selected' : '' }}>OBR Type</option>
                            <option value="particulars" {{ request('search_column') == 'particulars' ? 'selected' : '' }}>Particulars</option>
                            <option value="office_abbreviation" {{ request('search_column') == 'office_abbreviation' ? 'selected' : '' }}>Office</option>
                            <option value="allotment_class" {{ request('search_column') == 'allotment_class' ? 'selected' : '' }}>Allotment Class</option>
                            <option value="processed_by" {{ request('search_column') == 'processed_by' ? 'selected' : '' }}>Processed By</option>
                            <option value="remarks" {{ request('search_column') == 'remarks' ? 'selected' : '' }}>Remarks</option>
                        </x-form.select>
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for obligations" class="border border-gray-300 rounded-lg px-4 py-2 text-xs flex-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white w-80" />
                        <button type="submit" class="text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
                <div class="max-h-[560px] overflow-y-auto">
                    <table id="obligationsTable" class="mb-20 min-w-full text-xs text-center text-gray-600 dark:text-gray-300">
                        <thead id="obligationTableHead"
                            class="text-center border-b-2 border-t-2 border-gray-700 text-xs text-gray-700 bg-gray-200 dark:bg-gray-900 dark:text-gray-400 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'office_allotment_class', 'sort_order' => $sortBy == 'office_allotment_class' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Office & Class
                                        @if($sortBy == 'office_allotment_class')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'obr_no', 'sort_order' => $sortBy == 'obr_no' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        OBR No.
                                        @if($sortBy == 'obr_no')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'obr_date', 'sort_order' => $sortBy == 'obr_date' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        OBR Date
                                        @if($sortBy == 'obr_date')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'obr_type', 'sort_order' => $sortBy == 'obr_type' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        OBR Type
                                        @if($sortBy == 'obr_type')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'particulars', 'sort_order' => $sortBy == 'particulars' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Particulars
                                        @if($sortBy == 'particulars')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'obr_amount', 'sort_order' => $sortBy == 'obr_amount' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Obligation
                                        @if($sortBy == 'obr_amount')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'po_amount', 'sort_order' => $sortBy == 'po_amount' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Purchase Order
                                        @if($sortBy == 'po_amount')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                @hasanyrole('Obligation|Administrator|Developer')
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'remarks', 'sort_order' => $sortBy == 'remarks' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Remarks
                                        @if($sortBy == 'remarks')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                @endhasanyrole
                                @hasanyrole('Disbursement|Administrator|Developer|Obligation')
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'dv_amount', 'sort_order' => $sortBy == 'dv_amount' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Disbursement
                                        @if($sortBy == 'dv_amount')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'balance', 'sort_order' => $sortBy == 'balance' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Balance
                                        @if($sortBy == 'balance')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'payment_remarks', 'sort_order' => $sortBy == 'payment_remarks' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Payment Remarks
                                        @if($sortBy == 'payment_remarks')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                @endhasanyrole
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Files
                                </th>
                                <!-- <th class="px-6 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Actions
                                </th> -->
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($obligations as $obligation)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer relative"
                                ondblclick="openModal({{ $obligation->id }})"
                                oncontextmenu="showObligationContextMenu(event, this)"
                                data-obligation='@json($obligation)'
                                data-obligation-id="{{ $obligation->id }}"
                                data-obligation-obr="{{ $obligation->obr_no }}"
                                data-obligation-payment-remarks="{{ $obligation->payment_remarks }}"
                                data-obligation-office="{{ $obligation->officeAllotmentClass->offices->office_abbreviation }}"
                                data-obligation-class="{{ $obligation->officeAllotmentClass->allotmentClass->class }}"
                                data-obligation-amount="{{ $obligation->obr_amount }}"
                            >
                                <td class="font-semibold px-1 py-2">{{ $obligation->officeAllotmentClass->offices->office_abbreviation }} - {{ $obligation->officeAllotmentClass->allotmentClass->class }}</td>
                                <td class="font-semibold text-left px-1 py-2">{{ $obligation->obr_no }}</td>
                                <td class="text-left px-1 py-2">{{ $obligation->obr_date }}</td>
                                <td class="text-left px-1 py-2">{{ $obligation->obr_type }}</td>
                                <td class="text-left px-1 py-2 max-w-sm">{{ $obligation->particulars }}</td>

                                <td class="px-1 py-2 text-right obligation-amount">
                                    <div class="relative inline-block group">
                                        @if ($obligation->obr_amount == 0.00)
                                            <span class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 px-2 py-1 rounded font-semibold">Cancelled</span>
                                        @elseif ($obligation->obligationAdjustments->isNotEmpty())
                                            @unlessrole('Disbursement')
                                                <button onclick="openCreateObligationAdjustmentModal({{ $obligation->id }})"
                                                    type="button"
                                                    class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 hover:underline rounded font-semibold">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </button>
                                                <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                    Add Obligation Adjustment
                                                </span>
                                            @else
                                                <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </span>
                                            @endunlessrole
                                        @else
                                            @unlessrole('Disbursement')
                                                <button onclick="openCreateObligationAdjustmentModal({{ $obligation->id }})"
                                                    type="button"
                                                    class="font-semibold text-gray-700 dark:text-gray-400 hover:underline px-2 py-1">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </button>
                                                <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                    Add Obligation Adjustment
                                                </span>
                                            @else
                                                <span class="font-semibold text-gray-700 dark:text-gray-400 px-2 py-1">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </span>
                                            @endunlessrole
                                        @endif
                                    </div>
                                </td>

                                <td class="px-1 py-2 text-right po-amount">
                                    @php $poAmount = $obligation->purchaseOrders->sum('po_amount'); @endphp
                                    @if ($obligation->obr_type === 'Purchase Request')
                                        <div class="relative inline-block group">
                                            @if ($poAmount > 0)
                                                @unlessrole('Disbursement')
                                                    <button onclick="openCreatePOModal({{ $obligation->id }})"
                                                        type="button"
                                                        class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold hover:underline">
                                                        {{ number_format($poAmount, 2) }}
                                                    </button>
                                                    <!-- Tooltip -->
                                                    <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                        Add Purchase Order
                                                    </span>
                                                @else
                                                    <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold">
                                                        {{ number_format($poAmount, 2) }}
                                                    </span>
                                                @endunlessrole
                                            @else
                                                @unlessrole('Disbursement')
                                                    <button onclick="openCreatePOModal({{ $obligation->id }})"
                                                        type="button"
                                                        class="font-semibold text-blue-700 dark:text-blue-400 hover:underline px-2 py-1">
                                                        {{ number_format($poAmount, 2) }}
                                                    </button>
                                                    <!-- Tooltip -->
                                                    <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                        Add Purchase Order
                                                    </span>
                                                @else
                                                    <span class="font-semibold text-blue-700 dark:text-blue-400 px-2 py-1">
                                                        {{ number_format($poAmount, 2) }}
                                                    </span>
                                                @endunlessrole
                                            @endif
                                        </div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                @hasanyrole('Obligation|Administrator|Developer')
                                <td class="px-1 py-2 text-center max-w-48">{{ $obligation->remarks ? : '-' }}</td>
                                @endhasanyrole

                                @hasanyrole('Disbursement|Administrator|Developer|Obligation')
                                <td class="px-1 py-2 text-right dv-amount">
                                @php
                                    $disbursementAmount = $obligation->disbursements->sum('disbursement_amount') ?? 0;
                                    $obligationAmount = $obligation->obr_amount ?? 0;

                                    $disbursementAmountStr = number_format((float) (is_numeric($disbursementAmount) ? $disbursementAmount : 0), 2, '.', '');
                                    $obligationAmountStr = number_format((float) (is_numeric($obligationAmount) ? $obligationAmount : 0), 2, '.', '');

                                    $isEqual = bccomp($disbursementAmountStr, $obligationAmountStr, 2) === 0;
                                    $isLower = $disbursementAmount < $obligationAmount && $disbursementAmount > 0;
                                    $isZero = $disbursementAmount == 0;
                                    $isOBRZero = $obligationAmount == 0;
                                @endphp

                                @if ($obligation->obr_type !== 'Purchase Request')
                                <div class="relative inline-block group">
                                        <button onclick="openCreateDisbursementModal({{ $obligation->id }})"
                                            type="button"
                                            class="hover:underline px-2 py-1
                                                @if ($isOBRZero)
                                                    font-semibold text-gray-700 dark:text-gray-400
                                                @elseif ($isEqual)
                                                    bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-semibold
                                                @elseif ($isLower)
                                                    bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 font-semibold
                                                @elseif ($isZero)
                                                    font-semibold text-gray-700 dark:text-gray-400
                                                @else
                                                    font-semibold text-gray-700 dark:text-gray-400
                                                @endif
                                            ">
                                            {{ number_format($disbursementAmount, 2) }}
                                        </button>

                                        <!-- Tooltip -->
                                        <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block 
                                                    bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                            Add Disbursement
                                        </span>
                                </div>
                                @else
                                    <span class="text-gray-700 dark:text-gray-400 px-2 py-1">{{ number_format($disbursementAmount, 2) }}</span>
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right balance">
                                @php
                                    $balance = $obligation->obr_amount - $disbursementAmount;
                                @endphp
                                <div class="relative inline-block group">
                                    @if ($obligationAmount == 0)
                                        <span class="text-gray-700 dark:text-gray-400 px-2 py-1">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @elseif ($balance > 0)
                                        <span class="bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-400 font-semibold px-2 py-1">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @elseif ($balance < 0)
                                        <span class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 font-semibold px-2 py-1">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @else
                                        <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-semibold px-2 py-1">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-1 py-2 text-center max-w-48 payment-remarks">
                                <div class="relative inline-block group">
                                    {{ $obligation->payment_remarks ? $obligation->payment_remarks : '-' }}
                                </div>
                            </td>
                            @endhasanyrole
                            <td class="px-1 py-2 text-center">
                                @php
                                    $fileCount = $obligation->files()->count();
                                @endphp
                                <button onclick="openObligationFilesModal({{ $obligation->id }}, '{{ $obligation->obr_no }}')" 
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded transition-colors
                                    @if($fileCount > 0)
                                        bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-800 font-semibold
                                    @else
                                        bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600
                                    @endif"
                                    title="View files">
                                    <i class="fas fa-file"></i>
                                    <span>{{ $fileCount }}</span>
                                </button>
                            </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 italic">
                                        No Obligations found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- Sticky footer table for totals -->
                    <div id="obligationTableFooter" class="sticky bottom-0 left-0 right-0 bg-white dark:bg-gray-900 border-t-2 border-b-2 border-gray-700 dark:border-gray-600 z-10">
                        <table class="min-w-full text-sm text-center text-gray-600 bg-gray-200 dark:bg-gray-900 dark:text-gray-300">
                            <tbody>
                                <tr class="bg-gray-200 dark:bg-gray-900 font-bold">
                                    <td class="text-right px-4 py-3">Total Obligation:</td>
                                    <td class="text-left px-4 py-3 text-green-700 dark:text-green-300 font-semibold" id="footerTotalObligationAmount">0.00</td>
                                    <td class="text-right px-4 py-3">Total Purchase Order:</td>
                                    <td class="text-left px-4 py-3 text-blue-700 dark:text-blue-300 font-semibold" id="footerTotalPOAmount">0.00</td>
                                    @hasanyrole('Disbursement|Administrator|Developer')
                                    <td class="text-right px-4 py-3">Total Disbursement:</td>
                                    <td class="text-left px-4 py-3 text-orange-700 dark:text-orange-300 font-semibold" id="footerTotalDisbursementAmount">0.00</td>
                                    @endhasanyrole
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Obligation Details Panel -->
                    <div id="obligationDetailsPanel" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-md px-4 pb-4 pt-2">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                <span id="detailObrNo" class="text-blue-600 dark:text-blue-400"></span>
                                <span id="detailParticulars" class="text-gray-700 dark:text-gray-300 text-xs font-normal"></span>
                            </h3>
                            <button onclick="closeObligationDetails()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <!-- Details Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Program</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Account Code</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Description</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-200">Original Obligation</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-200">Adjustment</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-200">Adjusted Obligation</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-200">Purchase Order</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-200">Disbursement</th>
                                    </tr>
                                </thead>
                                <tbody id="detailsTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <!-- Populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
            <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                @if ($perPage != 'all')
                {{ $obligations->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div id="obligationContextMenu" 
        class="fixed hidden w-48 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-400 rounded-lg shadow-2xl z-50 dark:from-blue-900 dark:to-blue-800 dark:border-blue-600"
        style="display: none;">
        <button id="contextView"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 transition-colors duration-150">
            <i class="fas fa-eye mr-2 text-blue-600"></i>View Details
        </button>
        @can('view obligation adjustments')
        <button id="contextAdjustments"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-file-edit mr-2 text-blue-600"></i>Adjustments
        </button>
        @endcan
        @can('view purchase orders')
        <button id="contextPurchaseOrders"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-file-invoice mr-2 text-blue-600"></i>Purchase Order
        </button>
        @endcan
        @can('view disbursement')
        <button id="contextDisbursement"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-file-medical-alt mr-2 text-blue-600"></i>Disbursement
        </button>
        @endcan
        @can('cancel obligations')
        <button id="contextCancellation"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-window-close mr-2 text-blue-600"></i>Cancellation
        </button>
        @endcan
        @hasanyrole('Disbursement|Administrator|Developer')
        <button id="contextPaymentRemarks"
                class="w-full text-left px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-comment-dollar mr-2 text-blue-600"></i>Payment Remarks
        </button>
        @endhasanyrole
        <button id="contextFiles"
                class="w-full text-left block px-4 py-2 text-xs text-green-900 hover:bg-green-200 dark:text-green-100 dark:hover:bg-green-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-file-upload mr-2 text-green-600"></i>Files
        </button>
        <button id="contextHistory"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-history mr-2 text-blue-600"></i>Status/History
        </button>
        @can('edit obligations')
        <button id="contextEdit"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-edit mr-2 text-blue-600"></i>Edit
        </button>
        @endcan
        @can('delete obligations')
        <button id="contextDelete"
                class="w-full text-left px-4 py-2 text-xs text-red-700 hover:bg-red-200 dark:text-red-300 dark:hover:bg-red-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-trash mr-2 text-red-600"></i>Delete
        </button>
        @endcan
    </div>
    

    <div id="createPOModalContainer"></div>
    <div id="createObligationAdjustmentModalContainer"></div>
    <div id="createDisbursementModalContainer"></div>

    <!-- Obligation History Modal -->
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

        .animate-scaleInUp {
            animation: scaleInUp 0.3s ease-out;
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

    <div id="obligationHistoryModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal header -->
            <div class="flex justify-between items-center px-4 py-4 bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-900 dark:to-slate-900 border-b-2 border-gray-200 dark:border-gray-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-history text-gray-600 dark:text-gray-300 text-xl"></i>
                    <div>
                        <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
                            Obligation Status/History
                        </h3>
                        <span id="historyObligationInfo" class="text-xs text-gray-600 dark:text-gray-400"></span>
                    </div>
                </div>
                <button type="button" onclick="closeObligationHistoryModal()" class="text-gray-600 dark:text-gray-300 hover:text-white hover:bg-gray-600 dark:hover:bg-gray-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body (scrollable) -->
            <div id="historyContent" class="overflow-y-auto flex-1 max-h-[calc(90vh-240px)] p-4 space-y-3">
                <div class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-500"></div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex justify-end gap-3 p-4 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                <button type="button" onclick="closeObligationHistoryModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

<script>

    function showLoadingOverlay() {
        let overlay = document.getElementById('pageLoadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'pageLoadingOverlay';
            overlay.className = 'fixed inset-0 bg-black bg-opacity-30 z-[10005] flex items-center justify-center';
            overlay.innerHTML = '<div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-white"></div>';
            document.body.appendChild(overlay);
        }
    }
    document.getElementById('filterForm').addEventListener('submit', showLoadingOverlay);
    document.getElementById('searchForm').addEventListener('submit', showLoadingOverlay);
    document.querySelectorAll('#filterForm select').forEach(el => el.addEventListener('change', showLoadingOverlay));
    document.querySelectorAll('.pagination a').forEach(el => el.addEventListener('click', showLoadingOverlay));

    // Date Range Validation: Set minimum to date based on from date
    const fromDateInput = document.getElementById('fromDate');
    const toDateInput = document.getElementById('toDate');

    if (fromDateInput && toDateInput) {
        fromDateInput.addEventListener('change', function() {
            if (this.value) {
                toDateInput.min = this.value;
                // If the current to_date is before the new from_date, clear it
                if (toDateInput.value && toDateInput.value < this.value) {
                    toDateInput.value = '';
                }
            } else {
                toDateInput.min = '';
            }
        });
    }

    const menu = document.getElementById('obligationContextMenu');
    let scrollTimeout;

    (function() {

    // Function to handle scroll events with debouncing
    function handleScroll() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(hideObligationContextMenu, 150);
    }

    // showContextMenu receives the mouse event and the row element
    window.showObligationContextMenu = function(event, row) {
        event.preventDefault();
        event.stopPropagation();

        if (!menu) return;

        // Remove highlight from previously selected row
        document.querySelectorAll('table tbody tr.context-menu-active').forEach(r => {
            r.classList.remove('context-menu-active');
        });
        
        // Highlight the current row
        row.classList.add('context-menu-active');
        window.currentObligationContextMenuRow = row;

        // Get element positions
        const menuHeight = 400; // Approximate menu height
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
            top = mouseY + scrollTop - menuHeight + 120;
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
        menu.style.position = 'fixed';
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
        menu.style.display = 'block';
        menu.classList.remove('hidden');

        // Get obligation data and set up menu items
        const obligation = row.dataset.obligation ? JSON.parse(row.dataset.obligation) : null;
        if (obligation) {
            // View button
            const viewBtn = menu.querySelector('#contextView');
            if (viewBtn) {
                viewBtn.onclick = () => {
                    hideObligationContextMenu();
                    openModal(obligation.id);
                };
            }

            // Adjustments button
            const adjustBtn = menu.querySelector('#contextAdjustments');
            if (adjustBtn && obligation.id) {
                adjustBtn.onclick = () => {
                    hideObligationContextMenu();
                    window.location.href = `/obligation_adjustments?obligation_id=${obligation.id}`;
                };
            }

            // Purchase Orders button
            const poBtn = menu.querySelector('#contextPurchaseOrders');
            if (poBtn && obligation.id) {
                // Only show for Purchase Request type
                if (obligation.obr_type === 'Purchase Request') {
                    poBtn.style.display = 'block';
                    poBtn.onclick = () => {
                        hideObligationContextMenu();
                        window.location.href = `/purchase_orders?obligation_id=${obligation.id}`;
                    };
                } else {
                    poBtn.style.display = 'none';
                }
            }

            // Disbursement button
            const disbursementBtn = menu.querySelector('#contextDisbursement');
            if (disbursementBtn && obligation.id) {
                    disbursementBtn.style.display = 'block';
                    disbursementBtn.onclick = () => {
                        hideObligationContextMenu();
                        window.location.href = `/disbursements?obligation_id=${obligation.id}`;
                    };
            }

            // Edit button
            const editBtn = menu.querySelector('#contextEdit');
            if (editBtn) {
                editBtn.onclick = () => {
                    hideObligationContextMenu();
                    openEditObligationsModal(obligation);
                };
            }

            // Payment Remarks button
            const paymentRemarksBtn = menu.querySelector('#contextPaymentRemarks');
            if (paymentRemarksBtn && obligation.id) {
                paymentRemarksBtn.onclick = () => {
                    hideObligationContextMenu();
                    openPaymentRemarksModal(
                        obligation.id,
                        obligation.obr_no,
                        obligation.payment_remarks || ''
                    );
                };
            }

            // Files button
            const filesBtn = menu.querySelector('#contextFiles');
            if (filesBtn && obligation.id) {
                filesBtn.onclick = () => {
                    hideObligationContextMenu();
                    openObligationFilesModal(obligation.id, obligation.obr_no);
                };
            }

            // Delete button
            const deleteBtn = menu.querySelector('#contextDelete');
            if (deleteBtn) {
                deleteBtn.onclick = () => {
                    hideObligationContextMenu();
                    openDeleteModal(
                        row.dataset.obligationId,
                        row.dataset.obligationObr,
                        row.dataset.obligationOffice,
                        row.dataset.obligationClass,
                        row.dataset.obligationAmount
                    );
                };
            }

            // Cancellation button
            const cancelBtn = menu.querySelector('#contextCancellation');
            if (cancelBtn) {
                cancelBtn.onclick = () => {
                    hideObligationContextMenu();
                    openCancellationModal(obligation.id, obligation);
                };
            }

            // Status/History button
            const historyBtn = menu.querySelector('#contextHistory');
            if (historyBtn) {
                historyBtn.onclick = () => {
                    hideObligationContextMenu();
                    openObligationHistoryModal(obligation);
                };
            }
        }

        // Add event listeners with delay
        setTimeout(() => {
            document.addEventListener('click', hideObligationContextMenu);
            window.addEventListener('resize', hideObligationContextMenu);
            window.addEventListener('scroll', hideObligationContextMenu, { passive: true });
            
            // Add scroll listener to container
            container.addEventListener('scroll', hideObligationContextMenu, { passive: true });
        }, 30);
    };

    function hideObligationContextMenu() {
        if (!menu) return;
        menu.classList.add('hidden');
        menu.style.display = 'none';
        
        // Remove highlight when menu is closed
        if (window.currentObligationContextMenuRow) {
            window.currentObligationContextMenuRow.classList.remove('context-menu-active');
            window.currentObligationContextMenuRow = null;
        }
        
        // Clear any existing scroll timeout
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        
        // Clean up event listeners
        document.removeEventListener('click', hideObligationContextMenu);
        window.removeEventListener('resize', hideObligationContextMenu);
        window.removeEventListener('scroll', handleScroll);
        
        // Clean up horizontal scroll container listener
        const container = document.querySelector('.overflow-x-auto');
        if (container) {
            container.removeEventListener('scroll', handleScroll);
        }

        // Clean up vertical scroll container listener
        const scrollableContainer = document.querySelector('.max-h-\\[720px\\].overflow-y-auto');
        if (scrollableContainer) {
            scrollableContainer.removeEventListener('scroll', handleScroll);
        }
    }

    // Hide on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideObligationContextMenu();
    });

    // Initialize scroll event listeners
    document.addEventListener('DOMContentLoaded', () => {
        // Handle horizontal scroll container
        const container = document.querySelector('.overflow-x-auto');
        if (container) {
            container.addEventListener('scroll', hideObligationContextMenu, { passive: true });
        }

        // Handle vertical scroll container
        const scrollableContainer = document.querySelector('.max-h-\\[720px\\].overflow-y-auto');
        if (scrollableContainer) {
            scrollableContainer.addEventListener('scroll', hideObligationContextMenu, { passive: true });
        }
    });
})();

    /**
     * Update total records count based on visible rows
     */
    function updateTotalRecordsCount() {
        const rows = document.querySelectorAll('#obligationsTable tbody tr');
        let visibleCount = 0;

        rows.forEach(row => {
            if (row.offsetParent !== null && row.dataset.obligationId) {
                visibleCount++;
            }
        });

        const totalRecordsElement = document.getElementById('totalRecordsCount');
        if (totalRecordsElement) {
            totalRecordsElement.textContent = visibleCount;
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

        // Show modal with loading spinner and display flex
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        historyInfo.textContent = ` | ${obligation.obr_no || 'Loading...'}`;
        historyContent.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-500"></div></div>';

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

    // Compute totals for visible rows
    function computeTableTotals() {
        let totalObligation = 0;
        let totalPO = 0;
        let totalDisbursement = 0;
        const table = document.getElementById('obligationsTable');
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            if (row.style.display === 'none') return;
            // Obligation Amount
            let obligationCell = row.querySelector('.obligation-amount');
            let poCell = row.querySelector('.po-amount');
            let disbursementCell = row.querySelector('.dv-amount');
            // Get the value, stripping formatting if needed
            let obligationVal = 0;
            if (obligationCell) {
                let span = obligationCell.querySelector('button');
                let text = span ? span.textContent : obligationCell.textContent;
                text = text.replace(/[^\d.-]/g, '');
                obligationVal = parseFloat(text) || 0;
            }
            let poVal = 0;
            if (poCell) {
                let span = poCell.querySelector('button');
                let text = span ? span.textContent : poCell.textContent;
                text = text.replace(/[^\d.-]/g, '');
                poVal = parseFloat(text) || 0;
            }
            let disbursementVal = 0;
            if (disbursementCell) {
                let span = disbursementCell.querySelector('button');
                let text = span ? span.textContent : disbursementCell.textContent;
                text = text.replace(/[^\d.-]/g, '');
                disbursementVal = parseFloat(text) || 0;
            }
            totalObligation += obligationVal;
            totalPO += poVal;
            totalDisbursement += disbursementVal;
        });
        // Update sticky footer totals only
        const footerObligation = document.getElementById('footerTotalObligationAmount');
        const footerPO = document.getElementById('footerTotalPOAmount');
        const footerDisbursement = document.getElementById('footerTotalDisbursementAmount');
        if (footerObligation) footerObligation.textContent = totalObligation.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        if (footerPO) footerPO.textContent = totalPO.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        if (footerDisbursement) footerDisbursement.textContent = totalDisbursement.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Initialize all event handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize table totals
        computeTableTotals();
        updateTotalRecordsCount();
        
        // Initialize search handling
        document.getElementById('searchInput').addEventListener('input', function() {
            computeTableTotals();
            updateTotalRecordsCount();
        });
        
        // Initialize context menu handling
        document.addEventListener('click', function(e) {
            const contextMenu = document.getElementById('obligationContextMenu');
            if (contextMenu && !contextMenu.contains(e.target) && !e.target.closest('[oncontextmenu]')) {
                contextMenu.style.display = 'none';
                contextMenu.classList.add('hidden');
            }
        });

        // Prevent default context menu on the table
        document.querySelector('.overflow-x-auto').addEventListener('contextmenu', function(e) {
            if (!e.target.closest('[oncontextmenu]')) {
                e.preventDefault();
            }
        });
    });
    // If you have other filters, add their event listeners here to call computeTableTotals

    // If you use pagination or AJAX, call computeTableTotals after table updates
    function filterTable() {
        // Declare variables
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toLowerCase();
        table = document.getElementById("obligationsTable");
        tr = table.getElementsByTagName("tr");

        let firstVisibleRowId = null;

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 1; i < tr.length; i++) {
            tr[i].style.display = "none";
            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        // Store the first visible row ID
                        if (firstVisibleRowId === null) {
                            firstVisibleRowId = tr[i].dataset.obligationId;
                        }
                        break;
                    }
                }
            }
        }

        // Auto-display the details of the first visible row after filtering, or hide if no results
        if (firstVisibleRowId) {
            displayObligationDetails(firstVisibleRowId);
        } else {
            // Hide the panel if no data is found
            closeObligationDetails();
        }

        computeTableTotals();
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
        // Close regular dropdowns
        document.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.add("hidden"));
        
        // Close context menu
        const contextMenu = document.getElementById('obligationContextMenu');
        if (contextMenu) {
            contextMenu.style.display = 'none';
            contextMenu.classList.add('hidden');
        }
    }

    // Close dropdown if click happens outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative.inline-block')) {
            closeAllDropdowns();
        }
    });

    /* Modal Create ObligationAdjustment - FIXED VERSION */
let isSubmittingObligationAdjustment = false;
let isSubmittingDisbursement = false;
let isSubmittingPurchaseOrder = false;
let isSubmittingPaymentRemarks = false;
let isSubmittingCancellation = false;
let isSubmittingCreateObligationAdjustment = false;
let isSubmittingCreatePurchaseOrder = false;
let isSubmittingCreateDisbursement = false;

function openCreateObligationAdjustmentModal(obligationId) {
    closeAllDropdowns();
    isSubmittingObligationAdjustment = false;
    
    // Reset origin flags since we're opening from obligations page
    window.isFromDashboard = false;
    window.isFromAccounts = false;
    
    // Get current query parameters from the URL
    const currentParams = new URLSearchParams(window.location.search);
    fetch(`/obligations/${obligationId}/obligation-adjustment-modal?${currentParams.toString()}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('createObligationAdjustmentModalContainer').innerHTML = html;
            const modal = document.getElementById('createObligationAdjustmentModal');
            
            // Show modal with display flex
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            
            // Prevent form submission on Enter key
            const form = document.getElementById('createObligationAdjustmentForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!isSubmittingObligationAdjustment) {
                        e.preventDefault();
                        validateCreateObligationAdjustmentForm();
                    }
                });
                
                // Also prevent Enter key from submitting
                form.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
}

function closeCreateObligationAdjustmentModal() {
    const modal = document.getElementById('createObligationAdjustmentModal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
}

function validateCreateObligationAdjustmentForm() {
    // Prevent multiple submissions
    if (isSubmittingObligationAdjustment) {
        return false;
    }

    const modal = document.getElementById('createObligationAdjustmentModal');
    
    if (!modal) {
        console.error('Modal not found!');
        return;
    }
    
    const remarks = modal.querySelector('#adjustment_remarks');
    const adjustmentAmounts = modal.querySelectorAll("input[name^='adjusted_amount']");
    const tableMessage = modal.querySelector('#tableMessage');
    const remarksError = modal.querySelector('#remarksError');

    let isValid = true;

    // Clear previous messages
    if (tableMessage) {
        tableMessage.innerText = '';
        tableMessage.classList.add('hidden');
    }
    if (remarksError) {
        remarksError.innerText = '';
    }

    // Validate remarks
    if (!remarks.value.trim()) {
        if (remarksError) {
            remarksError.innerText = 'Remarks are required.';
        }
        isValid = false;
    }

    // Validate at least one obligation amount has a meaningful adjustment
    // Adjusted Amount can be 0 (which creates a negative adjustment), but we need at least one row with a non-zero Adjusted Amount
    let atLeastOneNonZeroAdjustment = false;
    
    adjustmentAmounts.forEach(input => {
        const val = parseFloat(input.value);
        
        // Check if field has a value (not empty/null)
        if (!isNaN(val)) {
            const row = input.closest('tr');
            const obrAmountCell = row.querySelector("td:nth-child(5)");
            const currentObrAmount = parseFloat(obrAmountCell.textContent.replace(/,/g, '')) || 0;
            
            // Check if Adjusted Amount differs from current OBR Amount (creating a real adjustment)
            // Using toFixed(2) to avoid floating-point precision issues
            if (val.toFixed(2) !== currentObrAmount.toFixed(2)) {
                atLeastOneNonZeroAdjustment = true;
            }
        }
    });

    if (!atLeastOneNonZeroAdjustment) {
        if (tableMessage) {
            tableMessage.innerText = 'At least one Adjusted Amount must differ from the current Amount of Obligation.';
            tableMessage.classList.remove('hidden');
        }
        isValid = false;
    } else {
        // Check PO minimum validation for all non-zero adjustments
        let poValidationFailed = false;
        adjustmentAmounts.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val !== 0) {
                const row = input.closest('tr');
                const poAmountCell = row.querySelector('.po-amount-cell');
                if (poAmountCell) {
                    const poAmount = parseFloat(poAmountCell.textContent.replace(/,/g, '')) || 0;
                    // FIX: Use toFixed(2) for comparison
                    if (poAmount > 0 && parseFloat(val.toFixed(2)) < parseFloat(poAmount.toFixed(2))) {
                        poValidationFailed = true;
                    }
                }
            }
        });

        if (poValidationFailed) {
            if (tableMessage) {
                tableMessage.innerText = 'Adjusted amount cannot be less than Purchase Order amount.';
                tableMessage.classList.remove('hidden');
            }
            isValid = false;
        }
    }

    if (isValid) {
        const form = document.getElementById('createObligationAdjustmentForm');
        if (form) {
            // Set flag to allow submission
            isSubmittingObligationAdjustment = true;
            // Submit the form
            form.submit();
        } else {
            console.error('Form not found for submission!');
        }
    } else {
        console.error('Form validation failed');
    }
}

// Function to compute adjustment amount for each row
function computeAdjustmentAmountForRow(row) {
    const obrAmountCell = row.querySelector("td:nth-child(5)");
    const adjustedAmountInput = row.querySelector("input[name^='adjusted_amount']");
    const adjustmentAmountCell = row.querySelector("td:nth-child(7)");

    if (obrAmountCell && adjustedAmountInput && adjustmentAmountCell) {
        const obrAmount = parseFloat(obrAmountCell.textContent.replace(/,/g, '')) || 0;
        const adjustedAmount = parseFloat(adjustedAmountInput.value.replace(/,/g, '')) || 0;
        const adjustmentAmount = adjustedAmount - obrAmount;

        adjustmentAmountCell.textContent = adjustmentAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}

function validateAmountAdjustment(inputElement) {
    const row = inputElement.closest('tr');
    const poAmountCell = row.querySelector('.po-amount-cell');
    const errorSpan = row.querySelector('.adjustmentAmountError');

    let minAllowed = 0;
    let poAmount = 0;

    // Check if PO amount exists and is > 0 - sets minimum constraint (only for non-zero adjusted amounts)
    if (poAmountCell) {
        poAmount = parseFloat(poAmountCell.textContent.replace(/,/g, '')) || 0;
        if (poAmount > 0) {
            minAllowed = poAmount;
        }
    }

    const currentValue = parseFloat(inputElement.value);
    
    // Allow empty/null values (no adjustment for this row)
    if (isNaN(currentValue)) {
        inputElement.classList.remove('border-red-500');
        if (errorSpan) {
            errorSpan.innerText = '';
            errorSpan.classList.add('hidden');
        }
        return;
    }

    // For non-zero values, check PO minimum constraint
    if (currentValue !== 0 && currentValue < minAllowed) {
        inputElement.classList.add('border-red-500');
        if (errorSpan && minAllowed > 0) {
            errorSpan.innerText = `Adjusted amount must be at least ${minAllowed.toFixed(2)} (Purchase Order amount)`;
            errorSpan.classList.remove('hidden');
        }
        return;
    }

    // Clear error if valid
    inputElement.classList.remove('border-red-500');
    if (errorSpan) {
        errorSpan.innerText = '';
        errorSpan.classList.add('hidden');
    }
}

function updateAdjustedAmountTotal() {
    const adjustedInputs = document.querySelectorAll("input[name^='adjusted_amount']");
    let total = 0;
    adjustedInputs.forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) {
            total += val;
        }
    });
    const totalCell = document.getElementById('adjustedAmountTotalCell');
    if (totalCell) {
        totalCell.textContent = total.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('adjusted_amount')) {
            const row = event.target.closest('tr');
            if (row) {
                computeAdjustmentAmountForRow(row);
                updateAdjustedAmountTotal();
            }
        }
    });

    // Initial update on modal open
    document.addEventListener('DOMContentLoaded', function() {
        updateAdjustedAmountTotal();
        document.querySelectorAll("input[name^='adjusted_amount']").forEach(input => {
            input.addEventListener('input', updateAdjustedAmountTotal);
        });
    });


    /* Modal Create PurchaseOrder */
function openCreatePOModal(obligationId) {
    closeAllDropdowns();
    isSubmittingPurchaseOrder = false;

    // Get current query parameters from the URL
    const currentParams = new URLSearchParams(window.location.search);
    fetch(`/obligations/${obligationId}/purchase-order-modal?${currentParams.toString()}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('createPOModalContainer').innerHTML = html;
            // Show the modal after content is loaded
            const modal = document.getElementById('createPOModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        });
}

    function closeCreatePOModal() {
        const modal = document.getElementById('createPOModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    function validateAmountPO(inputElement) {
        const maxBalance = parseFloat(inputElement.dataset.balance || "0");
        const inputValue = parseFloat(inputElement.value || "0");

        if (inputValue > maxBalance) {
            inputElement.value = maxBalance.toFixed(2);
            inputElement.title = `Max allowed is ₱${maxBalance.toFixed(2)}`;
        }
        updatePOAmountTotal();
    }

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

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('po_amount')) {
            updatePOAmountTotal();
        }
    });

    function validateFormCreatePO() {
        // Prevent multiple submissions
        if (isSubmittingPurchaseOrder) {
            return false;
        }

        const po_remarks = document.getElementById('po_remarks');
        const po_number = document.getElementById('po_number');
        const pr_no = document.getElementById('pr_no');
        const delivery_period = document.getElementById('delivery_period');
        const supplier = document.getElementById('supplier');
        const adjustmentAmounts = document.querySelectorAll("input[name^='adjusted_amount']");
        const poInputs = document.querySelectorAll("input[name^='po_amount']");

        let atLeastOnePOFilled = false;
        let isValid = true;

        // Validate PO Number
        const poNum = po_number.value.trim();
        if (!poNum) {
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

        // If validations pass so far, check if PO number already exists for this obligation
        if (isValid && poNum !== '') {
            // Get obligationId from the form's hidden input
            const obligationId = document.getElementById('CreatePurchaseOrderForm')?.querySelector('input[name="obligation_id"]')?.value;
            
            if (!obligationId) {
                console.error('Obligation ID not found');
                isSubmittingPurchaseOrder = false;
                const errorElement = document.getElementById('po_numberError');
                errorElement.innerText = 'Error: Obligation not found.';
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            
            // Set flag to prevent multiple submissions
            isSubmittingPurchaseOrder = true;
            
            // Fetch the year from the obligation's office allotment class
            fetch(`/api/obligations/${obligationId}/year`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Year fetch failed: ${response.status}`);
                    }
                    return response.json();
                })
                .then(yearData => {
                    // Make AJAX call to check PO uniqueness
                    return fetch('{{ route("purchase_orders.checkPoNumber") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        body: JSON.stringify({
                            po_number: poNum,
                            year: yearData.year
                        })
                    });
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`PO check failed: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.exists) {
                        const errorElement = document.getElementById('po_numberError');
                        errorElement.innerHTML = data.message;
                        // Reset flag since we're not submitting
                        isSubmittingPurchaseOrder = false;
                        // Scroll error into view and focus on the field
                        errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        document.getElementById('po_number').focus();
                        return;
                    }

                    // All validations passed, submit the form
                    document.getElementById('CreatePurchaseOrderForm').submit();
                })
                .catch(error => {
                    console.error('Error checking PO number:', error);
                    console.error('Error details:', error.message);
                    // Reset flag and show error
                    isSubmittingPurchaseOrder = false;
                    const errorElement = document.getElementById('po_numberError');
                    errorElement.innerText = 'Error validating PO number. Please try again.';
                    // Scroll error into view and focus on the field
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    document.getElementById('po_number').focus();
                });
        } else if (isValid) {
            // If all validations pass, submit the form
            isSubmittingPurchaseOrder = true;
            document.getElementById('CreatePurchaseOrderForm').submit();
        }
    }


    /* Modal Create Disbursement */
    function openCreateDisbursementModal(obligationId) {
        closeAllDropdowns();
        isSubmittingDisbursement = false;
        // Get current query parameters from the URL
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.append('from', 'obligation');
        fetch(`/obligations/${obligationId}/disbursement-modal?${currentParams.toString()}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('createDisbursementModalContainer').innerHTML = html;
                const modal = document.getElementById('createDisbursementModal');
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

                // Attach DV number validation listener if function exists
                const dvNoField = modal.querySelector('#dv_no');
                if (dvNoField && typeof validateDvNumberInput !== 'undefined') {
                    dvNoField.addEventListener('blur', validateDvNumberInput);
                    dvNoField.addEventListener('change', validateDvNumberInput);
                }
            });
    }

    function closeCreateDisbursementModal() {
        const modal = document.getElementById('createDisbursementModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
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

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('disbursement_amount')) {
            updateDVAmountTotal();
        }
    });

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
            
            // Look for the obligation_id in the modal's form context
            const modal = document.getElementById('createDisbursementModal');
            const obligationIdInput = modal ? modal.querySelector('input[name="obligation_id"]') : document.querySelector('input[name="obligation_id"]');
            const obligationId = obligationIdInput?.value;
            
            // Set flag to prevent multiple submissions
            isSubmittingDisbursement = true;
            
            // Check if obligationId is available
            if (!obligationId) {
                isSubmittingDisbursement = false;
                if (dvNoError) dvNoError.innerText = 'Error: Obligation ID not found. Please close and reopen the modal.';
                console.warn('Obligation ID input not found. Modal HTML:', modal?.innerHTML);
                return false;
            }
            
            // Fetch the year from the obligation's office allotment class
            fetch(`/api/obligations/${obligationId}/year`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status}`);
                    }
                    return response.json();
                })
                .then(yearData => {
                    if (!yearData.year) {
                        throw new Error('No year data returned from server');
                    }
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.exists) {
                        if (dvNoError) {
                            dvNoError.innerHTML = data.message;
                            // Scroll error into view and focus on the DV field
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
                    // Reset flag and show error with more details
                    isSubmittingDisbursement = false;
                    if (dvNoError) {
                        dvNoError.innerText = 'Error validating DV number: ' + error.message;
                        // Scroll error into view and focus on the DV field
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

    // Display Obligation Details Panel
    function displayObligationDetails(obligationId) {
        // Remove highlight from any previously highlighted row
        const previouslyHighlighted = document.querySelector('tr.obligation-row-highlighted');
        if (previouslyHighlighted) {
            previouslyHighlighted.classList.remove('obligation-row-highlighted');
        }

        // Add highlight to the current row
        const currentRow = document.querySelector(`tr[data-obligation-id="${obligationId}"]`);
        if (currentRow) {
            currentRow.classList.add('obligation-row-highlighted');
        }

        // Build URL with date range parameters if they exist
        const params = new URLSearchParams(window.location.search);
        let url = `/api/obligations/${obligationId}/details`;
        
        if (params.has('from_date') || params.has('to_date')) {
            const queryParams = new URLSearchParams();
            if (params.has('from_date')) {
                queryParams.append('from_date', params.get('from_date'));
            }
            if (params.has('to_date')) {
                queryParams.append('to_date', params.get('to_date'));
            }
            url += `?${queryParams.toString()}`;
        }

        // Fetch obligation details with amounts
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch obligation details');
                }
                return response.json();
            })
            .then(data => {
                const panel = document.getElementById('obligationDetailsPanel');
                const tbody = document.getElementById('detailsTableBody');
                
                // Clear previous rows
                tbody.innerHTML = '';
                
                // Populate header info
                document.getElementById('detailObrNo').textContent = data.obr_no;
                document.getElementById('detailParticulars').textContent = '| ' + data.particulars;
                
                // Populate details rows
                if (data.obligation_amounts && data.obligation_amounts.length > 0) {
                    data.obligation_amounts.forEach(amount => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700';
                        
                        const originalObligation = parseFloat(amount.amount || 0);
                        const adjustment = parseFloat(amount.adjustment || 0);
                        const adjustedObligation = parseFloat(amount.adjusted_obligation || 0);
                        const poAmount = parseFloat(amount.po_amount || 0);
                        const disbursementAmount = parseFloat(amount.disbursement_amount || 0);
                        
                        // Helper function to format currency or show '-' if zero
                        const formatCurrency = (value) => {
                            return value === 0 ? '-' : value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        };
                        
                        row.innerHTML = `
                            <td class="px-3 py-2 text-xs text-gray-900 dark:text-gray-200">${amount.program || '-'}</td>
                            <td class="px-3 py-2 text-xs text-gray-900 dark:text-gray-200">${amount.account_code || '-'}</td>
                            <td class="px-3 py-2 text-xs text-gray-900 dark:text-gray-200">${amount.description || '-'}</td>
                            <td class="px-3 py-2 text-right text-xs text-gray-900 dark:text-gray-200">${formatCurrency(originalObligation)}</td>
                            <td class="px-3 py-2 text-right text-xs text-gray-900 dark:text-gray-200">${formatCurrency(adjustment)}</td>
                            <td class="px-3 py-2 text-right text-xs text-gray-900 dark:text-gray-200 font-semibold">${formatCurrency(adjustedObligation)}</td>
                            <td class="px-3 py-2 text-right text-xs text-gray-900 dark:text-gray-200">${formatCurrency(poAmount)}</td>
                            <td class="px-3 py-2 text-right text-xs text-gray-900 dark:text-gray-200">${formatCurrency(disbursementAmount)}</td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 italic">No details available</td></tr>';
                }
                
                // Show the panel
                panel.classList.remove('hidden');
                
                // Scroll to the panel
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load obligation details');
            });
    }

    // Close Obligation Details Panel
    function closeObligationDetails() {
        document.getElementById('obligationDetailsPanel').classList.add('hidden');
        
        // Remove highlight from the row when closing panel
        const highlightedRow = document.querySelector('tr.obligation-row-highlighted');
        if (highlightedRow) {
            highlightedRow.classList.remove('obligation-row-highlighted');
        }
    }

    // Add click listeners to obligation rows (left click)
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr[data-obligation-id]');
        rows.forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger on button clicks or double-click
                if (e.target.closest('button') || e.detail === 2) {
                    return;
                }
                const obligationId = this.dataset.obligationId;
                if (obligationId) {
                    displayObligationDetails(obligationId);
                }
            });
        });

        // Auto-select and display the first obligation's details
        if (rows.length > 0) {
            const firstObligationId = rows[0].dataset.obligationId;
            if (firstObligationId) {
                displayObligationDetails(firstObligationId);
            }
        }
    });

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        const toastId = 'toast_' + Date.now();
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        // Determine color and icon based on type
        let bgColor = 'bg-blue-500'; // Default: blue
        let iconClass = 'check-circle';
        
        if (type === 'create' || type === 'success') {
            bgColor = 'bg-green-500'; // Green for success/create
            iconClass = 'check-circle';
        } else if (type === 'edit') {
            bgColor = 'bg-blue-500'; // Blue for edit
            iconClass = 'edit';
        } else if (type === 'delete') {
            bgColor = 'bg-red-500'; // Red for delete
            iconClass = 'trash';
        } else if (type === 'error') {
            bgColor = 'bg-red-500'; // Red for error
            iconClass = 'exclamation-circle';
        }
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-slideInRight`;
        toast.innerHTML = `
            <i class="fas fa-${iconClass}"></i>
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
     * Export filtered obligations data to Excel XLSX
     */
    async function exportObligationsToExcel() {
        const button = document.getElementById('exportObligationsBtn');
        if (button.dataset.loading === 'true') {
            return;
        }
        button.dataset.loading = 'true';

        const icon = document.getElementById('exportObligationsIcon');
        const label = document.getElementById('exportObligationsLabel');

        icon.classList.remove('fa-file-excel');
        icon.classList.add('fa-spinner', 'fa-spin');
        button.classList.add('opacity-60', 'pointer-events-none');

        try {
            const table = document.getElementById('obligationsTable');
            const allRows = Array.from(table.querySelectorAll('tbody tr'))
                .filter(row => row.style.display !== 'none');

            const headers = [];
            table.querySelectorAll('thead th').forEach(th => headers.push(th.textContent.trim()));

            const data = [headers];

            function getAmountValue(td) {
                const amountEl = td.querySelector('button, span');
                const raw = (amountEl ? amountEl.textContent : td.textContent).trim();

                if (/^cancelled$/i.test(raw) || /^n\/a$/i.test(raw)) {
                    return 0;
                }

                const num = parseFloat(raw.replace(/[^\d.-]/g, ''));
                return isNaN(num) ? raw : num;
            }

            function extractRow(row) {
                const rowData = [];
                row.querySelectorAll('td').forEach(td => {
                    if (
                        td.classList.contains('obligation-amount') ||
                        td.classList.contains('po-amount') ||
                        td.classList.contains('dv-amount') ||
                        td.classList.contains('balance')
                    ) {
                        rowData.push(getAmountValue(td));
                    } else {
                        rowData.push(td.textContent.trim());
                    }
                });
                return rowData;
            }

            // Process in chunks, yielding to the browser between each one so the
            // tab stays responsive and repaints the progress label — a plain
            // synchronous loop over thousands of rows would freeze the page for
            // its entire duration with no visual feedback in between.
            const CHUNK_SIZE = 500;
            const total = allRows.length;

            for (let i = 0; i < total; i += CHUNK_SIZE) {
                const chunk = allRows.slice(i, i + CHUNK_SIZE);
                chunk.forEach(row => data.push(extractRow(row)));

                const processed = Math.min(i + CHUNK_SIZE, total);
                const percent = Math.round((processed / total) * 100);
                label.textContent = `Exporting... ${percent}%`;

                // Yield one tick back to the browser before the next chunk
                await new Promise(resolve => setTimeout(resolve, 0));
            }

            label.textContent = 'Generating file...';
            await new Promise(resolve => setTimeout(resolve, 0));

            const ws = XLSX.utils.aoa_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Obligations');

            const colWidths = headers.map(() => 15);
            ws['!cols'] = colWidths.map(w => ({ wch: w }));

            const formattedColumnNames = ['Obligation', 'Disbursement', 'Balance'];
            const numberFormat = '#,##0.00';

            formattedColumnNames.forEach(colName => {
                const colIndex = headers.indexOf(colName);
                if (colIndex === -1) return;

                const colLetter = XLSX.utils.encode_col(colIndex);
                for (let r = 1; r < data.length; r++) {
                    const cellRef = `${colLetter}${r + 1}`;
                    const cell = ws[cellRef];
                    if (cell && typeof cell.v === 'number') {
                        cell.t = 'n';
                        cell.z = numberFormat;
                    }
                }
            });

            const today = new Date().toISOString().split('T')[0];
            XLSX.writeFile(wb, `obligations_${today}.xlsx`);

            showToast('Obligations data exported successfully!', 'success');
        } catch (error) {
            console.error('Export error:', error);
            showToast('Failed to export obligations data.', 'error');
        } finally {
            icon.classList.remove('fa-spinner', 'fa-spin');
            icon.classList.add('fa-file-excel');
            label.textContent = 'Export to Excel';
            button.classList.remove('opacity-60', 'pointer-events-none');
            button.dataset.loading = 'false';
        }
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

    .obligation-row-highlighted {
        background-color: #bfdbfe !important;
    }

    .dark .obligation-row-highlighted {
        background-color: #1e40af !important;
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
</style>
    </div>
</x-app-layout>