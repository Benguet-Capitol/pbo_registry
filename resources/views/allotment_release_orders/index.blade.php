@php
    use App\Models\AllotmentReleaseOrder;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Allotment Release Order') }}

                @php
                    $headerFilters = [];

                    if (request('office_filter')) {
                        $filterOffice = $offices->firstWhere('id', request('office_filter'));
                        if ($filterOffice) {
                            $headerFilters[] = $filterOffice->office_abbreviation;
                        }
                    }
                    if (request('allotment_class_filter')) {
                        $headerFilters[] = AllotmentReleaseOrder::displayClassLabel(request('allotment_class_filter'));
                    }
                    if (request('fund_source_filter')) {
                        $headerFilters[] = request('fund_source_filter');
                    }
                    if ($search) {
                        $headerFilters[] = '"'.$search.'"';
                    }
                @endphp

                @if (count($headerFilters) > 0)
                    <span class="text-lg"> > </span>
                    <span class="text-blue-800 dark:text-blue-400">{{ implode(' / ', $headerFilters) }}</span>
                @endif
                <span class="text-blue-800 dark:text-blue-400">(CY {{ $selectedYear }})</span>
            </h3>
            @if(isset($breadcrumb))
            <nav class="text-xs text-gray-600 dark:text-gray-300" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex flex-wrap items-center space-x-1 rtl:space-x-reverse">
                    @foreach ($breadcrumb as $index => $item)
                    <li>
                        @if (!empty($item['route']) && $index < count($breadcrumb) - 1)
                            <a href="{{ $item['route'] }}" class="text-gray-600 hover:underline dark:text-blue-400">{{ $item['label'] }}</a>
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

    @include('allotment_release_orders.modal.create')
    @include('allotment_release_orders.modal.edit')
    @include('allotment_release_orders.modal.delete')

    <div class="page-transition">

    @if(session('status'))
        @php
            $status = session('status');
            $color = match ($status['type'] ?? 'info') { 'delete' => 'red', 'update' => 'blue', default => 'green' };
        @endphp
        <div class="bg-{{ $color }}-100 border border-{{ $color }}-400 text-{{ $color }}-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{!! $status['message'] ?? $status !!}</span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                <span class="text-{{ $color }}-700">&times;</span>
            </button>
        </div>
    @endif

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

        <form id="filterForm" method="GET" action="{{ route('allotment_release_orders.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex flex-col space-y-1">
                    <label class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Year</label>
                    <x-form.select name="year1" class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ (string) $selectedYear === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Office</label>
                    <x-form.select name="office_filter" class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        <option value="">All Offices</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ request('office_filter') == $office->id ? 'selected' : '' }}>{{ $office->office_abbreviation }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Allotment Class</label>
                    <x-form.select name="allotment_class_filter" class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        <option value="">All Classes</option>
                        @foreach($allotmentClasses as $class)
                            <option value="{{ $class->class }}" {{ request('allotment_class_filter') == $class->class ? 'selected' : '' }}>
                                {{ AllotmentReleaseOrder::displayClassLabel($class->class, $class->description) }}
                            </option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fund Source</label>
                    <x-form.select name="fund_source_filter" class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        <option value="">All Fund Sources</option>
                        <option value="Annual Budget" {{ request('fund_source_filter') == 'Annual Budget' ? 'selected' : '' }}>Annual Budget</option>
                        <option value="Supplemental Budget" {{ request('fund_source_filter') == 'Supplemental Budget' ? 'selected' : '' }}>Supplemental Budget</option>
                        <option value="Reenacted Budget" {{ request('fund_source_filter') == 'Reenacted Budget' ? 'selected' : '' }}>Reenacted Budget</option>
                    </x-form.select>
                </div>
            </div>

            @if(count($activeFilterChips) > 0)
            <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <span class="text-[11px] text-gray-400 dark:text-gray-500 uppercase tracking-wide">Active:</span>
                @foreach($activeFilterChips as $chip)
                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200 text-[11px] font-medium px-2 py-1 rounded-full">
                    {{ $chip['label'] }}: {{ $chip['value'] }}
                    <a href="{{ request()->fullUrlWithQuery([$chip['param'] => null]) }}" class="hover:text-red-600 dark:hover:text-red-400" aria-label="Remove {{ $chip['label'] }} filter">
                        <i class="fas fa-times text-[9px]"></i>
                    </a>
                </span>
                @endforeach
            </div>
            @endif
        </form>
    </div>

    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex flex-col gap-3 lg:flex-row lg:justify-between lg:items-center mb-4">
                <!-- Left: Action Button -->
                <div class="flex-shrink-0">
                    @can('create appropriations')
                    <button type="button" onclick="openCreateAroModal()" class="text-blue-600 inline-flex items-center justify-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center w-full lg:w-auto dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Create Allotment Release Order') }}
                    </button>
                    @endcan
                </div>

                <!-- Right: Total Records, Search -->
                <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto lg:justify-end">
                    <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 whitespace-nowrap">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $allotmentReleaseOrders->count() }}</span>
                    </div>

                    <form id="searchForm" method="GET" action="{{ route('allotment_release_orders.index') }}" class="flex items-center gap-2 flex-1 min-w-[240px] lg:flex-initial">
                        <input type="hidden" name="year1" value="{{ $selectedYear }}">
                        <input type="hidden" name="office_filter" value="{{ request('office_filter') }}">
                        <input type="hidden" name="allotment_class_filter" value="{{ request('allotment_class_filter') }}">
                        <input type="hidden" name="fund_source_filter" value="{{ request('fund_source_filter') }}">
                        <x-form.input type="text" name="search" value="{{ $search }}" placeholder="Search ARO No., Office, PPA Code, Account Code..." class="border border-gray-300 rounded-lg px-4 py-2 text-xs flex-1 min-w-0 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                        <button type="submit" class="flex-shrink-0 text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card View -->
            <div class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden">
                <div class="max-h-[720px] overflow-y-auto p-2 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 bg-gray-50 dark:bg-gray-900">
                @forelse ($allotmentReleaseOrders as $aro)
                    @php
                        $officeAbbr = $aro->isSefConsolidated()
                            ? 'SEF'
                            : (optional($aro->officeAllotmentClass->offices)->office_abbreviation ?? 'Unknown Office');
                        $allotmentClass = optional($aro->officeAllotmentClass->allotmentClass);
                        $classLabel = AllotmentReleaseOrder::displayClassLabel(optional($allotmentClass)->class, optional($allotmentClass)->description);
                        $totalThisRelease = $aro->items->sum('this_release');
                        $fundSourceClasses = match ($aro->fund_source) {
                            'Annual Budget' => [
                                'cardBorder' => 'border-blue-300 dark:border-blue-700 border-l-4 border-l-blue-500',
                                'headerBg' => 'bg-blue-50 dark:bg-blue-950 border-b border-blue-200 dark:border-blue-800',
                                'aroText' => 'text-blue-700 dark:text-blue-300',
                                'aroIcon' => 'text-blue-500',
                                'badge' => 'bg-blue-200 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                            ],
                            'Supplemental Budget' => [
                                'cardBorder' => 'border-green-300 dark:border-green-700 border-l-4 border-l-green-500',
                                'headerBg' => 'bg-green-50 dark:bg-green-950 border-b border-green-200 dark:border-green-800',
                                'aroText' => 'text-green-700 dark:text-green-300',
                                'aroIcon' => 'text-green-500',
                                'badge' => 'bg-green-200 dark:bg-green-900 text-green-800 dark:text-green-200',
                            ],
                            default => [
                                'cardBorder' => 'border-gray-300 dark:border-gray-700 border-l-4 border-l-gray-500',
                                'headerBg' => 'bg-gray-50 dark:bg-gray-950 border-b border-gray-200 dark:border-gray-800',
                                'aroText' => 'text-gray-700 dark:text-gray-300',
                                'aroIcon' => 'text-gray-500',
                                'badge' => 'bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-200',
                            ],
                        };
                    @endphp
                    <div class="bg-white dark:bg-gray-800 border {{ $fundSourceClasses['cardBorder'] }} rounded-lg shadow-sm overflow-hidden text-xs hover:shadow-md transition-shadow">
                        <div class="flex flex-wrap justify-between items-center gap-2 px-3 py-2 {{ $fundSourceClasses['headerBg'] }}">
                            <span class="flex items-center gap-2 font-bold {{ $fundSourceClasses['aroText'] }}">
                                <span class="flex items-center gap-1"><i class="fas fa-building {{ $fundSourceClasses['aroIcon'] }}"></i>{{ $officeAbbr }}</span>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <span class="flex items-center gap-1"><i class="fas fa-hashtag {{ $fundSourceClasses['aroIcon'] }}"></i>{{ $aro->aro_no }}</span>
                            </span>
                            <span class="px-2 py-1 rounded font-semibold {{ $fundSourceClasses['badge'] }}">{{ $aro->fund_source }}</span>
                        </div>
                        <div class="px-3 py-3">
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 mb-2">
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Allotment Class</div>
                                    <div class="font-semibold text-gray-700 dark:text-gray-200">{{ $classLabel }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Date of Issue</div>
                                    <div class="font-semibold text-gray-700 dark:text-gray-200">{{ $aro->date_of_issue->format('M d, Y') }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mb-0.5">Total This Release</div>
                                    <div class="font-bold text-green-700 dark:text-green-400 tabular-nums">{{ number_format($totalThisRelease, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 px-3 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                            <a href="{{ route('allotment_release_orders.preview', $aro) }}" title="Preview"
                                class="text-blue-600 inline-flex items-center gap-1 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-colors">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                            @can('edit appropriations')
                            <button type="button" onclick="openEditAroModal({{ \Illuminate\Support\Js::from($aro->load(['items', 'provincialGovernor', 'provincialBudgetOfficer'])) }})" title="Edit"
                                class="text-gray-600 inline-flex items-center gap-1 hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-gray-500 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-colors">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            @endcan
                            @can('delete appropriations')
                            <button type="button" onclick="openDeleteAroModal({{ $aro->id }}, '{{ $aro->aro_no }}')" title="Delete"
                                class="text-red-600 inline-flex items-center gap-1 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-3 py-1.5 dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-colors ml-auto">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="col-span-full px-3 py-10 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-inbox text-2xl mb-2 block text-gray-300 dark:text-gray-600"></i><br>
                        No Allotment Release Orders found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    </div>

    </div>

    <script>
        function closeAllDropdowns() {
            document.querySelectorAll('[id$="_office_allotment_class_dropdown"]').forEach(d => d.classList.add('hidden'));
        }
    </script>
</x-app-layout>
