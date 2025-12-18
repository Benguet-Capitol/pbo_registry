<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <!-- Left: Obligations Title with Filters -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Obligations') }}

                @php
                $filters = [];

                if (request('office_allotment_class_filter')) {
                    $officeClass = $officeAllotmentClasses->firstWhere('id', request('office_allotment_class_filter'));
                    if ($officeClass) {
                        $filters[] = $officeClass->offices->office_abbreviation . ' - ' . $officeClass->allotmentClass->class;
                    }
                }
                if (request('obr_type_filter')) {
                    $filters[] = request('obr_type_filter');
                }
                @endphp

                @if (count($filters) > 0)
                    <span class="text-lg"> > </span>
                    <span class="text-blue-800 dark:text-blue-400">{{ implode(' / ', $filters) }}</span>
                @endif
                <span class="text-blue-800 dark:text-blue-400">
                    (CY {{ request('year1', date('Y')) }})
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

        <form id="filterForm" method="GET" action="{{ route('obligations.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

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

                <!-- OBR Type Filter -->
                <div class="flex items-center space-x-2">
                    <label for="obr_type" class="sr-only">OBR Type</label>
                    <x-form.select name="obr_type_filter" id="obr_type_filter" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="Regular" {{ request('obr_type_filter') == 'Regular' ? 'selected' : '' }}>Regular</option>
                        <option value="Purchase Request" {{ request('obr_type_filter') == 'Purchase Request' ? 'selected' : '' }}>Purchase Request</option>
                        <option value="Contract" {{ request('obr_type_filter') == 'Contract' ? 'selected' : '' }}>Project/Contract</option>
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
    
    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <!-- Left: Action Button -->
                @can('create obligations')
                <button onclick="openCreateModal()" class="text-blue-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Create Obligation') }}
                </button>
                @endcan
                <!-- Search Input -->
                <div class="flex items-center space-x-2">
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for obligations" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-72 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
                <div class="max-h-[720px] overflow-y-auto">
                    <table id="obligationsTable" class="mb-20 min-w-full text-xs text-center text-gray-600 dark:text-gray-300">
                        <thead id="obligationTableHead"
                            class="text-center border-b-2 border-t-2 border-gray-700 text-xs text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'obr_date', 'sort_order' => $sortBy == 'obr_date' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        OBR Date
                                        @if($sortBy == 'obr_date')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'office_allotment_class', 'sort_order' => $sortBy == 'office_allotment_class' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Office & Class
                                        @if($sortBy == 'office_allotment_class')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'obr_type', 'sort_order' => $sortBy == 'obr_type' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        OBR Type
                                        @if($sortBy == 'obr_type')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'obr_no', 'sort_order' => $sortBy == 'obr_no' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        OBR No.
                                        @if($sortBy == 'obr_no')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'particulars', 'sort_order' => $sortBy == 'particulars' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Particulars
                                        @if($sortBy == 'particulars')
                                        {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'obr_amount', 'sort_order' => $sortBy == 'obr_amount' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Obligation
                                        @if($sortBy == 'obr_amount')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'po_amount', 'sort_order' => $sortBy == 'po_amount' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Purchase Order
                                        @if($sortBy == 'po_amount')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                @hasanyrole('Obligation|Administrator|Developer')
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'remarks', 'sort_order' => $sortBy == 'remarks' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Remarks
                                        @if($sortBy == 'remarks')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                @endhasanyrole
                                @hasanyrole('Disbursement|Administrator|Developer')
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'dv_amount', 'sort_order' => $sortBy == 'dv_amount' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Disbursement
                                        @if($sortBy == 'dv_amount')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'balance', 'sort_order' => $sortBy == 'balance' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Balance
                                        @if($sortBy == 'balance')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    <a href="{{ route('obligations.index', ['sort_by' => 'payment_remarks', 'sort_order' => $sortBy == 'payment_remarks' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Payment Remarks
                                        @if($sortBy == 'payment_remarks')
                                            {{ $sortOrder == 'asc' ? '▲' : '▼' }}
                                        @endif
                                    </a>
                                </th>
                                @endhasanyrole
                                <!-- <th class="px-6 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Actions
                                </th> -->
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($obligations as $obligation)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer relative"
                                ondblclick="openModal({{ $obligation->id }})"
                                oncontextmenu="showObligationContextMenu(event, this)"
                                data-obligation='@json($obligation)'
                                data-obligation-id="{{ $obligation->id }}"
                                data-obligation-obr="{{ $obligation->obr_no }}"
                                data-obligation-payment-remarks="{{ $obligation->payment_remarks }}"
                                data-obligation-office="{{ $obligation->officeAllotmentClass->offices->office_abbreviation }}"
                                data-obligation-class="{{ $obligation->officeAllotmentClass->allotmentClass->class }}"
                                data-obligation-amount="{{ $obligation->obr_amount }}"
                            >
                                <td class="px-1 py-2">{{ $obligation->obr_date }}</td>
                                <td class="px-1 py-2">{{ $obligation->officeAllotmentClass->offices->office_abbreviation }} - {{ $obligation->officeAllotmentClass->allotmentClass->class }}</td>
                                <td class="px-1 py-2">{{ $obligation->obr_type }}</td>
                                <td class="px-1 py-2">{{ $obligation->obr_no }}</td>
                                <td class="px-1 py-2 text-center max-w-sm">{{ $obligation->particulars }}</td>

                                <td class="px-1 py-2 text-right obligation-amount">
                                    <div class="relative inline-block group">
                                        @if ($obligation->obr_amount == 0.00)
                                            <span class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 px-2 py-1 rounded font-semibold">Cancelled</span>
                                        @elseif ($obligation->obligationAdjustments->isNotEmpty())
                                            @unlessrole('Disbursement')
                                                <button onclick="openCreateObligationAdjustmentModal({{ $obligation->id }})"
                                                    type="button"
                                                    class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 hover:underline rounded font-semibold">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </button>
                                                <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                    Add Obligation Adjustment
                                                </span>
                                            @else
                                                <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </span>
                                            @endunlessrole
                                        @else
                                            @unlessrole('Disbursement')
                                                <button onclick="openCreateObligationAdjustmentModal({{ $obligation->id }})"
                                                    type="button"
                                                    class="text-gray-700 dark:text-gray-400 hover:underline px-2 py-1">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </button>
                                                <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                    Add Obligation Adjustment
                                                </span>
                                            @else
                                                <span class="text-gray-700 dark:text-gray-400 px-2 py-1">
                                                    {{ number_format($obligation->obr_amount, 2) }}
                                                </span>
                                            @endunlessrole
                                        @endif
                                    </div>
                                </td>

                                <td class="px-1 py-2 text-right po-amount">
                                    @php $poAmount = $obligation->purchaseOrders->sum('po_amount'); @endphp
                                    @if ($obligation->obr_type === 'Purchase Request')
                                        <div class="relative inline-block group">
                                            @if ($poAmount > 0)
                                                @unlessrole('Disbursement')
                                                    <button onclick="openCreatePOModal({{ $obligation->id }})"
                                                        type="button"
                                                        class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold hover:underline">
                                                        {{ number_format($poAmount, 2) }}
                                                    </button>
                                                    <!-- Tooltip -->
                                                    <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                        Add Purchase Order
                                                    </span>
                                                @else
                                                    <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 px-2 py-1 rounded font-semibold">
                                                        {{ number_format($poAmount, 2) }}
                                                    </span>
                                                @endunlessrole
                                            @else
                                                @unlessrole('Disbursement')
                                                    <button onclick="openCreatePOModal({{ $obligation->id }})"
                                                        type="button"
                                                        class="text-blue-700 dark:text-blue-400 hover:underline px-2 py-1">
                                                        {{ number_format($poAmount, 2) }}
                                                    </button>
                                                    <!-- Tooltip -->
                                                    <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                        Add Purchase Order
                                                    </span>
                                                @else
                                                    <span class="text-blue-700 dark:text-blue-400 px-2 py-1">
                                                        {{ number_format($poAmount, 2) }}
                                                    </span>
                                                @endunlessrole
                                            @endif
                                        </div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                @hasanyrole('Obligation|Administrator|Developer')
                                <td class="px-1 py-2 text-center max-w-48">{{ $obligation->remarks ? : '-' }}</td>
                                @endhasanyrole

                                @hasanyrole('Disbursement|Administrator|Developer')
                                <td class="px-1 py-2 text-right dv-amount">
                                @php
                                    $disbursementAmount = $obligation->disbursements->sum('disbursement_amount');
                                    $obligationAmount = $obligation->obr_amount;

                                    $isEqual = bccomp($disbursementAmount, $obligationAmount, 2) === 0;
                                    $isLower = $disbursementAmount < $obligationAmount && $disbursementAmount > 0;
                                    $isZero = $disbursementAmount == 0;
                                    $isOBRZero = $obligationAmount == 0; // ✅ New condition
                                @endphp

                                <div class="relative inline-block group">
                                        <button onclick="openCreateDisbursementModal({{ $obligation->id }})"
                                            type="button"
                                            class="hover:underline px-2 py-1
                                                @if ($isOBRZero)
                                                    text-gray-700 dark:text-gray-400
                                                @elseif ($isEqual)
                                                    bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-semibold
                                                @elseif ($isLower)
                                                    bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-400 font-semibold
                                                @elseif ($isZero)
                                                    text-gray-700 dark:text-gray-400
                                                @else
                                                    text-gray-700 dark:text-gray-400
                                                @endif
                                            ">
                                            {{ number_format($disbursementAmount, 2) }}
                                        </button>

                                        <!-- Tooltip -->
                                        <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block 
                                                    bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                            Add Disbursement
                                        </span>
                                </div>
                            </td>

                            <td class="px-1 py-2 text-right balance">
                                @php
                                    $balance = $obligation->obr_amount - $disbursementAmount;
                                @endphp
                                <div class="relative inline-block group">
                                    @if ($obligationAmount == 0)
                                        <span class="text-gray-700 dark:text-gray-400 px-2 py-1">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @elseif ($balance > 0)
                                        <span class="bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-400 font-semibold px-2 py-1">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @elseif ($balance < 0)
                                        <span class="bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 font-semibold px-2 py-1">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @else
                                        <span class="bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 font-semibold px-2 py-1">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-1 py-2 text-center max-w-sm payment-remarks">
                                <div class="relative inline-block group">
                                    {{ $obligation->payment_remarks ? Str::limit($obligation->payment_remarks, 50) : '-' }}
                                </div>
                            </td>
                            @endhasanyrole
                                

                                <!-- <td class="px-1 py-2">
                                    <div class="relative inline-block text-left">
                                        <button onclick="toggleDropdown(this)"
                                            class="relative text-xs group px-2 py-1.5">
                                            <span class="fas fa-ellipsis-v"></span>
                                            
                                            <span class="absolute right-full ml-2 top-1/2 -translate-y-1/2 hidden group-hover:block bg-gray-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap z-20">
                                                {{ $obligation->officeAllotmentClass->offices->office_abbreviation }} - {{ $obligation->officeAllotmentClass->allotmentClass->class }} | {{ $obligation->obr_no }} | {{ number_format($obligation->obr_amount, 2) }}
                                            </span>
                                        </button>
                                        <div class="absolute right-0 mt-1 w-44 z-50 hidden dropdown-menu bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-md">
                                            <button onclick="openModal({{ json_encode($obligation->id) }})"
                                                class="w-full px-4 py-2 text-left text-xs hover:bg-gray-200 dark:hover:bg-gray-600">
                                                <i class="fas fa-eye mr-2"></i>View Details
                                            </button>
                                            @can('view obligation adjustments')
                                            <a href="{{ route('obligation_adjustments.index', ['obligation_id' => $obligation->id]) }}"
                                                class="block px-4 py-2 text-xs hover:bg-gray-200 dark:hover:bg-gray-600">
                                                <i class="fas fa-file-edit mr-2"></i>Adjustments
                                            </a>
                                            @endcan
                                            @can('view purchase orders')
                                            @if ($obligation->obr_type === 'Purchase Request')
                                            <a href="{{ route('purchase_orders.index', ['obligation_id' => $obligation->id]) }}"
                                                class="block px-4 py-2 text-xs hover:bg-gray-200 dark:hover:bg-gray-600">
                                                <i class="fas fa-file-invoice mr-2"></i>Purchase Order
                                            </a>
                                            @endif
                                            @endcan
                                            @can('view disbursement')
                                            <a href="{{ route('disbursements.index', ['obligation_id' => $obligation->id]) }}"
                                                class="block px-4 py-2 text-xs hover:bg-gray-200 dark:hover:bg-gray-600">
                                                <i class="fas fa-file-medical-alt mr-2"></i>Disbursement
                                            </a>
                                            @endcan

                                            @can('cancel obligations')
                                            <button onclick="openCancellationModal(this.dataset.id, JSON.parse(this.dataset.obligation))"
                                                data-id="{{ $obligation->id }}"
                                                data-obligation='{{ $obligation->obligation_data }}'
                                                class="w-full px-4 py-2 text-left text-xs text-red-600 dark:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-600">
                                                <i class="fas fa-window-close mr-2"></i>Cancellation
                                            </button>
                                            @endcan
                                            
                                            @can('edit obligations')
                                            <button onclick='openEditObligationsModal(@json($obligation))' class="w-full text-left px-4 py-2 text-xs text-gray-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                            <i class="fas fa-edit mr-2"></i>Edit
                                            </button>
                                            @endcan

                                            @can('delete obligations')
                                            <button onclick="openDeleteModal({{ $obligation->id }}, '{{ $obligation->obr_no }}', '{{ $obligation->officeAllotmentClass->offices->office_abbreviation }}', '{{ $obligation->officeAllotmentClass->allotmentClass->class }}', '{{ $obligation->obr_amount }}')"
                                                class="w-full px-4 py-2 text-left text-xs text-red-600 dark:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-600">
                                                <i class="fas fa-trash mr-2"></i>Delete
                                            </button>
                                            @endcan
                                        </div>
                                    </div>
                                </td> -->
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 italic">
                                        No Obligations found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- Sticky footer table for totals -->
                    <div id="obligationTableFooter" class="sticky bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t-2 border-b-2 border-gray-700 dark:border-gray-600 z-20">
                        <table class="min-w-full text-sm text-center text-gray-600 dark:text-gray-300">
                            <tbody>
                                <tr class="bg-gray-200 dark:bg-gray-700 font-bold">
                                    <td class="text-right px-4 py-3">Total Obligation:</td>
                                    <td class="text-left px-4 py-3 text-green-700 dark:text-green-300 font-semibold" id="footerTotalObligationAmount">0.00</td>
                                    <td class="text-right px-4 py-3">Total Purchase Order:</td>
                                    <td class="text-left px-4 py-3 text-blue-700 dark:text-blue-300 font-semibold" id="footerTotalPOAmount">0.00</td>
                                    <td class="text-right px-4 py-3">Total Disbursement:</td>
                                    <td class="text-left px-4 py-3 text-orange-700 dark:text-orange-300 font-semibold" id="footerTotalDisbursementAmount">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                @if ($perPage != 'all')
                {{ $obligations->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div id="obligationContextMenu" 
        class="absolute hidden w-44 bg-white border border-gray-300 rounded-lg shadow-lg z-50 dark:bg-gray-700 dark:border-gray-600"
        style="display: none;">
        <button id="contextView"
                class="w-full text-left block px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-eye mr-2"></i>View Details
        </button>
        @can('view obligation adjustments')
        <button id="contextAdjustments"
                class="w-full text-left block px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-file-edit mr-2"></i>Adjustments
        </button>
        @endcan
        @can('view purchase orders')
        <button id="contextPurchaseOrders"
                class="w-full text-left block px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-file-invoice mr-2"></i>Purchase Order
        </button>
        @endcan
        @can('view disbursement')
        <button id="contextDisbursement"
                class="w-full text-left block px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-file-medical-alt mr-2"></i>Disbursement
        </button>
        @endcan
        @can('cancel obligations')
        <button id="contextCancellation"
                class="w-full text-left block px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-window-close mr-2"></i>Cancellation
        </button>
        @endcan
        @hasanyrole('Disbursement|Administrator|Developer')
        <button id="contextPaymentRemarks"
                class="w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-comment-dollar mr-2"></i>Payment Remarks
        </button>
        @endhasanyrole
        @can('edit obligations')
        <button id="contextEdit"
                class="w-full text-left block px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-edit mr-2"></i>Edit
        </button>
        @endcan
        @can('delete obligations')
        <button id="contextDelete"
                class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-gray-200 dark:text-red-400 dark:hover:bg-gray-600">
            <i class="fas fa-trash mr-2"></i>Delete
        </button>
        @endcan
    </div>
    

    @include('obligations.modal.obligation_details')
    @include('obligations.modal.cancellation')
    @include('obligations.modal.delete')
    @include('obligations.modal.create')
    @include('obligations.modal.edit')
    @include('obligations.modal.payment_remarks')
    <div id="createPOModalContainer"></div>
    <div id="createObligationAdjustmentModalContainer"></div>.
    <div id="createDisbursementModalContainer"></div>

</x-app-layout>

<script>

    const menu = document.getElementById('obligationContextMenu');
    let scrollTimeout;

    (function() {

    // Function to handle scroll events with debouncing
    function handleScroll() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(hideObligationContextMenu, 150);
    }

    // showContextMenu receives the mouse event and the row element
    window.showObligationContextMenu = function(event, row) {
        event.preventDefault();
        event.stopPropagation();

        if (!menu) return;

        // Get the table container
        const container = row.closest('.overflow-x-auto') || document.body;
        if (!container) return;

        // Get element positions
        const rowRect = row.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();

        // Calculate position relative to container
        const top = rowRect.top + rowRect.height + 5;
        const left = containerRect.left + container.scrollLeft + 10;

        // Position menu
        menu.style.position = 'absolute';
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
        menu.style.display = 'block';
        menu.classList.remove('hidden');

        // Get obligation data and set up menu items
        const obligation = row.dataset.obligation ? JSON.parse(row.dataset.obligation) : null;
        if (obligation) {
            // View button
            const viewBtn = menu.querySelector('#contextView');
            if (viewBtn) {
                viewBtn.onclick = () => {
                    hideObligationContextMenu();
                    openModal(obligation.id);
                };
            }

            // Adjustments button
            const adjustBtn = menu.querySelector('#contextAdjustments');
            if (adjustBtn && obligation.id) {
                adjustBtn.onclick = () => {
                    hideObligationContextMenu();
                    window.location.href = `/obligation_adjustments?obligation_id=${obligation.id}`;
                };
            }

            // Purchase Orders button
            const poBtn = menu.querySelector('#contextPurchaseOrders');
            if (poBtn && obligation.id) {
                // Only show for Purchase Request type
                if (obligation.obr_type === 'Purchase Request') {
                    poBtn.style.display = 'block';
                    poBtn.onclick = () => {
                        hideObligationContextMenu();
                        window.location.href = `/purchase_orders?obligation_id=${obligation.id}`;
                    };
                } else {
                    poBtn.style.display = 'none';
                }
            }

            // Disbursement button
            const disbursementBtn = menu.querySelector('#contextDisbursement');
            if (disbursementBtn && obligation.id) {
                disbursementBtn.onclick = () => {
                    hideObligationContextMenu();
                    window.location.href = `/disbursements?obligation_id=${obligation.id}`;
                };
            }

            // Edit button
            const editBtn = menu.querySelector('#contextEdit');
            if (editBtn) {
                editBtn.onclick = () => {
                    hideObligationContextMenu();
                    openEditObligationsModal(obligation);
                };
            }

            // Payment Remarks button
            const paymentRemarksBtn = menu.querySelector('#contextPaymentRemarks');
            if (paymentRemarksBtn && obligation.id) {
                paymentRemarksBtn.onclick = () => {
                    hideObligationContextMenu();
                    openPaymentRemarksModal(
                        obligation.id,
                        obligation.obr_no,
                        obligation.payment_remarks || ''
                    );
                };
            }

            // Delete button
            const deleteBtn = menu.querySelector('#contextDelete');
            if (deleteBtn) {
                deleteBtn.onclick = () => {
                    hideObligationContextMenu();
                    openDeleteModal(
                        row.dataset.obligationId,
                        row.dataset.obligationObr,
                        row.dataset.obligationOffice,
                        row.dataset.obligationClass,
                        row.dataset.obligationAmount
                    );
                };
            }

            // Cancellation button
            const cancelBtn = menu.querySelector('#contextCancellation');
            if (cancelBtn) {
                cancelBtn.onclick = () => {
                    hideObligationContextMenu();
                    openCancellationModal(obligation.id, obligation);
                };
            }
        }

        // Add event listeners with delay
        setTimeout(() => {
            document.addEventListener('click', hideObligationContextMenu);
            window.addEventListener('resize', hideObligationContextMenu);
            window.addEventListener('scroll', hideObligationContextMenu, { passive: true });
            
            // Add scroll listener to container
            container.addEventListener('scroll', hideObligationContextMenu, { passive: true });
        }, 30);
    };

    function hideObligationContextMenu() {
        if (!menu) return;
        menu.classList.add('hidden');
        menu.style.display = 'none';
        
        // Clear any existing scroll timeout
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        
        // Clean up event listeners
        document.removeEventListener('click', hideObligationContextMenu);
        window.removeEventListener('resize', hideObligationContextMenu);
        window.removeEventListener('scroll', handleScroll);
        
        // Clean up horizontal scroll container listener
        const container = document.querySelector('.overflow-x-auto');
        if (container) {
            container.removeEventListener('scroll', handleScroll);
        }

        // Clean up vertical scroll container listener
        const scrollableContainer = document.querySelector('.max-h-\\[720px\\].overflow-y-auto');
        if (scrollableContainer) {
            scrollableContainer.removeEventListener('scroll', handleScroll);
        }
    }

    // Hide on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideObligationContextMenu();
    });

    // Initialize scroll event listeners
    document.addEventListener('DOMContentLoaded', () => {
        // Handle horizontal scroll container
        const container = document.querySelector('.overflow-x-auto');
        if (container) {
            container.addEventListener('scroll', hideObligationContextMenu, { passive: true });
        }
        
        // Handle vertical scroll container
        const scrollableContainer = document.querySelector('.max-h-\\[720px\\].overflow-y-auto');
        if (scrollableContainer) {
            scrollableContainer.addEventListener('scroll', hideObligationContextMenu, { passive: true });
        }
    });
})();


    // Compute totals for visible rows
    function computeTableTotals() {
        let totalObligation = 0;
        let totalPO = 0;
        let totalDisbursement = 0;
        const table = document.getElementById('obligationsTable');
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            if (row.style.display === 'none') return;
            // Obligation Amount
            let obligationCell = row.querySelector('.obligation-amount');
            let poCell = row.querySelector('.po-amount');
            let disbursementCell = row.querySelector('.dv-amount');
            // Get the value, stripping formatting if needed
            let obligationVal = 0;
            if (obligationCell) {
                let span = obligationCell.querySelector('button');
                let text = span ? span.textContent : obligationCell.textContent;
                text = text.replace(/[^\d.-]/g, '');
                obligationVal = parseFloat(text) || 0;
            }
            let poVal = 0;
            if (poCell) {
                let span = poCell.querySelector('button');
                let text = span ? span.textContent : poCell.textContent;
                text = text.replace(/[^\d.-]/g, '');
                poVal = parseFloat(text) || 0;
            }
            let disbursementVal = 0;
            if (disbursementCell) {
                let span = disbursementCell.querySelector('button');
                let text = span ? span.textContent : disbursementCell.textContent;
                text = text.replace(/[^\d.-]/g, '');
                disbursementVal = parseFloat(text) || 0;
            }
            totalObligation += obligationVal;
            totalPO += poVal;
            totalDisbursement += disbursementVal;
        });
        // Update sticky footer totals only
        const footerObligation = document.getElementById('footerTotalObligationAmount');
        const footerPO = document.getElementById('footerTotalPOAmount');
        const footerDisbursement = document.getElementById('footerTotalDisbursementAmount');
        if (footerObligation) footerObligation.textContent = totalObligation.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        if (footerPO) footerPO.textContent = totalPO.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        if (footerDisbursement) footerDisbursement.textContent = totalDisbursement.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Initialize all event handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize table totals
        computeTableTotals();
        
        // Initialize search handling
        document.getElementById('searchInput').addEventListener('input', computeTableTotals);
        
        // Initialize context menu handling
        document.addEventListener('click', function(e) {
            const contextMenu = document.getElementById('obligationContextMenu');
            if (contextMenu && !contextMenu.contains(e.target) && !e.target.closest('[oncontextmenu]')) {
                contextMenu.style.display = 'none';
                contextMenu.classList.add('hidden');
            }
        });

        // Prevent default context menu on the table
        document.querySelector('.overflow-x-auto').addEventListener('contextmenu', function(e) {
            if (!e.target.closest('[oncontextmenu]')) {
                e.preventDefault();
            }
        });
    });
    // If you have other filters, add their event listeners here to call computeTableTotals

    // If you use pagination or AJAX, call computeTableTotals after table updates
    function filterTable() {
        // Declare variables
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toLowerCase();
        table = document.getElementById("obligationsTable");
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
        computeTableTotals();
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
        // Close regular dropdowns
        document.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.add("hidden"));
        
        // Close context menu
        const contextMenu = document.getElementById('obligationContextMenu');
        if (contextMenu) {
            contextMenu.style.display = 'none';
            contextMenu.classList.add('hidden');
        }
    }

    // Close dropdown if click happens outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative.inline-block')) {
            closeAllDropdowns();
        }
    });

    /* Modal Create ObligationAdjustment - FIXED VERSION */
