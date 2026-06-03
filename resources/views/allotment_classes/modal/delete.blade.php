<div id="deleteModal" style="display: none;" tabindex="-1" aria-labelledby="deleteAllotmentClassLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
            <h3 id="deleteAllotmentClassLabel" class="text-base font-semibold text-red-600 dark:text-red-400 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                {{ __('Delete Allotment Class') }}
            </h3>
            <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal body -->
        <div class="px-6 py-4">
            <p id="deleteModalContent" class="text-sm text-gray-900 dark:text-gray-200">{{ __('Are you sure you want to delete this Allotment Class? This action cannot be undone.') }}</p>
        </div>
        <!-- Modal footer -->
        <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-trash text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Delete') }}
                </button>
            </form>
            <button type="button" onclick="closeDeleteModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    function openDeleteModal(userId, description) {
        closeAllDropdowns();
        const modal = document.getElementById('deleteModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        document.getElementById('deleteForm').action = '{{ route("allotment_classes.destroy", ":id") }}'.replace(':id', userId);
        document.getElementById('deleteModalContent').innerHTML = `{{ __('Are you sure you want to delete the Allotment Class:') }} <strong>${description}</strong>? {{ __('This action cannot be undone.') }}`;
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        if (modalContent) {
            modalContent.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    }
</script>