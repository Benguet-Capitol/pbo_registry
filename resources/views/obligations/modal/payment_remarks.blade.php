{{-- Payment Remarks Modal --}}
<div id="createPaymentRemarksContainer"></div>

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

<div id="paymentRemarksModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
    <div class="flex flex-col max-h-[90vh] w-full max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
        <!-- Modal header -->
        <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-b-2 border-blue-200 dark:border-blue-700 rounded-t-lg">
            <div class="flex items-center gap-3">
                <i class="fas fa-comment-dots text-blue-600 dark:text-blue-300 text-xl"></i>
                <h3 class="text-base leading-6 font-semibold text-blue-900 dark:text-blue-100">
                    Payment Remarks
                </h3>
            </div>
            <button type="button" onclick="closePaymentRemarksModal()" class="text-blue-600 dark:text-blue-300 hover:text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-full p-2 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body (scrollable) -->
        <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)]">
            <form id="paymentRemarksForm" method="POST" action="" class="p-6">
                @csrf
                <input type="hidden" name="year1" value="{{ request('year1') }}">
                <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
                <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="page" value="{{ request('page') }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        OBR No: <span id="paymentRemarksObrNo" class="font-bold text-blue-700 dark:text-blue-300"></span>
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
                        class="w-full px-3 py-2 text-xs text-gray-700 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600"
                        placeholder="Enter payment remarks"></x-form.textarea>
                </div>
            </form>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-4 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
            <button 
                type="submit" 
                form="paymentRemarksForm"
                onclick="if(isSubmittingPaymentRemarks) return false; isSubmittingPaymentRemarks = true;" 
                id="submitPaymentRemarksBtn"
                class="text-blue-600 dark:text-blue-400 inline-flex leading-4 tracking-wider hover:text-white border border-blue-600 dark:border-blue-500 hover:bg-blue-600 dark:hover:bg-blue-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-save mr-2"></i>
                Save
            </button>
            <button 
                type="button" 
                onclick="closePaymentRemarksModal()" 
                class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
    function openPaymentRemarksModal(obligationId, obrNo, paymentRemarks) {
        // Close any open dropdowns/menus
        if (typeof closeAllDropdowns === 'function') {
            closeAllDropdowns();
        }
        
        // Reset the submission flag
        isSubmittingPaymentRemarks = false;
        
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
        
        // Show modal with display flex
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        
        // Focus on textarea
        setTimeout(() => remarksTextarea.focus(), 100);
    }

    function closePaymentRemarksModal() {
        const modal = document.getElementById('paymentRemarksModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            
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
            if (paymentRemarksModal && paymentRemarksModal.style.display !== 'none') {
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