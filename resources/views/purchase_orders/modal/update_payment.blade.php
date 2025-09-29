@if(session('error'))
    <div class="text-red-500 text-sm mb-2">
        {{ session('error') }}
    </div>
@endif
<!-- Update Status Modal -->
<form id="updatePaymentForm" method="POST" action="">
    @csrf
    <div id="updatePaymentModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Update Payment Status') }}
                    </h3>
                    <button type="button" onclick="closeUpdatePaymentModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="purchase_order_id" id="purchase_order_id">
                            
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
                                    <span id="po_numberError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Supplier -->
                            <div class="sm:col-span-3">
                                <x-form.label for="supplier" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Supplier')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-store"></i>
                                        </x-slot>
                                        <x-form.input withicon name="edit_supplier" autocomplete="off" id="edit_supplier" placeholder="{{ __('Supplier') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="supplierError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Delivery Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="delivery_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Delivery Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="delivery_date" autocomplete="off" id="delivery_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="sm:col-span-3">
                                <x-form.label for="status" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Status')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-arrow-up-right-dots"></i>
                                        </x-slot>
                                        <x-form.select withicon id="status" class="block w-full" type="text" name="status" placeholder="{{ __('Status') }}">
                                            <option value="">{{ __('Select Status') }}</option>
                                            <option value="Unpaid">{{ __('Unpaid') }}</option>
                                            <option value="Partially Paid">{{ __('Partially Paid') }}</option>
                                            <option value="Fully Paid">{{ __('Fully Paid') }}</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="obrTypeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Total Amount Paid -->
                            <div class="sm:col-span-6">
                                <x-form.label for="total_amount_paid" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Total Amount Paid')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </x-slot>
                                        <x-form.input withicon type="number" name="total_amount_paid" autocomplete="off" id="total_amount_paid" placeholder="{{ __('Total Amount Paid') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Delivery Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="delivery_remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.input withicon name="delivery_remarks" autocomplete="off" id="delivery_remarks" placeholder="{{ __('Remarks') }}" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
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
                    <button type="button" onclick="closeUpdatePaymentModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function openUpdatePaymentModal(purchaseOrder) {
        // Close any open dropdowns or autocomplete suggestions
        closeAllDropdowns();

        document.querySelector("input[name='purchase_order_id']").value = purchaseOrder.id;

        document.getElementById('EditPurchaseOrderForm').action = `/purchase_orders/${purchaseOrder.id}`;

        document.getElementById('edit_po_date').value = purchaseOrder.po_date ?? '';
        document.getElementById('edit_po_number').value = purchaseOrder.po_number ?? '';
        document.getElementById('edit_pr_no').value = purchaseOrder.pr_no ?? '';
        document.getElementById('edit_delivery_period').value = purchaseOrder.delivery_period ?? '';
        document.getElementById('edit_supplier').value = purchaseOrder.supplier ?? '';
        document.getElementById('edit_po_remarks').value = purchaseOrder.po_remarks ?? '';

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
        document.getElementById('updatePaymentModal').classList.remove('hidden');
    }

    function closeUpdatePaymentModal() {
        document.getElementById('updatePaymentModal').classList.add('hidden');
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
            document.getElementById('EditPurchaseOrderForm').submit();
        }
    } 
</script>