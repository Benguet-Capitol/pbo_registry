<!-- Edit Obligations Modal -->
<form id="editObligationsForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editObligationsModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Obligation Request') }}
                    </h3>
                    <button type="button" onclick="closeEditObligationsModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" id="obligation_id" value="{{ $obligation->id }}">
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
                                            name="edit_office_allotment_class"
                                            id="edit_office_allotment_class"
                                            placeholder="{{ __('Office and Allotment Class') }}"
                                            class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            oninput="filterEditOfficeAllotmentClasses()"
                                            autocomplete="off" />
                                    </x-form.input-with-icon-wrapper>
                                    <!-- Hidden input to store the selected ID -->
                                    <input type="hidden" name="office_allotment_class_id" id="office_allotment_class_id" />
                                    <div id="EditOfficeAllotmentClassDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
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
                                        <x-form.input withicon type='date' name="edit_obr_date" autocomplete="off" id="edit_obr_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.select withicon id="edit_obr_type" class="block w-full" type="text" name="edit_obr_type" :value="old('obr_type')" placeholder="{{ __('Obligation Type') }}">
                                            <option value="">{{ __('Select Obligation Type') }}</option>
                                            <option value="Regular">{{ __('Regular') }}</option>
                                            <option value="Purchase Request">{{ __('Purchase Request') }}</option>
                                            <option value="Project/Contract">{{ __('Project/Contract') }}</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="obrTypeError" class="text-red-500 text-sm"></span>
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
                                        <x-form.input withicon type='text' name="edit_obr_no" autocomplete="off" id="edit_obr_no" placeholder="{{ __('Number') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
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
                                        <x-form.input withicon name="edit_particulars" autocomplete="off" id="edit_particulars" placeholder="{{ __('Particulars') }}" :value="old('particulars')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.input withicon name="edit_remarks" autocomplete="off" id="edit_remarks" placeholder="{{ __('Remarks') }}" :value="old('remarks')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Programs Table -->
                            <div class="sm:col-span-6">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                <div class="mt-2 overflow-x-auto">
                                    <table id="edit_programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
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
                                                        <x-form.input name="account_code[]" value="{{ $amount['account_code'] ?? '' }}" placeholder="Account Code" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterAccountCodesEdit(this)" autocomplete="off" />
                                                        <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="AccountCodeDropdown"></div>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea name="description[]" placeholder="Description" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off">{{ $amount['description'] ?? '' }}</x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea name="programs[]" placeholder="Program" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off">{{ $amount['program'] ?? '' }}</x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input type="text" name="balance_from_allotment[]" value="{{ number_format($amount['obr_amount'] ?? 0, 2) }}" oninput="formatCurrency(this)" placeholder="Balance" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input type="text" name="amount_of_obligation[]" value="{{ number_format($amount['amount'] ?? 0, 2) }}" oninput="validateAmountEdit(this); calculateTotalObligationEdit();" onblur="calculateTotalObligationEdit();" placeholder="Amount" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                                                    </td>
                                                    <td class="px-1 py-2 text-center">
                                                        <button type="button" onclick="deleteRowEdit(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td class="px-1 py-2">
                                                        <x-form.input name="account_code[]" placeholder="Account Code" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterAccountCodesEdit(this)" autocomplete="off" />
                                                        <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="AccountCodeDropdown"></div>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea name="description[]" placeholder="Description" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea name="programs[]" placeholder="Program" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input type="text" name="balance_from_allotment[]" oninput="formatCurrency(this)" placeholder="Balance" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input type="text" name="amount_of_obligation[]" oninput="validateAmountEdit(this); calculateTotalObligationEdit();" onblur="calculateTotalObligationEdit();" placeholder="Amount" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                                                    </td>
                                                    <td class="px-1 py-2 text-center">
                                                        <button type="button" onclick="deleteRowEdit(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
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
                                                <td class="px-2 py-2 text-left text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    <span id="totalObligation" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs">0.00</span>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <!-- Add Button for Dynamic Rows -->
                                <div class="sm:col-span-6 mt-4">
                                    <button type="button" onclick="addRowEdit()" class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
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
                    <button type="button" onclick="validateEditObligationsForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save mr-2 ml-1"></i> {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditObligationsModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times mr-2 ml-1"></i> {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmEditModal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6">
        <h2 class="text-lg font-semibold text-red-600 mb-4">Confirm Deletion</h2>
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-6">
            Are you sure you want to delete this row? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-2">
            <button id="confirmEditDeleteBtn" class="mr-1 text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                <i class="fas fa-trash mr-1 -ml-1"></i> Delete
            </button>
            <button id="cancelEditDeleteBtn" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                <i class="fas fa-times mr-1 -ml-1"></i> Cancel
            </button>
        </div>
    </div>
</div>

<script>
    //Open Edit Modal
    function openEditObligationsModal(obligation) {
        closeAllDropdowns();
        // Populate the form fields with the obligation data
        document.querySelector("input[name='obligation_id']").value = obligation.id;

        // Fix: handle object or string for office_allotment_class
        let officeAllotmentClassValue = '';
        if (typeof obligation.office_allotment_class === 'object' && obligation.office_allotment_class !== null) {
            officeAllotmentClassValue = (obligation.office_allotment_class.office_abbreviation || '') + ' - ' + (obligation.office_allotment_class.class || '');
        } else if (obligation.office_allotment_class_name) {
            officeAllotmentClassValue = obligation.office_allotment_class_name;
        } else {
            officeAllotmentClassValue = obligation.office_allotment_class || '';
        }
        document.getElementById('edit_office_allotment_class').value = officeAllotmentClassValue;

        document.getElementById('edit_obr_no').value = obligation.obr_no;
        document.getElementById('edit_obr_date').value = obligation.obr_date;
        document.getElementById('edit_obr_type').value = obligation.obr_type;
        document.getElementById('edit_particulars').value = obligation.particulars;
        document.getElementById('edit_remarks').value = obligation.remarks;

        // Populate programs_table tbody with ObligationAmounts
        const tbody = document.querySelector('#edit_programs_table tbody');
        tbody.innerHTML = '';
        if (obligation.obligation_amounts && Array.isArray(obligation.obligation_amounts) && obligation.obligation_amounts.length > 0) {
            obligation.obligation_amounts.forEach(function(amount, idx) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-1 py-2">
                        <x-form.input name="account_code[]" value="${amount.account_code || ''}" placeholder="Account Code" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterAccountCodesEdit(this)" autocomplete="off" />
                        <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="AccountCodeDropdown"></div>
                    </td>
                    <td class="px-1 py-2">
                        <x-form.textarea name="description[]" placeholder="Description" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off">${amount.description || ''}</x-form.textarea>
                    </td>
                    <td class="px-1 py-2">
                        <x-form.textarea name="programs[]" placeholder="Program" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off">${amount.program || ''}</x-form.textarea>
                    </td>
                    <td class="px-1 py-2">
                        <x-form.input type="text" name="balance_from_allotment[]" value="${parseFloat(amount.obr_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}" oninput="formatCurrency(this)" placeholder="Balance" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
                    </td>
                    <td class="px-1 py-2">
                        <x-form.input type="text" name="amount_of_obligation[]" value="${parseFloat(amount.amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}" oninput="validateAmountEdit(this); calculateTotalObligationEdit();" onblur="calculateTotalObligationEdit();" placeholder="Amount" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                    </td>
                    <td class="px-1 py-2 text-center">
                        <button type="button" onclick="deleteRowEdit(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        } else {
            // If no amounts, add a single empty row
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-1 py-2">
                    <x-form.input name="account_code[]" placeholder="Account Code" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterAccountCodesEdit(this)" autocomplete="off" />
                    <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="AccountCodeDropdown"></div>
                </td>
                <td class="px-1 py-2">
                    <x-form.textarea name="description[]" placeholder="Description" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                </td>
                <td class="px-1 py-2">
                    <x-form.textarea name="programs[]" placeholder="Program" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                </td>
                <td class="px-1 py-2">
                    <x-form.input type="text" name="balance_from_allotment[]" oninput="formatCurrency(this)" placeholder="Balance" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
                </td>
                <td class="px-1 py-2">
                    <x-form.input type="text" name="amount_of_obligation[]" oninput="validateAmountEdit(this); calculateTotalObligationEdit();" onblur="calculateTotalObligationEdit();" placeholder="Amount" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                </td>
                <td class="px-1 py-2 text-center">
                    <button type="button" onclick="deleteRowEdit(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        }
        calculateTotalObligationEdit();
        document.getElementById('editObligationsModal').classList.remove('hidden');
    }
    //Close Edit Modal
    function closeEditObligationsModal() {
        document.getElementById('editObligationsModal').classList.add('hidden');
    }


    // List of appropriations passed from the backend
    const editAppropriations = [
    @foreach($appropriations as $appropriation)
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
    function filterAccountCodesEdit(inputElement) {
        const officeAllotmentClassId = document.getElementById('office_allotment_class_id').value;
        const dropdown = inputElement.nextElementSibling;
        const filter = inputElement.value.toLowerCase();

        dropdown.innerHTML = ''; // Clear previous results

        if (!filter || !officeAllotmentClassId) {
            dropdown.classList.add('hidden');
            return;
        }

        // Filter account codes based on the input and matching office_allotment_class_id
        const filteredCodes = editAppropriations.filter(item =>
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
                inputElement.value = item.account_code;
                populateFieldsEdit(inputElement, item);
                calculateBalanceEdit(inputElement, item);
                dropdown.classList.add('hidden');
            };
            dropdown.appendChild(option);
        });

        dropdown.classList.remove('hidden');
    }

    // Populate related fields (description and program) based on the selected account code (Edit Modal)
    function populateFieldsEdit(inputElement, item) {
        const row = inputElement.closest('tr');
        const programField = row.querySelector('[name="programs[]"]');
        const descriptionField = row.querySelector('[name="description[]"]');

        // Populate the program and description fields
        if (programField) programField.value = item.program ? item.program.trim() : '';
        if (descriptionField) descriptionField.value = item.description ? item.description.trim() : '';
    }

    function calculateBalanceEdit(inputElement, item) {
        const row = inputElement.closest('tr');
        const balanceField = row.querySelector('[name="balance_from_allotment[]"]');

        // Use the pre-calculated balance from the appropriations data
        const balance = parseFloat(item.balance || 0);
        const formattedBalance = balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (balanceField) balanceField.value = formattedBalance;
    }

    // Hide account code dropdowns for edit modal only
    function hideAccountCodeDropdownsEdit(event) {
        const dropdowns = document.querySelectorAll('#AccountCodeDropdown');
        dropdowns.forEach(dropdown => {
            if (!event.target.closest('[name="account_code[]"]')) {
                dropdown.classList.add('hidden');
            }
        });
    }
    document.addEventListener('click', hideAccountCodeDropdownsEdit);

    function validateAmountEdit(inputElement) {
        const row = inputElement.closest('tr'); // Get the current row
        const balanceField = row.querySelector('[name="balance_from_allotment[]"]'); // Get the balance field

        if (balanceField) {
            const maxBalance = parseFloat((balanceField.value || 0).replace(/,/g, ''));
            const currentValue = parseFloat((inputElement.value || 0).replace(/,/g, ''));
            if (currentValue > maxBalance) {
                inputElement.value = maxBalance.toFixed(2);
            }
        }
    }

    function calculateTotalObligationEdit() {
        const amountFields = document.querySelectorAll('[name="amount_of_obligation[]"]');
        let total = 0;
        amountFields.forEach(field => {
            const value = parseFloat(field.value.replace(/,/g, '') || 0);
            total += value;
        });
        const totalObligationElement = document.getElementById('totalObligation');
        totalObligationElement.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function addRowEdit() {
        const tableBody = document.querySelector('#edit_programs_table tbody');
        const lastRow = tableBody.querySelector('tr:last-child');
        const newRow = lastRow.cloneNode(true);
        newRow.querySelectorAll('input, textarea').forEach(input => { input.value = ''; });
        tableBody.appendChild(newRow);
        calculateTotalObligationEdit();
    }

    let editRowToDelete = null;

    function deleteRowEdit(button) {
        editRowToDelete = button.closest('tr'); // Store the row for deletion
        document.getElementById('deleteConfirmEditModal').classList.remove('hidden'); // Show the modal
    }

    // Confirm delete
    document.getElementById('confirmEditDeleteBtn').addEventListener('click', function() {
        const tableBody = editRowToDelete.parentNode;
        const messageDiv = document.getElementById('tableMessage');

        if (tableBody.rows.length > 1) {
            tableBody.removeChild(editRowToDelete);
            messageDiv.classList.add('hidden');
        } else {
            messageDiv.textContent = "At least one row must remain in the table.";
            messageDiv.classList.remove('hidden');
        }

        editRowToDelete = null;
        document.getElementById('deleteConfirmEditModal').classList.add('hidden');
        calculateTotalObligationEdit();
    });

    // Cancel delete
    document.getElementById('cancelEditDeleteBtn').addEventListener('click', function() {
        editRowToDelete = null;
        document.getElementById('deleteConfirmEditModal').classList.add('hidden');
    });

    // Autocomplete for Office and Allotment Class (Edit Modal)
    const editOfficeAllotmentClasses = [
        @foreach($office_allotment_classes as $office_allotment_class) {
            id: "{{ $office_allotment_class->id }}",
            name: "{{ $office_allotment_class->office_abbreviation }} - {{ $office_allotment_class->class }}",
            fund: "{{ $office_allotment_class->fund ?? 'General Fund' }}"
        },
        @endforeach
    ];
    function filterEditOfficeAllotmentClasses() {
        const input = document.getElementById("edit_office_allotment_class");
        const dropdown = document.getElementById("EditOfficeAllotmentClassDropdown");
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
                document.getElementById("office_allotment_class_id").value = item.id;
                // Reset all account code, description, program, balance, and amount fields in the edit table
                document.querySelectorAll('#edit_programs_table [name="account_code[]"]').forEach(field => field.value = '');
                document.querySelectorAll('#edit_programs_table [name="description[]"]').forEach(field => field.value = '');
                document.querySelectorAll('#edit_programs_table [name="programs[]"]').forEach(field => field.value = '');
                document.querySelectorAll('#edit_programs_table [name="balance_from_allotment[]"]').forEach(field => field.value = '');
                document.querySelectorAll('#edit_programs_table [name="amount_of_obligation[]"]').forEach(field => field.value = '');
                dropdown.classList.add("hidden");
            };
            dropdown.appendChild(option);
        });
        dropdown.classList.remove("hidden");
    }
    // Hide dropdown when clicking outside the edit input
    function hideEditOfficeAllotmentClassDropdown(event) {
        const dropdown = document.getElementById("EditOfficeAllotmentClassDropdown");
        if (!event.target.closest("#edit_office_allotment_class")) {
            dropdown.classList.add("hidden");
        }
    }
    document.addEventListener("click", hideEditOfficeAllotmentClassDropdown);

    // Update text color for edit modal fields
    function updateTextColorEdit(element) {
        if (element.value.trim() !== "") {
            element.classList.remove("text-gray-500");
            element.classList.add("text-gray-900", "dark:text-gray-100");
        } else {
            element.classList.remove("text-gray-900", "dark:text-gray-100");
            element.classList.add("text-gray-500");
        }
        if (element.hasAttribute("readonly")) {
            element.classList.add("text-gray-900", "dark:text-gray-400");
        }
        if (element.tagName === "SELECT" && element.disabled) {
            element.classList.add("text-gray-700", "dark:text-gray-500");
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        const inputsAndSelects = document.querySelectorAll("#editObligationsModal input, #editObligationsModal select");
        inputsAndSelects.forEach(element => {
            updateTextColorEdit(element);
            element.addEventListener("input", function() {
                updateTextColorEdit(this);
            });
            if (element.tagName === "SELECT") {
                element.addEventListener("change", function() {
                    updateTextColorEdit(this);
                });
            }
        });
    });

    function validateEditObligationsForm() {
        const form = document.getElementById('editObligationsForm');
        let isValid = true;
        document.querySelectorAll('#editObligationsModal .text-red-500').forEach(error => error.textContent = '');
        const officeAllotmentClass = document.getElementById('edit_office_allotment_class');
        const officeAllotmentClassId = document.getElementById('office_allotment_class_id');
        if (!officeAllotmentClass.value.trim() || !officeAllotmentClassId.value.trim()) {
            document.getElementById('OfficeAllotmentClassError').textContent = 'Office and Allotment Class is required.';
            isValid = false;
        }
        const obrDate = document.getElementById('edit_obr_date');
        if (!obrDate.value.trim()) {
            obrDate.classList.add('border-red-500');
            obrDate.classList.remove('border-gray-300');
            isValid = false;
        } else {
            obrDate.classList.remove('border-red-500');
            obrDate.classList.add('border-gray-300');
        }
        const obrType = document.getElementById('edit_obr_type');
        if (!obrType.value.trim()) {
            document.getElementById('obrTypeError').textContent = 'Obligation Type is required.';
            isValid = false;
        }
        const obrNo = document.getElementById('edit_obr_no');
        if (!obrNo.value.trim()) {
            obrNo.classList.add('border-red-500');
            obrNo.classList.remove('border-gray-300');
            isValid = false;
        } else {
            obrNo.classList.remove('border-red-500');
            obrNo.classList.add('border-gray-300');
        }
        const particulars = document.getElementById('edit_particulars');
        if (!particulars.value.trim()) {
            document.getElementById('particularsError').textContent = 'Particulars field is required.';
            isValid = false;
        } else {
            particulars.classList.remove('border-red-500');
            particulars.classList.add('border-gray-300');
        }
        const tableBody = document.querySelector('#edit_programs_table tbody');
        if (tableBody.rows.length === 0) {
            const tableMessage = document.getElementById('tableMessage');
            tableMessage.textContent = 'At least one row is required in the table.';
            tableMessage.classList.remove('hidden');
            isValid = false;
        }
        const amountFields = document.querySelectorAll('[name="amount_of_obligation[]"]');
        amountFields.forEach((field, index) => {
            const value = parseFloat(field.value || 0);
            if (value <= 0) {
                field.classList.add('border-red-500');
                field.classList.remove('border-gray-300');
                const errorMessage = document.createElement('div');
                errorMessage.className = 'text-red-500 text-xs mt-1';
                errorMessage.textContent = `Row ${index + 1}: Amount of Obligation must be greater than 0.`;
                errorMessage.style.display = 'block'; // Ensure the error message is displayed
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
            const value = parseFloat(field.value || 0);
            totalBalance += value; // Sum up all balances
        });

        if (totalBalance === 0) {
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