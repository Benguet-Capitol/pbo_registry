{{-- Shared Restrict/Activate Confirmation Modal --}}
<div id="confirmToggleModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-sm p-6">
        <div class="flex items-center gap-3 mb-4">
            <div id="confirmToggleIcon" class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
            <h3 id="confirmToggleTitle" class="text-base font-semibold text-gray-800 dark:text-gray-100"></h3>
        </div>

        <p id="confirmToggleMessage" class="text-sm text-gray-600 dark:text-gray-300 mb-6"></p>

        <div class="flex justify-end gap-2">
            <button type="button" id="confirmToggleSubmitBtn" onclick="submitConfirmedToggle()"
                class="px-4 py-2 text-xs font-medium text-white rounded-lg transition-colors">
                Confirm
            </button>
            <button type="button" onclick="closeConfirmToggleModal()"
                class="px-4 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
    let pendingToggleFormId = null;

    function confirmToggle({ formId, action, subjectLabel, subjectType }) {
        // action: true = about to restrict, false = about to activate
        pendingToggleFormId = formId;

        const title = document.getElementById('confirmToggleTitle');
        const message = document.getElementById('confirmToggleMessage');
        const icon = document.getElementById('confirmToggleIcon');
        const submitBtn = document.getElementById('confirmToggleSubmitBtn');

        if (action) {
            title.textContent = `Restrict this ${subjectType}?`;
            message.innerHTML = subjectType === 'role'
                ? `This will immediately block <strong>all users</strong> assigned the <strong>${subjectLabel}</strong> role from logging in. You can reverse this at any time.`
                : `This will immediately block <strong>${subjectLabel}</strong> from logging in. You can reverse this at any time.`;
            icon.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300';
            submitBtn.className = 'px-4 py-2 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors';
            submitBtn.textContent = 'Yes, Restrict';
        } else {
            title.textContent = `Activate this ${subjectType}?`;
            message.innerHTML = subjectType === 'role'
                ? `This will allow all users assigned the <strong>${subjectLabel}</strong> role to log in again.`
                : `This will allow <strong>${subjectLabel}</strong> to log in again.`;
            icon.className = 'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300';
            submitBtn.className = 'px-4 py-2 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors';
            submitBtn.textContent = 'Yes, Activate';
        }

        document.getElementById('confirmToggleModal').classList.remove('hidden');
        document.getElementById('confirmToggleModal').classList.add('flex');
    }

    function closeConfirmToggleModal() {
        pendingToggleFormId = null;
        document.getElementById('confirmToggleModal').classList.add('hidden');
        document.getElementById('confirmToggleModal').classList.remove('flex');
    }

    function submitConfirmedToggle() {
        if (pendingToggleFormId) {
            document.getElementById(pendingToggleFormId).submit();
        }
    }
</script>