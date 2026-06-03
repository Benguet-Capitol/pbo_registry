<!-- Cancellation Modal -->
<form id="cancelObligationForm" method="POST">
    @csrf
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
    <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
    <input type="hidden" name="fund_filter" value="{{ request('fund_filter') }}">
    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="search_column" value="{{ request('search_column') }}">
    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
    
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

    <div id="cancellationModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900 dark:to-violet-900 border-b-2 border-purple-200 dark:border-purple-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-ban text-purple-600 dark:text-purple-300 text-xl"></i>
                    <h3 class="text-base leading-6 font-semibold text-purple-900 dark:text-purple-100">
                        Cancel Obligation
                    </h3>
                </div>
                <button type="button" onclick="closeCancellationModal()" class="text-purple-600 dark:text-purple-300 hover:text-white hover:bg-purple-600 dark:hover:bg-purple-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body (scrollable) -->
            <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)] p-6">
                <input type="hidden" id="hiddenObligationId" name="obligation_id" value="">
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">
                    Do you want to proceed with the cancellation of this Obligation? If cancelled, the obligation amount will be set to zero.
                </p>

                <div class="mt-4">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <tbody>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR Date:</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="obr_date"></td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Office Abbreviation:</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="office_abbreviation"></td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Allotment Class:</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="allotment_class"></td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR No:</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="obr_no"></td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR Type:</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="obr_type"></td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Particulars:</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="particulars"></td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Obligation Amount:</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-400" data-field="obr_amount"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <label for="cancellationRemarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remarks:</label>
                    <x-form.textarea id="cancellationRemarks" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="3" placeholder="Enter remarks..."></x-form.textarea>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
                <button type="button" onclick="try { if(!window.isSubmittingCancellation) window.proceedCancellation(); } catch(e) { console.error('Cancellation error:', e); }" id="submitCancellationBtn" class="text-red-600 dark:text-red-400 inline-flex leading-4 tracking-wider hover:text-white border border-red-600 dark:border-red-500 hover:bg-red-600 dark:hover:bg-red-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-window-close mr-2"></i>
                    Proceed
                </button>
                <button type="button" onclick="try { window.closeCancellationModal(); } catch(e) { console.error('Cancel modal error:', e); }" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Global flag to prevent double submission - ensure it's truly global
if (typeof window.isSubmittingCancellation === 'undefined') {
    window.isSubmittingCancellation = false;
}

function openCancellationModal(obligationId, obligationData) {
        closeAllDropdowns();
        window.isSubmittingCancellation = false;
        const modal = document.getElementById('cancellationModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        modal.dataset.obligationId = obligationId;

        // Set the hidden input
        document.getElementById('hiddenObligationId').value = obligationId;

        // Fill modal data
        document.querySelector('#cancellationModal td[data-field="obr_date"]').textContent = obligationData.obr_date;
        document.querySelector('#cancellationModal td[data-field="office_abbreviation"]').textContent = obligationData.office_abbreviation;
        document.querySelector('#cancellationModal td[data-field="allotment_class"]').textContent = obligationData.allotment_class;
        document.querySelector('#cancellationModal td[data-field="obr_no"]').textContent = obligationData.obr_no;
        document.querySelector('#cancellationModal td[data-field="obr_type"]').textContent = obligationData.obr_type;
        document.querySelector('#cancellationModal td[data-field="particulars"]').textContent = obligationData.particulars;
        document.querySelector('#cancellationModal td[data-field="obr_amount"]').textContent = Number(obligationData.obr_amount).toLocaleString(undefined, {
            minimumFractionDigits: 2
        });

        const proceedBtn = modal.querySelector('button[onclick="proceedCancellation()"]');
        const remarksBox = document.getElementById('cancellationRemarks');
        const messageContainerId = 'cancelNotice';

        // Remove any previous message
        const oldMessage = document.getElementById(messageContainerId);
        if (oldMessage) oldMessage.remove();

        // Check if obligation is already cancelled
        if (Number(obligationData.obr_amount) === 0) {
            // Disable button and textarea
            proceedBtn.disabled = true;
            remarksBox.disabled = true;

            // Add a note below the table
            const message = document.createElement('p');
            message.id = messageContainerId;
            message.className = 'text-red-600 text-sm mt-4 font-semibold';
            message.textContent = 'This obligation is already cancelled.';

            // Append after table
            modal.querySelector('div.p-6') ? modal.querySelector('div.p-6').appendChild(message) : null;
        } else {
            // Enable if it was previously disabled
            proceedBtn.disabled = false;
            remarksBox.disabled = false;
        }
    }

    window.closeCancellationModal = function() {
        const modal = document.getElementById('cancellationModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    };

    window.proceedCancellation = function() {
        // Prevent multiple submissions
        if (window.isSubmittingCancellation) {
            return false;
        }

        const modal = document.getElementById('cancellationModal');
        const obligationId = modal.dataset.obligationId;
        const remarks = document.getElementById('cancellationRemarks').value.trim();

        if (!remarks) {
            let errorSpan = document.getElementById('remarksError');
            if (!errorSpan) {
                errorSpan = document.createElement('span');
                errorSpan.id = 'remarksError';
                errorSpan.className = 'text-sm text-red-600 mt-1 block';
                document.getElementById('cancellationRemarks').parentNode.appendChild(errorSpan);
            }
            errorSpan.textContent = 'Remarks is required.';
            return;
        }

        // Set flag before submission
        window.isSubmittingCancellation = true;

        // Prepare the form
        const form = document.getElementById('cancelObligationForm');
        form.action = `/obligations/${obligationId}/cancel`;
        
        // Update or add remarks input while preserving other hidden inputs
        let remarksInput = form.querySelector('input[name="remarks"]');
        if (!remarksInput) {
            remarksInput = document.createElement('input');
            remarksInput.type = 'hidden';
            remarksInput.name = 'remarks';
            form.appendChild(remarksInput);
        }
        remarksInput.value = remarks;

        form.submit(); // Submit the form (will follow Laravel's redirect)
    };
</script>