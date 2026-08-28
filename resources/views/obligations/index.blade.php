<x-app-layout>
    <!-- Load SheetJS Library for Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
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
                <ol class="list-none p-0 inline-flex flex-wrap items-center space-x-1 rtl:space-x-reverse">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">

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
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mt-2">
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

        @php
            $activeFilterChips = [];

            if (request('office_allotment_class_filter')) {
                $officeClassChip = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_filter'));
                if ($officeClassChip) {
                    $activeFilterChips[] = [
                        'label' => $officeClassChip->offices->office_abbreviation . ' - ' . $officeClassChip->allotmentClass->class,
                        'url' => '?' . http_build_query(request()->except(['office_allotment_class_filter', 'page'])),
                    ];
                }
            }
            if (request('fund_filter')) {
                $activeFilterChips[] = [
                    'label' => 'Fund: ' . request('fund_filter'),
                    'url' => '?' . http_build_query(request()->except(['fund_filter', 'page'])),
                ];
            }
            if (request('obr_type_filter')) {
                $activeFilterChips[] = [
                    'label' => 'Type: ' . request('obr_type_filter'),
                    'url' => '?' . http_build_query(request()->except(['obr_type_filter', 'page'])),
                ];
            }
            if (request('from_date') || request('to_date')) {
                $fromDateChip = request('from_date') ? date('M d, Y', strtotime(request('from_date'))) : 'Start';
                $toDateChip = request('to_date') ? date('M d, Y', strtotime(request('to_date'))) : 'End';
                $activeFilterChips[] = [
                    'label' => "$fromDateChip - $toDateChip",
                    'url' => '?' . http_build_query(request()->except(['from_date', 'to_date', 'page'])),
                ];
            }
            if (request('search')) {
                $searchColumnLabels = [
                    'obr_no' => 'OBR No.',
                    'obr_date' => 'OBR Date',
                    'obr_type' => 'OBR Type',
                    'particulars' => 'Particulars',
                    'office_abbreviation' => 'Office',
                    'allotment_class' => 'Allotment Class',
                    'processed_by' => 'Processed By',
                    'remarks' => 'Remarks',
                ];
                $searchChipLabel = 'Search: "' . request('search') . '"';
                if (request('search_column') && isset($searchColumnLabels[request('search_column')])) {
                    $searchChipLabel .= ' in ' . $searchColumnLabels[request('search_column')];
                }
                $activeFilterChips[] = [
                    'label' => $searchChipLabel,
                    'url' => '?' . http_build_query(request()->except(['search', 'search_column', 'page'])),
                    'isClientSide' => true,
                ];
            }
        @endphp

        @if (count($activeFilterChips) > 0)
            <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Active filters:</span>
                @foreach ($activeFilterChips as $chip)
                    <span class="inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700">
                        {{ $chip['label'] }}
                        @if (!empty($chip['isClientSide']))
                            <button type="button" onclick="clearObligationSearchFilter()" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Clear">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        @else
                            <a href="{{ $chip['url'] }}" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Remove filter">
                                <i class="fas fa-times text-[10px]"></i>
                            </a>
                        @endif
                    </span>
                @endforeach
                <a href="{{ route('obligations.index') }}" class="text-xs text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 underline ml-1">
                    Clear all
                </a>
            </div>
        @endif
    </div>

    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">

            @can('create obligations')
            <div class="mb-4">
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Create Obligation') }}
                </button>
            </div>
            @endcan

            <!-- Sort pills (left) and Export / Search / Total Records (right), all inline -->
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mr-1">Sort by:</span>
                    @php
                        $sortPill = function ($key, $label) use ($sortBy, $sortOrder) {
                            $isActive = $sortBy == $key;
                            $nextOrder = $isActive && $sortOrder == 'asc' ? 'desc' : 'asc';
                            $url = route('obligations.index', array_merge(request()->except('page'), ['sort_by' => $key, 'sort_order' => $nextOrder]));
                            $classes = $isActive
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600';
                            $icon = $isActive ? '<i class="fas fa-arrow-' . ($sortOrder == 'asc' ? 'up' : 'down') . ' text-[10px]"></i>' : '';
                            return '<a href="' . $url . '" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition-colors ' . $classes . '">' . e($label) . ' ' . $icon . '</a>';
                        };
                    @endphp
                    {!! $sortPill('office_allotment_class', 'Office & Class') !!}
                    {!! $sortPill('obr_no', 'OBR No.') !!}
                    {!! $sortPill('obr_date', 'OBR Date') !!}
                    {!! $sortPill('obr_type', 'OBR Type') !!}
                    {!! $sortPill('particulars', 'Particulars') !!}
                    {!! $sortPill('obr_amount', 'Obligation') !!}
                    {!! $sortPill('po_amount', 'Purchase Order') !!}
                    @hasanyrole('Obligation|Administrator|Developer')
                        {!! $sortPill('remarks', 'Remarks') !!}
                    @endhasanyrole
                    @hasanyrole('Disbursement|Administrator|Developer|Obligation')
                        {!! $sortPill('dv_amount', 'Disbursement') !!}
                        {!! $sortPill('balance', 'Balance') !!}
                        {!! $sortPill('payment_remarks', 'Payment Remarks') !!}
                    @endhasanyrole
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Search Section -->
                    <form id="searchForm" method="GET" action="{{ route('obligations.index') }}" class="flex flex-wrap items-center gap-2">
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

                        <x-form.select name="search_column" id="searchColumn" class="border border-gray-300 rounded-lg px-3 py-2 text-xs w-36 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
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
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for obligations" class="border border-gray-300 rounded-lg px-3 py-2 text-xs w-48 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                        <button type="submit" class="text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <!-- Export Button -->
                    <button type="button" id="exportObligationsBtn" onclick="exportObligationsToExcel()" class="text-green-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-colors whitespace-nowrap">
                        <i id="exportObligationsIcon" class="fas fa-file-excel mr-1"></i>
                        <span id="exportObligationsLabel">Export to Excel</span>
                    </button>

                    <!-- Total Records -->
                    <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 whitespace-nowrap">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $totalRecords }}</span>
                    </div>
                </div>
            </div>

            @php
                // Precompute derived values once per obligation so both the
                // card view and the table view below can reuse them without
                // duplicating the same computation logic in two loops.
                $obligationComputed = [];
                foreach ($obligations as $obligation) {
                    $officeAbbr = $obligation->officeAllotmentClass->offices->office_abbreviation ?? '-';
                    $allotmentClass = $obligation->officeAllotmentClass->allotmentClass->class ?? '-';
                    $poAmount = $obligation->purchaseOrders->sum('po_amount');
                    $disbursementAmount = $obligation->disbursements->sum('disbursement_amount') ?? 0;
                    $obligationAmount = $obligation->obr_amount ?? 0;
                    $balance = $obligation->obr_amount - $disbursementAmount;
                    $fileCount = $obligation->files()->count();

                    $disbursementAmountStr = number_format((float) (is_numeric($disbursementAmount) ? $disbursementAmount : 0), 2, '.', '');
                    $obligationAmountStr = number_format((float) (is_numeric($obligationAmount) ? $obligationAmount : 0), 2, '.', '');
                    $isEqual = bccomp($disbursementAmountStr, $obligationAmountStr, 2) === 0;
                    $isLower = $disbursementAmount < $obligationAmount && $disbursementAmount > 0;
                    $isOBRZero = $obligationAmount == 0;

                    $searchText = strtolower(collect([
                        $officeAbbr, $allotmentClass, $obligation->obr_no, $obligation->obr_date,
                        $obligation->obr_type, $obligation->particulars, $obligation->remarks,
                        $obligation->payment_remarks,
                    ])->implode(' '));

                    $obligationComputed[$obligation->id] = compact(
                        'officeAbbr', 'allotmentClass', 'poAmount', 'disbursementAmount',
                        'obligationAmount', 'balance', 'fileCount', 'isEqual', 'isLower',
                        'isOBRZero', 'searchText'
                    );
                }
            @endphp

            <!-- View toggle: Card / List -->
            <div class="flex items-center gap-1 mb-3 bg-gray-100 dark:bg-gray-900 rounded-lg p-1 w-fit">
                <button type="button" id="tableViewBtn" onclick="setObligationsView('table')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                    <i class="fas fa-table-list"></i> List View
                </button>
                <button type="button" id="cardViewBtn" onclick="setObligationsView('card')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                    <i class="fas fa-grip"></i> Card View
                </button>
            </div>

            <!-- Shared "no results" message — sits outside both views so it's visible regardless of which is active -->
            <div id="noSearchResultsMsg" class="hidden px-3 py-10 text-center text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-md mb-3 bg-gray-50 dark:bg-gray-900">
                <i class="fas fa-magnifying-glass text-2xl mb-2 block text-gray-300 dark:text-gray-600"></i>
                No obligations match <span id="noSearchResultsQuery" class="font-semibold text-gray-700 dark:text-gray-300"></span>
                <button type="button" onclick="clearObligationSearchFilter()" class="block mx-auto mt-2 text-xs text-blue-600 hover:underline dark:text-blue-400">
                    Clear search
                </button>
            </div>

            <!-- Obligation Cards -->
            <div id="obligationsCardView">
            <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                <div class="max-h-[720px] overflow-y-auto p-2 space-y-3 bg-gray-50 dark:bg-gray-900" id="obligationsContainer">
                    @forelse ($obligations as $obligation)
                        @php
                            $c = $obligationComputed[$obligation->id];
                            $officeAbbr = $c['officeAbbr'];
                            $allotmentClass = $c['allotmentClass'];
                            $poAmount = $c['poAmount'];
                            $disbursementAmount = $c['disbursementAmount'];
                            $obligationAmount = $c['obligationAmount'];
                            $balance = $c['balance'];
                            $fileCount = $c['fileCount'];
                            $isEqual = $c['isEqual'];
                            $isLower = $c['isLower'];
                            $isOBRZero = $c['isOBRZero'];
                            $searchText = $c['searchText'];
                        @endphp
                        <div class="obligation-item obligation-card bg-white dark:bg-gray-800 border border-blue-300 dark:border-blue-700 border-l-4 border-l-blue-500 rounded-lg shadow-sm overflow-hidden text-xs hover:shadow-md transition-shadow cursor-pointer"
                             ondblclick="openModal({{ $obligation->id }})"
                             oncontextmenu="showObligationContextMenu(event, this)"
                             data-obligation='@json($obligation)'
                             data-obligation-id="{{ $obligation->id }}"
                             data-obligation-obr="{{ $obligation->obr_no }}"
                             data-obligation-payment-remarks="{{ $obligation->payment_remarks }}"
                             data-obligation-office="{{ $officeAbbr }}"
                             data-obligation-class="{{ $allotmentClass }}"
                             data-obligation-amount="{{ $obligation->obr_amount }}"
                             data-obr-date="{{ $obligation->obr_date }}"
                             data-obr-type="{{ $obligation->obr_type }}"
                             data-particulars="{{ $obligation->particulars }}"
                             data-remarks-text="{{ $obligation->remarks }}"
                             data-obr-amount="{{ $obligationAmount }}"
                             data-po-amount="{{ $obligation->obr_type === 'Purchase Request' ? $poAmount : 0 }}"
                             data-dv-amount="{{ $disbursementAmount }}"
                             data-balance="{{ $balance }}"
                             data-file-count="{{ $fileCount }}"
                             data-search-text="{{ $searchText }}">

                            <!-- Card Header -->
                            <div class="flex flex-wrap justify-between items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-600">
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white font-bold text-[11px] tracking-wide shadow-sm dark:bg-blue-700">
                                        <i class="fas fa-building text-[10px] opacity-80"></i>{{ $officeAbbr }} - {{ $allotmentClass }}
                                    </span>
                                    <span class="font-bold text-blue-700 dark:text-blue-300">
                                        <i class="fas fa-hashtag mr-1 text-blue-500"></i>{{ $obligation->obr_no }}
                                    </span>
                                    <span class="font-semibold text-gray-600 dark:text-gray-300">
                                        <i class="far fa-calendar mr-1"></i>{{ $obligation->obr_date }}
                                    </span>
                                    <span class="px-2 py-1 rounded font-semibold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                        {{ $obligation->obr_type }}
                                    </span>
                                </div>
                                <button onclick="event.stopPropagation(); openObligationFilesModal({{ $obligation->id }}, '{{ $obligation->obr_no }}')"
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
                            </div>

                            <!-- Card Body -->
                            <div class="px-3 py-3">
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-2 mb-2">
                                    <div class="col-span-2 sm:col-span-4">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Particulars</div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $obligation->particulars }}</div>
                                    </div>
                                </div>

                                <!-- Amounts row -->
                                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 py-2 border-t border-gray-100 dark:border-gray-700">
                                    <div class="obligation-amount">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Obligation</div>
                                        @if ($obligation->obr_amount == 0.00)
                                            <span class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 px-2 py-1 rounded font-bold text-sm">Cancelled</span>
                                        @elseif ($obligation->obligationAdjustments->isNotEmpty())
                                            @unlessrole('Disbursement')
                                                <button onclick="event.stopPropagation(); openCreateObligationAdjustmentModal({{ $obligation->id }})" type="button"
                                                    class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 hover:underline rounded font-bold text-sm tabular-nums">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </button>
                                            @else
                                                <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-bold text-sm tabular-nums">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </span>
                                            @endunlessrole
                                        @else
                                            @unlessrole('Disbursement')
                                                <button onclick="event.stopPropagation(); openCreateObligationAdjustmentModal({{ $obligation->id }})" type="button"
                                                    class="font-bold text-sm tabular-nums text-gray-700 dark:text-gray-300 hover:underline px-2 py-1">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </button>
                                            @else
                                                <span class="font-bold text-sm tabular-nums text-gray-700 dark:text-gray-300 px-2 py-1">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </span>
                                            @endunlessrole
                                        @endif
                                    </div>

                                    <div class="po-amount">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Purchase Order</div>
                                        @if ($obligation->obr_type === 'Purchase Request')
                                            @if ($poAmount > 0)
                                                @unlessrole('Disbursement')
                                                    <button onclick="event.stopPropagation(); openCreatePOModal({{ $obligation->id }})" type="button"
                                                        class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-bold text-sm tabular-nums hover:underline">
                                                        {{ number_format($poAmount, 2) }}
                                                    </button>
                                                @else
                                                    <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-bold text-sm tabular-nums">
                                                        {{ number_format($poAmount, 2) }}
                                                    </span>
                                                @endunlessrole
                                            @else
                                                @unlessrole('Disbursement')
                                                    <button onclick="event.stopPropagation(); openCreatePOModal({{ $obligation->id }})" type="button"
                                                        class="font-bold text-sm tabular-nums text-blue-700 dark:text-blue-400 hover:underline px-2 py-1">
                                                        {{ number_format($poAmount, 2) }}
                                                    </button>
                                                @else
                                                    <span class="font-bold text-sm tabular-nums text-blue-700 dark:text-blue-400 px-2 py-1">
                                                        {{ number_format($poAmount, 2) }}
                                                    </span>
                                                @endunlessrole
                                            @endif
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 px-2 py-1">N/A</span>
                                        @endif
                                    </div>

                                    @hasanyrole('Disbursement|Administrator|Developer|Obligation')
                                        <div class="dv-amount">
                                            <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Disbursement</div>
                                            @if ($obligation->obr_type !== 'Purchase Request')
                                                <button onclick="event.stopPropagation(); openCreateDisbursementModal({{ $obligation->id }})" type="button"
                                                    class="hover:underline px-2 py-1 font-bold text-sm tabular-nums
                                                        @if ($isOBRZero)
                                                            text-gray-700 dark:text-gray-300
                                                        @elseif ($isEqual)
                                                            bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded
                                                        @elseif ($isLower)
                                                            bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 rounded
                                                        @else
                                                            text-gray-700 dark:text-gray-300
                                                        @endif
                                                    ">
                                                    {{ number_format($disbursementAmount, 2) }}
                                                </button>
                                            @else
                                                <span class="text-gray-700 dark:text-gray-300 px-2 py-1 font-bold text-sm tabular-nums">{{ number_format($disbursementAmount, 2) }}</span>
                                            @endif
                                        </div>

                                        <div class="balance">
                                            <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Balance</div>
                                            @if ($obligationAmount == 0)
                                                <span class="text-gray-700 dark:text-gray-300 px-2 py-1 font-bold text-sm tabular-nums">{{ number_format($balance, 2) }}</span>
                                            @elseif ($balance > 0)
                                                <span class="bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-400 font-bold text-sm tabular-nums px-2 py-1 rounded">{{ number_format($balance, 2) }}</span>
                                            @elseif ($balance < 0)
                                                <span class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 font-bold text-sm tabular-nums px-2 py-1 rounded">{{ number_format($balance, 2) }}</span>
                                            @else
                                                <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-bold text-sm tabular-nums px-2 py-1 rounded">{{ number_format($balance, 2) }}</span>
                                            @endif
                                        </div>
                                    @endhasanyrole
                                </div>

                                @hasanyrole('Obligation|Administrator|Developer')
                                    @if($obligation->remarks)
                                        <div class="pt-1 border-t border-gray-100 dark:border-gray-700">
                                            <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Remarks</div>
                                            <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $obligation->remarks }}</div>
                                        </div>
                                    @endif
                                @endhasanyrole

                                @hasanyrole('Disbursement|Administrator|Developer|Obligation')
                                    @if($obligation->payment_remarks)
                                        <div class="pt-1 border-t border-gray-100 dark:border-gray-700 payment-remarks">
                                            <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Payment Remarks</div>
                                            <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $obligation->payment_remarks }}</div>
                                        </div>
                                    @endif
                                @endhasanyrole
                            </div>

                            <!-- Card Footer: Actions (mirrors the right-click context menu, always visible) -->
                            <div class="flex flex-wrap items-center gap-1.5 px-3 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                                <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'view')" type="button" title="View Details"
                                    class="text-blue-600 inline-flex items-center gap-1 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-colors">
                                    <i class="fas fa-eye"></i> View
                                </button>

                                @can('view obligation adjustments')
                                    <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'adjustments')" type="button" title="Adjustments"
                                        class="text-gray-600 inline-flex items-center gap-1 hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-gray-500 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-colors">
                                        <i class="fas fa-file-edit"></i> Adjustments
                                    </button>
                                @endcan

                                @can('view purchase orders')
                                    @if($obligation->obr_type === 'Purchase Request')
                                        <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'purchaseOrders')" type="button" title="Purchase Order"
                                            class="text-purple-600 inline-flex items-center gap-1 hover:text-white border border-purple-600 hover:bg-purple-600 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-purple-500 dark:text-purple-500 dark:hover:text-white dark:hover:bg-purple-600 dark:focus:ring-purple-900 transition-colors">
                                            <i class="fas fa-file-invoice"></i> PO
                                        </button>
                                    @endif
                                @endcan

                                @can('view disbursement')
                                    <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'disbursement')" type="button" title="Disbursement"
                                        class="text-indigo-600 inline-flex items-center gap-1 hover:text-white border border-indigo-600 hover:bg-indigo-600 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-indigo-500 dark:text-indigo-500 dark:hover:text-white dark:hover:bg-indigo-600 dark:focus:ring-indigo-900 transition-colors">
                                        <i class="fas fa-file-medical-alt"></i> Disbursement
                                    </button>
                                @endcan

                                @hasanyrole('Disbursement|Administrator|Developer')
                                    <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'paymentRemarks')" type="button" title="Payment Remarks"
                                        class="text-gray-600 inline-flex items-center gap-1 hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-gray-500 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-colors">
                                        <i class="fas fa-comment-dollar"></i> Payment Remarks
                                    </button>
                                @endhasanyrole

                                <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'history')" type="button" title="Status/History"
                                    class="text-gray-600 inline-flex items-center gap-1 hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-gray-500 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-colors">
                                    <i class="fas fa-history"></i> History
                                </button>

                                @can('cancel obligations')
                                    <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'cancel')" type="button" title="Cancellation"
                                        class="text-orange-600 inline-flex items-center gap-1 hover:text-white border border-orange-600 hover:bg-orange-600 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-orange-500 dark:text-orange-500 dark:hover:text-white dark:hover:bg-orange-600 dark:focus:ring-orange-900 transition-colors">
                                        <i class="fas fa-window-close"></i> Cancel
                                    </button>
                                @endcan

                                <!-- Right-aligned group: Add Adjustment / Add PO / Add Disbursement, Edit, Delete -->
                                <div class="flex flex-wrap items-center gap-1.5 ml-auto">
                                    @unlessrole('Disbursement')
                                        @if($obligation->obr_amount != 0)
                                            <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'addAdjustment')" type="button" title="Add Adjustment"
                                                class="text-teal-600 inline-flex items-center gap-1 hover:text-white border border-teal-600 hover:bg-teal-600 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-teal-500 dark:text-teal-500 dark:hover:text-white dark:hover:bg-teal-600 dark:focus:ring-teal-900 transition-colors">
                                                <i class="fas fa-plus-circle"></i> Add Adjustment
                                            </button>
                                        @endif

                                        @if($obligation->obr_type === 'Purchase Request')
                                            <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'addPurchaseOrder')" type="button" title="Add Purchase Order"
                                                class="text-purple-600 inline-flex items-center gap-1 hover:text-white border border-purple-600 hover:bg-purple-600 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-purple-500 dark:text-purple-500 dark:hover:text-white dark:hover:bg-purple-600 dark:focus:ring-purple-900 transition-colors">
                                                <i class="fas fa-plus-circle"></i> Add PO
                                            </button>
                                        @endif
                                    @endunlessrole

                                    @hasanyrole('Disbursement|Administrator|Developer|Obligation')
                                        @if($obligation->obr_type !== 'Purchase Request')
                                            <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'addDisbursement')" type="button" title="Add Disbursement"
                                                class="text-indigo-600 inline-flex items-center gap-1 hover:text-white border border-indigo-600 hover:bg-indigo-600 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-indigo-500 dark:text-indigo-500 dark:hover:text-white dark:hover:bg-indigo-600 dark:focus:ring-indigo-900 transition-colors">
                                                <i class="fas fa-plus-circle"></i> Add DV
                                            </button>
                                        @endif
                                    @endhasanyrole

                                    @can('edit obligations')
                                        <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'edit')" type="button" title="Edit"
                                            class="text-amber-600 inline-flex items-center gap-1 hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-colors">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    @endcan

                                    @can('delete obligations')
                                        <button onclick="event.stopPropagation(); triggerObligationCardAction(this, 'delete')" type="button" title="Delete"
                                            class="text-red-600 inline-flex items-center gap-1 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-colors">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-3 py-10 text-center text-gray-500 dark:text-gray-400 italic">
                            No Obligations found
                        </div>
                    @endforelse
                </div>

                <!-- Totals Footer -->
                <div id="obligationTableFooter" class="bg-gray-200 dark:bg-gray-900 border-t-2 border-gray-700 dark:border-gray-600 px-3 py-3">
                    <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-sm font-bold text-gray-700 dark:text-gray-200">
                        <div>Total Obligation:
                            <span id="footerTotalObligationAmount" class="text-green-700 dark:text-green-300 tabular-nums ml-1">0.00</span>
                        </div>
                        <div>Total Purchase Order:
                            <span id="footerTotalPOAmount" class="text-blue-700 dark:text-blue-300 tabular-nums ml-1">0.00</span>
                        </div>
                        @hasanyrole('Disbursement|Administrator|Developer')
                        <div>Total Disbursement:
                            <span id="footerTotalDisbursementAmount" class="text-orange-700 dark:text-orange-300 tabular-nums ml-1">0.00</span>
                        </div>
                        @endhasanyrole
                    </div>
                </div>
            </div>
            </div>
            <!-- /#obligationsCardView -->

            <!-- Obligation List (table) View -->
            <div id="obligationsTableView" class="hidden">
                <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <div class="max-h-[720px] overflow-y-auto" id="obligationsTableContainer">
                            <table id="obligationsTable" class="min-w-full text-xs text-center text-gray-600 dark:text-gray-300">
                                <thead class="text-center border-b-2 border-t-2 border-gray-700 text-xs text-gray-700 bg-gray-200 dark:bg-gray-900 dark:text-gray-400 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Office & Class</th>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">OBR No.</th>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">OBR Date</th>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">OBR Type</th>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Particulars</th>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Obligation</th>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Purchase Order</th>
                                        @hasanyrole('Obligation|Administrator|Developer')
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Remarks</th>
                                        @endhasanyrole
                                        @hasanyrole('Disbursement|Administrator|Developer|Obligation')
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Disbursement</th>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Balance</th>
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Payment Remarks</th>
                                        @endhasanyrole
                                        <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Files</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($obligations as $obligation)
                                        @php
                                            $c = $obligationComputed[$obligation->id];
                                            $officeAbbr = $c['officeAbbr'];
                                            $allotmentClass = $c['allotmentClass'];
                                            $poAmount = $c['poAmount'];
                                            $disbursementAmount = $c['disbursementAmount'];
                                            $obligationAmount = $c['obligationAmount'];
                                            $balance = $c['balance'];
                                            $fileCount = $c['fileCount'];
                                            $isEqual = $c['isEqual'];
                                            $isLower = $c['isLower'];
                                            $isOBRZero = $c['isOBRZero'];
                                            $searchText = $c['searchText'];
                                        @endphp
                                        <tr class="obligation-item obligation-row bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                                            ondblclick="openModal({{ $obligation->id }})"
                                            oncontextmenu="showObligationContextMenu(event, this)"
                                            data-obligation='@json($obligation)'
                                            data-obligation-id="{{ $obligation->id }}"
                                            data-obligation-obr="{{ $obligation->obr_no }}"
                                            data-obligation-payment-remarks="{{ $obligation->payment_remarks }}"
                                            data-obligation-office="{{ $officeAbbr }}"
                                            data-obligation-class="{{ $allotmentClass }}"
                                            data-obligation-amount="{{ $obligation->obr_amount }}"
                                            data-obr-date="{{ $obligation->obr_date }}"
                                            data-obr-type="{{ $obligation->obr_type }}"
                                            data-particulars="{{ $obligation->particulars }}"
                                            data-remarks-text="{{ $obligation->remarks }}"
                                            data-obr-amount="{{ $obligationAmount }}"
                                            data-po-amount="{{ $obligation->obr_type === 'Purchase Request' ? $poAmount : 0 }}"
                                            data-dv-amount="{{ $disbursementAmount }}"
                                            data-balance="{{ $balance }}"
                                            data-file-count="{{ $fileCount }}"
                                            data-search-text="{{ $searchText }}">
                                            <td class="font-semibold px-1 py-2">{{ $officeAbbr }} - {{ $allotmentClass }}</td>
                                            <td class="font-semibold text-left px-1 py-2">{{ $obligation->obr_no }}</td>
                                            <td class="text-left px-1 py-2">{{ $obligation->obr_date }}</td>
                                            <td class="text-left px-1 py-2">{{ $obligation->obr_type }}</td>
                                            <td class="text-left px-1 py-2 max-w-sm">{{ $obligation->particulars }}</td>
                                            <td class="px-1 py-2 text-right obligation-amount">
                                                @if ($obligation->obr_amount == 0.00)
                                                    <span class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 px-2 py-1 rounded font-semibold">Cancelled</span>
                                                @elseif ($obligation->obligationAdjustments->isNotEmpty())
                                                    @unlessrole('Disbursement')
                                                        <button onclick="event.stopPropagation(); openCreateObligationAdjustmentModal({{ $obligation->id }})" type="button"
                                                            class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 hover:underline rounded font-semibold">
                                                            {{ number_format($obligation->obr_amount, 2) }}
                                                        </button>
                                                    @else
                                                        <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold">
                                                            {{ number_format($obligation->obr_amount, 2) }}
                                                        </span>
                                                    @endunlessrole
                                                @else
                                                    @unlessrole('Disbursement')
                                                        <button onclick="event.stopPropagation(); openCreateObligationAdjustmentModal({{ $obligation->id }})" type="button"
                                                            class="font-semibold text-gray-700 dark:text-gray-400 hover:underline px-2 py-1">
                                                            {{ number_format($obligation->obr_amount, 2) }}
                                                        </button>
                                                    @else
                                                        <span class="font-semibold text-gray-700 dark:text-gray-400 px-2 py-1">
                                                            {{ number_format($obligation->obr_amount, 2) }}
                                                        </span>
                                                    @endunlessrole
                                                @endif
                                            </td>
                                            <td class="px-1 py-2 text-right po-amount">
                                                @if ($obligation->obr_type === 'Purchase Request')
                                                    @if ($poAmount > 0)
                                                        @unlessrole('Disbursement')
                                                            <button onclick="event.stopPropagation(); openCreatePOModal({{ $obligation->id }})" type="button"
                                                                class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold hover:underline">
                                                                {{ number_format($poAmount, 2) }}
                                                            </button>
                                                        @else
                                                            <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold">
                                                                {{ number_format($poAmount, 2) }}
                                                            </span>
                                                        @endunlessrole
                                                    @else
                                                        @unlessrole('Disbursement')
                                                            <button onclick="event.stopPropagation(); openCreatePOModal({{ $obligation->id }})" type="button"
                                                                class="font-semibold text-blue-700 dark:text-blue-400 hover:underline px-2 py-1">
                                                                {{ number_format($poAmount, 2) }}
                                                            </button>
                                                        @else
                                                            <span class="font-semibold text-blue-700 dark:text-blue-400 px-2 py-1">
                                                                {{ number_format($poAmount, 2) }}
                                                            </span>
                                                        @endunlessrole
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">N/A</span>
                                                @endif
                                            </td>
                                            @hasanyrole('Obligation|Administrator|Developer')
                                            <td class="px-1 py-2 text-center max-w-48">{{ $obligation->remarks ?: '-' }}</td>
                                            @endhasanyrole
                                            @hasanyrole('Disbursement|Administrator|Developer|Obligation')
                                            <td class="px-1 py-2 text-right dv-amount">
                                                @if ($obligation->obr_type !== 'Purchase Request')
                                                    <button onclick="event.stopPropagation(); openCreateDisbursementModal({{ $obligation->id }})" type="button"
                                                        class="hover:underline px-2 py-1
                                                            @if ($isOBRZero)
                                                                font-semibold text-gray-700 dark:text-gray-400
                                                            @elseif ($isEqual)
                                                                bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-semibold
                                                            @elseif ($isLower)
                                                                bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 font-semibold
                                                            @else
                                                                font-semibold text-gray-700 dark:text-gray-400
                                                            @endif
                                                        ">
                                                        {{ number_format($disbursementAmount, 2) }}
                                                    </button>
                                                @else
                                                    <span class="text-gray-700 dark:text-gray-400 px-2 py-1">{{ number_format($disbursementAmount, 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-1 py-2 text-right balance">
                                                @if ($obligationAmount == 0)
                                                    <span class="text-gray-700 dark:text-gray-400 px-2 py-1">{{ number_format($balance, 2) }}</span>
                                                @elseif ($balance > 0)
                                                    <span class="bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-400 font-semibold px-2 py-1">{{ number_format($balance, 2) }}</span>
                                                @elseif ($balance < 0)
                                                    <span class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 font-semibold px-2 py-1">{{ number_format($balance, 2) }}</span>
                                                @else
                                                    <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-semibold px-2 py-1">{{ number_format($balance, 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-1 py-2 text-center max-w-48 payment-remarks">{{ $obligation->payment_remarks ?: '-' }}</td>
                                            @endhasanyrole
                                            <td class="px-1 py-2 text-center">
                                                <button onclick="event.stopPropagation(); openObligationFilesModal({{ $obligation->id }}, '{{ $obligation->obr_no }}')"
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
                                            <td colspan="12" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 italic">
                                                No Obligations found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totals Footer -->
                    <div class="bg-gray-200 dark:bg-gray-900 border-t-2 border-gray-700 dark:border-gray-600 px-3 py-3">
                        <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-sm font-bold text-gray-700 dark:text-gray-200">
                            <div>Total Obligation:
                                <span id="footerTotalObligationAmountTable" class="text-green-700 dark:text-green-300 tabular-nums ml-1">0.00</span>
                            </div>
                            <div>Total Purchase Order:
                                <span id="footerTotalPOAmountTable" class="text-blue-700 dark:text-blue-300 tabular-nums ml-1">0.00</span>
                            </div>
                            @hasanyrole('Disbursement|Administrator|Developer')
                            <div>Total Disbursement:
                                <span id="footerTotalDisbursementAmountTable" class="text-orange-700 dark:text-orange-300 tabular-nums ml-1">0.00</span>
                            </div>
                            @endhasanyrole
                        </div>
                    </div>
                </div>
            </div>
            <!-- /#obligationsTableView -->

            <!-- Obligation Details Panel -->
            <div id="obligationDetailsPanel" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-md px-4 pb-4 pt-2 mt-3">
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

            <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                @if ($perPage != 'all')
                {{ $obligations->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div id="obligationContextMenu" 
        class="fixed hidden w-48 max-w-[calc(100vw-16px)] bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-400 rounded-lg shadow-2xl z-50 dark:from-blue-900 dark:to-blue-800 dark:border-blue-600"
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

        /* Highlight when context menu is open (applies to both card and list/table views) */
        .obligation-item.context-menu-active {
            background-color: rgba(59, 130, 246, 0.1);
            transition: background-color 0.2s ease-in-out;
        }

        .dark .obligation-item.context-menu-active {
            background-color: rgba(59, 130, 246, 0.2);
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

    /**
     * Card / List view toggle
     */
    function getActiveContainer() {
        const cardView = document.getElementById('obligationsCardView');
        return cardView.classList.contains('hidden')
            ? document.getElementById('obligationsTableView')
            : cardView;
    }

    function updateViewToggleButtons(view) {
        const cardBtn = document.getElementById('cardViewBtn');
        const tableBtn = document.getElementById('tableViewBtn');
        const activeClasses = ['bg-white', 'dark:bg-gray-700', 'text-blue-700', 'dark:text-blue-300', 'shadow-sm'];
        const inactiveClasses = ['text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-200'];

        [cardBtn, tableBtn].forEach(btn => btn.classList.remove(...activeClasses, ...inactiveClasses));

        if (view === 'card') {
            cardBtn.classList.add(...activeClasses);
            tableBtn.classList.add(...inactiveClasses);
        } else {
            tableBtn.classList.add(...activeClasses);
            cardBtn.classList.add(...inactiveClasses);
        }
    }

    /**
     * Dispatches a Card View footer action button to the same functions the
     * right-click context menu already uses, so both stay in sync with a
     * single implementation per action.
     */
    window.triggerObligationCardAction = function(button, action) {
        const card = button.closest('.obligation-item');
        if (!card) return;
        const obligation = card.dataset.obligation ? JSON.parse(card.dataset.obligation) : null;
        if (!obligation) return;

        switch (action) {
            case 'view':
                openModal(obligation.id);
                break;
            case 'addAdjustment':
                openCreateObligationAdjustmentModal(obligation.id);
                break;
            case 'addPurchaseOrder':
                openCreatePOModal(obligation.id);
                break;
            case 'addDisbursement':
                openCreateDisbursementModal(obligation.id);
                break;
            case 'edit':
                openEditObligationsModal(obligation);
                break;
            case 'cancel':
                openCancellationModal(obligation.id, obligation);
                break;
            case 'paymentRemarks':
                openPaymentRemarksModal(obligation.id, obligation.obr_no, obligation.payment_remarks || '');
                break;
            case 'history':
                openObligationHistoryModal(obligation);
                break;
            case 'delete':
                openDeleteModal(
                    card.dataset.obligationId,
                    card.dataset.obligationObr,
                    card.dataset.obligationOffice,
                    card.dataset.obligationClass,
                    card.dataset.obligationAmount
                );
                break;
            case 'adjustments':
                window.location.href = `/obligation_adjustments?obligation_id=${obligation.id}`;
                break;
            case 'purchaseOrders':
                window.location.href = `/purchase_orders?obligation_id=${obligation.id}`;
                break;
            case 'disbursement':
                window.location.href = `/disbursements?obligation_id=${obligation.id}`;
                break;
        }
    };

    window.setObligationsView = function(view) {
        const cardView = document.getElementById('obligationsCardView');
        const tableView = document.getElementById('obligationsTableView');

        if (view === 'table') {
            cardView.classList.add('hidden');
            tableView.classList.remove('hidden');
        } else {
            view = 'card';
            tableView.classList.add('hidden');
            cardView.classList.remove('hidden');
        }

        try {
            localStorage.setItem('obligationsView', view);
        } catch (e) {
            // localStorage unavailable (private browsing, etc.) — view just won't persist
        }

        updateViewToggleButtons(view);

        // Recompute totals/record count scoped to whichever view just became active
        computeTableTotals();
        updateTotalRecordsCount();
    };

    document.addEventListener('DOMContentLoaded', function() {
        let savedView = 'table';
        try {
            savedView = localStorage.getItem('obligationsView') || 'table';
        } catch (e) {
            // ignore
        }
        setObligationsView(savedView);
    });

    const menu = document.getElementById('obligationContextMenu');

    (function() {

    // showContextMenu receives the mouse event and the card/row element
    window.showObligationContextMenu = function(event, card) {
        event.preventDefault();
        event.stopPropagation();

        if (!menu) return;

        // Remove highlight from previously selected item (either view)
        document.querySelectorAll('.obligation-item.context-menu-active').forEach(r => {
            r.classList.remove('context-menu-active');
        });
        
        // Highlight the current item
        card.classList.add('context-menu-active');
        window.currentObligationContextMenuRow = card;

        // The menu is position:fixed, so we work in raw viewport coordinates
        // (clientX/clientY) and must NOT add page scroll offsets, or the menu
        // drifts away from the cursor the further the page has been scrolled.
        const menuHeight = 400; // Approximate menu height
        const menuWidth = 192; // w-48 = 12rem = 192px
        const viewportHeight = window.innerHeight;
        const viewportWidth = window.innerWidth;
        const mouseX = event.clientX;
        const mouseY = event.clientY;

        let top;
        const spaceBelow = viewportHeight - mouseY;
        const spaceAbove = mouseY;

        if (spaceBelow > menuHeight + 20) {
            top = mouseY;
        } else if (spaceAbove > menuHeight + 20) {
            top = mouseY - menuHeight + 120;
        } else {
            top = viewportHeight - menuHeight - 8;
        }
        top = Math.min(Math.max(top, 8), viewportHeight - 8);

        let left = mouseX + 2;
        if (left + menuWidth > viewportWidth) {
            left = mouseX - menuWidth - 2;
        }
        if (left < 8) {
            left = 8;
        }

        // Position menu
        menu.style.position = 'fixed';
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
        menu.style.display = 'block';
        menu.classList.remove('hidden');

        // Get obligation data and set up menu items
        const obligation = card.dataset.obligation ? JSON.parse(card.dataset.obligation) : null;
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
                        card.dataset.obligationId,
                        card.dataset.obligationObr,
                        card.dataset.obligationOffice,
                        card.dataset.obligationClass,
                        card.dataset.obligationAmount
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

            // Add scroll listeners to both scrollable containers (card view + table view)
            ['obligationsContainer', 'obligationsTableContainer'].forEach(id => {
                const container = document.getElementById(id);
                if (container) {
                    container.addEventListener('scroll', hideObligationContextMenu, { passive: true });
                }
            });
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
        
        // Clean up event listeners
        document.removeEventListener('click', hideObligationContextMenu);
        window.removeEventListener('resize', hideObligationContextMenu);
        window.removeEventListener('scroll', hideObligationContextMenu);
        
        // Clean up scroll container listeners
        ['obligationsContainer', 'obligationsTableContainer'].forEach(id => {
            const container = document.getElementById(id);
            if (container) {
                container.removeEventListener('scroll', hideObligationContextMenu);
            }
        });
    }

    // Hide on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideObligationContextMenu();
    });

    // Initialize scroll event listeners
    document.addEventListener('DOMContentLoaded', () => {
        ['obligationsContainer', 'obligationsTableContainer'].forEach(id => {
            const container = document.getElementById(id);
            if (container) {
                container.addEventListener('scroll', hideObligationContextMenu, { passive: true });
            }
        });
    });
})();

    /**
     * Update total records count based on visible items in the active view
     */
    function updateTotalRecordsCount() {
        const items = getActiveContainer().querySelectorAll('.obligation-item');
        let visibleCount = 0;

        items.forEach(item => {
            if (item.style.display !== 'none' && item.dataset.obligationId) {
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

    /**
     * Compute totals for the visible items in the active view (reading
     * precomputed numeric data attributes rather than parsing formatted/
     * displayed text), then mirror the result into both views' footer
     * elements so switching views never shows a stale figure.
     */
    function computeTableTotals() {
        let totalObligation = 0;
        let totalPO = 0;
        let totalDisbursement = 0;

        getActiveContainer().querySelectorAll('.obligation-item').forEach(item => {
            if (item.style.display === 'none') return;
            totalObligation += parseFloat(item.dataset.obrAmount) || 0;
            totalPO += parseFloat(item.dataset.poAmount) || 0;
            totalDisbursement += parseFloat(item.dataset.dvAmount) || 0;
        });

        const format = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        ['footerTotalObligationAmount', 'footerTotalObligationAmountTable'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = format(totalObligation);
        });
        ['footerTotalPOAmount', 'footerTotalPOAmountTable'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = format(totalPO);
        });
        ['footerTotalDisbursementAmount', 'footerTotalDisbursementAmountTable'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = format(totalDisbursement);
        });
    }

    // Initialize all event handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize card totals
        computeTableTotals();
        updateTotalRecordsCount();

        // Initialize context menu handling
        document.addEventListener('click', function(e) {
            const contextMenu = document.getElementById('obligationContextMenu');
            if (contextMenu && !contextMenu.contains(e.target) && !e.target.closest('[oncontextmenu]')) {
                contextMenu.style.display = 'none';
                contextMenu.classList.add('hidden');
            }
        });

        // Prevent default browser context menu inside the card list
        const cardsContainer = document.getElementById('obligationsContainer');
        if (cardsContainer) {
            cardsContainer.addEventListener('contextmenu', function(e) {
                if (!e.target.closest('[oncontextmenu]')) {
                    e.preventDefault();
                }
            });
        }
    });

    /**
     * Filter obligation cards/rows based on the search input. Filters items
     * in BOTH views (not just the active one) so that switching views keeps
     * whatever search is currently applied instead of resetting it.
     */
    function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase().trim();
        const items = document.querySelectorAll('.obligation-item');
        let visibleCount = 0;
        let firstVisibleId = null;

        items.forEach(item => {
            const searchText = item.dataset.searchText || item.textContent.toLowerCase();
            if (searchText.includes(filter)) {
                item.style.display = '';
                visibleCount++;
                if (firstVisibleId === null) {
                    firstVisibleId = item.dataset.obligationId;
                }
            } else {
                item.style.display = 'none';
            }
        });

        // Show a "no results" message when a search query matches nothing
        const noResultsMsg = document.getElementById('noSearchResultsMsg');
        const noResultsQuery = document.getElementById('noSearchResultsQuery');
        if (noResultsMsg) {
            if (filter.length > 0 && visibleCount === 0 && items.length > 0) {
                if (noResultsQuery) noResultsQuery.textContent = `"${input.value.trim()}"`;
                noResultsMsg.classList.remove('hidden');
            } else {
                noResultsMsg.classList.add('hidden');
            }
        }

        // Auto-display the details of the first visible item after filtering, or hide if no results
        if (firstVisibleId) {
            displayObligationDetails(firstVisibleId);
        } else {
            closeObligationDetails();
        }

        updateTotalRecordsCount();
        computeTableTotals();
    }

    /**
     * Clear the search box (used by the active-filter chip and the no-results message)
     */
    function clearObligationSearchFilter() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
            filterTable();
            searchInput.focus();
        }
    }

    // Add event listener for input event to filter cards as you type
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
        // Remove highlight from any previously highlighted card
        const previouslyHighlighted = document.querySelector('.obligation-row-highlighted');
        if (previouslyHighlighted) {
            previouslyHighlighted.classList.remove('obligation-row-highlighted');
        }

        // Add highlight to the current card
        const currentCard = document.querySelector(`.obligation-card[data-obligation-id="${obligationId}"]`);
        if (currentCard) {
            currentCard.classList.add('obligation-row-highlighted');
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
        
        // Remove highlight from the card when closing panel
        const highlightedCard = document.querySelector('.obligation-row-highlighted');
        if (highlightedCard) {
            highlightedCard.classList.remove('obligation-row-highlighted');
        }
    }

    // Add click listeners to obligation cards/rows (left click), in both views
    document.addEventListener('DOMContentLoaded', function() {
        const items = document.querySelectorAll('.obligation-item[data-obligation-id]');
        items.forEach(item => {
            item.addEventListener('click', function(e) {
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

        // Auto-select and display the first obligation's details (from whichever view is active)
        const activeItems = getActiveContainer().querySelectorAll('.obligation-item[data-obligation-id]');
        if (activeItems.length > 0) {
            const firstObligationId = activeItems[0].dataset.obligationId;
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
     * Export filtered obligations data to Excel.
     * Reads directly from each visible card's data attributes (numeric
     * amounts already computed server-side) rather than scraping
     * formatted table-cell text — simpler and avoids re-parsing
     * "Cancelled"/"N/A" placeholder strings back into numbers.
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
            const allCards = Array.from(document.querySelectorAll('#obligationsContainer .obligation-card'))
                .filter(card => card.style.display !== 'none');

            const hasDisbursementCols = document.getElementById('footerTotalDisbursementAmount') !== null;

            const headers = ['Office & Class', 'OBR No.', 'OBR Date', 'OBR Type', 'Particulars', 'Obligation', 'Purchase Order', 'Remarks'];
            if (hasDisbursementCols) {
                headers.push('Disbursement', 'Balance', 'Payment Remarks');
            }
            headers.push('Files');

            const data = [headers];

            // Process in chunks, yielding to the browser between each one so the
            // tab stays responsive and repaints the progress label — a plain
            // synchronous loop over thousands of cards would freeze the page for
            // its entire duration with no visual feedback in between.
            const CHUNK_SIZE = 500;
            const total = allCards.length;

            for (let i = 0; i < total; i += CHUNK_SIZE) {
                const chunk = allCards.slice(i, i + CHUNK_SIZE);
                chunk.forEach(card => {
                    const row = [
                        `${card.dataset.obligationOffice} - ${card.dataset.obligationClass}`,
                        card.dataset.obligationObr || '',
                        card.dataset.obrDate || '',
                        card.dataset.obrType || '',
                        card.dataset.particulars || '',
                        parseFloat(card.dataset.obrAmount) || 0,
                        parseFloat(card.dataset.poAmount) || 0,
                        card.dataset.remarksText || '',
                    ];
                    if (hasDisbursementCols) {
                        row.push(
                            parseFloat(card.dataset.dvAmount) || 0,
                            parseFloat(card.dataset.balance) || 0,
                            card.dataset.obligationPaymentRemarks || ''
                        );
                    }
                    row.push(card.dataset.fileCount || '0');
                    data.push(row);
                });

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

            const formattedColumnNames = ['Obligation', 'Purchase Order', 'Disbursement', 'Balance'];
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
        background-color: #dbeafe;
        outline: 2px solid #3b82f6;
        outline-offset: -1px;
    }

    .dark .obligation-row-highlighted {
        background-color: #1e2f4d;
        outline-color: #60a5fa;
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