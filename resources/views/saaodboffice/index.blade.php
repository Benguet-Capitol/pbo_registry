<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">

            <div>
                @php
                $selectedOffice = null;
                if(request('office_filter')) {
                $selectedOffice = $offices->firstWhere('id', request('office_filter'));
                }
                $selectedYear = request('year1', date('Y'));
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                    {{ __('Statement of Appropriations, Allotments, Obligations, Disbursements and Balances') }}
                    |
                    <span class="text-blue-800 dark:text-blue-400">
                        {{ $selectedOffice ? $selectedOffice->office_abbreviation : 'All Offices' }}
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

        <!-- First row: Year, Office, As of -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 items-center mb-3">
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
                    id="office_filter"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    <option value="">All Office</option>
                    @foreach($allOffices as $office)
                    <option value="{{ $office->id }}" data-office-name="{{ $office->office_name }}" {{ request('office_filter') == $office->id ? 'selected' : '' }}>
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
        </div>

        <!-- Second row: Signatories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
            <!-- Prepared By -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-2">
                    Prepared By
                </label>
                <x-form.select
                    name="prepared_signatory_name"
                    id="prepared_signatory_name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                <input type="hidden" id="prepared_signatory_designation" name="prepared_signatory_designation">
            </div>

            <!-- Certified Correct: Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-2">
                    Certified Correct
                </label>
                <x-form.select
                    name="certified_signatory_name"
                    id="certified_signatory_name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    <option value="">Select Signatory</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->name }}"
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
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900"
                type="button">
                Print Report
            </button>
    </form>

    <form method="GET" action="{{ route('saaodb.exportExcel') }}" style="display:inline;">
        <input type="hidden" name="year1" value="{{ request('year1') }}">
        <input type="hidden" name="office_filter" value="{{ request('office_filter') }}">
        <input type="hidden" name="as_of_filter" value="{{ request('as_of_filter') }}">
        <input type="hidden" name="prepared_signatory_name" value="{{ request('prepared_signatory_name') }}">
        <input type="hidden" name="prepared_signatory_designation" value="{{ request('prepared_signatory_designation') }}">
        <input type="hidden" name="certified_signatory_name" value="{{ request('certified_signatory_name') }}">
        <input type="hidden" name="certified_signatory_designation" value="{{ request('certified_signatory_designation') }}">

        <button type="submit" class="text-green-700 border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
            Generate Excel
        </button>
    </form>
    </div>


    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mt-6 mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
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
                                <td class="px-1 py-2 text-left">{{ $appropriation->description }}
                                    @if(!empty($appropriation->cco_year))
                                    - {{ $appropriation->cco_year }}
                                    @endif</td>
                                <td class="px-1 py-2 text-center" data-key="account_code">{{ $appropriation->account_code }}</td>
                                <td class="px-1 py-2 text-right" data-key="appropriation">
                                    @if (is_null($appropriation->appropriation) || $appropriation->appropriation == 0)
                                        -
                                    @elseif ($appropriation->appropriation < 0)
                                        ({{ number_format(abs($appropriation->appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                    @if (is_null($appropriation->sb_appropriation) || $appropriation->sb_appropriation == 0)
                                        -
                                    @elseif ($appropriation->sb_appropriation < 0)
                                        ({{ number_format(abs($appropriation->sb_appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->sb_appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="reversion">
                                    @if (is_null($appropriation->reversion) || $appropriation->reversion == 0)
                                        -
                                    @elseif ($appropriation->reversion < 0)
                                        ({{ number_format(abs($appropriation->reversion), 2) }})
                                    @else
                                        {{ number_format($appropriation->reversion, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="realignment">
                                    @if (is_null($appropriation->realignment) || $appropriation->realignment == 0)
                                        -
                                    @elseif ($appropriation->realignment < 0)
                                        ({{ number_format(abs($appropriation->realignment), 2) }})
                                    @else
                                        {{ number_format($appropriation->realignment, 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right" data-key="authorized_appropriation">
                                    @if (is_null($appropriation->authorized_appropriation) || $appropriation->authorized_appropriation == 0)
                                        -
                                    @elseif ($appropriation->authorized_appropriation < 0)
                                        ({{ number_format(abs($appropriation->authorized_appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->authorized_appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="allotment">
                                    @if (is_null($appropriation->allotment) || $appropriation->allotment == 0)
                                        -
                                    @elseif ($appropriation->allotment < 0)
                                        ({{ number_format(abs($appropriation->allotment), 2) }})
                                    @else
                                        {{ number_format($appropriation->allotment, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="obligation">
                                    @if (is_null($appropriation->obligation) || $appropriation->obligation == 0)
                                        -
                                    @elseif ($appropriation->obligation < 0)
                                        ({{ number_format(abs($appropriation->obligation), 2) }})
                                    @else
                                        {{ number_format($appropriation->obligation, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                    @if (is_null($appropriation->appropriation_balance) || $appropriation->appropriation_balance == 0)
                                        -
                                    @elseif ($appropriation->appropriation_balance < 0)
                                        ({{ number_format(abs($appropriation->appropriation_balance), 2) }})
                                    @else
                                        {{ number_format($appropriation->appropriation_balance, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                    {{ number_format($appropriation->appropriation_accomplishment, 2) }}%
                                </td>

                                <td class="px-1 py-2 text-right" data-key="disbursement">
                                    @if (is_null($appropriation->disbursement) || $appropriation->disbursement == 0)
                                        -
                                    @elseif ($appropriation->disbursement < 0)
                                        ({{ number_format(abs($appropriation->disbursement), 2) }})
                                    @else
                                        {{ number_format($appropriation->disbursement, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-center" data-key="disbursement_to_obligation">
                                    {{ number_format($appropriation->disbursement_to_obligation, 2) }}%
                                </td>

                                <td class="px-1 py-2 text-center" data-key="disbursement_to_appropriation">
                                    {{ number_format($appropriation->disbursement_to_appropriation, 2) }}%
                                </td>

                                <td class="px-1 py-2 text-right" data-key="obligation_balance">
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
                                <td class="px-1 py-2 text-left">{{ $appropriation->description }}
                                    @if(!empty($appropriation->cco_year))
                                    - {{ $appropriation->cco_year }}
                                    @endif</td>
                                <td class="px-1 py-2 text-center" data-key="account_code">{{ $appropriation->account_code }}</td>
                                <td class="px-1 py-2 text-right" data-key="appropriation">
                                    @if (is_null($appropriation->appropriation) || $appropriation->appropriation == 0)
                                        -
                                    @elseif ($appropriation->appropriation < 0)
                                        ({{ number_format(abs($appropriation->appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                    @if (is_null($appropriation->sb_appropriation) || $appropriation->sb_appropriation == 0)
                                        -
                                    @elseif ($appropriation->sb_appropriation < 0)
                                        ({{ number_format(abs($appropriation->sb_appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->sb_appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="reversion">
                                    @if (is_null($appropriation->reversion) || $appropriation->reversion == 0)
                                        -
                                    @elseif ($appropriation->reversion < 0)
                                        ({{ number_format(abs($appropriation->reversion), 2) }})
                                    @else
                                        {{ number_format($appropriation->reversion, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="realignment">
                                    @if (is_null($appropriation->realignment) || $appropriation->realignment == 0)
                                        -
                                    @elseif ($appropriation->realignment < 0)
                                        ({{ number_format(abs($appropriation->realignment), 2) }})
                                    @else
                                        {{ number_format($appropriation->realignment, 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right" data-key="authorized_appropriation">
                                    @if (is_null($appropriation->authorized_appropriation) || $appropriation->authorized_appropriation == 0)
                                        -
                                    @elseif ($appropriation->authorized_appropriation < 0)
                                        ({{ number_format(abs($appropriation->authorized_appropriation), 2) }})
                                    @else
                                        {{ number_format($appropriation->authorized_appropriation, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="allotment">
                                    @if (is_null($appropriation->allotment) || $appropriation->allotment == 0)
                                        -
                                    @elseif ($appropriation->allotment < 0)
                                        ({{ number_format(abs($appropriation->allotment), 2) }})
                                    @else
                                        {{ number_format($appropriation->allotment, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="obligation">
                                    @if (is_null($appropriation->obligation) || $appropriation->obligation == 0)
                                        -
                                    @elseif ($appropriation->obligation < 0)
                                        ({{ number_format(abs($appropriation->obligation), 2) }})
                                    @else
                                        {{ number_format($appropriation->obligation, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                    @if (is_null($appropriation->appropriation_balance) || $appropriation->appropriation_balance == 0)
                                        -
                                    @elseif ($appropriation->appropriation_balance < 0)
                                        ({{ number_format(abs($appropriation->appropriation_balance), 2) }})
                                    @else
                                        {{ number_format($appropriation->appropriation_balance, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                    {{ number_format($appropriation->appropriation_accomplishment, 2) }}%
                                </td>

                                <td class="px-1 py-2 text-right" data-key="disbursement">
                                    @if (is_null($appropriation->disbursement) || $appropriation->disbursement == 0)
                                        -
                                    @elseif ($appropriation->disbursement < 0)
                                        ({{ number_format(abs($appropriation->disbursement), 2) }})
                                    @else
                                        {{ number_format($appropriation->disbursement, 2) }}
                                    @endif
                                </td>

                                <td class="px-1 py-2 text-center" data-key="disbursement_to_obligation">
                                    {{ number_format($appropriation->disbursement_to_obligation, 2) }}%
                                </td>

                                <td class="px-1 py-2 text-center" data-key="disbursement_to_appropriation">
                                    {{ number_format($appropriation->disbursement_to_appropriation, 2) }}%
                                </td>

                                <td class="px-1 py-2 text-right" data-key="obligation_balance">
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
                                <td colspan="2" class="px-2 py-2 text-right italic">Subtotal ({{ $program }}): </td>
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
                        @if ($isLastCOE)
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

                        @if ($isLastCO)
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

                    </tbody>
                </table>
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