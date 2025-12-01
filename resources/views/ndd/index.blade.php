<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                @php
                $selectedYear = request('year1', date('Y'));
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('List of Not Yet Due and Demandable') }}
                <span class="text-blue-800 dark:text-blue-400">
                    (CY {{ $selectedYear }})
                </span>
            </h3>
            </div>
        </div>
    </x-slot>

    <!-- Unified Filter Section -->
    <form method="GET" action="" class="bg-white p-4 rounded-lg shadow-md mb-3 dark:bg-gray-800" id="filterForm">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Filters</h4>
        <!-- Shared validation message -->
        <span id="signatory_error" class="text-red-500 text-xs mb-2 hidden"></span>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 items-center">
            <!-- Year Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="year1"
                    id="year1"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ request('year1', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Office Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="office_filter"
                    id="officeFilter"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    <option value="">All Offices</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ request('office_filter') == $office->id ? 'selected' : '' }}>
                            {{ $office->office_abbreviation }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- As of Filter -->
            <div class="flex items-center space-x-2">
                <x-form.input
                    name="as_of_filter"
                    type="date"
                    autocomplete="off"
                    id="as_of_filter"
                    value="{{ request('as_of_filter', now()->format('Y-m-d')) }}"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                </x-form.input>
            </div>
            <!-- Signatory Name Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="signatory_name"
                    id="signatory_name"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    <option value="">Select Signatory</option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->name }}" {{ request('signatory_name') == $employee->name ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                    @endforeach
                </x-form.select>
            </div>
            <!-- Signatory Designation Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="signatory_designation"
                    id="signatory_designation"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    <option value="">Select Designation</option>
                    <option value="Provincial Budget Officer" {{ request('signatory_designation') == 'Provincial Budget Officer' ? 'selected' : '' }}>Provincial Budget Officer</option>
                    <option value="Acting Provincial Budget Officer" {{ request('signatory_designation') == 'Acting Provincial Budget Officer' ? 'selected' : '' }}>Acting Provincial Budget Officer</option>
                    <option value="OIC, Provincial Budget Officer" {{ request('signatory_designation') == 'OIC, Provincial Budget Officer' ? 'selected' : '' }}>OIC, Provincial Budget Officer</option>
                </x-form.select>
    </form>
            </div>
            <div class="flex items-center space-x-2">
                <form method="GET" action="" style="display:inline;">
                    <input type="hidden" name="year1" value="{{ request('year1') }}">
                    <input type="hidden" name="office_filter" value="{{ request('office_filter') }}">
                    <input type="hidden" name="as_of_filter" value="{{ request('as_of_filter') }}">
                    <input type="hidden" name="signatory_name" value="{{ request('signatory_name') }}">
                    <input type="hidden" name="signatory_designation" value="{{ request('signatory_designation') }}">

                    <button type="submit" class="text-green-700 border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        Generate Excel
                    </button>
                </form>
            </div>
        </div>
        
    

    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mt-6 mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="overflow-x-auto overflow-y-auto max-h-[700px]">
                <table id="dashboardTable" class="min-w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-gray-100 dark:bg-gray-200 dark:text-gray-900 border border-gray-300 dark:border-gray-600">
                        <tr>
                            <th class="px-2 py-2 min-w-[200px] text-center border border-gray-300 dark:border-gray-600">Payee / Supplier / Particulars</th>
                            <th class="px-2 py-2 min-w-[100px] text-center border border-gray-300 dark:border-gray-600">Budget Control No.</th>
                            <th class="px-2 py-2 min-w-[100px] text-center border border-gray-300 dark:border-gray-600">PO Number</th>
                            <th class="px-2 py-2 min-w-[100px] text-center border border-gray-300 dark:border-gray-600">PO Date</th>
                            <th class="px-2 py-2 min-w-[100px] text-center border border-gray-300 dark:border-gray-600">Amount</th>
                            <th class="px-2 py-2 min-w-[150px] text-center border border-gray-300 dark:border-gray-600">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hasObligations = is_countable($obligations) ? count($obligations) > 0 : !empty($obligations);
                        @endphp
                        
                        @if($hasObligations)
                            @foreach($obligations as $officeName => $officeObligations)
                                <!-- Office Header Row -->
                                <tr class="bg-gray-100 dark:bg-gray-600">
                                    <td colspan="6" class="px-2 py-2 font-bold text-xs border border-gray-300 dark:border-gray-600">
                                        {{ $officeName }}
                                    </td>
                                </tr>
                                
                                <!-- Obligations for this office -->
                                @foreach($officeObligations as $obligation)
                                    <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-1 py-1 border border-gray-300 dark:border-gray-600">{{ $obligation['payee'] }}</td>
                                        <td class="px-1 py-1 border border-gray-300 dark:border-gray-600 text-center">{{ $obligation['budget_control_no'] }}</td>
                                        <td class="px-1 py-1 border border-gray-300 dark:border-gray-600 text-center">{{ $obligation['po_number'] }}</td>
                                        <td class="px-1 py-1 border border-gray-300 dark:border-gray-600 text-center">{{ $obligation['po_date'] }}</td>
                                        <td class="px-1 py-1 border border-gray-300 dark:border-gray-600 text-right">{{ $obligation['amount'] }}</td>
                                        <td class="px-1 py-1 border border-gray-300 dark:border-gray-600">{{ $obligation['remarks'] }}</td>
                                    </tr>
                                @endforeach
                                
                                <!-- Office Total Row -->
                                <tr class="bg-gray-200 dark:bg-gray-700 font-bold border-b">
                                    <td colspan="4" class="px-2 py-2 text-xs border border-gray-300 dark:border-gray-600 text-right">Total ({{ $officeName }}):</td>
                                    <td class="px-1 py-1 border border-gray-300 dark:border-gray-600 text-right">{{ $totals[$officeName] }}</td>
                                    <td class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                                </tr>
                            @endforeach
                            
                            <!-- Grand Total Row -->
                            <tr class="bg-gray-800 dark:bg-gray-900 text-white dark:text-gray-100 font-bold">
                                <td colspan="4" class="px-2 py-2 text-xs border border-gray-300 dark:border-gray-600 text-right">Grand Total:</td>
                                <td class="px-1 py-1 border border-gray-300 dark:border-gray-600 text-right">{{ $totals['GRAND_TOTAL'] }}</td>
                                <td class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="6" class="px-1 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No obligations found matching the criteria.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Validation for the signatory fields and office
        function validateSignatories() {
            const name = document.getElementById('signatory_name').value.trim();
            const designation = document.getElementById('signatory_designation').value.trim();
            const office = document.getElementById('officeFilter').value.trim();
            const errorSpan = document.getElementById('signatory_error');

            let errorMessage = '';
            

            // Check signatory fields
            if (!name && !designation) {
                errorMessage = 'Please select both Signatory Name and Designation.';
            } else if (!name) {
                errorMessage = 'Please select a Signatory Name.';
            } else if (!designation) {
                errorMessage = 'Please select a Designation.';
            }

            if (errorMessage) {
                errorSpan.textContent = errorMessage;
                errorSpan.classList.remove('hidden');
                return false;
            } else {
                errorSpan.classList.add('hidden');
                return true;
            }
        }

        // Intercept Excel Export Submit
        document.querySelector('form[action=""]')
            .addEventListener('submit', function(e) {
                if (!validateSignatories()) {
                    e.preventDefault();
                }
            });
    </script>

</x-app-layout>