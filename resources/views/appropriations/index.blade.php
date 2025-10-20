<x-app-layout>
    @if (session('status'))
    @php
    $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-600 dark:text-green-200';
    if (str_contains(session('status'), 'updated successfully')) {
    $alertType = 'bg-blue-100 border-blue-400 text-blue-700 dark:bg-blue-900 dark:border-blue-600 dark:text-blue-200';
    } elseif (str_contains(session('status'), 'deleted successfully')) {
    $alertType = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-600 dark:text-red-200';
    }
    @endphp
    <div class="border-l-4 p-4 mb-4 {{ $alertType }}" role="alert">
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
                {{ __('Accounts') }} |
                <span class="text-blue-800 dark:text-blue-400">
                    {{ $officeAllotmentClass->offices->office_name }} - {{ $officeAllotmentClass->allotmentClass->class}}
                    (CY {{ $officeAllotmentClass->year }})
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
        @if ($officeAllotmentClass && $officeAllotmentClass->offices)
        @php
        // Determine grid columns: 2 if import is visible, 1 if not
        $showImport = auth()->user() && auth()->user()->can('import appropriations');
        $gridCols = $showImport ? 'md:grid-cols-2' : 'md:grid-cols-1';
        @endphp
        <div class="p-4 bg-white shadow-md rounded-md mt-6 text-gray-600 text-xs grid grid-cols-1 {{ $gridCols }} gap-4 dark:text-gray-400 dark:bg-gray-800 dark:border-gray-700">
            <div>
                <table class="min-w-full table-auto bg-white rounded-lg dark:bg-gray-800">
                    <tbody>
                        <tr>
                            <th class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">Office:</th>
                            <td class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">{{ $officeAllotmentClass->offices->office_name }}</td>
                        </tr>
                        <tr>
                            <th class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">Fund:</th>
                            <td class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">{{ $officeAllotmentClass->fund }}</td>
                        </tr>
                        <tr>
                            <th class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">Fund Source:</th>
                            <td class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">{{ $officeAllotmentClass->fund_source }}</td>
                        </tr>
                        <tr>
                            <th class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">Allotment Class:</th>
                            <td class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">{{ $officeAllotmentClass->allotmentClass->description}}</td>
                        </tr>
                        <tr>
                            <th class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">Approved Appropriation:</th>
                            <td class="px-4 py-3 border-b text-left dark:border-gray-700 dark:text-gray-200">{{ number_format($totalAppropriation, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="px-4 py-3 text-left dark:border-gray-700 dark:text-gray-200">Total Allotment:</th>
                            <td class="px-4 py-3 text-left dark:border-gray-700 dark:text-gray-200">{{ number_format($totalAllotment, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @can('import appropriations')
            <div class="flex flex-col justify-center items-center bg-white p-2 rounded-lg dark:bg-gray-800">
                <form action="{{ route('appropriations.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col space-y-3">
                    @csrf
                    <label for="file-upload" class="block text-xs dark:text-gray-200 font-semibold text-center">
                        {{ __('Import Accounts') }}
                    </label>
                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
                        <input type="file" name="file" id="file-upload" accept=".xlsx,.xls,.csv" required class="form-control border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <button type="submit" class="text-blue-600 inline-flex items-center justify-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                            {{ __('Import') }}
                        </button>
                    </div>
                    <div class="mt-2 flex justify-center">
                        <a href="{{ asset('storage/sample/Accounts.xlsx') }}" download class="text-xs text-gray-600 dark:text-gray-300 hover:underline">
                            📄 Download Sample Format (Excel)
                        </a>
                    </div>
                </form>
            </div>
            @endcan
        </div>
        @endif

    </x-slot>

    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-6 dark:bg-gray-800">
        <div class="p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <div>
                    @can('create appropriations')
                    <button onclick="openCreateAppropriationsModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-plus text-lg mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Create Account') }}
                    </button>
                    @endcan
                </div>
                <div class="flex items-center text-xs">
                    <form method="GET" action="{{ route('appropriations.index') }}" class="flex items-center">
                        <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for accounts" class="form-control border border-gray-300 rounded-lg px-4 py-2 mr-2 text-xs w-72 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                        <x-form.select name="per_page" id="perPage" onchange="this.form.submit()" class="form-control border border-gray-300 rounded-lg px-4 py-2 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <option value="10" {{ request('per_page', 'all') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 'all') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 'all') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 'all') == 100 ? 'selected' : '' }}>100</option>
                            <option value="all" {{ request('per_page', 'all') == 'all' ? 'selected' : '' }}>All</option>
                        </x-form.select>
                        <button type="submit" class="hidden"></button>
                    </form>
                </div>
            </div>
            <table id="appropriationsTable" class="table-auto text-center w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400 mb-10">
                <thead class="text-center border-gray-400 text-xs text-gray-700 bg-gray-50 border-b-2 border-t-2 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'programs', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                Programs
                                @if($sortBy == 'programs')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'account_code', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                Account Code
                                @if($sortBy == 'account_code')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'description', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                Description
                                @if($sortBy == 'description')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'fpp_code', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                FPP Code
                                @if($sortBy == 'fpp_code')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'project_location', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                Project Location
                                @if($sortBy == 'project_location')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        @if(optional($officeAllotmentClass->allotmentClass)->description === 'Continuing Capital Outlay')
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'project_no', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                Project No.
                                @if($sortBy == 'project_no')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'cco_year', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                CCO Year
                                @if($sortBy == 'cco_year')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        @endif
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'appropriation', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                Appropriation
                                @if($sortBy == 'appropriation')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'quarter1', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                1st Quarter Allotment
                                @if($sortBy == 'quarter1')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'quarter2', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                2nd Quarter Allotment
                                @if($sortBy == 'quarter2')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'quarter3', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                3rd Quarter Allotment
                                @if($sortBy == 'quarter3')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'quarter4', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                4th Quarter Allotment
                                @if($sortBy == 'quarter4')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-1 border-gray-300 leading-4 tracking-wider dark:border-gray-700">
                            <a href="{{ route('appropriations.index', array_merge(request()->query(), ['sort_by' => 'remarks', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="dark:text-gray-200">
                                Remarks
                                @if($sortBy == 'remarks')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appropriations as $appropriation)
                    <tr 
                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 text-gray-600 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer relative"
                        oncontextmenu="showAppropriationContextMenu(event, this)"
                        @if(isset($appropriation))
                            data-appropriation='@json($appropriation)'
                            data-appropriation-id="{{ $appropriation->id }}"
                            data-appropriation-name="{{ $appropriation->appropriation }}"
                            data-appropriation-code="{{ $appropriation->account_code }}"
                            data-appropriation-desc="{{ $appropriation->description }}"
                        @endif
                    >
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ $appropriation->programs }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ $appropriation->account_code }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ $appropriation->description }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ $appropriation->fpp_code }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ $appropriation->project_location }}</td>
                        @if(optional($officeAllotmentClass->allotmentClass)->description === 'Continuing Capital Outlay')
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ $appropriation->project_no }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ $appropriation->cco_year }}</td>
                        @endif
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ number_format($appropriation->appropriation, 2) }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ number_format($appropriation->quarter1, 2) }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ number_format($appropriation->quarter2, 2) }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ number_format($appropriation->quarter3, 2) }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ number_format($appropriation->quarter4, 2) }}</td>
                        <td class="px-2 py-2 border-b border-gray-300 dark:text-gray-300">{{ $appropriation->remarks }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 italic">
                            No Accounts found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                @if ($perPage != 'all')
                {{ $appropriations->appends(request()->query())->links() }}
                @endif
            </div>

            <!-- Context Menu -->
        <div id="appropriationContextMenu" 
            class="absolute hidden w-44 bg-white border border-gray-300 rounded-lg shadow-lg z-50 dark:bg-gray-700 dark:border-gray-600">
            @can('edit appropriations')
            <button id="contextEdit"
                    class="w-full text-left block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                <i class="fas fa-edit mr-2"></i>Edit
            </button>
            @endcan
            @can('delete appropriations')
            <button id="contextDelete"
                    class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-600">
                <i class="fas fa-trash mr-2"></i>Delete
            </button>
            @endcan
        </div>


        </div>
    </div>

    @include('appropriations.modal.create')
    @include('appropriations.modal.delete')
    @include('appropriations.modal.edit')

</x-app-layout>

<script>
    function filterTable() {
        // Declare variables
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toLowerCase();
        table = document.getElementById("appropriationsTable");
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
    }

    // Add event listener for input event to filter table as you type
    document.getElementById('searchInput').addEventListener('input', filterTable);

    function showAppropriationContextMenu(event, id) {
    event.preventDefault();

    const menu = document.getElementById('appropriationContextMenu');
    const row = event.currentTarget;
    const container = row.closest('.overflow-x-auto') || document.body;
    const rowRect = row.getBoundingClientRect();
    const containerRect = container.getBoundingClientRect();

    // Calculate position relative to container
    const top = rowRect.top - containerRect.top + container.scrollTop + rowRect.height;
    const left = rowRect.left - containerRect.left + container.scrollLeft + 10;

    menu.style.position = 'absolute';
    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
    menu.classList.remove('hidden');

    // Attach action handlers if data is present
    const appropriation = row.dataset.appropriation ? JSON.parse(row.dataset.appropriation) : null;

    if (appropriation) {
        document.getElementById('contextEdit')?.setAttribute('onclick', `openEditAppropriationsModal(${JSON.stringify(appropriation)})`);
        document.getElementById('contextDelete')?.setAttribute(
            'onclick',
            `openDeleteModal(${appropriation.id}, '${appropriation.appropriation}', '${appropriation.account_code}', '${appropriation.description}')`
        );
    } else {
        document.getElementById('contextEdit')?.removeAttribute('onclick');
        document.getElementById('contextDelete')?.removeAttribute('onclick');
    }

    // Close on outside click
    document.addEventListener('click', hideAppropriationContextMenu);
}

function hideAppropriationContextMenu(event) {
    const menu = document.getElementById('appropriationContextMenu');
    if (!menu.contains(event.target)) {
        menu.classList.add('hidden');
        document.removeEventListener('click', hideAppropriationContextMenu);
    }
}


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