let isSubmittingObligationAdjustment = false;

function openCreateObligationAdjustmentModal(obligationId) {
    closeAllDropdowns();
    isSubmittingObligationAdjustment = false;
    
    fetch(`/obligations/${obligationId}/obligation-adjustment-modal`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('createObligationAdjustmentModalContainer').innerHTML = html;
            const modal = document.getElementById('createObligationAdjustmentModal');
            
            // Remove aria-hidden when modal is shown
            modal.removeAttribute('aria-hidden');
            modal.classList.remove('hidden');
            
            // Prevent form submission on Enter key
            const form = document.getElementById('createObligationAdjustmentForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!isSubmittingObligationAdjustment) {
                        e.preventDefault();
                        validateCreateObligationAdjustmentForm();
                    }
                });
                
                // Also prevent Enter key from submitting
                form.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
}

function closeCreateObligationAdjustmentModal() {
    const modal = document.getElementById('createObligationAdjustmentModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
    }
}

function validateCreateObligationAdjustmentForm() {
    const modal = document.getElementById('createObligationAdjustmentModal');
    
    if (!modal) {
        console.error('Modal not found!');
        return;
    }
    
    const remarks = modal.querySelector('#adjustment_remarks');
    const adjustmentAmounts = modal.querySelectorAll("input[name^='adjusted_amount']");
    const tableMessage = modal.querySelector('#tableMessage');
    const remarksError = modal.querySelector('#remarksError');

    let isValid = true;

    // Clear previous messages
    if (tableMessage) {
        tableMessage.innerText = '';
        tableMessage.classList.add('hidden');
    }
    if (remarksError) {
        remarksError.innerText = '';
    }

    // Validate remarks
    if (!remarks.value.trim()) {
        if (remarksError) {
            remarksError.innerText = 'Remarks are required.';
        }
        isValid = false;
    }

    // Validate at least one adjustment amount is non-zero and greater than zero
    let atLeastOneNonZero = false;
    let hasNegativeOrZero = false;
    adjustmentAmounts.forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val) && val !== 0) {
            if (val <= 0) {
                hasNegativeOrZero = true;
            }
            atLeastOneNonZero = true;
        }
    });

    if (hasNegativeOrZero) {
        if (tableMessage) {
            tableMessage.innerText = 'Adjusted amounts must be greater than zero.';
            tableMessage.classList.remove('hidden');
        }
        isValid = false;
    } else if (!atLeastOneNonZero) {
        if (tableMessage) {
            tableMessage.innerText = 'At least one adjustment amount must be non-zero.';
            tableMessage.classList.remove('hidden');
        }
        isValid = false;
    } else {
        // Check if no actual adjustment was made (adjusted amount equals current obligation amount)
        let allAdjustmentsNoChange = true;
        adjustmentAmounts.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val !== 0) {
                const row = input.closest('tr');
                const obrAmountCell = row.querySelector("td:nth-child(5)");
                const currentObrAmount = parseFloat(obrAmountCell.textContent.replace(/,/g, '')) || 0;
                
                // FIX: Use toFixed(2) for comparison to avoid floating-point precision issues
                if (val.toFixed(2) !== currentObrAmount.toFixed(2)) {
                    allAdjustmentsNoChange = false;
                }
            }
        });

        if (allAdjustmentsNoChange) {
            if (tableMessage) {
                tableMessage.innerText = 'No adjustment was made. The adjusted amounts are the same as the current obligation amounts.';
                tableMessage.classList.remove('hidden');
            }
            isValid = false;
        } else {
            // Check PO minimum validation for all non-zero adjustments
            let poValidationFailed = false;
            adjustmentAmounts.forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val) && val !== 0) {
                    const row = input.closest('tr');
                    const poAmountCell = row.querySelector('.po-amount-cell');
                    if (poAmountCell) {
                        const poAmount = parseFloat(poAmountCell.textContent.replace(/,/g, '')) || 0;
                        // FIX: Use toFixed(2) for comparison
                        if (poAmount > 0 && parseFloat(val.toFixed(2)) < parseFloat(poAmount.toFixed(2))) {
                            poValidationFailed = true;
                        }
                    }
                }
            });

            if (poValidationFailed) {
                if (tableMessage) {
                    tableMessage.innerText = 'Adjusted amount cannot be less than Purchase Order amount.';
                    tableMessage.classList.remove('hidden');
                }
                isValid = false;
            }
        }
    }

    if (isValid) {
        console.log('Form validation passed, submitting...');
        const form = document.getElementById('createObligationAdjustmentForm');
        if (form) {
            console.log('Form found, setting flag and submitting...');
            // Set flag to allow submission
            isSubmittingObligationAdjustment = true;
            // Submit the form
            form.submit();
        } else {
            console.error('Form not found for submission!');
        }
    } else {
        console.log('Form validation failed');
    }
}

