<x-app-layout>
    <x-slot name="header">
    <div class="flex justify-between items-center">
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
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

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
                    <x-form.select name="office_allotment_class_filter" id="officeAllotmentClass" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-full text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" onchange="this.form.submit()">
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
    </div>
    
    <div class="bg-white overflow-hidden sm:rounded-lg shadow-md mb-2 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <!-- Left: Action Button -->
                <button onclick="openCreateCOSListModal()" class="text-blue-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                    <i class="fas fa-plus text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Add Contract of Service') }}
                </button>
                <!-- Right: Total Records and Search Input -->
                <div class="flex items-center space-x-4">
                    <!-- Total Records -->
                    <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $cosList->total() ?? count($cosList) }}</span>
                    </div>
                    <!-- Search Section -->
                    <form id="searchForm" method="GET" action="{{ route('cos_lists.index') }}" class="flex items-center space-x-2">
                        <!-- Hidden inputs to preserve filters -->
                        <input type="hidden" name="year1" value="{{ request('year1', date('Y')) }}">
                        <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
                        <input type="hidden" name="appropriation_filter" value="{{ request('appropriation_filter') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', '10') }}">
                        
                        <x-form.select name="search_column" id="searchColumn" class="border border-gray-300 rounded-lg px-4 py-2 text-xs w-40 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Columns</option>
                            <option value="employee_name" {{ request('search_column') == 'employee_name' ? 'selected' : '' }}>Employee Name</option>
                            <option value="position_title" {{ request('search_column') == 'position_title' ? 'selected' : '' }}>Position Title</option>
                            <option value="salary_grade" {{ request('search_column') == 'salary_grade' ? 'selected' : '' }}>Salary Grade</option>
                            <option value="period" {{ request('search_column') == 'period' ? 'selected' : '' }}>Period</option>
                            <option value="monthly_rate" {{ request('search_column') == 'monthly_rate' ? 'selected' : '' }}>Monthly Rate</option>
                            <option value="total_amount" {{ request('search_column') == 'total_amount' ? 'selected' : '' }}>Total Amount</option>
                        </x-form.select>
                        <x-form.input type="text" name="search" id="searchInput" value="{{ request('search') }}" autocomplete="off" placeholder="Search for Contract of Services" class="border border-gray-300 rounded-lg px-4 py-2 text-xs flex-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white w-80" />
                        <button type="submit" class="text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-md">
                <div class="max-h-[560px] overflow-y-auto">
                    <table class="min-w-full text-xs text-center text-gray-600 dark:text-gray-300">
                        <thead class="text-center border-b-2 border-t-2 border-gray-700 text-xs text-gray-700 bg-gray-200 dark:bg-gray-900 dark:text-gray-400 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Office
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Employee Name
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Position Title
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Salary Grade
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Period
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Monthly Rate
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Total Amount
                                </th><th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Basis
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Remarks
                                </th>
                                <th class="px-3 py-3 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($cosList as $cos)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                                data-cos="{{ json_encode($cos->only(['id','office_allotment_class_id','appropriation_id','employee_id','employee_name','position_title','salary_grade','from_date','to_date','monthly_rate','annual_rate','remarks','basis'])) }}">
                                <td class="font-semibold text-left px-3 py-3">
                                    {{ $cos->officeAllotmentClass->offices->office_abbreviation ?? '-' }}
                                </td>
                                <td class="font-semibold text-left px-3 py-3 {{ $cos->employee_name === 'Vacant' ? 'text-blue-600 dark:text-blue-400' : '' }}">{{ $cos->employee_name }}</td>
                                <td class="text-left px-3 py-3">{{ $cos->position_title }}</td>
                                <td class="text-center px-3 py-3">{{ $cos->salary_grade ?? '-' }}</td>
                                <td class="text-center px-3 py-3 max-w-xs">@if($cos->from_date && $cos->to_date){{ \Carbon\Carbon::parse($cos->from_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($cos->to_date)->format('M d, Y') }}@else-@endif</td>
                                <td class="px-3 py-3 text-right">{{ number_format($cos->monthly_rate, 2) }}</td>
                                <td class="px-3 py-3 text-right font-semibold">{{ number_format($cos->annual_rate, 2) }}</td>
                                <td class="text-left px-3 py-3 max-w-xs text-xs">{{ $cos->basis ?? '-' }}</td>
                                <td class="text-left px-3 py-3 max-w-xs text-xs">{{ $cos->remarks ?? '-' }}</td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex gap-2 justify-center">
                                        <button onclick="openEditModalFromRow(this)" type="button" class="text-amber-600 hover:text-white border border-amber-600 hover:bg-amber-600 rounded-lg px-2 py-1.5 text-xs dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 transition-colors" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="openDeleteModalFromRow(this)" type="button" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 rounded-lg px-2 py-1.5 text-xs dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 transition-colors" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('No Contract of Services records found.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                        @if($cosList->count() > 0)
                        <tfoot class="bg-gray-100 dark:bg-gray-700 border-t-2 border-gray-300 dark:border-gray-600">
                            @if(!is_null($totalAppropriation))
                            <tr class="bg-gray-100 dark:bg-gray-800 border-t border-b border-gray-300 dark:border-gray-600">
                                <td colspan="6" class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                    Appropriation (Selected Account):
                                </td>
                                
                                <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100 text-base">
                                    {{ number_format($totalAppropriation, 2) }}
                                </td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="6" class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                    Total Amount:
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100 text-base">
                                    {{ number_format($totalAnnualRate, 2) }}
                                </td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                            </tr>
                            @if(!is_null($totalAppropriation))
                            @php
                            $balance = $totalAppropriation - $totalAnnualRate;
                            @endphp
                            <tr class="border-t border-gray-300 dark:border-gray-600">
                                <td colspan="6" class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                                    Balance:
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-base {{ $balance < 0 ? 'text-red-600 dark:text-red-400' : 'text-blue-700 dark:text-blue-400' }}">
                                    {{ number_format($balance, 2) }}
                                </td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                                <td class="px-3 py-2"></td>
                            </tr>
                            @endif
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($cosList->hasPages())
        <div class="mt-6">
            {{ $cosList->links() }}
        </div>
    @endif

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
            const row = btn.closest('tr');
            const cos = JSON.parse(row.dataset.cos);
            
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
            const row = btn.closest('tr');
            const cos = JSON.parse(row.dataset.cos);
            const officeAbbr = row.dataset.officeAbbr;
            const allotmentClass = row.dataset.allotmentClass;
            
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