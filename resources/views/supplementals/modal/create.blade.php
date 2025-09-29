<!-- Create Supplemental Appropriations / Reversions Modal -->
<form id="createSupplementalsForm" method="POST" action="{{ route('supplementals.store') }}">
    @csrf
    <div id="createSupplementalsModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Create Supplemental Appropriation | Reversion') }}
                    </h3>
                    <button type="button" onclick="closeCreateSupplementalsModal()" class="text-black hover:text-gray-600 dark:text-white">
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
                            <!-- Type -->
                            <div class="sm:col-span-3">
                                <x-form.label for="type" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Type')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-arrow-up-right-dots"></i>
                                        </x-slot>
                                        <x-form.select withicon id="type" class="block w-full" type="text" name="type" placeholder="{{ __('Type') }}">
                                            <option value="Supplemental">{{ __('Supplemental') }}</option>
                                            <option value="Reversion">{{ __('Reversion') }}</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="typeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="supplemental_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="supplemental_date" autocomplete="off" id="supplemental_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="supplemental_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon type='text' name="supplemental_no" autocomplete="off" id="supplemental_no" placeholder="{{ __('Number') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- SB No. -->
                            <div class="sm:col-span-3" id="sbNoField" style="display:none;">
                                <x-form.label for="basis_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('SB No.')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-align-justify"></i>
                                        </x-slot>
                                        <x-form.input withicon name="basis_no" autocomplete="off" id="basis_no" placeholder="{{ __('SB No.') }}" :value="old('basis_no')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="basis_noError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Basis / Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="basis" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Basis / Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.input withicon name="basis" autocomplete="off" id="basis" placeholder="{{ __('Basis / Remarks') }}" :value="old('basis')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="basisError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Programs Table -->
                            <div class="sm:col-span-6">
                                <!-- Message Placeholder -->
                                <div id="tableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                <div class="mt-2 overflow-x-auto">
                                    <table id="programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('Account Code') }}</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('Description') }}</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('Program') }}</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('Balance from Allotment') }}</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('Amount') }}</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ">
                                            <tr>
                                                <td class="px-1 py-2">
                                                    <x-form.input
                                                        name="account_code[]"
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
                                                        autocomplete="off"
                                                        readonly></x-form.textarea>
                                                </td>
                                                <td class="px-1 py-2">
                                                    <x-form.textarea
                                                        name="programs[]"
                                                        placeholder="{{ __('Program') }}"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                        autocomplete="off"
                                                        readonly></x-form.textarea>
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
                                            <tr class="quarters-row">
                                                <td></td>
                                                <td colspan="4" class="px-1 py-2">
                                                    <div class="grid grid-cols-4 gap-2">
                                                        <x-form.input name="quarter_1[]" type="text" min="0" placeholder="1st Quarter" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-xs text-left" />
                                                        <x-form.input name="quarter_2[]" type="text" min="0" placeholder="2nd Quarter" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-xs text-left" />
                                                        <x-form.input name="quarter_3[]" type="text" min="0" placeholder="3rd Quarter" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-xs text-left" />
                                                        <x-form.input name="quarter_4[]" type="text" min="0" placeholder="4th Quarter" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-xs text-left" />
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <!-- Additional rows can be dynamically added using JavaScript -->
                                        </tbody>
                                        <!-- Fixed Total Row -->
                                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <td colspan="4" class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Total') }}
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
                <div class="justify-center items-center p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateSupplementalForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save mr-2 ml-1"></i> {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateSupplementalsModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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
    function openCreateSupplementalsModal() {
        closeAllDropdowns();
        document.getElementById('createSupplementalsModal').classList.remove('hidden');
    }
    //Close Create Modal
    function closeCreateSupplementalsModal() {
        document.getElementById('createSupplementalsModal').classList.add('hidden');
    }

    // Autocomplete for Office and Allotment Class
    const officeAllotmentClasses = [
        @foreach($office_allotment_classes as $office_allotment_class) {
            id: "{{ $office_allotment_class->id }}",
            name: "{{ $office_allotment_class->office_abbreviation }} - {{ $office_allotment_class->class }}",
            fund: "{{ $office_allotment_class->fund ?? 'General Fund' }}"
        }, // Ensure `fund` is in your DB query
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
                document.getElementById("office_allotment_class_id").value = item.id;
                // Reset all account code fields when a new OfficeAllotmentClass is selected
                document.querySelectorAll('[name="account_code[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="description[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="programs[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="balance_from_allotment[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="amount_of_obligation[]"]').forEach(field => field.value = '');
                generateSupplementalNumber(item.fund); // ✅ Pass fund name, not prefix
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


    // Generate the Supplemental number in the format yyyy-mm-office_abbreviation-class and add S or R depending on the type
    function generateSupplementalNumber() {
        const supplementalNoField = document.getElementById('supplemental_no');
        const officeAllotmentClassInput = document.getElementById('office_allotment_class');
        const typeField = document.getElementById('type');
        const date = new Date();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Get month (01-12)
        const year = String(date.getFullYear()).slice(-2); // Last two digits

        // Extract office_abbreviation and class from the input value (format: "ABBR - Class")
        let officeAbbr = '', officeClass = '';
        if (officeAllotmentClassInput && officeAllotmentClassInput.value.includes(' - ')) {
            [officeAbbr, officeClass] = officeAllotmentClassInput.value.split(' - ');
            officeAbbr = officeAbbr.trim();
            officeClass = officeClass.trim();
        }

        // Determine S or R based on type
        let typeSuffix = '';
        if (typeField && typeField.value === 'Supplemental') {
            typeSuffix = 'S';
        } else if (typeField && typeField.value === 'Reversion') {
            typeSuffix = 'R';
        }

        // Build the supplemental number
        var supplementalNumber = year + '-' + month + '-' + officeAbbr + '-' + officeClass + (typeSuffix ? '-' + typeSuffix : '');
        supplementalNoField.value = supplementalNumber;
    }

    // Update number when type changes or office/class changes
    if (document.getElementById('type')) {
        document.getElementById('type').addEventListener('change', function() {
            generateSupplementalNumber();
        });
    }
    if (document.getElementById('office_allotment_class')) {
        document.getElementById('office_allotment_class').addEventListener('input', function() {
            generateSupplementalNumber();
        });
    }

    // Helper to get selected fund from officeAllotmentClasses
    function getSelectedFund() {
        const input = document.getElementById('office_allotment_class');
        const value = input ? input.value : '';
        let fund = '';
        officeAllotmentClasses.forEach(item => {
            if (item.name === value) {
                fund = item.fund;
            }
        });
        return fund;
    }

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
            <span class="text-gray-500 dark:text-gray-400">${item.description || 'No description'}</span><br>
            <span class="text-gray-500 dark:text-gray-400">${item.program || 'No program'}</span>
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
    // Check if selected type is "Reversion"
    const typeElement = document.querySelector('[name="type"]');
    if (!typeElement || typeElement.value !== 'Reversion') {
        return; // Skip validation if not Reversion
    }

    // Proceed with validation
    let value = parseFloat(inputElement.value.replace(/,/g, ''));
    if (isNaN(value) || value <= 0) {
        inputElement.value = '';
        return;
    }

    const row = inputElement.closest('tr');
    const balanceField = row.querySelector('[name="balance_from_allotment[]"]');
    if (balanceField) {
        const maxBalance = parseFloat((balanceField.value || 0).replace(/,/g, ''));
        if (!isNaN(maxBalance) && value > maxBalance) {
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
        const tableBody = document.querySelector('#programs_table tbody');
        // Find all rows in tbody
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        // Find the last main row (the one with the delete button)
        let lastMainRowIdx = -1;
        for (let i = rows.length - 1; i >= 0; i--) {
            if (rows[i].querySelector('button[onclick^="deleteRow"]')) {
                lastMainRowIdx = i;
                break;
            }
        }
        if (lastMainRowIdx === -1 || lastMainRowIdx === rows.length - 1) {
            // Fallback: just clone the last two rows
            lastMainRowIdx = rows.length - 2;
        }
        const mainRow = rows[lastMainRowIdx];
        const quartersRow = rows[lastMainRowIdx + 1];
        // Clone both rows
        const newMainRow = mainRow.cloneNode(true);
        const newQuartersRow = quartersRow.cloneNode(true);
        // Clear all input/textarea values in both rows
        newMainRow.querySelectorAll('input, textarea').forEach(input => {
            input.value = '';
        });
        newQuartersRow.querySelectorAll('input, textarea').forEach(input => {
            input.value = '';
        });
        // Append both rows as a pair
        tableBody.appendChild(newMainRow);
        tableBody.appendChild(newQuartersRow);
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
    let quartersRowToDelete = null;

    function deleteRow(button) {
        rowToDelete = button.closest('tr'); // Main row
        // The quarters row is the next sibling row
        quartersRowToDelete = rowToDelete.nextElementSibling;
        document.getElementById('deleteConfirmModal').classList.remove('hidden'); // Show the modal
    }

    // Confirm delete
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        const tableBody = rowToDelete.parentNode;
        const messageDiv = document.getElementById('tableMessage');

        // Count only main rows (with delete button)
        const mainRows = Array.from(tableBody.querySelectorAll('tr')).filter(row => row.querySelector('button[onclick^="deleteRow"]'));
        if (mainRows.length > 1) {
            tableBody.removeChild(rowToDelete);
            if (quartersRowToDelete && quartersRowToDelete.parentNode === tableBody) {
                tableBody.removeChild(quartersRowToDelete);
            }
            messageDiv.classList.add('hidden');
        } else {
            messageDiv.textContent = "At least one row must remain in the table.";
            messageDiv.classList.remove('hidden');
        }

        rowToDelete = null;
        quartersRowToDelete = null;
        document.getElementById('deleteConfirmModal').classList.add('hidden');

        calculateTotalObligation(); // Recompute total
    });

    // Cancel delete
    document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
        rowToDelete = null;
        quartersRowToDelete = null;
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    });

    document.addEventListener("DOMContentLoaded", function() {
        const inputsAndSelects = document.querySelectorAll("input, select"); // Select both input and select elements

        inputsAndSelects.forEach(element => {
            updateTextColor(element); // Check initial values

            element.addEventListener("input", function() {
                updateTextColor(this);
            });

            // For select fields, listen to the 'change' event
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

            // Apply specific styles for readonly inputs
            if (element.hasAttribute("readonly")) {
                element.classList.add("text-gray-900", "dark:text-gray-400"); // Add styles for readonly inputs
            }

            // Apply specific styles for disabled select fields
            if (element.tagName === "SELECT" && element.disabled) {
                element.classList.add("text-gray-700", "dark:text-gray-500"); // Add styles for disabled selects
            }
        }
    });

    function validateSupplementalForm() {
        const form = document.getElementById('createSupplementalsForm');
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
        const supplementalDate = document.getElementById('supplemental_date');
        if (!supplementalDate.value.trim()) {
            supplementalDate.classList.add('border-red-500');
            supplementalDate.classList.remove('border-gray-300');
            isValid = false;
        } else {
            supplementalDate.classList.remove('border-red-500');
            supplementalDate.classList.add('border-gray-300');
        }

        // Validate  Type
        const type = document.getElementById('type');
        if (!type.value.trim()) {
            document.getElementById('typeError').textContent = 'Type is required.';
            isValid = false;
        }
        console.log("Type Selected:", type.value);

        // Validate Supplemental Number
        const supplementalNo = document.getElementById('supplemental_no');
        if (!supplementalNo.value.trim()) {
            supplementalNo.classList.add('border-red-500');
            supplementalNo.classList.remove('border-gray-300');
            isValid = false;
        } else {
            supplementalNo.classList.remove('border-red-500');
            supplementalNo.classList.add('border-gray-300');
        }

        // Validate Basis / Remarks
        const basis = document.getElementById('basis');
        if (!basis.value.trim()) {
            document.getElementById('basisError').textContent = 'Basis / Remarks is required.';
            isValid = false;
        } else {
            basis.classList.remove('border-red-500');
            basis.classList.add('border-gray-300');
        }

        // Validate at least one row in the Programs Table
        const tableBody = document.querySelector('#programs_table tbody');
        if (tableBody.rows.length === 0) {
            const tableMessage = document.getElementById('tableMessage');
            tableMessage.textContent = 'At least one row is required in the table.';
            tableMessage.classList.remove('hidden');
            isValid = false;
        }

        // Validate Amount
        const amountFields = document.querySelectorAll('[name="amount_of_obligation[]"]');
        amountFields.forEach((field, index) => {
            const value = parseFloat(field.value);
            if (isNaN(value) || value <= 0) {
                field.classList.add('border-red-500');
                field.classList.remove('border-gray-300');
                const errorMessage = document.createElement('div');
                errorMessage.className = 'text-red-500 text-xs mt-1';
                errorMessage.textContent = `Row ${index + 1}: Amount must be a number greater than 0.`;
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

        // Validate quarter fields if type is Supplemental
        const typeField = document.getElementById('type');
        if (typeField.value === 'Supplemental') {
            const quartersRows = document.querySelectorAll('.quarters-row');
            quartersRows.forEach((row, rowIndex) => {
                const mainRow = row.previousElementSibling;
                const amountInput = mainRow ? mainRow.querySelector('[name="amount_of_obligation[]"]') : null;
                const maxAmount = amountInput ? parseFloat(amountInput.value.replace(/,/g, '')) || 0 : 0;
                let sum = 0;
                row.querySelectorAll('input[name^="quarter_"]').forEach((input, qIndex) => {
                    const value = parseFloat(input.value);
                    sum += value || 0;
                    // Remove validation for value > 0; allow empty or zero
                    input.classList.remove('border-red-500');
                    input.classList.add('border-gray-300');
                });
                if (sum > maxAmount) {
                    // Optionally, show a message for sum exceeding amount
                    isValid = false;
                }
            });
        }

        // If the form is valid, submit it
        if (isValid) {
            form.submit();
        }
    }

    function toggleQuartersRows() {
        const type = document.getElementById('type').value;
        const quartersRows = document.querySelectorAll('.quarters-row');
        quartersRows.forEach(row => {
            if (type === 'Supplemental') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    document.getElementById('type').addEventListener('change', toggleQuartersRows);
    document.addEventListener('DOMContentLoaded', toggleQuartersRows);

    function validateQuarterInput(quarterInput) {
        const quartersRow = quarterInput.closest('tr');
        const mainRow = quartersRow.previousElementSibling;
        if (!mainRow) return;
        const amountInput = mainRow.querySelector('[name="amount_of_obligation[]"]');
        if (!amountInput) return;
        const maxAmount = parseFloat(amountInput.value.replace(/,/g, '')) || 0;
        // Get all quarter inputs in this quarters row
        const quarterInputs = quartersRow.querySelectorAll('input[name^="quarter_"]');
        let sum = 0;
        quarterInputs.forEach(input => {
            if (input !== quarterInput) {
                sum += parseFloat(input.value.replace(/,/g, '')) || 0;
            }
        });
        let thisValue = parseFloat(quarterInput.value.replace(/,/g, ''));
        // If not a number or less than or equal to 0, clear
        if (isNaN(thisValue) || thisValue <= 0) {
            quarterInput.value = '';
            return;
        }
        if (sum + thisValue > maxAmount) {
            quarterInput.value = Math.max(0, maxAmount - sum);
        }
    }

    function attachQuarterValidation() {
        document.querySelectorAll('.quarters-row input[name^="quarter_"]').forEach(input => {
            input.removeEventListener('input', quarterInputHandler); // Remove previous handler if any
            input.addEventListener('input', quarterInputHandler);
        });
    }
    function quarterInputHandler(e) {
        validateQuarterInput(e.target);
    }
    // Attach on page load and after adding a row

    document.addEventListener('DOMContentLoaded', attachQuarterValidation);
    // Patch addRow to call attachQuarterValidation after adding rows
    const originalAddRow = addRow;
    addRow = function() {
        originalAddRow();
        attachQuarterValidation();
    };

    // Add JS to toggle SB No. field
    function toggleSbNoField() {
        const type = document.getElementById('type').value;
        const sbNoField = document.getElementById('sbNoField');
        if (type === 'Supplemental') {
            sbNoField.style.display = '';
        } else {
            sbNoField.style.display = 'none';
        }
    }
    document.getElementById('type').addEventListener('change', toggleSbNoField);
    document.addEventListener('DOMContentLoaded', toggleSbNoField);
</script>