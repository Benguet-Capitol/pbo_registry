<x-app-layout>
    
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center">
            <!-- Left: Allotment Class Title with Filters -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Allotment Class') }}

                @php
                $filters = [];

                if (request('office_filter')) {
                    $officeFilter = $offices->firstWhere('id', request('office_filter'));
                    if ($officeFilter) $filters[] = $officeFilter->office_abbreviation;
                }
                if (request('allotment_class_filter')) {
                    $classFilter = $allotmentClasses->firstWhere('class', request('allotment_class_filter'));
                    if ($classFilter) $filters[] = $classFilter->description;
                }
                if (request('fund_source_filter')) {
                    $filters[] = request('fund_source_filter');
                }
                @endphp

                @if (count($filters) > 0)
                    <span class="text-lg"> | </span>
                    <span class="text-blue-800 dark:text-blue-400">{{ implode(' / ', $filters) }}</span>
                @endif
                <span class="text-blue-800 dark:text-blue-400">
                    (CY {{ $selectedYear }})
                </span>
            </h3>

            <!-- Right: Breadcrumb Navigation -->
            @if(isset($breadcrumb))
            <nav class="text-xs text-gray-600 dark:text-gray-300" aria-label="Breadcrumb">
                <ol class="list-none p-0 flex flex-wrap items-center gap-x-1 gap-y-1 rtl:space-x-reverse">
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

    <!-- Unified Filter Section -->
    <form method="GET" action="{{ route('office_allotment_classes.index') }}" class="bg-white p-6 rounded-lg shadow-md mb-3 dark:bg-gray-800 transition-all duration-300 ease-in-out hover:shadow-lg dark:shadow-gray-900/50" id="filterForm">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
            Filters
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-center">

            <!-- Year Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="year1"
                    id="year1"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:focus:ring-blue-400 hover:border-gray-400 dark:hover:border-gray-500"
                    data-default="{{ date('Y') }}" 
                    onchange="this.form.submit()"
                >
                    @foreach($availableYears as $year1)
                        <option value="{{ $year1 }}" {{ $year1 == $selectedYear ? 'selected' : '' }}>{{ $year1 }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Office Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="office_filter"
                    id="office_filter"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:focus:ring-blue-400 hover:border-gray-400 dark:hover:border-gray-500"
                    data-default="" 
                    onchange="this.form.submit()"
                >
                    <option value="">All Office</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ request('office_filter') == $office->id ? 'selected' : '' }}>{{ $office->office_abbreviation }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Allotment Class Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="allotment_class_filter"
                    id="allotment_class_filter"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:focus:ring-blue-400 hover:border-gray-400 dark:hover:border-gray-500"
                    data-default=""
                    onchange="this.form.submit()"
                >
                    <option value="">All Allotment Class</option>
                    @foreach($allotmentClasses as $class)
                        <option value="{{ $class->class }}" {{ request('allotment_class_filter') == $class->class ? 'selected' : '' }}>{{ $class->description }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Fund Source Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="fund_source_filter"
                    id="fund_source_filter"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:focus:ring-blue-400 hover:border-gray-400 dark:hover:border-gray-500"
                    data-default=""
                    onchange="this.form.submit()"
                >
                    <option value="">All Fund Source</option>
                    @foreach($fund_sources as $fund_source)
                        <option value="{{ $fund_source->source }}" {{ request('fund_source_filter') == $fund_source->source ? 'selected' : '' }}>{{ $fund_source->source }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Per Page Dropdown -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="per_page"
                    id="perPage"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-white transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:focus:ring-blue-400 hover:border-gray-400 dark:hover:border-gray-500"
                    data-default="all"
                    onchange="this.form.submit()"
                >
                    <option value="10" {{ request('per_page', 'all') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 'all') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 'all') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 'all') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page', 'all') == 'all' ? 'selected' : '' }}>All</option>
                </x-form.select>
            </div>
        </div>
    </form>

    {{-- Success messages --}}
    @if (session('status'))
        @php
            $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-600 dark:text-green-200';
            if (str_contains(session('status'), 'updated successfully')) {
                $alertType = 'bg-blue-100 border-blue-400 text-blue-700 dark:bg-blue-900 dark:border-blue-600 dark:text-blue-200';
            } elseif (str_contains(session('status'), 'deleted successfully')) {
                $alertType = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-600 dark:text-red-200';
            }
        @endphp

        <div class="border-l-4 p-4 mb-4 flex justify-between items-start {{ $alertType }} rounded-r-lg shadow-md animate-slideInDown transition-all duration-500 ease-out" role="alert">
            <p class="flex-1">{!! session('status') !!}</p>
            <button type="button" class="ml-4 text-2xl font-semibold leading-none hover:opacity-70 transition-opacity duration-200" onclick="this.closest('div[role=alert]').classList.add('animate-slideOutUp'); setTimeout(() => this.closest('div[role=alert]').remove(), 300);">
                &times;
            </button>
        </div>
    @endif

    {{-- Error/Warning messages --}}
    @if (session('error'))
        <div class="border-l-4 p-4 mb-4 flex justify-between items-start bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-600 dark:text-red-200 rounded-r-lg shadow-md animate-slideInDown transition-all duration-500 ease-out" role="alert">
            <p class="flex-1">{!! session('error') !!}</p>
            <button type="button" class="ml-4 text-2xl font-semibold leading-none hover:opacity-70 transition-opacity duration-200" onclick="this.closest('div[role=alert]').classList.add('animate-slideOutUp'); setTimeout(() => this.closest('div[role=alert]').remove(), 300);">
                &times;
            </button>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800 transition-all duration-300 ease-in-out">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-4 gap-4">
                <!-- Create Button -->
                @can('create office allotment classes')
                <button onclick="openCreateOfficeAllotmentClassModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 self-start">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Create Allotment Class per Office') }}
                </button>
                @endcan
                <!-- Total Records and Search Input -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                    <!-- Total Records -->
                    <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 shrink-0">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $totalRecords }}</span>
                    </div>
                    <!-- Search Input -->
                    <div class="flex items-center gap-2 w-full sm:min-w-96 sm:w-auto">
                        <i class="fas fa-search text-gray-400 shrink-0"></i>
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full min-w-0 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:focus:ring-blue-400" />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
            <div class="max-h-[720px] overflow-y-auto">
            <table id="employeesTable" class="text-center w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400 mb-10">
                <thead class="text-center text-xs border-b-2 border-gray-700 text-gray-700 bg-gray-200 border-t-2 dark:bg-gray-900 dark:text-gray-400 sticky top-0 z-10 transition-colors duration-200">
                    <tr>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer border-l-4 border-l-gray-500">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'office_abbreviation', 'sort_order' => $sortBy == 'office_abbreviation' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                                <span>Office</span>
                                <span class="transition-transform duration-200">
                                @if($sortBy == 'office_abbreviation')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                                </span>
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'class', 'sort_order' => $sortBy == 'class' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                                <span>Allotment Class</span>
                                <span class="transition-transform duration-200">
                                @if($sortBy == 'class')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                                </span>
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'fund_source', 'sort_order' => $sortBy == 'fund_source' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                                <span>Fund Source</span>
                                <span class="transition-transform duration-200">
                                @if($sortBy == 'fund_source')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                                </span>
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'fund', 'sort_order' => $sortBy == 'fund' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                                <span>Fund</span>
                                <span class="transition-transform duration-200">
                                @if($sortBy == 'fund')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                                </span>
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'fpp_code', 'sort_order' => $sortBy == 'fpp_code' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                                <span>FPP Code</span>
                                <span class="transition-transform duration-200">
                                @if($sortBy == 'fpp_code')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                                </span>
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'responsibility_code', 'sort_order' => $sortBy == 'responsibility_code' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                                <span>Responsibility Code</span>
                                <span class="transition-transform duration-200">
                                @if($sortBy == 'responsibility_code')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                                </span>
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200 cursor-pointer">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'total_appropriation', 'sort_order' => $sortBy == 'total_appropriation' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}" class="inline-flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                                <span>Approved Appropriation</span>
                                <span class="transition-transform duration-200">
                                @if($sortBy == 'total_appropriation')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody id="officeAllotmentBody">
                    @forelse ($office_allotment_classes as $office_allotment_class)
                        @php
                            // Color-codes the Fund Source badge so the three budget sources
                            // (Annual Budget / Supplemental Budget / Continuing Capital Outlay)
                            // are recognizable at a glance, consistent across offices.
                            $classBadgeColor = match ($office_allotment_class->fund_source) {
                                'Annual Budget' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400',
                                'Supplemental Budget' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400',
                                'Continuing Capital Outlay' => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400',
                                default => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
                            };
                        @endphp
                        <tr
                            class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-800/60' : 'bg-white dark:bg-gray-800' }} border-b dark:border-gray-700 text-gray-600 border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer relative transition-colors duration-200 ease-in-out"
                            ondblclick="window.location.href='{{ route('appropriations.index', ['office_allotment_class_id' => $office_allotment_class->id]) }}'"
                            oncontextmenu="showContextMenu(event, this)"
                            data-id="{{ $office_allotment_class->id }}"
                            data-office="{{ e($office_allotment_class->offices->office_abbreviation ?? '') }}"
                            data-class="{{ e($office_allotment_class->allotmentClass->description ?? '') }}"
                            data-json='@json($office_allotment_class)'
                        >
                            <td class="px-1 py-2 text-center transition-colors duration-200 border-l-4 border-l-gray-500">
                                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold inline-block transition-all duration-200 transform hover:scale-105">{{ $office_allotment_class->offices->office_abbreviation }}</span>
                            </td>
                            <td class="font-semibold px-1 py-2 text-gray-600 dark:text-gray-300 transition-colors duration-200">
                                {{ $office_allotment_class->allotmentClass->description ?? 'N/A' }}
                            </td>
                            <td class="px-1 py-2 text-center transition-colors duration-200">
                                <span class="px-2 py-1 rounded {{ $classBadgeColor }} font-semibold inline-block transition-all duration-200 transform hover:scale-105">{{ $office_allotment_class->fund_source }}</span>
                            </td>
                            <td class="px-1 py-2 text-gray-600 dark:text-gray-300 transition-colors duration-200">
                                {{ $office_allotment_class->fund }}
                            </td>
                            <td class="font-semibold px-1 py-2 text-gray-600 dark:text-gray-300 transition-colors duration-200">
                                {{ $office_allotment_class->fpp_code }}
                            </td>
                            <td class="px-1 py-2 text-gray-600 dark:text-gray-300 transition-colors duration-200">
                                {{ $office_allotment_class->responsibility_code }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                @if($office_allotment_class->total_appropriation > 0)
                                    <span class="px-2 py-1 rounded bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-semibold inline-block transition-all duration-200 transform hover:scale-105">
                                        {{ number_format($office_allotment_class->total_appropriation, 2) }}
                                    </span>
                                @elseif($office_allotment_class->total_appropriation == 0)
                                    <span class="px-2 py-1 rounded bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 font-semibold inline-block transition-all duration-200 transform hover:scale-105">
                                        {{ number_format($office_allotment_class->total_appropriation, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-300 transition-colors duration-200">
                                        {{ number_format($office_allotment_class->total_appropriation, 2) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 italic">
                                No Office Allotment Classes found
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot class="bg-gray-200 dark:bg-gray-900 border-t-2 border-b-2 border-gray-700 dark:border-gray-600">
                    <tr>
                        <td colspan="6" class="text-right text-sm font-bold px-1 py-3 text-gray-700 dark:text-gray-300 border-l-4 border-l-gray-500">
                            Total Approved Appropriation:
                        </td>
                        <td id="totalAppropriationFooter" class="px-1 py-3 font-bold text-sm text-gray-900 dark:text-white"></td>
                    </tr>
                </tfoot>
            </table>
            </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                @if ($perPage != 'all')
                    {{ $office_allotment_classes->appends(request()->query())->links() }}
                @endif
            </div>

            <!-- Single Context Menu (outside tbody) -->
            <div id="contextMenu" class="hidden fixed w-48 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-400 rounded-lg shadow-2xl z-50 dark:from-blue-900 dark:to-blue-800 dark:border-blue-600">
                <a id="contextAccounts" href="#" class="block px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 transition-colors duration-150 first:rounded-t-md">
                    <i class="fas fa-stream mr-2 text-blue-600"></i> Accounts
                </a>

                @can('edit office allotment classes')
                <button id="contextEdit" type="button" class="w-full text-left px-4 py-2 text-xs text-blue-900 hover:bg-blue-200 dark:text-blue-100 dark:hover:bg-blue-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150">
                    <i class="fas fa-edit mr-2 text-blue-600"></i> Edit
                </button>
                @endcan

                @can('delete office allotment classes')
                <button id="contextDelete" type="button" class="w-full text-left px-4 py-2 text-xs text-red-700 hover:bg-red-200 dark:text-red-300 dark:hover:bg-red-700 border-t border-blue-300 dark:border-blue-600 transition-colors duration-150 last:rounded-b-md">
                    <i class="fas fa-trash mr-2 text-red-600"></i> Delete
                </button>
                @endcan
            </div>
        </div>
    </div>

    @include('office_allotment_classes.modal.create')
    @include('office_allotment_classes.modal.delete')
    @include('office_allotment_classes.modal.edit')

</x-app-layout>

<script>
(function () {
    const menu = document.getElementById('contextMenu');
    const accountsLink = document.getElementById('contextAccounts');
    const editBtn = document.getElementById('contextEdit');
    const deleteBtn = document.getElementById('contextDelete');

    // showContextMenu receives the mouse event and the <tr> element (this)
    window.showContextMenu = function (event, row) {
        event.preventDefault();
        event.stopPropagation();

        // Remove highlight from previously selected row
        document.querySelectorAll('table tbody tr.context-menu-active').forEach(r => {
            r.classList.remove('context-menu-active');
        });
        
        // Highlight the current row
        row.classList.add('context-menu-active');
        window.currentContextMenuRow = row;

        // Get element positions
        const menuHeight = 200; // Approximate menu height
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

        // read data attributes
        const id = row.dataset.id;
        const office = row.dataset.office || '';
        const klass = row.dataset.class || '';
        const rowJson = row.dataset.json ? JSON.parse(row.dataset.json) : null;

        // build links / callbacks
        accountsLink.href = `{{ url('/appropriations') }}?office_allotment_class_id=${encodeURIComponent(id)}`;

        // Position menu
        menu.style.position = 'fixed';
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
        menu.classList.remove('hidden');

        if (editBtn) {
            // pass the JSON if your modal expects the whole record, otherwise just id
            editBtn.onclick = () => {
                if (typeof openEditOfficeAllotmentClassModal === 'function') {
                    openEditOfficeAllotmentClassModal(rowJson ?? { id });
                } else {
                    console.warn('openEditOfficeAllotmentClassModal() not defined');
                }
                hideContextMenu();
            };
        }

        if (deleteBtn) {
            deleteBtn.onclick = () => {
                if (typeof openDeleteModal === 'function') {
                    openDeleteModal(id, office, klass);
                } else {
                    console.warn('openDeleteModal() not defined');
                }
                hideContextMenu();
            };
        }

        // Add event listeners with delay to avoid immediate hide
        setTimeout(() => {
            document.addEventListener('click', hideContextMenu);
            window.addEventListener('resize', hideContextMenu);
            window.addEventListener('scroll', hideContextMenu, { passive: true });
        }, 30);
    };

    function hideContextMenu() {
        if (!menu) return;
        menu.classList.add('hidden');
        document.removeEventListener('click', hideContextMenu);
        window.removeEventListener('resize', hideContextMenu);
        window.removeEventListener('scroll', hideContextMenu);
        // Remove highlight when menu is closed
        if (window.currentContextMenuRow) {
            window.currentContextMenuRow.classList.remove('context-menu-active');
            window.currentContextMenuRow = null;
        }
    }

    // Hide on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideContextMenu();
    });

})();

    function closeAllDropdowns() {
        document.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.add("hidden"));
    }

    // Close dropdown if click happens outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative.inline-block')) {
            closeAllDropdowns();
        }
    });

    // Calculate total appropriation for visible rows only and update total records
    function calculateVisibleTotalAppropriation() {
        const rows = document.querySelectorAll('#employeesTable tbody tr');
        let total = 0;
        let visibleCount = 0;

        rows.forEach(row => {
            // Check if row is visible
            if (row.offsetParent !== null) {
                // Exclude the "No records found" row by checking if it has data attributes
                if (row.dataset.id) {
                    visibleCount++;
                    const cell = row.querySelector('td:nth-child(7)');
                    if (cell) {
                        const value = parseFloat(cell.textContent.replace(/,/g, ''));
                        if (!isNaN(value)) {
                            total += value;
                        }
                    }
                }
            }
        });

        const formattedTotal = total.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('totalAppropriationFooter').textContent = formattedTotal;
        document.getElementById('totalRecordsCount').textContent = visibleCount;
    }

    // Filter table rows based on search input
    function filterTable(searchValue) {
        const rows = document.querySelectorAll('#employeesTable tbody tr');
        const lowerSearch = searchValue.toLowerCase();

        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(lowerSearch)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Attach search listener
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterTable(this.value);
                calculateVisibleTotalAppropriation(); // live update after filter
            });
        }

        calculateVisibleTotalAppropriation(); // initial load
    });

    function updateSelectColors() {
        document.querySelectorAll('.filter-select').forEach(select => {
            const defaultValue = select.getAttribute('data-default') ?? "";
            const selectedValue = select.value;

            if (selectedValue === defaultValue) {
                select.classList.remove('text-gray-900');
                select.classList.add('text-gray-400');
            } else {
                select.classList.remove('text-gray-400');
                select.classList.add('text-gray-900');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', updateSelectColors);

    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', updateSelectColors);
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
