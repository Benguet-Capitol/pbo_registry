<x-app-layout>
    <!-- Load SheetJS Library for Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">

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
            if (request('search')) {
                $searchColumnLabels = [
                    'dv_no' => 'DV No.',
                    'dv_date' => 'DV Date',
                    'payee' => 'Payee',
                    'address' => 'Address',
                    'dv_remarks' => 'Remarks',
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
                            <button type="button" onclick="clearDisbursementSearchFilter()" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Clear">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        @else
                            <a href="{{ $chip['url'] }}" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Remove filter">
                                <i class="fas fa-times text-[10px]"></i>
                            </a>
                        @endif
                    </span>
                @endforeach
                <a href="{{ route('disbursements.all') }}" class="text-xs text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 underline ml-1">
                    Clear all
                </a>
            </div>
        @endif
    </div>

    <!-- Disbursements -->
     <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-6 dark:bg-gray-800">
        <div class="p-4 sm:p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            @php
                $sortOptions = [
                    'disbursement_date' => 'DV Date',
                    'dv_no' => 'DV No.',
                    'status' => 'Status',
                    'disbursement_amount' => 'DV Amount',
                    'remarks' => 'Remarks',
                ];
            @endphp
            {{-- @can('create purchase orders')
            <div class="mb-4">
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Add Purchase Order') }}
                </button>
            </div>
            @endcan --}}

            <!-- Sort pills (left) and Export / Search / Total Records (right), all inline -->
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mr-1">Sort by:</span>
                    @foreach ($sortOptions as $sortKey => $sortLabel)
                        @php
                            $isActiveSort = $sortBy == $sortKey;
                            $nextOrder = $isActiveSort && $sortOrder == 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="?{{ http_build_query(array_merge(request()->except(['page', 'sort_by', 'sort_order']), ['sort_by' => $sortKey, 'sort_order' => $nextOrder])) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                           {{ $isActiveSort ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                            {{ $sortLabel }}
                            @if ($isActiveSort)
                                <i class="fas fa-arrow-{{ $sortOrder == 'asc' ? 'up' : 'down' }} text-[10px]"></i>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Search Form -->
                    <form id="searchForm" method="GET" action="{{ route('disbursements.all') }}" class="flex flex-wrap items-center gap-2">
                        <!-- Hidden inputs to preserve filters -->
                        <input type="hidden" name="year1" value="{{ $selectedYear }}">
                        <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 'all') }}">
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_order" value="{{ $sortOrder }}">

                        <x-form.select name="search_column" id="searchColumn" class="border border-gray-300 rounded-lg px-3 py-2 text-xs w-36 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Columns</option>
                            <option value="dv_no" {{ request('search_column') == 'dv_no' ? 'selected' : '' }}>DV No.</option>
                            <option value="dv_date" {{ request('search_column') == 'dv_date' ? 'selected' : '' }}>DV Date</option>
                            <option value="payee" {{ request('search_column') == 'payee' ? 'selected' : '' }}>Payee</option>
                            <option value="address" {{ request('search_column') == 'address' ? 'selected' : '' }}>Address</option>
                            <option value="dv_remarks" {{ request('search_column') == 'dv_remarks' ? 'selected' : '' }}>Remarks</option>
                        </x-form.select>
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search disbursements" class="border border-gray-300 rounded-lg px-3 py-2 text-xs w-48 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                        <button type="submit" class="flex-shrink-0 text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <!-- Export Button -->
                    <button type="button" onclick="exportDisbursementsToExcel()" class="text-green-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 whitespace-nowrap" title="Export filtered data to Excel">
                        <i class="fas fa-download mr-1"></i>
                        Export to Excel
                    </button>

                    <!-- Total Records -->
                    <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 whitespace-nowrap">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $totalRecords }}</span>
                    </div>
                </div>
            </div>

            <!-- View toggle: Card / List -->
            <div class="flex items-center gap-1 mb-3 bg-gray-100 dark:bg-gray-900 rounded-lg p-1 w-fit">
                <button type="button" id="dvListViewBtn" onclick="setDisbursementsView('table')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                    <i class="fas fa-table-list"></i> List View
                </button>
                <button type="button" id="dvCardViewBtn" onclick="setDisbursementsView('card')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                    <i class="fas fa-grip"></i> Card View
                </button>
            </div>

            <!-- Shared "no results" message — sits outside both views so it's visible regardless of which is active -->
            <div id="noSearchResultsMsg" class="hidden px-3 py-10 text-center text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-md mb-3 bg-gray-50 dark:bg-gray-900">
                <i class="fas fa-magnifying-glass text-2xl mb-2 block text-gray-300 dark:text-gray-600"></i>
                No disbursements match <span id="noSearchResultsQuery" class="font-semibold text-gray-700 dark:text-gray-300"></span>
                <button type="button" onclick="clearDisbursementSearchFilter()" class="block mx-auto mt-2 text-xs text-blue-600 hover:underline dark:text-blue-400">
                    Clear search
                </button>
            </div>

            @php
                // Precompute derived values once per disbursement so both the
                // card view and the table view below can reuse them.
                $dvComputed = [];
                foreach ($disbursements as $disbursement) {
                    $officeClassLabel = (optional($disbursement->obligation->officeAllotmentClass->offices)->office_abbreviation ?? '-') . ' - ' . (optional($disbursement->obligation->officeAllotmentClass->allotmentClass)->class ?? '-');
                    $program = $disbursement->obligationAmount?->appropriation?->programs ?? '-';
                    $accountCode = $disbursement->obligationAmount?->appropriation?->account_code ?? '-';
                    $description = $disbursement->obligationAmount?->appropriation?->description ?? '-';
                    $dvAmount = (float)($disbursement->disbursement_amount ?? 0);
                    $status = $disbursement->status ?? '';

                    $statusBadge = $status === 'Full Payment'
                        ? ['bg' => 'bg-green-100 dark:bg-green-900', 'text' => 'text-green-700 dark:text-green-300']
                        : ($status === 'Partial Payment'
                            ? ['bg' => 'bg-blue-100 dark:bg-blue-900', 'text' => 'text-blue-700 dark:text-blue-300']
                            : ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-300']);

                    $searchText = strtolower(collect([
                        $officeClassLabel, $disbursement->obligation->obr_no ?? '', $disbursement->obligation->particulars ?? '',
                        $disbursement->dv_no, $disbursement->disbursement_date, $program, $accountCode, $description,
                        $status, $disbursement->remarks,
                    ])->implode(' '));

                    $dvComputed[$disbursement->id] = compact(
                        'officeClassLabel', 'program', 'accountCode', 'description',
                        'dvAmount', 'status', 'statusBadge', 'searchText'
                    );
                }
            @endphp

            <!-- Disbursement Cards -->
            <div id="disbursementsCardView">
            <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                <div class="max-h-[720px] overflow-y-auto p-2 space-y-3 bg-gray-50 dark:bg-gray-900" id="disbursementsContainer">
                    @forelse($disbursements as $disbursement)
                        @php
                            $dc = $dvComputed[$disbursement->id];
                            $officeClassLabel = $dc['officeClassLabel'];
                            $program = $dc['program'];
                            $accountCode = $dc['accountCode'];
                            $description = $dc['description'];
                            $dvAmount = $dc['dvAmount'];
                            $status = $dc['status'];
                            $statusBadge = $dc['statusBadge'];
                            $searchText = $dc['searchText'];
                        @endphp
                        <div class="dv-item dv-card bg-white dark:bg-gray-800 border border-green-300 dark:border-green-700 border-l-4 border-l-green-500 rounded-lg shadow-sm overflow-hidden text-xs hover:shadow-md transition-shadow cursor-pointer"
                             oncontextmenu="showDisbursementContextMenu(event, this)"
                             data-dv-no="{{ $disbursement->dv_no }}"
                             data-dv-date="{{ $disbursement->disbursement_date ?? '-' }}"
                             data-obligation-id="{{ $disbursement->obligation_id }}"
                             data-dv-amount="{{ $dvAmount }}"
                             data-office-class="{{ $officeClassLabel }}"
                             data-obr-no="{{ $disbursement->obligation->obr_no ?? '-' }}"
                             data-particulars="{{ $disbursement->obligation->particulars ?? '-' }}"
                             data-program="{{ $program }}"
                             data-account-code="{{ $accountCode }}"
                             data-description="{{ $description }}"
                             data-status="{{ $status }}"
                             data-remarks="{{ $disbursement->remarks ?? '-' }}"
                             data-search-text="{{ $searchText }}">

                            <!-- Card Header -->
                            <div class="flex flex-wrap justify-between items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-600">
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-600 text-white font-bold text-[11px] tracking-wide shadow-sm dark:bg-green-700">
                                        <i class="fas fa-building text-[10px] opacity-80"></i>{{ $officeClassLabel }}
                                    </span>
                                    <span class="font-bold text-green-700 dark:text-green-300">
                                        <i class="fas fa-hashtag mr-1 text-green-500"></i>{{ $disbursement->dv_no }}
                                    </span>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">
                                        <i class="far fa-calendar mr-1"></i>{{ $disbursement->disbursement_date ?? '-' }}
                                    </span>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">
                                        <span class="font-semibold">OBR:</span> {{ $disbursement->obligation->obr_no ?? '-' }}
                                    </span>
                                </div>
                                <div>
                                <span class="px-2 py-1 rounded font-semibold {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }}">
                                    {{ $status ? ucfirst($status) : '-' }}
                                </span>
                                <button type="button" onclick="event.stopPropagation(); displayObligationDetailsModal({{ $disbursement->obligation_id }})"
                                    class="text-blue-600 inline-flex leading-4 tracking-wider items-center ml-1 gap-1 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                                    <i class="fas fa-eye"></i><span>View</span>
                                </button>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="px-3 py-3 grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-2">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Account Code</div>
                                    <div class="font-medium text-gray-700 dark:text-gray-200 break-words">{{ $accountCode }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">DV / Check Amount</div>
                                    <div class="font-bold text-sm tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($dvAmount, 2) }}</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Program</div>
                                    <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $program }}</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Description</div>
                                    <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $description }}</div>
                                </div>
                                <div class="col-span-2 sm:col-span-4">
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Particulars</div>
                                    <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $disbursement->obligation->particulars ?? '-' }}</div>
                                </div>
                                @if($disbursement->remarks)
                                    <div class="col-span-2 sm:col-span-4 pt-1 border-t border-gray-100 dark:border-gray-700">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Remarks</div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $disbursement->remarks }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-file-circle-question text-2xl mb-2 block text-gray-300 dark:text-gray-600"></i>
                            No disbursements found.
                            @if(count($activeFilterChips) > 0)
                                <a href="{{ route('disbursements.all') }}" class="block mt-2 text-xs text-blue-600 hover:underline dark:text-blue-400">
                                    Clear filters and try again
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <!-- Totals Footer -->
                <div id="disbursementsFooter" class="bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-gray-700 dark:border-gray-600 text-center text-sm px-3 py-3">
                    Total DV / Check Amount:
                    <span id="totalDVAmountFooter" class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-bold text-base tabular-nums ml-2">
                        0.00
                    </span>
                </div>
            </div>
            </div>
            <!-- /#disbursementsCardView -->

            <!-- List View (flat table) -->
            <div id="disbursementsTableView" class="hidden">
                <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <div class="max-h-[720px] overflow-y-auto" id="disbursementsTableContainer">
                            <table class="min-w-full text-xs text-center text-gray-600 dark:text-gray-300">
                                <thead class="text-center border-b-2 border-t-2 border-gray-700 text-xs text-gray-700 bg-gray-200 dark:bg-gray-900 dark:text-gray-400 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">DV No.</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">DV Date</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">OBR No.</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Office & Class</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Status</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Account Code</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Program</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Description</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">DV Amount</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($disbursements as $disbursement)
                                        @php
                                            $dc = $dvComputed[$disbursement->id];
                                        @endphp
                                        <tr class="dv-item dv-row bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                                            oncontextmenu="showDisbursementContextMenu(event, this)"
                                            data-dv-no="{{ $disbursement->dv_no }}"
                                            data-dv-date="{{ $disbursement->disbursement_date ?? '-' }}"
                                            data-obligation-id="{{ $disbursement->obligation_id }}"
                                            data-dv-amount="{{ $dc['dvAmount'] }}"
                                            data-office-class="{{ $dc['officeClassLabel'] }}"
                                            data-obr-no="{{ $disbursement->obligation->obr_no ?? '-' }}"
                                            data-particulars="{{ $disbursement->obligation->particulars ?? '-' }}"
                                            data-program="{{ $dc['program'] }}"
                                            data-account-code="{{ $dc['accountCode'] }}"
                                            data-description="{{ $dc['description'] }}"
                                            data-status="{{ $dc['status'] }}"
                                            data-remarks="{{ $disbursement->remarks ?? '-' }}"
                                            data-search-text="{{ $dc['searchText'] }}">
                                            <td class="font-bold text-green-700 dark:text-green-300 px-2 py-2">{{ $disbursement->dv_no }}</td>
                                            <td class="px-2 py-2">{{ $disbursement->disbursement_date ?? '-' }}</td>
                                            <td class="px-2 py-2">{{ $disbursement->obligation->obr_no ?? '-' }}</td>
                                            <td class="text-left px-2 py-2">{{ $dc['officeClassLabel'] }}</td>
                                            <td class="px-2 py-2">
                                                <span class="px-2 py-1 rounded font-semibold {{ $dc['statusBadge']['bg'] }} {{ $dc['statusBadge']['text'] }}">{{ $dc['status'] ? ucfirst($dc['status']) : '-' }}</span>
                                            </td>
                                            <td class="font-semibold px-2 py-2">{{ $dc['accountCode'] }}</td>
                                            <td class="text-left px-2 py-2 max-w-xs">{{ $dc['program'] }}</td>
                                            <td class="text-left px-2 py-2 max-w-xs">{{ $dc['description'] }}</td>
                                            <td class="px-2 py-2 text-right font-bold tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($dc['dvAmount'], 2) }}</td>
                                            <td class="text-left px-2 py-2 max-w-xs">{{ $disbursement->remarks ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">No disbursements found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totals Footer -->
                    <div class="bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-gray-700 dark:border-gray-600 text-center text-sm px-3 py-3">
                        Total DV / Check Amount:
                        <span id="totalDVAmountFooterTable" class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-bold text-base tabular-nums ml-2">0.00</span>
                    </div>
                </div>
            </div>
            <!-- /#disbursementsTableView -->
        </div>
    </div>

    <!-- Include Modal Files -->
    @include('obligations.modal.obligation_details')
    <div id="disbursementContextMenu" 
        class="fixed hidden w-48 max-w-[calc(100vw-16px)] bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-400 rounded-lg shadow-2xl z-50 dark:from-blue-900 dark:to-blue-800 dark:border-blue-600"
        style="display: none;">
        @role('Developer|Administrator|Obligation|Disbursement')
        <button id="contextViewObligation"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-eye mr-2 text-blue-600"></i>View Obligation Details
        </button>
        @endrole
    </div>

<script>
    /**
     * Recompute the Total DV / Check Amount footer from each visible
     * item's data-dv-amount attribute, for whichever view is active,
     * mirrored into both views' footer elements.
     */
    function updateDisbursementFooterTotal() {
        const isTableView = !document.getElementById('disbursementsTableView').classList.contains('hidden');
        const items = isTableView
            ? document.querySelectorAll('.dv-row')
            : document.querySelectorAll('.dv-card');

        let totalDV = 0;
        items.forEach(item => {
            if (item.style.display === 'none') return;
            totalDV += parseFloat(item.dataset.dvAmount) || 0;
        });

        const formatted = totalDV.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        ['totalDVAmountFooter', 'totalDVAmountFooterTable'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = formatted;
        });
        // Always show the card-view footer
        document.getElementById('disbursementsFooter').style.display = '';
    }

    /**
     * Filter DV cards/rows based on the search input (whichever view is active)
     */
    function filterTable() {
        const isTableView = !document.getElementById('disbursementsTableView').classList.contains('hidden');
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase().trim();
        const items = document.querySelectorAll(isTableView ? '.dv-row' : '.dv-card');
        let visibleCount = 0;

        items.forEach(item => {
            const searchText = item.dataset.searchText || item.textContent.toLowerCase();
            if (searchText.includes(filter)) {
                item.style.display = '';
                visibleCount++;
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

        updateTotalRecordsCount();
        updateDisbursementFooterTotal();
    }

    /**
     * Clear the search box (used by the active-filter chip and the no-results message)
     */
    function clearDisbursementSearchFilter() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
            filterTable();
            searchInput.focus();
        }
    }

    /**
     * Update total records count based on visible items in the active view
     * (counted per dv_no)
     */
    function updateTotalRecordsCount() {
        const isTableView = !document.getElementById('disbursementsTableView').classList.contains('hidden');
        const items = document.querySelectorAll(isTableView ? '.dv-row' : '.dv-card');
        let dvNumbers = new Set();

        items.forEach(item => {
            if (item.style.display !== 'none' && item.dataset.dvNo) {
                dvNumbers.add(item.dataset.dvNo);
            }
        });

        const totalRecordsElement = document.getElementById('totalRecordsCount');
        if (totalRecordsElement) {
            totalRecordsElement.textContent = dvNumbers.size;
        }
    }

    /**
     * Card / List view toggle
     */
    function updateDisbursementsViewButtons(view) {
        const cardBtn = document.getElementById('dvCardViewBtn');
        const listBtn = document.getElementById('dvListViewBtn');
        const activeClasses = ['bg-white', 'dark:bg-gray-700', 'text-blue-700', 'dark:text-blue-300', 'shadow-sm'];
        const inactiveClasses = ['text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-200'];

        [cardBtn, listBtn].forEach(btn => btn.classList.remove(...activeClasses, ...inactiveClasses));

        if (view === 'table') {
            listBtn.classList.add(...activeClasses);
            cardBtn.classList.add(...inactiveClasses);
        } else {
            cardBtn.classList.add(...activeClasses);
            listBtn.classList.add(...inactiveClasses);
        }
    }

    window.setDisbursementsView = function(view) {
        const cardView = document.getElementById('disbursementsCardView');
        const tableView = document.getElementById('disbursementsTableView');

        if (view === 'table') {
            cardView.classList.add('hidden');
            tableView.classList.remove('hidden');
        } else {
            view = 'card';
            tableView.classList.add('hidden');
            cardView.classList.remove('hidden');
        }

        try {
            localStorage.setItem('disbursementsView', view);
        } catch (e) {
            // localStorage unavailable — view just won't persist
        }

        updateDisbursementsViewButtons(view);
        filterTable();
    };

    document.addEventListener('DOMContentLoaded', function() {
        let savedView = 'table';
        try {
            savedView = localStorage.getItem('disbursementsView') || 'table';
        } catch (e) {
            // ignore
        }
        setDisbursementsView(savedView);
    });

    // Add event listener for input event to filter items as you type
    document.getElementById('searchInput').addEventListener('input', function() {
        filterTable();
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

    // Context Menu Handler for Disbursements
    const dvMenu = document.getElementById('disbursementContextMenu');

    window.showDisbursementContextMenu = function(event, card) {
        event.preventDefault();
        event.stopPropagation();
        
        // Remove highlight from previously selected card
        document.querySelectorAll('.dv-item.context-menu-active').forEach(r => {
            r.classList.remove('context-menu-active');
        });
        
        // Highlight the current card
        card.classList.add('context-menu-active');
        window.currentContextMenuRow = card;

        if (!dvMenu) return;

        // The menu is position:fixed, so we work in raw viewport coordinates
        // (clientX/clientY) and must NOT add page scroll offsets, or the menu
        // drifts away from the cursor the further the page has been scrolled.
        const menuHeight = 60; // Approximate menu height
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
            top = mouseY - menuHeight + 40;
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
        dvMenu.style.position = 'fixed';
        dvMenu.style.top = `${top}px`;
        dvMenu.style.left = `${left}px`;
        dvMenu.style.display = 'block';
        dvMenu.classList.remove('hidden');

        // Get obligation data and set up menu items
        const obligationId = card.dataset.obligationId;
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

            // Add scroll listeners to both scrollable containers
            ['disbursementsContainer', 'disbursementsTableContainer'].forEach(id => {
                const container = document.getElementById(id);
                if (container) {
                    container.addEventListener('scroll', hideDisbursementContextMenu, { passive: true });
                }
            });
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
        ['disbursementsContainer', 'disbursementsTableContainer'].forEach(id => {
            const container = document.getElementById(id);
            if (container) {
                container.removeEventListener('scroll', hideDisbursementContextMenu);
            }
        });
    }

    // Hide on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideDisbursementContextMenu();
    });

    /**
     * Export filtered disbursements data to Excel.
     * Reads directly from each visible card's data attributes since
     * the layout is card-based rather than a table.
     */
    function exportDisbursementsToExcel() {
        const headers = [
            'Office & Class', 'OBR No.', 'Particulars', 'DV / Check Number', 'DV / Check Date',
            'Program', 'Account Code', 'Description', 'Status', 'DV / Check Amount', 'Remarks'
        ];
        const data = [headers];

        const isTableView = !document.getElementById('disbursementsTableView').classList.contains('hidden');
        document.querySelectorAll(isTableView ? '.dv-row' : '.dv-card').forEach(item => {
            if (item.style.display === 'none') return;
            data.push([
                item.dataset.officeClass || '',
                item.dataset.obrNo || '',
                item.dataset.particulars || '',
                item.dataset.dvNo || '',
                item.dataset.dvDate || '',
                item.dataset.program || '',
                item.dataset.accountCode || '',
                item.dataset.description || '',
                item.dataset.status || '',
                item.dataset.dvAmount || '0',
                item.dataset.remarks || '',
            ]);
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

    /* Highlight when context menu is open (card or list/table view) */
    .dv-item.context-menu-active {
        background-color: rgba(59, 130, 246, 0.1);
        transition: background-color 0.2s ease-in-out;
    }

    .dark .dv-item.context-menu-active {
        background-color: rgba(59, 130, 246, 0.2);
    }
</style>
    </div>
</x-app-layout>