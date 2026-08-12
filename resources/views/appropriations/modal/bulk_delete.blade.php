<div id="bulkDeleteModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
    <div class="w-full max-w-md rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <div class="flex justify-between items-center px-6 py-4 border-b-2 border-red-200 dark:border-red-900 bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-700 rounded-t-xl">
            <h3 class="text-base font-bold text-red-700 dark:text-red-400 flex items-center">
                <i class="fas fa-exclamation-triangle mr-3 text-2xl"></i>
                {{ __('Delete Accounts') }}
            </h3>
            <button type="button" onclick="closeBulkDeleteModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="py-4 px-6">
            <p id="bulkDeleteModalContent" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed"></p>
        </div>
        <div class="flex justify-end gap-3 p-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-b-xl">
            <button type="button" id="bulkDeleteConfirmBtn" onclick="confirmBulkDelete()" class="text-red-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
                <i class="fas fa-trash text-xl mr-1 -ml-1 w-5 h-5"></i>
                <span id="bulkDeleteConfirmLabel">{{ __('Delete') }}</span>
            </button>
            <button type="button" onclick="closeBulkDeleteModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-times mr-2"></i>
                {{ __('Cancel') }}
            </button>
        </div>
    </div>
</div>

<script>
    function openBulkDeleteModal(checkedCheckboxes) {
        const count = checkedCheckboxes.length;
        const preview = Array.from(checkedCheckboxes).slice(0, 5)
            .map(cb => `<li class="text-sm">${cb.dataset.code} - ${cb.dataset.desc}</li>`)
            .join('');
        const more = count > 5 ? `<li class="text-sm text-gray-500 dark:text-gray-400 italic">...and ${count - 5} more</li>` : '';

        document.getElementById('bulkDeleteModalContent').innerHTML = `
            Are you sure you want to delete <strong class="font-semibold text-red-600 dark:text-red-400">${count} account(s)</strong>?
            <ul class="list-disc list-inside mt-2 mb-2 max-h-32 overflow-y-auto space-y-0.5">${preview}${more}</ul>
            Accounts with existing obligations, realignments, or supplementals will be skipped automatically.
            <br><br>This action cannot be undone.
        `;

        const modal = document.getElementById('bulkDeleteModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            const box = modal.querySelector('div.hidden');
            if (box) box.classList.remove('hidden');
        }, 10);
    }

    function closeBulkDeleteModal() {
        const modal = document.getElementById('bulkDeleteModal');
        const box = modal.querySelector('div.hidden, div[style*="animation"]') || modal.querySelector('> div');
        if (box) {
            box.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        } else {
            modal.style.display = 'none';
        }
    }
</script>