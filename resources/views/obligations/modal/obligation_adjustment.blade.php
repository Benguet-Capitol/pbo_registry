<div id="createObligationAdjustmentModalContainer"></div>

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

    .animate-scaleInUp {
        animation: scaleInUp 0.3s ease-out;
    }
</style>

<!-- Adjust Obligations Modal -->
<form id="createObligationAdjustmentForm" method="POST" action="{{ route('obligations.storeObligationAdjustment', ['obligation' => $obligation->id]) }}">
    @csrf
    <div id="createObligationAdjustmentModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-5xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-b-2 border-blue-200 dark:border-blue-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-plus-circle text-blue-600 dark:text-blue-300 text-xl"></i>
                    <div>
                        <h3 class="text-lg leading-6 font-semibold text-blue-900 dark:text-blue-100">
                            {{ __('Adjust Obligation') }}
                        </h3>
                        <span class="text-xs text-blue-700 dark:text-blue-300">
                            {{ $obligation->officeAllotmentClass->offices->office_abbreviation ?? 'N/A' }} - {{ $obligation->officeAllotmentClass->allotmentClass->class ?? 'N/A' }} | {{ $obligation->obr_no }}
                        </span>
                    </div>
                </div>
                <button type="button" onclick="window.closeCreateObligationAdjustmentModal()" class="text-blue-600 dark:text-blue-300 hover:text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body (scrollable) -->
            <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)] px-7 py-3 text-xs">

                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
                            <!-- Filter Parameters -->
                            <input type="hidden" name="search" value="{{ request('search') ?? '' }}">
                            <input type="hidden" name="search_column" value="{{ request('search_column') ?? '' }}">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by') ?? '' }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order') ?? '' }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page') ?? '' }}">
                            <input type="hidden" name="year1" value="{{ request('year1') ?? '' }}">
                            <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') ?? '' }}">
                            <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') ?? '' }}">
                            <input type="hidden" name="fund_filter" value="{{ request('fund_filter') ?? '' }}">
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="adjustment_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="adjustment_date" autocomplete="off" id="adjustment_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
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
                            <!-- Current Obligation Adjustments Table -->
                            <div class="sm:col-span-6">
                                <h4 class="text-sm text-gray-900 dark:text-gray-200 mb-3">Current Obligation Adjustments for this Obligation</h4>
                                @if($obligation->obligationAdjustments && $obligation->obligationAdjustments->count())
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Date</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Remarks</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Program</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Account Code</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Description</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Adjustment Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @php
                                            $lastAdjustmentRemarks = null;
                                            @endphp
                                            @foreach($obligation->obligationAdjustments as $adj)
                                            <tr>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $adj->adjustment_remarks !== $lastAdjustmentRemarks ? $adj->adjustment_date : '' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $adj->adjustment_remarks !== $lastAdjustmentRemarks ? $adj->adjustment_remarks : '' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($adj->appropriation)->programs ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($adj->appropriation)->account_code ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($adj->appropriation)->description ?? '-' }}</td>
                                                <td class="px-3 py-2 text-right text-xs text-gray-700 dark:text-gray-200">{{ number_format($adj->adjustment_amount, 2) }}</td>
                                            </tr>
                                            @php
                                            $lastAdjustmentRemarks = $adj->adjustment_remarks;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900 border-t border-gray-300 dark:border-gray-700">
                                                <td colspan="5" class="px-2 py-2 text-right text-xs text-gray-900 dark:text-gray-200 font-semibold">Total Adjustment Amount:</td>
                                                <td class="px-2 py-2 text-right text-xs text-green-700 dark:text-green-400 font-bold">
                                                    {{ number_format($obligation->obligationAdjustments->sum('adjustment_amount'), 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400">No Obligation Adjustments found for this Obligation.</p>
                                @endif
                            </div>

                            <!-- Programs Table -->
                            <div class="sm:col-span-6 mb-3">
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
                                                    {{ __('Balance from Allotment') }}
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
                                                $currentQuarter=1;
                                                } elseif ($currentMonth>= 4 && $currentMonth <= 6) {
                                                    $currentQuarter=2;
                                                    } elseif ($currentMonth>= 7 && $currentMonth <= 9) {
                                                        $currentQuarter=3;
                                                        } else {
                                                        $currentQuarter=4;
                                                        }

                                                        // Compute Total Appropriation (allotment base, only up to current quarter)
                                                        $totalAppropriation=0;
                                                        if ($appropriation) {
                                                        if ($currentQuarter>= 1) $totalAppropriation += $appropriation->quarter1 ?? 0;
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
                                                            <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200 hidden">
                                                                {{ number_format($balanceFromAllotment, 2) }}
                                                            </td>
                                                            <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">
                                                                <span class="adjustment-amount">0.00</span>
                                                            </td>
                                                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 w-40">
                                                                <x-form.input type="number" name="adjusted_amount[{{ $obligationAmount->id }}]" autocomplete="off" oninput="window.validateAmountAdjustment(this)" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" placeholder="" />
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
            <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                <x-input-error :messages="$errors->get('message')" class="mr-auto" />
                <button type="button" onclick="try { if(!window.isSubmittingObligationAdjustment) window.validateCreateObligationAdjustmentForm(); } catch(e) { console.error('Validation error:', e); }" id="submitAdjustmentBtn" class="text-blue-600 dark:text-blue-400 inline-flex leading-4 tracking-wider hover:text-white border border-blue-600 dark:border-blue-500 hover:bg-blue-600 dark:hover:bg-blue-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-save mr-2"></i>
                    {{ __('Save') }}
                </button>
                <button type="button" onclick="try { window.closeCreateObligationAdjustmentModal(); } catch(e) { console.error('Close error:', e); }" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Global flag to prevent double submission - ensure it's truly global
if (typeof window.isSubmittingObligationAdjustment === 'undefined') {
    window.isSubmittingObligationAdjustment = false;
}

// Validate individual amount adjustment input
window.validateAmountAdjustment = function(inputElement) {
    const value = inputElement.value;
    const errorElement = inputElement.parentElement.querySelector('.adjustmentAmountError');
    
    // Allow 0 values (creates a reduction adjustment: 0 - OBR_Amount)
    if (value === '' || value === null) {
        errorElement.textContent = 'This field is required';
        errorElement.classList.remove('hidden');
        return false;
    }
    
    // Check for valid number
    const numValue = parseFloat(value);
    if (isNaN(numValue)) {
        errorElement.textContent = 'Please enter a valid number';
        errorElement.classList.remove('hidden');
        return false;
    }
    
    // Get the obligation amount value for PO minimum check
    const row = inputElement.closest('tr');
    const obligationAmountCell = row?.querySelector('td:nth-child(6)');
    const obligationAmount = obligationAmountCell ? parseFloat(obligationAmountCell.textContent) : 0;
    
    // Only check PO minimum if adjusted amount is non-zero
    if (numValue !== 0) {
        // Check for minimum PO amount (assumed to be some threshold - adjust as needed)
        // This allows 0 but validates non-zero entries
        if (numValue < -obligationAmount) {
            errorElement.textContent = 'Adjusted amount cannot exceed negative obligation amount';
            errorElement.classList.remove('hidden');
            return false;
        }
    }
    
    errorElement.classList.add('hidden');
    errorElement.textContent = '';
    return true;
};

// Update total adjusted amount
window.updateAdjustedAmountTotal = function() {
    const inputs = document.querySelectorAll('input[name^="adjusted_amount"]');
    let total = 0;
    
    inputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });
    
    const totalCell = document.getElementById('adjustedAmountTotalCell');
    if (totalCell) {
        totalCell.textContent = total.toFixed(2);
        totalCell.classList.toggle('text-red-700 dark:text-red-400', total < 0);
        totalCell.classList.toggle('text-green-700 dark:text-green-400', total >= 0);
    }
};

// Validate entire form before submission
window.validateCreateObligationAdjustmentForm = function() {
    const inputs = document.querySelectorAll('input[name^="adjusted_amount"]');
    const appropriationSelect = document.getElementById('appropriationSelect');
    const remarkInput = document.getElementById('remarkInput');
    
    let isValid = true;
    let hasAtLeastOneAdjustment = false;
    
    // Validate each amount input
    inputs.forEach(input => {
        if (!window.validateAmountAdjustment(input)) {
            isValid = false;
        }
        
        const value = parseFloat(input.value) || 0;
        if (value !== 0) {
            hasAtLeastOneAdjustment = true;
        }
    });
    
    // Check if appropriation is selected
    if (!appropriationSelect || appropriationSelect.value === '') {
        isValid = false;
        const errorElement = appropriationSelect?.parentElement.querySelector('.appropriationError');
        if (errorElement) {
            errorElement.textContent = 'Please select an appropriation';
            errorElement.classList.remove('hidden');
        }
    }
    
    // Check if at least one adjustment amount is non-zero
    if (!hasAtLeastOneAdjustment) {
        isValid = false;
        const errorElements = document.querySelectorAll('.adjustmentAmountError');
        errorElements.forEach(el => {
            el.textContent = 'At least one obligation amount must have an adjusted amount';
            el.classList.remove('hidden');
        });
    }
    
    if (isValid) {
        window.isSubmittingObligationAdjustment = true;
        document.getElementById('createAdjustmentForm')?.submit();
    }
};

// Close the modal
window.closeCreateObligationAdjustmentModal = function() {
    const container = document.getElementById('createObligationAdjustmentModalContainer');
    if (container) {
        container.innerHTML = '';
    }
    window.isSubmittingObligationAdjustment = false;
};

// Update total when inputs change
document.addEventListener('input', function(e) {
    if (e.target.name && e.target.name.startsWith('adjusted_amount')) {
        window.updateAdjustedAmountTotal();
    }
});
</script>