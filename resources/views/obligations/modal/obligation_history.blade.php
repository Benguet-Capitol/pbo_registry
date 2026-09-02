{{-- Obligation Status/History Modal: opened from the row context menu, shows the activity timeline. --}}
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

<div id="obligationHistoryModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="flex flex-col max-h-[90vh] w-full max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
        <!-- Modal header -->
        <div class="flex justify-between items-center px-4 py-4 bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-900 dark:to-slate-900 border-b-2 border-gray-200 dark:border-gray-700 rounded-t-lg">
            <div class="flex items-center gap-3">
                <i class="fas fa-history text-gray-600 dark:text-gray-300 text-xl"></i>
                <div>
                    <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-gray-100">
                        Obligation Status/History
                    </h3>
                    <span id="historyObligationInfo" class="text-xs text-gray-600 dark:text-gray-400"></span>
                </div>
            </div>
            <button type="button" onclick="closeObligationHistoryModal()" class="text-gray-600 dark:text-gray-300 hover:text-white hover:bg-gray-600 dark:hover:bg-gray-700 rounded-full p-2 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Modal body (scrollable) -->
        <div id="historyContent" class="overflow-y-auto flex-1 max-h-[calc(90vh-240px)] p-4 space-y-3">
            <div class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-500"></div>
            </div>
        </div>
        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-4 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg">
            <button type="button" onclick="closeObligationHistoryModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Close
            </button>
        </div>
    </div>
</div>

<script>
    // Open obligation history modal
    function openObligationHistoryModal(obligation) {
        if (!obligation || !obligation.id) {
            alert('Invalid obligation selected');
            return;
        }

        const modal = document.getElementById('obligationHistoryModal');
        const historyContent = document.getElementById('historyContent');
        const historyInfo = document.getElementById('historyObligationInfo');

        // Show modal with loading spinner and display flex
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        historyInfo.textContent = ` | ${obligation.obr_no || 'Loading...'}`;
        historyContent.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-500"></div></div>';

        // Fetch activity history
        fetch(`/obligations/${obligation.id}/activity-history`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    displayActivityHistory(data.data, historyContent);
                } else {
                    historyContent.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">No activity history found</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching activity history:', error);
                historyContent.innerHTML = '<div class="text-center py-8 text-red-500">Failed to load activity history. Please try again.</div>';
            });
    }

    // Close obligation history modal
    function closeObligationHistoryModal() {
        const modal = document.getElementById('obligationHistoryModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    // Display activity history in timeline format
    function displayActivityHistory(activities, container) {
        if (!activities || activities.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">No activity history found</div>';
            return;
        }

        let html = '<div class="space-y-4">';

        activities.forEach((activity, index) => {
            const date = new Date(activity.created_at);
            const formattedDate = date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            const formattedTime = date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            // Determine activity color and icon based on event type
            let colorClass = 'bg-blue-500';
            let icon = 'fas fa-circle';

            if (activity.event_type === 'created' || activity.description.toLowerCase().includes('created')) {
                colorClass = 'bg-green-500';
                icon = 'fas fa-plus-circle';
            } else if (activity.event_type === 'updated' || activity.description.toLowerCase().includes('updated') || activity.description.toLowerCase().includes('edited')) {
                colorClass = 'bg-blue-500';
                icon = 'fas fa-edit';
            } else if (activity.description.toLowerCase().includes('adjustment')) {
                colorClass = 'bg-yellow-500';
                icon = 'fas fa-file-edit';
            } else if (activity.description.toLowerCase().includes('purchase order')) {
                colorClass = 'bg-purple-500';
                icon = 'fas fa-file-invoice';
            } else if (activity.event_type === 'deleted' || activity.description.toLowerCase().includes('deleted')) {
                colorClass = 'bg-red-500';
                icon = 'fas fa-trash';
            }

            html += `
                <div class="flex gap-3 ${index !== activities.length - 1 ? 'border-l-2 border-gray-300 dark:border-gray-600 ml-2 pb-4' : ''}">
                    <div class="relative">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 ${colorClass} rounded-full flex items-center justify-center">
                            <i class="${icon} text-white text-[8px]"></i>
                        </div>
                    </div>
                    <div class="flex-1 ml-6">
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${activity.description}</p>
                                    ${activity.user ? `<p class="text-xs text-gray-600 dark:text-gray-400 mt-1">by ${activity.user.name}</p>` : ''}
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${formattedDate}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${formattedTime}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }
</script>
