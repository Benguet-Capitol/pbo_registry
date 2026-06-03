<!-- Single Delete Modal -->
<div id="deleteSupplementalModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
            <h3 id="deleteSupplementalLabel" class="text-base font-semibold text-red-600 dark:text-red-400 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                {{ __('Delete Supplemental | Reversion') }}
            </h3>
            <button onclick="closeDeleteSupplementalModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal body -->
        <div class="px-6 py-4">
            <p id="deleteSupplementalModalContent" class="text-gray-900 dark:text-gray-200">Are you sure you want to delete this Supplemental | Reversion? This action cannot be undone.</p>
        </div>
        <!-- Modal footer -->
        <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
            <form id="deleteSupplementalForm" method="POST" action="">
                <input type="hidden" name="_token" id="csrf_token_single" value="">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="year1" id="year1_single" value="">
                <input type="hidden" name="office_allotment_class_id" id="office_single" value="">
                <input type="hidden" name="supplemental_type_filter" id="type_filter_single" value="">
                <input type="hidden" name="per_page" id="per_page_single" value="">
                <input type="hidden" name="search" id="search_single" value="">
                <button type="submit" class="text-red-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-trash text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Delete') }}
                </button>
            </form>
            <button type="button" onclick="closeDeleteSupplementalModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                {{ __('Cancel') }}
            </button>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal for Related Supplementals -->
<div id="bulkDeleteSupplementalModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-2xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
            <h3 id="bulkDeleteSupplementalLabel" class="text-base font-semibold text-red-600 dark:text-red-400 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                {{ __('Delete All Related Supplementals | Reversions') }}
            </h3>
            <button onclick="closeBulkDeleteSupplementalModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal body -->
        <div class="px-6 py-4">
            <p id="bulkDeleteSupplementalModalContent" class="text-gray-900 dark:text-gray-200 mb-4"></p>
            <div id="bulkDeleteSupplementalDetails" class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm max-h-96 overflow-y-auto"></div>
        </div>
        <!-- Modal footer -->
        <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
            <form id="bulkDeleteSupplementalForm" method="POST" action="">
                <input type="hidden" name="_token" id="csrf_token_bulk" value="">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="year1" id="year1_bulk" value="">
                <input type="hidden" name="office_allotment_class_id" id="office_bulk" value="">
                <input type="hidden" name="supplemental_type_filter" id="type_filter_bulk" value="">
                <input type="hidden" name="per_page" id="per_page_bulk" value="">
                <input type="hidden" name="search" id="search_bulk" value="">
                <input type="hidden" name="bulk_delete" value="1">
                <button type="submit" class="text-red-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-trash-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Delete All') }}
                </button>
            </form>
            <button type="button" onclick="closeBulkDeleteSupplementalModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    // Get CSRF token and query params from the page
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Get query params from URL
    const urlParams = new URLSearchParams(window.location.search);
    const queryParams = {
        year1: urlParams.get('year1') || '',
        office_allotment_class_id: urlParams.get('office_allotment_class_id') || '',
        supplemental_type_filter: urlParams.get('supplemental_type_filter') || '',
        per_page: urlParams.get('per_page') || '',
        search: urlParams.get('search') || ''
    };

    function openDeleteSupplementalModal(supplementalId, supplementalNo, supplementalType, supplementalAmount, appropriationsId) {
        closeAllDropdowns();

        // Find the appropriation object by ID
        let appropriation = appropriations.find(
            app => String(app.id) === String(appropriationsId)
        );
        let accountCode = appropriation ? appropriation.account_code : '';
        let description = appropriation ? appropriation.description : '';

        // Ensure Supplemental Amount is a valid number before formatting
        let formattedSupplementalAmount = "0.00";
        if (!isNaN(supplementalAmount) && supplementalAmount !== null && supplementalAmount !== "") {
            formattedSupplementalAmount = parseFloat(supplementalAmount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Validate deletion date before showing modal
        fetch(`/api/supplementals/check-deletion-date`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                supplemental_id: supplementalId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Set form action and populate hidden fields
            const deleteForm = document.getElementById('deleteSupplementalForm');
            if (deleteForm) {
                deleteForm.action = `/supplementals/${supplementalId}`;
                
                // Set CSRF token
                document.getElementById('csrf_token_single').value = csrfToken;
                
                // Set query parameters
                document.getElementById('year1_single').value = queryParams.year1;
                document.getElementById('office_single').value = queryParams.office_allotment_class_id;
                document.getElementById('type_filter_single').value = queryParams.supplemental_type_filter;
                document.getElementById('per_page_single').value = queryParams.per_page;
                document.getElementById('search_single').value = queryParams.search;
            }

            let contentHtml = `
                Are you sure you want to delete this <strong>${supplementalType}</strong> No: <strong>${supplementalNo}</strong> with Account Code: <strong>${accountCode}</strong> - <strong>${description}</strong> and Amount: <strong>${formattedSupplementalAmount}</strong>?
                <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Note:</strong> This will only delete this specific account entry. Other accounts under the same ${supplementalType} No. will remain.
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
                            Supplemental Date: ${data.supplemental_date}<br>
                            Earliest Obligation Date: ${data.earliest_obligation_date}
                        </p>
                    </div>
                `;
                document.getElementById('deleteSupplementalModalContent').innerHTML = contentHtml;
                const modal = document.getElementById('deleteSupplementalModal');
                const deleteButton = modal.querySelector('button[onclick*="submitDeleteSupplementalForm"]');
                if (deleteButton) {
                    deleteButton.disabled = true;
                    deleteButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            } else {
                document.getElementById('deleteSupplementalModalContent').innerHTML = contentHtml;
                const modal = document.getElementById('deleteSupplementalModal');
                const deleteButton = modal.querySelector('button[onclick*="submitDeleteSupplementalForm"]');
                if (deleteButton) {
                    deleteButton.disabled = false;
                    deleteButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            const modal = document.getElementById('deleteSupplementalModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        })
        .catch(error => {
            console.error('Error checking supplemental deletion date:', error);
            // Show error message
            const deleteForm = document.getElementById('deleteSupplementalForm');
            if (deleteForm) {
                deleteForm.action = `/supplementals/${supplementalId}`;
                document.getElementById('csrf_token_single').value = csrfToken;
                document.getElementById('year1_single').value = queryParams.year1;
                document.getElementById('office_single').value = queryParams.office_allotment_class_id;
                document.getElementById('type_filter_single').value = queryParams.supplemental_type_filter;
                document.getElementById('per_page_single').value = queryParams.per_page;
                document.getElementById('search_single').value = queryParams.search;
            }

            document.getElementById('deleteSupplementalModalContent').innerHTML = `
                Are you sure you want to delete this <strong>${supplementalType}</strong> No: <strong>${supplementalNo}</strong> with Account Code: <strong>${accountCode}</strong> - <strong>${description}</strong> and Amount: <strong>${formattedSupplementalAmount}</strong>?
                <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Note:</strong> This will only delete this specific account entry. Other accounts under the same ${supplementalType} No. will remain.
                    </p>
                </div>
                <p class="mt-3 text-red-600 dark:text-red-400 font-semibold">This action cannot be undone.</p>
            `;

            const modal = document.getElementById('deleteSupplementalModal');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
        });
    }

    function closeDeleteSupplementalModal() {
        const modal = document.getElementById('deleteSupplementalModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    function openBulkDeleteSupplementalModal(supplementalNo, currentSupplementalId) {
        closeAllDropdowns();
        const modal = document.getElementById('bulkDeleteSupplementalModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        const supplementals = @json($supplementalsBulkDelete ?? []);
        const relatedSupplementals = supplementals.filter(s => s.supplemental_no === supplementalNo);
        
        if (relatedSupplementals.length === 0) {
            alert('No related supplementals found.');
            return;
        }

        // Build details list
        let detailsHtml = '<div class="space-y-2">';
        let totalAmount = 0;
        let supplementalCount = 0;
        let reversionCount = 0;
        
        relatedSupplementals.forEach(s => {
            let appropriation = appropriations.find(app => String(app.id) === String(s.appropriations_id));
            let accountCode = appropriation ? appropriation.account_code : 'N/A';
            let description = appropriation ? appropriation.description : 'N/A';
            let amount = parseFloat(s.amount) || 0;
            totalAmount += Math.abs(amount);
            
            if (s.type === 'Supplemental') supplementalCount++;
            else if (s.type === 'Reversion') reversionCount++;
            
            let typeColor = s.type === 'Supplemental' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
            
            detailsHtml += `
                <div class="flex items-start justify-between p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                    <div class="flex-1">
                        <span class="font-semibold ${typeColor}">${s.type}</span>: 
                        <span class="text-gray-700 dark:text-gray-300">${accountCode}</span> - 
                        <span class="text-gray-600 dark:text-gray-400 text-xs">${description}</span>
                    </div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100 ml-2">
                        ₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}
                    </div>
                </div>
            `;
        });
        
        detailsHtml += `
            </div>
            <div class="mt-4 pt-4 border-t border-gray-300 dark:border-gray-600 grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Total Records</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">${relatedSupplementals.length}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Supplemental: ${supplementalCount} | Reversion: ${reversionCount}</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">Accounts</div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Total Amount</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                </div>
            </div>
        `;

        document.getElementById('bulkDeleteSupplementalModalContent').innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-orange-500 text-2xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">
                        Are you sure you want to delete <strong class="text-orange-600">all ${relatedSupplementals.length} supplemental/reversion(s)</strong> with No: <strong>${supplementalNo}</strong>?
                    </p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        This will permanently delete all supplemental and reversion entries for this transaction.
                    </p>
                    <p class="mt-2 text-sm font-semibold text-red-600 dark:text-red-400">
                        ⚠️ This action cannot be undone!
                    </p>
                </div>
            </div>
        `;
        document.getElementById('bulkDeleteSupplementalDetails').innerHTML = detailsHtml;
        
        // Set form action and populate hidden fields
        const bulkForm = document.getElementById('bulkDeleteSupplementalForm');
        if (bulkForm) {
            bulkForm.action = `/supplementals/${currentSupplementalId}`;
            
            // Set CSRF token
            document.getElementById('csrf_token_bulk').value = csrfToken;
            
            // Set query parameters
            document.getElementById('year1_bulk').value = queryParams.year1;
            document.getElementById('office_bulk').value = queryParams.office_allotment_class_id;
            document.getElementById('type_filter_bulk').value = queryParams.supplemental_type_filter;
            document.getElementById('per_page_bulk').value = queryParams.per_page;
            document.getElementById('search_bulk').value = queryParams.search;
        }
        
        document.getElementById('bulkDeleteSupplementalModal').classList.remove('hidden');
    }

    function closeBulkDeleteSupplementalModal() {
        const modal = document.getElementById('bulkDeleteSupplementalModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
</script>