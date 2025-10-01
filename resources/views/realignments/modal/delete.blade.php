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
        document.getElementById('deleteRealignmentModalContent').innerHTML = `Are you sure you want to delete this Realignment No: <strong>${realignmentNo}</strong> with Type: <strong>${realignmentType}</strong>, Account Code: <strong>${accountCode}</strong> - <strong>${description}</strong> and Amount: <strong>${formattedRealignmentAmount}</strong>? This action cannot be undone.`;
        document.getElementById('deleteRealignmentModal').classList.remove('hidden');
    }

    function closeDeleteRealignmentModal() {
        document.getElementById('deleteRealignmentModal').classList.add('hidden');
    }
</script>