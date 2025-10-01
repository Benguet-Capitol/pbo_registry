<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('All Disbursements') }}
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
        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>

        <form id="filterForm" method="GET" action="{{ route('disbursements.all') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                <!-- Year Filter -->
                <div class="flex items-center space-x-2">
                    <label for="year1" class="sr-only">Year</label>
                    <x-form.select name="year1" id="year1" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        @foreach($availableYears as $year1)
                        <option value="{{ $year1 }}" {{ request('year1') == $year1 ? 'selected' : '' }}>{{ $year1 }}</option>
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
                        <option value="10" {{ request('per_page', 'all') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 'all') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 'all') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 'all') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page', 'all') == 'all' ? 'selected' : '' }}>All</option>
                    </x-form.select>
                </div>
            </div>
        </form>
    </div>

    <!-- Disbursements Table -->
     <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-6 dark:bg-gray-800">
        <div class="p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <!-- <div class="flex justify-between items-center mb-4">
                @can('create purchase orders')
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Add Purchase Order') }}
                </button>
                @endcan
            </div> -->
            <table id="adjustmentsTable" class="text-center w-full text-xs rtl:text-right text-gray-500 dark:text-gray-400 mb-8">
                <thead class="text-center text-xs border-b-2 border-gray-400 text-gray-700 bg-gray-50 border-t-2 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Office & Class</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">OBR No.</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Program</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Account Code</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Description</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">DV / Check Number</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Date</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Remarks</th>
                        <th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">DV / Check Amount</th>
                        @canany(['edit disbursements', 'delete disbursements'])<th class="px-2 py-3 border-gray-300 leading-4 text-gray-600 dark:text-gray-300">Actions</th>@endcanany
                    </tr>
                </thead>
            </table>
        </div>
    </div>



</x-app-layout>
