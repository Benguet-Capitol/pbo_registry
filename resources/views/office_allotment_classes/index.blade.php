<x-app-layout>
    @if (session('status'))
    @php
    $alertType = 'bg-green-100 border-green-400 text-green-700';
    if (str_contains(session('status'), 'updated successfully')) {
    $alertType = 'bg-blue-100 border-blue-400 text-blue-700';
    } elseif (str_contains(session('status'), 'deleted successfully')) {
    $alertType = 'bg-red-100 border-red-400 text-red-700';
    }
    @endphp
    <div class="border-l-4 p-4 mb-4 {{ $alertType }} dark:bg-green-900 dark:border-green-700 dark:text-green-200" role="alert">
        <div class="flex justify-between items-center">
            <div>
                <p>{!! session('status') !!}</p>
            </div>
            <button type="button" class="text-2xl font-semibold leading-none" onclick="this.parentElement.parentElement.remove();">
                &times;
            </button>
        </div>
    </div>
    @endif

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <!-- Left: Obligations Title -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Allotment Class') }}
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

    <!-- Unified Filter Section -->
    <form method="GET" action="{{ route('office_allotment_classes.index') }}" class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800" id="filterForm">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 items-center">

            <!-- Year Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="year1"
                    id="year1"
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select text-gray-400 w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-white"
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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <!-- Create Button -->
                @can('create office allotment classes')
                <button onclick="openCreateOfficeAllotmentClassModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Create Allotment Class per Office') }}
                </button>
                @endcan
                <!-- Search Input -->
                <div class="flex items-center space-x-2">
                    <form method="GET" action="{{ route('office_allotment_classes.index') }}" class="flex items-center">
                    <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-72 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                    </form>
                </div>
            </div>

            <table id="employeesTable" class="text-center w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400 mb-10">
                <thead class="text-center text-xs border-b-2 border-gray-700 text-gray-700 bg-gray-200 border-t-2 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'office_abbreviation', 'sort_order' => $sortBy == 'office_abbreviation' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                Office
                                @if($sortBy == 'office_abbreviation')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'class', 'sort_order' => $sortBy == 'class' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                Allotment Class
                                @if($sortBy == 'class')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'fund_source', 'sort_order' => $sortBy == 'fund_source' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                Fund Source
                                @if($sortBy == 'fund_source')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'fund', 'sort_order' => $sortBy == 'fund' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                Fund
                                @if($sortBy == 'fund')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'fpp_code', 'sort_order' => $sortBy == 'fpp_code' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                FPP Code
                                @if($sortBy == 'fpp_code')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'responsibility_code', 'sort_order' => $sortBy == 'responsibility_code' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                Responsibility Code
                                @if($sortBy == 'responsibility_code')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-1 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">
                            <a href="{{ route('office_allotment_classes.index', array_merge(request()->all(), ['sort_by' => 'total_appropriation', 'sort_order' => $sortBy == 'total_appropriation' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}">
                                Approved Appropriation
                                @if($sortBy == 'total_appropriation')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody id="officeAllotmentBody">
                    @forelse ($office_allotment_classes as $office_allotment_class)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                            ondblclick="window.location.href='{{ route('appropriations.index', ['office_allotment_class_id' => $office_allotment_class->id]) }}'"
                            oncontextmenu="showContextMenu(event, this)"
                            data-id="{{ $office_allotment_class->id }}"
                            data-office="{{ e($office_allotment_class->offices->office_abbreviation ?? '') }}"
                            data-class="{{ e($office_allotment_class->allotmentClass->description ?? '') }}"
                            data-json='@json($office_allotment_class)'
                        >
                            <td class="px-1 py-2 text-gray-600 dark:text-gray-300">
                                {{ $office_allotment_class->offices->office_abbreviation }}
                            </td>
                            <td class="px-1 py-2 text-gray-600 dark:text-gray-300">
                                {{ $office_allotment_class->allotmentClass->description ?? 'N/A' }}
                            </td>
                            <td class="px-1 py-2 text-gray-600 dark:text-gray-300">
                                {{ $office_allotment_class->fund_source }}
                            </td>
                            <td class="px-1 py-2 text-gray-600 dark:text-gray-300">
                                {{ $office_allotment_class->fund }}
                            </td>
                            <td class="px-1 py-2 text-gray-600 dark:text-gray-300">
                                {{ $office_allotment_class->fpp_code }}
                            </td>
                            <td class="px-1 py-2 text-gray-600 dark:text-gray-300">
                                {{ $office_allotment_class->responsibility_code }}
                            </td>
                            <td class="px-1 py-2 text-right">
                                @if($office_allotment_class->total_appropriation > 0)
                                    <span class="px-2 py-1 rounded bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-semibold">
                                        {{ number_format($office_allotment_class->total_appropriation, 2) }}
                                    </span>
                                @elseif($office_allotment_class->total_appropriation == 0)
                                    <span class="px-2 py-1 rounded bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 font-semibold">
                                        {{ number_format($office_allotment_class->total_appropriation, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-300">
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

                <tfoot class="bg-gray-200 dark:bg-gray-800 border-t-2 border-b-2 border-gray-700 dark:border-gray-600">
                    <tr>
                        <td colspan="6" class="text-right text-sm font-bold px-1 py-3 text-gray-700 dark:text-gray-300">
                            Total Approved Appropriation:
                        </td>
                        <td id="totalAppropriationFooter" class="px-1 py-3 font-bold text-sm text-gray-900 dark:text-white"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                @if ($perPage != 'all')
                    {{ $office_allotment_classes->appends(request()->query())->links() }}
                @endif
            </div>

            <!-- Single Context Menu (outside tbody) -->
            <div id="contextMenu" class="hidden absolute w-52 rounded-lg border shadow-lg z-50 bg-white dark:bg-gray-700 dark:border-gray-600">
                <a id="contextAccounts" href="#" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                    <i class="fas fa-stream mr-2"></i> Accounts
                </a>

                @can('edit office allotment classes')
                <button id="contextEdit" type="button" class="w-full text-left px-4 py-2 text-xs text-gray-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                    <i class="fas fa-edit mr-2"></i> Edit
                </button>
                @endcan

                @can('delete office allotment classes')
                <button id="contextDelete" type="button" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-600">
                    <i class="fas fa-trash mr-2"></i> Delete
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

        // read data attributes
        const id = row.dataset.id;
        const office = row.dataset.office || '';
        const klass = row.dataset.class || '';
        const rowJson = row.dataset.json ? JSON.parse(row.dataset.json) : null;

        // build links / callbacks
        accountsLink.href = `{{ url('/appropriations') }}?office_allotment_class_id=${encodeURIComponent(id)}`;

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

        // Find the nearest scrollable container (like overflow-x-auto)
        const container = row.closest('.overflow-x-auto') || document.body;

        // Get bounding rectangles
        const rowRect = row.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();

        // Calculate relative position inside container
        const top = rowRect.top - containerRect.top + container.scrollTop + rowRect.height + 4;
        const left = rowRect.left - containerRect.left + container.scrollLeft + 20;

        // Measure menu dimensions
        const menuRect = menu.getBoundingClientRect();
        const menuH = menuRect.height || 150;
        const menuW = menuRect.width || 200;

        // Get container visible area
        const containerVisibleHeight = container.clientHeight;
        const containerVisibleWidth = container.clientWidth;

        // Adjust if menu would overflow container bottom
        let adjustedTop = top;
        if (adjustedTop + menuH > container.scrollTop + containerVisibleHeight - 10) {
            adjustedTop = top - rowRect.height - menuH - 8; // place above
        }

        // Adjust horizontally if needed
        let adjustedLeft = left;
        if (adjustedLeft + menuW > container.scrollLeft + containerVisibleWidth - 10) {
            adjustedLeft = container.scrollLeft + containerVisibleWidth - menuW - 10;
        }
        if (adjustedLeft < 10) adjustedLeft = 10;

        // Apply
        menu.style.position = 'absolute';
        menu.style.top = `${adjustedTop}px`;
        menu.style.left = `${adjustedLeft}px`;
        menu.classList.remove('hidden');

        // show
        menu.classList.remove('hidden');

        // small delay to avoid immediate hide from the click that triggered contextmenu
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
    }

    // optional: hide when Esc is pressed
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

    // Calculate total appropriation for visible rows only
    function calculateVisibleTotalAppropriation() {
        const rows = document.querySelectorAll('#employeesTable tbody tr');
        let total = 0;

        rows.forEach(row => {
            if (row.offsetParent !== null) {
                const cell = row.querySelector('td:nth-child(7)');
                if (cell) {
                    const value = parseFloat(cell.textContent.replace(/,/g, ''));
                    if (!isNaN(value)) {
                        total += value;
                    }
                }
            }
        });

        const formattedTotal = total.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('totalAppropriationFooter').textContent = formattedTotal;
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