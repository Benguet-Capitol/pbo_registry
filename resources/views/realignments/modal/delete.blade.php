<div id="deleteRealignmentModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                {{ __('Delete Realignment | Augmentation') }}
            </h3>
            <button onclick="closeDeleteRealignmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal body -->
        <div class="px-6 py-4">
            <p id="deleteRealignmentModalContent" class="text-gray-900 dark:text-gray-200">{{ __('Are you sure you want to delete this Realignment? This action cannot be undone.') }}</p>
        </div>
        <!-- Modal footer -->
        <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
            <form id="deleteRealignmentForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <input type="hidden" name="year1" value="{{ request('year1') }}">
                <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
                <input type="hidden" name="realignment_type_filter" value="{{ request('realignment_type_filter') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <button type="submit" class="text-red-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-trash text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Delete') }}
                </button>
            </form>
            <button onclick="closeDeleteRealignmentModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                {{ __('Cancel') }}
            </button>
        </div>
    </div>
</div>

{{-- Bulk Delete Modal for Related Realignments --}}
<div id="bulkDeleteRealignmentModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-2xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                {{ __('Delete All Related Realignments') }}
            </h3>
            <button onclick="closeBulkDeleteRealignmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal body -->
        <div class="px-6 py-4">
            <p id="bulkDeleteRealignmentModalContent" class="text-gray-900 dark:text-gray-200 mb-4"></p>
            <div id="bulkDeleteRealignmentDetails" class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm max-h-96 overflow-y-auto"></div>
        </div>
        <!-- Modal footer -->
        <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
            <form id="bulkDeleteRealignmentForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <input type="hidden" name="year1" value="{{ request('year1') }}">
                <input type="hidden" name="office_allotment_class_id" value="{{ request('office_allotment_class_id') }}">
                <input type="hidden" name="realignment_type_filter" value="{{ request('realignment_type_filter') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="bulk_delete" value="1">
                <button type="submit" class="text-red-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-trash-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Delete All') }}
                </button>
            </form>
            <button type="button" onclick="closeBulkDeleteRealignmentModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                {{ __('Cancel') }}
            </button>
        </div>
    </div>
</div>

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

        // Validate deletion date before showing modal
        fetch(`/api/realignments/check-deletion-date`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                realignment_id: realignmentId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Set form action with proper route
            const deleteForm = document.getElementById('deleteRealignmentForm');
            if (deleteForm) {
                deleteForm.action = `/realignments/${realignmentId}`; // Direct path
                console.log('Form action set to:', deleteForm.action);
            }

            let contentHtml = `
                Are you sure you want to delete this Realignment No: <strong>${realignmentNo}</strong> with Type: <strong>${realignmentType}</strong>, Account Code: <strong>${accountCode}</strong> - <strong>${description}</strong> and Amount: <strong>${formattedRealignmentAmount}</strong>?
                <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Note:</strong> This will only delete this specific account entry. Other accounts under the same Realignment No. will remain.
                    </p>
                </div>
                <p class="mt-3 text-red-600 dark:text-red-400 font-semibold">This action cannot be undone.</p>
            `;

            // Add date validation error if deletion is not allowed
            if (!data.canDelete) {
                contentHtml += `
                    <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded">
                        <p class="text-sm text-red-800 dark:text-red-200">
                            <i class="fas fa-ban mr-1"></i>
                            <strong>Cannot Delete:</strong> ${data.message}
                        </p>
                        <p class="text-xs text-red-700 dark:text-red-300 mt-2">
                            Realignment Date: ${data.realignment_date}<br>
                            Earliest Obligation Date: ${data.earliest_obligation_date}
                        </p>
                    </div>
                `;
                document.getElementById('deleteRealignmentModalContent').innerHTML = contentHtml;
                const modal = document.getElementById('deleteRealignmentModal');
                const deleteButton = modal.querySelector('button[onclick*="submitDeleteRealignmentForm"]');
                if (deleteButton) {
                    deleteButton.disabled = true;
                    deleteButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            } else {
                document.getElementById('deleteRealignmentModalContent').innerHTML = contentHtml;
                const modal = document.getElementById('deleteRealignmentModal');
                const deleteButton = modal.querySelector('button[onclick*="submitDeleteRealignmentForm"]');
                if (deleteButton) {
                    deleteButton.disabled = false;
                    deleteButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            const modal = document.getElementById('deleteRealignmentModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        })
        .catch(error => {
            console.error('Error checking realignment deletion date:', error);
            // Show error message
            const deleteForm = document.getElementById('deleteRealignmentForm');
            if (deleteForm) {
                deleteForm.action = `/realignments/${realignmentId}`;
            }

            document.getElementById('deleteRealignmentModalContent').innerHTML = `
                Are you sure you want to delete this Realignment No: <strong>${realignmentNo}</strong> with Type: <strong>${realignmentType}</strong>, Account Code: <strong>${accountCode}</strong> - <strong>${description}</strong> and Amount: <strong>${formattedRealignmentAmount}</strong>?
                <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Note:</strong> This will only delete this specific account entry. Other accounts under the same Realignment No. will remain.
                    </p>
                </div>
                <p class="mt-3 text-red-600 dark:text-red-400 font-semibold">This action cannot be undone.</p>
            `;

            const modal = document.getElementById('deleteRealignmentModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        });
    }

    function closeDeleteRealignmentModal() {
        const modal = document.getElementById('deleteRealignmentModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
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
        // Set form action
    const bulkForm = document.getElementById('bulkDeleteRealignmentForm');
    if (bulkForm) {
        bulkForm.action = `/realignments/${currentRealignmentId}`; // Direct path
        console.log('Bulk form action set to:', bulkForm.action);
    }
        const modal = document.getElementById('bulkDeleteRealignmentModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeBulkDeleteRealignmentModal() {
        const modal = document.getElementById('bulkDeleteRealignmentModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
</script>