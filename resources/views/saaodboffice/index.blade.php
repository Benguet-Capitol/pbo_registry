<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                @php
                $selectedOffice = null;
                if(request('office_filter')) {
                    $selectedOffice = $allOffices->firstWhere('id', request('office_filter'));
                }
                $selectedYear = request('year1', date('Y'));
                $selectedAccountCode = request('account_code');
                $selectedAccountDisplay = null;
                if($selectedAccountCode && isset($accounts[$selectedAccountCode])) {
                    $selectedAccountDisplay = $accounts[$selectedAccountCode];
                }
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                    {{ __('Statement of Appropriations, Allotments, Obligations, Disbursements and Balances') }}
                    |
                    <span class="text-blue-800 dark:text-blue-400">
                        @if($isSEFConsolidated)
                            Special Education Fund
                        @else
                            {{ $selectedOffice ? $selectedOffice->office_abbreviation : 'All Offices' }}
                        @endif
                        @if($selectedAccountDisplay)
                        ({{ $selectedAccountDisplay }})
                        @endif
                        (CY {{ $selectedYear }})
                    </span>
                </h3>
            </div>
        </div>
    </x-slot>

    <!-- Success Toast Notification -->
    <div id="success-toast" class="fixed top-6 right-6 z-50 transform transition-all duration-300 ease-in-out opacity-0 translate-x-96 pointer-events-none">
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
    <div id="loading-toast" class="fixed top-6 right-6 z-50 transform transition-all duration-300 ease-in-out opacity-0 translate-x-96 pointer-events-none">
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

            <!-- Office Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="office_filter"
                    id="office_filter"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                    onchange="this.form.submit()">
                    <option value="">All Office</option>
                    @foreach($allOffices as $office)
                    <option value="{{ $office->id }}" data-office-name="{{ $office->office_name }}" {{ request('office_filter') == $office->id ? 'selected' : '' }}>
                        {{ $office->office_abbreviation }}
                    </option>
                    @endforeach
                </x-form.select>
            </div>
            
            <!-- Account Code Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="account_code"
                    id="account_code"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 transition-all duration-200 ease-in-out focus:ring-2 focus:ring-blue-400 focus:border-transparent hover:border-blue-400 dark:hover:border-blue-500"
                    onchange="this.form.submit()">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $accountCode => $accountDisplay)
                    <option value="{{ $accountCode }}" {{ request('account_code') == $accountCode ? 'selected' : '' }}>
                        {{ $accountDisplay }}
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
                onclick="printSAAODBTable()"
                class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95"
                type="button">
                <i class="fas fa-print text-lg mr-2 -ml-1 w-4 h-4"></i>
                Print Report
            </button>

            <button
                type="button"
                onclick="exportSAAODBExcel()"
                class="text-green-700 inline-flex leading-4 tracking-wider border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-file-excel text-lg mr-2 -ml-1 w-4 h-4"></i>
                Generate Excel
            </button>
        </div>
        </div>

        <div class="p-4 bg-white rounded-md relative dark:bg-gray-800 transition-colors duration-300 ease-in-out">
            <div class="overflow-x-auto overflow-y-auto max-h-[700px] border border-gray-300 dark:border-gray-600 rounded-md">
                <table id="dashboardTable" class="w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-gray-100 dark:bg-gray-200 dark:text-gray-900">
                        <tr>
                            <th class="px-1 py-1 w-[150px] text-center" data-key="ppas">Functions / Programs / Projects / Activities</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="account_code">Account Code</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation">Approved Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="sb_appropriation">Supplemental Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="reversion">Reversions</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="realignment">Realignments</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="authorized_appropriation">Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="allotment">Allotments</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="obligation">Obligations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation_balance"> Balances from Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="appropriation_accomplishment">Percent of Obligations / Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="disbursement">Disbursements</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="disbursement_to_obligation">Percent of Disbursements / Obligations</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="disbursement_to_appropriation">Percent of Disbursements / Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="obligation_balance">Obligations - Disbursements</th>
                        </tr>
                    </thead>

                    <tbody class="border border-gray-300 dark:border-gray-600 text-[10px]">
                        @foreach($offices as $office)
                        <tr id="officeNameRow-{{ $office->id }}" class="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-center text-sm">
                            <td colspan="16" class="px-2 py-3">{{ $office->office_name }}</td>
                        </tr>

                        @php
                        $officeTotals = array_fill_keys(['appropriation','sb_appropriation','reversion','realignment','authorized_appropriation','allotment','obligation','appropriation_balance','appropriation_accomplishment', 'disbursement', 'disbursement_to_obligation', 'disbursement_to_appropriation', 'disbursement_balance'], 0);
                        @endphp

                        @foreach ($office->officeAllotmentClasses as $oac)
                        <tr id="allotmentClassRow-{{ $oac->id }}" class="bg-gray-600 text-gray-100 dark:bg-gray-200 dark:text-gray-800 font-bold border-t border-b text-xs uppercase">
                            <td colspan="16" class="px-4 py-2">{{ $oac->allotmentClass->description }}</td>
                        </tr>

                        @php
                        $classTotals = array_fill_keys(array_keys($officeTotals), 0);
                        $noProgramTotals = array_fill_keys(array_keys($officeTotals), 0);
                        $officeCOETotals = array_fill_keys(array_keys($officeTotals), 0);
                        $officeCOECoTotals = array_fill_keys(array_keys($officeTotals), 0);
                        $classAccomplishmentCounts = ['appropriation_accomplishment' => 0, 'disbursement_to_obligation' => 0, 'disbursement_to_appropriation' => 0];
                        @endphp

                        {{-- Appropriations WITHOUT a Program --}}
                        @php
                            // Check if there are any programs (non-empty keys)
                            $hasPrograms = collect($oac->groupedAppropriations)->keys()->filter(fn($key) => $key !== '')->isNotEmpty();
                        @endphp

                        @if (!empty($oac->groupedAppropriations['']))
                            @foreach ($oac->groupedAppropriations[''] as $appropriation)
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="px-2 py-2 text-left">{{ $appropriation->description }}
                                    @if(!empty($appropriation->cco_year))
                                    - {{ $appropriation->cco_year }}
                                    @endif</td>
                                <td class="px-2 py-2 text-center" data-key="account_code">{{ $appropriation->account_code }}</td>
                                <td class="px-2 py-2 text-right" data-key="appropriation">
                                    @if (is_null($appropriation->appropriation) || $appropriation->appropriation == 0)
                                        -
                                    @elseif ($appropriation->appropriation < 0)
                                        ({{ number_format(abs($appropriation->appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                    @if (is_null($appropriation->sb_appropriation) || $appropriation->sb_appropriation == 0)
                                        -
                                    @elseif ($appropriation->sb_appropriation < 0)
                                        ({{ number_format(abs($appropriation->sb_appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->sb_appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="reversion">
                                    @if (is_null($appropriation->reversion) || $appropriation->reversion == 0)
                                        -
                                    @elseif ($appropriation->reversion < 0)
                                        ({{ number_format(abs($appropriation->reversion), 2) }})
                                    @else
                                        {{ number_format($appropriation->reversion, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="realignment">
                                    @if (is_null($appropriation->realignment) || $appropriation->realignment == 0)
                                        -
                                    @elseif ($appropriation->realignment < 0)
                                        ({{ number_format(abs($appropriation->realignment), 2) }})
                                    @else
                                        {{ number_format($appropriation->realignment, 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-right" data-key="authorized_appropriation">
                                    @if (is_null($appropriation->authorized_appropriation) || $appropriation->authorized_appropriation == 0)
                                        -
                                    @elseif ($appropriation->authorized_appropriation < 0)
                                        ({{ number_format(abs($appropriation->authorized_appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->authorized_appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="allotment">
                                    @if (is_null($appropriation->allotment) || $appropriation->allotment == 0)
                                        -
                                    @elseif ($appropriation->allotment < 0)
                                        ({{ number_format(abs($appropriation->allotment), 2) }})
                                    @else
                                        {{ number_format($appropriation->allotment, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="obligation">
                                    @if (is_null($appropriation->obligation) || $appropriation->obligation == 0)
                                        -
                                    @elseif ($appropriation->obligation < 0)
                                        ({{ number_format(abs($appropriation->obligation), 2) }})
                                    @else
                                        {{ number_format($appropriation->obligation, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                    @if (is_null($appropriation->appropriation_balance) || $appropriation->appropriation_balance == 0)
                                        -
                                    @elseif ($appropriation->appropriation_balance < 0)
                                        ({{ number_format(abs($appropriation->appropriation_balance), 2) }})
                                    @else
                                        {{ number_format($appropriation->appropriation_balance, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                    {{ number_format($appropriation->appropriation_accomplishment, 2) }}%
                                </td>

                                <td class="px-2 py-2 text-right" data-key="disbursement">
                                    @if (is_null($appropriation->disbursement) || $appropriation->disbursement == 0)
                                        -
                                    @elseif ($appropriation->disbursement < 0)
                                        ({{ number_format(abs($appropriation->disbursement), 2) }})
                                    @else
                                        {{ number_format($appropriation->disbursement, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-center" data-key="disbursement_to_obligation">
                                    {{ number_format($appropriation->disbursement_to_obligation, 2) }}%
                                </td>

                                <td class="px-2 py-2 text-center" data-key="disbursement_to_appropriation">
                                    {{ number_format($appropriation->disbursement_to_appropriation, 2) }}%
                                </td>

                                <td class="px-2 py-2 text-right" data-key="obligation_balance">
                                    @if (is_null($appropriation->disbursement_balance) || $appropriation->disbursement_balance == 0)
                                        -
                                    @elseif ($appropriation->disbursement_balance < 0)
                                        ({{ number_format(abs($appropriation->disbursement_balance), 2) }})
                                    @else
                                        {{ number_format($appropriation->disbursement_balance, 2) }}
                                    @endif
                                </td>
                            </tr>

                            @php
                            foreach ($noProgramTotals as $key => $val) {
                                $noProgramTotals[$key] += $appropriation->$key;
                                $classTotals[$key] += $appropriation->$key;
                                $officeTotals[$key] += $appropriation->$key;
                            }

                            $classAccomplishmentCounts['appropriation_accomplishment']++;
                            $classAccomplishmentCounts['disbursement_to_obligation']++;
                            $classAccomplishmentCounts['disbursement_to_appropriation']++;
                            @endphp
                            @endforeach

                            {{-- Subtotal row - only shows if there are also programmed appropriations --}}
                            @if ($hasPrograms)
                            <tr class="bg-gray-500 text-gray-100 dark:bg-gray-200 dark:text-gray-800 font-semibold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 text-[10px]">
                                <td colspan="2" class="px-2 py-2 text-right">Subtotal:</td>
                                @foreach ($noProgramTotals as $key => $val)
                                <td class="px-2 py-2 
                                    @if(in_array($key, ['appropriation_accomplishment', 'disbursement_to_obligation', 'disbursement_to_appropriation'])) 
                                        text-center 
                                    @else 
                                        text-right 
                                    @endif" 
                                    data-key="{{ $key }}">
                                    
                                    @if ($key === 'appropriation_accomplishment')
                                        {{ $noProgramTotals['authorized_appropriation'] > 0 
                                            ? number_format(($noProgramTotals['obligation'] / $noProgramTotals['authorized_appropriation']) * 100, 2) 
                                            : '0.00' }}%
                                    @elseif ($key === 'disbursement_to_obligation')
                                        {{ $noProgramTotals['obligation'] > 0 
                                            ? number_format(($noProgramTotals['disbursement'] / $noProgramTotals['obligation']) * 100, 2) 
                                            : '0.00' }}%
                                    @elseif ($key === 'disbursement_to_appropriation')
                                        {{ $noProgramTotals['authorized_appropriation'] > 0 
                                            ? number_format(($noProgramTotals['disbursement'] / $noProgramTotals['authorized_appropriation']) * 100, 2) 
                                            : '0.00' }}%
                                    @else
                                        @if ($val == 0 || $val === null)
                                            -
                                        @elseif ($val < 0)
                                            ({{ number_format(abs($val), 2) }})
                                        @else
                                            {{ number_format($val, 2) }}
                                        @endif
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endif
                        @endif

                        {{-- Appropriations GROUPED BY Program --}}
                        @foreach ($oac->groupedAppropriations as $program => $appropriations)
                            @if ($program !== '')
                            @php
                            $programTotals = array_fill_keys(array_keys($officeTotals), 0);
                            $programAccomplishmentCounts = ['appropriation_accomplishment' => 0, 'disbursement_to_obligation' => 0, 'disbursement_to_appropriation' => 0];
                            @endphp

                            <tr id="programRow-{{ $loop->index }}-{{ $oac->id }}" class="bg-gray-200 text-gray-700 dark:bg-gray-300 dark:text-gray-700 font-semibold border-t border-b border-gray-400 dark:border-gray-100 italic">
                                <td colspan="16" class="px-6 py-2 text-xs">{{ $program }}</td>
                            </tr>

                            @foreach ($appropriations as $appropriation)
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="px-2 py-2 text-left">{{ $appropriation->description }}
                                    @if(!empty($appropriation->cco_year))
                                    - {{ $appropriation->cco_year }}
                                    @endif</td>
                                <td class="px-2 py-2 text-center" data-key="account_code">{{ $appropriation->account_code }}</td>
                                <td class="px-2 py-2 text-right" data-key="appropriation">
                                    @if (is_null($appropriation->appropriation) || $appropriation->appropriation == 0)
                                        -
                                    @elseif ($appropriation->appropriation < 0)
                                        ({{ number_format(abs($appropriation->appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="sb_appropriation">
                                    @if (is_null($appropriation->sb_appropriation) || $appropriation->sb_appropriation == 0)
                                        -
                                    @elseif ($appropriation->sb_appropriation < 0)
                                        ({{ number_format(abs($appropriation->sb_appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->sb_appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="reversion">
                                    @if (is_null($appropriation->reversion) || $appropriation->reversion == 0)
                                        -
                                    @elseif ($appropriation->reversion < 0)
                                        ({{ number_format(abs($appropriation->reversion), 2) }})
                                    @else
                                        {{ number_format($appropriation->reversion, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="realignment">
                                    @if (is_null($appropriation->realignment) || $appropriation->realignment == 0)
                                        -
                                    @elseif ($appropriation->realignment < 0)
                                        ({{ number_format(abs($appropriation->realignment), 2) }})
                                    @else
                                        {{ number_format($appropriation->realignment, 2) }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-right" data-key="authorized_appropriation">
                                    @if (is_null($appropriation->authorized_appropriation) || $appropriation->authorized_appropriation == 0)
                                        -
                                    @elseif ($appropriation->authorized_appropriation < 0)
                                        ({{ number_format(abs($appropriation->authorized_appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->authorized_appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="allotment">
                                    @if (is_null($appropriation->allotment) || $appropriation->allotment == 0)
                                        -
                                    @elseif ($appropriation->allotment < 0)
                                        ({{ number_format(abs($appropriation->allotment), 2) }})
                                    @else
                                        {{ number_format($appropriation->allotment, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="obligation">
                                    @if (is_null($appropriation->obligation) || $appropriation->obligation == 0)
                                        -
                                    @elseif ($appropriation->obligation < 0)
                                        ({{ number_format(abs($appropriation->obligation), 2) }})
                                    @else
                                        {{ number_format($appropriation->obligation, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-right" data-key="appropriation_balance">
                                    @if (is_null($appropriation->appropriation_balance) || $appropriation->appropriation_balance == 0)
                                        -
                                    @elseif ($appropriation->appropriation_balance < 0)
                                        ({{ number_format(abs($appropriation->appropriation_balance), 2) }})
                                    @else
                                        {{ number_format($appropriation->appropriation_balance, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-center" data-key="appropriation_accomplishment">
                                    {{ number_format($appropriation->appropriation_accomplishment, 2) }}%
                                </td>

                                <td class="px-2 py-2 text-right" data-key="disbursement">
                                    @if (is_null($appropriation->disbursement) || $appropriation->disbursement == 0)
                                        -
                                    @elseif ($appropriation->disbursement < 0)
                                        ({{ number_format(abs($appropriation->disbursement), 2) }})
                                    @else
                                        {{ number_format($appropriation->disbursement, 2) }}
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-center" data-key="disbursement_to_obligation">
                                    {{ number_format($appropriation->disbursement_to_obligation, 2) }}%
                                </td>

                                <td class="px-2 py-2 text-center" data-key="disbursement_to_appropriation">
                                    {{ number_format($appropriation->disbursement_to_appropriation, 2) }}%
                                </td>

                                <td class="px-2 py-2 text-right" data-key="obligation_balance">
                                    @if (is_null($appropriation->disbursement_balance) || $appropriation->disbursement_balance == 0)
                                        -
                                    @elseif ($appropriation->disbursement_balance < 0)
                                        ({{ number_format(abs($appropriation->disbursement_balance), 2) }})
                                    @else
                                        {{ number_format($appropriation->disbursement_balance, 2) }}
                                    @endif
                                </td>
                            </tr>

                            @php
                            foreach ($programTotals as $key => $val) {
                                $programTotals[$key] += $appropriation->$key;
                                $classTotals[$key] += $appropriation->$key;
                                $officeTotals[$key] += $appropriation->$key;
                            }

                            $programAccomplishmentCounts['appropriation_accomplishment']++;
                            $programAccomplishmentCounts['disbursement_to_obligation']++;
                            $programAccomplishmentCounts['disbursement_to_appropriation']++;
                            @endphp
                            @endforeach

                            <tr class="bg-gray-500 text-white dark:bg-gray-200 dark:text-gray-800 font-semibold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 text-[10px]">
                                <td colspan="2" class="px-2 py-2 text-right italic">Subtotal: </td>
                                @foreach ($programTotals as $key => $val)
                                <td class="px-2 py-2 
                                    @if(in_array($key, ['appropriation_accomplishment', 'disbursement_to_obligation', 'disbursement_to_appropriation'])) 
                                        text-center 
                                    @else 
                                        text-right 
                                    @endif" 
                                    data-key="{{ $key }}">
                                    
                                    @if ($key === 'appropriation_accomplishment')
                                        {{ $programTotals['authorized_appropriation'] > 0 
                                            ? number_format(($programTotals['obligation'] / $programTotals['authorized_appropriation']) * 100, 2) 
                                            : '0.00' }}%
                                    @elseif ($key === 'disbursement_to_obligation')
                                        {{ $programTotals['obligation'] > 0 
                                            ? number_format(($programTotals['disbursement'] / $programTotals['obligation']) * 100, 2) 
                                            : '0.00' }}%
                                    @elseif ($key === 'disbursement_to_appropriation')
                                        {{ $programTotals['authorized_appropriation'] > 0 
                                            ? number_format(($programTotals['disbursement'] / $programTotals['authorized_appropriation']) * 100, 2) 
                                            : '0.00' }}%
                                    @else
                                        @if ($val == 0 || $val === null)
                                            -
                                        @elseif ($val < 0)
                                            ({{ number_format(abs($val), 2) }})
                                        @else
                                            {{ number_format($val, 2) }}
                                        @endif
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endif
                        @endforeach

                        <tr class="bg-gray-200 text-gray-700 dark:bg-gray-900 dark:text-white font-bold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 text-[10px]">
                            <td colspan="2" class="px-2 py-2 text-right">Total {{ $oac->allotmentClass->description }} ({{ $oac->allotmentClass->class }}): </td>
                            @foreach ($classTotals as $key => $val)
                            <td class="px-2 py-2 
                                @if(in_array($key, ['appropriation_accomplishment', 'disbursement_to_obligation', 'disbursement_to_appropriation'])) 
                                    text-center 
                                @else 
                                    text-right 
                                @endif" 
                                data-key="{{ $key }}">
                                
                                @if ($key === 'appropriation_accomplishment')
                                    {{ $classTotals['authorized_appropriation'] > 0 
                                        ? number_format(($classTotals['obligation'] / $classTotals['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_obligation')
                                    {{ $classTotals['obligation'] > 0 
                                        ? number_format(($classTotals['disbursement'] / $classTotals['obligation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_appropriation')
                                    {{ $classTotals['authorized_appropriation'] > 0 
                                        ? number_format(($classTotals['disbursement'] / $classTotals['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @else
                                    @if ($val == 0 || $val === null)
                                        -
                                    @elseif ($val < 0)
                                        ({{ number_format(abs($val), 2) }})
                                    @else
                                        {{ number_format($val, 2) }}
                                    @endif
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @php
                        $coeClasses = [
                        'PS', 'PERSONNEL SERVICES',
                        'MOOE', 'MAINTENANCE AND OTHER OPERATING EXPENDITURES',
                        'FE', 'FINANCIAL EXPENSES'
                        ];
                        $coClasses = ['CO', 'CAPITAL OUTLAY'];

                        $lastCOEOac = $office->officeAllotmentClasses
                        ->filter(fn($item) => in_array(strtoupper($item->allotmentClass->description), $coeClasses))
                        ->last();

                        $lastCOOac = $office->officeAllotmentClasses
                        ->filter(fn($item) => in_array(strtoupper($item->allotmentClass->description), $coClasses))
                        ->last();

                        $isLastCOE = optional($lastCOEOac)->id === $oac->id;
                        $isLastCO = optional($lastCOOac)->id === $oac->id;
                        @endphp
                        @if ($isLastCOE && !($isSEFConsolidated ?? false))
                        <tr class="bg-blue-200 dark:bg-blue-800 font-bold border-t-2 border-b-2 text-[10px]">
                            <td colspan="2" class="px-2 py-2 text-right">Total Current Operating Expenditure (COE):</td>
                            @foreach ($office->officeCOETotals as $key => $val)
                            <td class="px-2 py-2 
                                @if(in_array($key, ['appropriation_accomplishment', 'disbursement_to_obligation', 'disbursement_to_appropriation'])) 
                                    text-center 
                                @else 
                                    text-right 
                                @endif" 
                                data-key="{{ $key }}">
                                
                                @if ($key === 'appropriation_accomplishment')
                                    {{ $office->officeCOETotals['authorized_appropriation'] > 0 
                                        ? number_format(($office->officeCOETotals['obligation'] / $office->officeCOETotals['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_obligation')
                                    {{ $office->officeCOETotals['obligation'] > 0 
                                        ? number_format(($office->officeCOETotals['disbursement'] / $office->officeCOETotals['obligation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_appropriation')
                                    {{ $office->officeCOETotals['authorized_appropriation'] > 0 
                                        ? number_format(($office->officeCOETotals['disbursement'] / $office->officeCOETotals['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @else
                                    @if ($val == 0 || $val === null)
                                        -
                                    @elseif ($val < 0)
                                        ({{ number_format(abs($val), 2) }})
                                    @else
                                        {{ number_format($val, 2) }}
                                    @endif
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endif

                        @if ($isLastCO && !($isSEFConsolidated ?? false))
                        <tr class="bg-green-200 dark:bg-green-800 font-bold border-t-2 border-b-2 text-[10px]">
                            <td colspan="2" class="px-2 py-2 text-right">Total COE and CO:</td>
                            @foreach ($office->officeCOECoTotals as $key => $val)
                           <td class="px-2 py-2 
                                @if(in_array($key, ['appropriation_accomplishment', 'disbursement_to_obligation', 'disbursement_to_appropriation'])) 
                                    text-center 
                                @else 
                                    text-right 
                                @endif" 
                                data-key="{{ $key }}">
                                
                                @if ($key === 'appropriation_accomplishment')
                                    {{ $office->officeCOECoTotals['authorized_appropriation'] > 0 
                                        ? number_format(($office->officeCOECoTotals['obligation'] / $office->officeCOECoTotals['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_obligation')
                                    {{ $office->officeCOECoTotals['obligation'] > 0 
                                        ? number_format(($office->officeCOECoTotals['disbursement'] / $office->officeCOECoTotals['obligation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_appropriation')
                                    {{ $office->officeCOECoTotals['authorized_appropriation'] > 0 
                                        ? number_format(($office->officeCOECoTotals['disbursement'] / $office->officeCOECoTotals['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @else
                                    @if ($val == 0 || $val === null)
                                        -
                                    @elseif ($val < 0)
                                        ({{ number_format(abs($val), 2) }})
                                    @else
                                        {{ number_format($val, 2) }}
                                    @endif
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endif

                        @endforeach

                        <tr class="bg-gray-800 dark:bg-gray-200 text-gray-200 dark:text-gray-700 font-bold border-t-2 border-b-2 text-[10px]">
                            <td colspan="2" class="px-2 py-2 text-right">GRAND TOTAL: </td>
                            @foreach ($officeTotals as $key => $val)
                            <td class="px-2 py-2 
                                @if(in_array($key, ['appropriation_accomplishment', 'disbursement_to_obligation', 'disbursement_to_appropriation'])) 
                                    text-center 
                                @else 
                                    text-right 
                                @endif" 
                                data-key="{{ $key }}">
                                
                                @if ($key === 'appropriation_accomplishment')
                                    {{ $officeTotals['authorized_appropriation'] > 0 
                                        ? number_format(($officeTotals['obligation'] / $officeTotals['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_obligation')
                                    {{ $officeTotals['obligation'] > 0 
                                        ? number_format(($officeTotals['disbursement'] / $officeTotals['obligation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_appropriation')
                                    {{ $officeTotals['authorized_appropriation'] > 0 
                                        ? number_format(($officeTotals['disbursement'] / $officeTotals['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @else
                                    @if ($val == 0 || $val === null)
                                        -
                                    @elseif ($val < 0)
                                        ({{ number_format(abs($val), 2) }})
                                    @else
                                        {{ number_format($val, 2) }}
                                    @endif
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        {{-- Overall Total Row (only if all offices are selected or SEF consolidated) --}}
                        @if((empty($selectedOffice) || ($isSEFConsolidated ?? false)) && $overallTotal)
                        <tr class="bg-blue-900 dark:bg-blue-800 text-white dark:text-gray-100 font-bold border-t-4 border-b-2 text-[11px]">
                            <td colspan="2" class="px-2 py-3 text-right">OVERALL TOTAL: </td>
                            @foreach ($overallTotal as $key => $val)
                            @if(!in_array($key, ['count']))
                            <td class="px-2 py-3 
                                @if($key === 'appropriation_accomplishment' || $key === 'disbursement_to_obligation' || $key === 'disbursement_to_appropriation') 
                                    text-center 
                                @else 
                                    text-right 
                                @endif" 
                                data-key="{{ $key }}">
                                
                                @if ($key === 'appropriation_accomplishment')
                                    {{ $overallTotal['authorized_appropriation'] > 0 
                                        ? number_format(($overallTotal['obligation'] / $overallTotal['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_obligation')
                                    {{ $overallTotal['obligation'] > 0 
                                        ? number_format(($overallTotal['disbursement'] / $overallTotal['obligation']) * 100, 2) 
                                        : '0.00' }}%
                                @elseif ($key === 'disbursement_to_appropriation')
                                    {{ $overallTotal['authorized_appropriation'] > 0 
                                        ? number_format(($overallTotal['disbursement'] / $overallTotal['authorized_appropriation']) * 100, 2) 
                                        : '0.00' }}%
                                @else
                                    @if ($val == 0 || $val === null)
                                        -
                                    @elseif ($val < 0)
                                        ({{ number_format(abs($val), 2) }})
                                    @else
                                        {{ number_format($val, 2) }}
                                    @endif
                                @endif
                            </td>
                            @endif
                            @endforeach
                        </tr>
                        @endif
                    </tbody>
                </table>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</form>

<style>
    /* Smooth transitions for filter inputs */
    .filter-select {
        transition: all 0.2s ease-in-out;
    }

    .filter-select:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 0 0 2px rgba(59, 130, 246, 0.5);
    }

    /* Table row hover animations */
    tbody tr {
        transition: all 0.2s ease-in-out;
    }

    /* Header sticky effect with smooth animation */
    thead {
        animation: slideDown 0.3s ease-in-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Error message pulse */
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    /* Form container entrance animation */
    form {
        animation: slideUp 0.3s ease-in-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Table container entrance animation */
    .bg-white.overflow-hidden.shadow-md {
        animation: slideUp 0.4s ease-in-out 0.1s both;
    }

    /* Enhanced scrollbar styling for table */
    .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
        transition: background 0.2s ease-in-out;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Dark mode scrollbar */
    .dark .overflow-y-auto::-webkit-scrollbar-track {
        background: #1f2937;
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #4b5563;
    }

    .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #6b7280;
    }

    /* Smooth staggered table rendering */
    tbody tr {
        opacity: 1;
        animation: tableRowFade 0.3s ease-in-out;
    }

    @keyframes tableRowFade {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Focus indicator enhancement */
    .filter-select:focus-visible {
        outline: 2px solid transparent;
        outline-offset: 2px;
    }

    /* Toast notification animations */
    @keyframes slideInToast {
        from {
            opacity: 0;
            transform: translateX(400px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutToast {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(400px);
        }
    }

    #success-toast.show {
        animation: slideInToast 0.3s ease-out forwards;
        pointer-events: auto;
    }

    #success-toast.hide {
        animation: slideOutToast 0.3s ease-in forwards;
        pointer-events: none;
    }

    #loading-toast.show {
        animation: slideInToast 0.3s ease-out forwards;
        pointer-events: auto;
    }

    #loading-toast.hide {
        animation: slideOutToast 0.3s ease-in forwards;
        pointer-events: none;
    }
</style>

<script>
    // Initialize designation fields on page load
    function initializeDesignations() {
        const preparedNameSelect = document.getElementById('prepared_signatory_name');
        const preparedDesignationInput = document.getElementById('prepared_signatory_designation');
        
        // Set prepared signatory designation on page load
        if (preparedNameSelect && preparedNameSelect.value) {
            const selectedOption = preparedNameSelect.options[preparedNameSelect.selectedIndex];
            const designation = selectedOption.getAttribute('data-designation');
            if (preparedDesignationInput) {
                preparedDesignationInput.value = designation || '';
            }
        }
    }

    // Auto-populate prepared signatory designation when name is selected
    document.getElementById('prepared_signatory_name').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const designation = selectedOption.getAttribute('data-designation');
        document.getElementById('prepared_signatory_designation').value = designation || '';
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeDesignations();
    });

    // Toast notification functions
    function showSuccessToast() {
        const toast = document.getElementById('success-toast');
        toast.classList.remove('hide');
        toast.classList.add('show');
        
        setTimeout(() => {
            closeSuccessToast();
        }, 4000);
    }

    function closeSuccessToast() {
        const toast = document.getElementById('success-toast');
        toast.classList.remove('show');
        toast.classList.add('hide');
    }

    function showLoadingToast() {
        const toast = document.getElementById('loading-toast');
        toast.classList.remove('hide', 'pointer-events-none');
        toast.classList.add('show');
    }

    function closeLoadingToast() {
        const toast = document.getElementById('loading-toast');
        toast.classList.remove('show');
        toast.classList.add('hide', 'pointer-events-none');
    }

    // Excel export function using Fetch API
    async function exportSAAODBExcel() {
        if (!validateSignatories()) return;

        showLoadingToast();

        try {
            const params = new URLSearchParams({
                year1: document.getElementById('year1').value,
                office_filter: document.getElementById('office_filter').value,
                account_code: document.getElementById('account_code').value,
                as_of_filter: document.getElementById('as_of_filter').value,
                prepared_signatory_name: document.getElementById('prepared_signatory_name').value,
                prepared_signatory_designation: document.getElementById('prepared_signatory_designation').value,
                certified_signatory_name: document.getElementById('certified_signatory_name').value,
                certified_signatory_designation: document.getElementById('certified_signatory_designation').value
            });

            const response = await fetch(`{{ route('saaodb.exportExcel') }}?${params}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Export failed');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            
            // Build filename with office abbreviation and account code
            const officeSelect = document.getElementById('office_filter');
            const officeValue = officeSelect.value;
            const selectedOfficeOption = officeSelect.options[officeSelect.selectedIndex];
            const officeAbbreviation = officeValue ? selectedOfficeOption.text : '';
            
            const accountCodeSelect = document.getElementById('account_code');
            const accountCodeValue = accountCodeSelect.value;
            
            const year = document.getElementById('year1').value;
            
            // Build filename with available filters
            let filename = 'SAAODB';
            if (officeAbbreviation) filename += `_${officeAbbreviation}`;
            if (accountCodeValue) filename += `_${accountCodeValue}`;
            filename += `_${year}.xlsx`;
            
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            closeLoadingToast();
            
            setTimeout(() => {
                showSuccessToast();
            }, 500);
        } catch (error) {
            closeLoadingToast();
            console.error('Export error:', error);
            alert('Failed to generate Excel file. Please try again.');
        }
    }

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
        window.printSAAODBTable = function() {
            if (!validateSignatories()) return;
            runPrintSAAODBTable(); // call actual print function
        };

        // Intercept Excel Export Submit
        document.querySelector(`form[action="{{ route('saaodb.exportExcel') }}"]`)
            .addEventListener('submit', function(e) {
                if (!validateSignatories()) {
                    e.preventDefault();
                }
            });

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

        function runPrintSAAODBTable() {
            const table = document.getElementById('dashboardTable').cloneNode(true);

            // --- Insert a spacer row directly after the THEAD header row ---
            (function insertHeaderSpacer(clonedTable) {
                const thead = clonedTable.querySelector('thead');
                if (!thead) return;
                const headerRow = thead.querySelector('tr');
                if (!headerRow) return;

                // count header columns (use th count so colspan matches)
                const thCount = headerRow.querySelectorAll('th').length || 1;

                // create spacer row
                const spacerRow = document.createElement('tr');
                spacerRow.className = 'header-spacer';

                const spacerTd = document.createElement('td');
                spacerTd.setAttribute('colspan', thCount);
                spacerTd.style.border = 'none';
                spacerTd.style.height = '12px'; // adjust gap height here
                spacerTd.style.background = 'transparent';

                spacerRow.appendChild(spacerTd);

                // insert spacer row after headerRow
                headerRow.parentNode.insertBefore(spacerRow, headerRow.nextSibling);
            })(table);

            // Style office rows
            table.querySelectorAll('[id^="officeNameRow-"]').forEach(tr => {
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
            });
            // Style allotment class rows
            table.querySelectorAll('[id^="allotmentClassRow-"]').forEach(tr => {
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
                tr.style.paddingLeft = '12px'; // Indent the row
                // Also indent the first cell if needed
                const firstCell = tr.querySelector('td');
                if (firstCell) {
                    firstCell.style.paddingLeft = '12px';
                }
            });
            // Style program rows
            table.querySelectorAll('[id^="programRow-"]').forEach(tr => {
                tr.style.fontWeight = 'bold';
                tr.style.fontStyle = 'italic';
                tr.style.fontSize = '10px';
                tr.style.paddingLeft = '32px'; // Indent the row
                // Also indent the first cell if needed
                const firstCell = tr.querySelector('td');
                if (firstCell) {
                    firstCell.style.paddingLeft = '32px';
                }
            });
            // Set tbody font size to 10px
            Array.from(table.querySelectorAll('tbody')).forEach(tbody => {
                tbody.style.fontSize = '10px';
            });
            // Set columns 4-11 and 13 text-align right in tbody (adjusted for removed columns)
            table.querySelectorAll('tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                // After removing 6 columns, indices shift, so just align all except first 3 left columns
                for (let i = 2; i < cells.length; i++) {
                    cells[i].style.textAlign = 'right';
                }
            });
            // Make subtotal, total, and grand total rows bold and 1st/2nd/3rd column right-aligned, values uppercase
            table.querySelectorAll('tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (cells.length > 2) {
                    const text = cells[0].textContent.trim().toUpperCase();
                    if (
                        text.startsWith('SUBTOTAL') ||
                        text.startsWith('TOTAL') ||
                        text.startsWith('GRAND TOTAL')
                    ) {
                        tr.style.fontWeight = 'bold';
                        cells[0].style.textAlign = 'right';
                        cells[1].style.textAlign = 'right';
                        cells[2].style.textAlign = 'right';
                    }
                }
            });

            // Center align thead text
            Array.from(table.querySelectorAll('thead th')).forEach(th => {
                th.style.textAlign = 'center';
                th.style.fontWeight = 'bold';
                th.style.fontSize = '10px';
            });

            // Get selected office name (full name)
            const officeSelect = document.getElementById('office_filter');
            let officeText = 'All Offices';
            if (officeSelect && officeSelect.selectedIndex > 0) {
                const selectedOption = officeSelect.options[officeSelect.selectedIndex];
                officeText = (selectedOption.getAttribute('data-office-name') || selectedOption.text);
            }
            // Get as of date and format it
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

                    th[data-key="ppas"] { width:200px; text-align:center; }
                    th[data-key="account_code"] { width:100px; text-align:center; }
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
                    th[data-key="disbursement_to_obligation"] { width:70px; text-align:center; }
                    th[data-key="disbursement_to_appropriation"] { width:70px; text-align:center; }
                    th[data-key="obligation_balance"] { width:100px; text-align:center; }
                </style>
            `);
            newWin.document.write('</head><body>');
            newWin.document.write(`
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:12px;">Republic of the Philippines</div>
            <div style="font-size:11px; font-weight:bold; text-transform:uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</div>
            <div style="font-size:12px; margin-bottom:15px">La Trinidad, Benguet</div>
            <div style="font-size:12px; font-weight:bold;">
                Statement of Appropriations, Allotments, Obligations, Disbursements and Balances
            </div>
            <div style="font-size:12px;">${officeText}</div>
            <div style="font-size:12px;">As of ${asOfDate}</div>
        </div>
    `);
            newWin.document.write(table.outerHTML);
            newWin.document.write(`
        <div style="margin-top: 30px; display: flex; justify-content: space-between; font-size: 12px;">
            
            <!-- Prepared by (left side) -->
            <div style="width: 45%; text-align: left; margin-left: 5%;">
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
            <div style="width: 45%; text-align: left; margin-right: 5%;">
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

</x-app-layout>
