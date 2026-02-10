<div id="deleteModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-red-600 dark:text-white flex items-center gap-2">
                <i class="fas fa-trash text-red-600 dark:text-red-400"></i>
                {{ __('Delete Obligation') }}
            </h3>
            <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal body -->
        <div class="px-6 py-4">
            <p id="deleteModalContent" class="text-gray-700 dark:text-gray-200 text-sm">{{ __('Are you sure you want to delete this Obligation Request? This action cannot be undone.') }}</p>
        </div>
        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <input type="hidden" name="year1" value="{{ request('year1') }}">
                <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
                <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="search_column" value="{{ request('search_column') }}">
                <button type="submit" class="text-red-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-trash text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Delete') }}
                </button>
            </form>
            <button onclick="closeDeleteModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
</style>

<script>
    function openDeleteModal(userId, obrNo, officeAbbreviation, Class, obrAmount) {
        closeAllDropdowns();
        // Ensure appropriation is a valid number before formatting
        let formattedObrAmount = "0.00"; // Default value

        if (!isNaN(obrAmount) && obrAmount !== null && obrAmount !== "") {
            formattedObrAmount = parseFloat(obrAmount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        document.getElementById('deleteForm').action = '{{ route("obligations.destroy", ":id") }}'.replace(':id', userId);
        document.getElementById('deleteModalContent').innerHTML = `Are you sure you want to delete this Obligation with OBR No. <strong>${obrNo}</strong> under <strong>${officeAbbreviation} - ${Class}</strong> with Total Amount: <strong>${formattedObrAmount}</strong>? <br> This action cannot be undone.`;
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }
</script>