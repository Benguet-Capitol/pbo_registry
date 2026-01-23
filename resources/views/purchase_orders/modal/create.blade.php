@if(session('error'))
    <div class="text-red-500 text-sm mb-2">
        {{ session('error') }}
    </div>
@endif
<!-- Purchase Order Modal -->
<form id="CreatePurchaseOrderForm" method="POST" action="{{ route('purchase_orders.store') }}">
    @csrf
    <div id="createModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-600 dark:text-blue-400"></i>
                    {{ __('Add Purchase Order') }}
                </h3>
                <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
                            <!-- PO Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="po_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('PO Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="po_date" autocomplete="off" id="po_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- PO Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="po_number" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('PO Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-hashtag"></i>
                                        </x-slot>
                                        <x-form.input withicon name="po_number" autocomplete="off" id="po_number" placeholder="{{ __('PO Number') }}" :value="old('po_number')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="po_numberError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- PR Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="pr_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('PR Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon name="pr_no" autocomplete="off" id="pr_no" placeholder="{{ __('PR Number') }}" :value="old('pr_no')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="pr_noError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Delivery Period -->
                            <div class="sm:col-span-3">
                                <x-form.label for="delivery_period" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Delivery Period')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar-day"></i>
                                        </x-slot>
                                        <x-form.input withicon name="delivery_period" autocomplete="off" id="delivery_period" placeholder="{{ __('Delivery Period') }}" :value="old('delivery_period')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="delivery_periodError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Supplier -->
                            <div class="sm:col-span-6">
                                <x-form.label for="supplier" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Supplier')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-store"></i>
                                        </x-slot>
                                        <x-form.input withicon name="supplier" autocomplete="off" id="supplier" placeholder="{{ __('Supplier') }}" :value="old('supplier')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="supplierError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="po_remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="po_remarks" autocomplete="off" id="po_remarks" placeholder="{{ __('Remarks') }}" :value="old('po_remarks')" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
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
                                                    <x-form.input type="number" name="po_amount[{{ $obligationAmount->id }}]" min="0" step="0.01" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" placeholder="" oninput="validateAmount(this)" data-balance="{{ $balance  }}" />
                                                    <span class="text-red-500 text-sm"></span>
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

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateFormCreate()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
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

<script>
    function openCreateModal() {
        closeAllDropdowns();
        const modal = document.getElementById('createModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
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

    function updatePOAmountTotal() {
        const poInputs = document.querySelectorAll("input[name^='po_amount']");
        let total = 0;
        poInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                total += val;
            }
        });
        const totalCell = document.getElementById('poAmountTotalCell');
        if (totalCell) {
            totalCell.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
    document.querySelectorAll("input[name^='po_amount']").forEach(input => {
        input.addEventListener('input', updatePOAmountTotal);
    });

    function validateFormCreate() {
        const po_remarks = document.getElementById('po_remarks');
        const po_number = document.getElementById('po_number');
        const pr_no = document.getElementById('pr_no');
        const delivery_period = document.getElementById('delivery_period');
        const supplier = document.getElementById('supplier');
        const adjustmentAmounts = document.querySelectorAll("input[name^='adjusted_amount']");
        const poInputs = document.querySelectorAll("input[name^='po_amount']");

        let atLeastOnePOFilled = false;
        let isValid = true;

        // Validate PO Number
        if (!po_number.value.trim()) {
            document.getElementById('po_numberError').innerText = 'PO Number is required.';
            isValid = false;
        } else {
            document.getElementById('po_numberError').innerText = '';
        }
        // Validate PR Number
        if (!pr_no.value.trim()) {
            document.getElementById('pr_noError').innerText = 'PR Number is required.';
            isValid = false;
        } else {
            document.getElementById('pr_noError').innerText = '';
        }
        // Validate Delivery Period
        if (!delivery_period.value.trim()) {
            document.getElementById('delivery_periodError').innerText = 'Delivery Period is required.';
            isValid = false;
        } else {
            document.getElementById('delivery_periodError').innerText = '';
        }
        // Validate Supplier
        if (!supplier.value.trim()) {
            document.getElementById('supplierError').innerText = 'Supplier is required.';
            isValid = false;
        } else {
            document.getElementById('supplierError').innerText = '';
        }

        // Ensure at least one po_amount is filled
        poInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                atLeastOnePOFilled = true;
            }
        });

        if (!atLeastOnePOFilled) {
            document.getElementById('tableMessage').classList.remove('hidden');
            document.getElementById('tableMessage').innerText = 'Enter at least one Purchase Order amount.';
            isValid = false;
        } else {
            document.getElementById('tableMessage').classList.add('hidden');
            document.getElementById('tableMessage').innerText = '';
        }

        if (isValid) {
            document.getElementById('CreatePurchaseOrderForm').submit();
        }
    }
</script>