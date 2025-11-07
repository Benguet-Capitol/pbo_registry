<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">

            <div>
                @php
                $selectedYear = request('year1', date('Y'));
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                    {{ __('Record of Appropriations and Obligations') }}
                    |
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
            <!-- Office and Allotment Class Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select 
                    name="office_allotment_class_filter" 
                    id="officeAllotmentClass" 
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" 
                    onchange="this.form.submit()">
                    <option value="">All Allotment Classes per Office</option>
                    @foreach($officeAllotmentClasses as $officeAllotmentClass)
                    <option value="{{ $officeAllotmentClass->id }}" {{ request('office_allotment_class_filter') == $officeAllotmentClass->id ? 'selected' : '' }}>
                        {{ $officeAllotmentClass->offices->office_abbreviation }} - {{ $officeAllotmentClass->allotmentClass->class }}
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
            </div>
            
    </form>
    <form method="GET" action="{{ route('saaob.exportExcel') }}" style="display:inline;">
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


    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mt-6 mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table id="dashboardTable" class="min-w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-gray-100 dark:bg-gray-200 dark:text-gray-900 border border-gray-300 dark:border-gray-600">
                        <tr>
                            <th class="px-1 py-1 min-w-[100px] text-center border border-gray-300 dark:border-gray-600" rowspan="2">Date</th>
                            <th class="px-1 py-1 min-w-[100px] text-center border border-gray-300 dark:border-gray-600" rowspan="2">OBR No.</th>
                            <th class="px-1 py-1 min-w-[250px] text-center border border-gray-300 dark:border-gray-600" rowspan="2">Particulars</th>
                            <th class="px-1 py-1 min-w-[150px] text-center border border-gray-300 dark:border-gray-600" rowspan="2">Total</th>
                            @if(request('office_allotment_class_filter') && isset($appropriations) && $appropriations->count() > 0)
                                @foreach($appropriations as $appropriation)
                                <th class="px-1 py-1 min-w-[150px] text-center border border-gray-300 dark:border-gray-600">{{ $appropriation->description }}</th>
                                @endforeach
                            @endif
                        </tr>
                        <tr>
                            @if(request('office_allotment_class_filter') && isset($appropriations) && $appropriations->count() > 0)
                                @foreach($appropriations as $appropriation)
                                <th class="px-1 py-1 text-center border border-gray-300 dark:border-gray-600">{{ $appropriation->account_code }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    @php
                        $formatAmount = function ($amount) {
                            if (is_null($amount) || $amount == 0) {
                                return '-';
                            }

                            return $amount < 0
                                ? '(' . number_format(abs($amount), 2) . ')'
                                : number_format($amount, 2);
                        };
                    @endphp
                    <tbody>
                    @if(request('office_allotment_class_filter') && isset($appropriations) && $appropriations->count() > 0)
                        {{-- First Row: Empty Space --}}
                        <tr>
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                        </tr>

                        {{-- Second Row: Appropriations --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Appropriations
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalAppropriations, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ $formatAmount($appropriation->appropriation, 2) }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Third Row: Supplemental Appropriations --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Supplemental Appropriations
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalSupplemental, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['supplemental']) && $appropriationData[$appropriation->id]['supplemental'] > 0 ? $formatAmount($appropriationData[$appropriation->id]['supplemental'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Fourth Row: Reversions --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Reversions
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalReversions, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['reversion']) && $appropriationData[$appropriation->id]['reversion'] != 0 ? $formatAmount($appropriationData[$appropriation->id]['reversion'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Fifth Row: Realignments --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Realignments
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalRealignments, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['realignment']) && $appropriationData[$appropriation->id]['realignment'] != 0 ? $formatAmount($appropriationData[$appropriation->id]['realignment'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Sixth Row: Total Appropriations (Sum of all above) --}}
                        <tr class="bg-gray-200 dark:bg-gray-600 font-bold">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Total Appropriations
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($grandTotal, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ $formatAmount($appropriationData[$appropriation->id]['total'], 2) }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Empty Row Before Quarters --}}
                        <tr>
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                        </tr>

                        {{-- Quarter 1 Row --}}
                        <tr class="bg-gray-100 dark:bg-gray-600 font-semibold">
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600">1st Quarter</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Released Appropriation
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalQuarter1, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['quarter1']) && $appropriationData[$appropriation->id]['quarter1'] > 0 ? $formatAmount($appropriationData[$appropriation->id]['quarter1'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Quarter 2 Row --}}
                        <tr class="bg-gray-100 dark:bg-gray-600 font-semibold">
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600">2nd Quarter</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Released Appropriation
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalQuarter2, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['quarter2']) && $appropriationData[$appropriation->id]['quarter2'] > 0 ? $formatAmount($appropriationData[$appropriation->id]['quarter2'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Quarter 3 Row --}}
                        <tr class="bg-gray-100 dark:bg-gray-600 font-semibold">
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600">3rd Quarter</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Released Appropriation
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalQuarter3, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['quarter3']) && $appropriationData[$appropriation->id]['quarter3'] > 0 ? $formatAmount($appropriationData[$appropriation->id]['quarter3'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Quarter 4 Row --}}
                        <tr class="bg-gray-100 dark:bg-gray-600 font-semibold">
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600">4th Quarter</td>
                        </tr>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-2 py-2 text-left border border-gray-300 dark:border-gray-600">
                                Released Appropriation
                            </td>
                            
                            <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                {{ $formatAmount($totalQuarter4, 2) }}
                            </td>

                            @foreach($appropriations as $appropriation)
                                <td class="px-2 py-2 text-right border border-gray-300 dark:border-gray-600">
                                    {{ isset($appropriationData[$appropriation->id]['quarter4']) && $appropriationData[$appropriation->id]['quarter4'] > 0 ? $formatAmount($appropriationData[$appropriation->id]['quarter4'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>


                        {{-- Empty Row Before Obligations --}}
                        <tr>
                            <td colspan="{{ 4 + $appropriations->count() }}" class="px-1 py-1 border border-gray-300 dark:border-gray-600"></td>
                        </tr>

                    @else
                        {{-- No office allotment class selected --}}
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-info-circle mr-2"></i>
                                Please select an Office Allotment Class to view appropriations and obligations.
                            </td>
                        </tr>
                    @endif
                    
                </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Validation for the signatory fields
        function validateSignatories() {
            const name = document.getElementById('signatory_name').value.trim();
            const designation = document.getElementById('signatory_designation').value.trim();
            const errorSpan = document.getElementById('signatory_error');

            let errorMessage = '';
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
        document.querySelector(`form[action="{{ route('saaob.exportExcel') }}"]`)
            .addEventListener('submit', function(e) {
                if (!validateSignatories()) {
                    e.preventDefault();
                }
            });
    </script>

</x-app-layout>