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
                {{ __('Realignment / Augmentation') }}

                @php
                $filters = [];

                if (request('office_allotment_class_id')) {
                    $officeClass = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_id'));
                    if ($officeClass) {
                        $filters[] = $officeClass->offices->office_abbreviation . ' - ' . $officeClass->allotmentClass->class;
                    }
                }
                if (request('realignment_type_filter')) {
                    $filters[] = request('realignment_type_filter');
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

        <form id="filterForm" method="GET" action="{{ route('realignments.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

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
                            <option value="{{ $year1 }}" {{ $selectedYear == $year1 ? 'selected' : '' }}>{{ $year1 }}</option>
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

        @php
            $activeFilterChips = [];

            if (request('office_allotment_class_id')) {
                $officeClass = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_id'));
                if ($officeClass) {
                    $activeFilterChips[] = [
                        'label' => $officeClass->offices->office_abbreviation . ' - ' . $officeClass->allotmentClass->class,
                        'url' => '?' . http_build_query(request()->except(['office_allotment_class_id', 'page'])),
                    ];
                }
            }
            if (request('realignment_type_filter')) {
                $activeFilterChips[] = [
                    'label' => 'Type: ' . request('realignment_type_filter'),
                    'url' => '?' . http_build_query(request()->except(['realignment_type_filter', 'page'])),
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
                            <button type="button" onclick="clearSearchFilter()" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Clear">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        @else
                            <a href="{{ $chip['url'] }}" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Remove filter">
                                <i class="fas fa-times text-[10px]"></i>
                            </a>
                        @endif
                    </span>
                @endforeach
                <a href="{{ route('realignments.index') }}" class="text-xs text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 underline ml-1">
                    Clear all
                </a>
            </div>
        @endif
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex flex-col gap-3 md:flex-row md:justify-between md:items-center mb-4">
                <div class="flex-shrink-0">
                    @can('create realignments')
                    <button onclick="openCreateRealignmentModal()" class="text-blue-600 inline-flex items-center justify-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center w-full md:w-auto dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Create Realignment | Augmentation') }}
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
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for realignments" class="form-control border border-gray-300 rounded-lg w-full px-4 py-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                    </div>
                </div>
            </div>

            <!-- Legend / column headers for the Source | Recipient split -->
            <div class="hidden md:grid grid-cols-[1fr_28px_1fr] items-center gap-0 mb-1 px-1">
                <div class="text-center text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300 py-1">
                    <i class="fas fa-arrow-up mr-1"></i>Source
                </div>
                <div class="flex justify-center text-gray-400 dark:text-gray-500">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="text-center text-xs font-bold uppercase tracking-wider text-green-700 dark:text-green-300 py-1">
                    <i class="fas fa-arrow-down mr-1"></i>Recipient
                </div>
            </div>

            <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
            <div class="max-h-[720px] overflow-y-auto p-2 space-y-3 bg-gray-50 dark:bg-gray-900" id="realignmentsContainer">
                @php
                    $totalSource = 0;
                    $totalRecipient = 0;
                    $groupedRealignments = $realignments->groupBy('realignment_no');
                @endphp

                @forelse ($groupedRealignments as $realignmentNo => $group)
                    @php
                        $sources = $group->where('type', 'Source')->values();
                        $recipients = $group->where('type', 'Recipient')->values();
                        $groupSourceTotal = $sources->sum('amount');
                        $groupRecipientTotal = $recipients->sum('amount');
                        $totalSource += $groupSourceTotal;
                        $totalRecipient += $groupRecipientTotal;
                        $isBalanced = abs($groupSourceTotal - $groupRecipientTotal) < 0.01;
                        $firstItem = $group->first();
                        $fileCount = \App\Models\RealignmentFile::where('realignment_no', $realignmentNo)->count();
                        $searchText = strtolower(collect([
                            $realignmentNo,
                            $firstItem->realignment_date,
                            $firstItem->basis,
                            $group->pluck('officeAllotmentClass.office_abbreviation')->implode(' '),
                            $group->pluck('officeAllotmentClass.class')->implode(' '),
                            $group->pluck('appropriation.programs')->implode(' '),
                            $group->pluck('appropriation.account_code')->implode(' '),
                            $group->pluck('appropriation.description')->implode(' '),
                        ])->implode(' '));
                    @endphp
                    <div class="realignment-group bg-white dark:bg-gray-800 border-2 border-gray-400 dark:border-gray-500 rounded-lg overflow-hidden shadow-sm text-xs"
                         data-search-text="{{ $searchText }}"
                         data-realignment-no="{{ $realignmentNo }}">

                        <!-- Group Header -->
                        <div class="flex flex-wrap justify-between items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-600">
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                <span class="font-bold text-gray-800 dark:text-gray-100">
                                    <i class="fas fa-hashtag mr-1 text-blue-500"></i>{{ $realignmentNo }}
                                </span>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <i class="far fa-calendar mr-1"></i>{{ $firstItem->realignment_date }}
                                </span>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold">Basis:</span> {{ $firstItem->basis }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($isBalanced)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300" title="Source and Recipient totals match">
                                        <i class="fas fa-check-circle"></i>Balanced
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300" title="Source and Recipient totals do not match">
                                        <i class="fas fa-triangle-exclamation"></i>Mismatch
                                    </span>
                                @endif
                                <button onclick="openRealignmentFilesModal('{{ $realignmentNo }}')"
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
                        </div>

                        <!-- Source (left) | Recipient (right) -->
                        <div class="grid grid-cols-1 md:grid-cols-[1fr_24px_1fr] divide-y md:divide-y-0 divide-gray-200 dark:divide-gray-700">

                            <!-- SOURCE COLUMN -->
                            <div class="bg-blue-50/50 dark:bg-blue-950/10 md:border-r md:border-gray-200 md:dark:border-gray-700">
                                <div class="md:hidden px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 font-bold uppercase tracking-wide">
                                    Source
                                </div>
                                @forelse($sources as $realignment)
                                    <div class="realignment-entry px-3 py-2 border-b-2 border-blue-700 dark:border-blue-700 last:border-b-0 hover:bg-blue-100/60 dark:hover:bg-blue-900/30 cursor-pointer"
                                         oncontextmenu="showRealignmentContextMenu(event, this)"
                                         data-realignment='@json($realignment)'
                                         data-realignment-no="{{ $realignment->realignment_no }}"
                                         data-type="Source">
                                        <div class="flex flex-wrap justify-between items-start gap-2 pb-1.5 mb-1.5 border-b border-blue-500 dark:border-blue-700">
                                            <div class="font-semibold text-gray-700 dark:text-gray-200 break-words">
                                                {{ $realignment->officeAllotmentClass->office_abbreviation ?? '-' }} - {{ $realignment->officeAllotmentClass->class ?? '-' }}
                                            </div>
                                            <div class="px-2 py-1 rounded text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300 font-bold text-sm tabular-nums whitespace-nowrap">
                                                {{ number_format($realignment->amount, 2) }}
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-0.5 text-gray-500 dark:text-gray-400">
                                            <span class="text-gray-500 dark:text-gray-500">Account Code:</span>
                                            <span class="font-medium text-gray-600 dark:text-gray-300 break-words">{{ $realignment->appropriation->account_code ?? '-' }}</span>
                                            <span class="text-gray-500 dark:text-gray-500">Program:</span>
                                            <span class="text-gray-600 dark:text-gray-300 break-words">{{ $realignment->appropriation->programs ?? '-' }}</span>
                                            <span class="text-gray-500 dark:text-gray-500">Description:</span>
                                            <span class="text-gray-600 dark:text-gray-300 break-words">{{ $realignment->appropriation->description ?? '-' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-3 py-4 text-center text-gray-400 dark:text-gray-500 italic">
                                        No source entry
                                    </div>
                                @endforelse
                                <div class="flex justify-between items-center px-3 py-1.5 bg-blue-100/70 dark:bg-blue-900/40 border-t-2 border-blue-200 dark:border-blue-700">
                                    <span class="font-bold text-blue-800 dark:text-blue-300">Subtotal (Source)</span>
                                    <span class="font-bold text-blue-800 dark:text-blue-300 tabular-nums">{{ number_format($groupSourceTotal, 2) }}</span>
                                </div>
                            </div>

                            <!-- FLOW CONNECTOR (desktop only) -->
                            <div class="hidden md:flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                                <i class="fas fa-arrow-right {{ $isBalanced ? 'text-emerald-500' : 'text-amber-500' }}"></i>
                            </div>

                            <!-- RECIPIENT COLUMN -->
                            <div class="bg-green-50/50 dark:bg-green-950/10 md:border-l md:border-gray-200 md:dark:border-gray-700">
                                <div class="md:hidden px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 font-bold uppercase tracking-wide">
                                    Recipient
                                </div>
                                @forelse($recipients as $realignment)
                                    <div class="realignment-entry px-3 py-2 border-b-2 border-green-700 dark:border-green-700 last:border-b-0 hover:bg-green-100/60 dark:hover:bg-green-900/30 cursor-pointer"
                                         oncontextmenu="showRealignmentContextMenu(event, this)"
                                         data-realignment='@json($realignment)'
                                         data-realignment-no="{{ $realignment->realignment_no }}"
                                         data-type="Recipient">
                                        <div class="flex flex-wrap justify-between items-start gap-2 pb-1.5 mb-1.5 border-b border-green-500 dark:border-green-700">
                                            <div class="font-semibold text-gray-700 dark:text-gray-200 break-words">
                                                {{ $realignment->officeAllotmentClass->office_abbreviation ?? '-' }} - {{ $realignment->officeAllotmentClass->class ?? '-' }}
                                            </div>
                                            <div class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-bold text-sm tabular-nums whitespace-nowrap">
                                                {{ number_format($realignment->amount, 2) }}
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-0.5 text-gray-500 dark:text-gray-400">
                                            <span class="text-gray-500 dark:text-gray-500">Account Code:</span>
                                            <span class="font-medium text-gray-600 dark:text-gray-300 break-words">{{ $realignment->appropriation->account_code ?? '-' }}</span>
                                            <span class="text-gray-500 dark:text-gray-500">Program:</span>
                                            <span class="text-gray-600 dark:text-gray-300 break-words">{{ $realignment->appropriation->programs ?? '-' }}</span>
                                            <span class="text-gray-500 dark:text-gray-500">Description:</span>
                                            <span class="text-gray-600 dark:text-gray-300 break-words">{{ $realignment->appropriation->description ?? '-' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-3 py-4 text-center text-gray-400 dark:text-gray-500 italic">
                                        No recipient entry
                                    </div>
                                @endforelse
                                <div class="flex justify-between items-center px-3 py-1.5 bg-green-100/70 dark:bg-green-900/40 border-t-2 border-green-200 dark:border-green-700">
                                    <span class="font-bold text-green-800 dark:text-green-300">Subtotal (Recipient)</span>
                                    <span class="font-bold text-green-800 dark:text-green-300 tabular-nums">{{ number_format($groupRecipientTotal, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                        No Realignments found
                    </div>
                @endforelse

                <!-- Shown by JS when a search query matches no visible groups -->
                <div id="noSearchResultsMsg" class="hidden px-3 py-10 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-magnifying-glass text-2xl mb-2 block text-gray-300 dark:text-gray-600"></i>
                    No realignments match <span id="noSearchResultsQuery" class="font-semibold text-gray-700 dark:text-gray-300"></span>
                    <button type="button" onclick="clearSearchFilter()" class="block mx-auto mt-2 text-xs text-blue-600 hover:underline dark:text-blue-400">
                        Clear search
                    </button>
                </div>
            </div>

            <!-- Totals Footer -->
            <div id="realignmentsFooter" class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-300 dark:divide-gray-600 bg-gray-200 dark:bg-gray-900 font-bold text-gray-700 dark:text-gray-200 border-t-2 border-gray-700 dark:border-gray-600">
                <div class="text-center text-sm px-1 py-3">
                    Total Source:
                    <span id="totalSourceFooter" class="px-2 py-1 rounded text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300 font-bold text-base tabular-nums ml-2">
                        {{ number_format($totalSource, 2) }}
                    </span>
                </div>
                <div class="text-center text-sm px-1 py-3">
                    Total Recipient:
                    <span id="totalRecipientFooter" class="px-2 py-1 rounded text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 font-bold text-base tabular-nums ml-2">
                        {{ number_format($totalRecipient, 2) }}
                    </span>
                </div>
            </div>
            </div>
             <!-- Pagination -->
            <div class="mt-4">
                @if ($perPage != 'all')
                {{ $realignments->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div id="realignmentContextMenu"
        class="fixed hidden w-48 max-w-[calc(100vw-16px)] bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-400 rounded-lg shadow-2xl z-50 dark:from-blue-900 dark:to-blue-800 dark:border-blue-600"
        style="display: none;">
        <button id="contextFiles"
                class="w-full text-left block px-4 py-2 text-xs text-green-900 hover:bg-green-200 dark:text-green-100 dark:hover:bg-green-700 transition-colors duration-150">
            <i class="fas fa-file-upload mr-2 text-green-600"></i>Files
        </button>
        @can('edit realignments')
        <button id="contextEdit"
                class="w-full text-left block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
            <i class="fas fa-edit mr-2 text-blue-600"></i>Edit
        </button>
        @endcan
         @can('delete realignments')
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

    @include('realignments.modal.create')
    @include('realignments.modal.edit')
    @include('realignments.modal.delete')
    @include('realignments.modal.realignment_files')

<script>
    (function() {
    const menu = document.getElementById('realignmentContextMenu');

    // showContextMenu receives the mouse event and the entry element
    window.showRealignmentContextMenu = function(event, row) {
        event.preventDefault();
        event.stopPropagation();

        if (!menu) return;

        // Remove highlight from previously selected entry
        document.querySelectorAll('.realignment-entry.context-menu-active').forEach(r => {
            r.classList.remove('context-menu-active');
        });

        // Highlight the current entry
        row.classList.add('context-menu-active');
        window.currentContextMenuRow = row;

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
            // Show below cursor, tight to cursor position
            top = mouseY;
        } else if (spaceAbove > menuHeight + 20) {
            // Show above cursor, positioned lower so it's beside cursor
            top = mouseY - menuHeight + 40;
        } else {
            // Not enough room either way (common on short mobile viewports) —
            // pin it as low as it can go while staying fully on screen.
            top = viewportHeight - menuHeight - 8;
        }
        // Clamp so it never renders off the top/bottom edge
        top = Math.min(Math.max(top, 8), viewportHeight - 8);

        // Calculate left position (tight to cursor, with edge collision detection)
        let left = mouseX + 2;

        // Check if menu goes off screen to the right
        if (left + menuWidth > viewportWidth) {
            left = mouseX - menuWidth - 2;
        }

        // Ensure menu doesn't go off screen to the left
        if (left < 8) {
            left = 8;
        }

        // Position menu
        menu.style.position = 'fixed';
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
        menu.style.display = 'block';
        menu.classList.remove('hidden');

        // Get realignment data
        const realignment = row.dataset.realignment ? JSON.parse(row.dataset.realignment) : null;
        if (realignment) {
            // Files button
            const filesBtn = menu.querySelector('#contextFiles');
            if (filesBtn && realignment.realignment_no) {
                filesBtn.onclick = () => {
                    hideRealignmentContextMenu();
                    openRealignmentFilesModal(realignment.realignment_no);
                };
            }

            // Edit button
            const editBtn = menu.querySelector('#contextEdit');
            if (editBtn) {
                editBtn.onclick = () => {
                    hideRealignmentContextMenu();
                    openEditRealignmentModal(realignment);
                };
            }

            // Delete button
            const deleteBtn = menu.querySelector('#contextDelete');
            if (deleteBtn && realignment.id) {
                deleteBtn.onclick = () => {
                    hideRealignmentContextMenu();
                    openDeleteRealignmentModal(
                        realignment.id,
                        realignment.realignment_no,
                        realignment.type,
                        realignment.amount,
                        realignment.appropriations_id
                    );
                };
            }

            // Bulk Delete button
            const bulkDeleteBtn = menu.querySelector('#contextBulkDelete');
            if (bulkDeleteBtn && realignment.id && realignment.realignment_no) {
                bulkDeleteBtn.onclick = () => {
                    hideRealignmentContextMenu();
                    openBulkDeleteRealignmentModal(
                        realignment.realignment_no,
                        realignment.id
                    );
                };
            }
        }

        // Add event listeners with delay
        setTimeout(() => {
            document.addEventListener('click', hideRealignmentContextMenu);
            window.addEventListener('resize', hideRealignmentContextMenu);
            window.addEventListener('scroll', hideRealignmentContextMenu, { passive: true });
            const container = document.getElementById('realignmentsContainer');
            if (container) {
                container.addEventListener('scroll', hideRealignmentContextMenu, { passive: true });
            }
        }, 30);
    };

    function hideRealignmentContextMenu() {
        if (!menu) return;
        menu.classList.add('hidden');
        menu.style.display = 'none';

        // Remove highlight when menu is closed
        if (window.currentContextMenuRow) {
            window.currentContextMenuRow.classList.remove('context-menu-active');
            window.currentContextMenuRow = null;
        }

        // Remove event listeners
        document.removeEventListener('click', hideRealignmentContextMenu);
        window.removeEventListener('resize', hideRealignmentContextMenu);
        window.removeEventListener('scroll', hideRealignmentContextMenu);
        const container = document.getElementById('realignmentsContainer');
        if (container) {
            container.removeEventListener('scroll', hideRealignmentContextMenu);
        }
    }

    // Hide on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideRealignmentContextMenu();
    });

    /**
     * Filter realignment group cards based on search input
     */
    function filterRealignments(searchValue) {
        const groups = document.querySelectorAll('.realignment-group');
        const lowerSearch = searchValue.toLowerCase().trim();
        let visibleCount = 0;

        groups.forEach(group => {
            const searchText = group.dataset.searchText || group.textContent.toLowerCase();
            if (searchText.includes(lowerSearch)) {
                group.style.display = '';
                visibleCount++;
            } else {
                group.style.display = 'none';
            }
        });

        // Show a "no results" message when a search query matches nothing
        const noResultsMsg = document.getElementById('noSearchResultsMsg');
        const noResultsQuery = document.getElementById('noSearchResultsQuery');
        if (noResultsMsg) {
            if (lowerSearch.length > 0 && visibleCount === 0 && groups.length > 0) {
                if (noResultsQuery) noResultsQuery.textContent = `"${searchValue.trim()}"`;
                noResultsMsg.classList.remove('hidden');
            } else {
                noResultsMsg.classList.add('hidden');
            }
        }

        // Update total records count and footer totals
        updateTotalRecordsCount();
        updateFooterTotals();
    }
    window.filterRealignments = filterRealignments;

    /**
     * Clear the search box (used by the active-filter chip and the no-results message)
     */
    window.clearSearchFilter = function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
            filterRealignments('');
            searchInput.focus();
        }
    };

    function updateFooterTotals() {
        let totalSource = 0;
        let totalRecipient = 0;

        document.querySelectorAll('.realignment-group').forEach(group => {
            if (group.style.display === 'none') return;

            group.querySelectorAll('.realignment-entry[data-type="Source"]').forEach(entry => {
                const realignment = entry.dataset.realignment ? JSON.parse(entry.dataset.realignment) : null;
                if (realignment) {
                    totalSource += parseFloat(realignment.amount) || 0;
                }
            });
            group.querySelectorAll('.realignment-entry[data-type="Recipient"]').forEach(entry => {
                const realignment = entry.dataset.realignment ? JSON.parse(entry.dataset.realignment) : null;
                if (realignment) {
                    totalRecipient += parseFloat(realignment.amount) || 0;
                }
            });
        });

        const sourceEl = document.getElementById('totalSourceFooter');
        const recipientEl = document.getElementById('totalRecipientFooter');
        if (sourceEl) {
            sourceEl.textContent = totalSource.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        if (recipientEl) {
            recipientEl.textContent = totalRecipient.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        // Always show the footer
        const footer = document.getElementById('realignmentsFooter');
        if (footer) footer.style.display = '';
    }

    /**
     * Update total records count based on visible groups (counted per realignment_no)
     */
    function updateTotalRecordsCount() {
        const groups = document.querySelectorAll('.realignment-group');
        let visibleCount = 0;

        groups.forEach(group => {
            if (group.style.display !== 'none') {
                visibleCount++;
            }
        });

        const totalRecordsElement = document.getElementById('totalRecordsCount');
        if (totalRecordsElement) {
            totalRecordsElement.textContent = visibleCount;
        }
    }

    // Initial setup
    document.addEventListener('DOMContentLoaded', () => {
        // Hide context menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target)) {
                hideRealignmentContextMenu();
            }
        });

        // Initialize total records count
        updateTotalRecordsCount();
        updateFooterTotals();

        // Add search input listener for real-time updates
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterRealignments(this.value);
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

    function closeDropdown() {
        // Example: hide any elements with a class of 'dropdown' or 'autocomplete-dropdown'
        document.querySelectorAll('.dropdown, .autocomplete-dropdown').forEach(drop => {
            drop.classList.add('hidden');
        });
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

    /* Entry highlight when context menu is open */
    .realignment-entry.context-menu-active {
        background-color: rgba(59, 130, 246, 0.15);
        transition: background-color 0.2s ease-in-out;
    }

    .dark .realignment-entry.context-menu-active {
        background-color: rgba(59, 130, 246, 0.25);
    }
</style>
    </div>
</x-app-layout>