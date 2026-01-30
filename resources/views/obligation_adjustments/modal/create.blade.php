<!-- Adjust Obligations Modal -->
<form id="createObligationAdjustmentForm" method="POST" action="{{ route('obligation_adjustments.store') }}">
    @csrf
    <div id="createObligationAdjustmentModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-600 dark:text-blue-400"></i>
                    {{ __('Adjust Obligation') }}
                </h3>
                <button type="button" onclick="closeCreateObligationAdjustmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" value="{{ $obligation->id ?? '' }}">
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
                                <div class="mt-2 overflow-auto" style="max-height: 500px;">
                                    <!-- Display Obligation Amounts and Appropriations in Programs Table -->
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
                                            @if(isset($obligationAmounts) && $obligationAmounts->isNotEmpty())
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
                                            @endif
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td colspan="6" class="px-2 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-200">Total Adjusted Amount:</td>
                                                <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400" id="adjustedAmountTotalCell">0.00</td>
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
                    <button type="button" onclick="if(!isSubmittingCreateObligationAdjustment) validateCreateObligationAdjustmentForm(); else return false;" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateObligationAdjustmentModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Prevent multiple submissions
    let isSubmittingCreateObligationAdjustment = false;

    function openCreateObligationAdjustmentModal(obligationId) {
        if (typeof closeAllDropdowns === 'function') {
            closeAllDropdowns();
        }
        
        // If obligationId is provided, fetch the modal HTML with pre-populated data
        if (obligationId) {
            // Called from dashboard/accounts - fetch the complete modal HTML
            fetch(`/obligations/${obligationId}/obligation-adjustment-modal`)
                .then(response => response.text())
                .then(html => {
                    // Find the existing form and replace it entirely with the new HTML
                    const existingForm = document.getElementById('createObligationAdjustmentForm');
                    if (existingForm) {
                        // Create a temporary container to parse the HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        const newForm = temp.querySelector('form');
                        
                        if (newForm) {
                            // Replace the old form with the new one
                            existingForm.replaceWith(newForm);
                            
                            // Show the modal after replacement
                            setTimeout(() => {
                                const modal = document.getElementById('createObligationAdjustmentModal');
                                if (modal) {
                                    modal.style.display = 'flex';
                                    modal.setAttribute('aria-hidden', 'false');
                                    console.log('Modal opened with pre-populated data');
                                }
                            }, 10);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading obligation adjustment modal:', error);
                    alert('Failed to load adjustment modal. Please try again.');
                });
        } else {
            // Called from obligation_adjustments index - modal already has data from Blade
            const modal = document.getElementById('createObligationAdjustmentModal');
            if (modal) {
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            }
        }
    }

    function closeCreateObligationAdjustmentModal() {
        const modal = document.getElementById('createObligationAdjustmentModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
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

        // Clear error first
        inputElement.classList.remove('border-red-500');
        if (errorSpan) {
            errorSpan.innerText = '';
            errorSpan.classList.add('hidden');
        }

        const inputTrimmed = inputElement.value.trim();
        
        // Allow empty values (no adjustment for this row)
        if (inputTrimmed === '') {
            return true;
        }

        const currentValue = parseFloat(inputElement.value);
        
        // If value is not a valid number, show error
        if (isNaN(currentValue)) {
            inputElement.classList.add('border-red-500');
            if (errorSpan) {
                errorSpan.innerText = 'Please enter a valid number';
                errorSpan.classList.remove('hidden');
            }
            return false;
        }

        // For non-zero values, check PO minimum constraint
        if (currentValue !== 0) {
            let minAllowed = 0;
            
            // Check if PO amount exists - sets minimum constraint only (can exceed)
            if (poAmountCell) {
                const poAmount = parseFloat(poAmountCell.textContent.replace(/,/g, '')) || 0;
                
                // If PO amount > 0, it's an active PO
                if (poAmount > 0) {
                    minAllowed = poAmount; // Adjusted amount cannot be less than PO amount
                }
            }

            // Check if value is less than minimum allowed
            if (currentValue < minAllowed) {
                inputElement.classList.add('border-red-500');
                if (errorSpan) {
                    if (minAllowed > 0) {
                        errorSpan.innerText = `Adjusted amount must be at least ${minAllowed.toFixed(2)} (Purchase Order amount)`;
                    }
                    errorSpan.classList.remove('hidden');
                }
                return false;
            }
        }

        return true;
    }

    function validateCreateObligationAdjustmentForm() {
    if (isSubmittingCreateObligationAdjustment) return false;
    
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
    let atLeastOneValidAdjustment = false;
    let poValidationFailed = false;
    
    adjustmentAmounts.forEach(input => {
        const val = parseFloat(input.value);
        const inputTrimmed = input.value.trim();
        
        // Check if input has a value entered (including 0)
        if (inputTrimmed !== '') {
            if (!isNaN(val)) {
                // Check if adjusted amount is different from current obligation amount
                const row = input.closest('tr');
                const obrAmountCell = row.querySelector("td:nth-child(5)");
                const currentObrAmount = parseFloat(obrAmountCell.textContent.replace(/,/g, '')) || 0;
                
                // If adjusted amount is different from current, there's an actual adjustment
                if (val.toFixed(2) !== currentObrAmount.toFixed(2)) {
                    atLeastOneValidAdjustment = true;
                }
                
                // Check PO validation - only applies to non-zero adjusted amounts
                const poAmountCell = row.querySelector('.po-amount-cell');
                
                if (poAmountCell && val !== 0) {
                    const poAmount = parseFloat(poAmountCell.textContent.replace(/,/g, '')) || 0;
                    
                    // If active PO exists (amount > 0), adjusted amount cannot be less
                    if (poAmount > 0 && val < poAmount) {
                        poValidationFailed = true;
                        input.classList.add('border-red-500');
                        
                        const errorSpan = row.querySelector('.adjustmentAmountError');
                        if (errorSpan) {
                            errorSpan.innerText = `Adjusted amount must be at least ${poAmount.toFixed(2)} (Purchase Order amount)`;
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
    });
    
    const errorContainer = document.getElementById('tableMessage');
    
    if (!atLeastOneValidAdjustment) {
        if (errorContainer) {
            errorContainer.innerText = 'At least one Adjusted Amount must differ from the current Amount of Obligation.';
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
        isSubmittingCreateObligationAdjustment = true;
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

    // Update the total calculation to include all values (including 0)
    function updateAdjustedAmountTotal() {
        const adjustedInputs = document.querySelectorAll("input[name^='adjusted_amount']");
        let total = 0;
        adjustedInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val)) {
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