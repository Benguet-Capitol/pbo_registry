<x-app-layout>
    @if (session('status'))
    @php
    $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200';
    if (str_contains(session('status'), 'updated successfully')) {
    $alertType = 'bg-blue-100 border-blue-400 text-blue-700 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-200';
    } elseif (str_contains(session('status'), 'deleted successfully')) {
    $alertType = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200';
    }
    @endphp
    <div class="border-l-4 p-4 mb-4 {{ $alertType }}" role="alert">
        <div class="flex justify-between items-center">
            <div>
                <p>{!! session('status') !!}</p>
            </div>
            <button type="button" class="text-2xl font-semibold leading-none dark:text-gray-200" onclick="this.parentElement.parentElement.remove();">
                &times;
            </button>
        </div>
    </div>
    @endif

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Programs / Projects / Activities') }}
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

    <!-- Page Content Wrapper with Transition -->
    <div class="page-transition">

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800">
        <div class="p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <div>
                    @can('create programs')
                    <button onclick="openCreateProgramModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Create Program / Project / Activity') }}
                    </button>
                    @endcan
                </div>
                <div class="flex items-center">
                    <div class="flex items-center">
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for programs" class="border border-gray-300 rounded-lg px-4 py-2 mr-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                        <form method="GET" action="{{ route('programs.index') }}">
                        <x-form.select name="per_page" id="perPage" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-4 py-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                        </x-form.select>
                        <button type="submit" class="hidden"></button>
                        </form>
                    </div>
                </div>
            </div>
            <table id="ProgramsTable" class="text-center w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-center border-t-2 border-b-2 border-gray-400 text-xs text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 border-gray-300 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('programs.index', ['sort_by' => 'id', 'sort_order' => $sortBy == 'id' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                ID
                                @if($sortBy == 'id')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 border-gray-300 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            <a href="{{ route('programs.index', ['sort_by' => 'program', 'sort_order' => $sortBy == 'program' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                Programs / Projects / Activities
                                @if($sortBy == 'program')
                                {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                @endif
                            </a>
                        </th>
                        @canany(['edit programs', 'delete programs'])<th class="px-6 py-3 border-gray-300 leading-4 text-gray-600 tracking-wider dark:text-gray-300">{{ __('Action') }}</th>@endcanany
                    </tr>
                </thead>
                <tbody>
                    @foreach ($programs as $program)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $program->id }}</td>
                        <td class="px-6 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">{{ $program->program }}</td>
                        <td class="px-6 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                            <div class="relative inline-block text-left">
                                @canany(['edit programs', 'delete programs'])
                                <button onclick="toggleDropdown(this)" 
                                    class="relative text-xs group px-2 py-1.5">
                                    <span class="fas fa-forward"></span>
                                </button>
                                <div class="absolute right-0 mt-1 w-32 bg-white border border-gray-300 rounded-lg shadow-lg hidden dropdown-menu z-10 dark:bg-gray-700 dark:border-gray-600">
                                    @can('edit programs')
                                    <button onclick='openEditProgramModal(@json($program))' class="w-full text-left px-4 py-2 text-xs text-gray-600 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-600">
                                        <i class="fas fa-edit mr-2"></i>Edit
                                    </button>
                                    @endcan
                                    @can('delete programs')
                                    <button onclick="openDeleteProgramModal({{ $program->id }}, '{{ $program->program }}')" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-gray-200 dark:text-red-400 dark:hover:bg-gray-600">
                                        <i class="fas fa-trash mr-2"></i>Delete
                                    </button>
                                    @endcan
                                </div>
                                @endcanany
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                @if ($perPage != 'all')
                {{ $programs->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>

    @include('programs.modal.create')
    @include('programs.modal.edit')
    @include('programs.modal.delete')

<script>
    function filterTable() {
        // Declare variables
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toLowerCase();
        table = document.getElementById("ProgramsTable");
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
</style>
    </div>
</x-app-layout>