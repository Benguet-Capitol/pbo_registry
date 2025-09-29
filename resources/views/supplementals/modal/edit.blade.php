<!-- Edit Supplemental Appropriations / Reversions Modal -->
<form id="editSupplementalForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editSupplementalModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Supplemental Appropriation | Reversion') }}
                    </h3>
                    <button type="button" onclick="closeEditSupplementalModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">

                            <!-- Office and Allotment Class -->
                            <div class="sm:col-span-3 relative">
                                <x-form.label for="edit_office_allotment_class" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-laptop-house"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            type="text"
                                            name="edit_office_allotment_class"
                                            id="edit_office_allotment_class"
                                            placeholder="{{ __('Office and Allotment Class') }}"
                                            class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            oninput="filterOfficeAllotmentClasses()"
                                            autocomplete="off" />
                                    </x-form.input-with-icon-wrapper>
                                    <!-- Hidden input to store the selected ID -->
                                    <input type="hidden" name="edit_office_allotment_class_id" id="edit_office_allotment_class_id" />
                                    <div id="editOfficeAllotmentClassDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                        <!-- Suggestions appear here -->
                                    </div>
                                    <span id="edit_OfficeAllotmentClassError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Type -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_type" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Type')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-arrow-up-right-dots"></i>
                                        </x-slot>
                                        <x-form.select withicon id="edit_type" class="block w-full" type="text" name="edit_type" placeholder="{{ __('Type') }}">
                                            <option value="Supplemental">{{ __('Supplemental') }}</option>
                                            <option value="Reversion">{{ __('Reversion') }}</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_typeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_supplemental_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="edit_supplemental_date" autocomplete="off" id="edit_supplemental_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_supplemental_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon type='text' name="edit_supplemental_no" autocomplete="off" id="edit_supplemental_no" placeholder="{{ __('Number') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- SB No. -->
                            <div class="sm:col-span-3" id="editSbNoField" style="display:none;">
                                <x-form.label for="edit_basis_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('SB No.')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-align-justify"></i>
                                        </x-slot>
                                        <x-form.input withicon name="edit_basis_no" autocomplete="off" id="edit_basis_no" placeholder="{{ __('SB No.') }}" :value="old('basis_no')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_basis_noError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Basis / Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="edit_basis" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Basis / Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.input withicon name="edit_basis" autocomplete="off" id="edit_basis" placeholder="{{ __('Basis / Remarks') }}" :value="old('basis')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_basisError" class="text-red-500 text-sm"></span>
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
                                                        name="edit_account_code"
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
                                                        name="edit_description"
                                                        placeholder="{{ __('Description') }}"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                        autocomplete="off"
                                                        readonly></x-form.textarea>
                                                </td>
                                                <td class="px-1 py-2">
                                                    <x-form.textarea
                                                        name="edit_programs"
                                                        placeholder="{{ __('Program') }}"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                        autocomplete="off"
                                                        readonly></x-form.textarea>
                                                </td>
                                                <td class="px-1 py-2">
                                                    <x-form.input
                                                        type="text"
                                                        name="edit_balance_from_allotment"
                                                        oninput="formatCurrency(this)"
                                                        placeholder="{{ __('Balance') }}"
                                                        autocomplete="off"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                        readonly />
                                                </td>
                                                <td class="px-1 py-2">
                                                    <x-form.input
                                                        type="text"
                                                        name="edit_amount_of_obligation"
                                                        oninput="validateAmount(this); calculateTotalObligation();"
                                                        onblur="calculateTotalObligation();"
                                                        placeholder="{{ __('Amount') }}"
                                                        autocomplete="off"
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                                                </td>
                                            </tr>
                                            <tr class="quarters-row">
                                                <td></td>
                                                <td colspan="4" class="px-1 py-2">
                                                    <div class="grid grid-cols-4 gap-2">
                                                        <x-form.input name="edit_quarter_1" type="text" min="0" placeholder="1st Quarter" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-xs text-left" />
                                                        <x-form.input name="edit_quarter_2" type="text" min="0" placeholder="2nd Quarter" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-xs text-left" />
                                                        <x-form.input name="edit_quarter_3" type="text" min="0" placeholder="3rd Quarter" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-xs text-left" />
                                                        <x-form.input name="edit_quarter_4" type="text" min="0" placeholder="4th Quarter" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-xs text-left" />
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <!-- Additional rows can be dynamically added using JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditSupplementalForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save mr-2 ml-1"></i> {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditSupplementalModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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
    //Open Edit Modal
    function openEditSupplementalModal(supplemental) {
        closeAllDropdowns();

        document.getElementById('editSupplementalForm').action = `/supplementals/${supplemental.id}`;
        
        // Fix: handle object or string for office_allotment_class
        document.getElementById('edit_office_allotment_class').value = supplemental.office_allotment_class?.name || supplemental.office_allotment_class || '';
        document.getElementById('edit_office_allotment_class_id').value = supplemental.office_allotment_classes_id;
        document.getElementById('edit_supplemental_date').value = supplemental.supplemental_date || '';
        document.getElementById('edit_supplemental_no').value = supplemental.supplemental_no || '';
        document.getElementById('edit_basis').value = supplemental.basis || '';
        document.getElementById('edit_type').value = supplemental.type || '';
        document.getElementById('edit_basis_no').value = supplemental.basis_no || '';

        // Get the edit_office_allotment_class object by ID and display its office_abbreviation - class
        if (supplemental.office_allotment_class && typeof supplemental.office_allotment_class === 'object') {
            document.getElementById('edit_office_allotment_class').value = supplemental.office_allotment_class.office_abbreviation + ' - ' + supplemental.office_allotment_class.class;
        }

        // Find the appropriation object by ID
        let Appropriation = appropriations.find(
            appropriation => String(appropriation.id) === String(supplemental.appropriations_id)
        );
        if (Appropriation) {
            document.querySelector('[name="edit_account_code"]').value = Appropriation.account_code || '';
            document.querySelector('[name="edit_description"]').value = Appropriation.description || '';
            document.querySelector('[name="edit_programs"]').value = Appropriation.program || '';
        } else {
            document.querySelector('[name="edit_account_code"]').value = '';
            document.querySelector('[name="edit_description"]').value = '';
            document.querySelector('[name="edit_programs"]').value = '';
        }
        //Populate the balance from allotment
        document.querySelector('[name="edit_balance_from_allotment"]').value = Appropriation.balance || '';
        //Populate the edit amount
        document.querySelector('[name="edit_amount_of_obligation"]').value = supplemental.amount || '';

        //Populate the edit quarters
        document.querySelector('[name="edit_quarter_1"]').value = supplemental.quarter1 || '';
        document.querySelector('[name="edit_quarter_2"]').value = supplemental.quarter2 || '';
        document.querySelector('[name="edit_quarter_3"]').value = supplemental.quarter3 || '';
        document.querySelector('[name="edit_quarter_4"]').value = supplemental.quarter4 || '';

        document.getElementById('editSupplementalModal').classList.remove('hidden');

        // Immediately apply correct UI state for quarters rows and SB No. field
        toggleEditQuartersRows();
        toggleEditSbNoField();
    }
    //Close Create Modal
    function closeEditSupplementalModal() {
        document.getElementById('editSupplementalModal').classList.add('hidden');
    }

    // Autocomplete for Office and Allotment Class in Edit Modal
const editOfficeAllotmentClasses = [
    @foreach($office_allotment_classes as $i => $office_allotment_class)
    {
        id: "{{ $office_allotment_class->id }}",
        name: "{{ $office_allotment_class->office_abbreviation }} - {{ $office_allotment_class->class }}",
        fund: "{{ $office_allotment_class->fund ?? 'General Fund' }}"
    }@if(!$loop->last),@endif
    @endforeach
];
function filterEditOfficeAllotmentClasses() {
    const input = document.getElementById("edit_office_allotment_class");
    const dropdown = document.getElementById("editOfficeAllotmentClassDropdown");
    const filter = input.value.toLowerCase();
    dropdown.innerHTML = "";
    if (!filter) {
        dropdown.classList.add("hidden");
        return;
    }
    const filteredClasses = editOfficeAllotmentClasses.filter(item => item.name.toLowerCase().includes(filter));
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
            document.getElementById("edit_office_allotment_class_id").value = item.id;
            // Reset all account code fields when a new OfficeAllotmentClass is selected
            document.querySelectorAll('[name="edit_account_code"]').forEach(field => field.value = '');
            document.querySelectorAll('[name="edit_description"]').forEach(field => field.value = '');
            document.querySelectorAll('[name="edit_programs"]').forEach(field => field.value = '');
            document.querySelectorAll('[name="edit_balance_from_allotment"]').forEach(field => field.value = '');
            document.querySelectorAll('[name="edit_amount_of_obligation"]').forEach(field => field.value = '');
            // Optionally regenerate number if needed
            // generateEditSupplementalNumber(item.fund);
            dropdown.classList.add("hidden");
        };
        dropdown.appendChild(option);
    });
    dropdown.classList.remove("hidden");
}
document.addEventListener("click", function(event) {
    const dropdown = document.getElementById("editOfficeAllotmentClassDropdown");
    if (!event.target.closest("#edit_office_allotment_class")) {
        dropdown.classList.add("hidden");
    }
});
// Attach to input event
if (document.getElementById('edit_office_allotment_class')) {
    document.getElementById('edit_office_allotment_class').addEventListener('input', filterEditOfficeAllotmentClasses);
}

    function toggleEditQuartersRows() {
        const typeElement = document.getElementById('edit_type');
        if (!typeElement) return;
        const type = typeElement.value;
        const quartersRows = document.querySelectorAll('.quarters-row');
        quartersRows.forEach(row => {
            if (type === 'Supplemental') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    if (document.getElementById('edit_type')) {
        document.getElementById('edit_type').addEventListener('change', toggleEditQuartersRows);
        document.addEventListener('DOMContentLoaded', toggleEditQuartersRows);
    }

    function validateEditQuarterInput(quarterInput) {
        const quartersRow = quarterInput.closest('tr');
        const mainRow = quartersRow.previousElementSibling;
        if (!mainRow) return;
        const amountInput = mainRow.querySelector('[name="edit_amount_of_obligation"]');
        if (!amountInput) return;
        const maxAmount = parseFloat(amountInput.value.replace(/,/g, '')) || 0;
        const quarterInputs = quartersRow.querySelectorAll('input[name^="edit_quarter_"]');
        let sum = 0;
        quarterInputs.forEach(input => {
            if (input !== quarterInput) {
                sum += parseFloat(input.value.replace(/,/g, '')) || 0;
            }
        });
        let thisValue = parseFloat(quarterInput.value.replace(/,/g, ''));
        if (isNaN(thisValue) || thisValue <= 0) {
            quarterInput.value = '';
            return;
        }
        if (sum + thisValue > maxAmount) {
            quarterInput.value = Math.max(0, maxAmount - sum);
        }
    }
    function attachEditQuarterValidation() {
        document.querySelectorAll('.quarters-row input[name^="edit_quarter_"]').forEach(input => {
            input.removeEventListener('input', editQuarterInputHandler);
            input.addEventListener('input', editQuarterInputHandler);
        });
    }
    function editQuarterInputHandler(e) {
        validateEditQuarterInput(e.target);
    }
    document.addEventListener('DOMContentLoaded', attachEditQuarterValidation);
    const originalEditAddRow = addRow;
    addRow = function() {
        originalEditAddRow();
        attachEditQuarterValidation();
    };
    function toggleEditSbNoField() {
        const type = document.getElementById('edit_type').value;
        const sbNoField = document.getElementById('editSbNoField');
        if (type === 'Supplemental') {
            sbNoField.style.display = '';
        } else {
            sbNoField.style.display = 'none';
        }
    }
    if (document.getElementById('edit_type')) {
        document.getElementById('edit_type').addEventListener('change', toggleEditSbNoField);
        document.addEventListener('DOMContentLoaded', toggleEditSbNoField);
    }

// Generate the Supplemental number in the format yyyy-mm-office_abbreviation-class and add S or R depending on the type (Edit Modal)
function generateEditSupplementalNumber() {
    const supplementalNoField = document.getElementById('edit_supplemental_no');
    const officeAllotmentClassInput = document.getElementById('edit_office_allotment_class');
    const typeField = document.getElementById('edit_type');
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
    if (supplementalNoField) supplementalNoField.value = supplementalNumber;
}
// Update number when type changes or office/class changes (Edit Modal)
if (document.getElementById('edit_type')) {
    document.getElementById('edit_type').addEventListener('change', function() {
        generateEditSupplementalNumber();
    });
}
if (document.getElementById('edit_office_allotment_class')) {
    document.getElementById('edit_office_allotment_class').addEventListener('input', function() {
        generateEditSupplementalNumber();
    });
}
// Helper to get selected fund from editOfficeAllotmentClasses (Edit Modal)
function getEditSelectedFund() {
    const input = document.getElementById('edit_office_allotment_class');
    const value = input ? input.value : '';
    let fund = '';
    editOfficeAllotmentClasses.forEach(item => {
        if (item.name === value) {
            fund = item.fund;
        }
    });
    return fund;
}

// List of appropriations passed from the backend (Edit Modal)
const editAppropriations = [
    @foreach($appropriations as $i => $appropriation)
    {
        id: "{{ $appropriation->id }}",
        account_code: "{{ $appropriation->account_code }}",
        program: "{{ $appropriation->programs }}",
        description: "{{ $appropriation->description }}",
        office_allotment_class_id: "{{ $appropriation->office_allotment_class_id }}",
        balance: "{{ $appropriation->balance }}"
    }@if(!$loop->last),@endif
    @endforeach
];
// Filter account codes and display suggestions with description and program (Edit Modal)
function filterEditAccountCodes(inputElement) {
    const officeAllotmentClassId = document.getElementById('edit_office_allotment_class_id').value;
    const dropdown = inputElement.nextElementSibling;
    const filter = inputElement.value.toLowerCase();
    dropdown.innerHTML = '';
    if (!filter || !officeAllotmentClassId) {
        dropdown.classList.add('hidden');
        return;
    }
    const filteredCodes = editAppropriations.filter(item =>
        item.office_allotment_class_id === officeAllotmentClassId &&
        item.account_code.toLowerCase().includes(filter)
    );
    if (filteredCodes.length === 0) {
        dropdown.classList.add('hidden');
        return;
    }
    filteredCodes.forEach(item => {
        const option = document.createElement('div');
        option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-300 dark:border-gray-700';
        option.innerHTML = `
            <strong>${item.account_code}</strong><br>
            <span class="text-gray-500 dark:text-gray-400">${item.description || 'No description'}</span><br>
            <span class="text-gray-500 dark:text-gray-400">${item.program || 'No program'}</span>
        `;
        option.onclick = function() {
            inputElement.value = item.account_code;
            populateEditFields(inputElement, item);
            calculateEditBalance(inputElement, item);
            dropdown.classList.add('hidden');
        };
        dropdown.appendChild(option);
    });
    dropdown.classList.remove('hidden');
}
// Populate related fields (description and program) based on the selected account code (Edit Modal)
function populateEditFields(inputElement, item) {
    const row = inputElement.closest('tr');
    const programField = row.querySelector('[name="edit_programs"]');
    const descriptionField = row.querySelector('[name="edit_description"]');
    if (programField) programField.value = item.program ? item.program.trim() : '';
    if (descriptionField) descriptionField.value = item.description ? item.description.trim() : '';
}
// Calculate and populate the balance for the selected account code (Edit Modal)
function calculateEditBalance(inputElement, item) {
    const row = inputElement.closest('tr');
    const balanceField = row.querySelector('[name="edit_balance_from_allotment"]');
    const balance = parseFloat(item.balance || 0);
    const formattedBalance = balance.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    if (balanceField) balanceField.value = formattedBalance;
}
// Hide dropdown when clicking outside (Edit Modal)
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('#AccountCodeDropdown');
    dropdowns.forEach(dropdown => {
        if (!event.target.closest('[name="edit_account_code"]')) {
            dropdown.classList.add('hidden');
        }
    });
});
// Attach filterEditAccountCodes to edit_account_code inputs (Edit Modal)
document.querySelectorAll('[name="edit_account_code"]').forEach(input => {
    input.addEventListener('input', function() {
        filterEditAccountCodes(this);
    });
});

// Only allow numbers greater than 0 for edit modal
function validateEditAmount(inputElement) {
    let value = parseFloat(inputElement.value.replace(/,/g, ''));
    if (isNaN(value) || value <= 0) {
        inputElement.value = '';
        return;
    }
    const row = inputElement.closest('tr');
    const balanceField = row.querySelector('[name="edit_balance_from_allotment"]');
    if (balanceField) {
        const maxBalance = parseFloat((balanceField.value || 0).replace(/,/g, ''));
        if (!isNaN(maxBalance) && value > maxBalance) {
            inputElement.value = maxBalance.toFixed(2);
        }
    }
}

// Utility to update text color for edit modal fields
function editUpdateTextColor(element) {
    if (element.value && element.value.trim() !== "") {
        element.classList.remove("text-gray-500");
        element.classList.add("text-gray-900", "dark:text-gray-100");
    } else {
        element.classList.remove("text-gray-900", "dark:text-gray-100");
        element.classList.add("text-gray-500");
    }
    // Apply specific styles for readonly inputs
    if (element.hasAttribute("readonly")) {
        element.classList.add("text-gray-900", "dark:text-gray-400");
    }
    // Apply specific styles for disabled select fields
    if (element.tagName === "SELECT" && element.disabled) {
        element.classList.add("text-gray-700", "dark:text-gray-500");
    }
}

// Patch openEditSupplementalModal to update text color after populating fields
const originalOpenEditSupplementalModal = openEditSupplementalModal;
openEditSupplementalModal = function(supplemental) {
    originalOpenEditSupplementalModal(supplemental);
    // Update text color for all relevant fields after values are set
    [
        'edit_basis',
        'edit_office_allotment_class',
        'edit_supplemental_no',
        'edit_type',
        'edit_basis_no'
    ].forEach(function(id) {
        var el = document.getElementById(id) || document.querySelector('[name="'+id+'"]');
        if (el) editUpdateTextColor(el);
    });
    // Update for all table fields
    document.querySelectorAll('[name="edit_account_code"], [name="edit_description"], [name="edit_programs"], [name="edit_balance_from_allotment"], [name="edit_amount_of_obligation"]').forEach(function(el) {
        editUpdateTextColor(el);
    });
    // Update for all quarter fields
    document.querySelectorAll('input[name^="edit_quarter_"]').forEach(function(el) {
        editUpdateTextColor(el);
    });
};

function validateEditSupplementalForm() {
    const form = document.getElementById('editSupplementalForm');
    let isValid = true;

    // Clear previous error messages
    document.querySelectorAll('.text-red-500').forEach(error => error.textContent = '');

    // Validate Office and Allotment Class
    const officeAllotmentClass = document.getElementById('edit_office_allotment_class');
    const officeAllotmentClassId = document.getElementById('edit_office_allotment_class_id');
    if (!officeAllotmentClass.value.trim() || !officeAllotmentClassId.value.trim()) {
        document.getElementById('edit_OfficeAllotmentClassError').textContent = 'Office and Allotment Class is required.';
        isValid = false;
    }

    // Validate Date
    const supplementalDate = document.getElementById('edit_supplemental_date');
    if (!supplementalDate.value.trim()) {
        supplementalDate.classList.add('border-red-500');
        supplementalDate.classList.remove('border-gray-300');
        isValid = false;
    } else {
        supplementalDate.classList.remove('border-red-500');
        supplementalDate.classList.add('border-gray-300');
    }

    // Validate Type
    const type = document.getElementById('edit_type');
    if (!type.value.trim()) {
        document.getElementById('edit_typeError').textContent = 'Type is required.';
        isValid = false;
    }

    // Validate Supplemental Number
    const supplementalNo = document.getElementById('edit_supplemental_no');
    if (!supplementalNo.value.trim()) {
        supplementalNo.classList.add('border-red-500');
        supplementalNo.classList.remove('border-gray-300');
        isValid = false;
    } else {
        supplementalNo.classList.remove('border-red-500');
        supplementalNo.classList.add('border-gray-300');
    }

    // Validate Basis / Remarks
    const basis = document.getElementById('edit_basis');
    if (!basis.value.trim()) {
        document.getElementById('edit_basisError').textContent = 'Basis / Remarks is required.';
        isValid = false;
    } else {
        basis.classList.remove('border-red-500');
        basis.classList.add('border-gray-300');
    }

    // Validate Account Code
    const accountCode = document.querySelector('[name="edit_account_code"]');
    if (!accountCode.value.trim()) {
        accountCode.classList.add('border-red-500');
        accountCode.classList.remove('border-gray-300');
        isValid = false;
    } else {
        accountCode.classList.remove('border-red-500');
        accountCode.classList.add('border-gray-300');
    }

    // Validate Amount
    const amountField = document.querySelector('[name="edit_amount_of_obligation"]');
    const value = parseFloat(amountField.value);
    if (isNaN(value) || value <= 0) {
        amountField.classList.add('border-red-500');
        amountField.classList.remove('border-gray-300');
        isValid = false;
    } else {
        amountField.classList.remove('border-red-500');
        amountField.classList.add('border-gray-300');
    }

    // Validate quarters if type is Supplemental
    if (type.value === 'Supplemental') {
        let sum = 0;
        document.querySelectorAll('input[name^="edit_quarter_"]').forEach(input => {
            const qVal = parseFloat(input.value);
            sum += qVal || 0;
            input.classList.remove('border-red-500');
            input.classList.add('border-gray-300');
        });
        if (sum > value) {
            isValid = false;
        }
    }

    // If the form is valid, submit it
    if (isValid) {
        form.submit();
    }
}
</script>