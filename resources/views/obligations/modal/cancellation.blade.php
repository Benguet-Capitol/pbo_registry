<!-- Cancellation Modal -->
<form id="cancelObligationForm" method="POST">
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
    <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <div id="cancellationModal" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        Cancel Obligation
                    </h3>
                    <button type="button" onclick="closeCancellationModal()" class="text-black hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-6">
                    <input type="hidden" id="hiddenObligationId" name="obligation_id" value="">
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">
                        Do you want to proceed with the cancellation of this Obligation? If cancelled, the obligation amount will be set to zero.
                    </p>

                    <div class="mt-2">
                        <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400">
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
                <div class="flex justify-end p-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" onclick="proceedCancellation()" class="text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                        <i class="fas fa-window-close mr-1"></i>
                        Proceed with Cancellation
                    </button>
                    <button type="button" onclick="closeCancellationModal()" class="ml-1 text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times mr-1 -ml-1"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function openCancellationModal(obligationId, obligationData) {
        closeAllDropdowns();
        const modal = document.getElementById('cancellationModal');
        modal.classList.remove('hidden');

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
            modal.querySelector('.p-6').appendChild(message);
        } else {
            // Enable if it was previously disabled
            proceedBtn.disabled = false;
            remarksBox.disabled = false;
        }
    }

    function closeCancellationModal() {
        const modal = document.getElementById('cancellationModal');
        modal.classList.add('hidden');
    }

    function proceedCancellation() {
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

        // Prepare the form
        const form = document.getElementById('cancelObligationForm');
        form.action = `/obligations/${obligationId}/cancel`;
        form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        <input type="hidden" name="remarks" value="${remarks}">
    `;

        form.submit(); // Submit the form (will follow Laravel's redirect)
    }
</script>