<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
            
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                    {{ __('Statement of Appropriations, Allotments, Obligations, Disbursements and Balances') }}
                    |
                    <span class="text-blue-800 dark:text-blue-400">
                        General Fund
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

    <!-- Unified Filter Section -->
    <form method="GET" action="" class="bg-white overflow-hidden shadow-md sm:rounded-lg mb-6 dark:bg-gray-800 transition-all duration-300 ease-in-out" id="filterForm">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 ease-in-out">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-4 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
                Filters
            </h4>
            <!-- Shared validation message -->
            <span id="signatory_error" class="text-red-600 text-sm font-semibold mb-3 hidden block px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900 border-l-4 border-red-600 dark:border-red-400 animate-pulse transition-opacity duration-300 ease-in-out"></span>

        <!-- First row: Year, Office, As of -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 items-center mb-3">
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
        </div>

        <!-- Second row: Signatories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <!-- Prepared By -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-2">
                    Prepared By
                </label>
                <x-form.select
                    name="prepared_signatory_name"
                    id="prepared_signatory_name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                    onchange="this.form.submit()">
                    <option value="">Select Signatory</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->name }}"
                            data-designation="{{ $employee->designation }}"
                            {{ request('prepared_signatory_name') == $employee->name ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </x-form.select>
                <input type="hidden" id="prepared_signatory_designation" name="prepared_signatory_designation" value="{{ request('prepared_signatory_designation', '') }}">
            </div>

            <!-- Certified Correct: Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-2">
                    Certified Correct
                </label>
                <x-form.select
                    name="certified_signatory_name"
                    id="certified_signatory_name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                    onchange="this.form.submit()">
                    <option value="">Select Signatory</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->name }}"
                            data-designation="{{ $employee->designation }}"
                            {{ request('certified_signatory_name') == $employee->name ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>

            <!-- Certified Correct: Designation -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-2 invisible lg:visible">
                    &nbsp; <!-- To align label with Certified Correct -->
                </label>
                <x-form.select
                    name="certified_signatory_designation"
                    id="certified_signatory_designation"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                    onchange="this.form.submit()">
                    <option value="">Select Designation</option>
                    <option value="Provincial Accountant" {{ request('certified_signatory_designation') == 'Provincial Accountant' ? 'selected' : '' }}>Provincial Accountant</option>
                    <option value="Acting Provincial Accountant" {{ request('certified_signatory_designation') == 'Acting Provincial Accountant' ? 'selected' : '' }}>Acting Provincial Accountant</option>
                    <option value="OIC, Provincial Accountant" {{ request('certified_signatory_designation') == 'OIC, Provincial Accountant' ? 'selected' : '' }}>OIC, Provincial Accountant</option>
                </x-form.select>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center space-x-2 mt-4">
            <button
                onclick="printSAAODBGFTable()"
                class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95"
                type="button">
                <i class="fas fa-print text-lg mr-2 -ml-1 w-4 h-4"></i>Print Report
            </button>

            <button
                type="button"
                onclick="exportSAAODBGFExcel()"
                class="text-green-700 inline-flex leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4"></i>Generate Excel
            </button>
        </div>
    </div>

    <div class="p-4 bg-white rounded-md relative dark:bg-gray-800 transition-colors duration-300 ease-in-out">
        <div class="overflow-x-auto overflow-y-auto max-h-[700px] border border-gray-300 dark:border-gray-600 rounded-md">
        <table id="saaodbGFTable" class="w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-white dark:bg-gray-200 dark:text-gray-900">
                        <tr>
                            <th class="px-1 py-1 w-[150px] text-center">Office</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation">Approved Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="sb_appropriation">Supplemental Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="reversion">Reversions</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="realignment">Realignments</th>
                            <th class="px-1 py-1 w-[100px] text-center">Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center">Allotments</th>
                            <th class="px-1 py-1 w-[100px] text-center">Obligations</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="appropriation_accomplishment">Percentage of Obligations / Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center">Unobligated Authorized Appropriation</th>
                            <th class="px-1 py-1 w-[100px] text-center">Actual Disbursements</th>
                            <th class="px-1 py-1 w-[70px] text-center">Percentage of Disbursements / Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center">Unpaid Obligations</th>
                            <th class="px-1 py-1 w-[70px] text-center">Percentage of Disbursements / Obligations</th>
                        </tr>
                    </thead>
                    <tbody class="border border-gray-300 dark:border-gray-600 text-[10px]">
                        @foreach ($offices as $office)
                        <tr id="officeRow" class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-center text-sm">
                            <td colspan="15" class="px-2 py-3">{{ $office->office_name }}</td>
                        </tr>
                        @php
                            // Separate allotment classes into current and continuing (CCO)
                            $currentClasses = $office->allotmentClasses->filter(fn($c) => !str_contains(strtoupper($c->class), 'CCO'));
                            $continuingClasses = $office->allotmentClasses->filter(fn($c) => str_contains(strtoupper($c->class), 'CCO'));
                             @endphp

                            {{-- --- CURRENT APPROPRIATION CLASSES --- --}}
                            @foreach($currentClasses as $index => $class)
                                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-2 py-2 text-center">{{ $class->class }}</td>
                                    <td class="px-2 py-2 text-right" data-key="appropriation">
                                        @if (is_null($class->approved_appropriation) || $class->approved_appropriation == 0)
                                            -
                                        @elseif ($class->approved_appropriation < 0)
                                            ({{ number_format(abs($class->approved_appropriation), 2) }})
                                        @else
                                            {{ number_format($class->approved_appropriation, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                        @if ($class->supplemental == 0)
                                            -
                                        @elseif ($class->supplemental < 0)
                                            ({{ number_format(abs($class->supplemental), 2) }})
                                        @else
                                            {{ number_format($class->supplemental, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="reversion">
                                        @if (is_null($class->reversion) || $class->reversion == 0)
                                            -
                                        @elseif ($class->reversion < 0)
                                            ({{ number_format(abs($class->reversion), 2) }})
                                        @else
                                            {{ number_format($class->reversion, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="realignment">
                                        @if (is_null($class->realignment) || $class->realignment == 0)
                                            -
                                        @elseif ($class->realignment < 0)
                                            ({{ number_format(abs($class->realignment), 2) }})
                                        @else
                                            {{ number_format($class->realignment, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="authorized_appropriation">
                                        @if (is_null($class->authorized_appropriation) || $class->authorized_appropriation == 0)
                                            -
                                        @elseif ($class->authorized_appropriation < 0)
                                            ({{ number_format(abs($class->authorized_appropriation), 2) }})
                                        @else
                                            {{ number_format($class->authorized_appropriation, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="allotment">
                                        @if (is_null($class->allotment) || $class->allotment == 0)
                                            -
                                        @elseif ($class->allotment < 0)
                                            ({{ number_format(abs($class->allotment), 2) }})
                                        @else
                                            {{ number_format($class->allotment, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="obligation">
                                        @if (is_null($class->obligation) || $class->obligation == 0)
                                            -
                                        @elseif ($class->obligation < 0)
                                            ({{ number_format(abs($class->obligation), 2) }})
                                        @else
                                            {{ number_format($class->obligation, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="appropriation_accomplishment">
                                        {{ number_format($class->percent_obligated_to_authorized, 2) }}%
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                        @if (is_null($class->authorized_appropriation_balance) || $class->authorized_appropriation_balance == 0)
                                            -
                                        @elseif ($class->authorized_appropriation_balance < 0)
                                            ({{ number_format(abs($class->authorized_appropriation_balance), 2) }})
                                        @else
                                            {{ number_format($class->authorized_appropriation_balance, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="disbursement">
                                        @if (is_null($class->disbursement) || $class->disbursement == 0)
                                            -
                                        @elseif ($class->disbursement < 0)
                                            ({{ number_format(abs($class->disbursement), 2) }})
                                        @else
                                            {{ number_format($class->disbursement, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="disbursement_to_appropriation">
                                        {{ number_format($class->percent_disbursed_to_authorized, 2) }}%
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="obligation_balance">
                                        @if (is_null($class->obligation_balance) || $class->obligation_balance == 0)
                                            -
                                        @elseif ($class->obligation_balance < 0)
                                            ({{ number_format(abs($class->obligation_balance), 2) }})
                                        @else
                                            {{ number_format($class->obligation_balance, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="disbursement_to_obligation">
                                        {{ number_format($class->percent_disbursed_to_obligated, 2) }}%
                                    </td>

                                </tr>

                                {{-- Insert total after the last current class --}}
                                @if ($loop->last)
                                    <tr class="bg-gray-200 dark:bg-gray-700 font-bold text-right border-t border-b border-gray-700">
                                        <td class="px-2 py-2 text-right">Total Current Appropriation:</td>
                                        <td>
                                            @if (is_null($office->total_current->approved_appropriation) || $office->total_current->approved_appropriation == 0)
                                                -
                                            @elseif ($office->total_current->approved_appropriation < 0)
                                                ({{ number_format(abs($office->total_current->approved_appropriation), 2) }})
                                            @else
                                                {{ number_format($office->total_current->approved_appropriation, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_current->supplemental) || $office->total_current->supplemental == 0)
                                                -
                                            @elseif ($office->total_current->supplemental < 0)
                                                ({{ number_format(abs($office->total_current->supplemental), 2) }})
                                            @else
                                                {{ number_format($office->total_current->supplemental, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_current->reversion) || $office->total_current->reversion == 0)
                                                -
                                            @elseif ($office->total_current->reversion < 0)
                                                ({{ number_format(abs($office->total_current->reversion), 2) }})
                                            @else
                                                {{ number_format($office->total_current->reversion, 2) }}
                                            @endif
                                        </td>

                                        <td data-key="realignment">
                                            @if (is_null($office->total_current->realignment) || $office->total_current->realignment == 0)
                                                -
                                            @elseif ($office->total_current->realignment < 0)
                                                ({{ number_format(abs($office->total_current->realignment), 2) }})
                                            @else
                                                {{ number_format($office->total_current->realignment, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_current->authorized_appropriation) || $office->total_current->authorized_appropriation == 0)
                                                -
                                            @elseif ($office->total_current->authorized_appropriation < 0)
                                                ({{ number_format(abs($office->total_current->authorized_appropriation), 2) }})
                                            @else
                                                {{ number_format($office->total_current->authorized_appropriation, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_current->allotment) || $office->total_current->allotment == 0)
                                                -
                                            @elseif ($office->total_current->allotment < 0)
                                                ({{ number_format(abs($office->total_current->allotment), 2) }})
                                            @else
                                                {{ number_format($office->total_current->allotment, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_current->obligation) || $office->total_current->obligation == 0)
                                                -
                                            @elseif ($office->total_current->obligation < 0)
                                                ({{ number_format(abs($office->total_current->obligation), 2) }})
                                            @else
                                                {{ number_format($office->total_current->obligation, 2) }}
                                            @endif
                                        </td>

                                        <td>{{ number_format($office->total_current->percent_obligated_to_authorized, 2) }}%</td>

                                        <td>
                                            @if (is_null($office->total_current->authorized_appropriation_balance) || $office->total_current->authorized_appropriation_balance == 0)
                                                -
                                            @elseif ($office->total_current->authorized_appropriation_balance < 0)
                                                ({{ number_format(abs($office->total_current->authorized_appropriation_balance), 2) }})
                                            @else
                                                {{ number_format($office->total_current->authorized_appropriation_balance, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_current->disbursement) || $office->total_current->disbursement == 0)
                                                -
                                            @elseif ($office->total_current->disbursement < 0)
                                                ({{ number_format(abs($office->total_current->disbursement), 2) }})
                                            @else
                                                {{ number_format($office->total_current->disbursement, 2) }}
                                            @endif
                                        </td>

                                        <td>{{ number_format($office->total_current->percent_disbursed_to_authorized, 2) }}%</td>

                                        <td>
                                            @if (is_null($office->total_current->obligation_balance) || $office->total_current->obligation_balance == 0)
                                                -
                                            @elseif ($office->total_current->obligation_balance < 0)
                                                ({{ number_format(abs($office->total_current->obligation_balance), 2) }})
                                            @else
                                                {{ number_format($office->total_current->obligation_balance, 2) }}
                                            @endif
                                        </td>

                                        <td>{{ number_format($office->total_current->percent_disbursed_to_obligated, 2) }}%</td>
                                    </tr>
                                @endif
                            @endforeach
                            {{-- --- CONTINUING (CCO) CLASSES --- --}}
                            @foreach($continuingClasses as $index => $class)
                                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-2 py-2 text-center">{{ $class->class }}</td>
                                    <td class="px-2 py-2 text-right" data-key="appropriation">
                                        @if (is_null($class->approved_appropriation) || $class->approved_appropriation == 0)
                                            -
                                        @elseif ($class->approved_appropriation < 0)
                                            ({{ number_format(abs($class->approved_appropriation), 2) }})
                                        @else
                                            {{ number_format($class->approved_appropriation, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                        @if ($class->supplemental == 0)
                                            -
                                        @elseif ($class->supplemental < 0)
                                            ({{ number_format(abs($class->supplemental), 2) }})
                                        @else
                                            {{ number_format($class->supplemental, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="reversion">
                                        @if (is_null($class->reversion) || $class->reversion == 0)
                                            -
                                        @elseif ($class->reversion < 0)
                                            ({{ number_format(abs($class->reversion), 2) }})
                                        @else
                                            {{ number_format($class->reversion, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="realignment">
                                        @if (is_null($class->realignment) || $class->realignment == 0)
                                            -
                                        @elseif ($class->realignment < 0)
                                            ({{ number_format(abs($class->realignment), 2) }})
                                        @else
                                            {{ number_format($class->realignment, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="authorized_appropriation">
                                        @if (is_null($class->authorized_appropriation) || $class->authorized_appropriation == 0)
                                            -
                                        @elseif ($class->authorized_appropriation < 0)
                                            ({{ number_format(abs($class->authorized_appropriation), 2) }})
                                        @else
                                            {{ number_format($class->authorized_appropriation, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="allotment">
                                        @if (is_null($class->allotment) || $class->allotment == 0)
                                            -
                                        @elseif ($class->allotment < 0)
                                            ({{ number_format(abs($class->allotment), 2) }})
                                        @else
                                            {{ number_format($class->allotment, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="obligation">
                                        @if (is_null($class->obligation) || $class->obligation == 0)
                                            -
                                        @elseif ($class->obligation < 0)
                                            ({{ number_format(abs($class->obligation), 2) }})
                                        @else
                                            {{ number_format($class->obligation, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="appropriation_accomplishment">
                                        {{ number_format($class->percent_obligated_to_authorized, 2) }}%
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                        @if (is_null($class->authorized_appropriation_balance) || $class->authorized_appropriation_balance == 0)
                                            -
                                        @elseif ($class->authorized_appropriation_balance < 0)
                                            ({{ number_format(abs($class->authorized_appropriation_balance), 2) }})
                                        @else
                                            {{ number_format($class->authorized_appropriation_balance, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="disbursement">
                                        @if (is_null($class->disbursement) || $class->disbursement == 0)
                                            -
                                        @elseif ($class->disbursement < 0)
                                            ({{ number_format(abs($class->disbursement), 2) }})
                                        @else
                                            {{ number_format($class->disbursement, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="disbursement_to_appropriation">
                                        {{ number_format($class->percent_disbursed_to_authorized, 2) }}%
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="obligation_balance">
                                        @if (is_null($class->obligation_balance) || $class->obligation_balance == 0)
                                            -
                                        @elseif ($class->obligation_balance < 0)
                                            ({{ number_format(abs($class->obligation_balance), 2) }})
                                        @else
                                            {{ number_format($class->obligation_balance, 2) }}
                                        @endif
                                    </td>

                                    <td class="px-2 py-2 text-right" data-key="disbursement_to_obligation">
                                        {{ number_format($class->percent_disbursed_to_obligated, 2) }}%
                                    </td>
                                </tr>

                                {{-- Insert total after the last continuing class --}}
                                @if ($loop->last)
                                    <tr class="bg-gray-200 dark:bg-gray-700 font-bold text-right border-t border-b border-gray-700">
                                        <td class="px-2 py-2 text-right">Total Continuing Capital Outlay:</td>
                                        <td>
                                            @if (is_null($office->total_continuing->approved_appropriation) || $office->total_continuing->approved_appropriation == 0)
                                                -
                                            @elseif ($office->total_continuing->approved_appropriation < 0)
                                                ({{ number_format(abs($office->total_continuing->approved_appropriation), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->approved_appropriation, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_continuing->supplemental) || $office->total_continuing->supplemental == 0)
                                                -
                                            @elseif ($office->total_continuing->supplemental < 0)
                                                ({{ number_format(abs($office->total_continuing->supplemental), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->supplemental, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_continuing->reversion) || $office->total_continuing->reversion == 0)
                                                -
                                            @elseif ($office->total_continuing->reversion < 0)
                                                ({{ number_format(abs($office->total_continuing->reversion), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->reversion, 2) }}
                                            @endif
                                        </td>

                                        <td data-key="realignment">
                                            @if (is_null($office->total_continuing->realignment) || $office->total_continuing->realignment == 0)
                                                -
                                            @elseif ($office->total_continuing->realignment < 0)
                                                ({{ number_format(abs($office->total_continuing->realignment), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->realignment, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_continuing->authorized_appropriation) || $office->total_continuing->authorized_appropriation == 0)
                                                -
                                            @elseif ($office->total_continuing->authorized_appropriation < 0)
                                                ({{ number_format(abs($office->total_continuing->authorized_appropriation), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->authorized_appropriation, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_continuing->allotment) || $office->total_continuing->allotment == 0)
                                                -
                                            @elseif ($office->total_continuing->allotment < 0)
                                                ({{ number_format(abs($office->total_continuing->allotment), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->allotment, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_continuing->obligation) || $office->total_continuing->obligation == 0)
                                                -
                                            @elseif ($office->total_continuing->obligation < 0)
                                                ({{ number_format(abs($office->total_continuing->obligation), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->obligation, 2) }}
                                            @endif
                                        </td>

                                        <td>{{ number_format($office->total_continuing->percent_obligated_to_authorized, 2) }}%</td>

                                        <td>
                                            @if (is_null($office->total_continuing->authorized_appropriation_balance) || $office->total_continuing->authorized_appropriation_balance == 0)
                                                -
                                            @elseif ($office->total_continuing->authorized_appropriation_balance < 0)
                                                ({{ number_format(abs($office->total_continuing->authorized_appropriation_balance), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->authorized_appropriation_balance, 2) }}
                                            @endif
                                        </td>

                                        <td>
                                            @if (is_null($office->total_continuing->disbursement) || $office->total_continuing->disbursement == 0)
                                                -
                                            @elseif ($office->total_continuing->disbursement < 0)
                                                ({{ number_format(abs($office->total_continuing->disbursement), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->disbursement, 2) }}
                                            @endif
                                        </td>

                                        <td>{{ number_format($office->total_continuing->percent_disbursed_to_authorized, 2) }}%</td>

                                        <td>
                                            @if (is_null($office->total_continuing->obligation_balance) || $office->total_continuing->obligation_balance == 0)
                                                -
                                            @elseif ($office->total_continuing->obligation_balance < 0)
                                                ({{ number_format(abs($office->total_continuing->obligation_balance), 2) }})
                                            @else
                                                {{ number_format($office->total_continuing->obligation_balance, 2) }}
                                            @endif
                                        </td>

                                        <td>{{ number_format($office->total_continuing->percent_disbursed_to_obligated, 2) }}%</td>
                                    </tr>
                                @endif
                            @endforeach
                            {{-- --- FINAL TOTAL (Current + Continuing) --- --}}
                            <tr class="bg-gray-300 dark:bg-gray-600 font-extrabold text-right border-t border-gray-500">
                                <td class="px-2 py-2 text-right">Total Current and Continuing</td>
                                <td>
                                    @if (is_null($office->total_overall->approved_appropriation) || $office->total_overall->approved_appropriation == 0)
                                        -
                                    @elseif ($office->total_overall->approved_appropriation < 0)
                                        ({{ number_format(abs($office->total_overall->approved_appropriation), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->approved_appropriation, 2) }}
                                    @endif
                                </td>

                                <td>
                                    @if (is_null($office->total_overall->supplemental) || $office->total_overall->supplemental == 0)
                                        -
                                    @elseif ($office->total_overall->supplemental < 0)
                                        ({{ number_format(abs($office->total_overall->supplemental), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->supplemental, 2) }}
                                    @endif
                                </td>

                                <td>
                                    @if (is_null($office->total_overall->reversion) || $office->total_overall->reversion == 0)
                                        -
                                    @elseif ($office->total_overall->reversion < 0)
                                        ({{ number_format(abs($office->total_overall->reversion), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->reversion, 2) }}
                                    @endif
                                </td>

                                <td data-key="realignment">
                                    @if (is_null($office->total_overall->realignment) || $office->total_overall->realignment == 0)
                                        -
                                    @elseif ($office->total_overall->realignment < 0)
                                        ({{ number_format(abs($office->total_overall->realignment), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->realignment, 2) }}
                                    @endif
                                </td>

                                <td>
                                    @if (is_null($office->total_overall->authorized_appropriation) || $office->total_overall->authorized_appropriation == 0)
                                        -
                                    @elseif ($office->total_overall->authorized_appropriation < 0)
                                        ({{ number_format(abs($office->total_overall->authorized_appropriation), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->authorized_appropriation, 2) }}
                                    @endif
                                </td>

                                <td>
                                    @if (is_null($office->total_overall->allotment) || $office->total_overall->allotment == 0)
                                        -
                                    @elseif ($office->total_overall->allotment < 0)
                                        ({{ number_format(abs($office->total_overall->allotment), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->allotment, 2) }}
                                    @endif
                                </td>

                                <td>
                                    @if (is_null($office->total_overall->obligation) || $office->total_overall->obligation == 0)
                                        -
                                    @elseif ($office->total_overall->obligation < 0)
                                        ({{ number_format(abs($office->total_overall->obligation), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->obligation, 2) }}
                                    @endif
                                </td>

                                <td>{{ number_format($office->total_overall->percent_obligated_to_authorized, 2) }}%</td>

                                <td>
                                    @if (is_null($office->total_overall->authorized_appropriation_balance) || $office->total_overall->authorized_appropriation_balance == 0)
                                        -
                                    @elseif ($office->total_overall->authorized_appropriation_balance < 0)
                                        ({{ number_format(abs($office->total_overall->authorized_appropriation_balance), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->authorized_appropriation_balance, 2) }}
                                    @endif
                                </td>

                                <td>
                                    @if (is_null($office->total_overall->disbursement) || $office->total_overall->disbursement == 0)
                                        -
                                    @elseif ($office->total_overall->disbursement < 0)
                                        ({{ number_format(abs($office->total_overall->disbursement), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->disbursement, 2) }}
                                    @endif
                                </td>

                                <td>{{ number_format($office->total_overall->percent_disbursed_to_authorized, 2) }}%</td>

                                <td>
                                    @if (is_null($office->total_overall->obligation_balance) || $office->total_overall->obligation_balance == 0)
                                        -
                                    @elseif ($office->total_overall->obligation_balance < 0)
                                        ({{ number_format(abs($office->total_overall->obligation_balance), 2) }})
                                    @else
                                        {{ number_format($office->total_overall->obligation_balance, 2) }}
                                    @endif
                                </td>

                                <td>{{ number_format($office->total_overall->percent_disbursed_to_obligated, 2) }}%</td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-800 dark:bg-gray-200 text-gray-200 dark:text-gray-700 font-bold border-t-2 border-b-2 text-[10px]">
                            <td class="px-2 py-2 text-right">Grand Total:</td>
                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->approved_appropriation) || $grandTotals->approved_appropriation == 0)
                                    -
                                @elseif ($grandTotals->approved_appropriation < 0)
                                    ({{ number_format(abs($grandTotals->approved_appropriation), 2) }})
                                @else
                                    {{ number_format($grandTotals->approved_appropriation, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->supplemental) || $grandTotals->supplemental == 0)
                                    -
                                @elseif ($grandTotals->supplemental < 0)
                                    ({{ number_format(abs($grandTotals->supplemental), 2) }})
                                @else
                                    {{ number_format($grandTotals->supplemental, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->reversion) || $grandTotals->reversion == 0)
                                    -
                                @elseif ($grandTotals->reversion < 0)
                                    ({{ number_format(abs($grandTotals->reversion), 2) }})
                                @else
                                    {{ number_format($grandTotals->reversion, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right" data-key="realignment">
                                @if (is_null($grandTotals->realignment) || $grandTotals->realignment == 0)
                                    -
                                @elseif ($grandTotals->realignment < 0)
                                    ({{ number_format(abs($grandTotals->realignment), 2) }})
                                @else
                                    {{ number_format($grandTotals->realignment, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->authorized_appropriation) || $grandTotals->authorized_appropriation == 0)
                                    -
                                @elseif ($grandTotals->authorized_appropriation < 0)
                                    ({{ number_format(abs($grandTotals->authorized_appropriation), 2) }})
                                @else
                                    {{ number_format($grandTotals->authorized_appropriation, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->allotment) || $grandTotals->allotment == 0)
                                    -
                                @elseif ($grandTotals->allotment < 0)
                                    ({{ number_format(abs($grandTotals->allotment), 2) }})
                                @else
                                    {{ number_format($grandTotals->allotment, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->obligation) || $grandTotals->obligation == 0)
                                    -
                                @elseif ($grandTotals->obligation < 0)
                                    ({{ number_format(abs($grandTotals->obligation), 2) }})
                                @else
                                    {{ number_format($grandTotals->obligation, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right">{{ number_format($grandTotals->percent_obligated_to_authorized, 2) }}%</td>
                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->authorized_appropriation_balance) || $grandTotals->authorized_appropriation_balance == 0)
                                    -
                                @elseif ($grandTotals->authorized_appropriation_balance < 0)
                                    ({{ number_format(abs($grandTotals->authorized_appropriation_balance), 2) }})
                                @else
                                    {{ number_format($grandTotals->authorized_appropriation_balance, 2) }}
                                @endif
                            </td>

                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->disbursement) || $grandTotals->disbursement == 0)
                                    -
                                @elseif ($grandTotals->disbursement < 0)
                                    ({{ number_format(abs($grandTotals->disbursement), 2) }})
                                @else
                                    {{ number_format($grandTotals->disbursement, 2) }}
                                @endif
                            </td>

                            <td class="px-2 py-2 text-right">{{ number_format($grandTotals->percent_disbursed_to_authorized, 2) }}%</td>

                            <td class="px-2 py-2 text-right">
                                @if (is_null($grandTotals->obligation_balance) || $grandTotals->obligation_balance == 0)
                                    -
                                @elseif ($grandTotals->obligation_balance < 0)
                                    ({{ number_format(abs($grandTotals->obligation_balance), 2) }})
                                @else
                                    {{ number_format($grandTotals->obligation_balance, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-2 text-right">{{ number_format($grandTotals->percent_disbursed_to_obligated, 2) }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    </div>

    <script>
        // Pass employees to JS
        window.employees = @json($employees);

        // Validation for the signatory fields
        function validateSignatories() {
            const prepared_signatory_name = document.getElementById('prepared_signatory_name').value.trim();
            const certified_signatory_name = document.getElementById('certified_signatory_name').value.trim();
            const certified_signatory_designation = document.getElementById('certified_signatory_designation').value.trim();
            const errorSpan = document.getElementById('signatory_error');

            let errorMessage = '';
            if (!prepared_signatory_name && !certified_signatory_name && !certified_signatory_designation) {
                errorMessage = 'Please select the signatories and their designations.';
            } else if (!prepared_signatory_name) {
                errorMessage = "Please select a 'Prepared by' signatory.";
            } else if (!certified_signatory_name) {
                errorMessage = "Please select a 'Certified correct' signatory.";
            } else if (!certified_signatory_designation) {
                errorMessage = "Please select a 'Certified correct' designation.";
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


        // Intercept PDF generation
        window.printSAAODBGFTable = function() {
            if (!validateSignatories()) return;
            runPrintSAAODBGFTable(); // call actual print function
        };

        function updatePreparedDesignation() {
            const select = document.getElementById('prepared_signatory_name');
            const hiddenDesignation = document.getElementById('prepared_signatory_designation');

            if (select && hiddenDesignation && select.value) {
                const selectedOption = select.options[select.selectedIndex];
                hiddenDesignation.value = selectedOption.getAttribute('data-designation') || '';
            }
        }

        // Run once on page load (for already-selected employee after reload)
        document.addEventListener('DOMContentLoaded', updatePreparedDesignation);

        // Run again on every change (before reload)
        document.getElementById('prepared_signatory_name').addEventListener('change', updatePreparedDesignation);

        function runPrintSAAODBGFTable() {
            const table = document.getElementById('saaodbGFTable').cloneNode(true);
            
            const hiddenKeys = [
                'realignment'
            ];

            // Remove only <td> and <th> elements matching these keys
            table.querySelectorAll('thead th[data-key], tbody td[data-key]').forEach(cell => {
                const key = cell.getAttribute('data-key');
                if (hiddenKeys.includes(key)) {
                    cell.remove();
                }
            });

            // Styling rows
            table.querySelectorAll('[id^="officeRow"]').forEach(tr => {
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
                tr.style.textAlign = 'left';
            });
            table.querySelectorAll('[id^="sectorRow"]').forEach(tr => {
                tr.style.fontWeight = 'bold';
                tr.style.fontStyle = 'italic';
                tr.style.fontSize = '10px';
                const firstCell = tr.querySelector('td');
                if (firstCell) firstCell.style.paddingLeft = '32px';
            });

            table.querySelectorAll('tbody').forEach(tbody => {
                tbody.style.fontSize = '10px';
            });

            table.querySelectorAll('tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                for (let i = 1; i < cells.length; i++) {
                    cells[i].style.textAlign = 'right';
                }
            });

            table.querySelectorAll('tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (cells.length > 2) {
                    const text = cells[0].textContent.trim().toUpperCase();
                    if (text.startsWith('SUBTOTAL') || text.startsWith('TOTAL') || text.startsWith('GRAND TOTAL')) {
                        tr.style.fontWeight = 'bold';
                        cells[0].style.textAlign = 'right';
                        cells[1].style.textAlign = 'right';
                        cells[2].style.textAlign = 'right';
                        cells.forEach(cell => {
                            cell.textContent = cell.textContent;
                        });
                    }
                }
            });

            table.querySelectorAll('thead th').forEach(th => {
                th.style.textAlign = 'center';
                th.style.fontWeight = 'bold';
                th.style.fontSize = '10px';
            });

            table.querySelectorAll('[id^="summaryRow"]').forEach(tr => {
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
                tr.style.textAlign = 'center';
            });

            // Format As of date
            const asOfInput = document.getElementById('as_of_filter');
            let asOfDate = '';
            if (asOfInput && asOfInput.value) {
                const dateObj = new Date(asOfInput.value);
                const monthNames = [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ];
                const month = monthNames[dateObj.getMonth()];
                const day = dateObj.getDate();
                const year = dateObj.getFullYear();
                asOfDate = `${month} ${day}, ${year}`;
            }

            // Get signatory name and designation

            // Prepared by
            const preparedSignatoryNameInput = document.getElementById('prepared_signatory_name');
            const preparedSignatoryDesignationInput = document.getElementById('prepared_signatory_designation');

            let preparedSignatoryName = preparedSignatoryNameInput && preparedSignatoryNameInput.value ?
                preparedSignatoryNameInput.value :
                '';

            let preparedSignatoryDesignation = preparedSignatoryDesignationInput && preparedSignatoryDesignationInput.value ?
                preparedSignatoryDesignationInput.value :
                '';

            // Certified Correct
            const certifiedSignatoryNameInput = document.getElementById('certified_signatory_name');
            const certifiedSignatoryDesignationInput = document.getElementById('certified_signatory_designation');

            let certifiedSignatoryName = certifiedSignatoryNameInput && certifiedSignatoryNameInput.value ?
                certifiedSignatoryNameInput.value :
                '';

            let certifiedSignatoryDesignation = certifiedSignatoryDesignationInput && certifiedSignatoryDesignationInput.value ?
                certifiedSignatoryDesignationInput.value :
                '';

            // Get screen dimensions
            const screenW = window.screen.availWidth;
            const screenH = window.screen.availHeight;

            const newWin = window.open('', '', `width=${screenW},height=${screenH},left=0,top=0,scrollbars=yes,resizable=yes`);
            newWin.document.write('<html><head><title>SAAODB</title>');
            newWin.document.write(`
            <style>
                body { font-family: sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ccc; padding: 4px; }

                th[data-key="office"] { width:150px; text-align:center; }
                th[data-key="appropriation"] { width:100px; text-align:center; }
                th[data-key="sb_appropriation"] { width:100px; text-align:center; }
                th[data-key="reversion"] { width:100px; text-align:center; }
                th[data-key="realignment"] { width:100px; text-align:center; }
                th[data-key="authorized_appropriation"] { width:100px; text-align:center; }
                th[data-key="allotment"] { width:100px; text-align:center; }
                th[data-key="obligation"] { width:100px; text-align:center; }
                th[data-key="appropriation_balance"] { width:100px; text-align:center; }
                th[data-key="appropriation_accomplishment"] { width:70px; text-align:center; }
                th[data-key="disbursement"] { width:100px; text-align:center; }
                th[data-key="disbursement_to_appropriation"] { width:70px; text-align:center; }
                th[data-key="obligation_balance"] { width:100px; text-align:center; }
                th[data-key="disbursement_to_obligation"] { width:70px; text-align:center; }
            </style>
        `);
            newWin.document.write('</head><body>');
            newWin.document.write(`
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:12px;">Republic of the Philippines</div>
                <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</div>
                <div style="font-size:12px; margin-bottom:15px">La Trinidad, Benguet</div>
                <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS, DISBURSEMENTS AND BALANCES</div>
                <div style="font-size:12px; font-weight:bold;">GENERAL FUND</div>
                <div style="font-size:12px;">Current and Continuing Appropriations</div>
                <div style="font-size:12px; font-weight:bold;">As of ${asOfDate}</div>
            </div>
        `);
            newWin.document.write(table.outerHTML);
            newWin.document.write(`
        <div style="margin-top: 30px; display: flex; justify-content: space-between; font-size: 12px;">
            
            <!-- Prepared by (left side) -->
            <div style="width: 30%; text-align: left; margin-left: 30%;">
                <strong>Prepared by:</strong>
                <br><br><br>
                <div style="text-align: center;">
                    <span style="font-weight: bold; text-decoration: underline;">
                        ${preparedSignatoryName ? preparedSignatoryName.toUpperCase() : '_____________________'}
                    </span><br>
                    <span>
                        ${preparedSignatoryDesignation ? preparedSignatoryDesignation : '_____________________'}
                    </span>
                </div>
            </div>

            <!-- Certified Correct (right side) -->
            <div style="width: 35%; text-align: left; margin-right: 5%;">
                <strong>Certified Correct:</strong>
                <br><br><br>
                <div style="text-align: center;">
                    <span style="font-weight: bold; text-decoration: underline;">
                        ${certifiedSignatoryName ? certifiedSignatoryName.toUpperCase() : '_____________________'}
                    </span><br>
                    <span>
                        ${certifiedSignatoryDesignation ? certifiedSignatoryDesignation : '_____________________'}
                    </span>
                </div>
            </div>

        </div>
    `);
            newWin.document.write('</body></html>');
            newWin.document.close();
            newWin.focus();
            newWin.print();

        }
    </script>

    </form>

    <!-- CSS Animations (minified) -->
    <style>
        @keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}@keyframes fadeIn{from{opacity:0}to{opacity:1}}@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}@keyframes tableRowFade{from{opacity:0}to{opacity:1}}@keyframes slideInToast{from{opacity:0;transform:translateX(400px)}to{opacity:1;transform:translateX(0)}}@keyframes slideOutToast{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(400px)}}form{animation:slideUp .3s ease-in-out}table tbody tr{animation:tableRowFade .5s ease-in-out}.toast{animation:slideInToast .3s ease-in-out}.toast.hide{animation:slideOutToast .3s ease-in-out forwards}.toast.show{opacity:1;transform:translateX(0);pointer-events-auto}.toast.hide{opacity:0;transform:translateX(400px);pointer-events-none}
    </style>

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

        async function exportSAAODBGFExcel() {
            console.log('Export button clicked');
            if (!validateSignatories()) {
                console.log('Validation failed');
                return;
            }
            
            showLoadingToast();
            
            const year1 = document.querySelector('select[name="year1"]')?.value || '';
            const as_of = document.querySelector('input[name="as_of_filter"]')?.value || '';
            const prepared_name = document.querySelector('select[name="prepared_signatory_name"]')?.value || '';
            const prepared_desig = document.querySelector('input[name="prepared_signatory_designation"]')?.value || '';
            const certified_name = document.querySelector('select[name="certified_signatory_name"]')?.value || '';
            const certified_desig = document.querySelector('select[name="certified_signatory_designation"]')?.value || '';
            
            console.log('Params:', {year1, as_of, prepared_name, prepared_desig, certified_name, certified_desig});
            
            const params = new URLSearchParams({
                year1: year1,
                as_of_filter: as_of,
                prepared_signatory_name: prepared_name,
                prepared_signatory_designation: prepared_desig,
                certified_signatory_name: certified_name,
                certified_signatory_designation: certified_desig
            });

            try {
                const url = `{{ route('saaodbGF.exportExcel') }}?${params.toString()}`;
                console.log('Fetching:', url);
                const response = await fetch(url);
                console.log('Response status:', response.status, response.ok);
                
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Response text:', text);
                    throw new Error(`Export failed: ${response.status}`);
                }
                
                const blob = await response.blob();
                console.log('Blob size:', blob.size);
                const url2 = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url2;
                
                const year = year1 || new Date().getFullYear();
                link.download = `SAAODB_GF_${year}.xlsx`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url2);

                closeLoadingToast();
                setTimeout(() => showSuccessToast('Excel file downloaded successfully!'), 500);
            } catch (error) {
                closeLoadingToast();
                console.error('Export error:', error);
                alert('Error exporting file: ' + error.message);
            }
        }
    </script>

</x-app-layout>
