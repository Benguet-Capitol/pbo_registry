<!-- Edit Obligations Modal -->
<form id="editObligationsForm" method="POST" action="">
    @csrf
    @method('PUT')
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
    <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
    <input type="hidden" name="fund_filter" value="{{ request('fund_filter') }}">
    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="search_column" value="{{ request('search_column') }}">
    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
    <input type="hidden" name="group_filter" value="{{ request('group_filter') }}">
    <input type="hidden" name="fund_type_filter" value="{{ request('fund_type_filter') }}">
    <input type="hidden" name="office_filter" value="{{ request('office_filter') }}">
    <input type="hidden" name="allotment_class_filter" value="{{ request('allotment_class_filter') }}">
    <input type="hidden" name="from_dashboard" value="0">
    <input type="hidden" name="from_accounts" value="0">
    <input type="hidden" name="dashboard_class_id" value="">
    <input type="hidden" name="accounts_class_id" value="">
    <div id="editObligationsModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out; max-height: 90vh;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Obligation') }}
                </h3>
                <button type="button" onclick="closeEditObligationsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto flex-1" style="max-height: calc(90vh - 280px);">
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
                                            class="block w-full bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-200"
                                            oninput="filterEditOfficeAllotmentClasses()"
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
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_obr_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
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
                                <x-form.label for="edit_obr_type" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Type')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-arrow-up-right-dots"></i>
                                        </x-slot>
                                        <x-form.select withicon id="edit_obr_type" class="block w-full text-gray-900" type="text" name="edit_obr_type" :value="old('obr_type')" placeholder="{{ __('Obligation Type') }}">
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
                                <x-form.label for="edit_obr_no" class="block text-sm/6 font-medium dark:text-gray-200" :value="__('Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon type='text' name="edit_obr_no" autocomplete="off" id="edit_obr_no" placeholder="{{ __('Number') }}" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Particulars -->
                            <div class="sm:col-span-6">
                                <x-form.label for="edit_particulars" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Particulars')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-align-justify"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="edit_particulars" autocomplete="off" id="edit_particulars" placeholder="{{ __('Particulars') }}" :value="old('particulars')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="particularsError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="edit_remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="edit_remarks" autocomplete="off" id="edit_remarks" placeholder="{{ __('Remarks') }}" :value="old('remarks')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
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
                                                        <x-form.input
                                                            name="edit_account_code[]"
                                                            id="edit_account_code"
                                                            placeholder="{{ __('Account Code') }}"
                                                            value="{{ $amount['account_code'] ?? '' }}"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            oninput="filterAccountCodes(this)"
                                                            autocomplete="off" />
                                                        <div id="editAccountCodeDropdown" class="account-code-dropdown absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                                            <!-- Suggestions will appear here -->
                                                        </div>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea
                                                            name="edit_description[]"
                                                            placeholder="{{ __('Description') }}"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            autocomplete="off">{{ $amount['description'] ?? '' }}</x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea
                                                            name="edit_programs[]"
                                                            placeholder="{{ __('Program') }}"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            autocomplete="off">{{ $amount['program'] ?? '' }}</x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input
                                                            type="text"
                                                            name="edit_balance_from_allotment[]"
                                                            value="{{ number_format($amount['balance_from_allotment'] ?? 0, 2) }}"
                                                            placeholder="{{ __('Balance') }}"
                                                            autocomplete="off"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            readonly />
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input
                                                            type="text"
                                                            name="edit_amount_of_obligation[]"
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
                                                        <x-form.input name="edit_account_code[]" id="edit_account_code[]" placeholder="{{ __('Account Code') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterAccountCodes(this)" autocomplete="off" />
                                                        <div class="account-code-dropdown absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                                            <!-- Suggestions will appear here -->
                                                        </div>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea name="edit_description[]" placeholder="{{ __('Description') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.textarea name="edit_programs[]" placeholder="{{ __('Program') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input type="text" name="edit_balance_from_allotment[]" placeholder="{{ __('Balance') }}" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
                                                    </td>
                                                    <td class="px-1 py-2">
                                                        <x-form.input type="text" name="edit_amount_of_obligation[]" oninput="validateAmount(this); calculateTotalObligation();" onblur="calculateTotalObligation();" placeholder="{{ __('Amount') }}" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
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
                                                    {{ __('Total Obligation:') }}
                                                </td>
                                                <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400">
                                                    <span id="totalObligationEdit" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-right text-xs">0.00</span>
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
            <div class="justify-center items-center mt-0 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="submit" onclick="validateEditObligationsForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Update') }}
                </button>
                <button type="button" onclick="closeEditObligationsModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</form>

<style>
@keyframes scaleInUp {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmEditModal" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50 hidden">
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
    // Store original obligation info for validation
    let originalObligationClassId = null;
    let hasRelatedRecords = false;

    function openEditObligationsModal(obligation) {
        if (typeof closeAllDropdowns === 'function') {
            closeAllDropdowns();
        }

        document.getElementById('editObligationsForm').action = `/obligations/${obligation.id}`;
        console.log('Loaded obligation:', obligation);
        
        // Check if obligation has related records
        hasRelatedRecords = (obligation.purchase_orders && obligation.purchase_orders.length > 0) ||
                           (obligation.obligation_adjustments && obligation.obligation_adjustments.length > 0) ||
                           (obligation.disbursements && obligation.disbursements.length > 0);
        console.log('Has related records:', hasRelatedRecords);
        
        // Reset from_dashboard and from_accounts first
        const fromDashboardField = document.querySelector('#editObligationsForm input[name="from_dashboard"]');
        const fromAccountsField = document.querySelector('#editObligationsForm input[name="from_accounts"]');
        
        if (fromDashboardField) {
            fromDashboardField.value = '0'; // Reset to default
        }
        if (fromAccountsField) {
            fromAccountsField.value = '0'; // Reset to default
        }
        
        // Check if opened from dashboard
        const isFromDashboard = window.isFromDashboard || (window.currentClassId !== undefined);
        const isFromAccounts = window.isFromAccounts || false;
        
        console.log('Is from dashboard:', isFromDashboard, 'window.isFromDashboard:', window.isFromDashboard, 'window.currentClassId:', window.currentClassId);
        console.log('Is from accounts:', isFromAccounts, 'window.isFromAccounts:', window.isFromAccounts);
        
        if (isFromDashboard) {
            console.log('Setting from_dashboard to 1');
            if (fromDashboardField) {
                fromDashboardField.value = '1';
                console.log('from_dashboard field value set to:', fromDashboardField.value);
            }
            if (window.currentClassId) {
                const dashboardClassIdField = document.querySelector('#editObligationsForm input[name="dashboard_class_id"]');
                if (dashboardClassIdField) {
                    dashboardClassIdField.value = window.currentClassId;
                }
            }
        } else if (isFromAccounts) {
            console.log('Setting from_accounts to 1');
            if (fromAccountsField) {
                fromAccountsField.value = '1';
                console.log('from_accounts field value set to:', fromAccountsField.value);
            }
            if (window.accountsClassId) {
                const accountsClassIdField = document.querySelector('#editObligationsForm input[name="accounts_class_id"]');
                if (accountsClassIdField) {
                    accountsClassIdField.value = window.accountsClassId;
                }
            }
        }

        // Get office_allotment_class_id from obligation, or fallback to currentClassId or URL parameter
        let officeAllotmentClassId = obligation.office_allotment_class_id;
        console.log('openEditObligationsModal - Initial officeAllotmentClassId:', officeAllotmentClassId);
        console.log('openEditObligationsModal - window.currentClassId:', window.currentClassId);
        
        if (!officeAllotmentClassId && window.currentClassId) {
            officeAllotmentClassId = window.currentClassId;
            console.log('Using window.currentClassId:', officeAllotmentClassId);
        }
        if (!officeAllotmentClassId) {
            const urlParams = new URLSearchParams(window.location.search);
            officeAllotmentClassId = urlParams.get('office_allotment_class_id') || '';
            if (officeAllotmentClassId) {
                console.log('Using URL parameter office_allotment_class_id:', officeAllotmentClassId);
            }
        }

        console.log('Final officeAllotmentClassId to use:', officeAllotmentClassId);

        // Update the main form's hidden office_allotment_class_id field
        const mainOfficeAllotmentClassIdField = document.querySelector('#editObligationsForm input[name="office_allotment_class_id"]');
        if (mainOfficeAllotmentClassIdField) {
            mainOfficeAllotmentClassIdField.value = officeAllotmentClassId;
        }
        
        // Store original office_allotment_class_id for validation
        originalObligationClassId = officeAllotmentClassId;

        // Fields to populate
        const fields = {
            edit_office_allotment_class: obligation.office_allotment_class?.name || obligation.office_allotment_class || '',
            edit_office_allotment_class_id: officeAllotmentClassId,
            edit_obr_date: obligation.obr_date || '',
            edit_obr_type: obligation.obr_type || '',
            edit_obr_no: obligation.obr_no || '',
            edit_particulars: obligation.particulars || '',
            edit_remarks: obligation.remarks || ''
        };

        // Populate and colorize text fields
        Object.entries(fields).forEach(([id, value]) => {
            const el = document.getElementById(id);
            if (el) {
                el.value = value;
                el.classList.toggle('text-gray-900', !!value);
                el.classList.toggle('text-gray-400', !value);
            }
        });

        // Show abbreviation + class if available
        if (obligation.office_allotment_class && typeof obligation.office_allotment_class === 'object') {
            const el = document.getElementById('edit_office_allotment_class');
            el.value = `${obligation.office_allotment_class.office_abbreviation} - ${obligation.office_allotment_class.class}`;
            el.classList.add('text-gray-900');
            el.classList.remove('text-gray-400');
        }

        // Handle obligation amounts: render all as table rows
        try {
            const tableBody = document.querySelector('#edit_programs_table tbody');
        tableBody.innerHTML = '';
        if (Array.isArray(obligation.obligation_amounts) && obligation.obligation_amounts.length > 0) {
            console.log('Debug - Full obligation:', obligation);
            obligation.obligation_amounts.forEach((amount, index) => {
                console.log(`Debug - Processing amount ${index}:`, amount);
                
                // Get values with proper error handling
                const description = amount.description || amount.appropriation?.description || '';
                const program = amount.program || amount.appropriation?.programs || '';
                const balanceFromAllotment = parseFloat(amount.balance_from_allotment || 0);
                const obrAmount = parseFloat(amount.obr_amount || 0);
                
                console.log(`Debug - Processed values for row ${index}:`, {
                    description,
                    program,
                    raw_balance: amount.balance_from_allotment,
                    processed_balance: balanceFromAllotment,
                    raw_amount: amount.obr_amount,
                    processed_amount: obrAmount
                });
                
                console.log('Full amount object:', amount);
            console.log(`Row ${index} values:`, {
                description,
                program,
                balanceFromAllotment: amount.balance_from_allotment,
                obrAmount,
                appropriation: amount.appropriation
            });

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <input type="hidden" name="edit_obligation_amounts_id[]" value="${amount.id || ''}" />
                    <td class="px-1 py-2">
                        <x-form.input name="edit_account_code[]" id="edit_account_code[]" placeholder="Account Code" value="${amount.account_code || ''}" data-original-account-code="${amount.account_code || ''}" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterEditAccountCodes(this)" autocomplete="off" />
                        <div class="account-code-dropdown absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50"></div>
                    </td>
                    <td class="px-1 py-2">
                        <x-form.textarea name="edit_description[]" placeholder="Description" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off">${description}</x-form.textarea>
                    </td>
                    <td class="px-1 py-2">
                        <x-form.textarea name="edit_programs[]" placeholder="Program" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off">${program}</x-form.textarea>
                    </td>
                    <td class="px-1 py-2">
                        <x-form.input type="text" name="edit_balance_from_allotment[]" value="${balanceFromAllotment.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}" placeholder="Balance" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
                    </td>
                    <td class="px-1 py-2">
                        <x-form.input type="text" name="edit_amount_of_obligation[]" value="${parseFloat(obrAmount).toFixed(2)}" oninput="validateAmountEdit(this); calculateTotalObligationEdit();" onblur="calculateTotalObligationEdit();" placeholder="Amount" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                    </td>
                    <td class="px-1 py-2 text-center">
                        <button type="button" onclick="deleteRowEdit(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        } else {
            // If no amounts, add a blank row
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-1 py-2">
                    <x-form.input name="edit_account_code[]" placeholder="Account Code" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterEditAccountCodes(this)" autocomplete="off" />
                    <div class="account-code-dropdown absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="editAccountCodeDropdown"></div>
                </td>
                <td class="px-1 py-2">
                    <x-form.textarea name="edit_description[]" placeholder="Description" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                </td>
                <td class="px-1 py-2">
                    <x-form.textarea name="edit_programs[]" placeholder="Program" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
                </td>
                <td class="px-1 py-2">
                    <x-form.input type="text" name="edit_balance_from_allotment[]" placeholder="Balance" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
                </td>
                <td class="px-1 py-2">
                    <x-form.input type="text" name="edit_amount_of_obligation[]" oninput="validateAmountEdit(this); calculateTotalObligationEdit();" onblur="calculateTotalObligationEdit();" placeholder="Amount" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
                </td>
                <td class="px-1 py-2 text-center">
                    <button type="button" onclick="deleteRowEdit(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        }
        } catch (error) {
            console.error('Error populating edit table:', error);
        }

        // Colorize all editable input fields
        try {
            document.querySelectorAll('#editObligationsModal input, #editObligationsModal textarea, #editObligationsModal select').forEach(el => {
                const hasValue = el.value && el.value.trim() !== '';
                el.classList.toggle('text-gray-900', hasValue);
                el.classList.toggle('text-gray-400', !hasValue);
            });

            calculateTotalObligationEdit();
        } catch (error) {
            console.error('Error in edit modal population:', error);
        }

        try {
            const modal = document.getElementById('editObligationsModal');
            if (modal) {
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            }
        } catch (error) {
            console.error('Error displaying edit modal:', error);
        }
    }

    // Close Edit Modal
    function closeEditObligationsModal() {
        const modal = document.getElementById('editObligationsModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        // Reset the from_dashboard flag
        window.isFromDashboard = false;
        
        // Also reset the form field
        const fromDashboardField = document.querySelector('#editObligationsForm input[name="from_dashboard"]');
        if (fromDashboardField) {
            fromDashboardField.value = '0';
        }
    }

    const editOfficeAllotmentClasses = [
        @foreach($office_allotment_classes as $i => $office_allotment_class)
            {
                id: "{{ $office_allotment_class->id }}",
                name: "{{ $office_allotment_class->office_abbreviation }} - {{ $office_allotment_class->allotmentClass->class }}",
                class: "{{ $office_allotment_class->class }}",
                fund: "{{ $office_allotment_class->fund ?? 'General Fund' }}"
            }@if(!$loop->last),@endif
        @endforeach
    ];

    const allowedObligationTypesEdit = {
        'PS': ['Regular'],
        'MOOE': ['Regular', 'Purchase Request', 'Project/Contract'],
        'CO': ['Purchase Request', 'Project/Contract'],
        'CCO': ['Purchase Request', 'Project/Contract']
    };

    //Filter Edit Office and Allotment Classes
    function filterEditOfficeAllotmentClasses() {
        const input = document.getElementById('edit_office_allotment_class');
        const filter = input.value.toLowerCase();
        const dropdown = document.getElementById('editOfficeAllotmentClassDropdown');

        // Clear previous suggestions
        dropdown.innerHTML = '';

        if (!filter) {
            dropdown.classList.add('hidden');
            return;
        }

        const filterClasses = editOfficeAllotmentClasses.filter(item => item.name.toLowerCase().includes(filter));
        if (filterClasses.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        filterClasses.forEach(item => {
            const option = document.createElement('div');
            option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer';
            option.textContent = `${item.name}`;
            option.onclick = function() {
                // Clear any previous error messages
                const errorEl = document.getElementById('edit_OfficeAllotmentClassError');
                if (errorEl) {
                    errorEl.textContent = '';
                }
                
                input.value = `${item.name}`;
                document.getElementById('edit_office_allotment_class_id').value = item.id; // Set the hidden input value
                // Reset all account code fields when OfficeAllotmentClass is selected
                document.querySelectorAll('[name="edit_account_code[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="edit_description[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="edit_programs[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="edit_balance_from_allotment[]"]').forEach(field => field.value = '');
                document.querySelectorAll('[name="edit_amount_of_obligation[]"]').forEach(field => field.value = '');
                generateObrNumberEdit(item.fund); // Pass fund name, not prefix
                // Restrict Obligation Type based on detected class
                restrictObligationTypeEdit(item.class);
                // Auto-select "Regular" if PS
                if (item.class === 'PS') {
                    const obrTypeSelect = document.getElementById("edit_obr_type");
                    obrTypeSelect.value = 'Regular';
                }
                // Fetch new appropriations for the selected office_allotment_class
                fetchEditAppropriations(item.id);
                dropdown.classList.add('hidden');
                
            };
            dropdown.appendChild(option);
        });
        dropdown.classList.remove('hidden');
    }

    // Fetch appropriations for the selected office_allotment_class
    function fetchEditAppropriations(officeAllotmentClassId) {
        fetch(`/appropriations/by-office-allotment-class?office_allotment_class_id=${officeAllotmentClassId}`)
            .then(response => response.json())
            .then(data => {
                if (data.data) {
                    // Update the editAppropriations array with new data
                    window.editAppropriations = data.data;
                    console.log('Updated editAppropriations:', window.editAppropriations);
                } else {
                    console.error('No data returned from appropriations endpoint');
                }
            })
            .catch(error => {
                console.error('Error fetching appropriations:', error);
            });
    }

    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('editOfficeAllotmentClassDropdown');
        const input = document.getElementById('edit_office_allotment_class');
        if (!event.target.closest('#editOfficeAllotmentClassDropdown')) {
            dropdown.classList.add('hidden');
        }
    });
    // Attach to input event
    if (document.getElementById('edit_office_allotment_class')) {
        document.getElementById('edit_office_allotment_class').addEventListener('input', filterEditOfficeAllotmentClasses);
    }

    // Function to restrict obligation type options dynamically
    function restrictObligationTypeEdit(allotmentClass) {
        const obrTypeSelect = document.getElementById("edit_obr_type");

        obrTypeSelect.innerHTML = '<option value="">Select Obligation Type</option>'; // reset

        if (allowedObligationTypesEdit[allotmentClass]) {
            allowedObligationTypesEdit[allotmentClass].forEach(type => {
                const option = document.createElement("option");
                option.value = type;
                option.textContent = type;
                obrTypeSelect.appendChild(option);
            });
        }
    }

    // Generate the OBR number in the format 00000-mm-yy-000
    function generateObrNumberEdit(fund) {
        const obrNoField = document.getElementById('edit_obr_no');
        const date = new Date();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const year = String(date.getFullYear()).slice(-2); // Get last two digits of year

        const fundToSequence = {
            'General Fund' : '100',
            'Special Education Fund' : '200',
            'Benguet General Hospital Economic Enterprise' : '109',
            'Provincial Development Fund' : '107',
        }
    }

    // Call the function to set the initial value
    document.addEventListener('DOMContentLoaded', generateObrNumberEdit);

    let editAppropriations = [
        @foreach($appropriations as $i => $appropriation) 
        {
            id: "{{ $appropriation->id }}",
            account_code: "{{ $appropriation->account_code }}",
            description: `{{ $appropriation->description }}`,
            program: `{{ $appropriation->programs }}`,
            office_allotment_class_id: "{{ $appropriation->office_allotment_class_id }}",
            balance: "{{ number_format($appropriation->balance, 2) }}"
        }@if(!$loop->last),@endif
        @endforeach
    ];
    
    // Make it globally accessible for updates
    window.editAppropriations = editAppropriations;

    // Filter account codes and display suggstions with description and program
    function filterEditAccountCodes(inputElement) {
        // Clear error message
        const row = inputElement.closest('tr');
        if (row) {
            const errorMsg = row.querySelector('.account-code-error');
            if (errorMsg) {
                errorMsg.textContent = '';
            }
        }
        
        const officeAllotmentClassId = document.getElementById('edit_office_allotment_class_id').value;
        const dropdown = inputElement.nextElementSibling; // Assuming the dropdown is the next sibling
        const filter = inputElement.value.toLowerCase();
        dropdown.innerHTML = ''; // Clear previous suggestions
        if (!filter || !officeAllotmentClassId) {
            dropdown.classList.add('hidden');
            return;
        }
        const filteredCodes = window.editAppropriations.filter(item =>
            String(item.office_allotment_class_id) === String(officeAllotmentClassId) &&
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
                <strong>${item.account_code}</strong><br/>
                <span class="text-gray-600 dark:text-gray-400">${item.description || 'No Description'}</span><br/>
                <span class="text-blue-600 dark:text-blue-400">${item.program || 'No Program'}</span>`;
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

    // Populate related fields (description and program) based on selected account code (edit modal)
    function populateEditFields(inputElement, item) {
        const row = inputElement.closest('tr');
        const programField = row.querySelector('textarea[name="edit_programs[]"]');
        const descriptionField = row.querySelector('textarea[name="edit_description[]"]');
        if (programField) programField.value = item.program ? item.program.trim() : '';
        if (descriptionField) descriptionField.value = item.description ? item.description.trim() : '';
    }
    // Calculate and populate the balance for the selected account code (edit modal)
    function calculateEditBalance(inputElement, item) {
        const row = inputElement.closest('tr');
        const balanceField = row.querySelector('input[name="edit_balance_from_allotment[]"]');
        // Remove commas from formatted balance string before parsing
        const balanceValue = String(item.balance || '0').replace(/,/g, '');
        const balance = parseFloat(balanceValue);
        const formatBalance = balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (balanceField) balanceField.value = formatBalance;
    }

    // Hide dropdown when clicking outside (edit modal)
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('#AccountCodeDropdown, #editOfficeAllotmentClassDropdown');
        dropdowns.forEach(dropdown => {
            if (!event.target.closest('.absolute') && !event.target.closest('input[name="edit_account_code[]"]')) {
                dropdown.classList.add('hidden');
            }
        });
    });

    // Attach filterEditAccountCode to edit_account_code inputs (edit modal)
    document.querySelectorAll('input[name="edit_account_code[]"]').forEach(input => {
        input.addEventListener('input', function() {
            filterEditAccountCodes(this);
        });
    });

    // Validate Amount of Obligation (edit modal)
    function validateAmountEdit(inputElement) {
        const row = inputElement.closest('tr');
        const balanceField = row.querySelector('input[name="edit_balance_from_allotment[]"]');

        if (balanceField) {
            const maxBalance = parseFloat((balanceField.value || '0').replace(/,/g, ''));
            const currentValue = parseFloat((inputElement.value || '0').replace(/,/g, ''));

            if (currentValue > maxBalance) {
                inputElement.value = maxBalance.toFixed(2);
            }
        }
    }

    // Calculate Total Obligation (edit modal)
    function calculateTotalObligationEdit() {
        const amountFields = document.querySelectorAll('input[name="edit_amount_of_obligation[]"]');
        let total = 0;
        amountFields.forEach(field => {
            const value = parseFloat((field.value || '0').replace(/,/g, ''));
            if (!isNaN(value)) {
                total += value;
            }
        });
        document.getElementById('totalObligationEdit').textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    
    function addRowEdit() {
        const tableBody = document.querySelector('#edit_programs_table tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <input type="hidden" name="edit_obligation_amounts_id[]" value="" />
            <td class="px-1 py-2">
                <x-form.input name="edit_account_code[]" placeholder="Account Code" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" oninput="filterEditAccountCodes(this)" autocomplete="off" />
                <div class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50" id="editAccountCodeDropdown"></div>
            </td>
            <td class="px-1 py-2">
                <x-form.textarea name="edit_description[]" placeholder="Description" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
            </td>
            <td class="px-1 py-2">
                <x-form.textarea name="edit_programs[]" placeholder="Program" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" autocomplete="off"></x-form.textarea>
            </td>
            <td class="px-1 py-2">
                <x-form.input type="text" name="edit_balance_from_allotment[]" oninput="formatCurrency(this)" placeholder="Balance" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" readonly />
            </td>
            <td class="px-1 py-2">
                <x-form.input type="text" name="edit_amount_of_obligation[]" oninput="validateAmountEdit(this); calculateTotalObligationEdit();" onblur="calculateTotalObligationEdit();" placeholder="Amount" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" />
            </td>
            <td class="px-1 py-2 text-center">
                <button type="button" onclick="deleteRowEdit(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(newRow);
        // Re-attach event listeners to the new input fields
        newRow.querySelector('input[name="edit_account_code[]"]').addEventListener('input', function() {
            filterEditAccountCodes(this);
        });
        calculateTotalObligationEdit();
    }
    // Delete Row with Confirmation (edit modal)
    let rowToDeleteEdit = null;
    function deleteRowEdit(button) {
        rowToDeleteEdit = button.closest('tr');
        document.getElementById('deleteConfirmEditModal').classList.remove('hidden');
    }
    document.getElementById('confirmEditDeleteBtn').addEventListener('click', function() {
        if (rowToDeleteEdit) {
            rowToDeleteEdit.remove();
            rowToDeleteEdit = null;
            calculateTotalObligationEdit();
        }
        document.getElementById('deleteConfirmEditModal').classList.add('hidden');
    });
    document.getElementById('cancelEditDeleteBtn').addEventListener('click', function() {
        rowToDeleteEdit = null;
        document.getElementById('deleteConfirmEditModal').classList.add('hidden');
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
        enableDropdownKeyboardNavigation("edit_office_allotment_class", "editOfficeAllotmentClassDropdown");
        enableDropdownKeyboardNavigation("edit_account_code", "editAccountCodeDropdown");
    });
       
    function validateEditObligationsForm() {
        const form = document.getElementById('editObligationsForm');
        
        // Update hidden search input with current search field value
        const searchInput = document.getElementById('searchInput');
        const hiddenSearchInput = document.querySelector('#editObligationsForm input[name="search"]');
        if (searchInput && hiddenSearchInput) {
            hiddenSearchInput.value = searchInput.value;
        }
        
       // Log the current state BEFORE any modifications
        const fromDashboardField = document.querySelector('#editObligationsForm input[name="from_dashboard"]');
        console.log('validateEditObligationsForm - from_dashboard value:', fromDashboardField ? fromDashboardField.value : 'NOT FOUND');
        console.log('validateEditObligationsForm - window.isFromDashboard:', window.isFromDashboard);
        
        
        let isValid = true;
        document.querySelectorAll('#editObligationsModal .text-red-500').forEach(error => error.textContent = '');
        const officeAllotmentClass = document.getElementById('edit_office_allotment_class');
        const officeAllotmentClassId = document.getElementById('edit_office_allotment_class_id');
        if (!officeAllotmentClass.value.trim() || !officeAllotmentClassId.value.trim()) {
            document.getElementById('edit_OfficeAllotmentClassError').textContent = 'Office and Allotment Class is required.';
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
        if (obrType.value.trim()) {
            // Optionally validate the value is a valid option
            const validTypes = ['Regular', 'Purchase Request', 'Project/Contract'];
            if (!validTypes.includes(obrType.value)) {
                document.getElementById('obrTypeError').textContent = 'Please select a valid Obligation Type.';
                isValid = false;
            }
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
            document.getElementById('edit_particularsError').textContent = 'Particulars field is required.';
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
        const amountFields = document.querySelectorAll('[name="edit_amount_of_obligation[]"]');
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
        const balanceFields = document.querySelectorAll('[name="edit_balance_from_allotment[]"]');
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
            // Final check before submission
            console.log('Submitting form - from_dashboard final value:', fromDashboardField ? fromDashboardField.value : 'NOT FOUND');
            form.submit();
        }
    }
</script>