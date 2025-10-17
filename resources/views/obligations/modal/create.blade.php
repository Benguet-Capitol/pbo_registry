<!-- Create Obligations Modal -->
<form id="createObligationsForm" method="POST" action="{{ route('obligations.store') }}">
    @csrf
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
    <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <div id="createModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Create Obligation') }}
                    </h3>
                    <button type="button" onclick="closeCreateModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
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
                                        <x-form.input withicon type='date' name="obr_date" autocomplete="off" id="obr_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="obr_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('OBR No.')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon type='text' name="obr_no" autocomplete="off" id="obr_no" placeholder="{{ __('OBR No.') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Obligation Type -->
                            <div class="sm:col-span-3">
                                <x-form.label for="obr_type" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Obligation Type')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-arrow-up-right-dots"></i>
                                        </x-slot>
                                        <x-form.select withicon id="obr_type" class="block w-full" type="text" name="obr_type" placeholder="{{ __('Obligation Type') }}">
                                            <option value="">{{ __('Select Obligation Type') }}</option>
                                            <option value="Regular">{{ __('Regular') }}</option>
                                            <option value="Purchase Request">{{ __('Purchase Request') }}</option>
                                            <option value="Project/Contract">{{ __('Project/Contract') }}</option>
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
                                        <x-form.textarea withicon name="particulars" autocomplete="off" id="particulars" placeholder="{{ __('Particulars') }}" :value="old('particulars')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.textarea withicon name="remarks" autocomplete="off" id="remarks" placeholder="{{ __('Remarks') }}" :value="old('remarks')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
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
                                            <tr>
                                                <td class="px-1 py-2">
                                                    <x-form.input
                                                        name="account_code[]"
                                                        id="account_code"
                                                        placeholder="{{ __('Account Code') }}"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                        oninput="filterAccountCodes(this)"
                                                        autocomplete="off" />
                                                    <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="AccountCodeDropdown">
                                                        <!-- Suggestions will appear here -->
                                                    </div>
                                                </td>
                                                <td class="px-1 py-2">
                                                    <x-form.textarea
                                                        name="description[]"
                                                        placeholder="{{ __('Description') }}"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                        autocomplete="off"></x-form.textarea>
                                                </td>
                                                <td class="px-1 py-2">
                                                    <x-form.textarea
                                                        name="programs[]"
                                                        placeholder="{{ __('Program') }}"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                        autocomplete="off"></x-form.textarea>
                                                </td>
                                                <td class="px-1 py-2">
                                                    <x-form.input
                                                        type="text"
                                                        name="balance_from_allotment[]"
                                                        oninput="formatCurrency(this)"
                                                        placeholder="{{ __('Balance') }}"
                                                        autocomplete="off"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                        readonly />
                                                </td>
                                                <td class="px-1 py-2">
                                                    <x-form.input
                                                        type="text"
                                                        name="amount_of_obligation[]"
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

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center p-4 flex border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save mr-2 ml-1"></i> {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times mr-2 ml-1"></i> {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

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
    //Open Create Modal
    function openCreateModal() {
        closeAllDropdowns();
        document.getElementById('createModal').classList.remove('hidden');
    }
    //Close Create Modal
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    // Autocomplete for Office and Allotment Class
    const officeAllotmentClasses = [
        @foreach($office_allotment_classes as $office_allotment_class) {
            id: "{{ $office_allotment_class->id }}",
            name: "{{ $office_allotment_class->office_abbreviation }} - {{ $office_allotment_class->class }}",
            class: "{{ $office_allotment_class->class }}", 
            fund: "{{ $office_allotment_class->fund ?? 'General Fund' }}"
        }, // Ensure `fund` is in your DB query
        @endforeach
    ];

    const allowedObligationTypes = {
        'PS': ['Regular'],
        'MOOE': ['Regular', 'Purchase Request'],
        'CO': ['Purchase Request', 'Project/Contract'],
        'CCO': ['Purchase Request', 'Project/Contract']
    };


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
                document.getElementById("office_allotment_class_id").value = item.id;
                // Reset all account code fields when a new OfficeAllotmentClass is selected
                document.querySelectorAll('[name="account_code[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="description[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="programs[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="balance_from_allotment[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="amount_of_obligation[]"]').forEach(field => field.value = '');
                generateObrNumber(item.fund); // Pass fund name, not prefix
                // Restrict Obligation Type based on detected class
                restrictObligationType(item.class);
                // Auto-select "Regular" if PS
                if (item.class === 'PS') {
                    const obrTypeSelect = document.getElementById("obr_type");
                    obrTypeSelect.value = 'Regular';
                }
                dropdown.classList.add("hidden");
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

    // Function to restrict obligation type options dynamically
    function restrictObligationType(allotmentClass) {
        const obrTypeSelect = document.getElementById("obr_type");

        obrTypeSelect.innerHTML = '<option value="">Select Obligation Type</option>'; // reset

        if (allowedObligationTypes[allotmentClass]) {
            allowedObligationTypes[allotmentClass].forEach(type => {
                const option = document.createElement("option");
                option.value = type;
                option.textContent = type;
                obrTypeSelect.appendChild(option);
            });
        }
    }

    // Generate the OBR number in the format 00000-mm-yy-000
    function generateObrNumber(fund) {
        const obrNoField = document.getElementById('obr_no');
        const date = new Date();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Get month (01-12)
        const year = String(date.getFullYear()).slice(-2); // Last two digits

        const fundToSequence = {
            'General Fund': '100',
            'Special Education Fund': '200',
            'Benguet General Hospital Economic Enterprise': '109',
            'Provincial Development Fund': '107'
        };

        const sequence = fundToSequence[fund] || '000'; // Default to '000' if fund is unknown

        const obrNumber = `${sequence}-${year}-${month}-`;
        obrNoField.value = obrNumber;
    }

    // Call the function to set the initial value
    document.addEventListener('DOMContentLoaded', generateObrNumber);

    // List of appropriations passed from the backend
    const appropriations = [
        @foreach($appropriations as $appropriation) {
            id: "{{ $appropriation->id }}",
            account_code: "{{ $appropriation->account_code }}",
            program: "{{ $appropriation->programs }}",
            description: "{{ $appropriation->description }}",
            office_allotment_class_id: "{{ $appropriation->office_allotment_class_id }}",
            balance: "{{ $appropriation->balance }}" // Include the calculated balance
        },
        @endforeach
    ];

    // Filter account codes and display suggestions with description and program
    function filterAccountCodes(inputElement) {
        const officeAllotmentClassId = document.getElementById('office_allotment_class_id').value; // Get the selected office_allotment_class_id
        console.log('Appropriations Data:', appropriations);
        console.log('Selected Office Allotment Class ID:', officeAllotmentClassId);
        const dropdown = inputElement.nextElementSibling; // Get the dropdown element
        const filter = inputElement.value.toLowerCase();

        dropdown.innerHTML = ''; // Clear previous results

        if (!filter || !officeAllotmentClassId) {
            dropdown.classList.add('hidden');
            return;
        }

        // Filter account codes based on the input and matching office_allotment_class_id
        const filteredCodes = appropriations.filter(item =>
            item.office_allotment_class_id === officeAllotmentClassId && // Match office_allotment_class_id
            item.account_code.toLowerCase().includes(filter) // Match account code
        );

        console.log('Filtered Account Codes:', filteredCodes);

        if (filteredCodes.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }

        // Populate the dropdown with filtered account codes, descriptions, and programs
        filteredCodes.forEach(item => {
            const option = document.createElement('div');
            option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-300 dark:border-gray-700';
            option.innerHTML = `
            <strong>${item.account_code}</strong><br>
            <span class="text-gray-700 dark:text-gray-400">${item.description || 'No description'}</span><br>
            <span class="text-gray-700 dark:text-gray-400">${item.program || 'No program'}</span>
        `;
            option.onclick = function() {
                inputElement.value = item.account_code; // Set the selected value
                populateFields(inputElement, item); // Populate related fields
                calculateBalance(inputElement, item); // Calculate and populate the balance
                dropdown.classList.add('hidden');
            };
            dropdown.appendChild(option);
        });

        dropdown.classList.remove('hidden');
    }

    // Populate related fields (description and program) based on the selected account code
    function populateFields(inputElement, item) {
        const row = inputElement.closest('tr'); // Get the current row
        const programField = row.querySelector('[name="programs[]"]');
        const descriptionField = row.querySelector('[name="description[]"]');

        // Populate the program and description fields
        if (programField) programField.value = item.program ? item.program.trim() : '';
        if (descriptionField) descriptionField.value = item.description ? item.description.trim() : '';
    }

    // Calculate and populate the balance for the selected account code
    function calculateBalance(inputElement, item) {
        const row = inputElement.closest('tr'); // Get the current row
        const balanceField = row.querySelector('[name="balance_from_allotment[]"]');

        // Use the pre-calculated balance from the appropriations data
        const balance = parseFloat(item.balance || 0);

        // Format balance with commas and two decimal places for display
        const formattedBalance = balance.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Populate the balance field
        if (balanceField) balanceField.value = formattedBalance;
    }

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('#AccountCodeDropdown');
        dropdowns.forEach(dropdown => {
            if (!event.target.closest('[name="account_code[]"]')) {
                dropdown.classList.add('hidden');
            }
        });
    });

    function validateAmount(inputElement) {
        const row = inputElement.closest('tr'); // Get the current row
        const balanceField = row.querySelector('[name="balance_from_allotment[]"]'); // Get the balance field

        if (balanceField) {
            const maxBalance = parseFloat((balanceField.value || 0).replace(/,/g, ''));; // Get the balance value
            const currentValue = parseFloat((inputElement.value || 0).replace(/,/g, ''));; // Get the current input value

            // If the input value exceeds the balance, reset it to the balance
            if (currentValue > maxBalance) {
                inputElement.value = maxBalance.toFixed(2);
            }
        }
    }

    function calculateTotalObligation() {
        const amountFields = document.querySelectorAll('[name="amount_of_obligation[]"]'); // Select all amount fields
        let total = 0;

        amountFields.forEach(field => {
            const value = parseFloat(field.value.replace(/,/g, '') || 0); // Remove commas and parse as float
            total += value; // Add to total
        });

        // Update the total obligation display
        const totalObligationElement = document.getElementById('totalObligation');
        totalObligationElement.textContent = total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function addRow() {
        const tableBody = document.querySelector('#programs_table tbody'); // Get the table body
        const lastRow = tableBody.querySelector('tr:last-child'); // Get the last row in the table
        const newRow = lastRow.cloneNode(true); // Clone the last row

        // Clear the input values in the cloned row
        newRow.querySelectorAll('input, textarea').forEach(input => {
            input.value = '';
        });

        // Append the cloned row to the table body
        tableBody.appendChild(newRow);

        // Recalculate the total obligation
        calculateTotalObligation();
    }

    /* function deleteRow(button) {
        const row = button.closest('tr'); // Get the row containing the button
        const tableBody = row.parentNode; // Get the table body
        const messageDiv = document.getElementById('tableMessage'); // Get the message placeholder

        // Ensure at least one row remains in the table
        if (tableBody.rows.length > 1) {
            tableBody.removeChild(row); // Remove the row
            messageDiv.classList.add('hidden'); // Hide the message if it was visible
        } else {
            messageDiv.textContent = "At least one row must remain in the table."; // Set the message
            messageDiv.classList.remove('hidden'); // Show the message
        }

        // Recalculate the total obligation
        calculateTotalObligation();
    } */
    //Delete Row Functionality
    let rowToDelete = null;

    function deleteRow(button) {
        rowToDelete = button.closest('tr'); // Store the row for deletion
        document.getElementById('deleteConfirmModal').classList.remove('hidden'); // Show the modal
    }

    // Confirm delete
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

        calculateTotalObligation(); // Recompute total
    });

    // Cancel delete
    document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
        rowToDelete = null;
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    });

    // Update Text Color Based on Input
    document.addEventListener("DOMContentLoaded", function() {
        const fields = document.querySelectorAll("input, select, textarea"); 

        fields.forEach(element => {
            updateTextColor(element);

            element.addEventListener("input", function() {
                updateTextColor(this);
            });

            if (element.tagName === "SELECT") {
                element.addEventListener("change", function() {
                    updateTextColor(this);
                });
            }
        });

    function updateTextColor(element) {
        if (element.value.trim() !== "") {
            element.classList.remove("text-gray-500");
            element.classList.add("text-gray-900", "dark:text-gray-100");
        } else {
            element.classList.remove("text-gray-900", "dark:text-gray-100");
            element.classList.add("text-gray-500");
        }

        if ((element.tagName === "INPUT" || element.tagName === "TEXTAREA") && element.hasAttribute("readonly")) {
            element.classList.add("text-gray-900", "dark:text-gray-400");
        }

        if (element.tagName === "SELECT" && element.disabled) {
            element.classList.add("text-gray-700", "dark:text-gray-500");
        }

        if (element.tagName === "TEXTAREA") {
                if (element.value.trim() !== "") {
                    element.classList.remove("text-gray-400");
                    element.classList.add("text-gray-900", "dark:text-gray-100");
                } else {
                    element.classList.remove("text-gray-900", "dark:text-gray-100");
                    element.classList.add("text-gray-900");
                }
            }
        }
    });

    // Generic keyboard navigation for dropdowns
    function enableDropdownKeyboardNavigation(inputId, dropdownId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        let currentFocus = -1;

        if (!input || !dropdown) return;

        input.addEventListener("keydown", function (e) {
            let items = dropdown.querySelectorAll("div, li");
            if (dropdown.classList.contains("hidden") || items.length === 0) return;

            if (e.key === "ArrowDown") {
                e.preventDefault();
                currentFocus++;
                if (currentFocus >= items.length) currentFocus = 0;
                setActive(items, currentFocus);
            } 
            else if (e.key === "ArrowUp") {
                e.preventDefault();
                currentFocus--;
                if (currentFocus < 0) currentFocus = items.length - 1;
                setActive(items, currentFocus);
            } 
            else if (e.key === "Enter") {
                e.preventDefault();
                if (currentFocus > -1 && items[currentFocus]) {
                    items[currentFocus].click();
                    currentFocus = -1;
                }
            } 
            else if (e.key === "Escape") {
                dropdown.classList.add("hidden");
                currentFocus = -1;
            }
        });

        function setActive(items, index) {
            removeActive(items);
            if (items[index]) {
                items[index].classList.add("active");
                items[index].style.backgroundColor = "#e5e7eb"; // light blue highlight
                items[index].scrollIntoView({ block: "nearest" });
            }
        }

        function removeActive(items) {
            items.forEach(item => {
                item.classList.remove("active");
                item.style.backgroundColor = ""; // reset background
                item.style.color = ""; // reset text color
            });
        }

        document.addEventListener("click", function (event) {
            if (!event.target.closest(`#${inputId}`) && !event.target.closest(`#${dropdownId}`)) {
                dropdown.classList.add("hidden");
                currentFocus = -1;
            }
        });
    }

    // Initialize once DOM is loaded
    document.addEventListener("DOMContentLoaded", function() {
        enableDropdownKeyboardNavigation("office_allotment_class", "OfficeAllotmentClassDropdown");
        enableDropdownKeyboardNavigation("account_code", "AccountCodeDropdown");
    });

    function validateForm() {
        const form = document.getElementById('createObligationsForm');
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
        console.log("OBR Type Selected:", obrType.value);

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
            const value = parseFloat(field.value || 0);
            if (value <= 0) {
                field.classList.add('border-red-500');
                field.classList.remove('border-gray-300');
                const errorMessage = document.createElement('div');
                errorMessage.className = 'text-red-500 text-xs mt-1';
                errorMessage.textContent = `Row ${index + 1}: Amount of Obligation must be greater than 0.`;
                errorMessage.style.gridColumn = 'span 2'; // Make error message span two columns
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
        let hasNull = false;

        balanceFields.forEach(field => {
            const rawValue = field.value;
            if (rawValue === null || rawValue === '' || typeof rawValue === 'undefined') {
                hasNull = true;
            } else {
                const value = parseFloat(rawValue);
                totalBalance += value || 0;
            }
        });

        if (!hasNull && totalBalance === 0) {
            const tableMessage = document.getElementById('tableMessage');
            tableMessage.textContent = 'The Balance from Allotment has been exhausted.';
            tableMessage.classList.remove('hidden');
            isValid = false;
        }

        // If the form is valid, submit it
        if (isValid) {
            form.submit();
        }
    }
</script>