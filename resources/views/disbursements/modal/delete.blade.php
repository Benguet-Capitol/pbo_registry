<div id="deleteDisbursementModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-rose-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-red-600 dark:text-white flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                {{ __('Delete Disbursement') }}
            </h3>
            <button type="button" onclick="closeDeleteDisbursementModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal body -->
        <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
            <p id="deleteDisbursementModalContent">{{ __('Are you sure you want to delete this Disbursement? This action cannot be undone.') }}</p>
        </div>
        <!-- Modal footer -->
        <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
            <form id="deleteDisbursementForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-trash text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Delete') }}
                </button>
            </form>
            <button type="button" onclick="closeDeleteDisbursementModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    function openDeleteDisbursementModal(disbursementId, disbursementNo, disbursementDate, accountCode, disbursementAmount) {
        closeAllDropdowns();

    document.getElementById('deleteDisbursementForm').action = '{{ route("disbursements.destroy", ":id") }}'.replace(':id', disbursementId);
    const formattedAmount = Number(disbursementAmount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('deleteDisbursementModalContent').innerHTML = `Are you sure you want to delete this DV / Check No: <strong>${disbursementNo}</strong> dated <strong>${disbursementDate}</strong> under Account Code: <strong>${accountCode}</strong> and with DV / Check Amount: <strong>${formattedAmount}</strong>? </br></br> This action cannot be undone.`;
    const modal = document.getElementById('deleteDisbursementModal');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    }

    function closeDeleteDisbursementModal() {
        const modal = document.getElementById('deleteDisbursementModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
</script>