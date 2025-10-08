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
                    {{ __('Statement of Appropriations, Allotments, Obligations, Disbursements and Balances') }}
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

        <!-- First row: Year, Office, As of -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 items-center mb-3">
            <!-- Year Filter -->
            <div class="flex items-center space-x-2">
                <x-form.select
                    name="year1"
                    id="year1"
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="filter-select w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
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
                <table id="saaobFundSectorTable" class="w-full text-[11px] text-gray-900 dark:text-gray-300 text-left">
                    <thead class="sticky top-0 z-10 bg-gray-700 text-white dark:bg-gray-200 dark:text-gray-900">
                        <tr>
                            <th class="px-1 py-1 w-[100px] text-center">Allotment Class</th>
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
                        @foreach($funds as $fund)
                        <tr id="fundRow" class="bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-200 uppercase font-bold border-t border-b border-gray-700 dark:border-gray-100 text-center text-sm">
                            <td colspan="15" class="px-2 py-3">{{ $fund->fund }}</td>
                        </tr>
                        <tr class="bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-white text-xs font-semibold italic border-t border-b border-gray-400 dark:border-gray-100">
                            <td colspan="15" class="px-1 py-2">Regular Budget</td>
                        </tr>
                            @foreach($fund->uniqueAllotmentClasses as $class)
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <td class="px-1 py-2 text-center">{{ $class->class }}</td>
                                <td class="px-1 py-2 text-right" data-key="appropriation">{{ number_format($class->approved_appropriation, 2) }}</td>
                                <td class="px-1 py-2 text-right" data-key="sb_appropriation">{{ number_format($class->supplemental, 2) }}</td>
                                <td class="px-1 py-2 text-right" data-key="reversion">{{ number_format($class->reversion, 2) }}</td>
                                <td class="px-1 py-2 text-right" data-key="realignment">{{ number_format($class->realignment, 2) }}</td>
                                <td class="px-1 py-2 text-right" data-key="authorized_appropriation">{{ number_format($class->authorized_appropriation, 2) }}</td>
                                <td class="px-1 py-2 text-right" data-key="allotment">{{ number_format($class->allotment, 2) }}</td>
                                <td class="px-1 py-2 text-right" data-key="obligation">{{ number_format($class->obligation, 2) }}</td>

                            </tr>
                            @endforeach
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