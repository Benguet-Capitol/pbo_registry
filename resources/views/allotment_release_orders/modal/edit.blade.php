<!-- Edit Allotment Release Order Modal -->
<form id="editAroForm" method="POST" action="">
    @csrf
    @method('PUT')
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <div id="editAroModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-[95vw] xl:max-w-[1400px] mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-edit text-blue-600 dark:text-blue-400"></i>
                    {{ __('Edit Allotment Release Order') }} <span id="editAroNoLabel" class="text-blue-700 dark:text-blue-300"></span>
                </h3>
                <button type="button" onclick="closeEditAroModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                @include('allotment_release_orders.modal._fields', ['prefix' => 'edit'])
            </div>
            <!-- Modal footer -->
            <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="AroForm.validateAndSubmit('edit')" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Update') }}
                </button>
                <button type="button" onclick="closeEditAroModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    function openEditAroModal(aro) {
        closeAllDropdowns();

        document.getElementById('editAroForm').action = `/allotment-release-orders/${aro.id}`;
        document.getElementById('editAroNoLabel').textContent = aro.aro_no;
        document.getElementById('edit_aro_id').value = aro.id;
        AroForm.clearAllErrors('edit');

        const oac = aroOfficeAllotmentClasses.find(item => String(item.id) === String(aro.office_allotment_classes_id));
        document.getElementById('edit_office_allotment_class').value = oac ? oac.name : '';
        document.getElementById('edit_office_allotment_classes_id').value = aro.office_allotment_classes_id;
        document.getElementById('edit_date_of_issue').value = aro.date_of_issue.substring(0, 10);
        document.getElementById('edit_fund_source').value = aro.fund_source;
        document.getElementById('edit_supplemental_no_wrapper').style.display = aro.fund_source === 'Supplemental Budget' ? '' : 'none';
        document.getElementById('edit_supplemental_no').value = aro.supplemental_no || '';
        document.getElementById('edit_ppa_code').value = aro.ppa_code || '';
        document.getElementById('edit_aro_no_preview').value = aro.aro_no;
        document.getElementById('edit_pbo_designation').value = aro.provincial_budget_officer_title;
        document.getElementById('edit_lce_designation').value = aro.provincial_governor_title;

        // Pre-fill the LCE signatory fields directly from the saved ARO — this must
        // happen before loadSignatories() runs, since that call only auto-fills the
        // default "Provincial Governor" employee when these fields are still empty.
        document.getElementById('edit_lce_signatory_id').value = aro.provincial_governor_id || '';
        document.getElementById('edit_lce_signatory_auto').value = aro.provincial_governor
            ? aro.provincial_governor.name
            : '';
        document.getElementById('edit_lce_signatory_manual').value = aro.provincial_governor_name || '';
        AroForm.onGovernorTitleChange('edit');

        AroForm.loadSignatories('edit', aro);

        // Pre-check the account codes already saved on this ARO once the
        // office/class + fund source combination finishes loading.
        const checkedAppropriationIds = {};
        (aro.items || []).forEach(item => {
            checkedAppropriationIds[item.appropriation_id] = {
                // Eloquent's decimal:2 cast serializes these as strings (e.g. "31309572.00"),
                // not JSON numbers — parseFloat here avoids "+" silently doing string
                // concatenation instead of addition wherever this value is used later.
                authorized_appropriation: parseFloat(item.authorized_appropriation) || 0,
                ppa_code: item.ppa_code,
            };
        });

        const officeAllotmentClassesId = aro.office_allotment_classes_id;
        const fundSource = aro.fund_source;
        const supplementalNo = aro.supplemental_no || '';

        const params = new URLSearchParams({ office_allotment_classes_id: officeAllotmentClassesId, fund_source: fundSource, aro_id: aro.id });
        if (supplementalNo) params.set('supplemental_no', supplementalNo);

        fetch(`${aroRoutes.getAppropriations}?${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                AroForm.rowsData['edit'] = data.items || [];
                AroForm.renderRows('edit', checkedAppropriationIds);
            });

        const modal = document.getElementById('editAroModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeEditAroModal() {
        const modal = document.getElementById('editAroModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
</script>
