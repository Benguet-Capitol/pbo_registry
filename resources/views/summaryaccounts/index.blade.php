<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                @php
                $selectedYear = request('year1', date('Y'));
                $selectedFundKey = request('fund_filter', 'all');
                $selectedFundLabel = $availableFunds[$selectedFundKey] ?? 'ALL FUNDS';
                @endphp
                <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Summary of Appropriations, Allotments, Obligations and Balances') }}
                |
                <span class="text-blue-800 dark:text-blue-400">
                   {{ $selectedFundLabel }} (CY {{ $selectedYear }})
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
            <!-- Fund Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="fund_filter"
                    id="fund_filter"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    onchange="this.form.submit()">
                    @foreach($availableFunds as $fundKey => $fundLabel)
                    <option value="{{ $fundKey }}" {{ request('fund_filter', 'all') == $fundKey ? 'selected' : '' }}>
                        {{ $fundLabel }}
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
    <form method="GET" action="{{ route('summaryaccounts.exportExcel') }}" style="display:inline;">
        <input type="hidden" name="year1" value="{{ request('year1') }}">
        <input type="hidden" name="as_of_filter" value="{{ request('as_of_filter') }}">
        <input type="hidden" name="signatory_name" value="{{ request('signatory_name') }}">
        <input type="hidden" name="signatory_designation" value="{{ request('signatory_designation') }}">
        <input type="hidden" name="fund_filter" value="{{ request('fund_filter', 'all') }}">

        <button type="submit" class="text-green-700 border border-green-700 hover:bg-green-700 hover:text-white font-medium rounded-lg text-xs px-3 py-2 text-center dark:border-green-400 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
            Generate Excel
        </button>
    </form>
    </div>

    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg mt-6 mb-6 dark:bg-gray-800">
    <div class="p-4 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
        <div class="overflow-x-auto overflow-y-auto max-h-[700px]">
            <table id="dashboardTable" class="min-w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                <thead class="sticky top-0 z-10 bg-gray-700 text-gray-100 dark:bg-gray-200 dark:text-gray-900">
                    <tr>
                        <th class="px-1 py-1 w-[150px] text-center">Functions / Programs / Projects / Activities</th>
                        <th class="px-1 py-1 w-[140px] text-center">Account Code</th>
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
                    @forelse ($allotmentClassTotals as $className => $items)
                        {{-- Allotment Class Header --}}
                        <tr class="bg-gray-600 text-gray-100 dark:bg-gray-300 dark:text-gray-800 font-bold border-t border-b">
                            <td colspan="14" class="px-2 py-2 text-xs">{{ $className }}</td>
                        </tr>

                        {{-- Individual Accounts with Appropriations --}}
                        @if(isset($items['accounts']) && !empty($items['accounts']))
                            @foreach ($items['accounts'] as $app)
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-1 py-2 text-left">{{ $app['description'] }}</td>
                                <td class="px-1 py-2 text-center">{{ $app['account_code'] }}</td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['appropriation'] == 0 || is_null($app['appropriation']))
                                        -
                                    @elseif ($app['appropriation'] < 0)
                                        ({{ number_format(abs($app['appropriation']), 2) }})
                                    @else
                                        {{ number_format($app['appropriation'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['sb_appropriation'] == 0 || is_null($app['sb_appropriation']))
                                        -
                                    @elseif ($app['sb_appropriation'] < 0)
                                        ({{ number_format(abs($app['sb_appropriation']), 2) }})
                                    @else
                                        {{ number_format($app['sb_appropriation'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['reversion'] == 0 || is_null($app['reversion']))
                                        -
                                    @elseif ($app['reversion'] < 0)
                                        ({{ number_format(abs($app['reversion']), 2) }})
                                    @else
                                        {{ number_format($app['reversion'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['realignment'] == 0 || is_null($app['realignment']))
                                        -
                                    @elseif ($app['realignment'] < 0)
                                        ({{ number_format(abs($app['realignment']), 2) }})
                                    @else
                                        {{ number_format($app['realignment'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['authorized_appropriation'] == 0 || is_null($app['authorized_appropriation']))
                                        -
                                    @elseif ($app['authorized_appropriation'] < 0)
                                        ({{ number_format(abs($app['authorized_appropriation']), 2) }})
                                    @else
                                        {{ number_format($app['authorized_appropriation'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['allotment'] == 0 || is_null($app['allotment']))
                                        -
                                    @elseif ($app['allotment'] < 0)
                                        ({{ number_format(abs($app['allotment']), 2) }})
                                    @else
                                        {{ number_format($app['allotment'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['for_later_release'] == 0 || is_null($app['for_later_release']))
                                        -
                                    @elseif ($app['for_later_release'] < 0)
                                        ({{ number_format(abs($app['for_later_release']), 2) }})
                                    @else
                                        {{ number_format($app['for_later_release'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['obligation'] == 0 || is_null($app['obligation']))
                                        -
                                    @elseif ($app['obligation'] < 0)
                                        ({{ number_format(abs($app['obligation']), 2) }})
                                    @else
                                        {{ number_format($app['obligation'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['appropriation_balance'] == 0 || is_null($app['appropriation_balance']))
                                        -
                                    @elseif ($app['appropriation_balance'] < 0)
                                        ({{ number_format(abs($app['appropriation_balance']), 2) }})
                                    @else
                                        {{ number_format($app['appropriation_balance'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-center">
                                    {{ number_format($app['appropriation_accomplishment'], 2) }}%
                                </td>
                                <td class="px-1 py-2 text-right">
                                    @if ($app['allotment_balance'] == 0 || is_null($app['allotment_balance']))
                                        -
                                    @elseif ($app['allotment_balance'] < 0)
                                        ({{ number_format(abs($app['allotment_balance']), 2) }})
                                    @else
                                        {{ number_format($app['allotment_balance'], 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-2 text-center">
                                    {{ number_format($app['allotment_accomplishment'], 2) }}%
                                </td>
                            </tr>
                        @endforeach
                        @endif

                        {{-- Total Per Allotment Class --}}
                        @if(isset($items['subtotals']))
                        <tr class="bg-gray-200 dark:bg-gray-900 dark:text-white text-gray-700 font-bold border-t-2 border-b-2 text-[10px]">
                            <td colspan="2" class="px-2 py-2 text-right">Total {{ $className }}:</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['appropriation'], 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['sb_appropriation'], 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['reversion'], 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['realignment'], 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['authorized_appropriation'], 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['allotment'], 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['for_later_release'], 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['obligation'], 2) }}</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['appropriation_balance'], 2) }}</td>
                            <td class="px-1 py-2 text-center">{{ number_format($items['subtotals']['utilization_percent'], 2) }}%</td>
                            <td class="px-1 py-2 text-right">{{ number_format($items['subtotals']['allotment_balance'], 2) }}</td>
                            <td class="px-1 py-2 text-center">{{ number_format($items['subtotals']['allotment_utilization_percent'], 2) }}%</td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="14" class="px-3 py-4 text-center text-gray-500">No data available</td>
                        </tr>
                    @endforelse

                    {{-- Grand Total Row --}}
                    @if($allotmentClassTotals->isNotEmpty())
                        @php
                            $grandTotal = [
                                'appropriation' => 0,
                                'sb_appropriation' => 0,
                                'reversion' => 0,
                                'realignment' => 0,
                                'authorized_appropriation' => 0,
                                'allotment' => 0,
                                'for_later_release' => 0,
                                'obligation' => 0,
                                'appropriation_balance' => 0,
                                'allotment_balance' => 0,
                            ];
                            
                            foreach ($allotmentClassTotals as $class => $items) {
                                if (isset($items['subtotals'])) {
                                    foreach ($grandTotal as $key => $val) {
                                        $grandTotal[$key] += $items['subtotals'][$key] ?? 0;
                                    }
                                }
                            }
                            
                            $grandUtilization = $grandTotal['authorized_appropriation'] > 0 
                                ? ($grandTotal['obligation'] / $grandTotal['authorized_appropriation']) * 100 
                                : 0;
                            
                            $grandAllotmentUtilization = $grandTotal['allotment'] > 0 
                                ? ($grandTotal['obligation'] / $grandTotal['allotment']) * 100 
                                : 0;
                        @endphp
                        <tr class="bg-gray-800 dark:bg-gray-200 text-gray-100 dark:text-gray-900 font-bold border-t-4 border-b-2 text-[10px]">
                            <td colspan="2" class="px-2 py-3 text-right">GRAND TOTAL:</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['appropriation'], 2) }}</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['sb_appropriation'], 2) }}</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['reversion'], 2) }}</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['realignment'], 2) }}</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['authorized_appropriation'], 2) }}</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['allotment'], 2) }}</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['for_later_release'], 2) }}</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['obligation'], 2) }}</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['appropriation_balance'], 2) }}</td>
                            <td class="px-1 py-3 text-center">{{ number_format($grandUtilization, 2) }}%</td>
                            <td class="px-1 py-3 text-right">{{ number_format($grandTotal['allotment_balance'], 2) }}</td>
                            <td class="px-1 py-3 text-center">{{ number_format($grandAllotmentUtilization, 2) }}%</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>


    <script>
        // Validation for the signatory fields and office allotment class
    function validateSignatories() {
        const name = document.getElementById('signatory_name').value.trim();
        const designation = document.getElementById('signatory_designation').value.trim();
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
        document.querySelector(`form[action="{{ route('summaryaccounts.exportExcel') }}"]`)
            .addEventListener('submit', function(e) {
                if (!validateSignatories()) {
                    e.preventDefault();
                }
            });
    </script>

</x-app-layout>