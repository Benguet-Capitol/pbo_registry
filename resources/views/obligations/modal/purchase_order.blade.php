@if(session('error'))
    <div class="text-red-500 text-sm mb-2">
        {{ session('error') }}
    </div>
@endif
<!-- Container for AJAX-loaded Purchase Order Modal -->
<div id="createPOModalContainer"></div>

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

<!-- Purchase Order Modal -->
<form id="CreatePurchaseOrderForm" method="POST" action="{{ isset($obligation->id) && $obligation->id ? route('obligations.storePurchaseOrder', ['obligation' => $obligation->id]) : '#' }}">
    @csrf
    <div id="createPOModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-5xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-b-2 border-blue-200 dark:border-blue-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-plus-circle text-blue-600 dark:text-blue-300 text-xl"></i>
                    <div>
                        <h3 class="text-lg leading-6 font-semibold text-blue-900 dark:text-blue-100">
                            {{ __('Add Purchase Order') }}
                        </h3>
                        <span class="text-xs text-blue-700 dark:text-blue-300">
                            {{ $obligation->officeAllotmentClass->offices->office_abbreviation ?? 'N/A' }} - {{ $obligation->officeAllotmentClass->allotmentClass->class ?? 'N/A' }} | {{ $obligation->obr_no ?? 'N/A' }}
                        </span>
                    </div>
                </div>
                <button type="button" onclick="closeCreatePOModal()" class="text-blue-600 dark:text-blue-300 hover:text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body (scrollable) -->
            <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)] px-6 py-3 text-xs">
                <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" value="{{ $obligation->id ?? '' }}">
                            <input type="hidden" name="po_source" value="">
                            <input type="hidden" name="search" value="{{ request('search') ?? '' }}">
                            <input type="hidden" name="search_column" value="{{ request('search_column') ?? '' }}">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by') ?? '' }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order') ?? '' }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page') ?? '' }}">
                            <input type="hidden" name="year1" value="{{ request('year1') ?? '' }}">
                            <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') ?? '' }}">
                            <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') ?? '' }}">
                            <input type="hidden" name="fund_filter" value="{{ request('fund_filter') ?? '' }}">
                            <!-- PO Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="po_date" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('PO Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="po_date" autocomplete="off" id="po_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span class="poDateError text-red-500 text-xs hidden"></span>
                                </div>
                            </div>
                            <!-- PO Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="po_number" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('PO Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-hashtag"></i>
                                        </x-slot>
                                        <x-form.input withicon name="po_number" autocomplete="off" id="po_number" placeholder="{{ __('PO Number') }}" :value="old('po_number')" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="po_numberError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- PR Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="pr_no" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('PR Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon name="pr_no" autocomplete="off" id="pr_no" placeholder="{{ __('PR Number') }}" :value="old('pr_no')" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="pr_noError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- Delivery Period -->
                            <div class="sm:col-span-3">
                                <x-form.label for="delivery_period" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Delivery Period')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar-day"></i>
                                        </x-slot>
                                        <x-form.input withicon name="delivery_period" autocomplete="off" id="delivery_period" placeholder="{{ __('Delivery Period') }}" :value="old('delivery_period')" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="delivery_periodError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- Supplier -->
                            <div class="sm:col-span-6">
                                <x-form.label for="supplier" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Supplier')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-store"></i>
                                        </x-slot>
                                        <x-form.input withicon name="supplier" autocomplete="off" id="supplier" placeholder="{{ __('Supplier') }}" :value="old('supplier')" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="supplierError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="po_remarks" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="po_remarks" autocomplete="off" id="po_remarks" placeholder="{{ __('Remarks') }}" :value="old('po_remarks')" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Current Purchase Orders Table -->
                            <div class="sm:col-span-6">
                                <h4 class="text-sm text-gray-900 dark:text-gray-200 mb-3">Current Purchase Orders for this Obligation</h4>
                                @if(isset($obligation->purchaseOrders) && $obligation->purchaseOrders && $obligation->purchaseOrders->count())
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">PO Number</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">PO Date</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Supplier</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Program</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Account Code</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Description</th>
                                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @php
                                                $lastPoNumber = null;
                                            @endphp
                                            @foreach($obligation->purchaseOrders as $po)
                                            <tr>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $po->po_number !== $lastPoNumber ? $po->po_number : '' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $po->po_number !== $lastPoNumber ? $po->po_date : '' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $po->po_number !== $lastPoNumber ? $po->supplier : '' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($po->obligationAmount->appropriation)->programs ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($po->obligationAmount->appropriation)->account_code ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($po->obligationAmount->appropriation)->description ?? '-' }}</td>
                                                <td class="px-2 py-2 text-right text-xs text-gray-700 dark:text-gray-200">{{ number_format($po->po_amount, 2) }}</td>
                                            </tr>
                                            @php
                                                $lastPoNumber = $po->po_number;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900 border-t border-gray-300 dark:border-gray-700">
                                                <td colspan="5" class="px-2 py-2 text-right text-xs text-gray-900 dark:text-gray-200 font-semibold">Total Obligation:   <span class="text-green-700 dark:text-green-300 font-semibold">{{ number_format($totalObligationAmount, 2) }}</span></td>
                                                <td colspan="2" class="px-2 py-2 text-right text-xs text-gray-900 dark:text-gray-200 font-semibold">Total Purchase Order:   <span class="text-blue-700 dark:text-blue-300 font-semibold">{{ number_format($totalPOAmount, 2) }}</span></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400">No Purchase Orders found for this Obligation.</p>
                                @endif
                            </div>

                            <!-- Programs Table -->
                            <div class="sm:col-span-6 mb-3">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessagePO" class="text-red-500 text-sm hidden mb-2"></div>
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
                                                    {{ __('Purchase Order Amount') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($obligationAmounts as $obligationAmount)
                                            @php
                                                $totalPOs = \App\Models\PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)->sum('po_amount');
                                                $adjustments = \App\Models\ObligationAdjustment::where('obligation_amounts_id', $obligationAmount->id)->sum('adjustment_amount');
                                                $balance = ($obligationAmount->obr_amount - $totalPOs) + $adjustments;
                                                $appropriation = $obligationAmount->appropriation;
                                            @endphp
                                            <tr>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->programs ?? '-' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->account_code ?? '-' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->description ?? '-' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">
                                                    <span class="adjustment-amount">{{ number_format($balance, 2) }}</span>
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    <x-form.input type="number" name="po_amount[{{ $obligationAmount->id }}]" min="0" step="0.01" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" placeholder="" oninput="validateAmountPO(this)" data-balance="{{ $balance  }}" />
                                                    <span class="poAmountError text-red-500 text-xs hidden"></span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td colspan="4" class="px-2 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-200">Total Purchase Order:</td>
                                                <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400" id="poAmountTotalCell">0.00</td>
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
            <!-- Modal footer -->
            <div class="flex justify-end gap-3 p-4 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                <x-input-error :messages="$errors->get('message')" class="mr-auto" />
                <button type="button" onclick="if(!isSubmittingPurchaseOrder) { validateFormCreatePO(); }" id="submitPOBtn" class="text-blue-600 dark:text-blue-400 inline-flex leading-4 tracking-wider hover:text-white border border-blue-600 dark:border-blue-500 hover:bg-blue-600 dark:hover:bg-blue-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-save mr-2"></i>
                    {{ __('Save') }}
                </button>
                <button type="button" onclick="closeCreatePOModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Global flag to prevent double submission
let isSubmittingPurchaseOrder = false;

// Close the PO modal
function closeCreatePOModal() {
    const modal = document.getElementById('createPOModal');
    if (modal) {
        modal.style.display = 'none';
    }
    isSubmittingPurchaseOrder = false;
}

// Validate entire PO form
function validateFormCreatePO() {
    // Prevent multiple submissions
    if (isSubmittingPurchaseOrder) {
        return false;
    }

    const inputs = document.querySelectorAll('input[name^="po_amount"]');
    const poDateInput = document.getElementById('po_date');
    
    let isValid = true;
    let hasAtLeastOnePO = false;
    
    // Validate each PO amount input
    inputs.forEach(input => {
        const maxBalance = parseFloat(input.dataset.balance || "0");
        const inputValue = parseFloat(input.value || "0");
        const errorElement = input.parentElement?.querySelector('.poAmountError');
        
        // Clear previous errors
        if (errorElement) {
            errorElement.classList.add('hidden');
            errorElement.textContent = '';
        }
        
        // Check if amount exceeds balance
        if (inputValue > maxBalance) {
            if (errorElement) {
                errorElement.textContent = `Amount cannot exceed balance of ₱${maxBalance.toFixed(2)}`;
                errorElement.classList.remove('hidden');
            }
            isValid = false;
        }
        
        if (inputValue > 0) {
            hasAtLeastOnePO = true;
        }
    });
    
    // Check if PO date is selected
    if (!poDateInput || poDateInput.value === '') {
        isValid = false;
        if (poDateInput) {
            const errorElement = poDateInput.parentElement?.querySelector('.poDateError');
            if (errorElement) {
                errorElement.textContent = 'Please select a PO date';
                errorElement.classList.remove('hidden');
            }
        }
    }
    
    // Check if at least one PO amount is greater than zero
    if (!hasAtLeastOnePO) {
        isValid = false;
        const errorElements = document.querySelectorAll('.poAmountError');
        errorElements.forEach(el => {
            el.textContent = 'At least one obligation amount must have a purchase order amount';
            el.classList.remove('hidden');
        });
    }
    
    if (isValid) {
        isSubmittingPurchaseOrder = true;
        
        // Detect source (dashboard or accounts) and set hidden input
        const form = document.getElementById('CreatePurchaseOrderForm');
        const pathname = window.location.pathname;
        const sourceInput = form.querySelector('input[name="po_source"]');
        
        if (pathname.includes('dashboard/accounts')) {
            if (sourceInput) sourceInput.value = 'accounts';
        } else if (pathname.includes('dashboard')) {
            if (sourceInput) sourceInput.value = 'dashboard';
        }
        
        form?.submit();
    }
}
</script>