<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <!-- Left: Obligations Title -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Obligations') }}
            </h3>

            <!-- Right: Breadcrumb Navigation -->
            @if(isset($breadcrumb))
            <nav class="text-xs text-gray-600 dark:text-gray-300" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex items-center space-x-1 rtl:space-x-reverse">
                    @foreach ($breadcrumb as $index => $item)
                    <li>
                        @if (!empty($item['route']) && $index < count($breadcrumb) - 1)
                            <a href="{{ $item['route'] }}" class="text-gray-600 hover:underline dark:text-blue-400">
                            {{ $item['label'] }}
                            </a>
                            <span class="mx-2">/</span>
                            @else
                            <span class="text-gray-500 dark:text-gray-400">{{ $item['label'] }}</span>
                            @endif
                    </li>
                    @endforeach
                </ol>
            </nav>
            @endif
        </div>
    </x-slot>
    <div class="relative mx-auto border w-full shadow-lg rounded-md bg-white max-h-full dark:bg-gray-800 dark:border-gray-700">
        <!-- Content -->
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                    {{ __('Edit Obligation Request') }}
                </h3>
            </div>
            <!-- Body -->
            <div class="mt-2 px-7 py-3 text-xs">
                <form id="editObligationsForm" method="POST" action="{{ route('obligations.update', $obligation) }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">

                            <!-- Office and Allotment Class -->
                            <div class="sm:col-span-3 relative">
                                <x-form.label for="office_allotment_class" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-laptop-house"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            type="text"
                                            name="office_allotment_class"
                                            id="office_allotment_class"
                                            placeholder="{{ __('Office and Allotment Class') }}"
                                            value="{{ $obligation->officeAllotmentClass->offices->office_abbreviation ?? '' }} - {{ $obligation->officeAllotmentClass->allotmentClass->class ?? '' }}"
                                            class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            oninput="filterOfficeAllotmentClasses()"
                                            autocomplete="off" />
                                    </x-form.input-with-icon-wrapper>
                                    <!-- Hidden input to store the selected ID -->
                                    <input type="hidden" name="office_allotment_class_id" id="office_allotment_class_id" />
                                    <div id="OfficeAllotmentClassDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                        <!-- Suggestions appear here -->
                                    </div>
                                    <span id="OfficeAllotmentClassError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="obr_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="obr_date" autocomplete="off" id="obr_date" placeholder="{{ __('Date') }}" value="{{ $obligation->obr_date }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="obr_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon type='text' name="obr_no" value="{{ $obligation->obr_no }}" autocomplete="off" id="obr_no" placeholder="{{ __('Number') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Obligation Type -->
                            <div class="sm:col-span-3">
                                <x-form.label for="obr_type" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Type')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-arrow-up-right-dots"></i>
                                        </x-slot>
                                        <x-form.select
                                            withicon
                                            id="obr_type"
                                            name="obr_type"
                                            class="block w-full dark:bg-gray-800 dark:text-gray-200">
                                            <option value="">{{ __('Select Obligation Type') }}</option>
                                            <option value="Regular" {{ $obligation->obr_type == 'Regular' ? 'selected' : '' }}>{{ __('Regular') }}</option>
                                            <option value="Purchase Request" {{ $obligation->obr_type == 'Purchase Request' ? 'selected' : '' }}>{{ __('Purchase Request') }}</option>
                                            <option value="Project/Contract" {{ $obligation->obr_type == 'Project/Contract' ? 'selected' : '' }}>{{ __('Project/Contract') }}</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="obrTypeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Particulars -->
                            <div class="sm:col-span-6">
                                <x-form.label for="particulars" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Particulars')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-align-justify"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="particulars" autocomplete="off" id="particulars" placeholder="{{ __('Particulars') }}" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200">{{ old('particulars', $obligation->particulars) }}</x-form.textarea>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="particularsError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="remarks" autocomplete="off" id="remarks" placeholder="{{ __('Remarks') }}" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200">{{ old('remarks', $obligation->remarks) }}</x-form.textarea>
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Programs Table -->
                            <div class="sm:col-span-6">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                <div class="mt-2 overflow-x-auto">
                                    <table id="programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Account Code') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Description') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Program') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Balance from Allotment') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Amount of Obligation') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ">
                                            @if(isset($obligation_amounts) && count($obligation_amounts) > 0)
                                                @foreach($obligation_amounts as $amount)
                                                <tr>
                                                    <td class="px-1 py-2">
                                                        <x-form.input
                                                            name="account_code[]"
                                                            placeholder="{{ __('Account Code') }}"
                                                            value="{{ $amount['account_code'] ?? '' }}"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            oninput="filterAccountCodes(this)"
                                                            autocomplete="off" />
                                                        <div class="account-code-dropdown absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                                            <!-- Suggestions will appear here -->
                                                        </div>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea
                                                            name="description[]"
                                                            placeholder="{{ __('Description') }}"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            autocomplete="off">{{ $amount['description'] ?? '' }}</x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea
                                                            name="programs[]"
                                                            placeholder="{{ __('Program') }}"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            autocomplete="off">{{ $amount['program'] ?? '' }}</x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input
                                                            type="text"
                                                            name="balance_from_allotment[]"
                                                            value="{{ number_format($amount['obr_amount'] ?? 0, 2) }}"
                                                            placeholder="{{ __('Balance') }}"
                                                            autocomplete="off"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            readonly />
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input
                                                            type="text"
                                                            name="amount_of_obligation[]"
                                                            value="{{ number_format($amount['amount'] ?? 0, 2) }}"
                                                            oninput="validateAmount(this); calculateTotalObligation();"
                                                            onblur="calculateTotalObligation();"
                                                            placeholder="{{ __('Amount') }}"
                                                            autocomplete="off"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                                                    </td>
                                                    <td class="px-1 py-2 text-center">
                                                        <button type="button" onclick="deleteRow(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td class="px-1 py-2">
                                                        <x-form.input name="account_code[]" placeholder="{{ __('Account Code') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterAccountCodes(this)" autocomplete="off" />
                                                        <div class="account-code-dropdown absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                                            <!-- Suggestions will appear here -->
                                                        </div>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea name="description[]" placeholder="{{ __('Description') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea name="programs[]" placeholder="{{ __('Program') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input type="text" name="balance_from_allotment[]" placeholder="{{ __('Balance') }}" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input type="text" name="amount_of_obligation[]" oninput="validateAmount(this); calculateTotalObligation();" onblur="calculateTotalObligation();" placeholder="{{ __('Amount') }}" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                                                    </td>
                                                    <td class="px-1 py-2 text-center">
                                                        <button type="button" onclick="deleteRow(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                            <!-- Additional rows can be dynamically added using JavaScript -->
                                        </tbody>
                                        <!-- Fixed Total Row -->
                                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <td colspan="4" class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Total Obligation') }}
                                                </td>
                                                <td class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    <span id="totalObligation" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-right text-xs">0.00</span>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <!-- Add Button for Dynamic Rows -->
                                <div class="sm:col-span-6 mt-4">
                                    <button type="button" onclick="addRow()" class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                                        <i class="fas fa-plus text-sm mr-2"></i>
                                        {{ __('Add Row') }}
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div class="justify-center items-center mb-4 mt-4 px-7 flex items-center">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="validateForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                    <i class="fas fa-save text-xl mr-2"></i>
                    {{ __('Save') }}
                </button>
                <a href="{{ route('obligations.index') }}" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times text-xl mr-2"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6">
            <h2 class="text-lg font-semibold text-red-600 mb-4">Confirm Deletion</h2>
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-6">
                Are you sure you want to delete this row? This action cannot be undone.
            </p>
            <div class="flex justify-end gap-2">
                <button id="confirmDeleteBtn" class="mr-1 text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1.5 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                    <i class="fas fa-trash mr-1 -ml-1"></i> Delete
                </button>
                <button id="cancelDeleteBtn" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-3 py-1.5 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times mr-1 -ml-1"></i> Cancel
                </button>
            </div>
        </div>
    </div>


    <script>
        //Checks if an input has a value and adjusts the text color accordingly
        document.addEventListener("DOMContentLoaded", function() {
            const elements = document.querySelectorAll("input, select");

            elements.forEach(element => {
                updateTextColor(element); // Check initial values

                element.addEventListener("input", function() {
                    updateTextColor(this);
                });

                element.addEventListener("change", function() {
                    updateTextColor(this);
                });

                element.addEventListener("focus", function() {
                    updateTextColor(this);
                });
            });

            // Handle autofill values after a short delay
            setTimeout(() => {
                elements.forEach(updateTextColor);
            }, 100);

            function updateTextColor(element) {
                if (element.value.trim() !== "") {
                    element.classList.remove("text-gray-500");
                    element.classList.add("text-gray-900", "dark:text-gray-100");
                } else {
                    element.classList.remove("text-gray-900", "dark:text-gray-100");
                }
            }

            // Set office_allotment_class_id based on the current value of office_allotment_class input
            const officeInput = document.getElementById("office_allotment_class");
            const officeIdInput = document.getElementById("office_allotment_class_id");

            if (officeInput && officeIdInput && officeInput.value.trim() !== "") {
                const matched = officeAllotmentClasses.find(item => item.name === officeInput.value.trim());
                if (matched) {
                    officeIdInput.value = matched.id;
                    console.log('Auto-matched Office Allotment Class ID:', matched.id);
                }
            }
            // Filter account codes based on the selected office_allotment_class_id
            const tableBody = document.querySelector("#programs_table tbody");

            tableBody.addEventListener("input", function(event) {
                if (event.target && event.target.name === "account_code[]") {
                    filterAccountCodes(event.target);
                }
            });

            tableBody.addEventListener("input", function(event) {
                if (event.target && event.target.name === "amount_of_obligation[]") {
                    validateAmount(event.target);
                    calculateTotalObligation();
                }
            });

            // Automatically filter and populate fields for pre-filled account codes
            const accountCodeInputs = document.querySelectorAll('[name="account_code[]"]');
            accountCodeInputs.forEach(input => {
                if (input.value.trim() !== "") {
                    filterAccountCodes(input);
                }
            });

            // Retrieve the pre-filled account code (e.g., from a hidden input or data attribute)
            const accountCode = document.getElementById('account_code');

            if (accountCode) {
                // Populate fields based on the account code
                populateFields(accountCode);
            } else {
                console.log('No account code provided.');
            }

            tableBody.addEventListener("input", function(event) {
                if (event.target && event.target.name === "amount_of_obligation[]") {
                    validateAmount(event.target);
                    calculateTotalObligation();
                }
            });

            tableBody.addEventListener("blur", function(event) {
                if (event.target && event.target.name === "amount_of_obligation[]") {
                    formatWithCommas(event.target);
                }
            }, true); // true = useCapture to catch blur bubbling

            // Automatically recalculate the total obligation when an amount field changes
            // Initial total computation from existing input values
            calculateTotalObligation();

            // Add listeners to each amount input to auto-recalculate on change
            document.querySelectorAll('[name="amount_of_obligation[]"]').forEach(input => {
                input.addEventListener('input', () => {
                    // Optional: Format input with commas while typing
                    let val = input.value.replace(/,/g, '');
                    val = parseFloat(val || 0);
                    input.value = val.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    // Recalculate total after formatting
                    calculateTotalObligation();
                });
            });



        });


        // Autocomplete for Office and Allotment Class
        const officeAllotmentClasses = [
            @foreach($office_allotment_classes as $office_allotment_class) {
                id: "{{ $office_allotment_class->id }}",
                name: "{{ $office_allotment_class->office_abbreviation }} - {{ $office_allotment_class->class }}"
            },
            @endforeach
        ];
        // Filter office allotment classes based on input
        function filterOfficeAllotmentClasses() {
            const input = document.getElementById("office_allotment_class");
            const dropdown = document.getElementById("OfficeAllotmentClassDropdown");
            const filter = input.value.toLowerCase();

            dropdown.innerHTML = ""; // Clear previous results

            if (!filter) {
                dropdown.classList.add("hidden");
                return;
            }

            const filteredClasses = officeAllotmentClasses.filter(item => item.name.toLowerCase().includes(filter));

            if (filteredClasses.length === 0) {
                dropdown.classList.add("hidden");
                return;
            }

            filteredClasses.forEach(item => {
                const option = document.createElement("div");
                option.className = "p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer";
                option.innerHTML = `${item.name}`;
                option.onclick = function() {
                    input.value = item.name;
                    document.getElementById("office_allotment_class_id").value = item.id; // Set the hidden input value
                    console.log('Selected Office Allotment Class ID:', item.id);
                    console.log('Selected Office Allotment Class Name:', item.name);
                    dropdown.classList.add("hidden");

                    // Reset all account code, description, program, balance, and amount fields in the programs table
                    const tableBody = document.querySelector('#programs_table tbody');
                    if (tableBody) {
                        tableBody.querySelectorAll('tr').forEach(row => {
                            const accountCode = row.querySelector('[name="account_code[]"]');
                            const description = row.querySelector('[name="description[]"]');
                            const program = row.querySelector('[name="programs[]"]');
                            const balance = row.querySelector('[name="balance_from_allotment[]"]');
                            if (accountCode) accountCode.value = '';
                            if (description) description.value = '';
                            if (program) program.value = '';
                            if (balance) balance.value = '';
                        });
                    }
                };
                dropdown.appendChild(option);
            });

            dropdown.classList.remove("hidden");
        }

        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("OfficeAllotmentClassDropdown");
            if (!event.target.closest("#office_allotment_class")) {
                dropdown.classList.add("hidden");
            }
        });

        // List of appropriations passed from the backend
        const appropriations = @json($appropriations);


        function filterAccountCodes(inputElement) {
            const officeAllotmentClassId = document.getElementById('office_allotment_class_id').value;
            const row = inputElement.closest('td');
            const dropdown = row.querySelector('.account-code-dropdown');
            const filter = inputElement.value.trim().toLowerCase();

            // If no input or no office allotment class ID, hide dropdown and exit
            if (!filter || !officeAllotmentClassId) {
                dropdown.classList.add('hidden');
                return;
            }

            // Helper function to strip extensions (anything after the first space)
            function stripExtension(accountCode) {
                return accountCode.split(' ')[0].toLowerCase(); // Get everything before the first space
            }

            const normalizedCode = inputElement.value.trim().toLowerCase().split(' ')[0]; // Normalize the input code

            // Find exact match for pre-filled account code (without extension)
            const exactMatch = appropriations.find(item =>
                item.office_allotment_class_id == officeAllotmentClassId &&
                item.account_code.toLowerCase().split(' ')[0] === normalizedCode
            );

            // If exact match found, populate fields and hide dropdown
            if (exactMatch) {
                populateFields(inputElement, exactMatch);
                dropdown.classList.add('hidden');
                return;
            }

            // Filter codes based on input, considering only the main part (without extensions)
            const filteredCodes = appropriations.filter(item =>
                item.office_allotment_class_id == officeAllotmentClassId &&
                stripExtension(item.account_code).includes(stripExtension(filter))
            );

            // If no matches found, hide dropdown
            if (filteredCodes.length === 0) {
                dropdown.classList.add('hidden');
                return;
            }

            // Populate dropdown with filtered codes
            dropdown.innerHTML = ''; // Clear previous results
            filteredCodes.forEach((item, index) => {
                const option = document.createElement('div');
                option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-300 dark:border-gray-700';
                option.innerHTML = `
            <strong>${item.account_code}</strong><br>
            <span class="text-gray-500 dark:text-gray-400">${item.description || 'No description'}</span><br>
            <span class="text-gray-500 dark:text-gray-400">${item.programs || 'No program'}</span>
        `;
                option.onmousedown = function() {
                    inputElement.value = item.account_code;
                    populateFields(inputElement, item);
                    dropdown.classList.add('hidden');
                };
                dropdown.appendChild(option);
            });

            dropdown.classList.remove('hidden');
        }


        // Function to populate fields based on the selected account code
        function populateFields(inputElement, item) {
            const row = inputElement.closest('tr');
            const descriptionField = row.querySelector('[name="description[]"]');
            const programField = row.querySelector('[name="programs[]"]');
            const balanceField = row.querySelector('[name="balance_from_allotment[]"]');
            console.log("Matched item:", item);
            console.log("Used office_allotment_class_id:", item.office_allotment_class_id);
            console.log("Current input office_allotment_class_id:", document.getElementById('office_allotment_class_id').value);

            // Populate description field if available
            if (descriptionField) {
                descriptionField.value = item.description ? item.description.trim() : '';
            }

            // Populate program field based on the selected account code
            if (programField) {
                // Only populate the program field if a valid value exists
                programField.value = item.programs && item.programs.trim() !== '' ? item.programs.trim() : ''; // Empty if no value
                console.log("Program value: ", item.programs);
            }

            // Populate balance field based on the selected account code
            if (balanceField) {
                const balance = parseFloat(item.balance || 0);
                const formatted = balance.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                console.log("Setting balance:", balance, "Formatted:", formatted);
                balanceField.value = formatted;
            } else {
                console.log("❌ balanceField not found for row", row);
            }
        }
        // Function to initialize the form by checking for existing account codes
        function initializeForm() {
            const officeAllotmentClassId = document.getElementById('office_allotment_class_id').value;

            document.querySelectorAll('[name="account_code[]"]').forEach(inputElement => {
                const accountCode = inputElement.value.trim();

                if (accountCode) {
                    let item;

                    // If the code has a space, it has a suffix → match full
                    if (accountCode.includes(' ')) {
                        item = appropriations.find(item =>
                            item.office_allotment_class_id == officeAllotmentClassId &&
                            item.account_code.toLowerCase() === accountCode.toLowerCase()
                        );
                    } else {
                        // No suffix → match only exact base code (no suffixes)
                        item = appropriations.find(item =>
                            item.office_allotment_class_id == officeAllotmentClassId &&
                            item.account_code.toLowerCase().split(' ')[0] === accountCode.toLowerCase()
                        );
                    }

                    if (item) {
                        populateFields(inputElement, item);
                    } else {
                        console.log(`No match found for account code: ${accountCode} (Office ID: ${officeAllotmentClassId})`);
                    }
                }
            });
        }

        // Initialize the form on page load
        document.addEventListener('DOMContentLoaded', initializeForm);

        // Calculate and populate the balance for the selected account code
        function calculateBalance(inputElement, item) {
            const row = inputElement.closest('tr');
            const balanceField = row.querySelector('[name="balance_from_allotment[]"]');
            const balance = parseFloat(item.balance || 0);
            const formattedBalance = balance.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            if (balanceField) balanceField.value = formattedBalance;
            console.log('Balance set to:', formattedBalance);
        }

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(event) {
            document.querySelectorAll('[name="account_code[]"]').forEach(input => {
                const row = input.closest('td');
                const dropdown = row.querySelector('.account-code-dropdown');
                if (!input.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        function validateAmount(inputElement) {
            const row = inputElement.closest('tr');
            const balanceField = row.querySelector('[name="balance_from_allotment[]"]');

            if (balanceField) {
                const maxBalance = parseFloat((balanceField.value || '0').replace(/,/g, ''));
                let currentValue = parseFloat((inputElement.value || '0').replace(/,/g, ''));

                // Clamp to max balance
                if (currentValue > maxBalance) {
                    currentValue = maxBalance;
                }

                // Only update value without formatting to prevent input disruption
                inputElement.value = currentValue || '';
            }
        }
        // Format input value with commas
        function formatWithCommas(inputElement) {
            const raw = parseFloat((inputElement.value || '0').replace(/,/g, ''));
            if (!isNaN(raw)) {
                inputElement.value = raw.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }

        function calculateTotalObligation() {
            let total = 0;

            const amountFields = document.querySelectorAll('[name="amount_of_obligation[]"]');

            amountFields.forEach(field => {
                const rawValue = field.value.replace(/,/g, '');
                const numericValue = parseFloat(rawValue || 0);
                total += numericValue;
            });

            const totalObligationElement = document.getElementById('totalObligation');
            if (totalObligationElement) {
                totalObligationElement.textContent = total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }


        // Recalculate the total obligation when a row is added
        function addRow() {
            const tableBody = document.querySelector('#programs_table tbody');
            const lastRow = tableBody.querySelector('tr:last-child');
            const newRow = lastRow.cloneNode(true);

            // Clear input values in the cloned row
            newRow.querySelectorAll('input, textarea').forEach(input => {
                input.value = '';
            });

            // Reattach event listeners to the new row for amount_of_obligation
            const amountInput = newRow.querySelector('[name="amount_of_obligation[]"]');
            if (amountInput) {
                amountInput.addEventListener('input', function() {
                    calculateTotalObligation();
                });
            }

            // Append the cloned row to the table body
            tableBody.appendChild(newRow);

            // Recalculate the total obligation
            calculateTotalObligation();
        }

        // Recalculate the total obligation when a row is deleted
        /* function deleteRow(button) {
            const row = button.closest('tr');
            const tableBody = row.parentNode;
            const messageDiv = document.getElementById('tableMessage');

            if (confirm("⚠️ Confirm Deletion\n\nAre you sure you want to delete this row? This action cannot be undone.")) {
                if (tableBody.rows.length > 1) {
                    tableBody.removeChild(row);
                    messageDiv.classList.add('hidden');
                } else {
                    messageDiv.textContent = "At least one row must remain in the table.";
                    messageDiv.classList.remove('hidden');
                }

                calculateTotalObligation(); // Recalculate after deletion
            }
        } */
        let rowToDelete = null;

        function deleteRow(button) {
            rowToDelete = button.closest('tr');
            document.getElementById('deleteConfirmModal').classList.remove('hidden');
        }

        // Confirm button logic
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const tableBody = rowToDelete.parentNode;
            const messageDiv = document.getElementById('tableMessage');

            if (tableBody.rows.length > 1) {
                tableBody.removeChild(rowToDelete);
                messageDiv.classList.add('hidden');
            } else {
                messageDiv.textContent = "At least one row must remain in the table.";
                messageDiv.classList.remove('hidden');
            }

            rowToDelete = null;
            document.getElementById('deleteConfirmModal').classList.add('hidden');
            calculateTotalObligation();
        });

        // Cancel button logic
        document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
            rowToDelete = null;
            document.getElementById('deleteConfirmModal').classList.add('hidden');
        });

        // Validate the form before submission
        function validateForm() {
            const form = document.getElementById('editObligationsForm');
            let isValid = true;

            // Clear previous error messages
            document.querySelectorAll('.text-red-500').forEach(error => error.textContent = '');

            // Validate Office and Allotment Class
            const officeAllotmentClass = document.getElementById('office_allotment_class');
            const officeAllotmentClassId = document.getElementById('office_allotment_class_id');
            if (!officeAllotmentClass.value.trim() || !officeAllotmentClassId.value.trim()) {
                document.getElementById('OfficeAllotmentClassError').textContent = 'Office and Allotment Class is required.';
                isValid = false;
            }

            // Validate Date
            const obrDate = document.getElementById('obr_date');
            if (!obrDate.value.trim()) {
                obrDate.classList.add('border-red-500');
                obrDate.classList.remove('border-gray-300');
                isValid = false;
            } else {
                obrDate.classList.remove('border-red-500');
                obrDate.classList.add('border-gray-300');
            }

            // Validate Obligation Type
            const obrType = document.getElementById('obr_type');
            if (!obrType.value.trim()) {
                document.getElementById('obrTypeError').textContent = 'Obligation Type is required.';
                isValid = false;
            }

            // Validate OBR Number
            const obrNo = document.getElementById('obr_no');
            if (!obrNo.value.trim()) {
                obrNo.classList.add('border-red-500');
                obrNo.classList.remove('border-gray-300');
                isValid = false;
            } else {
                obrNo.classList.remove('border-red-500');
                obrNo.classList.add('border-gray-300');
            }

            // Validate Particulars
            const particulars = document.getElementById('particulars');
            if (!particulars.value.trim()) {
                document.getElementById('particularsError').textContent = 'Particulars field is required.';
                isValid = false;
            } else {
                particulars.classList.remove('border-red-500');
                particulars.classList.add('border-gray-300');
            }

            // Validate at least one row in the Programs Table
            const tableBody = document.querySelector('#programs_table tbody');
            if (tableBody.rows.length === 0) {
                const tableMessage = document.getElementById('tableMessage');
                tableMessage.textContent = 'At least one row is required in the table.';
                tableMessage.classList.remove('hidden');
                isValid = false;
            }

            // Validate Amount of Obligation
            const amountFields = document.querySelectorAll('[name="amount_of_obligation[]"]');
            amountFields.forEach((field, index) => {
                const rawValue = field.value.replace(/,/g, '');
                const value = parseFloat(rawValue || 0);
                if (value <= 0) {
                    field.classList.add('border-red-500');
                    field.classList.remove('border-gray-300');
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'text-red-500 text-xs mt-1';
                    errorMessage.textContent = `Row ${index + 1}: Amount of Obligation must be greater than 0.`;
                    field.parentNode.appendChild(errorMessage);
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                    field.classList.add('border-gray-300');
                }
            });

            // Validate if the total balance from allotment is exhausted
            const balanceFields = document.querySelectorAll('[name="balance_from_allotment[]"]');
            let totalBalance = 0;
            balanceFields.forEach(field => {
                const value = parseFloat((field.value || '0').replace(/,/g, ''));
                totalBalance += value;
            });

            if (totalBalance === 0) {
                const tableMessage = document.getElementById('tableMessage');
                tableMessage.textContent = 'The Balance from Allotment has been exhausted.';
                tableMessage.classList.remove('hidden');
                isValid = false;
            }

            // If valid, strip commas and submit
            if (isValid) {
                amountFields.forEach(field => {
                    field.value = field.value.replace(/,/g, '');
                });

                form.submit();
            }
        }
    </script>
</x-app-layout>