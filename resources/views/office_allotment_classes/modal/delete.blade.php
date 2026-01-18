<div id="deleteModal" class="fixed inset-0 z-50 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full hidden flex items-center justify-center transition-all duration-300 ease-in-out animate-fadeIn" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative w-full max-w-md p-0 bg-white rounded-xl shadow-2xl border-2 border-red-200 dark:bg-gray-800 dark:border-red-900 transform transition-all duration-300 ease-out animate-scaleInUp">
            <div class="flex justify-between items-center p-6 border-b-2 border-red-200 dark:border-red-900 bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-700 rounded-t-xl">
                <h3 class="text-lg font-bold text-red-700 dark:text-red-400 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3 text-2xl"></i>
                    {{ __('Delete Office Allotment Class') }}
                </h3>
                <button onclick="closeDeleteModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                    <i class="fas fa-times h-6 w-6"></i>
                </button>
            </div>
            <div class="py-6 px-6">
                <p id="deleteModalContent" class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ __('Are you sure you want to delete this Allotment Class? This action cannot be undone.') }}</p>
            </div>
            <div class="flex justify-end gap-3 p-6 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-b-xl">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
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
</div>

<script>
    function openDeleteModal(userId, officeAbbreviation, allotmentClass) {
        closeAllDropdowns();
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        document.getElementById('deleteForm').action = '{{ route("office_allotment_classes.destroy", ":id") }}'.replace(':id', userId);
        document.getElementById('deleteModalContent').innerHTML = `Are you sure you want to delete this Registry for <strong class="text-red-700 dark:text-red-400 font-semibold">${officeAbbreviation}</strong> with allotment class <strong class="text-red-700 dark:text-red-400 font-semibold">${allotmentClass}</strong>? This action cannot be undone.`;
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
</script>