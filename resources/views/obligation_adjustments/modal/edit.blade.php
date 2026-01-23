<!-- Adjust Obligations Modal -->
<form id="editObligationAdjustmentForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editObligationAdjustmentModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Obligation Adjustment') }}
                </h3>
                <button type="button" onclick="closeEditObligationAdjustmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_adjustment_id" id="obligation_adjustment_id" value="">
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="adjustment_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="edit_adjustment_date" autocomplete="off" id="edit_adjustment_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="adjustment_remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="edit_adjustment_remarks" autocomplete="off" id="edit_adjustment_remarks" placeholder="{{ __('Remarks') }}" :value="old('adjustment_remarks')" class="block w-full text-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_remarksError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Programs Table -->
                            <div class="sm:col-span-6">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                <div class="mt-2 overflow-x-auto">
                                    <!-- Display Obligation Amounts and Appropriations in Programs Table -->
                                    <table id="edit_programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Program') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Account Code') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Description') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Allotment / Purchase Order Amount') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Amount of Obligation') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200 hidden">
                                                    {{ __('Allotment Balance') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Adjustment Amount') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200 w-40">
                                                    {{ __('Adjusted Amount') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                           <!-- Dynamic content will be inserted here via JS -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td colspan="6" class="px-2 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-200">Total Adjusted Amount:</td>
                                                <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400" id="editAdjustedAmountTotalCell">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditObligationAdjustmentForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditObligationAdjustmentModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>

    const adjustments = @json($adjustments);

    function updateTextColor() {
        const fields = [
            document.getElementById('edit_adjustment_date'),
            document.getElementById('edit_adjustment_remarks'),
            document.getElementById('edit_adjusted_amount')
        ];
        fields.forEach(field => {
            if (!field) return;
            if (field.value && field.value.trim() !== '') {
                field.classList.remove('text-gray-500');
                field.classList.add('text-gray-900', 'dark:text-gray-100');
            } else {
                field.classList.remove('text-gray-900', 'dark:text-gray-100');
                field.classList.add('text-gray-500');
            }
        });
    }

    function openEditObligationAdjustmentModal(adjustmentId) {
        closeAllDropdowns();

        const adjustment = adjustments.find(a => a.id === adjustmentId);
        if (!adjustment) return;

        // Set form action
        document.getElementById('editObligationAdjustmentForm').action = 'obligation_adjustments/' + adjustment.id;

        // Set fields
        document.querySelector("input[name='obligation_adjustment_id']").value = adjustment.id;
        document.getElementById('edit_adjustment_date').value = adjustment.adjustment_date;
        document.getElementById('edit_adjustment_remarks').value = adjustment.adjustment_remarks;

        // Update the single row in table
        const tableBody = document.querySelector('#edit_programs_table tbody');
        tableBody.innerHTML = `
            <tr>
                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${adjustment.program}</td>
                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${adjustment.account_code}</td>
                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">${adjustment.description}</td>
                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 balance-cell-edit">
                    ${
                        adjustment.obr_type === 'Purchase Request'
                            ? Number(adjustment.po_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                            : Number(adjustment.allotment).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    }
                </td>
                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                    ${Number(adjustment.obr_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </td>
                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 hidden">
                ${Number(adjustment.balance_from_allotment).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </td>
                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                    <span class="adjustment-amount">
                        ${Number(adjustment.adjustment_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </span>
                </td>
                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 w-40">
                    <x-form.input
                        type="number"
                        name="edit_adjusted_amount"
                        id="edit_adjusted_amount"
                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                        value="${Number(adjustment.adjusted_amount).toFixed(2)}"
                        step="0.01"
                        oninput="validateAmountEdit(this); computeAdjustmentAmountForEditRow(this.closest('tr')); updateAdjustedAmountTotalEdit();"
                    />
                    <span class="text-red-500 text-sm"></span>
                </td>
            </tr>
        `;

        updateAdjustedAmountTotalEdit();

        // Show modal
        const modal = document.getElementById('editObligationAdjustmentModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        updateTextColor(); // Ensure text color updates after setting values
    }

    function closeEditObligationAdjustmentModal() {
        const modal = document.getElementById('editObligationAdjustmentModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    function validateEditObligationAdjustmentForm() {
        const edit_remarks = document.getElementById('edit_adjustment_remarks');
        const edit_adjustment_date = document.getElementById('edit_adjustment_date');
        const edit_adjustmentAmounts = document.querySelectorAll("input[name^='edit_adjusted_amount']");

        let isValid = true;

        // Validate adjustment date
        if (!edit_adjustment_date.value) {
            edit_adjustment_date.classList.add('border-red-500');
            edit_adjustment_date.title = 'Adjustment date is required.';
            isValid = false;
        } else {
            edit_adjustment_date.classList.remove('border-red-500');
            edit_adjustment_date.title = '';
        }

        // Validate remarks
        if (!edit_remarks.value.trim()) {
            document.getElementById('edit_remarksError').innerText = 'Remarks are required.';
            isValid = false;
        } else {
            document.getElementById('edit_remarksError').innerText = '';
        }

        // Validate adjusted_amount fields
        edit_adjustmentAmounts.forEach(function(input) {
            const errorSpan = input.nextElementSibling;
            const value = parseFloat(input.value);
            if (!input.value.trim() || value === 0) {
                if (errorSpan) {
                    errorSpan.innerText = 'Adjusted amount must not be zero or empty.';
                }
                isValid = false;
            } else {
                if (errorSpan) {
                    errorSpan.innerText = '';
                }
            }
        });

        if (isValid) {
            document.getElementById('editObligationAdjustmentForm').submit();
        }
    }
    // Function to compute adjustment amount for each row in the edit view
    function computeAdjustmentAmountForEditRow(row) {
        const obrAmountCell = row.querySelector("td:nth-child(5)"); // Original Obligation column
        const adjustedAmountInput = row.querySelector("input[name^='edit_adjusted_amount']"); // Input name for edit
        const adjustmentAmountCell = row.querySelector("td:nth-child(7)"); // Adjustment Amount column

        if (obrAmountCell && adjustedAmountInput && adjustmentAmountCell) {
            const obrAmount = parseFloat(obrAmountCell.textContent.replace(/,/g, '')) || 0;
            const adjustedAmount = parseFloat(adjustedAmountInput.value.replace(/,/g, '')) || 0;
            const adjustmentAmount = adjustedAmount - obrAmount;

            adjustmentAmountCell.textContent = adjustmentAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    // Watch for input changes in adjusted amount fields on edit blade
    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('edit_adjusted_amount')) {
            const row = event.target.closest('tr');
            if (row) {
                computeAdjustmentAmountForEditRow(row);
                updateAdjustedAmountTotalEdit();
            }
        }
    });

    // Attach updateTextColor to input/change events for relevant fields
    document.addEventListener('DOMContentLoaded', function() {
        var ids = ['edit_adjustment_date', 'edit_adjustment_remarks'];
        for (var i = 0; i < ids.length; i++) {
            var el = document.getElementById(ids[i]);
            if (el) {
                el.addEventListener('input', updateTextColor);
                el.addEventListener('change', updateTextColor);
                updateTextColor();
            }
        }
        // For dynamically inserted adjusted amount input
        document.getElementById('edit_programs_table').addEventListener('input', function(e) {
            if (e.target && e.target.id === 'edit_adjusted_amount') {
                updateTextColor();
            }
        });
    });

    function updateAdjustedAmountTotalEdit() {
        const adjustedInputs = document.querySelectorAll("input[name^='edit_adjusted_amount']");
        let total = 0;
        adjustedInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val !== 0) {
                total += val;
            }
        });
        const totalCell = document.getElementById('editAdjustedAmountTotalCell');
        if (totalCell) {
            totalCell.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('edit_adjusted_amount')) {
            const row = event.target.closest('tr');
            if (row) {
                computeAdjustmentAmountForEditRow(row);
                updateAdjustedAmountTotalEdit();
            }
        }
    });

    function validateAmountEdit(inputElement) {
        const row = inputElement.closest('tr'); 
        const balanceCell = row.querySelector('.balance-cell-edit'); // get the <td> text

        if (balanceCell) {
            const maxBalance = parseFloat(balanceCell.textContent.replace(/,/g, '')) || 0;
            const currentValue = parseFloat(inputElement.value) || 0;

            if (currentValue > maxBalance) {
                inputElement.value = maxBalance.toFixed(2);
            }
        }
    }
</script>