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
                    {{ __('Statement of Appropriations, Allotments, Obligations and Balances') }}
                    |
                    <span class="text-blue-800 dark:text-blue-400">
                        {{ $selectedOffice ? $selectedOffice->office_abbreviation : 'All Offices' }} - Current
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
            <!-- Account Code Filter -->
        <div class="flex items-center space-x-2">
            <x-form.select
                name="account_code"
                id="account_code"
                class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                onchange="this.form.submit()">
                <option value="">All Account Codes</option>
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
            <div class="flex items-center space-x-2">
                <button
                    onclick="printSAAOBTable()"
                    class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900"
                    type="button">
                    Print Report
                </button>
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
    </div>


    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mt-6 mb-6 dark:bg-gray-800">
        <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <table id="dashboardTable" class="w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-gray-100 dark:bg-gray-200 dark:text-gray-900">
                        <tr>
                            <th class="px-1 py-1 w-[150px] text-center">Functions / Programs / Projects / Activities</th>
                            <th class="px-1 py-1 w-[140px] text-center">Account Code</th>
                            <th class="px-1 py-1 w-[70px] text-center">FPP</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation">Approved Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="sb_appropriation">Supplemental Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="reversion">Reversions</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="realignment">Realignments</th>
                            <th class="px-1 py-1 w-[100px] text-center">Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center">Allotments</th>
                            <th class="px-1 py-1 w-[100px] text-center">For Later Release</th>
                            <th class="px-1 py-1 w-[100px] text-center">Obligations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation_balance">Balances from Authorized Appropriation</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="appropriation_accomplishment">Percent of Utilization</th>
                            <th class="px-1 py-1 w-[100px] text-center">Balances from Allotment</th>
                            <th class="px-1 py-1 w-[70px] text-center">Percent of Utilization</th>
                        </tr>
                    </thead>

                    <tbody class="border border-gray-300 dark:border-gray-600 text-[10px]">
                        @foreach($offices as $office)
                        <tr id="officeNameRow-{{ $office->id }}" class="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-center text-sm">
                            <td colspan="15" class="px-2 py-3">{{ $office->office_name }}</td>
                        </tr>

                        @php
                        $officeTotals = array_fill_keys(['appropriation','sb_appropriation','reversion','realignment','authorized_appropriation','allotment','for_later_release','obligation','appropriation_balance','appropriation_accomplishment','allotment_balance','allotment_accomplishment'], 0);
                        @endphp

                        @foreach ($office->officeAllotmentClasses as $oac)
                        <tr id="allotmentClassRow-{{ $oac->id }}" class="bg-gray-600 text-gray-100 dark:bg-gray-200 dark:text-gray-800 font-bold border-t border-b text-xs uppercase">
                            <td colspan="15" class="px-4 py-2">{{ $oac->allotmentClass->description }}</td>
                        </tr>

                        @php
                        $classTotals = array_fill_keys(array_keys($officeTotals), 0);
                        $noProgramTotals = array_fill_keys(array_keys($officeTotals), 0);
                        $classAccomplishmentCounts = ['appropriation_accomplishment' => 0, 'allotment_accomplishment' => 0];
                        @endphp

                        {{-- Appropriations WITHOUT a Program --}}
                        @if (isset($oac->groupedAppropriations['']) && count($oac->groupedAppropriations['']) > 0)
                            @php
                                // Check if there are any appropriations WITH programs
                                $hasProgramGroups = false;
                                foreach ($oac->groupedAppropriations as $program => $appropriations) {
                                    if ($program !== '') {
                                        $hasProgramGroups = true;
                                        break;
                                    }
                                }
                            @endphp

                            @foreach ($oac->groupedAppropriations[''] as $appropriation)
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="px-1 py-2 text-left">{{ $appropriation->description }}</td>
                                <td class="px-1 py-2 text-center">{{ $appropriation->account_code }}</td>
                                <td class="px-1 py-2 text-center">{{ $appropriation->fpp_code }}</td>
                                <td class="px-1 py-2 text-right" data-key="appropriation">
                                    @php $val = $appropriation->appropriation ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                    @php $val = $appropriation->sb_appropriation ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-right" data-key="reversion">
                                    @php $val = $appropriation->reversion ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-right" data-key="realignment">
                                    @php $val = $appropriation->realignment ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-right">
                                    @php $val = $appropriation->authorized_appropriation ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-right">
                                    @php $val = $appropriation->allotment ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-right">
                                    @php $val = $appropriation->for_later_release ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-right">
                                    @php $val = $appropriation->obligation ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                    @php $val = $appropriation->appropriation_balance ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                    {{ number_format($appropriation->appropriation_accomplishment ?? 0, 2) }}%
                                </td>

                                <td class="px-1 py-2 text-right">
                                    @php $val = $appropriation->allotment_balance ?? null; @endphp
                                    {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                                </td>

                                <td class="px-1 py-2 text-center">
                                    {{ number_format($appropriation->allotment_accomplishment ?? 0, 2) }}%
                                </td>
                            </tr>

                            @php
                            foreach ($noProgramTotals as $key => $val) {
                                $noProgramTotals[$key] += $appropriation->$key;
                                $classTotals[$key] += $appropriation->$key;
                                $officeTotals[$key] += $appropriation->$key;
                            }

                            $classAccomplishmentCounts['appropriation_accomplishment']++;
                            $classAccomplishmentCounts['allotment_accomplishment']++;
                            @endphp
                            @endforeach

                            {{-- Only show subtotal if there are program groups --}}
                            @if($hasProgramGroups)
                            <tr class="bg-gray-500 text-gray-100 dark:bg-gray-200 dark:text-gray-800 font-semibold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 text-[10px]">
                                <td colspan="3" class="px-2 py-2 text-right">Subtotal:</td>
                                @foreach ($noProgramTotals as $key => $val)
                                <td class="px-2 py-2 
                                    @if($key === 'appropriation_accomplishment' || $key === 'allotment_accomplishment') 
                                        text-center 
                                    @else 
                                        text-right 
                                    @endif" 
                                    data-key="{{ $key }}">
                                    
                                    @if ($key === 'appropriation_accomplishment')
                                        {{ $noProgramTotals['authorized_appropriation'] > 0 ? number_format(($noProgramTotals['obligation'] / $noProgramTotals['authorized_appropriation']) * 100, 2) : '0.00' }}%
                                    @elseif ($key === 'allotment_accomplishment')
                                        {{ $noProgramTotals['allotment'] > 0 ? number_format(($noProgramTotals['obligation'] / $noProgramTotals['allotment']) * 100, 2) : '0.00' }}%
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
                        $programAccomplishmentCounts = ['appropriation_accomplishment' => 0, 'allotment_accomplishment' => 0];
                        @endphp

                        <tr id="programRow-{{ $loop->index }}-{{ $oac->id }}" class="bg-gray-200 text-gray-700 dark:bg-gray-300 dark:text-gray-700 font-semibold border-t border-b border-gray-400 dark:border-gray-100 italic">
                            <td colspan="15" class="px-6 py-2 text-xs">{{ $program }}</td>
                        </tr>

                        @foreach ($appropriations as $appropriation)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="px-1 py-2 text-left">{{ $appropriation->description }}</td>
                            <td class="px-1 py-2 text-center">{{ $appropriation->account_code }}</td>
                            <td class="px-1 py-2 text-center">{{ $appropriation->fpp_code }}</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                @php $val = $appropriation->appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                @php $val = $appropriation->sb_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                @php $val = $appropriation->reversion ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                @php $val = $appropriation->realignment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $appropriation->authorized_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $appropriation->allotment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $appropriation->for_later_release ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $appropriation->obligation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                @php $val = $appropriation->appropriation_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($appropriation->appropriation_accomplishment ?? 0, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $appropriation->allotment_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($appropriation->allotment_accomplishment ?? 0, 2) }}%
                            </td>
                        </tr>

                        @php
                        foreach ($programTotals as $key => $val) {
                        $programTotals[$key] += $appropriation->$key;
                        $classTotals[$key] += $appropriation->$key;
                        $officeTotals[$key] += $appropriation->$key;
                        }

                        $programAccomplishmentCounts['appropriation_accomplishment']++;
                        $programAccomplishmentCounts['allotment_accomplishment']++;
                        @endphp
                        @endforeach

                        <tr class="bg-gray-500 text-white dark:bg-gray-200 dark:text-gray-800 font-semibold border-t-2 border-b-2 border-gray-700 dark:border-gray-100 text-[10px]">
                            <td colspan="3" class="px-2 py-2 text-right italic">Subtotal {{ $program }}: </td>
                            @foreach ($programTotals as $key => $val)
                            <td class="px-2 py-2 
                                @if($key === 'appropriation_accomplishment' || $key === 'allotment_accomplishment') 
                                    text-center 
                                @else 
                                    text-right 
                                @endif" 
                                data-key="{{ $key }}">
                                
                                @if ($key === 'appropriation_accomplishment')
                                    {{ $programTotals['authorized_appropriation'] > 0 ? number_format(($programTotals['obligation'] / $programTotals['authorized_appropriation']) * 100, 2) : '0.00' }}%
                                @elseif ($key === 'allotment_accomplishment')
                                    {{ $programTotals['allotment'] > 0 ? number_format(($programTotals['obligation'] / $programTotals['allotment']) * 100, 2) : '0.00' }}%
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
                            <td colspan="3" class="px-2 py-2 text-right">Total {{ $oac->allotmentClass->description }}: </td>
                            @foreach ($classTotals as $key => $val)
                            <td class="px-2 py-2 
                                @if($key === 'appropriation_accomplishment' || $key === 'allotment_accomplishment') 
                                    text-center 
                                @else 
                                    text-right 
                                @endif" 
                                data-key="{{ $key }}">
                                
                                @if ($key === 'appropriation_accomplishment')
                                    {{ $classTotals['authorized_appropriation'] > 0 ? number_format(($classTotals['obligation'] / $classTotals['authorized_appropriation']) * 100, 2) : '0.00' }}%
                                @elseif ($key === 'allotment_accomplishment')
                                    {{ $classTotals['allotment'] > 0 ? number_format(($classTotals['obligation'] / $classTotals['allotment']) * 100, 2) : '0.00' }}%
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

                        <tr class="bg-gray-800 dark:bg-gray-200 text-gray-200 dark:text-gray-700 font-bold border-t-2 border-b-2 text-[10px]">
                            <td colspan="3" class="px-2 py-2 text-right">Grand Total Current Operating Expentiture: </td>
                            @foreach ($officeTotals as $key => $val)
                            <td class="px-2 py-2 
                                @if($key === 'appropriation_accomplishment' || $key === 'allotment_accomplishment') 
                                    text-center 
                                @else 
                                    text-right 
                                @endif" 
                                data-key="{{ $key }}">
                                
                                @if ($key === 'appropriation_accomplishment')
                                    {{ $officeTotals['authorized_appropriation'] > 0 ? number_format(($officeTotals['obligation'] / $officeTotals['authorized_appropriation']) * 100, 2) : '0.00' }}%
                                @elseif ($key === 'allotment_accomplishment')
                                    {{ $officeTotals['allotment'] > 0 ? number_format(($officeTotals['obligation'] / $officeTotals['allotment']) * 100, 2) : '0.00' }}%
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

        // Intercept PDF generation
        window.printSAAOBTable = function() {
            if (!validateSignatories()) return;
            runPrintSAAOBTable(); // call actual print function
        };

        // Intercept Excel Export Submit
        document.querySelector(`form[action="{{ route('saaob.exportExcel') }}"]`)
            .addEventListener('submit', function(e) {
                if (!validateSignatories()) {
                    e.preventDefault();
                }
            });


        function runPrintSAAOBTable() {
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

            const hiddenKeys = [
                'appropriation',
                'sb_appropriation',
                'reversion',
                'realignment',
                'appropriation_balance',
                'appropriation_accomplishment'
            ];

            // Remove only <td> and <th> elements matching these keys
            table.querySelectorAll('thead th[data-key], tbody td[data-key]').forEach(cell => {
                const key = cell.getAttribute('data-key');
                if (hiddenKeys.includes(key)) {
                    cell.remove();
                }
            });

            // Style office rows
            table.querySelectorAll('[id^="officeNameRow-"]').forEach(tr => {
                tr.style.textTransform = 'uppercase';
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
                for (let i = 3; i < cells.length; i++) {
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
            let officeText = 'ALL OFFICES';
            if (officeSelect && officeSelect.selectedIndex > 0) {
                const selectedOption = officeSelect.options[officeSelect.selectedIndex];
                officeText = (selectedOption.getAttribute('data-office-name') || selectedOption.text).toUpperCase();
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
            const signatoryNameInput = document.getElementById('signatory_name');
            let signatoryName = '';
            if (signatoryNameInput && signatoryNameInput.value) {
                signatoryName = signatoryNameInput.value;
            }
            const signatoryDesignationInput = document.getElementById('signatory_designation');
            let signatoryDesignation = '';
            if (signatoryDesignationInput && signatoryDesignationInput.value) {
                signatoryDesignation = signatoryDesignationInput.value;
            }

            // Get screen dimensions
            const screenW = window.screen.availWidth;
            const screenH = window.screen.availHeight;

            const newWin = window.open('', '', `width=${screenW},height=${screenH},left=0,top=0,scrollbars=yes,resizable=yes`);
            newWin.document.write('<html><head><title>SAAOB</title>');
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
            <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</div>
            <div style="font-size:12px;">La Trinidad, Benguet</div>
            <div style="font-size:12px;">Provincial Budget Office</div>
            <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">
                STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES
            </div>
            <div style="font-size:12px; text-transform:uppercase;">${officeText}</div>
            <div style="font-size:12px;">Current</div>
            <div style="font-size:12px;">As of ${asOfDate}</div>
        </div>
    `);
            newWin.document.write(table.outerHTML);
            newWin.document.write(`
        <div style="margin-top: 30px; margin-left: 60%; font-size: 12px; text-align: left;">
            <strong>Certified Correct:</strong>
            <br><br><br>
            <div style="text-align: center;">
                <span style="font-weight: bold; text-decoration: underline;">
                    ${signatoryName ? signatoryName.toUpperCase() : '_____________________'}
                </span><br>
                <span>
                    ${signatoryDesignation ? signatoryDesignation : '_____________________'}
                </span>
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