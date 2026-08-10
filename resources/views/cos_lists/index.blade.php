<x-app-layout>
    <x-slot name="header">
    <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
        <!-- Left: COS List Title with Filters -->
        <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
            {{ __('Contract of Services |') }}

            @php
            $officeAbbreviation = null;
            $appropriationDescription = null;

            if (request('office_allotment_class_filter')) {
                $officeClass = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_filter'));
                if ($officeClass) {
                    $officeAbbreviation = $officeClass->offices->office_abbreviation;
                }
            }

            if (request('appropriation_filter')) {
                $selectedAppropriation = $appropriationsForFilter->firstWhere('id', request('appropriation_filter'));
                if ($selectedAppropriation) {
                    $appropriationDescription = $selectedAppropriation->description;
                }
            }
            @endphp

            @if ($officeAbbreviation || $appropriationDescription)
                <span class="text-blue-800 dark:text-blue-400">
                    {{ collect([$officeAbbreviation, $appropriationDescription])->filter()->implode(' - ') }}
                </span>
            @endif
            <span class="text-blue-800 dark:text-blue-400">
                (CY {{ request('year1', date('Y')) }})
            </span>
        </h3>
    </div>
</x-slot>
    <!-- Include modals early so functions are available -->
    @include('cos_lists.modal.create')
    @include('cos_lists.modal.edit')
    @include('cos_lists.modal.delete')
    @include('cos_lists.modal.import')

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

    <!-- Display Validation Errors -->
    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline font-semibold">Please fix the following:</span>
        <ul class="list-disc list-inside mt-1 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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

        <form id="filterForm" method="GET" action="{{ route('cos_lists.index') }}">
            <!-- Hidden inputs to preserve search parameters -->
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="search_column" value="{{ request('search_column') }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

                <!-- Year Filter -->
                <div class="flex items-center space-x-2">
                    <label for="year1" class="sr-only">Year</label>
                    <x-form.select name="year1" id="year1" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="resetDependentFiltersAndSubmit(this)">
                        @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ request('year1', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <!-- Office and Allotment Class Filter -->
                <div class="flex items-center space-x-2">
                    <label for="officeAllotmentClass" class="sr-only">Office & Class</label>
                    <x-form.select name="office_allotment_class_filter" id="officeAllotmentClass" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="resetAppropriationAndSubmit(this)">
                        <option value="">All Allotment Classes per Office</option>
                        @foreach($officeAllotmentClasses as $officeAllotmentClass)
                        <option value="{{ $officeAllotmentClass->id }}" {{ request('office_allotment_class_filter') == $officeAllotmentClass->id ? 'selected' : '' }}>
                            {{ $officeAllotmentClass->offices->office_abbreviation }}
                        </option>
                        @endforeach
                    </x-form.select>
                </div>

                <!-- Accounts Filter -->
                <div class="flex items-center space-x-2">
                    <label for="appropriation_filter" class="sr-only">Accounts</label>
                    <x-form.select
                        name="appropriation_filter"
                        id="appropriation_filter"
                        class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        onchange="this.form.submit()"
                        :disabled="$appropriationsForFilter->isEmpty()">
                        <option value="">
                            {{ $appropriationsForFilter->isEmpty() ? __('Select Office/Class first') : __('All Accounts') }}
                        </option>
                        @foreach($appropriationsForFilter as $appropriation)
                        <option value="{{ $appropriation->id }}" {{ request('appropriation_filter') == $appropriation->id ? 'selected' : '' }}>
                            {{ $appropriation->account_code }} @if($appropriation->description) - {{ $appropriation->description }} @endif
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
                    </x-form.select>
                </div>
            </div>
        </form>

        @php
            $columnLabels = [
                'employee_name' => 'Employee Name',
                'position_title' => 'Position Title',
                'salary_grade' => 'Salary Grade',
                'period' => 'Period',
                'monthly_rate' => 'Monthly Rate',
                'total_amount' => 'Total Amount',
            ];

            $activeFilterChips = [];

            if (request('office_allotment_class_filter')) {
                $officeClassChip = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_filter'));
                if ($officeClassChip) {
                    $activeFilterChips[] = [
                        'label' => 'Office: ' . $officeClassChip->offices->office_abbreviation,
                        'url' => '?' . http_build_query(request()->except(['office_allotment_class_filter', 'appropriation_filter', 'page'])),
                    ];
                }
            }

            if (request('appropriation_filter')) {
                $selectedAppropriationChip = $appropriationsForFilter->firstWhere('id', request('appropriation_filter'));
                if ($selectedAppropriationChip) {
                    $activeFilterChips[] = [
                        'label' => $selectedAppropriationChip->account_code . ($selectedAppropriationChip->description ? ' - ' . $selectedAppropriationChip->description : ''),
                        'url' => '?' . http_build_query(request()->except(['appropriation_filter', 'page'])),
                    ];
                }
            }

            if (request('search')) {
                $searchChipLabel = 'Search: "' . request('search') . '"';
                if (request('search_column') && isset($columnLabels[request('search_column')])) {
                    $searchChipLabel .= ' in ' . $columnLabels[request('search_column')];
                }
                $activeFilterChips[] = [
                    'label' => $searchChipLabel,
                    'url' => '?' . http_build_query(request()->except(['search', 'search_column', 'page'])),
                ];
            }
        @endphp

        @if (count($activeFilterChips) > 0)
            <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Active filters:</span>
                @foreach ($activeFilterChips as $chip)
                    <span class="inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700">
                        {{ $chip['label'] }}
                        <a href="{{ $chip['url'] }}" class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors" title="Remove filter">
                            <i class="fas fa-times text-[10px]"></i>
                        </a>
                    </span>
                @endforeach
                <a href="{{ route('cos_lists.index') }}" class="text-xs text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 underline ml-1">
                    Clear all
                </a>
            </div>
        @endif
    </div>

    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex flex-col gap-3 lg:flex-row lg:justify-between lg:items-center mb-4">
                <!-- Left: Action Button -->
                <div class="flex-shrink-0">
                    <button onclick="openCreateCOSListModal()" class="text-blue-600 inline-flex items-center justify-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center w-full lg:w-auto dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Add Contract of Service') }}
                    </button>
                </div>
                <!-- Right: Import/Export, Total Records and Search Input -->
                <div class="flex flex-col gap-2 w-full lg:w-auto">
                    <div class="flex flex-wrap items-center gap-2 justify-between lg:justify-end">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Import from Excel Button -->
                            @if(request()->filled('office_allotment_class_filter') && request()->filled('appropriation_filter'))
                                <button type="button" onclick="openImportModal()" class="text-purple-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-purple-600 hover:bg-purple-600 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-xs px-4 py-2 text-center dark:border-purple-500 dark:text-purple-500 dark:hover:text-white dark:hover:bg-purple-600 dark:focus:ring-purple-900 transition-colors whitespace-nowrap">
                                    <i class="fas fa-file-import mr-1"></i>
                                    {{ __('Import') }}
                                </button>
                            @endif
                            <!-- Export to Excel Button -->
                            @if(request()->filled('office_allotment_class_filter'))
                                <button type="button"
                                id="exportBtn"
                                data-url="{{ route('cos_lists.export', request()->only(['year1', 'office_allotment_class_filter', 'appropriation_filter'])) }}"
                                onclick="handleExportClick(this)"
                                class="text-green-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-4 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-colors whitespace-nowrap">
                                    <i id="exportBtnIcon" class="fas fa-file-excel mr-1"></i>
                                    <span id="exportBtnLabel">{{ __('Export') }}</span>
                                </button>
                            @endif
                        </div>
                        <!-- Total Records -->
                        <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 whitespace-nowrap">
                            <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                            <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $cosList->total() ?? count($cosList) }}</span>
                        </div>
                    </div>
                    <!-- Search Section -->
                    <form id="searchForm" method="GET" action="{{ route('cos_lists.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full">
                        <!-- Hidden inputs to preserve filters -->
                        <input type="hidden" name="year1" value="{{ request('year1', date('Y')) }}">
                        <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
                        <input type="hidden" name="appropriation_filter" value="{{ request('appropriation_filter') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', '10') }}">

                        <x-form.select name="search_column" id="searchColumn" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full sm:w-40 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Columns</option>
                            <option value="employee_name" {{ request('search_column') == 'employee_name' ? 'selected' : '' }}>Employee Name</option>
                            <option value="position_title" {{ request('search_column') == 'position_title' ? 'selected' : '' }}>Position Title</option>
                            <option value="salary_grade" {{ request('search_column') == 'salary_grade' ? 'selected' : '' }}>Salary Grade</option>
                            <option value="period" {{ request('search_column') == 'period' ? 'selected' : '' }}>Period</option>
                            <option value="monthly_rate" {{ request('search_column') == 'monthly_rate' ? 'selected' : '' }}>Monthly Rate</option>
                            <option value="total_amount" {{ request('search_column') == 'total_amount' ? 'selected' : '' }}>Total Amount</option>
                        </x-form.select>
                        <div class="flex items-center gap-2 flex-1">
                            <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for Contract of Services" class="border border-gray-300 rounded-lg px-4 py-2 text-xs flex-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white w-full" />
                            <button type="submit" class="flex-shrink-0 text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- COS Cards -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                <div class="max-h-[720px] overflow-y-auto p-2 space-y-3 bg-gray-50 dark:bg-gray-900" id="cosListContainer">
                    @forelse ($cosList as $cos)
                        <div class="cos-card bg-white dark:bg-gray-800 border border-blue-300 dark:border-blue-700 border-l-4 border-l-blue-500 rounded-lg shadow-sm overflow-hidden text-xs hover:shadow-md transition-all"
                             data-cos="{{ json_encode($cos->only(['id','office_allotment_class_id','appropriation_id','employee_id','employee_name','position_title','salary_grade','from_date','to_date','monthly_rate','annual_rate','remarks','basis'])) }}">

                            <!-- Card Header: Employee identity -->
                            <div class="flex flex-wrap justify-between items-start gap-3 px-4 py-3 bg-gray-100 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-sm text-gray-800 dark:text-gray-100 {{ $cos->employee_name === 'Vacant' ? 'text-blue-700 dark:text-blue-400' : '' }} break-words">
                                            {{ $cos->employee_name }}
                                        </span>
                                        @if($cos->employee_name === 'Vacant')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                                Vacant
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-gray-600 dark:text-gray-300 mt-0.5 break-words">{{ $cos->position_title }}</div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                                    <span class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold whitespace-nowrap">
                                        <i class="fas fa-building mr-1 text-gray-400"></i>{{ $cos->officeAllotmentClass->offices->office_abbreviation ?? '-' }}
                                    </span>
                                    @if($cos->salary_grade)
                                        <span class="px-2 py-1 rounded bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-semibold whitespace-nowrap">
                                            SG {{ $cos->salary_grade }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Body: Details -->
                            <div class="px-4 py-3 grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-3">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Period</div>
                                    <div class="text-gray-700 dark:text-gray-300">
                                        @if($cos->from_date && $cos->to_date)
                                            {{ \Carbon\Carbon::parse($cos->from_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($cos->to_date)->format('M d, Y') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Monthly Rate</div>
                                    <div class="text-gray-700 dark:text-gray-300 tabular-nums">{{ number_format($cos->monthly_rate, 2) }}</div>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Total Amount</div>
                                    <div class="font-bold text-sm text-emerald-700 dark:text-emerald-400 tabular-nums">{{ number_format($cos->annual_rate, 2) }}</div>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Basis</div>
                                    <div class="text-gray-700 dark:text-gray-300 break-words">{{ $cos->basis ?? '-' }}</div>
                                </div>
                                @if($cos->remarks)
                                    <div class="col-span-2 sm:col-span-4 pt-1 border-t border-gray-100 dark:border-gray-700">
                                        <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Remarks</div>
                                        <div class="text-gray-700 dark:text-gray-300 break-words">{{ $cos->remarks }}</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Footer: Actions -->
                            <div class="flex justify-end gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                                <button onclick="openEditModalFromRow(this)" type="button" class="text-amber-600 hover:text-white border border-amber-600 hover:bg-amber-600 rounded-lg px-3 py-1.5 text-xs dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 transition-colors" title="Edit">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button onclick="openDeleteModalFromRow(this)" type="button" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 rounded-lg px-3 py-1.5 text-xs dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 transition-colors" title="Delete">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-file-circle-question text-2xl mb-2 block text-gray-300 dark:text-gray-600"></i>
                            {{ __('No Contract of Services records found.') }}
                            @if(count($activeFilterChips) > 0)
                                <a href="{{ route('cos_lists.index') }}" class="block mt-2 text-xs text-blue-600 hover:underline dark:text-blue-400">
                                    Clear filters and try again
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <!-- Summary Footer -->
                @if($cosList->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-300 dark:divide-gray-600 bg-gray-200 dark:bg-gray-900 border-t-2 border-gray-700 dark:border-gray-600">
                        @if(!is_null($totalAppropriation))
                            <div class="text-center px-3 py-3">
                                <span class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Appropriation (Selected Account)</span>
                                <span class="block mt-1 text-base font-bold tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($totalAppropriation, 2) }}</span>
                            </div>
                        @endif
                        <div class="text-center px-3 py-3">
                            <span class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Total Amount</span>
                            <span class="block mt-1 text-base font-bold tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($totalAnnualRate, 2) }}</span>
                        </div>
                        @if(!is_null($totalAppropriation))
                            @php $balance = $totalAppropriation - $totalAnnualRate; @endphp
                            <div class="text-center px-3 py-3">
                                <span class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Balance</span>
                                <span class="block mt-1 text-base font-bold tabular-nums {{ $balance < 0 ? 'text-red-600 dark:text-red-400' : 'text-blue-700 dark:text-blue-400' }}">
                                    {{ number_format($balance, 2) }}
                                    @if($balance < 0)
                                        <i class="fas fa-triangle-exclamation ml-1" title="Over appropriation"></i>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($cosList->hasPages())
                <div class="mt-2">
                    {{ $cosList->links() }}
                </div>
            @endif
        </div>
    </div>

    </div>

    <script>
        // Office allotment classes for the Create COS modal's autocomplete.
        // Sourced from $currentYearOfficeAllotmentClasses — always the actual
        // current year, regardless of the index page's active year filter —
        // and already restricted server-side to COS-eligible appropriation codes.
        const officeAllotmentClasses = {!! json_encode($currentYearOfficeAllotmentClasses->map(function($oac) {
            return [
                'id' => $oac->id,
                'name' => $oac->offices->office_abbreviation . ' - ' . $oac->allotmentClass->class,
                'office_abbreviation' => $oac->offices->office_abbreviation,
                'allotment_class' => $oac->allotmentClass->class
            ];
        })->values()->all()) !!};

        // Initialize empty employees array - would be populated from API/employee search
        let employees = [];

        function openEditModalFromRow(btn) {
            const card = btn.closest('.cos-card');
            const cos = JSON.parse(card.dataset.cos);

            // Find office allotment class name from the data
            const oac = officeAllotmentClasses.find(o => o.id === cos.office_allotment_class_id);

            document.getElementById('editCosForm').action = '{{ route("cos_lists.update", ":id") }}'.replace(':id', cos.id);
            document.getElementById('edit_office_allotment_class_id').value = cos.office_allotment_class_id;
            document.getElementById('edit_office_allotment_class').value = oac ? oac.name : '';
            document.getElementById('edit_appropriation_id').value = cos.appropriation_id;
            document.getElementById('edit_employee_id').value = cos.employee_id;
            document.getElementById('edit_employee_name').value = cos.employee_name;
            document.getElementById('edit_position_title').value = cos.position_title;
            document.getElementById('edit_salary_grade').value = cos.salary_grade;
            document.getElementById('edit_from_date').value = cos.from_date || '';
            document.getElementById('edit_to_date').value = cos.to_date || '';
            document.getElementById('edit_monthly_rate').value = cos.monthly_rate;
            document.getElementById('edit_remarks').value = cos.remarks || '';
            document.getElementById('edit_basis').value = cos.basis || '';

            if (cos.employee_id === 'VACANT' || (cos.employee_id && cos.employee_id.startsWith('MANUAL-'))) {
                setEditPositionFieldsEditable(true);
            } else {
                setEditPositionFieldsEditable(false);
            }

            // Fetch and populate appropriations for this office/allotment class
            if (cos.office_allotment_class_id) {
                fetch(`/api/cos_lists/appropriations/${cos.office_allotment_class_id}`)
                    .then(res => res.json())
                    .then(data => {
                        editAppropriations = data;
                        const selectedApp = editAppropriations.find(app => app.id === cos.appropriation_id);
                        if (selectedApp) {
                            document.getElementById('edit_appropriation_name').value =
                                (selectedApp.account_code || '') + ' - ' + (selectedApp.description || '');
                        }
                    })
                    .catch(error => console.error('Error fetching appropriations:', error));
            }

            calculateEditTotalContractAmount();
            closeAllDropdowns();
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('editModal').setAttribute('aria-hidden', 'false');
            fetchAllEditEmployees();
        }

        function openDeleteModalFromRow(btn) {
            const card = btn.closest('.cos-card');
            const cos = JSON.parse(card.dataset.cos);

            document.getElementById('deleteForm').action = '{{ route("cos_lists.destroy", ":id") }}'.replace(':id', cos.id);

            // Format annual rate with proper number formatting
            let formattedAnnualRate = "0.00";
            if (!isNaN(cos.annual_rate) && cos.annual_rate !== null) {
                formattedAnnualRate = parseFloat(cos.annual_rate).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            document.getElementById('deleteModalContent').innerHTML = `Are you sure you want to delete this Contract of Service for <strong>${cos.employee_name}</strong>? <br><br> This action cannot be undone.`;
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        }

        function resetAppropriationAndSubmit(select) {
            document.getElementById('appropriation_filter').value = '';
            select.form.submit();
        }

        function resetDependentFiltersAndSubmit(yearSelect) {
            document.getElementById('officeAllotmentClass').value = '';
            document.getElementById('appropriation_filter').value = '';
            yearSelect.form.submit();
        }

        function closeAllDropdowns() {
            const dropdowns = document.querySelectorAll('[id$="Dropdown"]');
            dropdowns.forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }

        async function handleExportClick(button) {
            if (button.dataset.loading === 'true') {
                return;
            }
            button.dataset.loading = 'true';

            const icon = document.getElementById('exportBtnIcon');
            const label = document.getElementById('exportBtnLabel');

            icon.classList.remove('fa-file-excel');
            icon.classList.add('fa-spinner', 'fa-spin');
            label.textContent = '{{ __("Exporting...") }}';
            button.classList.add('opacity-60', 'pointer-events-none');

            try {
                const response = await fetch(button.dataset.url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Export failed with status ${response.status}`);
                }

                const blob = await response.blob();

                // Pull the filename the controller set via Excel::download(), falling
                // back to a generic name if the header is missing for some reason
                const disposition = response.headers.get('Content-Disposition') || '';
                const match = disposition.match(/filename="?([^"]+)"?/);
                const filename = match ? match[1] : 'cos_list_export.xlsx';

                const downloadUrl = window.URL.createObjectURL(blob);
                const tempLink = document.createElement('a');
                tempLink.href = downloadUrl;
                tempLink.download = filename;
                document.body.appendChild(tempLink);
                tempLink.click();
                document.body.removeChild(tempLink);
                window.URL.revokeObjectURL(downloadUrl);
            } catch (error) {
                console.error('Export error:', error);
                alert('{{ __("Failed to export. Please try again.") }}');
            } finally {
                icon.classList.remove('fa-spinner', 'fa-spin');
                icon.classList.add('fa-file-excel');
                label.textContent = '{{ __("Export to Excel") }}';
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
    </style>
</x-app-layout>