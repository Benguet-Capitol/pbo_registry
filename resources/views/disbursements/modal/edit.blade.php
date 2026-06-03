@if(session('error'))
    <div class="text-red-500 text-sm mb-2">
        {{ session('error') }}
    </div>
@endif
<!-- Disbursement Modal -->
 @foreach ($disbursements as $disbursement)
<form id="EditDisbursementForm" method="POST" action="{{ route('disbursements.update', $disbursement->id) }}">
    @csrf
    @method('PUT')
    <div id="editDisbursementModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Disbursement') }}
                </h3>
                <button type="button" onclick="closeEditDisbursementModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="disbursement_id" id="disbursement_id">
                            <!-- DV Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_disbursement_date" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('DV / Check Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="edit_disbursement_date" autocomplete="off" id="edit_disbursement_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- DV Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_dv_no" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('DV / Check Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-hashtag"></i>
                                        </x-slot>
                                        <x-form.input withicon name="edit_dv_no" autocomplete="off" id="edit_dv_no" placeholder="{{ __('DV / Check Number') }}" :value="old('dv_no')" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_dv_noError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_status" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Status')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </x-slot>
                                        <x-form.select withicon name="edit_status" id="edit_status" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200">
                                            <option value="">-- Select Status --</option>
                                            <option value="Partial Payment" {{ old('status') == 'Partial Payment' ? 'selected' : '' }}>Partial Payment</option>
                                            <option value="Full Payment" {{ old('status') == 'Full Payment' ? 'selected' : '' }}>Full Payment</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_statusError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_remarks" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="edit_remarks" autocomplete="off" id="edit_remarks" placeholder="{{ __('Remarks') }}" :value="old('remarks')" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Programs Table -->
                            <div class="sm:col-span-6">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessage" class="text-red-500 text-xs hidden mb-2"></div>
                                <div class="mt-2 overflow-x-auto">
                                    <!-- Display Obligation Amounts and Appropriations in Programs Table -->
                                    @if(isset($obligationAmounts) && $obligationAmounts->isNotEmpty())
                                    <table id="programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
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
                                                    {{ __('Balance from Obligations') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('DV / Check Amount') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($obligationAmounts as $obligationAmount)
                                            @php
                                                $adjustments = \App\Models\ObligationAdjustment::where('obligation_amounts_id', $obligationAmount->id)->sum('adjustment_amount');
                                                $disbursements = \App\Models\Disbursement::where('obligation_amounts_id', $obligationAmount->id)->sum('disbursement_amount');
                                                $balance = $obligationAmount->obr_amount + $adjustments;
                                                $appropriation = $obligationAmount->appropriation;
                                            @endphp
                                            <tr data-obligation-id="{{ $obligationAmount->id }}" class="obligation-row hidden">
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->programs ?? 'N/A' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->account_code ?? 'N/A' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->description ?? 'N/A' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">
                                                    <span class="adjustment-amount">{{ number_format($balance, 2) }}</span>
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    <x-form.input type="number" name="edit_disbursement_amount[{{ $obligationAmount->id }}]" min="0" step="0.01" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" placeholder="" oninput="validateAmount(this)" data-balance="{{ $balance  }}" />
                                                    <span id="editDisbursementAmountError" class="text-red-500 text-xs"></span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td colspan="4" class="px-2 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-200">Total DV / Check Amount:</td>
                                                <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400" id="dvAmountTotalCellEdit">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    @else
                                    <p class="text-center text-gray-500 dark:text-gray-400">No obligation amounts found for this obligation.</p>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-4 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateFormEdit()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditDisbursementModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endforeach

<script>
    function updateDVAmountTotalEdit() {
        const adjustedInputs = document.querySelectorAll("input[name^='edit_disbursement_amount']");
        let total = 0;
        adjustedInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                total += val;
            }
        });
        const totalCell = document.getElementById('dvAmountTotalCellEdit');
        if (totalCell) {
            totalCell.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('edit_disbursement_amount')) {
            updateDVAmountTotalEdit();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        updateDVAmountTotalEdit();
    });
    //Checks if an input has a value and adjusts the text color accordingly
    // Make updateTextColor globally accessible
    function updateTextColor(element) {
        if (element.disabled) {
            element.classList.remove("text-gray-900", "dark:text-gray-100");
            element.classList.add("text-gray-400");
        } else if (element.value.trim() !== "") {
            element.classList.remove("text-gray-500", "text-gray-400");
            element.classList.add("text-gray-900", "dark:text-gray-100");
        } else {
            element.classList.remove("text-gray-900", "dark:text-gray-100", "text-gray-400");
        }
    }

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

            // Detect when a field is enabled
            const observer = new MutationObserver(() => updateTextColor(element));
            observer.observe(element, {
                attributes: true,
                attributeFilter: ["disabled"]
            });
        });

        // Handle autofill values after a short delay
        setTimeout(() => {
            elements.forEach(updateTextColor);
        }, 100);
    });

    function openEditDisbursementModal(disbursement) {
        // Close any open dropdowns or autocomplete suggestions
        closeAllDropdowns();

        document.querySelector("input[name='disbursement_id']").value = disbursement.id;

        document.getElementById('EditDisbursementForm').action = `/disbursements/${disbursement.id}`;

        document.getElementById('edit_disbursement_date').value = disbursement.disbursement_date ?? '';
        updateTextColor(document.getElementById('edit_disbursement_date'));
        document.getElementById('edit_dv_no').value = disbursement.dv_no ?? '';
        updateTextColor(document.getElementById('edit_dv_no'));
        document.getElementById('edit_status').value = disbursement.status ?? '';
        updateTextColor(document.getElementById('edit_status'));
        document.getElementById('edit_remarks').value = disbursement.remarks ?? '';
        updateTextColor(document.getElementById('edit_remarks'));

        // --- Only show matching obligation row ---
        document.querySelectorAll('.obligation-row').forEach(row => row.classList.add('hidden'));

        const matchingRow = document.querySelector(`tr[data-obligation-id="${disbursement.obligation_amounts_id}"]`);
        if (matchingRow) {
            matchingRow.classList.remove('hidden');
            const dvAmountInput = document.querySelector(`input[name='edit_disbursement_amount[${disbursement.obligation_amounts_id}]']`);
            if (dvAmountInput) {
                dvAmountInput.value = disbursement.disbursement_amount ?? '';
            }
        }

    // Show the modal
    const modal = document.getElementById('editDisbursementModal');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    // Update total footer
    updateDVAmountTotalEdit();
    }

    function closeEditDisbursementModal() {
        const modal = document.getElementById('editDisbursementModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    function validateAmount(inputElement) {
        const maxBalance = parseFloat(inputElement.dataset.balance || "0");
        const inputValue = parseFloat(inputElement.value || "0");

        if (inputValue > maxBalance) {
            inputElement.value = maxBalance.toFixed(2);
            inputElement.title = `Max allowed is ₱${maxBalance.toFixed(2)}`;
        }
    }

    function validateFormEdit() {
        const dv_no = document.getElementById('edit_dv_no');
        const status = document.getElementById('edit_status');
        const adjustmentAmounts = document.querySelectorAll("input[name^='adjusted_amount']");
        const dvInputs = document.querySelectorAll("input[name^='edit_disbursement_amount']");

        let atLeastOneDVFilled = false;
        let isValid = true;

        // Validate DV Number
        if (!dv_no.value.trim()) {
            document.getElementById('edit_dv_noError').innerText = 'DV / Check Number is required.';
            isValid = false;
        } else {
            document.getElementById('edit_dv_noError').innerText = '';
        }
        // Validate Status
        if (!status.value.trim()) {
            document.getElementById('edit_statusError').innerText = 'Status is required.';
            isValid = false;
        } else {
            document.getElementById('edit_statusError').innerText = '';
        }

        // Ensure at least one dv_amount is filled
        dvInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                atLeastOneDVFilled = true;
            }
        });

        if (!atLeastOneDVFilled) {
            document.getElementById('tableMessage').classList.remove('hidden');
            document.getElementById('tableMessage').innerText = 'Enter at least one Disbursement amount.';
            isValid = false;
        } else {
            document.getElementById('tableMessage').classList.add('hidden');
            document.getElementById('tableMessage').innerText = '';
        }

        if (isValid) {
            document.getElementById('EditDisbursementForm').submit();
        }
    } 

</script>