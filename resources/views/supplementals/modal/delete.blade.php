<!-- Single Delete Modal -->
<div id="deleteSupplementalModal" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative w-full max-w-md p-4 bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-semibold text-red-600 dark:text-white">Delete Supplemental | Reversion</h3>
                <button onclick="closeDeleteSupplementalModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                    <i class="fas fa-times h-6 w-6"></i>
                </button>
            </div>
            <div class="py-4">
                <p id="deleteSupplementalModalContent" class="text-gray-700 dark:text-gray-200">Are you sure you want to delete this Supplemental | Reversion? This action cannot be undone.</p>
            </div>
            <div class="flex justify-end pt-2">
                <form id="deleteSupplementalForm" method="POST" action="">
                    <input type="hidden" name="_token" id="csrf_token_single" value="">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="year1" id="year1_single" value="">
                    <input type="hidden" name="office_allotment_class_id" id="office_single" value="">
                    <input type="hidden" name="supplemental_type_filter" id="type_filter_single" value="">
                    <input type="hidden" name="per_page" id="per_page_single" value="">
                    <input type="hidden" name="search" id="search_single" value="">
                    <button type="submit" class="mr-1 text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                        <i class="fas fa-trash mr-1 -ml-1"></i>
                        Delete
                    </button>
                </form>
                <button onclick="closeDeleteSupplementalModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times mr-1 -ml-1"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal for Related Supplementals -->
<div id="bulkDeleteSupplementalModal" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative w-full max-w-2xl p-4 bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-semibold text-orange-600 dark:text-white">Delete All Related Supplementals | Reversions</h3>
                <button onclick="closeBulkDeleteSupplementalModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                    <i class="fas fa-times h-6 w-6"></i>
                </button>
            </div>
            <div class="py-4">
                <p id="bulkDeleteSupplementalModalContent" class="text-gray-700 dark:text-gray-200 mb-4"></p>
                <div id="bulkDeleteSupplementalDetails" class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm max-h-96 overflow-y-auto"></div>
            </div>
            <div class="flex justify-end pt-2">
                <form id="bulkDeleteSupplementalForm" method="POST" action="">
                    <input type="hidden" name="_token" id="csrf_token_bulk" value="">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="year1" id="year1_bulk" value="">
                    <input type="hidden" name="office_allotment_class_id" id="office_bulk" value="">
                    <input type="hidden" name="supplemental_type_filter" id="type_filter_bulk" value="">
                    <input type="hidden" name="per_page" id="per_page_bulk" value="">
                    <input type="hidden" name="search" id="search_bulk" value="">
                    <input type="hidden" name="bulk_delete" value="1">
                    <button type="submit" class="mr-1 text-orange-600 inline-flex items-center hover:text-white border border-orange-600 hover:bg-orange-600 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-orange-500 dark:text-orange-500 dark:hover:text-white dark:hover:bg-orange-600 dark:focus:ring-orange-900">
                        <i class="fas fa-trash-alt mr-1 -ml-1"></i>
                        Delete All
                    </button>
                </form>
                <button onclick="closeBulkDeleteSupplementalModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times mr-1 -ml-1"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

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
    
    console.log('Query params loaded:', queryParams);

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
            
            console.log('Single delete form configured:', {
                action: deleteForm.action,
                method: deleteForm.method
            });
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

        document.getElementById('deleteSupplementalModal').classList.remove('hidden');
    }

    function closeDeleteSupplementalModal() {
        document.getElementById('deleteSupplementalModal').classList.add('hidden');
    }

    function openBulkDeleteSupplementalModal(supplementalNo, currentSupplementalId) {
        closeAllDropdowns();

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
            
            console.log('Bulk delete form configured:', {
                action: bulkForm.action,
                method: bulkForm.method,
                bulk_delete: '1'
            });
        }
        
        document.getElementById('bulkDeleteSupplementalModal').classList.remove('hidden');
    }

    function closeBulkDeleteSupplementalModal() {
        document.getElementById('bulkDeleteSupplementalModal').classList.add('hidden');
    }
</script>