<!-- Edit Realignment Modal -->
<form id="editRealignmentForm" method="POST" action="">
    @csrf
    @method('PUT')
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
    <input type="hidden" name="realignment_type_filter" value="{{ request('realignment_type_filter') }}">
    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <div id="editRealignmentModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Realignment | Augmentation') }}
                    </h3>
                    <button type="button" onclick="closeEditRealignmentModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
                    <div class="grid gap-3">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="realignment_id" id="realignment_id">
                            <input type="hidden" name="appropriations_id" id="appropriations_id">

                            <!-- Realignment Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_realignment_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Realignment No.')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon type='text' name="edit_realignment_no" autocomplete="off" id="edit_realignment_no" placeholder="{{ __('Realignment No.') }}" :value="now()->format('Y-m-')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_realignment_noError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_realignment_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="edit_realignment_date" autocomplete="off" id="edit_realignment_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Basis -->
                            <div class="sm:col-span-6">
                                <x-form.label for="edit_basis" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Basis')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-copy"></i>
                                        </x-slot>
                                        <x-form.textarea withicon type="text" name="edit_basis" autocomplete="off" id="edit_basis" placeholder="{{ __('Basis') }}" :value="old('edit_basis')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_basisError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                            <!-- SOURCE SECTION -->
                            <div class="sm:col-span-6" id="edit_source_section">
                                
                                <div class="bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-4 mb-2">
                                    <div class="mb-2 mt-2 font-bold text-base text-gray-800 dark:text-gray-100 border-b border-gray-300 dark:border-gray-600 pb-1">
                                        <i class="fas fa-upload mr-1 text-blue-500"></i> Source
                                    </div>
                                    <!-- Source Office and Allotment Class -->
                                    <div class="sm:col-span-3 relative mb-4">
                                        <x-form.label for="edit_source_office_allotment_class" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                                        <div class="mt-2">
                                            <x-form.input-with-icon-wrapper>
                                                <x-slot name="icon">
                                                    <i class="fas fa-laptop-house"></i>
                                                </x-slot>
                                                <x-form.input
                                                    withicon
                                                    type="text"
                                                    name="edit_source_office_allotment_class"
                                                    id="edit_source_office_allotment_class"
                                                    placeholder="{{ __('Office and Allotment Class') }}"
                                                    class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                                    oninput="editFilterOfficeAllotmentClasses('source')"
                                                    autocomplete="off" />
                                            </x-form.input-with-icon-wrapper>
                                            <input type="hidden" name="edit_source_office_allotment_class_id" id="edit_source_office_allotment_class_id" />
                                            <div id="edit_SourceOfficeAllotmentClassDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                                <!-- Suggestions appear here -->
                                            </div>
                                            <span id="edit_SourceOfficeAllotmentClassError" class="text-red-500 text-sm"></span>
                                        </div>
                                    </div>
                                    <!-- Source Programs Table -->
                                    <div class="sm:col-span-6">
                                        <div id="edit_sourceTableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                        <div class="mt-2 overflow-x-auto">
                                            <table id="edit_source_programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
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
                                                            {{ __('Amount For Realignment') }}
                                                        </th>
                                                        <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                            <!-- Actions -->
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ">
                                                    <tr>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                name="edit_source_account_code"
                                                                placeholder="{{ __('Account Code') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                oninput="editFilterAccountCodes(this, 'source')"
                                                                autocomplete="off" />
                                                            <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="SourceAccountCodeDropdown">
                                                                <!-- Suggestions will appear here -->
                                                            </div>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.textarea
                                                                name="edit_source_description"
                                                                placeholder="{{ __('Description') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                autocomplete="off"></x-form.textarea>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.textarea
                                                                name="edit_source_programs"
                                                                placeholder="{{ __('Program') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                autocomplete="off"></x-form.textarea>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                type="text"
                                                                name="edit_source_balance_from_allotment"
                                                                oninput="formatCurrency(this)"
                                                                placeholder="{{ __('Balance') }}"
                                                                autocomplete="off"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                readonly />
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                type="text"
                                                                name="edit_source_amount"
                                                                placeholder="{{ __('Amount') }}"
                                                                autocomplete="off"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                :value="isset($realignment) ? $realignment->amount : ''"
                                                            />
                                                        </td>
                                                    </tr>
                                                    <!-- Additional rows can be dynamically added using JavaScript -->
                                                </tbody>
                                            </table>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="sm:col-span-6 my-2">
                                <hr class="border-t-2 border-gray-300 dark:border-gray-600">
                            </div>

                            <!-- RECIPIENT SECTION -->
                            <div class="sm:col-span-6" id="edit_recipient_section">
                                
                                <div class="bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-4 mb-2">
                                    <div class="mb-2 font-bold text-base text-gray-800 dark:text-gray-100 border-b border-gray-300 dark:border-gray-600 pb-1">
                                        <i class="fas fa-download mr-1 text-green-500"></i> Recipient
                                    </div>
                                    <!-- Recipient Office and Allotment Class -->
                                    <div class="sm:col-span-3 relative mb-4">
                                        <x-form.label for="edit_recipient_office_allotment_class" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                                        <div class="mt-2">
                                            <x-form.input-with-icon-wrapper>
                                                <x-slot name="icon">
                                                    <i class="fas fa-laptop-house"></i>
                                                </x-slot>
                                                <x-form.input
                                                    withicon
                                                    type="text"
                                                    name="edit_recipient_office_allotment_class"
                                                    id="edit_recipient_office_allotment_class"
                                                    placeholder="{{ __('Office and Allotment Class') }}"
                                                    class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                                    oninput="editFilterOfficeAllotmentClasses('recipient')"
                                                    autocomplete="off" />
                                            </x-form.input-with-icon-wrapper>
                                            <input type="hidden" name="edit_recipient_office_allotment_class_id" id="edit_recipient_office_allotment_class_id" />
                                            <div id="edit_RecipientOfficeAllotmentClassDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                                <!-- Suggestions appear here -->
                                            </div>
                                            <span id="edit_RecipientOfficeAllotmentClassError" class="text-red-500 text-sm"></span>
                                        </div>
                                    </div>
                                    <!-- Recipient Programs Table -->
                                    <div class="sm:col-span-6">
                                        <div id="edit_recipientTableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                        <div class="mt-2 overflow-x-auto">
                                            <table id="edit_recipient_programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
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
                                                            {{ __('Balance from Realignment') }}
                                                        </th>
                                                        <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                            {{ __('Amount For Realignment') }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ">
                                                    <tr>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                name="edit_recipient_account_code"
                                                                placeholder="{{ __('Account Code') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                oninput="editFilterAccountCodes(this, 'recipient')"
                                                                autocomplete="off" />
                                                            <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="RecipientAccountCodeDropdown">
                                                                <!-- Suggestions will appear here -->
                                                            </div>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.textarea
                                                                name="edit_recipient_description"
                                                                placeholder="{{ __('Description') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                autocomplete="off"></x-form.textarea>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.textarea
                                                                name="edit_recipient_programs"
                                                                placeholder="{{ __('Program') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                autocomplete="off"></x-form.textarea>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                type="text"
                                                                name="edit_recipient_balance"
                                                                oninput="formatCurrency(this)"
                                                                placeholder="{{ __('Balance') }}"
                                                                autocomplete="off"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                readonly />
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                type="text"
                                                                name="edit_recipient_amount"
                                                                placeholder="{{ __('Amount') }}"
                                                                autocomplete="off"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                :value="isset($realignment) ? $realignment->amount : ''"
                                                            />
                                                        </td>
                                                    </tr>
                                                    <!-- Additional rows can be dynamically added using JavaScript -->
                                                </tbody>
                                            </table>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center p-4 flex border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditRealignmentForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save mr-2 ml-1"></i> {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditRealignmentModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times mr-2 ml-1"></i> {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
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

    function openEditRealignmentModal(realignment) {
        closeAllDropdowns(); // Close any open dropdowns
        // Populate the modal fields with the realignment data

        document.querySelector("input[name='realignment_id']").value = realignment.id;
        document.querySelector("input[name='appropriations_id']").value = realignment.appropriations_id;

        document.getElementById('editRealignmentForm').action = `/realignments/${realignment.id}`;

        document.getElementById('edit_realignment_no').value = realignment.realignment_no;
        document.getElementById('edit_realignment_date').value = realignment.realignment_date;
        document.getElementById('edit_basis').value = realignment.basis;
        
        // Get the office_allotment_class object by ID and display its office_abbreviation - class
        let sourceOAC = officeAllotmentClasses.find(
            oac => String(oac.id) === String(realignment.office_allotment_classes_id)
        );
        if (sourceOAC) {
            document.getElementById('edit_source_office_allotment_class').value = sourceOAC.name;
            document.getElementById('edit_source_office_allotment_class_id').value = sourceOAC.id;
        } else {
            document.getElementById('edit_source_office_allotment_class').value = '';
            document.getElementById('edit_source_office_allotment_class_id').value = '';
        }

        let recipientOAC = officeAllotmentClasses.find(
            oac => String(oac.id) === String(realignment.office_allotment_classes_id)
        );
        if (recipientOAC) {
            document.getElementById('edit_recipient_office_allotment_class').value = recipientOAC.name;
            document.getElementById('edit_recipient_office_allotment_class_id').value = recipientOAC.id;
        } else {
            document.getElementById('edit_recipient_office_allotment_class').value = '';
            document.getElementById('edit_recipient_office_allotment_class_id').value = '';
        }

        // Find the source appropriation object by ID
        let sourceAppropriation = appropriations.find(
            app => String(app.id) === String(realignment.appropriations_id)
        );
        if (sourceAppropriation) {
            document.querySelector('[name="edit_source_account_code"]').value = sourceAppropriation.account_code;
            document.querySelector('[name="edit_source_description"]').value = sourceAppropriation.description;
            document.querySelector('[name="edit_source_programs"]').value = sourceAppropriation.program;
            // Set balance from appropriation->balance (from controller)
            let balanceField = document.querySelector('[name="edit_source_balance_from_allotment"]');
            if (balanceField) {
                let balance = parseFloat(sourceAppropriation.balance || 0);
                let formattedBalance = balance.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                balanceField.value = formattedBalance;
            }
        } else {
            document.querySelector('[name="edit_source_account_code"]').value = '';
            document.querySelector('[name="edit_source_description"]').value = '';
            document.querySelector('[name="edit_source_programs"]').value = '';
            let balanceField = document.querySelector('[name="edit_source_balance_from_allotment"]');
            if (balanceField) balanceField.value = '';
        }

        // Find the recipient appropriation object by ID
        let recipientAppropriation = appropriations.find(
            app => String(app.id) === String(realignment.appropriations_id)
        );
        if (recipientAppropriation) {
            document.querySelector('[name="edit_recipient_account_code"]').value = recipientAppropriation.account_code;
            document.querySelector('[name="edit_recipient_description"]').value = recipientAppropriation.description;
            document.querySelector('[name="edit_recipient_programs"]').value = recipientAppropriation.program;
        } else {
            document.querySelector('[name="edit_recipient_account_code"]').value = '';
            document.querySelector('[name="edit_recipient_description"]').value = '';
            document.querySelector('[name="edit_recipient_programs"]').value = '';
        }

        // Populate all source amounts if realignment.source_amounts exists (array of amounts)
        if (realignment.source_amounts && Array.isArray(realignment.source_amounts)) {
            let sourceAmountInputs = document.getElementsByName('edit_source_amount[]');
            for (let i = 0; i < sourceAmountInputs.length; i++) {
                sourceAmountInputs[i].value = realignment.source_amounts[i] !== undefined ? realignment.source_amounts[i] : '';
            }
        } else if (realignment.amount !== undefined) {
            // fallback for single amount
            let sourceAmountInputs = document.getElementsByName('edit_source_amount[]');
            if (sourceAmountInputs.length > 0) sourceAmountInputs[0].value = realignment.amount;
        }

        // Populate all recipient amounts if realignment.recipient_amounts exists (array of amounts)
        if (realignment.recipient_amounts && Array.isArray(realignment.recipient_amounts)) {
            let recipientAmountInputs = document.getElementsByName('edit_recipient_amount[]');
            for (let i = 0; i < recipientAmountInputs.length; i++) {
                recipientAmountInputs[i].value = realignment.recipient_amounts[i] !== undefined ? realignment.recipient_amounts[i] : '';
            }
        } else if (realignment.amount !== undefined) {
            // fallback for single amount
            let recipientAmountInputs = document.getElementsByName('edit_recipient_amount[]');
            if (recipientAmountInputs.length > 0) recipientAmountInputs[0].value = realignment.amount;
        }

        // Set the correct amount field based on type
        if (realignment.type === 'Source') {
            const sourceAmountInput = document.querySelector('[name="edit_source_amount"]');
            if (sourceAmountInput) sourceAmountInput.value = realignment.amount !== undefined ? realignment.amount : '';
            const recipientAmountInput = document.querySelector('[name="edit_recipient_amount"]');
            if (recipientAmountInput) recipientAmountInput.value = '';
        } else if (realignment.type === 'Recipient') {
            const recipientAmountInput = document.querySelector('[name="edit_recipient_amount"]');
            if (recipientAmountInput) recipientAmountInput.value = realignment.amount !== undefined ? realignment.amount : '';
            const sourceAmountInput = document.querySelector('[name="edit_source_amount"]');
            if (sourceAmountInput) sourceAmountInput.value = '';
        } else {
            // fallback: set both if type is not clear
            const sourceAmountInput = document.querySelector('[name="edit_source_amount"]');
            if (sourceAmountInput) sourceAmountInput.value = realignment.amount !== undefined ? realignment.amount : '';
            const recipientAmountInput = document.querySelector('[name="edit_recipient_amount"]');
            if (recipientAmountInput) recipientAmountInput.value = realignment.amount !== undefined ? realignment.amount : '';
        }

        // Show/hide tables based on type
        const sourceSection = document.getElementById('edit_source_section');
        const recipientSection = document.getElementById('edit_recipient_section');
        if (realignment.type === 'Source') {
            sourceSection.style.display = '';
            recipientSection.style.display = 'none';
        } else if (realignment.type === 'Recipient') {
            sourceSection.style.display = 'none';
            recipientSection.style.display = '';
        } else {
            sourceSection.style.display = '';
            recipientSection.style.display = '';
        }

        // Show the modal
        document.getElementById('editRealignmentModal').classList.remove('hidden');
    }

    function closeEditRealignmentModal() {
        document.getElementById('editRealignmentModal').classList.add('hidden');
    }
    

    function validateEditRealignmentForm() {
        // Perform form validation
        let isValid = true;
        const realignmentNo = document.getElementById('edit_realignment_no').value.trim();
        const realignmentDate = document.getElementById('edit_realignment_date').value.trim();
        const basis = document.getElementById('edit_basis').value.trim();
        const sourceOfficeAllotmentClass = document.getElementById('edit_source_office_allotment_class').value.trim();
        const recipientOfficeAllotmentClass = document.getElementById('edit_recipient_office_allotment_class').value.trim();

        // Validate Realignment No.
        if (realignmentNo === '') {
            document.getElementById('edit_realignment_noError').innerText = 'Realignment No. is required.';
            isValid = false;
        } else {
            document.getElementById('edit_realignment_noError').innerText = '';
        }

        // Validate Basis
        if (basis === '') {
            document.getElementById('edit_basisError').innerText = 'Basis is required.';
            isValid = false;
        } else {
            document.getElementById('edit_basisError').innerText = '';
        }

        // Validate Source Office and Allotment Class
        if (sourceOfficeAllotmentClass === '') {
            document.getElementById('edit_SourceOfficeAllotmentClassError').innerText = 'Source Office and Allotment Class is required.';
            isValid = false;
        } else {
            document.getElementById('edit_SourceOfficeAllotmentClassError').innerText = '';
        }

        // Validate Recipient Office and Allotment Class
        if (recipientOfficeAllotmentClass === '') {
            document.getElementById('edit_RecipientOfficeAllotmentClassError').innerText = 'Recipient Office and Allotment Class is required.';
            isValid = false;
        } else {
            document.getElementById('edit_RecipientOfficeAllotmentClassError').innerText = '';
        }


        if (isValid) {
            // Submit the form via AJAX or any other method
            document.getElementById('editRealignmentForm').submit();
        }
    }

    // --- OfficeAllotmentClass Dropdown Logic (for edit modal, both source and recipient) ---
    function editFilterOfficeAllotmentClasses(type) {
        let input, dropdown, hiddenId;
        if (type === 'source') {
            input = document.getElementById('edit_source_office_allotment_class');
            dropdown = document.getElementById('edit_SourceOfficeAllotmentClassDropdown');
            hiddenId = document.getElementById('edit_source_office_allotment_class_id');
        } else {
            input = document.getElementById('edit_recipient_office_allotment_class');
            dropdown = document.getElementById('edit_RecipientOfficeAllotmentClassDropdown');
            hiddenId = document.getElementById('edit_recipient_office_allotment_class_id');
        }
        const val = input.value.trim().toLowerCase();
        dropdown.innerHTML = '';
        if (!val) {
            dropdown.classList.add('hidden');
            hiddenId.value = '';
            return;
        }
        let matches = officeAllotmentClasses.filter(oac =>
            (oac.name || '').toLowerCase().includes(val)
        );
        if (matches.length === 0) {
            dropdown.classList.add('hidden');
            hiddenId.value = '';
            return;
        }
        matches.forEach(oac => {
            let div = document.createElement('div');
            div.className = 'px-3 py-2 cursor-pointer hover:bg-blue-100 dark:hover:bg-gray-700';
            div.textContent = oac.name;
            div.onclick = function() {
                input.value = oac.name;
                hiddenId.value = oac.id;
                dropdown.classList.add('hidden');

                // Reset all account code, description, program, balance, and amount fields in the relevant table
                let tableId = type === 'source' ? 'edit_source_programs_table' : 'edit_recipient_programs_table';
                const tableBody = document.getElementById(tableId).getElementsByTagName('tbody')[0];
                if (tableBody) {
                    Array.from(tableBody.querySelectorAll('tr')).forEach(row => {
                        const accountCode = row.querySelector(type === 'source' ? '[name="edit_source_account_code"]' : '[name="edit_recipient_account_code"]');
                        const description = row.querySelector(type === 'source' ? '[name="edit_source_description"]' : '[name="edit_recipient_description"]');
                        const program = row.querySelector(type === 'source' ? '[name="edit_source_programs"]' : '[name="edit_recipient_programs"]');
                        const balance = row.querySelector(type === 'source' ? '[name="edit_source_balance_from_allotment"]' : '[name="edit_recipient_balance"]');
                        if (accountCode) accountCode.value = '';
                        if (description) description.value = '';
                        if (program) program.value = '';
                        if (balance) balance.value = '';
                    });
                }
            };
            dropdown.appendChild(div);
        });
        dropdown.classList.remove('hidden');
    }

    // --- Account Code Dropdown Logic (for edit modal, both source and recipient) ---
    function editFilterAccountCodes(input, type) {
        let dropdownId = (type === 'source') ? 'SourceAccountCodeDropdown' : 'RecipientAccountCodeDropdown';
        let dropdown = input.parentElement.parentElement.querySelector('#' + dropdownId);
        dropdown.innerHTML = '';
        const val = input.value.trim().toLowerCase();
        if (!val) {
            dropdown.classList.add('hidden');
            return;
        }
        let matches = appropriations.filter(app =>
            (app.account_code || '').toLowerCase().includes(val)
        );
        if (matches.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        matches.forEach(item => {
            let option = document.createElement('div');
            option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-300 dark:border-gray-700';
            option.innerHTML = `
                <strong>${item.account_code}</strong><br>
                <span class="text-gray-500 dark:text-gray-400">${item.description || 'No description'}</span><br>
                <span class="text-gray-500 dark:text-gray-400">${item.programs || item.program || 'No program'}</span>
            `;
            option.onclick = function() {
                input.value = item.account_code;
                editPopulateFields(input, type, item);
                if (type === 'source') {
                    calculateEditSourceBalance(input, item);
                }
                dropdown.classList.add('hidden');
            };
            dropdown.appendChild(option);
        });
        dropdown.classList.remove('hidden');
    }

    function editPopulateFields(input, type, appropriation) {
        // Find the row (tr) containing the input
        let row = input.closest('tr');
        if (!row) return;
        // Description
        let descField = row.querySelector(
            type === 'source' ? 'textarea[name="edit_source_description"]' : 'textarea[name="edit_recipient_description"]'
        );
        if (descField) descField.value = appropriation.description || '';
        // Program
        let progField = row.querySelector(
            type === 'source' ? 'textarea[name="edit_source_programs"]' : 'textarea[name="edit_recipient_programs"]'
        );
        if (progField) progField.value = appropriation.programs || appropriation.program || 'No program';
        // Optionally set a hidden appropriation_id field if you have one
        // (add hidden input if needed)
    }

    // --- Calculate and Populate Balance for Edit Modal (like create blade) ---
    function calculateEditSourceBalance(inputElement, item) {
        const row = inputElement.closest('tr');
        const balanceField = row.querySelector('[name="edit_source_balance_from_allotment"]');
        const balance = parseFloat(item.balance || 0);
        const formattedBalance = balance.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        if (balanceField) balanceField.value = formattedBalance;
    }

    // Hide dropdowns on click outside
    document.addEventListener('click', function(e) {
        ['edit_SourceOfficeAllotmentClassDropdown', 'edit_RecipientOfficeAllotmentClassDropdown'].forEach(function(id) {
            let dropdown = document.getElementById(id);
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });

    // Hide account code dropdowns on click outside
    document.addEventListener('click', function(e) {
        ['SourceAccountCodeDropdown', 'RecipientAccountCodeDropdown'].forEach(function(id) {
            document.querySelectorAll('#' + id).forEach(function(dropdown) {
                if (dropdown && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    });

    // Close all dropdowns helper
    function closeAllDropdowns() {
        document.getElementById('edit_SourceOfficeAllotmentClassDropdown').classList.add('hidden');
        document.getElementById('edit_RecipientOfficeAllotmentClassDropdown').classList.add('hidden');
    }

    document.addEventListener("DOMContentLoaded", function() {
        const inputsAndSelects = document.querySelectorAll("input, select"); // Select both input and select elements

        inputsAndSelects.forEach(element => {
            editUpdateTextColor(element); // Check initial values

            element.addEventListener("input", function() {
                editUpdateTextColor(this);
            });

            // For select fields, listen to the 'change' event
            if (element.tagName === "SELECT") {
                element.addEventListener("change", function() {
                    editUpdateTextColor(this);
                });
            }
        });

        // Utility to update text color for edit modal fields
        function editUpdateTextColor(element) {
            if (element.value && element.value.trim() !== "") {
                element.classList.remove("text-gray-400");
                element.classList.add("text-gray-900", "dark:text-gray-100");
            } else {
                element.classList.remove("text-gray-900", "dark:text-gray-100");
                element.classList.add("text-gray-400");
            }
            // Apply specific styles for readonly inputs
            if (element.hasAttribute("readonly")) {
                element.classList.add("text-gray-900", "dark:text-gray-400");
            }
            // Apply specific styles for disabled select fields
            if (element.tagName === "SELECT" && element.disabled) {
                element.classList.add("text-gray-700", "dark:text-gray-400");
            }

            // Apply text color for textarea (including basis)
            if (element.tagName === "TEXTAREA") {
                if (element.value.trim() !== "") {
                    element.classList.remove("text-gray-400");
                    element.classList.add("text-gray-900", "dark:text-gray-100");
                } else {
                    element.classList.remove("text-gray-900", "dark:text-gray-100");
                    element.classList.add("text-gray-400");
                }
            }
        }

        // Patch openEditRealignmentModal to update text color after populating fields
        const originalOpenEditRealignmentModal = openEditRealignmentModal;
        openEditRealignmentModal = function(realignment) {
            originalOpenEditRealignmentModal(realignment);
            // Update text color for all relevant fields after values are set
            [
                'edit_basis',
                'edit_source_office_allotment_class',
                'edit_recipient_office_allotment_class',
                'edit_source_account_code',
                'edit_recipient_account_code'
            ].forEach(function(id) {
                var el = document.getElementById(id) || document.querySelector('[name="'+id+'"]');
                if (el) editUpdateTextColor(el);
            });
        };
    });

</script>