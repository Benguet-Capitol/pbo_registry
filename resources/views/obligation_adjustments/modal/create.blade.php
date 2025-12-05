<!-- Adjust Obligations Modal -->
<form id="createObligationAdjustmentForm" method="POST" action="{{ route('obligation_adjustments.store') }}">
    @csrf
    <div id="createObligationAdjustmentModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Adjust Obligation') }}
                    </h3>
                    <button type="button" onclick="closeCreateObligationAdjustmentModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="adjustment_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="adjustment_date" autocomplete="off" id="adjustment_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.textarea withicon name="adjustment_remarks" autocomplete="off" id="adjustment_remarks" placeholder="{{ __('Remarks') }}" :value="old('adjustment_remarks')" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="remarksError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Programs Table -->
                            <div class="sm:col-span-6">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessage" class="text-red-500 text-sm hidden mb-2"></div>
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
                                            @foreach($obligationAmounts as $obligationAmount)
                                            @php
                                                $appropriation = $obligationAmount->appropriation;
                                                
                                                // Compute Adjusted OBR
                                                $adjustedObrAmount = $obligationAmount->obr_amount;
                                                if ($obligationAmount->obligationAdjustments && $obligationAmount->obligationAdjustments->isNotEmpty()) {
                                                    $adjustedObrAmount += $obligationAmount->obligationAdjustments->sum('adjustment_amount');
                                                }

                                                // Determine current quarter
                                                $currentMonth = now()->month; 
                                                if ($currentMonth >= 1 && $currentMonth <= 3) {
                                                    $currentQuarter = 1;
                                                } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
                                                    $currentQuarter = 2;
                                                } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
                                                    $currentQuarter = 3;
                                                } else {
                                                    $currentQuarter = 4;
                                                }

                                                // Compute Total Appropriation (allotment base, only up to current quarter)
                                                $totalAppropriation = 0;
                                                if ($appropriation) {
                                                    if ($currentQuarter >= 1) $totalAppropriation += $appropriation->quarter1 ?? 0;
                                                    if ($currentQuarter >= 2) $totalAppropriation += $appropriation->quarter2 ?? 0;
                                                    if ($currentQuarter >= 3) $totalAppropriation += $appropriation->quarter3 ?? 0;
                                                    if ($currentQuarter >= 4) $totalAppropriation += $appropriation->quarter4 ?? 0;
                                                }

                                                // Realignments
                                                $realignmentTotal = 0;
                                                foreach ($appropriation->realignments ?? [] as $realignment) {
                                                    if ($realignment->type === 'Source') {
                                                        $realignmentTotal -= $realignment->amount;
                                                    } elseif ($realignment->type === 'Recipient') {
                                                        $realignmentTotal += $realignment->amount;
                                                    }
                                                }

                                                // Supplementals
                                                $supplementalTotal = 0;
                                                foreach ($appropriation->supplementals ?? [] as $supplemental) {
                                                    if ($supplemental->type === 'Reversion') {
                                                        $supplementalTotal -= $supplemental->amount;
                                                    } elseif ($supplemental->type === 'Supplemental') {
                                                        $supplementalTotal += $supplemental->amount;
                                                    }
                                                }

                                                // Final Allotment
                                                $allotment = $totalAppropriation + $realignmentTotal + $supplementalTotal;

                                                // Balance
                                                $balanceFromAllotment = $allotment - $adjustedObrAmount;
                                            @endphp
                                            <tr 
                                                data-obligation-type="{{ $obligationAmount->obligation->type ?? '' }}" 
                                                data-po-amount="{{ $obligationAmount->obligation->purchaseOrders->sum('po_amount') ?? 0 }}">
                                                
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->programs ?? '-' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->account_code ?? '-' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->description ?? '-' }}
                                                </td>
                                                {{-- Show PO amount if obligation type is Purchase Request --}}
                                                @if($obligationAmount->obligation->obr_type === 'Purchase Request')
                                                    <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 po-amount-cell">
                                                        {{ number_format($obligationAmount->purchaseOrders->sum('po_amount'), 2) }}
                                                    </td>
                                                @else
                                                    {{-- Otherwise show Allotment --}}
                                                    <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 allotment-cell">
                                                        {{ number_format($allotment, 2) }}
                                                    </td>
                                                @endif
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ number_format($adjustedObrAmount, 2) }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 hidden">
                                                    {{ number_format($balanceFromAllotment, 2) }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">
                                                    <span class="adjustment-amount">0.00</span>
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">
                                                    <x-form.input type="number" 
                                                        name="adjusted_amount[{{ $obligationAmount->id }}]" 
                                                        autocomplete="off" 
                                                        oninput="validateAmount(this)"  
                                                        class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"/>
                                                    <span class="adjustmentAmountError text-red-500 text-xs hidden"></span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td colspan="6" class="px-2 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-200">Total Adjusted Amount:</td>
                                                <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400" id="adjustedAmountTotalCell">0.00</td>
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
                <div class="justify-center items-center mt-4 p-4 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <div class="flex items-center justify-center">
                        <button type="button" onclick="validateCreateObligationAdjustmentForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                            <i class="fas fa-save text-xl mr-2"></i>
                            {{ __('Save') }}
                        </button>
                        <button type="button" onclick="closeCreateObligationAdjustmentModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                            <i class="fas fa-times text-xl mr-2"></i>
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function openCreateObligationAdjustmentModal() {
        closeAllDropdowns();
        document.getElementById('createObligationAdjustmentModal').classList.remove('hidden');
    }

    function closeCreateObligationAdjustmentModal() {
        document.getElementById('createObligationAdjustmentModal').classList.add('hidden');
    }
    
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
    });

    function validateAmount(inputElement) {
        const row = inputElement.closest('tr'); 
        const poAmountCell = row.querySelector('.po-amount-cell'); 
        const errorSpan = row.querySelector('span.adjustmentAmountError');

        let minAllowed = 0.01; // Minimum value must be greater than 0

        // Check if PO amount exists - sets minimum constraint only (can exceed)
        if (poAmountCell) {
            const poAmount = parseFloat(poAmountCell.textContent.replace(/,/g, '')) || 0;
            
            // If PO amount > 0, it's an active PO
            if (poAmount > 0) {
                minAllowed = poAmount; // Adjusted amount cannot be less than PO amount
            }
        }

        const currentValue = parseFloat(inputElement.value) || 0;

        // Clear error first
        inputElement.classList.remove('border-red-500');
        if (errorSpan) {
            errorSpan.innerText = '';
            errorSpan.classList.add('hidden');
        }

        // Check if value is zero or empty
        if (currentValue === 0 || inputElement.value.trim() === '') {
            inputElement.classList.add('border-red-500');
            if (errorSpan) {
                errorSpan.innerText = 'Adjusted amount must be greater than 0';
                errorSpan.classList.remove('hidden');
            }
            return false;
        }

        // Check if value is less than minimum allowed
        if (currentValue < minAllowed) {
            inputElement.classList.add('border-red-500');
            if (errorSpan) {
                if (minAllowed > 0.01) {
                    errorSpan.innerText = `Adjustment amount must be at least ${minAllowed.toFixed(2)} (Purchase Order amount)`;
                } else {
                    errorSpan.innerText = 'Adjusted amount must be greater than 0';
                }
                errorSpan.classList.remove('hidden');
            }
            return false;
        }

        return true;
    }

    function validateCreateObligationAdjustmentForm() {
    const remarks = document.getElementById('adjustment_remarks');
    const adjustmentDate = document.getElementById('adjustment_date');
    const adjustmentAmounts = document.querySelectorAll("input[name^='adjusted_amount']");

    let isValid = true;

    // Validate adjustment date
    if (!adjustmentDate.value.trim()) {
        adjustmentDate.classList.add('border-red-500');
        adjustmentDate.title = 'Adjustment date is required.';
        isValid = false;
    } else {
        adjustmentDate.classList.remove('border-red-500');
        adjustmentDate.title = '';
    }

    // Validate remarks
    if (!remarks.value.trim()) {
        document.getElementById('remarksError').innerText = 'Remarks are required.';
        isValid = false;
    } else {
        document.getElementById('remarksError').innerText = '';
    }

    // Validate adjustment amounts
    let atLeastOneNonZero = false;
    let hasZeroValue = false;
    let poValidationFailed = false;
    let allAdjustmentsNoChange = true;
    
    adjustmentAmounts.forEach(input => {
        const val = parseFloat(input.value);
        const inputTrimmed = input.value.trim();
        
        // Check if input has a value entered (even if it's 0)
        if (inputTrimmed !== '') {
            // Check for zero value
            if (val === 0) {
                hasZeroValue = true;
                input.classList.add('border-red-500');
                const row = input.closest('tr');
                const errorSpan = row.querySelector('.adjustmentAmountError');
                if (errorSpan) {
                    errorSpan.innerText = 'Adjusted amount must be greater than 0';
                    errorSpan.classList.remove('hidden');
                }
            } else if (!isNaN(val) && val > 0) {
                atLeastOneNonZero = true;
                
                // Check if adjusted amount is different from current obligation amount
                const row = input.closest('tr');
                const obrAmountCell = row.querySelector("td:nth-child(5)");
                const currentObrAmount = parseFloat(obrAmountCell.textContent.replace(/,/g, '')) || 0;
                
                // If adjusted amount is different from current, there's an actual change
                if (val !== currentObrAmount) {
                    allAdjustmentsNoChange = false;
                }
                
                // Check PO validation
                const poAmountCell = row.querySelector('.po-amount-cell');
                
                if (poAmountCell) {
                    const poAmount = parseFloat(poAmountCell.textContent.replace(/,/g, '')) || 0;
                    
                    // If active PO exists (amount > 0), adjusted amount cannot be less
                    if (poAmount > 0 && val < poAmount) {
                        poValidationFailed = true;
                        input.classList.add('border-red-500');
                        
                        const errorSpan = row.querySelector('.adjustmentAmountError');
                        if (errorSpan) {
                            errorSpan.innerText = `Adjustment amount must be at least ${poAmount.toFixed(2)} (Purchase Order amount)`;
                            errorSpan.classList.remove('hidden');
                        }
                    } else {
                        input.classList.remove('border-red-500');
                        const errorSpan = row.querySelector('.adjustmentAmountError');
                        if (errorSpan) {
                            errorSpan.innerText = '';
                            errorSpan.classList.add('hidden');
                        }
                    }
                }
            }
        }
    });
    
    const errorContainer = document.getElementById('tableMessage');
    
    if (hasZeroValue) {
        if (errorContainer) {
            errorContainer.innerText = 'Adjusted amounts cannot be zero. Please enter a value greater than 0 or leave the field empty.';
            errorContainer.classList.remove('hidden');
        }
        isValid = false;
    } else if (!atLeastOneNonZero) {
        if (errorContainer) {
            errorContainer.innerText = 'At least one adjustment amount must be entered.';
            errorContainer.classList.remove('hidden');
        }
        isValid = false;
    } else if (allAdjustmentsNoChange) {
        if (errorContainer) {
            errorContainer.innerText = 'No adjustment was made. The adjusted amounts are the same as the current obligation amounts.';
            errorContainer.classList.remove('hidden');
        }
        isValid = false;
    } else if (poValidationFailed) {
        if (errorContainer) {
            errorContainer.innerText = 'Adjusted amount cannot be less than Purchase Order amount for active POs.';
            errorContainer.classList.remove('hidden');
        }
        isValid = false;
    } else {
        if (errorContainer) {
            errorContainer.innerText = '';
            errorContainer.classList.add('hidden');
        }
    }

    if (isValid) {
        document.getElementById('createObligationAdjustmentForm').submit();
    }
}
    
    // Function to compute adjustment amount for each row
    function computeAdjustmentAmountForRow(row) {
        const obrAmountCell = row.querySelector("td:nth-child(5)");
        const adjustedAmountInput = row.querySelector("input[name^='adjusted_amount']");
        const adjustmentAmountCell = row.querySelector("td:nth-child(7)");

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

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('adjusted_amount')) {
            const row = event.target.closest('tr');
            if (row) {
                computeAdjustmentAmountForRow(row);
            }
        }
    });

    // Update the total calculation to only include non-zero values
function updateAdjustedAmountTotal() {
    const adjustedInputs = document.querySelectorAll("input[name^='adjusted_amount']");
    let total = 0;
    adjustedInputs.forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val) && val > 0) { // Changed from !== 0 to > 0
            total += val;
        }
    });
    const totalCell = document.getElementById('adjustedAmountTotalCell');
    if (totalCell) {
        totalCell.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}

    document.addEventListener('input', function(event) {
        if (event.target.name && event.target.name.startsWith('adjusted_amount')) {
            const row = event.target.closest('tr');
            if (row) {
                computeAdjustmentAmountForRow(row);
                updateAdjustedAmountTotal();
            }
        }
    });
</script>