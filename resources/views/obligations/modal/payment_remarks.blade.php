{{-- Payment Remarks Modal --}}
<div id="createPaymentRemarksContainer"></div>
<div id="paymentRemarksModal" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center pb-3 mb-4 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Payment Remarks
                </h3>
                <button type="button" onclick="closePaymentRemarksModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                    <i class="fas fa-times h-6 w-6"></i>
                </button>
            </div>

            <form id="paymentRemarksForm" method="POST" action="">
                @csrf
                <input type="hidden" name="year1" value="{{ request('year1') }}">
                <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
                <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        OBR No: <span id="paymentRemarksObrNo" class="font-bold text-blue-700"></span>
                    </label>
                </div>

                <div class="mb-4">
                    <label for="payment_remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Payment Remarks
                    </label>
                    <x-form.textarea
                        id="payment_remarks" 
                        name="payment_remarks" 
                        rows="4" 
                        maxlength="1000"
                        class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600"
                        placeholder="Enter payment remarks"></x-form.textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button 
                        type="submit" 
                        class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-save mr-1"></i>
                        Save Remarks
                    </button>
                    <button 
                        type="button" 
                        onclick="closePaymentRemarksModal()" 
                        class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times mr-1"></i>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPaymentRemarksModal(obligationId, obrNo, paymentRemarks) {
        // Close any open dropdowns/menus
        if (typeof closeAllDropdowns === 'function') {
            closeAllDropdowns();
        }
        
        const modal = document.getElementById('paymentRemarksModal');
        const form = document.getElementById('paymentRemarksForm');
        const obrNoSpan = document.getElementById('paymentRemarksObrNo');
        const remarksTextarea = document.getElementById('payment_remarks');
        
        if (!modal || !form || !obrNoSpan || !remarksTextarea) {
            console.error('Payment remarks modal elements not found');
            return;
        }
        
        // Set form action
        form.action = `/obligations/${obligationId}/payment-remarks`;
        
        // Set OBR No display
        obrNoSpan.textContent = obrNo;
        
        // Set existing payment remarks
        remarksTextarea.value = paymentRemarks || '';
        
        // Show modal
        modal.classList.remove('hidden');
        
        // Focus on textarea
        setTimeout(() => remarksTextarea.focus(), 100);
    }

    function closePaymentRemarksModal() {
        const modal = document.getElementById('paymentRemarksModal');
        if (modal) {
            modal.classList.add('hidden');
            
            // Reset form
            const form = document.getElementById('paymentRemarksForm');
            if (form) {
                form.reset();
            }
        }
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const paymentRemarksModal = document.getElementById('paymentRemarksModal');
            if (paymentRemarksModal && !paymentRemarksModal.classList.contains('hidden')) {
                closePaymentRemarksModal();
            }
        }
    });

    // Close modal when clicking outside
    const paymentRemarksModalElement = document.getElementById('paymentRemarksModal');
    if (paymentRemarksModalElement) {
        paymentRemarksModalElement.addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentRemarksModal();
            }
        });
    }
</script>