// Function to compute adjustment amount for each row
function computeAdjustmentAmountForRow(row) {
    const obrAmountCell = row.querySelector("td:nth-child(5)");
    const adjustedAmountInput = row.querySelector("input[name^='adjusted_amount']");
    const adjustmentAmountCell = row.querySelector("td:nth-child(7)");

    if (obrAmountCell && adjustedAmountInput && adjustmentAmountCell) {
        const obrAmount = parseFloat(obrAmountCell.textContent.replace(/,/g, '')) || 0;
        const adjustedAmount = parseFloat(adjustedAmountInput.value.replace(/,/g, '')) || 0;
        const adjustmentAmount = adjustedAmount - obrAmount;

        adjustmentAmountCell.textContent = adjustmentAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}

function validateAmountAdjustment(inputElement) {
    const row = inputElement.closest('tr');
    const poAmountCell = row.querySelector('.po-amount-cell');
    const errorSpan = row.querySelector('.adjustmentAmountError');

    let minAllowed = 0;
    let poAmount = 0;

    // Check if PO amount exists and is > 0 - sets minimum constraint only (can exceed)
    if (poAmountCell) {
        poAmount = parseFloat(poAmountCell.textContent.replace(/,/g, '')) || 0;
        if (poAmount > 0) {
            minAllowed = poAmount;
        }
    }

    const currentValue = parseFloat(inputElement.value) || 0;

    // Show warning if below minimum but don't auto-correct
    if (currentValue > 0 && currentValue < minAllowed) {
        inputElement.classList.add('border-red-500');
        if (errorSpan && minAllowed > 0) {
            errorSpan.innerText = `Adjustment amount must be at least ${minAllowed.toFixed(2)} (Purchase Order amount)`;
            errorSpan.classList.remove('hidden');
        }
        return;
    }

    // Clear error if valid
    inputElement.classList.remove('border-red-500');
    if (errorSpan) {
        errorSpan.innerText = '';
        errorSpan.classList.add('hidden');
    }
}

function updateAdjustedAmountTotal() {
    const adjustedInputs = document.querySelectorAll("input[name^='adjusted_amount']");
    let total = 0;
    adjustedInputs.forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val) && val !== 0) {
            total += val;
        }
    });
    const totalCell = document.getElementById('adjustedAmountTotalCell');
    if (totalCell) {
        totalCell.textContent = total.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('adjusted_amount')) {
            const row = event.target.closest('tr');
            if (row) {
                computeAdjustmentAmountForRow(row);
                updateAdjustedAmountTotal();
            }
        }
    });

    // Initial update on modal open
    document.addEventListener('DOMContentLoaded', function() {
        updateAdjustedAmountTotal();
        document.querySelectorAll("input[name^='adjusted_amount']").forEach(input => {
            input.addEventListener('input', updateAdjustedAmountTotal);
        });
    });


    /* Modal Create PurchaseOrder */
    function openCreatePOModal(obligationId) {
        closeAllDropdowns();

        fetch(`/obligations/${obligationId}/purchase-order-modal`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('createPOModalContainer').innerHTML = html;
                // Only show the modal after content is loaded
                document.getElementById('createPOModal').classList.remove('hidden');
            });
    }

    function closeCreatePOModal() {

        document.getElementById('createPOModal').classList.add('hidden');
    }

    function validateAmountPO(inputElement) {
        const maxBalance = parseFloat(inputElement.dataset.balance || "0");
        const inputValue = parseFloat(inputElement.value || "0");

        if (inputValue > maxBalance) {
            inputElement.value = maxBalance.toFixed(2);
            inputElement.title = `Max allowed is ₱${maxBalance.toFixed(2)}`;
        }
        updatePOAmountTotal();
    }

    function updatePOAmountTotal() {
        const poInputs = document.querySelectorAll("input[name^='po_amount']");
        let total = 0;
        poInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                total += val;
            }
        });
        const totalCell = document.getElementById('poAmountTotalCell');
        if (totalCell) {
            totalCell.textContent = total.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    function validateFormCreatePO() {
        const po_remarks = document.getElementById('po_remarks');
        const po_number = document.getElementById('po_number');
        const pr_no = document.getElementById('pr_no');
        const delivery_period = document.getElementById('delivery_period');
        const supplier = document.getElementById('supplier');
        const adjustmentAmounts = document.querySelectorAll("input[name^='adjusted_amount']");
        const poInputs = document.querySelectorAll("input[name^='po_amount']");

        let atLeastOnePOFilled = false;
        let isValid = true;

        // Validate PO Number
        if (!po_number.value.trim()) {
            document.getElementById('po_numberError').innerText = 'PO Number is required.';
            isValid = false;
        } else {
            document.getElementById('po_numberError').innerText = '';
        }
        // Validate PR Number
        if (!pr_no.value.trim()) {
            document.getElementById('pr_noError').innerText = 'PR Number is required.';
            isValid = false;
        } else {
            document.getElementById('pr_noError').innerText = '';
        }
        // Validate Delivery Period
        if (!delivery_period.value.trim()) {
            document.getElementById('delivery_periodError').innerText = 'Delivery Period is required.';
            isValid = false;
        } else {
            document.getElementById('delivery_periodError').innerText = '';
        }
        // Validate Supplier
        if (!supplier.value.trim()) {
            document.getElementById('supplierError').innerText = 'Supplier is required.';
            isValid = false;
        } else {
            document.getElementById('supplierError').innerText = '';
        }

        // Ensure at least one po_amount is filled
        poInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                atLeastOnePOFilled = true;
            }
        });

        // Validate po_amount fields and require at least one valid entry
        if (!atLeastOnePOFilled) {
            document.getElementById('tableMessagePO').classList.remove('hidden');
            document.getElementById('tableMessagePO').innerText = 'Enter at least one valid Purchase Order amount.';
            isValid = false;
        } else {
            document.getElementById('tableMessagePO').classList.add('hidden');
            document.getElementById('tableMessagePO').innerText = '';
        }

        if (isValid) {
            document.getElementById('CreatePurchaseOrderForm').submit();
        }
    }


    /* Modal Create Disbursement */
    function openCreateDisbursementModal(obligationId) {
        closeAllDropdowns();
        fetch(`/obligations/${obligationId}/disbursement-modal`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('createDisbursementModalContainer').innerHTML = html;
                const modal = document.getElementById('createDisbursementModal');
                modal.classList.remove('hidden');

                // Attach event listener AFTER modal is loaded
                const statusField = modal.querySelector('#status');
                if (statusField) {
                    statusField.addEventListener('change', function() {
                        if (statusField.value === 'Full Payment') {
                            modal.querySelectorAll('input[name^="disbursement_amount"]').forEach(function(input) {
                                input.value = input.dataset.balance || "0";
                            });
                            updateDVAmountTotal();
                        }
                    });

                    // If modal opens with "Full Payment" already selected (edit mode)
                    if (statusField.value === 'Full Payment') {
                        modal.querySelectorAll('input[name^="disbursement_amount"]').forEach(function(input) {
                            input.value = input.dataset.balance || "0";
                        });
                        updateDVAmountTotal();
                    }
                }

                // Run initial calculation inside modal
                updateDVAmountTotal();
            });
    }

    function closeCreateDisbursementModal() {
        document.getElementById('createDisbursementModal').classList.add('hidden');
    }

    function validateDisbursementAmount(inputElement) {
        const maxBalance = parseFloat(inputElement.dataset.balance || "0");
        const inputValue = parseFloat(inputElement.value || "0");

        if (inputValue > maxBalance) {
            inputElement.value = maxBalance.toFixed(2);
            inputElement.title = `Max allowed is ₱${maxBalance.toFixed(2)}`;
        }
    }

    function updateDVAmountTotal() {
        const adjustedInputs = document.querySelectorAll("input[name^='disbursement_amount']");
        let total = 0;
        adjustedInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                total += val;
            }
        });
        const totalCell = document.getElementById('dvAmountTotalCell');
        if (totalCell) {
            totalCell.textContent = total.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('disbursement_amount')) {
            updateDVAmountTotal();
        }
    });

    function validateFormCreateDisbursement() {
        let isValid = true;

        // Clear previous error messages
        document.getElementById('dv_noError').innerText = '';
        document.getElementById('statusError').innerText = '';
        document.getElementById('tableMessage').classList.add('hidden');
        document.getElementById('tableMessage').innerText = '';

        // Validate PO Number
        const poNumber = document.getElementById('dv_no').value.trim();
        if (poNumber === '') {
            document.getElementById('dv_noError').innerText = 'DV / Check Number is required.';
            isValid = false;
        }

        // Validate Status
        const status = document.getElementById('status').value;
        if (status === '') {
            document.getElementById('statusError').innerText = 'Status is required.';
            isValid = false;
        }

        // Validate at least one DV Amount is entered and does not exceed balance
        const amountInputs = document.querySelectorAll('input[name^="disbursement_amount"]');
        let atLeastOneAmountEntered = false;

        amountInputs.forEach(input => {
            const value = parseFloat(input.value || "0");
            const maxBalance = parseFloat(input.dataset.balance || "0");

            if (value > 0) {
                atLeastOneAmountEntered = true;
                if (value > maxBalance) {
                    input.nextElementSibling.innerText = `Amount exceeds the available balance of ₱${maxBalance.toFixed(2)}.`;
                    isValid = false;
                } else {
                    input.nextElementSibling.innerText = '';
                }
            } else {
                input.nextElementSibling.innerText = '';
            }
        });

        if (!atLeastOneAmountEntered) {
            document.getElementById('tableMessageDV').innerText = 'Please enter at least one DV / Check Amount.';
            document.getElementById('tableMessageDV').classList.remove('hidden');
            isValid = false;
        }

        // If all validations pass, submit the form
        if (isValid) {
            document.getElementById('CreateDisbursementForm').submit();
        }
    }
</script>