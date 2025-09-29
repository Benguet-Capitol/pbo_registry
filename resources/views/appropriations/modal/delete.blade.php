<div id="deleteModal" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative w-full max-w-md p-4 bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center pb-3">
                <h3 class="text-lg font-semibold text-red-600 dark:text-white">{{ __('Delete Account') }}</h3>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                    <i class="fas fa-times h-6 w-6"></i>
                </button>
            </div>
            <div class="py-4">
                <p id="deleteModalContent" class="text-gray-900 dark:text-gray-200">{{ __('Are you sure you want to delete this Appropriation and Allotment? This action cannot be undone.') }}</p>
            </div>
            <div class="flex justify-end pt-2">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="mr-1 text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                        <i class="fas fa-trash mr-1 -ml-1"></i>
                        {{ __('Delete') }}
                    </button>
                </form>
                <button onclick="closeDeleteModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times mr-1 -ml-1"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(appropriationId, appropriation, accountCode, description) {
        closeAllDropdowns();
        // Ensure appropriation is a valid number before formatting
        let formattedAppropriation = "0.00"; // Default value

        if (!isNaN(appropriation) && appropriation !== null && appropriation !== "") {
            formattedAppropriation = parseFloat(appropriation).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Set form action dynamically
        document.getElementById('deleteForm').action = '{{ route("appropriations.destroy", ":id") }}'.replace(':id', appropriationId);

        // Set modal content with formatted values
        document.getElementById('deleteModalContent').innerHTML = `
        Are you sure you want to delete the Account with <strong class="text-gray-900 dark:text-gray-200">${formattedAppropriation}</strong> 
        under <strong class="text-gray-900 dark:text-gray-200">${accountCode} - ${description}</strong>?
        This action cannot be undone.
    `;

        // Show the modal
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>