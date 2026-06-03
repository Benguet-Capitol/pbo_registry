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

    <!-- Success Toast Notification -->
    <div id="success-toast" class="toast fixed top-6 right-6 z-50 transform transition-all duration-300 ease-in-out opacity-0 translate-x-96 pointer-events-none hide">
        <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-300 rounded-lg shadow-lg p-4 dark:bg-gradient-to-r dark:from-green-900 dark:to-green-800 dark:border-green-700">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-100" id="toast-message">File downloaded successfully</h3>
                    <p class="mt-1 text-sm text-green-700 dark:text-green-200">Your Excel report is ready.</p>
                </div>
                <button onclick="closeSuccessToast()" class="ml-4 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Toast Notification -->
    <div id="loading-toast" class="toast fixed top-6 right-6 z-50 transform transition-all duration-300 ease-in-out opacity-0 translate-x-96 pointer-events-none hide">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-300 rounded-lg shadow-lg p-4 dark:bg-gradient-to-r dark:from-blue-900 dark:to-blue-800 dark:border-blue-700">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.581 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-100">Generating Excel Report</h3>
                    <p class="mt-1 text-sm text-blue-700 dark:text-blue-200">Please wait while your file is being generated...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content Wrapper with Transition -->
    <div class="page-transition">

    <!-- Unified Filter Section -->
    <form method="GET" action="" class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-6 dark:bg-gray-800 transition-all duration-300 ease-in-out" id="filterForm">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
                Filters
            </h4>
            <!-- Shared validation message -->
            <span id="signatory_error" class="text-red-600 text-sm font-semibold mb-3 hidden block px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900 border-l-4 border-red-600 dark:border-red-400 animate-pulse transition-opacity duration-300 ease-in-out"></span>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 items-center mb-3">
                <!-- Year Filter -->
                <div class="flex items-center space-x-2">
                    <x-form.select
                        name="year1"
                        id="year1"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
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
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
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
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                        onchange="this.form.submit()">
                    </x-form.input>
                </div>
                <!-- Signatory Name Filter -->
                <div class="flex items-center space-x-2">
                    <x-form.select
                        name="signatory_name"
                        id="signatory_name"
                        class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
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
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                    onchange="this.form.submit()">
                    <option value="">Select Designation</option>
                    <option value="Provincial Budget Officer" {{ request('signatory_designation') == 'Provincial Budget Officer' ? 'selected' : '' }}>Provincial Budget Officer</option>
                    <option value="Acting Provincial Budget Officer" {{ request('signatory_designation') == 'Acting Provincial Budget Officer' ? 'selected' : '' }}>Acting Provincial Budget Officer</option>
                    <option value="OIC, Provincial Budget Officer" {{ request('signatory_designation') == 'OIC, Provincial Budget Officer' ? 'selected' : '' }}>OIC, Provincial Budget Officer</option>
                </x-form.select>
                </div>
            </div>

            <!-- Buttons Row -->
            <div class="flex items-center space-x-2 mt-4">
                <button
                    type="button"
                    onclick="exportNDDExcel()"
                    class="text-green-700 inline-flex leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4"></i>Generate Excel
                </button>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            <div class="overflow-x-auto overflow-y-auto max-h-[700px] border border-gray-300 dark:border-gray-600 rounded-md">
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
        function showSuccessToast(message = 'Success!') {
            const toast = document.getElementById('success-toast');
            const successMessage = document.getElementById('toast-message');
            if (successMessage) successMessage.textContent = message;
            if (toast) {
                toast.classList.remove('hide');
                toast.classList.add('show');
                setTimeout(() => closeSuccessToast(), 4000);
            }
        }

        function closeSuccessToast() {
            const toast = document.getElementById('success-toast');
            if (toast) {
                toast.classList.remove('show');
                toast.classList.add('hide');
            }
        }

        function showLoadingToast() {
            const toast = document.getElementById('loading-toast');
            if (toast) {
                toast.classList.remove('hide');
                toast.classList.add('show');
            }
        }

        function closeLoadingToast() {
            const toast = document.getElementById('loading-toast');
            if (toast) {
                toast.classList.remove('show');
                toast.classList.add('hide');
            }
        }

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

        async function exportNDDExcel() {
            if (!validateSignatories()) return;
            
            showLoadingToast();
            
            const params = new URLSearchParams({
                year1: document.getElementById('year1').value,
                office_filter: document.getElementById('officeFilter').value,
                as_of_filter: document.getElementById('as_of_filter').value,
                signatory_name: document.getElementById('signatory_name').value,
                signatory_designation: document.getElementById('signatory_designation').value
            });

            try {
                const response = await fetch(`/ndd/export-excel?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Export failed');
                
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `NDD_${new Date().getFullYear()}.xlsx`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);

                closeLoadingToast();
                setTimeout(() => showSuccessToast('Excel file downloaded successfully!'), 500);
            } catch (error) {
                closeLoadingToast();
                console.error('Export error:', error);
                alert('Error exporting file: ' + error.message);
            }
        }
    </script>

    <!-- CSS Animations -->
    <style>
        @keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}@keyframes fadeIn{from{opacity:0}to{opacity:1}}@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}@keyframes pageSlideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}form{animation:slideUp .3s ease-in-out}.toast{opacity:0;transform:translateX(400px);pointer-events:none;transition:all .3s ease-in-out}.toast.show{opacity:1;transform:translateX(0);pointer-events:auto}.toast.hide{opacity:0;transform:translateX(400px);pointer-events:none}.page-transition{animation:pageSlideUp .4s ease-in-out}
    </style>

</x-app-layout>