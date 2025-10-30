<div id="deleteRealignmentModal" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative w-full max-w-md p-4 bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-semibold text-red-600 dark:text-white">{{ __('Delete Realignment | Augmentation') }}</h3>
                <button onclick="closeDeleteRealignmentModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                    <i class="fas fa-times h-6 w-6"></i>
                </button>
            </div>
            <div class="py-4">
                <p id="deleteRealignmentModalContent" class="text-gray-700 dark:text-gray-200">{{ __('Are you sure you want to delete this Realignment? This action cannot be undone.') }}</p>
            </div>
            <div class="flex justify-end pt-2">
                <form id="deleteRealignmentForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="year1" value="{{ request('year1') }}">
                    <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
                    <input type="hidden" name="realignment_type_filter" value="{{ request('realignment_type_filter') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <button type="submit" class="mr-1 text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                        <i class="fas fa-trash mr-1 -ml-1"></i>
                        {{ __('Delete') }}
                    </button>
                </form>
                <button onclick="closeDeleteRealignmentModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times mr-1 -ml-1"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Delete Modal for Related Realignments --}}
<div id="bulkDeleteRealignmentModal" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative w-full max-w-2xl p-4 bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-semibold text-orange-600 dark:text-white">{{ __('Delete All Related Realignments') }}</h3>
                <button onclick="closeBulkDeleteRealignmentModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                    <i class="fas fa-times h-6 w-6"></i>
                </button>
            </div>
            <div class="py-4">
                <p id="bulkDeleteRealignmentModalContent" class="text-gray-700 dark:text-gray-200 mb-4"></p>
                <div id="bulkDeleteRealignmentDetails" class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm max-h-96 overflow-y-auto"></div>
            </div>
            <div class="flex justify-end pt-2">
                <form id="bulkDeleteRealignmentForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="year1" value="{{ request('year1') }}">
                    <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
                    <input type="hidden" name="realignment_type_filter" value="{{ request('realignment_type_filter') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="bulk_delete" value="1">
                    <button type="submit" class="mr-1 text-orange-600 inline-flex items-center hover:text-white border border-orange-600 hover:bg-orange-600 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-orange-500 dark:text-orange-500 dark:hover:text-white dark:hover:bg-orange-600 dark:focus:ring-orange-900">
                        <i class="fas fa-trash-alt mr-1 -ml-1"></i>
                        {{ __('Delete All') }}
                    </button>
                </form>
                <button onclick="closeBulkDeleteRealignmentModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times mr-1 -ml-1"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>



<script>
    function openDeleteRealignmentModal(realignmentId, realignmentNo, realignmentType, realignmentAmount, appropriationsId) {
        closeAllDropdowns();

        // Find the appropriation object by ID
        let appropriation = appropriations.find(
            app => String(app.id) === String(appropriationsId)
        );
        let accountCode = appropriation ? appropriation.account_code : '';
        let description = appropriation ? appropriation.description : '';

        // Ensure Realignment Amount is a valid number before formatting
        let formattedRealignmentAmount = "0.00"; // Default value

        if (!isNaN(realignmentAmount) && realignmentAmount !== null && realignmentAmount !== "") {
            formattedRealignmentAmount = parseFloat(realignmentAmount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        document.getElementById('deleteRealignmentForm').action = '{{ route("realignments.destroy", ":id") }}'.replace(':id', realignmentId);
        document.getElementById('deleteRealignmentModalContent').innerHTML = `Are you sure you want to delete this Realignment No: <strong>${realignmentNo}</strong> with Type: <strong>${realignmentType}</strong>, Account Code: <strong>${accountCode}</strong> - <strong>${description}</strong> and Amount: <strong>${formattedRealignmentAmount}</strong>?</br>This action cannot be undone.`;
        document.getElementById('deleteRealignmentModal').classList.remove('hidden');
    }

    function closeDeleteRealignmentModal() {
        document.getElementById('deleteRealignmentModal').classList.add('hidden');
    }

    // Bulk Delete Modal
    function openBulkDeleteRealignmentModal(realignmentNo, currentRealignmentId) {
        closeAllDropdowns();

        // Get all realignments with the same realignment_no from the page data
        const realignments = @json($realignmentsBulkDelete ?? []);
        
        const relatedRealignments = realignments.filter(r => r.realignment_no === realignmentNo);
        
        if (relatedRealignments.length === 0) {
            alert('No related realignments found.');
            return;
        }

        // Build details list
        let detailsHtml = '<div class="space-y-2">';
        let totalAmount = 0;
        let sourceCount = 0;
        let recipientCount = 0;
        
        relatedRealignments.forEach(r => {
            let appropriation = appropriations.find(app => String(app.id) === String(r.appropriations_id));
            let accountCode = appropriation ? appropriation.account_code : 'N/A';
            let description = appropriation ? appropriation.description : 'N/A';
            let amount = parseFloat(r.amount) || 0;
            totalAmount += Math.abs(amount);
            
            if (r.type === 'Source') {
                sourceCount++;
            } else if (r.type === 'Recipient') {
                recipientCount++;
            }
            
            let typeColor = r.type === 'Source' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400';
            
            detailsHtml += `
                <div class="flex items-start justify-between p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                    <div class="flex-1">
                        <span class="font-semibold ${typeColor}">${r.type}</span>: 
                        <span class="text-gray-700 dark:text-gray-300">${accountCode}</span> - 
                        <span class="text-gray-600 dark:text-gray-400 text-xs">${description}</span>
                    </div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100 ml-2">
                        ₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}
                    </div>
                </div>
            `;
        });
        detailsHtml += '</div>';
        
        detailsHtml += `
            <div class="mt-4 pt-4 border-t border-gray-300 dark:border-gray-600 grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Total Records</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">${relatedRealignments.length}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Source: ${sourceCount} | Recipient: ${recipientCount}</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">Accounts</div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Total Amount</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                </div>
            </div>
        `;

        document.getElementById('bulkDeleteRealignmentModalContent').innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-orange-500 text-2xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">
                        Are you sure you want to delete <strong class="text-orange-600">all ${relatedRealignments.length} realignment(s)</strong> with Realignment No: <strong>${realignmentNo}</strong>?
                    </p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        This will permanently delete all source and recipient entries for this transaction.
                    </p>
                    <p class="mt-2 text-sm font-semibold text-red-600 dark:text-red-400">
                        ⚠️ This action cannot be undone!
                    </p>
                </div>
            </div>
        `;
        document.getElementById('bulkDeleteRealignmentDetails').innerHTML = detailsHtml;
        document.getElementById('bulkDeleteRealignmentForm').action = '{{ route("realignments.destroy", ":id") }}'.replace(':id', currentRealignmentId);
        document.getElementById('bulkDeleteRealignmentModal').classList.remove('hidden');
    }

    function closeBulkDeleteRealignmentModal() {
        document.getElementById('bulkDeleteRealignmentModal').classList.add('hidden');
    }
</script>