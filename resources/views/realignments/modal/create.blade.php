<!-- Create Realignment Modal -->
<form id="createRealignmentForm" method="POST" action="">
    @csrf
    
    <div id="createRealignmentModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Create Realignment | Augmentation') }}
                    </h3>
                    <button type="button" onclick="closeCreateRealignmentModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">

                            <!-- Realignment Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="realignment_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Realignment No.')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon type='text' name="realignment_no" autocomplete="off" id="realignment_no" placeholder="{{ __('Realignment No.') }}" :value="now()->format('Y-m-')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="realignment_noError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="realignment_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="realignment_date" autocomplete="off" id="realignment_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Basis -->
                            <div class="sm:col-span-6">
                                <x-form.label for="basis" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Basis')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-copy"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="basis" autocomplete="off" id="basis" placeholder="{{ __('Basis') }}" :value="old('basis')" class="block w-full dark:bg-gray-800 text-gray-900 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="basisError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                            <!-- Type Selection Dropdown -->
                            <div class="sm:col-span-6">
                                <x-form.label for="section_select" 
                                    class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" 
                                    :value="__('Type')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-code-branch"></i>
                                        </x-slot>
                                        <x-form.select withicon id="section_select" name="section_select" 
                                            class="block w-full border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200">
                                            <option value="both">Source & Recipient</option>
                                            <option value="source">Source Only</option>
                                            <option value="recipient">Recipient Only</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="section_selectError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                            <!-- SOURCE SECTION -->
                            <div class="sm:col-span-6">
                                
                                <div id="source_section" class="bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-4">
                                    <div class="mb-2 mt-2 font-bold text-base text-gray-800 dark:text-gray-100 border-b border-gray-300 dark:border-gray-600 pb-1">
                                        <i class="fas fa-upload mr-1 text-blue-500"></i> Source
                                    </div>
                                    <!-- Source Office and Allotment Class -->
                                    <div class="sm:col-span-3 relative mb-4">
                                        <x-form.label for="source_office_allotment_class" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                                        <div class="mt-2">
                                            <x-form.input-with-icon-wrapper>
                                                <x-slot name="icon">
                                                    <i class="fas fa-laptop-house"></i>
                                                </x-slot>
                                                <x-form.input
                                                    withicon
                                                    type="text"
                                                    name="source_office_allotment_class"
                                                    id="source_office_allotment_class"
                                                    placeholder="{{ __('Office and Allotment Class') }}"
                                                    class="block w-full bg-white text-gray-400 dark:bg-gray-800 dark:text-gray-200"
                                                    oninput="filterOfficeAllotmentClasses('source')"
                                                    autocomplete="off" />
                                            </x-form.input-with-icon-wrapper>
                                            <input type="hidden" name="source_office_allotment_class_id" id="source_office_allotment_class_id" />
                                            <div id="SourceOfficeAllotmentClassDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                                <!-- Suggestions appear here -->
                                            </div>
                                            <span id="SourceOfficeAllotmentClassError" class="text-red-500 text-sm"></span>
                                        </div>
                                    </div>
                                    <!-- Source Programs Table -->
                                    <div class="sm:col-span-6">
                                        <div id="sourceTableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                        <div class="mt-2 overflow-x-auto">
                                            <table id="source_programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
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
                                                <tfoot class="bg-gray-50 dark:bg-gray-800">
                                                    <tr>
                                                        <td colspan="4" class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                            {{ __('Total Source Amount') }}
                                                        </td>
                                                        <td class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                            <span id="totalSourceAmount" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-right text-xs">0.00</span>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ">
                                                    <tr>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                name="source_account_code[]"
                                                                placeholder="{{ __('Account Code') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                oninput="filterAccountCodes(this, 'source')"
                                                                autocomplete="off" />
                                                            <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="SourceAccountCodeDropdown">
                                                                <!-- Suggestions will appear here -->
                                                            </div>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.textarea
                                                                name="source_description[]"
                                                                placeholder="{{ __('Description') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                autocomplete="off"></x-form.textarea>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.textarea
                                                                name="source_programs[]"
                                                                placeholder="{{ __('Program') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                autocomplete="off"></x-form.textarea>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                type="text"
                                                                name="source_balance_from_allotment[]"
                                                                oninput="formatCurrency(this)"
                                                                placeholder="{{ __('Balance') }}"
                                                                autocomplete="off"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                readonly />
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                type="text"
                                                                name="source_amount[]"
                                                                placeholder="{{ __('Amount') }}"
                                                                autocomplete="off"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                                                        </td>
                                                        <td class="px-1 py-2 text-center">
                                                            <button type="button" onclick="deleteRow(this, 'source')" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <!-- Additional rows can be dynamically added using JavaScript -->
                                                </tbody>
                                            </table>
                                            <!-- Add Button for Dynamic Rows -->
                                            <div class="sm:col-span-6 mt-2 justify-end">
                                                <button type="button" onclick="addSourceRow()" class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                                                    <i class="fas fa-plus text-sm mr-2"></i>
                                                    {{ __('Add Row') }}
                                                </button>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="sm:col-span-6">
                                <hr class="border-t-2 border-gray-300 dark:border-gray-600">
                            </div>

                            <!-- RECIPIENT SECTION -->
                            <div class="sm:col-span-6">
                                
                                <div id="recipient_section" class="bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-4 mb-2">
                                    <div class="mb-2 font-bold text-base text-gray-800 dark:text-gray-100 border-b border-gray-300 dark:border-gray-600 pb-1">
                                        <i class="fas fa-download mr-1 text-green-500"></i> Recipient
                                    </div>
                                    <!-- Recipient Office and Allotment Class -->
                                    <div class="sm:col-span-3 relative mb-4">
                                        <x-form.label for="recipient_office_allotment_class" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                                        <div class="mt-2">
                                            <x-form.input-with-icon-wrapper>
                                                <x-slot name="icon">
                                                    <i class="fas fa-laptop-house"></i>
                                                </x-slot>
                                                <x-form.input
                                                    withicon
                                                    type="text"
                                                    name="recipient_office_allotment_class"
                                                    id="recipient_office_allotment_class"
                                                    placeholder="{{ __('Office and Allotment Class') }}"
                                                    class="block w-full bg-white text-gray-400 dark:bg-gray-800 dark:text-gray-200"
                                                    oninput="filterOfficeAllotmentClasses('recipient')"
                                                    autocomplete="off" />
                                            </x-form.input-with-icon-wrapper>
                                            <input type="hidden" name="recipient_office_allotment_class_id" id="recipient_office_allotment_class_id" />
                                            <div id="RecipientOfficeAllotmentClassDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                                <!-- Suggestions appear here -->
                                            </div>
                                            <span id="RecipientOfficeAllotmentClassError" class="text-red-500 text-sm"></span>
                                        </div>
                                    </div>
                                    <!-- Recipient Programs Table -->
                                    <div class="sm:col-span-6">
                                        <div id="recipientTableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                        <div class="mt-2 overflow-x-auto">
                                            <table id="recipient_programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
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
                                                            {{ __('Balance from Source') }}
                                                        </th>
                                                        <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                            {{ __('Amount For Realignment') }}
                                                        </th>
                                                        <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                            <!-- Actions -->
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tfoot class="bg-gray-50 dark:bg-gray-800">
                                                    <tr>
                                                        <td colspan="4" class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                            {{ __('Total Recipient Amount') }}
                                                        </td>
                                                        <td class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                            <span id="totalRecipientAmount" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-right text-xs">0.00</span>
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ">
                                                    <tr>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                name="recipient_account_code[]"
                                                                placeholder="{{ __('Account Code') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                oninput="filterAccountCodes(this, 'recipient')"
                                                                autocomplete="off" />
                                                            <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="RecipientAccountCodeDropdown">
                                                                <!-- Suggestions will appear here -->
                                                            </div>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.textarea
                                                                name="recipient_description[]"
                                                                placeholder="{{ __('Description') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                autocomplete="off"></x-form.textarea>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.textarea
                                                                name="recipient_programs[]"
                                                                placeholder="{{ __('Program') }}"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                autocomplete="off"></x-form.textarea>
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                type="text"
                                                                name="recipient_balance[]"
                                                                oninput="formatCurrency(this)"
                                                                placeholder="{{ __('Balance') }}"
                                                                autocomplete="off"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                                readonly />
                                                        </td>
                                                        <td class="px-1 py-2">
                                                            <x-form.input
                                                                type="text"
                                                                name="recipient_amount[]"
                                                                placeholder="{{ __('Amount') }}"
                                                                autocomplete="off"
                                                                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                                                        </td>
                                                        <td class="px-1 py-2 text-center">
                                                            <button type="button" onclick="deleteRow(this, 'recipient')" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <!-- Additional rows can be dynamically added using JavaScript -->
                                                </tbody>
                                            </table>
                                            <!-- Add Button for Dynamic Rows -->
                                            <div class="sm:col-span-6 mt-2 justify-end">
                                                <button type="button" onclick="addRecipientRow()" class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                                                    <i class="fas fa-plus text-sm mr-2"></i>
                                                    {{ __('Add Row') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateRealignmentForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save mr-2 ml-1"></i> {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateRealignmentModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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
    function attachTotalAmountListeners() {
        document.querySelectorAll('[name="source_amount[]"]').forEach(field => {
            field.removeEventListener('input', calculateTotalSourceAmount);
            field.removeEventListener('blur', calculateTotalSourceAmount);
            field.addEventListener('input', calculateTotalSourceAmount);
            field.addEventListener('blur', calculateTotalSourceAmount);
        });
        document.querySelectorAll('[name="recipient_amount[]"]').forEach(field => {
            field.removeEventListener('input', calculateTotalRecipientAmount);
            field.removeEventListener('blur', calculateTotalRecipientAmount);
            field.addEventListener('input', calculateTotalRecipientAmount);
            field.addEventListener('blur', calculateTotalRecipientAmount);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateTotalSourceAmount();
        calculateTotalRecipientAmount();
        attachTotalAmountListeners();
    });
    // Section Select Show/Hide Logic
    document.addEventListener('DOMContentLoaded', function() {
        const sectionSelect = document.getElementById('section_select');
        const sourceSection = document.getElementById('source_section');
        const recipientSection = document.getElementById('recipient_section');
        function updateSectionVisibility() {
            const val = sectionSelect.value;
            if (val === 'both') {
                sourceSection.style.display = '';
                recipientSection.style.display = '';
            } else if (val === 'source') {
                sourceSection.style.display = '';
                recipientSection.style.display = 'none';
            } else if (val === 'recipient') {
                sourceSection.style.display = 'none';
                recipientSection.style.display = '';
            }
        }
        sectionSelect.addEventListener('change', updateSectionVisibility);
        updateSectionVisibility();
    });

    //Open Create Modal
    function openCreateRealignmentModal() {
        closeAllDropdowns();
        document.getElementById('createRealignmentModal').classList.remove('hidden');
    }
    //Close Create Modal
    function closeCreateRealignmentModal() {
        document.getElementById('createRealignmentModal').classList.add('hidden');
    }

    const officeAllotmentClasses = @json($officeAllotmentClassesJs);
    const appropriations = @json($appropriationsJs);

    // --- Filter Office Allotment Classes for Source/Recipient ---
    function filterOfficeAllotmentClasses(type) {
        const input = document.getElementById(type + "_office_allotment_class");
        const dropdown = document.getElementById((type.charAt(0).toUpperCase() + type.slice(1)) + "OfficeAllotmentClassDropdown");
        const filter = input.value.toLowerCase();
        dropdown.innerHTML = "";
        if (!filter) {
            dropdown.classList.add("hidden");
            return;
        }
        let filteredClasses;
        if (type === 'recipient') {
            // If Source section is hidden or not selected, show all recipient options
            const sourceSection = document.getElementById('source_section');
            const sectionSelect = document.getElementById('section_select');
            const sectionValue = sectionSelect ? sectionSelect.value : 'both';
            let showAll = false;
            if (sectionValue === 'recipient' || (sourceSection && sourceSection.style.display === 'none')) {
                showAll = true;
            }
            if (showAll) {
                filteredClasses = officeAllotmentClasses.filter(item => item.name.toLowerCase().includes(filter));
            } else {
                // Get the selected source OfficeAllotmentClass's allotment class
                const sourceOACId = document.getElementById('source_office_allotment_class_id').value;
                let sourceOAC = officeAllotmentClasses.find(item => String(item.id) === String(sourceOACId));
                let sourceAllotmentClass = null;
                if (sourceOAC && sourceOAC.name) {
                    // Extract allotment class from the name (format: "ABBR - CLASS")
                    const parts = sourceOAC.name.split(' - ');
                    if (parts.length > 1) {
                        sourceAllotmentClass = parts[1].trim();
                    }
                }
                // Only show recipient options with the same allotment class
                filteredClasses = officeAllotmentClasses.filter(item => {
                    const parts = item.name.split(' - ');
                    const itemAllotmentClass = parts.length > 1 ? parts[1].trim() : '';
                    return item.name.toLowerCase().includes(filter) && itemAllotmentClass === sourceAllotmentClass;
                });
            }
        } else {
            filteredClasses = officeAllotmentClasses.filter(item => item.name.toLowerCase().includes(filter));
        }
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
                document.getElementById(type + "_office_allotment_class_id").value = item.id;
                dropdown.classList.add("hidden");

                // Reset all account code, description, program, balance, and amount fields in the relevant table
                let tableId = type === 'source' ? 'source_programs_table' : 'recipient_programs_table';
                const tableBody = document.getElementById(tableId).getElementsByTagName('tbody')[0];
                if (tableBody) {
                    Array.from(tableBody.querySelectorAll('tr')).forEach(row => {
                        const accountCode = row.querySelector(`[name="${type}_account_code[]"]`);
                        const description = row.querySelector(`[name="${type}_description[]"]`);
                        const program = row.querySelector(`[name="${type}_programs[]"]`);
                        const balance = row.querySelector(`[name="${type}_balance_from_allotment[]"], [name="${type}_balance[]"]`);
                        if (accountCode) accountCode.value = '';
                        if (description) description.value = '';
                        if (program) program.value = '';
                    });
                }

                // If selecting source, set recipient to the same value if it matches the filter
                if (type === 'source') {
                    const recipientInput = document.getElementById('recipient_office_allotment_class');
                    const recipientIdInput = document.getElementById('recipient_office_allotment_class_id');
                    if (recipientInput && recipientIdInput) {
                        const sourceParts = item.name.split(' - ');
                        const sourceOffice = sourceParts[0]?.trim() || '';
                        const sourceAllotmentClass = sourceParts[1]?.trim() || '';

                        // Default to same office & class
                        let recipientMatch = officeAllotmentClasses.find(oac => {
                            const parts = oac.name.split(' - ');
                            return parts.length > 1 && 
                                parts[0].trim() === sourceOffice && 
                                parts[1].trim() === sourceAllotmentClass;
                        });

                        // Fallback: match other offices with same allotment class
                        if (!recipientMatch) {
                            recipientMatch = officeAllotmentClasses.find(oac => {
                                const parts = oac.name.split(' - ');
                                return parts.length > 1 && parts[1].trim() === sourceAllotmentClass;
                            });
                        }

                        if (recipientMatch) {
                            recipientInput.value = recipientMatch.name;
                            recipientIdInput.value = recipientMatch.id;
                        } else {
                            recipientInput.value = '';
                            recipientIdInput.value = '';
                        }

                        // Also reset recipient table fields
                        const recTableBody = document.getElementById('recipient_programs_table').getElementsByTagName('tbody')[0];
                        if (recTableBody) {
                            Array.from(recTableBody.querySelectorAll('tr')).forEach(row => {
                                const accountCode = row.querySelector('[name="recipient_account_code[]"]');
                                const description = row.querySelector('[name="recipient_description[]"]');
                                const program = row.querySelector('[name="recipient_programs[]"]');
                                if (accountCode) accountCode.value = '';
                                if (description) accountCode.value = '';
                                if (program) program.value = '';
                            });
                        }
                    }
                }
            };
            dropdown.appendChild(option);
        });
        dropdown.classList.remove("hidden");
    }

    // --- Filter Account Codes for Source/Recipient ---
    function filterAccountCodes(inputElement, type) {
        const row = inputElement.closest('tr');
        let officeAllotmentClassId = null;
        // For recipient, get the office_allotment_class_id for this row (should be per-row for correct filtering)
        if (type === 'recipient') {
            // Try to get from hidden input in the row, fallback to global recipient_office_allotment_class_id
            let rowOacField = row.querySelector('[name="recipient_office_allotment_class_id[]"]');
            if (!rowOacField) {
                // fallback: try to get from global hidden input (single recipient section)
                rowOacField = document.getElementById('recipient_office_allotment_class_id');
            }
            officeAllotmentClassId = rowOacField ? rowOacField.value : '';
        } else {
            officeAllotmentClassId = document.getElementById(type + '_office_allotment_class_id').value;
        }
        const dropdown = inputElement.nextElementSibling;
        const filter = inputElement.value.toLowerCase();
        dropdown.innerHTML = '';
        if (!filter || !officeAllotmentClassId) {
            dropdown.classList.add('hidden');
            return;
        }
        // --- Exclude source pairs if type is recipient ---
        let excludePairs = [];
        if (type === 'recipient') {
            // Collect all (office_allotment_class_id, account_code) pairs from source rows
            document.querySelectorAll('#source_programs_table tbody tr').forEach(sourceRow => {
                // Get the office_allotment_class_id for this source row
                let oac = '';
                // Try to get from hidden input in the row, fallback to global
                let oacField = sourceRow.querySelector('[name="source_office_allotment_class_id[]"]');
                if (!oacField) {
                    oacField = document.getElementById('source_office_allotment_class_id');
                }
                oac = oacField ? oacField.value.trim() : '';
                const accCodeField = sourceRow.querySelector('[name="source_account_code[]"]');
                const accCode = accCodeField ? accCodeField.value.trim() : '';
                if (oac && accCode) {
                    excludePairs.push(oac + '|' + accCode);
                }
            });
        }
        const filteredCodes = appropriations.filter(item => {
            // Only show account codes for the selected office_allotment_class_id
            if (String(item.office_allotment_class_id) !== String(officeAllotmentClassId)) return false;
            if (!item.account_code.toLowerCase().includes(filter)) return false;
            // For recipient, exclude if this (office_allotment_class_id, account_code) is in source
            if (type === 'recipient' && excludePairs.includes(String(item.office_allotment_class_id) + '|' + item.account_code)) return false;
            return true;
        });
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
                populateFields(inputElement, item, type);
                calculateBalance(inputElement, item, type);
                // Set appropriation id in hidden input
                let hiddenInput = row.querySelector('[name="' + type + '_appropriations_id[]"]');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = type + '_appropriations_id[]';
                    row.appendChild(hiddenInput);
                }
                hiddenInput.value = item.id;
                dropdown.classList.add('hidden');
            };
            dropdown.appendChild(option);
        });
        dropdown.classList.remove('hidden');
    }

    // --- Populate Related Fields (Description/Program) ---
    function populateFields(inputElement, item, type) {
        const row = inputElement.closest('tr');
        const programField = row.querySelector(`[name="${type}_programs[]"]`);
        const descriptionField = row.querySelector(`[name="${type}_description[]"]`);
        if (programField) programField.value = item.program ? item.program.trim() : '';
        if (descriptionField) descriptionField.value = item.description ? item.description.trim() : '';
    }

    // --- Calculate and Populate Balance ---
    function calculateBalance(inputElement, item, type) {
        const row = inputElement.closest('tr');
        const balanceField = row.querySelector(`[name="${type}_balance_from_allotment[]"]`);
        const balance = parseFloat(item.balance || 0);
        const formattedBalance = balance.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        if (balanceField) balanceField.value = formattedBalance;
    }

    // --- Hide Dropdowns on Click Outside ---
    document.addEventListener("click", function(event) {
        ['SourceOfficeAllotmentClassDropdown', 'RecipientOfficeAllotmentClassDropdown'].forEach(function(id) {
            const dropdown = document.getElementById(id);
            if (dropdown && !event.target.closest('#' + id.replace('Dropdown', ''))) {
                dropdown.classList.add("hidden");
            }
        });
    });

    //Delete Row Functionality
    let rowToDelete = null;
    let rowToDeleteTableType = null;
    function deleteRow(button, tableType) {
        rowToDelete = button.closest('tr');
        rowToDeleteTableType = tableType;
        document.getElementById('deleteConfirmModal').classList.remove('hidden');
    }
    document.getElementById('confirmDeleteBtn').onclick = function() {
        if (!rowToDelete || !rowToDeleteTableType) return;
        const table = rowToDelete.parentNode;
        if (table.rows.length > 1) {
            table.removeChild(rowToDelete);
            if (rowToDeleteTableType === 'source') {
                document.getElementById('sourceTableMessage').classList.add('hidden');
            } else if (rowToDeleteTableType === 'recipient') {
                document.getElementById('recipientTableMessage').classList.add('hidden');
            }
        } else {
            if (rowToDeleteTableType === 'source') {
                document.getElementById('sourceTableMessage').textContent = 'At least one row must remain in the table.';
                document.getElementById('sourceTableMessage').classList.remove('hidden');
            } else if (rowToDeleteTableType === 'recipient') {
                document.getElementById('recipientTableMessage').textContent = 'At least one row must remain in the table.';
                document.getElementById('recipientTableMessage').classList.remove('hidden');
            }
        }
        rowToDelete = null;
        rowToDeleteTableType = null;
        document.getElementById('deleteConfirmModal').classList.add('hidden');
        attachTotalAmountListeners();
        calculateTotalSourceAmount();
        calculateTotalRecipientAmount();
    };
    document.getElementById('cancelDeleteBtn').onclick = function() {
        rowToDelete = null;
        rowToDeleteTableType = null;
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    };

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
                element.classList.remove("text-gray-400");
                element.classList.add("text-gray-900", "dark:text-gray-100");
            } else {
                element.classList.remove("text-gray-900", "dark:text-gray-100");
                element.classList.add("text-gray-400");
            }

            // Apply specific styles for readonly inputs
            if (element.hasAttribute("readonly")) {
                element.classList.add("text-gray-900", "dark:text-gray-400"); // Add styles for readonly inputs
            }

            // Apply specific styles for disabled select fields
            if (element.tagName === "SELECT" && element.disabled) {
                element.classList.add("text-gray-700", "dark:text-gray-400"); // Add styles for disabled selects
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
    });

    // Add Recipient Row
    function addRecipientRow() {
        const table = document.getElementById('recipient_programs_table').getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);

        // Remove all id attributes in the cloned row to avoid duplicate element conflicts
        Array.from(newRow.querySelectorAll('[id]')).forEach(el => el.removeAttribute('id'));

        // Get current selected values
        const currentOfficeId = document.getElementById('recipient_office_allotment_class_id')?.value || '';
        const currentAppropriationId = document.getElementById('recipient_appropriations_id')?.value || '';

        // Clear inputs
        Array.from(newRow.querySelectorAll('input, textarea')).forEach(input => {
            if (input.type === 'text' || input.tagName === 'TEXTAREA') {
                input.value = '';
            }
            if (input.type === 'hidden') {
                input.value = '';
            }
        });

        // Remove any error messages
        Array.from(newRow.querySelectorAll('.text-red-500')).forEach(el => el.textContent = '');

        // Inject new hidden inputs with current selected office/appropriation
        const hiddenOfficeInput = document.createElement('input');
        hiddenOfficeInput.type = 'hidden';
        hiddenOfficeInput.name = 'recipient_office_allotment_class_id[]';
        hiddenOfficeInput.value = currentOfficeId;

        const hiddenAppropriationInput = document.createElement('input');
        hiddenAppropriationInput.type = 'hidden';
        hiddenAppropriationInput.name = 'recipient_appropriations_id[]';
        hiddenAppropriationInput.value = currentAppropriationId;

        // Append hidden inputs to one of the cells (any cell is okay)
        newRow.cells[0].appendChild(hiddenOfficeInput);
        newRow.cells[0].appendChild(hiddenAppropriationInput);

        table.appendChild(newRow);
        updateRecipientBalancesFromSource();
        attachAmountBalanceListeners();
        attachRecipientAmountListeners();
        enforceRecipientAmountsNotExceedBalance();
        attachTotalAmountListeners();
        calculateTotalRecipientAmount();
    }

    // Add Source Row
    function addSourceRow() {
        const table = document.getElementById('source_programs_table').getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);

        // Remove all id attributes in the cloned row to avoid duplicate element conflicts
        Array.from(newRow.querySelectorAll('[id]')).forEach(el => el.removeAttribute('id'));

        // Get the currently selected values
        const currentOfficeId = document.getElementById('source_office_allotment_class_id')?.value || '';
        const currentAppropriationId = document.getElementById('source_appropriations_id')?.value || '';

        // Clear input values in the new row
        Array.from(newRow.querySelectorAll('input, textarea')).forEach(input => {
            if (input.type === 'text' || input.tagName === 'TEXTAREA') {
                input.value = '';
            }
            if (input.type === 'hidden') {
                input.value = '';
            }
        });

        // Remove any error messages
        Array.from(newRow.querySelectorAll('.text-red-500')).forEach(el => el.textContent = '');

        // Inject new hidden inputs for office and appropriation ID
        const hiddenOfficeInput = document.createElement('input');
        hiddenOfficeInput.type = 'hidden';
        hiddenOfficeInput.name = 'source_office_allotment_class_id[]';
        hiddenOfficeInput.value = currentOfficeId;

        const hiddenAppropriationInput = document.createElement('input');
        hiddenAppropriationInput.type = 'hidden';
        hiddenAppropriationInput.name = 'source_appropriations_id[]';
        hiddenAppropriationInput.value = currentAppropriationId;

        // Append hidden inputs to the first cell of the row (can be any cell)
        newRow.cells[0].appendChild(hiddenOfficeInput);
        newRow.cells[0].appendChild(hiddenAppropriationInput);

        // Append the new row to the table
        table.appendChild(newRow);

        // Re-attach necessary listeners and validation
        attachSourceAmountListeners();
        updateRecipientBalancesFromSource();
        attachAmountBalanceListeners();
        attachRecipientAmountListeners();
        enforceRecipientAmountsNotExceedBalance();
        attachTotalAmountListeners();
        calculateTotalSourceAmount();
    }
    // Delete Source Row
    function deleteSourceRow(btn) {
        const row = btn.closest('tr');
        const table = row.parentNode;
        if (table.rows.length > 1) {
            table.removeChild(row);
            document.getElementById('sourceTableMessage').classList.add('hidden');
        } else {
            document.getElementById('sourceTableMessage').textContent = 'At least one row must remain in the table.';
            document.getElementById('sourceTableMessage').classList.remove('hidden');
        }
        updateRecipientBalancesFromSource();
        enforceRecipientAmountsNotExceedBalance();
        attachTotalAmountListeners();
        calculateTotalSourceAmount();
    }

    // --- Calculate and Populate Recipient Balance as sum of all source_amounts ---
    function updateRecipientBalancesFromSource() {
        const sectionSelect = document.getElementById('section_select');
        const sectionValue = sectionSelect ? sectionSelect.value : 'both';
        const recipientBalanceFields = document.querySelectorAll('[name="recipient_balance[]"]');
        if (sectionValue === 'recipient') {
            recipientBalanceFields.forEach(field => {
                field.disabled = true;
            });
            // Do not calculate or populate recipient balance
            return;
        } else {
            recipientBalanceFields.forEach(field => {
                field.disabled = false;
            });
            // Sum all source_amount values
            let totalSourceAmount = 0;
            document.querySelectorAll('[name="source_amount[]"]').forEach(field => {
                let val = parseFloat((field.value || '').replace(/,/g, ''));
                if (!isNaN(val)) totalSourceAmount += val;
            });
            // Set all recipient_balance fields to this total
            recipientBalanceFields.forEach(field => {
                field.value = totalSourceAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            });
        }
    }

    // Attach to source_amount input/blur and row add/delete
    function attachSourceAmountListeners() {
        document.querySelectorAll('[name="source_amount[]"]').forEach(field => {
            field.removeEventListener('input', updateRecipientBalancesFromSource);
            field.addEventListener('input', updateRecipientBalancesFromSource);
            field.removeEventListener('blur', updateRecipientBalancesFromSource);
            field.addEventListener('blur', updateRecipientBalancesFromSource);
        });
    }

    // --- Enforce recipient_amounts do not exceed total recipient_balance ---
    function enforceRecipientAmountsNotExceedBalance() {
        const sectionSelect = document.getElementById('section_select');
        if (!sectionSelect) return;
        const rows = Array.from(document.querySelectorAll('#recipient_programs_table tbody tr'));
        if (sectionSelect.value !== 'both') {
            // Hide infoSpan for all rows if not 'both'
            rows.forEach(row => {
                let infoSpan = row.querySelector('.recipient-remaining-info');
                if (infoSpan) infoSpan.style.display = 'none';
            });
            return;
        }
        // 'both' selected: show and update infoSpan
        let recipientBalanceField = document.querySelector('[name="recipient_balance[]"]');
        let totalBalance = recipientBalanceField ? parseFloat((recipientBalanceField.value || '').replace(/,/g, '')) : 0;
        if (isNaN(totalBalance)) totalBalance = 0;
        let runningTotal = 0;
        rows.forEach((row, idx) => {
            const amountField = row.querySelector('[name="recipient_amount[]"]');
            const balanceField = row.querySelector('[name="recipient_balance[]"]');
            if (!amountField || !balanceField) return;
            let val = parseFloat((amountField.value || '').replace(/,/g, ''));
            if (isNaN(val)) val = 0;
            let maxAllowed = totalBalance - runningTotal;
            // Update balance field for this row
            balanceField.value = maxAllowed.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (val > maxAllowed) {
                amountField.value = maxAllowed > 0 ? String(Math.floor(maxAllowed)) : '';
                val = maxAllowed;
            }
            runningTotal += val;
            let infoSpan = row.querySelector('.recipient-remaining-info');
            if (!infoSpan) {
                infoSpan = document.createElement('span');
                infoSpan.className = 'recipient-remaining-info text-xs text-gray-500 ml-2';
                amountField.parentNode.appendChild(infoSpan);
            }
            infoSpan.style.display = '';
            infoSpan.textContent = `Max: ${maxAllowed.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        });
    }

    // Attach to recipient_amount input/blur and row add/delete
    function attachRecipientAmountListeners() {
        document.querySelectorAll('[name="recipient_amount[]"]').forEach(field => {
            field.removeEventListener('input', enforceRecipientAmountsNotExceedBalance);
            field.addEventListener('input', enforceRecipientAmountsNotExceedBalance);
            field.removeEventListener('blur', enforceRecipientAmountsNotExceedBalance);
            field.addEventListener('blur', enforceRecipientAmountsNotExceedBalance);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        attachSourceAmountListeners();
        updateRecipientBalancesFromSource();
        attachAmountBalanceListeners();
        attachRecipientAmountListeners();
        enforceRecipientAmountsNotExceedBalance();
        // Also update recipient balance visibility on section change
        const sectionSelect = document.getElementById('section_select');
        if (sectionSelect) {
            sectionSelect.addEventListener('change', updateRecipientBalancesFromSource);
        }
    });

    function validateCreateRealignmentForm() {
        const form = document.getElementById('createRealignmentForm');
        let isValid = true;

        // Clear previous error messages
        document.querySelectorAll('.text-red-500').forEach(error => error.textContent = '');
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
            el.classList.add('border-gray-300');
        });
        document.querySelectorAll('.amount-error-message').forEach(el => el.remove());


        // Section selection logic
        const sectionSelect = document.getElementById('section_select');
        const sectionValue = sectionSelect ? sectionSelect.value : 'both';

        // Validate Source Office and Allotment Class if Source or Both
        if (sectionValue === 'source' || sectionValue === 'both') {
            const sourceOfficeAllotmentClass = document.getElementById('source_office_allotment_class');
            const sourceOfficeAllotmentClassId = document.getElementById('source_office_allotment_class_id');
            if (!sourceOfficeAllotmentClass || !sourceOfficeAllotmentClassId || !sourceOfficeAllotmentClass.value.trim() || !sourceOfficeAllotmentClassId.value.trim()) {
                document.getElementById('SourceOfficeAllotmentClassError').textContent = 'Source Office and Allotment Class is required.';
                isValid = false;
            }
        }

        // Validate Recipient Office and Allotment Class if Recipient or Both
        if (sectionValue === 'recipient' || sectionValue === 'both') {
            const recipientOfficeAllotmentClass = document.getElementById('recipient_office_allotment_class');
            const recipientOfficeAllotmentClassId = document.getElementById('recipient_office_allotment_class_id');
            if (!recipientOfficeAllotmentClass || !recipientOfficeAllotmentClassId || !recipientOfficeAllotmentClass.value.trim() || !recipientOfficeAllotmentClassId.value.trim()) {
                document.getElementById('RecipientOfficeAllotmentClassError').textContent = 'Recipient Office and Allotment Class is required.';
                isValid = false;
            }
        }

        // Validate Basis
        const basis = document.getElementById('basis');
        if (!basis.value.trim()) {
            document.getElementById('basisError').textContent = 'Basis is required.';
            isValid = false;
        }

        // Validate Realignment No.
        const realignmentNo = document.getElementById('realignment_no');
        if (!realignmentNo.value.trim()) {
            document.getElementById('realignment_noError').textContent = 'Realignment No. field is required.';
            isValid = false;
        } else {
            realignmentNo.classList.remove('border-red-500');
            realignmentNo.classList.add('border-gray-300');
        }


        // --- Validate Source Table Rows if Source or Both ---
        if (sectionValue === 'source' || sectionValue === 'both') {
            const sourceTableBody = document.querySelector('#source_programs_table tbody');
            const sourceRows = Array.from(sourceTableBody.rows);
            if (sourceRows.length === 0) {
                const tableMessage = document.getElementById('sourceTableMessage');
                tableMessage.textContent = 'At least one row is required in the Source table.';
                tableMessage.classList.remove('hidden');
                isValid = false;
            } else {
                document.getElementById('sourceTableMessage').classList.add('hidden');
            }
            sourceRows.forEach((row, idx) => {
                const amountField = row.querySelector('[name="source_amount[]"]');
                const balanceField = row.querySelector('[name="source_balance_from_allotment[]"]');
                let amount = amountField ? parseFloat(amountField.value.replace(/,/g, '')) : 0;
                let balance = balanceField ? parseFloat(balanceField.value.replace(/,/g, '')) : 0;
                // Validate balance is a number and not negative
                if (balanceField && (isNaN(balance) || balance < 0)) {
                    balanceField.classList.add('border-red-500');
                    balanceField.classList.remove('border-gray-300');
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'text-red-500 text-xs mt-1 amount-error-message';
                    errorMessage.textContent = `Row ${idx + 1}: Balance from Allotment must be a non-negative number.`;
                    balanceField.parentNode.appendChild(errorMessage);
                    isValid = false;
                }
                // Validate amount
                if (amountField) {
                    if (isNaN(amount) || amount <= 0) {
                        amountField.classList.add('border-red-500');
                        amountField.classList.remove('border-gray-300');
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'text-red-500 text-xs mt-1 amount-error-message';
                        errorMessage.textContent = `Row ${idx + 1}: Amount must be greater than 0.`;
                        amountField.parentNode.appendChild(errorMessage);
                        isValid = false;
                    } else if (!isNaN(balance) && amount > balance) {
                        amountField.classList.add('border-red-500');
                        amountField.classList.remove('border-gray-300');
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'text-red-500 text-xs mt-1 amount-error-message';
                        errorMessage.textContent = `Row ${idx + 1}: Amount cannot exceed Balance from Allotment.`;
                        amountField.parentNode.appendChild(errorMessage);
                        isValid = false;
                    } else {
                        amountField.classList.remove('border-red-500');
                        amountField.classList.add('border-gray-300');
                    }
                }
            });
        }

        // --- Validate Recipient Table Rows if Recipient or Both ---
        if (sectionValue === 'recipient' || sectionValue === 'both') {
            const recipientTableBody = document.querySelector('#recipient_programs_table tbody');
            const recipientRows = Array.from(recipientTableBody.rows);
            if (recipientRows.length === 0) {
                const tableMessage = document.getElementById('recipientTableMessage');
                tableMessage.textContent = 'At least one row is required in the Recipient table.';
                tableMessage.classList.remove('hidden');
                isValid = false;
            } else {
                document.getElementById('recipientTableMessage').classList.add('hidden');
            }

            // --- Collect Source (office_allotment_class_id, account_code) pairs ---
            let sourcePairs = [];
            if (sectionValue === 'source' || sectionValue === 'both') {
                const sourceTableBody = document.querySelector('#source_programs_table tbody');
                const sourceRows = Array.from(sourceTableBody.rows);
                sourceRows.forEach((row) => {
                    const oacField = row.querySelector('[name="source_office_allotment_class_id[]"]');
                    const accCodeField = row.querySelector('[name="source_account_code[]"]');
                    const oac = oacField ? oacField.value.trim() : '';
                    const accCode = accCodeField ? accCodeField.value.trim() : '';
                    if (oac && accCode) {
                        sourcePairs.push(oac + '|' + accCode);
                    }
                });
            }

            recipientRows.forEach((row, idx) => {
                const amountField = row.querySelector('[name="recipient_amount[]"]');
                const balanceField = row.querySelector('[name="recipient_balance[]"]');
                let amount = amountField ? parseFloat(amountField.value.replace(/,/g, '')) : 0;
                let balance = balanceField ? parseFloat(balanceField.value.replace(/,/g, '')) : 0;
                // Validate balance is a number and not negative ONLY if sectionValue is 'both'
                if (sectionValue === 'both' && balanceField && (isNaN(balance) || balance < 0)) {
                    balanceField.classList.add('border-red-500');
                    balanceField.classList.remove('border-gray-300');
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'text-red-500 text-xs mt-1 amount-error-message';
                    errorMessage.textContent = `Row ${idx + 1}: Balance from Allotment must be a non-negative number.`;
                    balanceField.parentNode.appendChild(errorMessage);
                    isValid = false;
                }
                // Validate amount
                if (amountField) {
                    if (isNaN(amount) || amount <= 0) {
                        amountField.classList.add('border-red-500');
                        amountField.classList.remove('border-gray-300');
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'text-red-500 text-xs mt-1 amount-error-message';
                        errorMessage.textContent = `Row ${idx + 1}: Amount must be greater than 0.`;
                        amountField.parentNode.appendChild(errorMessage);
                        isValid = false;
                    } else {
                        // Only enforce amount <= balance if sectionValue is 'both'
                        if (sectionValue === 'both' && !isNaN(balance) && amount > balance) {
                            amountField.classList.add('border-red-500');
                            amountField.classList.remove('border-gray-300');
                            const errorMessage = document.createElement('div');
                            errorMessage.className = 'text-red-500 text-xs mt-1 amount-error-message';
                            errorMessage.textContent = `Row ${idx + 1}: Amount cannot exceed Balance from Allotment.`;
                            amountField.parentNode.appendChild(errorMessage);
                            isValid = false;
                        } else {
                            amountField.classList.remove('border-red-500');
                            amountField.classList.add('border-gray-300');
                        }
                    }
                }
                // --- Check for same office_allotment_class_id and account_code as any source row ---
                if (sourcePairs.length > 0) {
                    const oacField = row.querySelector('[name="recipient_office_allotment_class_id[]"]');
                    const accCodeField = row.querySelector('[name="recipient_account_code[]"]');
                    const oac = oacField ? oacField.value.trim() : '';
                    const accCode = accCodeField ? accCodeField.value.trim() : '';
                    if (oac && accCode && sourcePairs.includes(oac + '|' + accCode)) {
                        if (accCodeField) {
                            accCodeField.classList.add('border-red-500');
                            accCodeField.classList.remove('border-gray-300');
                            const errorMessage = document.createElement('div');
                            errorMessage.className = 'text-red-500 text-xs mt-1 amount-error-message';
                            errorMessage.textContent = `Row ${idx + 1}: Cannot realign to the same Office/Allotment Class and Account Code as a source row.`;
                            accCodeField.parentNode.appendChild(errorMessage);
                        }
                        isValid = false;
                    }
                }
            });
        }

        // If the form is valid, submit it
        if (isValid) {
            form.submit();
        }
    }

    function enforceAmountNotExceedBalance(amountField, balanceField) {
    if (!amountField || !balanceField) return;

    let amount = parseFloat((amountField.value || '').replace(/,/g, ''));
    let balance = parseFloat((balanceField.value || '').replace(/,/g, ''));

    if (!isNaN(amount) && !isNaN(balance) && amount > balance) {
        amountField.value = balance > 0 ? balance.toFixed(2) : '';
    }
}

    // Attach event listeners to enforce this on input/blur for both tables
    function attachAmountBalanceListeners() {
        // Source
        document.querySelectorAll('#source_programs_table tbody tr').forEach(row => {
            const amountField = row.querySelector('[name="source_amount[]"]');
            const balanceField = row.querySelector('[name="source_balance_from_allotment[]"]');
            if (amountField && balanceField) {
                amountField.removeEventListener('input', amountField._enforceListener);
                amountField.removeEventListener('blur', amountField._enforceListener);
                amountField._enforceListener = function() {
                    enforceAmountNotExceedBalance(amountField, balanceField);
                };
                amountField.addEventListener('input', amountField._enforceListener);
                amountField.addEventListener('blur', amountField._enforceListener);
            }
        });

        // Recipient
        const sectionSelect = document.getElementById('section_select');
        const sectionValue = sectionSelect ? sectionSelect.value : 'both';
        document.querySelectorAll('#recipient_programs_table tbody tr').forEach(row => {
            const amountField = row.querySelector('[name="recipient_amount[]"]');
            const balanceField = row.querySelector('[name="recipient_balance[]"]');
            if (amountField && balanceField) {
                // Always remove any previous listeners
                if (amountField._enforceListener) {
                    amountField.removeEventListener('input', amountField._enforceListener);
                    amountField.removeEventListener('blur', amountField._enforceListener);
                }
                if (sectionValue === 'both') {
                    // Set max attribute to balance value
                    let balance = parseFloat((balanceField.value || '').replace(/,/g, ''));
                    if (!isNaN(balance)) {
                        amountField.setAttribute('max', balance);
                    } else {
                        amountField.removeAttribute('max');
                    }
                    amountField._enforceListener = function() {
                        enforceAmountNotExceedBalance(amountField, balanceField);
                    };
                    amountField.addEventListener('input', amountField._enforceListener);
                    amountField.addEventListener('blur', amountField._enforceListener);
                } else if (sectionValue === 'recipient') {
                    // Remove max and enforcement for recipient-only
                    amountField.removeAttribute('max');
                    amountField._enforceListener = null;
                } else {
                    // For source-only, also remove max and enforcement
                    amountField.removeAttribute('max');
                    amountField._enforceListener = null;
                }
            }
        });
    }

    // --- Calculate and update total amounts for Source and Recipient tables ---
    function calculateTotalSourceAmount() {
        const amountFields = document.querySelectorAll('[name="source_amount[]"]');
        let total = 0;
        amountFields.forEach(field => {
            const value = parseFloat((field.value || '').replace(/,/g, ''));
            if (!isNaN(value)) total += value;
        });
        const totalElement = document.getElementById('totalSourceAmount');
        if (totalElement) {
            totalElement.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    function calculateTotalRecipientAmount() {
        const amountFields = document.querySelectorAll('[name="recipient_amount[]"]');
        let total = 0;
        amountFields.forEach(field => {
            const value = parseFloat((field.value || '').replace(/,/g, ''));
            if (!isNaN(value)) total += value;
        });
        const totalElement = document.getElementById('totalRecipientAmount');
        if (totalElement) {
            totalElement.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        attachAmountBalanceListeners();
    });
    // If rows are dynamically added, re-attach listeners
    function addSourceRow() {
        const table = document.getElementById('source_programs_table').getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);
        // Remove all id attributes in the cloned row to avoid duplicate element conflicts
        Array.from(newRow.querySelectorAll('[id]')).forEach(el => el.removeAttribute('id'));
        // Clear input values in the new row
        Array.from(newRow.querySelectorAll('input, textarea')).forEach(input => {
            if (input.type === 'text' || input.tagName === 'TEXTAREA') {
                input.value = '';
            }
          // If hidden input for source_office_allotment_class_id or source_appropriations_id, clear as well
          if (input.type === 'hidden') {
            input.value = '';
          }
        });
        // Remove any error messages
        Array.from(newRow.querySelectorAll('.text-red-500')).forEach(el => el.textContent = '');
    table.appendChild(newRow);
    attachSourceAmountListeners();
    updateRecipientBalancesFromSource();
    attachAmountBalanceListeners(); // Re-attach listeners to all rows
    attachTotalAmountListeners(); // Re-attach total listeners to all rows
    attachRecipientAmountListeners();
    enforceRecipientAmountsNotExceedBalance();
    calculateTotalSourceAmount();
    }
    function addRecipientRow() {
        const table = document.getElementById('recipient_programs_table').getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);
        // Remove all id attributes in the cloned row to avoid duplicate element conflicts
        Array.from(newRow.querySelectorAll('[id]')).forEach(el => el.removeAttribute('id'));
        // Clear input values in the new row
        Array.from(newRow.querySelectorAll('input, textarea')).forEach(input => {
            if (input.type === 'text' || input.tagName === 'TEXTAREA') {
                input.value = '';
            }
        });
        // Remove any error messages
        Array.from(newRow.querySelectorAll('.text-red-500')).forEach(el => el.textContent = '');
    table.appendChild(newRow);
    updateRecipientBalancesFromSource();
    attachAmountBalanceListeners(); // Re-attach listeners to all rows
    attachTotalAmountListeners(); // Re-attach total listeners to all rows
    attachRecipientAmountListeners();
    enforceRecipientAmountsNotExceedBalance();
    calculateTotalRecipientAmount();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sectionSelect = document.getElementById('section_select');
        sectionSelect.addEventListener('change', function() {
            // Reset office allotment class fields for Source
            const sourceOfficeAllotmentClass = document.getElementById('source_office_allotment_class');
            const sourceOfficeAllotmentClassId = document.getElementById('source_office_allotment_class_id');
            if (sourceOfficeAllotmentClass) sourceOfficeAllotmentClass.value = '';
            if (sourceOfficeAllotmentClassId) sourceOfficeAllotmentClassId.value = '';

            // Reset office allotment class fields for Recipient
            const recipientOfficeAllotmentClass = document.getElementById('recipient_office_allotment_class');
            const recipientOfficeAllotmentClassId = document.getElementById('recipient_office_allotment_class_id');
            if (recipientOfficeAllotmentClass) recipientOfficeAllotmentClass.value = '';
            if (recipientOfficeAllotmentClassId) recipientOfficeAllotmentClassId.value = '';

            // Reset all rows in Source table except the first
            const sourceTableBody = document.querySelector('#source_programs_table tbody');
            while (sourceTableBody.rows.length > 1) {
                sourceTableBody.deleteRow(1);
            }
            // Reset all input values in Source table
            Array.from(sourceTableBody.rows).forEach(row => {
                Array.from(row.querySelectorAll('input, textarea')).forEach(input => {
                    if (input.type === 'text' || input.tagName === 'TEXTAREA' || input.type === 'hidden') {
                        input.value = '';
                    }
                });
            });

            // Reset all rows in Recipient table except the first
            const recipientTableBody = document.querySelector('#recipient_programs_table tbody');
            while (recipientTableBody.rows.length > 1) {
                recipientTableBody.deleteRow(1);
            }
            // Reset all input values in Recipient table
            Array.from(recipientTableBody.rows).forEach(row => {
                Array.from(row.querySelectorAll('input, textarea')).forEach(input => {
                    if (input.type === 'text' || input.tagName === 'TEXTAREA' || input.type === 'hidden') {
                        input.value = '';
                    }
                });
            });

            // Update max for recipient_amount fields
            attachAmountBalanceListeners();
            calculateTotalSourceAmount();
            calculateTotalRecipientAmount();
        });
    });
</script>