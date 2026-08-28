<x-app-layout>
    @if (session('status') || session('error'))
        @php
            $message = session('status') ?? session('error');
            $isError = session()->has('error');

            if ($isError) {
                $alertType = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200';
            } else {
                // Default success color
                $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200';
                if (str_contains($message, 'updated successfully')) {
                    $alertType = 'bg-blue-100 border-blue-400 text-blue-700 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-200';
                } elseif (str_contains($message, 'deleted successfully')) {
                    $alertType = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200';
                } elseif (str_contains($message, 'created successfully')) {
                    $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200';
                }
            }
        @endphp

        <div class="border-l-4 p-4 mb-4 {{ $alertType }}" role="alert">
            <div class="flex justify-between items-center">
                <div>
                    <p>{!! $message !!}</p>
                </div>
                <button type="button"
                    class="text-2xl font-semibold leading-none dark:text-gray-200"
                    onclick="this.parentElement.parentElement.remove();">
                    &times;
                </button>
            </div>
        </div>
    @endif

    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Supplemental Appropriations / Reversions') }}

                @php
                $filters = [];

                if (request('office_allotment_class_id')) {
                    $officeClass = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_id'));
                    if ($officeClass) {
                        $filters[] = $officeClass->offices->office_abbreviation . ' - ' . $officeClass->allotmentClass->class;
                    }
                }
                if (request('supplemental_type_filter')) {
                    $filters[] = request('supplemental_type_filter');
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

    <!-- Page Content Wrapper with Transition -->
    <div class="page-transition">

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
            Filters
        </h4>

        <form id="filterForm" method="GET" action="{{ route('supplementals.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

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

        @php
            $activeFilterChips = [];

            if (request('office_allotment_class_id')) {
                $officeClassChip = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_id'));
                if ($officeClassChip) {
                    $activeFilterChips[] = [
                        'label' => $officeClassChip->offices->office_abbreviation . ' - ' . $officeClassChip->allotmentClass->class,
                        'url' => '?' . http_build_query(request()->except(['office_allotment_class_id', 'page'])),
                    ];
                }
            }
            if (request('supplemental_type_filter')) {
                $activeFilterChips[] = [
                    'label' => 'Type: ' . request('supplemental_type_filter'),
                    'url' => '?' . http_build_query(request()->except(['supplemental_type_filter', 'page'])),
                ];
            }
            if (request('search')) {
                $activeFilterChips[] = [
                    'label' => 'Search: "' . request('search') . '"',
                    'url' => '?' . http_build_query(request()->except(['search', 'page'])),
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
                            <button type="button" onclick="clearSupplementalSearchFilter()" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Clear">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        @else
                            <a href="{{ $chip['url'] }}" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Remove filter">
                                <i class="fas fa-times text-[10px]"></i>
                            </a>
                        @endif
                    </span>
                @endforeach
                <a href="{{ route('supplementals.index') }}" class="text-xs text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 underline ml-1">
                    Clear all
                </a>
            </div>
        @endif
    </div>


    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex flex-col gap-3 md:flex-row md:justify-between md:items-center mb-4">
                <div class="flex-shrink-0">
                    @can('create supplementals')
                    <button onclick="openCreateSupplementalsModal()" class="text-blue-600 inline-flex items-center justify-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center w-full md:w-auto dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Create Supplemental Appropriation | Reversion') }}
                    </button>
                    @endcan
                </div>
                <!-- Right: Total Records and Search Input -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 w-full md:w-auto">
                    <!-- Total Records -->
                    <div class="flex items-center justify-center sm:justify-start space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 whitespace-nowrap">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $totalRecords }}</span>
                    </div>
                    <!-- Search Input -->
                    <div class="flex items-center space-x-2 w-full sm:w-72 md:w-96">
                        <i class="fas fa-search text-gray-400 flex-shrink-0"></i>
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for supplementals" class="form-control border border-gray-300 rounded-lg w-full px-4 py-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                    </div>
                </div>
            </div>

            <!-- Sort pills (replaces the old sortable table headers) -->
            @php
                $sortOptions = [
                    'office_allotment_class' => 'Office & Class',
                    'supplemental_no' => 'No.',
                    'supplemental_date' => 'Date',
                    'type' => 'Type',
                    'basis' => 'Basis',
                ];
            @endphp
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mr-1">Sort by:</span>
                @foreach ($sortOptions as $sortKey => $sortLabel)
                    @php
                        $isActiveSort = $sortBy == $sortKey;
                        $nextOrder = $isActiveSort && $sortOrder == 'asc' ? 'desc' : 'asc';
                    @endphp
                    <a href="{{ route('supplementals.index', array_merge(request()->except('page'), ['sort_by' => $sortKey, 'sort_order' => $nextOrder])) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                       {{ $isActiveSort ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        {{ $sortLabel }}
                        @if ($isActiveSort)
                            <i class="fas fa-arrow-{{ $sortOrder == 'asc' ? 'up' : 'down' }} text-[10px]"></i>
                        @endif
                    </a>
                @endforeach
            </div>

            <!-- View toggle: Card / List -->
            <div class="flex items-center gap-1 mb-3 bg-gray-100 dark:bg-gray-900 rounded-lg p-1 w-fit">
                <button type="button" id="supplementalsListViewBtn" onclick="setSupplementalsView('table')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                    <i class="fas fa-table-list"></i> List View
                </button>
                <button type="button" id="supplementalsCardViewBtn" onclick="setSupplementalsView('card')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                    <i class="fas fa-grip"></i> Card View
                </button>
            </div>

            <!-- Shared "no results" message — sits outside both views so it's visible regardless of which is active -->
            <div id="noSearchResultsMsg" class="hidden px-3 py-10 text-center text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-md mb-3 bg-gray-50 dark:bg-gray-900">
                <i class="fas fa-magnifying-glass text-2xl mb-2 block text-gray-300 dark:text-gray-600"></i>
                No supplementals match <span id="noSearchResultsQuery" class="font-semibold text-gray-700 dark:text-gray-300"></span>
                <button type="button" onclick="clearSupplementalSearchFilter()" class="block mx-auto mt-2 text-xs text-blue-600 hover:underline dark:text-blue-400">
                    Clear search
                </button>
            </div>

            <div id="supplementalsCardView">
            <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
            <div class="max-h-[720px] overflow-y-auto p-2 space-y-3 bg-gray-50 dark:bg-gray-900" id="supplementalsContainer">
                @php
                    $totalSupplemental = 0;
                    $totalReversion = 0;
                    // Cached per-record values so the List View below doesn't need to recompute them
                    $supplementalFileCounts = [];
                    $supplementalBalanced = [];
                    // Group by supplemental_no so all lines belonging to the same
                    // Supplemental Budget / Reversion document render as one card.
                    $groupedSupplementals = $supplementals->groupBy('supplemental_no');
                @endphp

                @forelse($groupedSupplementals as $groupNo => $groupItems)
                    @php
                        $groupFirst = $groupItems->first();
                        $groupType = trim($groupFirst->type ?? '');
                        $groupFileCount = \App\Models\SupplementalFile::where('supplemental_no', $groupNo)->count();
                        $groupAmountTotal = 0;

                        $groupTypeAccent = $groupType === 'Supplemental'
                            ? ['cardBorder' => 'border-emerald-300 dark:border-emerald-700', 'border' => 'border-emerald-500', 'badgeBg' => 'bg-emerald-100 dark:bg-emerald-900/50', 'badgeText' => 'text-emerald-700 dark:text-emerald-300', 'amount' => 'text-emerald-700 dark:text-emerald-400']
                            : ($groupType === 'Reversion'
                                ? ['cardBorder' => 'border-gray-300 dark:border-gray-600', 'border' => 'border-red-500', 'badgeBg' => 'bg-red-100 dark:bg-red-900/50', 'badgeText' => 'text-red-700 dark:text-red-300', 'amount' => 'text-red-700 dark:text-red-400']
                                : ['cardBorder' => 'border-gray-300 dark:border-gray-600', 'border' => 'border-gray-400', 'badgeBg' => 'bg-gray-100 dark:bg-gray-700', 'badgeText' => 'text-gray-700 dark:text-gray-300', 'amount' => 'text-gray-700 dark:text-gray-300']);
                        // Every ARO tied to this batch's SB No., and whether any row in the
                        // batch's amount no longer matches its ARO(s) (see
                        // SupplementalController::buildExistingAroLookups()).
                        $groupAros = $existingAroByBatch[$groupNo] ?? [];
                        $groupAroStale = $staleAroByBatch[$groupNo] ?? false;
                    @endphp
                    <div class="supplemental-group bg-white dark:bg-gray-800 border {{ $groupTypeAccent['cardBorder'] }} border-l-4 {{ $groupTypeAccent['border'] }} rounded-lg shadow-sm overflow-hidden text-xs hover:shadow-md transition-shadow"
                         data-supplemental-no="{{ $groupNo }}">

                        <!-- Group Header (shared by every line under this Supplemental No.) -->
                        <div class="flex flex-wrap justify-between items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-600">
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white font-bold text-[11px] tracking-wide shadow-sm dark:bg-blue-700">
                                    <i class="fas fa-building text-[10px] opacity-80"></i>{{ $groupFirst->officeAllotmentClass->office_abbreviation ?? '-' }} - {{ $groupFirst->officeAllotmentClass->class ?? '-' }}
                                </span>
                                <span class="font-bold text-gray-800 dark:text-gray-100">
                                    <i class="fas fa-hashtag mr-1 text-blue-500"></i>{{ $groupNo }}
                                </span>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <i class="far fa-calendar mr-1"></i>{{ $groupFirst->supplemental_date }}
                                </span>
                                <span class="px-2 py-1 rounded font-semibold {{ $groupTypeAccent['badgeBg'] }} {{ $groupTypeAccent['badgeText'] }}">
                                    {{ $groupType ? ucfirst($groupType) : '-' }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 text-[11px]">
                                    <i class="fas fa-layer-group mr-1"></i>{{ $groupItems->count() }} {{ $groupItems->count() == 1 ? 'line' : 'lines' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="openSupplementalFilesModal('{{ $groupNo }}')"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded transition-colors
                                    @if($groupFileCount > 0)
                                        bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-800 font-semibold
                                    @else
                                        bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600
                                    @endif"
                                    title="View files">
                                    <i class="fas fa-file"></i>
                                    <span>{{ $groupFileCount }}</span>
                                </button>
                                @if ($groupType === 'Supplemental')
                                    @if ($groupAros)
                                        @if ($groupAroStale)
                                            <span title="This batch's amount no longer matches its ARO — it may have been edited after the ARO was created." class="inline-flex items-center gap-1 px-2 py-1 rounded font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                                                <i class="fas fa-triangle-exclamation"></i>
                                            </span>
                                        @endif
                                        <x-aro-preview-picker :aros="$groupAros" return-to="supplementals" uid="batch-{{ $groupFirst->id }}"
                                            onclick="event.stopPropagation();"
                                            class="text-blue-600 inline-flex items-center gap-1 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-2 py-1 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-colors">
                                            <i class="fas fa-eye"></i> Preview ARO
                                        </x-aro-preview-picker>
                                    @else
                                        @can('create appropriations')
                                            <button type="button" onclick="event.stopPropagation(); fillAndOpenAroCreateModal({{ \Illuminate\Support\Js::from([
                                                'officeAllotmentClassesId' => (string) $groupFirst->office_allotment_classes_id,
                                                'supplementalNo' => $groupFirst->basis_no,
                                                'dateOfIssue' => $groupFirst->supplemental_date,
                                            ]) }})" title="Create ARO for this Supplemental"
                                                class="text-green-600 inline-flex items-center gap-1 hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-2 py-1 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-colors">
                                                <i class="fas fa-folder-open"></i> Create ARO
                                            </button>
                                        @endcan
                                    @endif
                                @endif
                                @can('delete supplementals')
                                    <button onclick="openBulkDeleteSupplementalModal('{{ $groupNo }}', {{ $groupFirst->id }})" type="button" title="Delete All Related"
                                        class="text-orange-600 inline-flex items-center gap-1 hover:text-white border border-orange-600 hover:bg-orange-600 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-xs px-2 py-1 dark:border-orange-500 dark:text-orange-500 dark:hover:text-white dark:hover:bg-orange-600 dark:focus:ring-orange-900 transition-colors">
                                        <i class="fas fa-trash-alt"></i> Delete All
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <!-- Group Lines: one row per Appropriation covered by this Supplemental No. -->
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($groupItems as $supplemental)
                            @php
                                $amount = isset($supplemental->amount) ? (float)$supplemental->amount : 0;
                                $type = trim($supplemental->type ?? '');

                                if ($type === 'Supplemental') {
                                    $totalSupplemental += $amount;
                                } elseif ($type === 'Reversion') {
                                    $totalReversion += $amount;
                                }
                                $groupAmountTotal += $amount;

                                $fileCount = $groupFileCount;

                                $quarterSum = (float)($supplemental->quarter1 ?? 0)
                                    + (float)($supplemental->quarter2 ?? 0)
                                    + (float)($supplemental->quarter3 ?? 0)
                                    + (float)($supplemental->quarter4 ?? 0);
                                $quarterDiff = round($amount - $quarterSum, 2);
                                $isQuarterBalanced = abs($quarterDiff) < 0.01;
                                $supplementalFileCounts[$supplemental->id] = $fileCount;
                                $supplementalBalanced[$supplemental->id] = $isQuarterBalanced;

                                $searchText = strtolower(collect([
                                    $supplemental->supplemental_no,
                                    $supplemental->supplemental_date,
                                    $type,
                                    $supplemental->basis,
                                    $supplemental->officeAllotmentClass->office_abbreviation ?? '',
                                    $supplemental->officeAllotmentClass->class ?? '',
                                    $supplemental->appropriation->programs ?? '',
                                    $supplemental->appropriation->account_code ?? '',
                                    $supplemental->appropriation->description ?? '',
                                ])->implode(' '));
                            @endphp
                            <div class="supplemental-item supplemental-card px-3 py-3 hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors cursor-pointer"
                                 oncontextmenu="showSupplementalContextMenu(event, this)"
                                 data-supplemental='@json($supplemental)'
                                 data-supplemental-no="{{ $supplemental->supplemental_no }}"
                                 data-type="{{ $type }}"
                                 data-amount="{{ $amount }}"
                                 data-search-text="{{ $searchText }}">

                                <!-- Line Header -->
                                <div class="flex flex-wrap justify-between items-center gap-2 mb-2">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <span class="font-semibold text-gray-700 dark:text-gray-200">
                                            <i class="fas fa-code mr-1 text-blue-400"></i>{{ $supplemental->appropriation->account_code ?? '-' }}
                                        </span>
                                        @if ($type !== 'Reversion')
                                            @if ($isQuarterBalanced)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300" title="Quarterly breakdown matches the amount">
                                                    <i class="fas fa-check-circle"></i>Balanced
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300" title="Quarterly breakdown ({{ number_format($quarterSum, 2) }}) does not match the amount ({{ number_format($amount, 2) }})">
                                                    <i class="fas fa-triangle-exclamation"></i>Mismatch
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        @can('edit supplementals')
                                            <button onclick="event.stopPropagation(); triggerSupplementalCardAction(this, 'edit')" type="button" title="Edit"
                                                class="text-amber-600 inline-flex items-center gap-1 hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-colors">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        @endcan
                                        @can('delete supplementals')
                                            <button onclick="event.stopPropagation(); triggerSupplementalCardAction(this, 'delete')" type="button" title="Delete This Entry"
                                                class="text-red-600 inline-flex items-center gap-1 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-colors">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        @endcan
                                    </div>
                                </div>

                                <!-- Line Body -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-2">
                                    <div class="col-span-2">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Amount</div>
                                        <div class="font-bold text-sm tabular-nums {{ $groupTypeAccent['amount'] }}">{{ number_format($amount, 2) }}</div>
                                    </div>
                                    <div class="col-span-2">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Program</div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $supplemental->appropriation->programs ?? '-' }}</div>
                                    </div>
                                    <div class="col-span-2">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Description</div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-300 break-words">{{ $supplemental->appropriation->description ?? '-' }}</div>
                                    </div>
                                    <div class="col-span-2 sm:col-span-4">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Basis</div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200 break-words">{{ $supplemental->basis ?? '-' }}</div>
                                    </div>
                                </div>

                                @if ($type !== 'Reversion')
                                <!-- Quarterly breakdown (inline) — Reversions don't use quarterly allocation -->
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 pt-1.5 border-t border-gray-200 dark:border-gray-700 text-[11px]">
                                    <span class="text-gray-400 dark:text-gray-500">
                                        <span class="uppercase tracking-wide text-[10px]">1st Qtr</span>
                                        <span class="font-semibold text-gray-700 dark:text-gray-300 tabular-nums ml-1">{{ $supplemental->quarter1 ?? '-' }}</span>
                                    </span>
                                    <span class="text-gray-400 dark:text-gray-500">
                                        <span class="uppercase tracking-wide text-[10px]">2nd Qtr</span>
                                        <span class="font-semibold text-gray-700 dark:text-gray-300 tabular-nums ml-1">{{ $supplemental->quarter2 ?? '-' }}</span>
                                    </span>
                                    <span class="text-gray-400 dark:text-gray-500">
                                        <span class="uppercase tracking-wide text-[10px]">3rd Qtr</span>
                                        <span class="font-semibold text-gray-700 dark:text-gray-300 tabular-nums ml-1">{{ $supplemental->quarter3 ?? '-' }}</span>
                                    </span>
                                    <span class="text-gray-400 dark:text-gray-500">
                                        <span class="uppercase tracking-wide text-[10px]">4th Qtr</span>
                                        <span class="font-semibold text-gray-700 dark:text-gray-300 tabular-nums ml-1">{{ $supplemental->quarter4 ?? '-' }}</span>
                                    </span>
                                    <span class="ml-auto text-gray-400 dark:text-gray-500">
                                        <span class="uppercase tracking-wide text-[10px]">Qtr Total</span>
                                        <span class="font-bold tabular-nums ml-1 {{ $isQuarterBalanced ? 'text-gray-700 dark:text-gray-300' : 'text-amber-700 dark:text-amber-400' }}">
                                            {{ number_format($quarterSum, 2) }}
                                        </span>
                                    </span>
                                </div>
                                @endif
                            </div>
                        @endforeach
                        </div>

                        <!-- Group Total (sum of all lines under this Supplemental No.) -->
                        <div class="px-3 py-1.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 text-right text-[11px]">
                            <span class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mr-1">Group Total</span>
                            <span class="font-bold tabular-nums {{ $groupTypeAccent['amount'] }}">{{ number_format($groupAmountTotal, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                        No Supplemental Appropriations or Reversions found
                    </div>
                @endforelse
            </div>

            <!-- Totals Footer -->
            <div id="supplementalsFooter" class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-300 dark:divide-gray-600 bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-gray-700 dark:border-gray-600">
                <div class="text-center text-sm px-1 py-3">
                    Total Supplemental:
                    <span id="totalSourceFooter" class="px-2 py-1 rounded text-emerald-700 bg-emerald-100 dark:bg-emerald-900 dark:text-emerald-300 font-bold text-base tabular-nums ml-2">
                        {{ number_format($totalSupplemental, 2) }}
                    </span>
                </div>
                <div class="text-center text-sm px-1 py-3">
                    Total Reversions:
                    <span id="totalRecipientFooter" class="px-2 py-1 rounded text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300 font-bold text-base tabular-nums ml-2">
                        {{ number_format($totalReversion, 2) }}
                    </span>
                </div>
                <div class="text-center text-sm px-1 py-3">
                    Net Change:
                    <span id="netChangeFooter" class="px-2 py-1 rounded font-bold text-base tabular-nums ml-2 {{ ($totalSupplemental - $totalReversion) < 0 ? 'text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300' : 'text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300' }}">
                        {{ number_format($totalSupplemental - $totalReversion, 2) }}
                    </span>
                </div>
            </div>
            </div>
            </div>
            <!-- /#supplementalsCardView -->

            <!-- List View (flat table) -->
            <div id="supplementalsTableView" class="hidden">
                <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <div class="max-h-[720px] overflow-y-auto" id="supplementalsTableContainer">
                            <table class="min-w-full text-xs text-center text-gray-600 dark:text-gray-300">
                                <thead class="text-center border-b-2 border-t-2 border-gray-700 text-xs text-gray-700 bg-gray-200 dark:bg-gray-900 dark:text-gray-400 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">No.</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Date</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Type</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Office & Class</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Account Code</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Program</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Description</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Basis</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Amount</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Qtr Total</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Balanced</th>
                                        <th class="px-2 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">Files</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($supplementals as $supplemental)
                                        @php
                                            $rowAmount = isset($supplemental->amount) ? (float)$supplemental->amount : 0;
                                            $rowType = trim($supplemental->type ?? '');
                                            $rowQuarterSum = (float)($supplemental->quarter1 ?? 0)
                                                + (float)($supplemental->quarter2 ?? 0)
                                                + (float)($supplemental->quarter3 ?? 0)
                                                + (float)($supplemental->quarter4 ?? 0);
                                            $rowFileCount = $supplementalFileCounts[$supplemental->id] ?? 0;
                                            $rowBalanced = $supplementalBalanced[$supplemental->id] ?? true;
                                            // Every ARO already releasing this specific row, and whether its amount
                                            // no longer matches (see SupplementalController::buildExistingAroLookups()).
                                            $rowAroKey = "{$supplemental->appropriations_id}|{$supplemental->basis_no}";
                                            $rowAros = $existingAroByRow[$rowAroKey] ?? [];
                                            $rowAroStale = $staleAroByRow[$rowAroKey] ?? false;
                                            $rowSearchText = strtolower(collect([
                                                $supplemental->supplemental_no,
                                                $supplemental->supplemental_date,
                                                $rowType,
                                                $supplemental->basis,
                                                $supplemental->officeAllotmentClass->office_abbreviation ?? '',
                                                $supplemental->officeAllotmentClass->class ?? '',
                                                $supplemental->appropriation->programs ?? '',
                                                $supplemental->appropriation->account_code ?? '',
                                                $supplemental->appropriation->description ?? '',
                                            ])->implode(' '));
                                        @endphp
                                        <tr class="supplemental-item supplemental-row bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                                            oncontextmenu="showSupplementalContextMenu(event, this)"
                                            data-supplemental='@json($supplemental)'
                                            data-supplemental-no="{{ $supplemental->supplemental_no }}"
                                            data-type="{{ $rowType }}"
                                            data-amount="{{ $rowAmount }}"
                                            data-search-text="{{ $rowSearchText }}">
                                            <td class="font-semibold px-2 py-2">{{ $supplemental->supplemental_no }}</td>
                                            <td class="px-2 py-2">{{ $supplemental->supplemental_date }}</td>
                                            <td class="px-2 py-2">
                                                @if ($rowType === 'Supplemental')
                                                    <span class="px-2 py-1 rounded font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">Supplemental</span>
                                                @elseif ($rowType === 'Reversion')
                                                    <span class="px-2 py-1 rounded font-semibold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300">Reversion</span>
                                                @else
                                                    <span class="px-2 py-1 rounded font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $rowType ?: '-' }}</span>
                                                @endif
                                            </td>
                                            <td class="text-left px-2 py-2">{{ $supplemental->officeAllotmentClass->office_abbreviation ?? '-' }} - {{ $supplemental->officeAllotmentClass->class ?? '-' }}</td>
                                            <td class="font-semibold px-2 py-2">{{ $supplemental->appropriation->account_code ?? '-' }}</td>
                                            <td class="text-left px-2 py-2 max-w-xs">{{ $supplemental->appropriation->programs ?? '-' }}</td>
                                            <td class="text-left px-2 py-2 max-w-xs">{{ $supplemental->appropriation->description ?? '-' }}</td>
                                            <td class="text-left px-2 py-2 max-w-xs">{{ $supplemental->basis ?? '-' }}</td>
                                            <td class="px-2 py-2 text-right font-bold tabular-nums {{ $rowType === 'Supplemental' ? 'text-emerald-700 dark:text-emerald-400' : ($rowType === 'Reversion' ? 'text-red-700 dark:text-red-400' : 'text-gray-700 dark:text-gray-300') }}">
                                                {{ number_format($rowAmount, 2) }}
                                            </td>
                                            <td class="px-2 py-2 text-right tabular-nums">
                                                @if ($rowType !== 'Reversion')
                                                    {{ number_format($rowQuarterSum, 2) }}
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-2">
                                                @if ($rowType !== 'Reversion')
                                                    @if ($rowBalanced)
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300"><i class="fas fa-check-circle"></i></span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300"><i class="fas fa-triangle-exclamation"></i></span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-2">
                                                <div class="inline-flex items-center gap-1">
                                                    @if ($rowType === 'Supplemental')
                                                        @if ($rowAros)
                                                            @if ($rowAroStale)
                                                                <span title="This row's amount no longer matches its ARO — it may have been edited after the ARO was created." class="inline-flex items-center gap-1 px-2 py-1 rounded font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                                                                    <i class="fas fa-triangle-exclamation"></i>
                                                                </span>
                                                            @endif
                                                            <x-aro-preview-picker :aros="$rowAros" return-to="supplementals" uid="row-{{ $supplemental->id }}"
                                                                onclick="event.stopPropagation();"
                                                                class="text-blue-600 inline-flex items-center gap-1 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded transition-colors px-2 py-1">
                                                                <i class="fas fa-eye"></i>
                                                            </x-aro-preview-picker>
                                                        @else
                                                            @can('create appropriations')
                                                                <button type="button" onclick="event.stopPropagation(); fillAndOpenAroCreateModal({{ \Illuminate\Support\Js::from([
                                                                    'officeAllotmentClassesId' => (string) $supplemental->office_allotment_classes_id,
                                                                    'supplementalNo' => $supplemental->basis_no,
                                                                    'dateOfIssue' => $supplemental->supplemental_date,
                                                                ]) }})" title="Create ARO for this Supplemental"
                                                                    class="text-green-600 inline-flex items-center gap-1 hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded transition-colors px-2 py-1">
                                                                    <i class="fas fa-folder-open"></i>
                                                                </button>
                                                            @endcan
                                                        @endif
                                                    @endif
                                                    <button onclick="event.stopPropagation(); openSupplementalFilesModal('{{ $supplemental->supplemental_no }}')"
                                                        class="inline-flex items-center gap-1 px-2 py-1 rounded transition-colors
                                                        @if($rowFileCount > 0)
                                                            bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-800 font-semibold
                                                        @else
                                                            bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600
                                                        @endif"
                                                        title="View files">
                                                        <i class="fas fa-file"></i>
                                                        <span>{{ $rowFileCount }}</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                                No Supplemental Appropriations or Reversions found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totals Footer -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-300 dark:divide-gray-600 bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-gray-700 dark:border-gray-600">
                        <div class="text-center text-sm px-1 py-3">
                            Total Supplemental:
                            <span id="totalSourceFooterTable" class="px-2 py-1 rounded text-emerald-700 bg-emerald-100 dark:bg-emerald-900 dark:text-emerald-300 font-bold text-base tabular-nums ml-2">0.00</span>
                        </div>
                        <div class="text-center text-sm px-1 py-3">
                            Total Reversions:
                            <span id="totalRecipientFooterTable" class="px-2 py-1 rounded text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300 font-bold text-base tabular-nums ml-2">0.00</span>
                        </div>
                        <div class="text-center text-sm px-1 py-3">
                            Net Change:
                            <span id="netChangeFooterTable" class="px-2 py-1 rounded font-bold text-base tabular-nums ml-2">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /#supplementalsTableView -->

             <!-- Pagination -->
            <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                @if ($perPage != 'all')
                {{ $supplementals->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div id="supplementalContextMenu" 
        class="fixed hidden w-48 max-w-[calc(100vw-16px)] bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-400 rounded-lg shadow-2xl z-50 dark:from-blue-900 dark:to-blue-800 dark:border-blue-600"
        style="display: none;">
        <button id="contextFiles"
                class="w-full text-left block px-4 py-2 text-xs text-green-900 hover:bg-green-200 dark:text-green-100 dark:hover:bg-green-700 transition-colors duration-150">
            <i class="fas fa-file-upload mr-2 text-green-600"></i>Files
        </button>
        @can('edit supplementals')
        <button id="contextEdit"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-edit mr-2 text-blue-600"></i>Edit
        </button>
        @endcan
        @can('delete supplementals')
        <button id="contextDelete"
                class="w-full text-left px-4 py-2 text-xs text-red-700 hover:bg-red-200 dark:text-red-300 dark:hover:bg-red-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-trash mr-2 text-red-600"></i>Delete This Entry
        </button>
        <button id="contextBulkDelete"
                class="w-full text-left px-4 py-2 text-xs text-orange-700 hover:bg-orange-200 dark:text-orange-300 dark:hover:bg-orange-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-trash-alt mr-2 text-orange-600"></i>Delete All Related
        </button>
        @endcan
    </div>

    @include('supplementals.modal.create')
    @include('supplementals.modal.edit')
    @include('supplementals.modal.delete')
    @include('supplementals.modal.supplemental_files')

    @can('create appropriations')
    @include('allotment_release_orders.modal.create', [
        'officeAllotmentClassesForForm' => $officeAllotmentClasses,
        'returnTo' => 'supplementals',
    ])
    @include('allotment_release_orders.modal.edit', [
        'returnTo' => 'supplementals',
    ])

    <!-- Prompt shown right after a NEW "Supplemental" batch is saved, asking
         whether to create an ARO for it now (see the DOMContentLoaded handler
         below). -->
    <div id="supplementalAroPromptModal" style="display:none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300 ease-out hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <div class="flex justify-between items-center px-6 py-4 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-700">
                <h2 class="text-base leading-6 font-bold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 mr-3 text-xl"></i>
                    {{ __('Supplemental Created') }}
                </h2>
                <button type="button" onclick="closeSupplementalAroPromptModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="px-6 py-5 text-sm text-gray-700 dark:text-gray-200">
                {{ __('Would you like to create an Allotment Release Order (ARO) to release this Supplemental now?') }}
            </div>
            <div class="flex justify-end items-center gap-3 p-4 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                <button type="button" onclick="closeSupplementalAroPromptModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    {{ __('Not Now') }}
                </button>
                <button type="button" onclick="openAroCreateModalFromPending()" class="text-green-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-folder-open mr-1"></i> {{ __('Create ARO') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Prompt shown after EDITING a "Supplemental" that already has an ARO,
         asking whether to edit that ARO too (see the DOMContentLoaded handler
         below). -->
    <div id="supplementalEditAroPromptModal" style="display:none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300 ease-out hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <div class="flex justify-between items-center px-6 py-4 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700">
                <h2 class="text-base leading-6 font-bold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mr-3 text-xl"></i>
                    {{ __('Supplemental Updated') }}
                </h2>
                <button type="button" onclick="closeSupplementalEditAroPromptModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="px-6 py-5 text-sm text-gray-700 dark:text-gray-200">
                {{ __('This Supplemental already has an Allotment Release Order. Would you like to edit that ARO as well, to match what just changed?') }}
            </div>
            <div class="flex justify-end items-center gap-3 p-4 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                <button type="button" onclick="closeSupplementalEditAroPromptModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    {{ __('Not Now') }}
                </button>
                <button type="button" onclick="editPendingAro()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-edit mr-1"></i> {{ __('Edit ARO') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        let pendingAroPrefill = null;
        let pendingExistingAroId = null;

        function openSupplementalAroPromptModal() {
            const modal = document.getElementById('supplementalAroPromptModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                const box = modal.querySelector('div.hidden');
                if (box) box.classList.remove('hidden');
            }, 10);
        }

        function closeSupplementalAroPromptModal() {
            const modal = document.getElementById('supplementalAroPromptModal');
            const box = modal.querySelector('div.hidden, div[style*="animation"]') || modal.querySelector('> div');
            if (box) {
                box.classList.add('hidden');
                setTimeout(() => { modal.style.display = 'none'; }, 300);
            } else {
                modal.style.display = 'none';
            }
        }

        function openSupplementalEditAroPromptModal() {
            const modal = document.getElementById('supplementalEditAroPromptModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                const box = modal.querySelector('div.hidden');
                if (box) box.classList.remove('hidden');
            }, 10);
        }

        function closeSupplementalEditAroPromptModal() {
            const modal = document.getElementById('supplementalEditAroPromptModal');
            const box = modal.querySelector('div.hidden, div[style*="animation"]') || modal.querySelector('> div');
            if (box) {
                box.classList.add('hidden');
                setTimeout(() => { modal.style.display = 'none'; }, 300);
            } else {
                modal.style.display = 'none';
            }
        }

        // Pre-fills and opens the (embedded) ARO create modal from a Supplemental
        // batch — office/allotment class, Fund Source = Supplemental Budget, SB
        // No., and Date of Issue — so the user reviews/checks the account codes
        // and creates the ARO without looking everything up again.
        function fillAndOpenAroCreateModal(prefill) {
            const item = aroOfficeAllotmentClasses.find(o => o.id === prefill.officeAllotmentClassesId);
            if (!item) return;

            openCreateAroModal();

            // Set every prefilled field directly first, then fetch the Account
            // Codes exactly once at the end. Previously this called
            // AroForm.selectOfficeAllotmentClass()/onFundSourceChange()/
            // onSupplementalNoChange() in sequence, each of which independently
            // fires its own loadAccountCodes() request — those requests race, so
            // an earlier one (e.g. still Fund Source "Annual Budget", before it
            // was switched to "Supplemental Budget") could resolve last and leave
            // the table showing the wrong account codes until the button was
            // clicked a second time.
            document.getElementById('create_office_allotment_class').value = item.name;
            document.getElementById('create_office_allotment_classes_id').value = item.id;
            document.getElementById('create_office_allotment_class_dropdown').classList.add('hidden');
            document.getElementById('create_ppa_code').value = '';

            document.getElementById('create_fund_source').value = 'Supplemental Budget';
            document.getElementById('create_supplemental_no_wrapper').style.display = '';
            document.getElementById('create_realignment_no_wrapper').style.display = 'none';

            if (prefill.supplementalNo) {
                document.getElementById('create_supplemental_no').value = prefill.supplementalNo;
            }

            if (prefill.dateOfIssue) {
                document.getElementById('create_date_of_issue').value = prefill.dateOfIssue;
            }

            AroForm.loadAccountCodes('create');
            AroForm.previewAroNo('create');
        }

        function openAroCreateModalFromPending() {
            if (!pendingAroPrefill) return;
            closeSupplementalAroPromptModal();
            fillAndOpenAroCreateModal(pendingAroPrefill);
        }

        // Fetches the ARO flagged by the "edit that ARO too?" prompt and opens
        // it in the (embedded) edit modal — same modal/JS the ARO index's own
        // "Edit" button uses, just fed data from an AJAX call instead of having
        // it already inlined server-side.
        function editPendingAro() {
            if (!pendingExistingAroId) return;
            closeSupplementalEditAroPromptModal();

            const url = aroRoutes.jsonTemplate.replace('__ARO_ID__', pendingExistingAroId);
            fetch(url)
                .then(r => r.json())
                .then(aro => openEditAroModal(aro));
        }

        // SupplementalController::store()/update() redirect back here with these
        // query params after a "Supplemental" batch is saved/edited (not shown
        // for "Reversion" batches — no release applies there):
        //   - open_aro_create=1: a brand-new batch was just created — ask first
        //     (see the prompt above) before opening the ARO create modal.
        //   - open_aro_create_direct=1: an existing batch was edited and has no
        //     ARO yet — open the (pre-filled) create modal directly, no prompt.
        //   - existing_aro_id=<id>: an existing batch was edited and already has
        //     an ARO — ask whether to edit that ARO too.
        document.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);
            const openCreate = params.get('open_aro_create');
            const openCreateDirect = params.get('open_aro_create_direct');
            const existingAroId = params.get('existing_aro_id');

            if (openCreate === '1') {
                pendingAroPrefill = {
                    officeAllotmentClassesId: params.get('aro_office_allotment_classes_id'),
                    supplementalNo: params.get('aro_supplemental_no'),
                    dateOfIssue: params.get('aro_date_of_issue'),
                };
                openSupplementalAroPromptModal();
            } else if (openCreateDirect === '1') {
                fillAndOpenAroCreateModal({
                    officeAllotmentClassesId: params.get('aro_office_allotment_classes_id'),
                    supplementalNo: params.get('aro_supplemental_no'),
                    dateOfIssue: params.get('aro_date_of_issue'),
                });
            } else if (existingAroId) {
                pendingExistingAroId = existingAroId;
                openSupplementalEditAroPromptModal();
            } else {
                return;
            }

            // Clean the URL so refreshing the page doesn't repeat any of the above.
            ['open_aro_create', 'open_aro_create_direct', 'existing_aro_id',
                'aro_office_allotment_classes_id', 'aro_supplemental_no', 'aro_date_of_issue']
                .forEach(key => params.delete(key));
            const query = params.toString();
            window.history.replaceState({}, '', window.location.pathname + (query ? '?' + query : ''));
        });
    </script>
    @endcan

    </div>
</x-app-layout>

<script>
    (function() {
    const menu = document.getElementById('supplementalContextMenu');

    // showContextMenu receives the mouse event and the card element
    window.showSupplementalContextMenu = function(event, card) {
        event.preventDefault();
        event.stopPropagation();

        if (!menu) return;

        // Remove highlight from previously selected card/row
        document.querySelectorAll('.supplemental-item.context-menu-active').forEach(r => {
            r.classList.remove('context-menu-active');
        });

        // Highlight the current card
        card.classList.add('context-menu-active');
        window.currentContextMenuRow = card;

        // The menu is position:fixed, so we work in raw viewport coordinates
        // (clientX/clientY) and must NOT add page scroll offsets, or the menu
        // drifts away from the cursor the further the page has been scrolled.
        const menuHeight = 200; // Approximate menu height
        const menuWidth = 192; // w-48 = 12rem = 192px
        const viewportHeight = window.innerHeight;
        const viewportWidth = window.innerWidth;
        const mouseX = event.clientX;
        const mouseY = event.clientY;

        // Determine if menu should appear above or below the cursor
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
        menu.style.position = 'fixed';
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
        menu.style.display = 'block';
        menu.classList.remove('hidden');

        // Get supplemental data
        const supplemental = card.dataset.supplemental ? JSON.parse(card.dataset.supplemental) : null;
        if (supplemental) {
            // Files button
            const filesBtn = menu.querySelector('#contextFiles');
            if (filesBtn && supplemental.supplemental_no) {
                filesBtn.onclick = () => {
                    hideSupplementalContextMenu();
                    openSupplementalFilesModal(supplemental.supplemental_no);
                };
            }

            // Edit button
            const editBtn = menu.querySelector('#contextEdit');
            if (editBtn) {
                editBtn.onclick = () => {
                    hideSupplementalContextMenu();
                    openEditSupplementalModal(supplemental);
                };
            }

            // Delete button
            const deleteBtn = menu.querySelector('#contextDelete');
            if (deleteBtn && supplemental.id) {
                deleteBtn.onclick = () => {
                    hideSupplementalContextMenu();
                    openDeleteSupplementalModal(
                        supplemental.id,
                        supplemental.supplemental_no,
                        supplemental.type,
                        supplemental.amount,
                        supplemental.appropriations_id
                    );
                };
            }

            // Bulk Delete button
            const bulkDeleteBtn = menu.querySelector('#contextBulkDelete');
            if (bulkDeleteBtn && supplemental.supplemental_no) {
                bulkDeleteBtn.onclick = () => {
                    hideSupplementalContextMenu();
                    openBulkDeleteSupplementalModal(
                        supplemental.supplemental_no,
                        supplemental.id
                    );
                };
            }
        }

        // Add event listeners with delay
        setTimeout(() => {
            document.addEventListener('click', hideSupplementalContextMenu);
            window.addEventListener('resize', hideSupplementalContextMenu);
            window.addEventListener('scroll', hideSupplementalContextMenu, { passive: true });
            ['supplementalsContainer', 'supplementalsTableContainer'].forEach(id => {
                const container = document.getElementById(id);
                if (container) {
                    container.addEventListener('scroll', hideSupplementalContextMenu, { passive: true });
                }
            });
        }, 30);
    };

    /**
     * Dispatches a Card View footer action button to the same functions the
     * right-click context menu already uses, so both stay in sync with a
     * single implementation per action.
     */
    window.triggerSupplementalCardAction = function(button, action) {
        const card = button.closest('.supplemental-item');
        if (!card) return;
        const supplemental = card.dataset.supplemental ? JSON.parse(card.dataset.supplemental) : null;
        if (!supplemental) return;

        switch (action) {
            case 'files':
                openSupplementalFilesModal(supplemental.supplemental_no);
                break;
            case 'edit':
                openEditSupplementalModal(supplemental);
                break;
            case 'delete':
                openDeleteSupplementalModal(
                    supplemental.id,
                    supplemental.supplemental_no,
                    supplemental.type,
                    supplemental.amount,
                    supplemental.appropriations_id
                );
                break;
            case 'bulkDelete':
                openBulkDeleteSupplementalModal(
                    supplemental.supplemental_no,
                    supplemental.id
                );
                break;
        }
    };

    function hideSupplementalContextMenu() {
        if (!menu) return;
        menu.classList.add('hidden');
        menu.style.display = 'none';

        // Remove highlight when menu is closed
        if (window.currentContextMenuRow) {
            window.currentContextMenuRow.classList.remove('context-menu-active');
            window.currentContextMenuRow = null;
        }

        // Remove event listeners
        document.removeEventListener('click', hideSupplementalContextMenu);
        window.removeEventListener('resize', hideSupplementalContextMenu);
        window.removeEventListener('scroll', hideSupplementalContextMenu);
        ['supplementalsContainer', 'supplementalsTableContainer'].forEach(id => {
            const container = document.getElementById(id);
            if (container) {
                container.removeEventListener('scroll', hideSupplementalContextMenu);
            }
        });
    }

    // Hide on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideSupplementalContextMenu();
    });

    /**
     * Filter supplemental cards based on search input (Card View)
     */
    function filterSupplementals(searchValue) {
        const cards = document.querySelectorAll('.supplemental-card');
        const lowerSearch = searchValue.toLowerCase().trim();
        let visibleCount = 0;
        const visibleGroups = new Set();

        cards.forEach(card => {
            const searchText = card.dataset.searchText || card.textContent.toLowerCase();
            if (searchText.includes(lowerSearch)) {
                card.style.display = '';
                visibleCount++;
                if (card.dataset.supplementalNo) visibleGroups.add(card.dataset.supplementalNo);
            } else {
                card.style.display = 'none';
            }
        });

        // A group card (one per Supplemental No.) stays visible only while at
        // least one of its lines matches the search.
        document.querySelectorAll('.supplemental-group').forEach(group => {
            group.style.display = visibleGroups.has(group.dataset.supplementalNo) ? '' : 'none';
        });

        updateSupplementalsNoResultsMessage(lowerSearch, visibleCount, cards.length, searchValue);
        updateTotalRecordsCount();
        updateFooterTotals();
    }
    window.filterSupplementals = filterSupplementals;

    /**
     * Filter supplemental rows based on search input (List View)
     */
    function filterSupplementalsTable(searchValue) {
        const rows = document.querySelectorAll('.supplemental-row');
        const lowerSearch = searchValue.toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(row => {
            const searchText = row.dataset.searchText || row.textContent.toLowerCase();
            if (searchText.includes(lowerSearch)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        updateSupplementalsNoResultsMessage(lowerSearch, visibleCount, rows.length, searchValue);
        updateTotalRecordsCount();
        updateFooterTotals();
    }
    window.filterSupplementalsTable = filterSupplementalsTable;

    function updateSupplementalsNoResultsMessage(lowerSearch, visibleCount, totalCount, rawValue) {
        const noResultsMsg = document.getElementById('noSearchResultsMsg');
        const noResultsQuery = document.getElementById('noSearchResultsQuery');
        if (!noResultsMsg) return;

        if (lowerSearch.length > 0 && visibleCount === 0 && totalCount > 0) {
            if (noResultsQuery) noResultsQuery.textContent = `"${rawValue.trim()}"`;
            noResultsMsg.classList.remove('hidden');
        } else {
            noResultsMsg.classList.add('hidden');
        }
    }

    /**
     * Re-apply the current search value to whichever view is active
     */
    function filterActiveSupplementalsView(searchValue) {
        const isTableView = !document.getElementById('supplementalsTableView').classList.contains('hidden');
        if (isTableView) {
            filterSupplementalsTable(searchValue);
        } else {
            filterSupplementals(searchValue);
        }
    }

    /**
     * Clear the search box (used by the active-filter chip and the no-results message)
     */
    window.clearSupplementalSearchFilter = function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
            filterActiveSupplementalsView('');
            searchInput.focus();
        }
    };

    /**
     * Update total records count for the active view (counts visible cards or rows)
     */
    function updateTotalRecordsCount() {
        const isTableView = !document.getElementById('supplementalsTableView').classList.contains('hidden');
        const items = isTableView
            ? document.querySelectorAll('.supplemental-row')
            : document.querySelectorAll('.supplemental-card');
        let visibleCount = 0;

        items.forEach(item => {
            if (item.style.display !== 'none') {
                visibleCount++;
            }
        });

        const totalRecordsElement = document.getElementById('totalRecordsCount');
        if (totalRecordsElement) {
            totalRecordsElement.textContent = visibleCount;
        }
    }

    /**
     * Recompute the Total Supplemental / Total Reversion / Net Change footer
     * from the active view's visible items, mirrored into both views' footers.
     */
    function updateFooterTotals() {
        const isTableView = !document.getElementById('supplementalsTableView').classList.contains('hidden');
        const items = isTableView
            ? document.querySelectorAll('.supplemental-row')
            : document.querySelectorAll('.supplemental-card');

        let totalSupplemental = 0;
        let totalReversion = 0;

        items.forEach(item => {
            if (item.style.display === 'none') return;
            const amount = parseFloat(item.dataset.amount) || 0;
            if (item.dataset.type === 'Supplemental') {
                totalSupplemental += amount;
            } else if (item.dataset.type === 'Reversion') {
                totalReversion += amount;
            }
        });

        const netChange = totalSupplemental - totalReversion;
        const format = (n) => n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        ['totalSourceFooter', 'totalSourceFooterTable'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = format(totalSupplemental);
        });
        ['totalRecipientFooter', 'totalRecipientFooterTable'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = format(totalReversion);
        });
        ['netChangeFooter', 'netChangeFooterTable'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = format(netChange);
            el.classList.remove('text-red-700', 'bg-red-100', 'dark:bg-red-900', 'dark:text-red-300', 'text-blue-700', 'bg-blue-100', 'dark:bg-blue-900', 'dark:text-blue-300');
            if (netChange < 0) {
                el.classList.add('text-red-700', 'bg-red-100', 'dark:bg-red-900', 'dark:text-red-300');
            } else {
                el.classList.add('text-blue-700', 'bg-blue-100', 'dark:bg-blue-900', 'dark:text-blue-300');
            }
        });

        // Always show the card-view footer
        const footer = document.getElementById('supplementalsFooter');
        if (footer) footer.style.display = '';
    }

    /**
     * Card / List view toggle
     */
    function updateSupplementalsViewButtons(view) {
        const cardBtn = document.getElementById('supplementalsCardViewBtn');
        const listBtn = document.getElementById('supplementalsListViewBtn');
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

    window.setSupplementalsView = function(view) {
        const cardView = document.getElementById('supplementalsCardView');
        const tableView = document.getElementById('supplementalsTableView');

        if (view === 'table') {
            cardView.classList.add('hidden');
            tableView.classList.remove('hidden');
        } else {
            view = 'card';
            tableView.classList.add('hidden');
            cardView.classList.remove('hidden');
        }

        try {
            localStorage.setItem('supplementalsView', view);
        } catch (e) {
            // localStorage unavailable — view just won't persist
        }

        updateSupplementalsViewButtons(view);

        const searchInput = document.getElementById('searchInput');
        filterActiveSupplementalsView(searchInput ? searchInput.value : '');
    };

    // Initial setup
    document.addEventListener('DOMContentLoaded', () => {
        // Hide context menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target)) {
                hideSupplementalContextMenu();
            }
        });

        // Apply saved view preference (defaults to table)
        let savedView = 'table';
        try {
            savedView = localStorage.getItem('supplementalsView') || 'table';
        } catch (e) {
            // ignore
        }
        setSupplementalsView(savedView);

        // Add search input listener for real-time updates
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterActiveSupplementalsView(this.value);
            });
        }
    });
    })();

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

    /* Highlight when context menu is open (card or list/table view) */
    .supplemental-item.context-menu-active {
        background-color: rgba(59, 130, 246, 0.1);
        transition: background-color 0.2s ease-in-out;
    }

    .dark .supplemental-item.context-menu-active {
        background-color: rgba(59, 130, 246, 0.2);
    }
</style>