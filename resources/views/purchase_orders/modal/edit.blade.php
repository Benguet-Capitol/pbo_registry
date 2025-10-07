@if(session('error'))
    <div class="text-red-500 text-sm mb-2">
        {{ session('error') }}
    </div>
@endif
<!-- Purchase Order Modal -->
 @foreach ($purchase_orders as $purchase_order)
<form id="EditPurchaseOrderForm" method="POST" action="{{ route('purchase_orders.update', $purchase_order->id) }}">
    @csrf
    @method('PUT')
    <div id="editPurchaseOrderModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Purchase Order') }}
                    </h3>
                    <button type="button" onclick="closeEditPurchaseOrderModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="purchase_order_id" id="purchase_order_id">
                            <!-- PO Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="po_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('PO Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="edit_po_date" autocomplete="off" id="edit_po_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.input withicon name="edit_po_number" autocomplete="off" id="edit_po_number" placeholder="{{ __('PO Number') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_po_numberError" class="text-red-500 text-sm"></span>
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
                                        <x-form.input withicon name="edit_pr_no" autocomplete="off" id="edit_pr_no" placeholder="{{ __('PR Number') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_pr_noError" class="text-red-500 text-sm"></span>
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
                                        <x-form.input withicon name="edit_delivery_period" autocomplete="off" id="edit_delivery_period" placeholder="{{ __('Delivery Period') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_delivery_periodError" class="text-red-500 text-sm"></span>
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
                                        <x-form.input withicon name="edit_supplier" autocomplete="off" id="edit_supplier" placeholder="{{ __('Supplier') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_supplierError" class="text-red-500 text-sm"></span>
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
                                        <x-form.textarea withicon name="edit_po_remarks" autocomplete="off" id="edit_po_remarks" placeholder="{{ __('Remarks') }}" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
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
                                                        <x-form.input
                                                            type="number"
                                                            name="edit_po_amount[{{ $obligationAmount->id }}]"
                                                            id="edit_po_amount_{{ $obligationAmount->id }}"
                                                            min="0"
                                                            step="0.01"
                                                            autocomplete="off"
                                                            oninput="validateAmount(this)"
                                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs"
                                                            placeholder=""
                                                            value="{{ old('edit_po_amount.' . $obligationAmount->id, $purchase_order->po_amount) }}"
                                                            data-balance="{{ $balance }}"
                                                        />
                                                        <span class="text-red-500 text-sm"></span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
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
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateFormEdit()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditPurchaseOrderModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endforeach

<script>
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
        const elements = document.querySelectorAll("input, select, textarea");

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

    function openEditPurchaseOrderModal(purchaseOrder) {
        // Close any open dropdowns or autocomplete suggestions
        closeAllDropdowns();

        document.querySelector("input[name='purchase_order_id']").value = purchaseOrder.id;

        document.getElementById('EditPurchaseOrderForm').action = `/purchase_orders/${purchaseOrder.id}`;

        document.getElementById('edit_po_date').value = purchaseOrder.po_date ?? '';
        updateTextColor(document.getElementById('edit_po_date'));
        document.getElementById('edit_po_number').value = purchaseOrder.po_number ?? '';
        updateTextColor(document.getElementById('edit_po_number'));
        document.getElementById('edit_pr_no').value = purchaseOrder.pr_no ?? '';
        updateTextColor(document.getElementById('edit_pr_no'));
        document.getElementById('edit_delivery_period').value = purchaseOrder.delivery_period ?? '';
        updateTextColor(document.getElementById('edit_delivery_period'));
        document.getElementById('edit_supplier').value = purchaseOrder.supplier ?? '';
        updateTextColor(document.getElementById('edit_supplier'));
        document.getElementById('edit_po_remarks').value = purchaseOrder.po_remarks ?? '';
        updateTextColor(document.getElementById('edit_po_remarks'));

        // --- Only show matching obligation row ---
        document.querySelectorAll('.obligation-row').forEach(row => row.classList.add('hidden'));

        const matchingRow = document.querySelector(`tr[data-obligation-id="${purchaseOrder.obligation_amounts_id}"]`);
        if (matchingRow) {
            matchingRow.classList.remove('hidden');
            const poAmountInput = document.querySelector(`input[name='edit_po_amount[${purchaseOrder.obligation_amounts_id}]']`);
            if (poAmountInput) {
                poAmountInput.value = purchaseOrder.po_amount ?? '';
            }
        }

        // Show the modal
        document.getElementById('editPurchaseOrderModal').classList.remove('hidden');
    }

    function closeEditPurchaseOrderModal() {
        document.getElementById('editPurchaseOrderModal').classList.add('hidden');
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
        const po_remarks = document.getElementById('edit_po_remarks');
        const po_number = document.getElementById('edit_po_number');
        const pr_no = document.getElementById('edit_pr_no');
        const delivery_period = document.getElementById('edit_delivery_period');
        const supplier = document.getElementById('edit_supplier');
        const adjustmentAmounts = document.querySelectorAll("input[name^='adjusted_amount']");
        const poInputs = document.querySelectorAll("input[name^='edit_po_amount']");

        let atLeastOnePOFilled = false;
        let isValid = true;

        // Validate PO Number
        if (!po_number.value.trim()) {
            document.getElementById('edit_po_numberError').innerText = 'PO Number is required.';
            isValid = false;
        } else {
            document.getElementById('edit_po_numberError').innerText = '';
        }
        // Validate PR Number
        if (!pr_no.value.trim()) {
            document.getElementById('edit_pr_noError').innerText = 'PR Number is required.';
            isValid = false;
        } else {
            document.getElementById('edit_pr_noError').innerText = '';
        }
        // Validate Delivery Period
        if (!delivery_period.value.trim()) {
            document.getElementById('edit_delivery_periodError').innerText = 'Delivery Period is required.';
            isValid = false;
        } else {
            document.getElementById('edit_delivery_periodError').innerText = '';
        }
        // Validate Supplier
        if (!supplier.value.trim()) {
            document.getElementById('edit_supplierError').innerText = 'Supplier is required.';
            isValid = false;
        } else {
            document.getElementById('edit_supplierError').innerText = '';
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
            document.getElementById('EditPurchaseOrderForm').submit();
        }
    } 

</script>