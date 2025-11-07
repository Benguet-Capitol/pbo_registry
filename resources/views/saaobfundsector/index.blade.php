<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                @php
                $selectedFund = null;

                if (request('fund_filter')) {
                if (request('fund_filter') === 'others') {
                $selectedFund = 'BeGHEE and SEF';
                } else {
                $selectedFund = request('fund_filter'); // e.g., 'General Fund'
                }
                }
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                    {{ __('Statement of Appropriations, Allotments, Obligations and Balances') }}
                    |
                    <span class="text-blue-800 dark:text-blue-400">
                        {{ $selectedFund ?? 'All Funds' }}
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
            <!--  Fund Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="fund_filter"
                    id="fund_filter"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    <option value="">All Funds</option>

                    @foreach($allFunds as $fund)
                    @if($fund === 'General Fund')
                    <option value="{{ $fund }}" data-fund-name="{{ $fund }}" {{ request('fund_filter') == $fund ? 'selected' : '' }}>
                        {{ $fund }}
                    </option>
                    @endif
                    @endforeach

                    <option value="others"
                        data-fund-name="Benguet General Hospital Economic Enterprise and Special Education Fund" {{ request('fund_filter') == 'others' ? 'selected' : '' }}>
                        BeGHEE and SEF
                    </option>
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
            <div class="flex space-x-2 flex-col items-start">
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
            <div class="flex space-x-2 flex-col items-start">
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
                    onclick="printSAAOBFundSectorTable()"
                    class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900"
                    type="button">
                    Print Report
                </button>
    </form>
    <form method="GET" action="{{ route('saaobFundSector.exportExcel') }}" style="display:inline;">
        <input type="hidden" name="year1" value="{{ request('year1') }}">
        <input type="hidden" name="fund_filter" value="{{ request('fund_filter') }}">
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
                <table id="saaobFundSectorTable" class="w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-white dark:bg-gray-200 dark:text-gray-900">
                        <tr>
                            <th class="px-1 py-1 w-[70px] text-center">Code</th>
                            <th class="px-1 py-1 w-[170px] text-center">Function / Program / Project</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation">Approved Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="sb_appropriation">Supplemental Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="reversion">Reversions</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="realignment">Realignments</th>
                            <th class="px-1 py-1 w-[100px] text-center">Authorized Appropriations</th>
                            <th class="px-1 py-1 w-[100px] text-center">Allotments</th>
                            <th class="px-1 py-1 w-[100px] text-center">For Later Release</th>
                            <th class="px-1 py-1 w-[100px] text-center">Obligations</th>
                            <th class="px-1 py-1 w-[100px] text-center" data-key="appropriation_balance">Authorized Appropriation Balance</th>
                            <th class="px-1 py-1 w-[70px] text-center" data-key="appropriation_accomplishment">% of Utilization</th>
                            <th class="px-1 py-1 w-[100px] text-center">Balance</th>
                            <th class="px-1 py-1 w-[70px] text-center">% of Utilization</th>
                        </tr>
                    </thead>

                    <tbody class="border border-gray-300 dark:border-gray-600 text-[10px]">
                        @foreach($groupedFunds as $fund)
                        <tr id="fundRow" class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-center text-sm">
                            <td colspan="15" class="px-2 py-3">{{ $fund->fund_type }}</td>
                        </tr>
                        @foreach ($fund->matchedSectorsByCategory as $category => $sectors)
                        <tr id="fundSourceRow" class="bg-gray-600 text-white dark:bg-gray-200 dark:text-gray-800 font-bold text-xs border-t border-b border-gray-700 dark:border-gray-100">
                            <td colspan="15" class="px-4 py-2"> {{ $category }} Appropriations </td>
                        </tr>
                        @foreach($sectors as $sector)
                        @if ($sector->sector_code !== '' && $sector->present_allotment_classes->isNotEmpty())
                        <tr id="sectorRow" class="bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-white text-xs font-semibold italic border-t border-b border-gray-400 dark:border-gray-100">
                            <td class="px-1 py-2 text-center"> {{ $sector->sector_code }}</td>
                            <td colspan="14" class="px-1 py-2 text-left">{{ $sector->sector }}</td>
                        </tr>
                        @foreach ($sector->present_allotment_classes as $aClass)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="px-1 py-2"></td>
                            <td class="px-1 py-2 text-left">{{ $aClass->description }}</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                @if (is_null($aClass->approved_appropriation) || $aClass->approved_appropriation == 0)
                                    -
                                @elseif ($aClass->approved_appropriation < 0)
                                    ({{ number_format(abs($aClass->approved_appropriation), 2) }})
                                @else
                                    {{ number_format($aClass->approved_appropriation, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                @if (is_null($aClass->supplemental) || $aClass->supplemental == 0)
                                    -
                                @elseif ($aClass->supplemental < 0)
                                    ({{ number_format(abs($aClass->supplemental), 2) }})
                                @else
                                    {{ number_format($aClass->supplemental, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                @if (is_null($aClass->reversion) || $aClass->reversion == 0)
                                    -
                                @elseif ($aClass->reversion < 0)
                                    ({{ number_format(abs($aClass->reversion), 2) }})
                                @else
                                    {{ number_format($aClass->reversion, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                @if (is_null($aClass->realignment) || $aClass->realignment == 0)
                                    -
                                @elseif ($aClass->realignment < 0)
                                    ({{ number_format(abs($aClass->realignment), 2) }})
                                @else
                                    {{ number_format($aClass->realignment, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->authorized_appropriation) || $aClass->authorized_appropriation == 0)
                                    -
                                @elseif ($aClass->authorized_appropriation < 0)
                                    ({{ number_format(abs($aClass->authorized_appropriation), 2) }})
                                @else
                                    {{ number_format($aClass->authorized_appropriation, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->allotment) || $aClass->allotment == 0)
                                    -
                                @elseif ($aClass->allotment < 0)
                                    ({{ number_format(abs($aClass->allotment), 2) }})
                                @else
                                    {{ number_format($aClass->allotment, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->for_later_release) || $aClass->for_later_release == 0)
                                    -
                                @elseif ($aClass->for_later_release < 0)
                                    ({{ number_format(abs($aClass->for_later_release), 2) }})
                                @else
                                    {{ number_format($aClass->for_later_release, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->obligations) || $aClass->obligations == 0)
                                    -
                                @elseif ($aClass->obligations < 0)
                                    ({{ number_format(abs($aClass->obligations), 2) }})
                                @else
                                    {{ number_format($aClass->obligations, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                @if (is_null($aClass->appropriation_balance) || $aClass->appropriation_balance == 0)
                                    -
                                @elseif ($aClass->appropriation_balance < 0)
                                    ({{ number_format(abs($aClass->appropriation_balance), 2) }})
                                @else
                                    {{ number_format($aClass->appropriation_balance, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($aClass->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                @if (is_null($aClass->allotment_balance) || $aClass->allotment_balance == 0)
                                    -
                                @elseif ($aClass->allotment_balance < 0)
                                    ({{ number_format(abs($aClass->allotment_balance), 2) }})
                                @else
                                    {{ number_format($aClass->allotment_balance, 2) }}
                                @endif
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($aClass->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-500 text-white dark:bg-gray-600 dark:text-white font-semibold border-t-2 border-b-2 border-gray-700 dark:border-gray-100">
                            <td colspan="2" class="px-1 py-2 text-right">Total:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $sector->totals->approved_appropriation == 0 ? '-' : ($sector->totals->approved_appropriation < 0 ? '(' . number_format(abs($sector->totals->approved_appropriation), 2) . ')' : number_format($sector->totals->approved_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $sector->totals->supplemental == 0 ? '-' : ($sector->totals->supplemental < 0 ? '(' . number_format(abs($sector->totals->supplemental), 2) . ')' : number_format($sector->totals->supplemental, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $sector->totals->reversion == 0 ? '-' : ($sector->totals->reversion < 0 ? '(' . number_format(abs($sector->totals->reversion), 2) . ')' : number_format($sector->totals->reversion, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $sector->totals->realignment == 0 ? '-' : ($sector->totals->realignment < 0 ? '(' . number_format(abs($sector->totals->realignment), 2) . ')' : number_format($sector->totals->realignment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->authorized_appropriation == 0 ? '-' : ($sector->totals->authorized_appropriation < 0 ? '(' . number_format(abs($sector->totals->authorized_appropriation), 2) . ')' : number_format($sector->totals->authorized_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->allotment == 0 ? '-' : ($sector->totals->allotment < 0 ? '(' . number_format(abs($sector->totals->allotment), 2) . ')' : number_format($sector->totals->allotment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->for_later_release == 0 ? '-' : ($sector->totals->for_later_release < 0 ? '(' . number_format(abs($sector->totals->for_later_release), 2) . ')' : number_format($sector->totals->for_later_release, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->obligations == 0 ? '-' : ($sector->totals->obligations < 0 ? '(' . number_format(abs($sector->totals->obligations), 2) . ')' : number_format($sector->totals->obligations, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                {{ $sector->totals->appropriation_balance == 0 ? '-' : ($sector->totals->appropriation_balance < 0 ? '(' . number_format(abs($sector->totals->appropriation_balance), 2) . ')' : number_format($sector->totals->appropriation_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($sector->totals->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $sector->totals->allotment_balance == 0 ? '-' : ($sector->totals->allotment_balance < 0 ? '(' . number_format(abs($sector->totals->allotment_balance), 2) . ')' : number_format($sector->totals->allotment_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($sector->totals->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        @php
                        $hasValidSector = $sectors->contains(fn($s) =>
                        $s->sector_code !== '' && $s->present_allotment_classes->isNotEmpty()
                        );
                        @endphp
                        @if ($hasValidSector)
                        <tr class="bg-gray-100 text-gray-700 dark:bg-gray-200 dark:text-gray-800 font-bold border-t-2 border-b-2 border-gray-700 dark:border-gray-100">
                            <td colspan="2" class="px-1 py-2 text-right">Total {{ $category }} Appropriations:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $fund->categoryTotals[$category]->approved_appropriation == 0 ? '-' : ($fund->categoryTotals[$category]->approved_appropriation < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->approved_appropriation), 2) . ')' : number_format($fund->categoryTotals[$category]->approved_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $fund->categoryTotals[$category]->supplemental == 0 ? '-' : ($fund->categoryTotals[$category]->supplemental < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->supplemental), 2) . ')' : number_format($fund->categoryTotals[$category]->supplemental, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $fund->categoryTotals[$category]->reversion == 0 ? '-' : ($fund->categoryTotals[$category]->reversion < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->reversion), 2) . ')' : number_format($fund->categoryTotals[$category]->reversion, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $fund->categoryTotals[$category]->realignment == 0 ? '-' : ($fund->categoryTotals[$category]->realignment < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->realignment), 2) . ')' : number_format($fund->categoryTotals[$category]->realignment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->authorized_appropriation == 0 ? '-' : ($fund->categoryTotals[$category]->authorized_appropriation < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->authorized_appropriation), 2) . ')' : number_format($fund->categoryTotals[$category]->authorized_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->allotment == 0 ? '-' : ($fund->categoryTotals[$category]->allotment < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->allotment), 2) . ')' : number_format($fund->categoryTotals[$category]->allotment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->for_later_release == 0 ? '-' : ($fund->categoryTotals[$category]->for_later_release < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->for_later_release), 2) . ')' : number_format($fund->categoryTotals[$category]->for_later_release, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->obligations == 0 ? '-' : ($fund->categoryTotals[$category]->obligations < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->obligations), 2) . ')' : number_format($fund->categoryTotals[$category]->obligations, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                {{ $fund->categoryTotals[$category]->appropriation_balance == 0 ? '-' : ($fund->categoryTotals[$category]->appropriation_balance < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->appropriation_balance), 2) . ')' : number_format($fund->categoryTotals[$category]->appropriation_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fund->categoryTotals[$category]->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $fund->categoryTotals[$category]->allotment_balance == 0 ? '-' : ($fund->categoryTotals[$category]->allotment_balance < 0 ? '(' . number_format(abs($fund->categoryTotals[$category]->allotment_balance), 2) . ')' : number_format($fund->categoryTotals[$category]->allotment_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($fund->categoryTotals[$category]->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endif

                        {{-- Display Allotment Class Totals Right After Category Totals --}}
                        @if (
                        $sectors->filter(fn($sector) => $sector->present_allotment_classes->isNotEmpty())->isNotEmpty()
                        && $fund->categoryClassStats[$category]?->isNotEmpty()
                        )
                        <tr id="fundSourceRow" class="bg-gray-700 text-white text-xs font-bold border-t border-b border-gray-600">
                            <td colspan="15" class="px-2 py-2 text-left">{{ $category }} Appropriations by Allotment Class</td>
                        </tr>

                        @foreach ($fund->categoryClassStats[$category] as $classCode => $row)
                        <tr class="bg-white text-gray-800 border-b dark:bg-gray-900 dark:text-gray-200">
                            <td colspan="2" class="px-1 py-2 text-left">{{ $row->description }}</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                {{ $row->approved_appropriation == 0 || is_null($row->approved_appropriation) ? '-' : ($row->approved_appropriation < 0 ? '(' . number_format(abs($row->approved_appropriation), 2) . ')' : number_format($row->approved_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                {{ $row->supplemental == 0 || is_null($row->supplemental) ? '-' : ($row->supplemental < 0 ? '(' . number_format(abs($row->supplemental), 2) . ')' : number_format($row->supplemental, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                {{ $row->reversion == 0 || is_null($row->reversion) ? '-' : ($row->reversion < 0 ? '(' . number_format(abs($row->reversion), 2) . ')' : number_format($row->reversion, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                {{ $row->realignment == 0 || is_null($row->realignment) ? '-' : ($row->realignment < 0 ? '(' . number_format(abs($row->realignment), 2) . ')' : number_format($row->realignment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->authorized_appropriation == 0 || is_null($row->authorized_appropriation) ? '-' : ($row->authorized_appropriation < 0 ? '(' . number_format(abs($row->authorized_appropriation), 2) . ')' : number_format($row->authorized_appropriation, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->allotment == 0 || is_null($row->allotment) ? '-' : ($row->allotment < 0 ? '(' . number_format(abs($row->allotment), 2) . ')' : number_format($row->allotment, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->for_later_release == 0 || is_null($row->for_later_release) ? '-' : ($row->for_later_release < 0 ? '(' . number_format(abs($row->for_later_release), 2) . ')' : number_format($row->for_later_release, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->obligations == 0 || is_null($row->obligations) ? '-' : ($row->obligations < 0 ? '(' . number_format(abs($row->obligations), 2) . ')' : number_format($row->obligations, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                {{ $row->appropriation_balance == 0 || is_null($row->appropriation_balance) ? '-' : ($row->appropriation_balance < 0 ? '(' . number_format(abs($row->appropriation_balance), 2) . ')' : number_format($row->appropriation_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($row->appropriation_accomplishment, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                {{ $row->allotment_balance == 0 || is_null($row->allotment_balance) ? '-' : ($row->allotment_balance < 0 ? '(' . number_format(abs($row->allotment_balance), 2) . ')' : number_format($row->allotment_balance, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($row->allotment_accomplishment, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                        {{-- Totals per Category after Allotment Class Totals --}}
                        <tr class="bg-gray-100 text-gray-700 dark:bg-gray-200 dark:text-gray-800 font-bold border-t-2 border-b-2 border-gray-700 dark:border-gray-100">
                            <td colspan="2" class="px-1 py-2 text-right">Total {{ $category }} Appropriations:</td>
                            <td class="px-1 py-2 text-right" data-key="appropriation">
                                @php $val = $fund->categoryTotals[$category]->approved_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="sb_appropriation">
                                @php $val = $fund->categoryTotals[$category]->supplemental ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="reversion">
                                @php $val = $fund->categoryTotals[$category]->reversion ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="realignment">
                                @php $val = $fund->categoryTotals[$category]->realignment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->authorized_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->allotment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->for_later_release ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->obligations ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-right" data-key="appropriation_balance">
                                @php $val = $fund->categoryTotals[$category]->appropriation_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fund->categoryTotals[$category]->appropriation_accomplishment ?? 0, 2) }}%
                            </td>

                            <td class="px-1 py-2 text-right">
                                @php $val = $fund->categoryTotals[$category]->allotment_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-2 text-center">
                                {{ number_format($fund->categoryTotals[$category]->allotment_accomplishment ?? 0, 2) }}%
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        <tr class="bg-gray-700 text-white dark:bg-gray-700 font-bold text-right border-t-2 border-b-2 border-white dark:border-gray-100">
                            <td colspan="2" class="px-1 py-4 text-right">Grand Total {{ $fund->fund_type }}:</td>
                           <td class="px-1 py-4 text-right" data-key="appropriation">
                                @php $val = $fund->grandTotal->approved_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right" data-key="sb_appropriation">
                                @php $val = $fund->grandTotal->supplemental ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right" data-key="reversion">
                                @php $val = $fund->grandTotal->reversion ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right" data-key="realignment">
                                @php $val = $fund->grandTotal->realignment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->authorized_appropriation ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->allotment ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->for_later_release ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->obligations ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-right" data-key="appropriation_balance">
                                @php $val = $fund->grandTotal->appropriation_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            {{-- Percentages stay as-is --}}
                            <td class="px-1 py-4 text-center" data-key="appropriation_accomplishment">
                                {{ number_format($fund->grandTotal->appropriation_accomplishment ?? 0, 2) }}%
                            </td>

                            <td class="px-1 py-4 text-right">
                                @php $val = $fund->grandTotal->allotment_balance ?? null; @endphp
                                {{ is_null($val) || $val == 0 ? '-' : ($val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2)) }}
                            </td>

                            <td class="px-1 py-4 text-center">
                                {{ number_format($fund->grandTotal->allotment_accomplishment ?? 0, 2) }}%
                            </td>
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
        window.printSAAOBFundSectorTable = function() {
            if (!validateSignatories()) return;
            runPrintSAAOBFundSectorTable(); // call actual print function
        };

        // Intercept Excel Export Submit
        document.querySelector(`form[action="{{ route('saaobFundSector.exportExcel') }}"]`)
            .addEventListener('submit', function(e) {
                if (!validateSignatories()) {
                    e.preventDefault();
                }
            });


        function runPrintSAAOBFundSectorTable() {
            const table = document.getElementById('saaobFundSectorTable').cloneNode(true);
            const hiddenKeys = [
                'appropriation', 'sb_appropriation', 'reversion', 'realignment',
                'appropriation_balance', 'appropriation_accomplishment'
            ];

            table.querySelectorAll('thead th[data-key], tbody td[data-key]').forEach(cell => {
                const key = cell.getAttribute('data-key');
                if (hiddenKeys.includes(key)) {
                    cell.remove();
                }
            });

            // Styling rows
            table.querySelectorAll('[id^="fundRow"]').forEach(tr => {
                tr.style.textTransform = 'uppercase';
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
                tr.style.textAlign = 'center';
            });
            table.querySelectorAll('[id^="fundSourceRow"]').forEach(tr => {
                tr.style.fontWeight = 'bold';
                tr.style.fontSize = '10px';
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
                for (let i = 2; i < cells.length; i++) {
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

            // Get selected fund
            const fundSelect = document.getElementById('fund_filter');
            let fundText = 'All Funds';
            if (fundSelect && fundSelect.selectedIndex > 0) {
                const selectedOption = fundSelect.options[fundSelect.selectedIndex];
                fundText = (selectedOption.getAttribute('data-fund-name') || selectedOption.text);
            }

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

            const signatoryName = document.getElementById('signatory_name').value.trim();
            const signatoryDesignation = document.getElementById('signatory_designation').value.trim();

            // Get screen dimensions
            const screenW = window.screen.availWidth;
            const screenH = window.screen.availHeight;

            const newWin = window.open('', '', `width=${screenW},height=${screenH},left=0,top=0,scrollbars=yes,resizable=yes`);
            newWin.document.write('<html><head><title>SAAOB</title>');
            newWin.document.write('<style>body{font-family:sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:4px;} </style>');
            newWin.document.write('</head><body>');
            newWin.document.write(`
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:12px;">Republic of the Philippines</div>
                <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">PROVINCIAL GOVERNMENT OF BENGUET</div>
                <div style="font-size:12px;">La Trinidad, Benguet</div>
                <div style="font-size:12px;">Provincial Budget Office</div>
                <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">STATEMENT OF APPROPRIATIONS, ALLOTMENTS, OBLIGATIONS AND BALANCES</div>
                <div style="font-size:12px; ">(Current Legislative Appropriation)</div>
                <div style="font-size:12px; ">${fundText}</div>
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