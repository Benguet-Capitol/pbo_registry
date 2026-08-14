<!-- Create Allotment Release Order Modal -->
<form id="createAroForm" method="POST" action="{{ route('allotment_release_orders.store') }}">
    @csrf
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <div id="createAroModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-[95vw] xl:max-w-[1400px] mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-600 dark:text-blue-400"></i>
                    {{ __('Create Allotment Release Order') }}
                </h3>
                <button type="button" onclick="closeCreateAroModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                @include('allotment_release_orders.modal._fields', ['prefix' => 'create'])
            </div>
            <!-- Modal footer -->
            <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="AroForm.validateAndSubmit('create')" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Save') }}
                </button>
                <button type="button" onclick="closeCreateAroModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</form>

<style>
    @keyframes scaleInUp {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-scaleInUp { animation: scaleInUp 0.3s ease-out; }
</style>

<script>
    const aroOfficeAllotmentClasses = [
        @foreach($officeAllotmentClassesForForm as $oac)
        {
            id: "{{ $oac->id }}",
            name: "{{ $oac->offices->office_abbreviation ?? 'N/A' }} - {{ $oac->allotmentClass->class ?? 'N/A' }}",
            class: "{{ $oac->allotmentClass->class ?? '' }}",
            year: "{{ $oac->year }}",
        },
        @endforeach
    ];

    const aroRoutes = {
        getAppropriations: "{{ route('allotment_release_orders.getAppropriations') }}",
        previewNo: "{{ route('allotment_release_orders.previewNo') }}",
        signatories: "{{ route('allotment_release_orders.signatories') }}",
    };

    const aroCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    /**
     * Shared logic for both the Create and Edit ARO modals, keyed off a
     * `prefix` ('create' | 'edit') so each modal's fields (id="{prefix}_x")
     * stay independent while the behavior is written once.
     */
    const AroForm = {
        rowsData: {}, // prefix -> array of currently loaded appropriation/supplemental rows

        el(prefix, name) {
            return document.getElementById(`${prefix}_${name}`);
        },

        currentQuarter(dateStr) {
            if (!dateStr) return 1;
            const month = new Date(dateStr + 'T00:00:00').getMonth() + 1;
            if (month <= 3) return 1;
            if (month <= 6) return 2;
            if (month <= 9) return 3;
            return 4;
        },

        filterOfficeAllotmentClasses(prefix) {
            const input = this.el(prefix, 'office_allotment_class');
            const dropdown = this.el(prefix, 'office_allotment_class_dropdown');
            const filter = input.value.toLowerCase();
            dropdown.innerHTML = '';

            if (!filter) {
                dropdown.classList.add('hidden');
                return;
            }

            const matches = aroOfficeAllotmentClasses.filter(item => item.name.toLowerCase().includes(filter));
            if (matches.length === 0) {
                dropdown.classList.add('hidden');
                return;
            }

            matches.forEach(item => {
                const option = document.createElement('div');
                option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer text-xs';
                option.textContent = item.name;
                option.onclick = () => this.selectOfficeAllotmentClass(prefix, item);
                dropdown.appendChild(option);
            });
            dropdown.classList.remove('hidden');
        },

        selectOfficeAllotmentClass(prefix, item) {
            this.el(prefix, 'office_allotment_class').value = item.name;
            this.el(prefix, 'office_allotment_classes_id').value = item.id;
            this.el(prefix, 'office_allotment_class_dropdown').classList.add('hidden');
            this.loadAccountCodes(prefix);
            this.previewAroNo(prefix);
        },

        onFundSourceChange(prefix) {
            const fundSource = this.el(prefix, 'fund_source').value;
            const wrapper = this.el(prefix, 'supplemental_no_wrapper');
            wrapper.style.display = fundSource === 'Supplemental Budget' ? '' : 'none';
            this.loadAccountCodes(prefix);
        },

        onSupplementalNoChange(prefix) {
            this.loadAccountCodes(prefix);
        },

        loadAccountCodes(prefix) {
            const officeAllotmentClassesId = this.el(prefix, 'office_allotment_classes_id').value;
            const fundSource = this.el(prefix, 'fund_source').value;
            const supplementalNo = this.el(prefix, 'supplemental_no').value;
            const aroId = this.el(prefix, 'aro_id').value;
            const tbody = this.el(prefix, 'items_tbody');

            if (!officeAllotmentClassesId || !fundSource) {
                tbody.innerHTML = '';
                return;
            }

            const params = new URLSearchParams({
                office_allotment_classes_id: officeAllotmentClassesId,
                fund_source: fundSource,
            });
            if (supplementalNo) params.set('supplemental_no', supplementalNo);
            if (aroId) params.set('aro_id', aroId);

            fetch(`${aroRoutes.getAppropriations}?${params.toString()}`)
                .then(r => r.json())
                .then(data => {
                    if (!this.el(prefix, 'ppa_code').value && data.ppa_code) {
                        this.el(prefix, 'ppa_code').value = data.ppa_code;
                    }

                    this.rowsData[prefix] = data.items || [];
                    this.renderRows(prefix);
                });
        },

        /**
         * @param checkedAppropriationIds map of appropriation_id -> { authorized_appropriation, ppa_code }
         *        (edit mode pre-fills this from the ARO's saved items; create mode passes {}).
         */
        renderRows(prefix, checkedAppropriationIds = {}) {
            const tbody = this.el(prefix, 'items_tbody');
            const items = this.rowsData[prefix] || [];
            tbody.innerHTML = '';

            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-xs text-gray-500 py-4">No account codes found for this selection.</td></tr>';
                this.updateTotals(prefix);
                return;
            }

            // Rows default to whichever PPA Code came before them (starting from the
            // form's "Default PPA Code" field), so a contiguous run of rows shares one
            // PPA Code group unless the user overrides a row to start a new group.
            let lastPpaCode = this.el(prefix, 'ppa_code').value || '';
            let lastProgram = null;

            items.forEach(item => {
                const isChecked = Object.prototype.hasOwnProperty.call(checkedAppropriationIds, item.appropriation_id);
                const checkedData = isChecked ? checkedAppropriationIds[item.appropriation_id] : null;
                // Number(...) guards against "+" silently string-concatenating instead of
                // adding if either value ever arrives as a numeric string (e.g. from a
                // decimal-cast Eloquent attribute) rather than a JSON number.
                const checkedAmount = checkedData ? Number(checkedData.authorized_appropriation) || 0 : 0;
                const itemBalance = Number(item.balance) || 0;
                const itemAuthorized = Number(item.authorized_appropriation) || 0;

                // item.balance already excludes this ARO's own saved amount (the server
                // was passed aro_id), so it's already the correct ceiling — do NOT add
                // checkedAmount back on top, that would double count this row's own amount.
                const balance = itemBalance;
                const isExhausted = balance <= 0;
                // Default to the remaining balance (not the full authorized amount) so
                // the user works with what's actually still available by default.
                const authorizedValue = isChecked ? checkedAmount : Math.min(itemAuthorized, balance);
                const rowPpaCode = isChecked && checkedData.ppa_code ? checkedData.ppa_code : lastPpaCode;
                lastPpaCode = rowPpaCode;

                // Bold "Program" header row above the first account code of each
                // program grouping — mirrors the printed LBE Form No. 1 layout.
                if (item.programs && item.programs !== lastProgram) {
                    const programRow = document.createElement('tr');
                    programRow.innerHTML = `<td colspan="8" class="px-2 py-1 text-xs font-bold bg-gray-50 dark:bg-gray-900">${item.programs}</td>`;
                    tbody.appendChild(programRow);
                }
                lastProgram = item.programs || null;

                const row = document.createElement('tr');
                row.dataset.appropriationId = item.appropriation_id;
                row.dataset.quarter1 = item.quarter1;
                row.dataset.quarter2 = item.quarter2;
                row.dataset.quarter3 = item.quarter3;
                row.dataset.quarter4 = item.quarter4;
                row.dataset.balance = balance;
                if (isExhausted) row.classList.add('opacity-60');
                row.innerHTML = `
                    <td class="px-2 py-1 text-center">
                        <input type="checkbox" class="aro-row-check" ${isChecked ? 'checked' : ''} ${isExhausted ? 'disabled' : ''}
                            onchange="AroForm.toggleRow('${prefix}', this)">
                        <!-- account_code[] / ppa_description[] are intentionally NOT submitted — the
                             server re-fetches both fresh from the Appropriation model on save, and
                             posting them too was pushing large ARO lists (200+ rows) past PHP's
                             max_input_vars, which silently drops the request with no visible error. -->
                        <input type="hidden" name="appropriation_id[]" value="${item.appropriation_id}" ${isChecked ? '' : 'disabled'}>
                    </td>
                    <td class="px-1 py-1">
                        <input type="text" class="aro-ppa-code-input block w-full text-xs border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded"
                            name="row_ppa_code[]" value="${(rowPpaCode || '').replace(/"/g, '&quot;')}" ${isChecked ? '' : 'disabled'}>
                    </td>
                    <td class="px-2 py-1 text-xs">${item.account_code}</td>
                    <td class="px-2 py-1 text-xs">${item.description || ''}</td>
                    <td class="px-2 py-1 text-xs text-right ${isExhausted ? 'text-red-600 dark:text-red-400 font-semibold' : ''}" title="${item.already_committed > 0 ? 'Already committed to other ARO(s): ' + Number(item.already_committed).toLocaleString('en-US', { minimumFractionDigits: 2 }) : ''}">
                        ${isExhausted ? 'Fully released' : Number(balance).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                    </td>
                    <td class="px-1 py-1">
                        <input type="text" class="aro-authorized-input block w-full text-xs text-right border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded"
                            name="authorized_appropriation[]" value="${Number(authorizedValue).toFixed(2)}"
                            oninput="AroForm.recalcRow('${prefix}', this.closest('tr'))" ${isChecked ? '' : 'disabled'}>
                    </td>
                    <td class="px-2 py-1 text-xs text-right aro-for-later">0.00</td>
                    <td class="px-2 py-1 text-xs text-right font-semibold aro-this-release">0.00</td>
                `;
                tbody.appendChild(row);
                this.recalcRow(prefix, row);
            });

            this.syncSelectAll(prefix);
        },

        toggleRow(prefix, checkbox) {
            const row = checkbox.closest('tr');
            const hiddenInputs = row.querySelectorAll('input[type="hidden"]');
            const textInput = row.querySelector('.aro-authorized-input');
            const ppaCodeInput = row.querySelector('.aro-ppa-code-input');
            hiddenInputs.forEach(input => input.disabled = !checkbox.checked);
            textInput.disabled = !checkbox.checked;
            ppaCodeInput.disabled = !checkbox.checked;
            this.recalcRow(prefix, row);
            this.syncSelectAll(prefix);
        },

        toggleSelectAll(prefix, selectAllCheckbox) {
            // Captured up front: toggleRow() below calls syncSelectAll(), which
            // mutates selectAllCheckbox.checked as soon as the first row is
            // toggled — reading it fresh on every loop iteration would flip the
            // target state mid-loop and stop the rest of the rows from toggling.
            const shouldCheck = selectAllCheckbox.checked;
            const tbody = this.el(prefix, 'items_tbody');
            tbody.querySelectorAll('.aro-row-check:not(:disabled)').forEach(checkbox => {
                if (checkbox.checked !== shouldCheck) {
                    checkbox.checked = shouldCheck;
                    this.toggleRow(prefix, checkbox);
                }
            });
            this.syncSelectAll(prefix);
        },

        /**
         * Keeps the header "Select All" checkbox's checked/indeterminate state
         * in sync with the individual (selectable, non-exhausted) row checkboxes.
         */
        syncSelectAll(prefix) {
            const selectAllCheckbox = this.el(prefix, 'select_all_accounts');
            if (!selectAllCheckbox) return;

            const tbody = this.el(prefix, 'items_tbody');
            const selectable = Array.from(tbody.querySelectorAll('.aro-row-check:not(:disabled)'));
            const checkedCount = selectable.filter(cb => cb.checked).length;

            selectAllCheckbox.disabled = selectable.length === 0;
            selectAllCheckbox.checked = selectable.length > 0 && checkedCount === selectable.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < selectable.length;

            // Clear the inline "check at least one" error as soon as any row is checked.
            if (checkedCount > 0) {
                const accountCodesError = this.el(prefix, 'account_codes_error');
                if (accountCodesError) accountCodesError.classList.add('hidden');
            }
        },

        recalcRow(prefix, row) {
            const dateOfIssue = this.el(prefix, 'date_of_issue').value;
            const quarter = this.currentQuarter(dateOfIssue);
            let forLater = 0;
            if (quarter < 2) forLater += parseFloat(row.dataset.quarter2 || 0);
            if (quarter < 3) forLater += parseFloat(row.dataset.quarter3 || 0);
            if (quarter < 4) forLater += parseFloat(row.dataset.quarter4 || 0);

            const authorizedInput = row.querySelector('.aro-authorized-input');
            const balance = parseFloat(row.dataset.balance || 0);
            let authorized = parseFloat(authorizedInput.value.replace(/,/g, '')) || 0;

            // Soft-cap to the remaining balance so this ARO can't exceed what's
            // actually left after other ARO(s) already committed against this
            // account code — the server enforces this too on save.
            if (authorized > balance) {
                authorized = balance;
                authorizedInput.value = authorized.toFixed(2);
            }

            const thisRelease = authorized - forLater;

            row.querySelector('.aro-for-later').textContent = forLater.toLocaleString('en-US', { minimumFractionDigits: 2 });
            row.querySelector('.aro-this-release').textContent = thisRelease.toLocaleString('en-US', { minimumFractionDigits: 2 });
            this.updateTotals(prefix);
        },

        /**
         * Sums Authorized Appropriation / For Later Release / This Release
         * across only the currently checked (selected) Account Code rows, and
         * writes them into the table's footer Total row.
         */
        updateTotals(prefix) {
            const totalAuthorizedEl = this.el(prefix, 'total_authorized');
            const totalForLaterEl = this.el(prefix, 'total_for_later');
            const totalThisReleaseEl = this.el(prefix, 'total_this_release');
            if (!totalAuthorizedEl || !totalForLaterEl || !totalThisReleaseEl) return;

            const tbody = this.el(prefix, 'items_tbody');
            let totalAuthorized = 0;
            let totalForLater = 0;
            let totalThisRelease = 0;

            tbody.querySelectorAll('.aro-row-check:checked').forEach(checkbox => {
                const row = checkbox.closest('tr');
                totalAuthorized += parseFloat(row.querySelector('.aro-authorized-input').value.replace(/,/g, '')) || 0;
                totalForLater += parseFloat(row.querySelector('.aro-for-later').textContent.replace(/,/g, '')) || 0;
                totalThisRelease += parseFloat(row.querySelector('.aro-this-release').textContent.replace(/,/g, '')) || 0;
            });

            const fmt = n => n.toLocaleString('en-US', { minimumFractionDigits: 2 });
            totalAuthorizedEl.textContent = fmt(totalAuthorized);
            totalForLaterEl.textContent = fmt(totalForLater);
            totalThisReleaseEl.textContent = fmt(totalThisRelease);
        },

        onDateOfIssueChange(prefix) {
            const tbody = this.el(prefix, 'items_tbody');
            tbody.querySelectorAll('tr').forEach(row => {
                if (row.dataset.appropriationId) this.recalcRow(prefix, row);
            });
            this.previewAroNo(prefix);
        },

        previewAroNo(prefix) {
            const officeAllotmentClassesId = this.el(prefix, 'office_allotment_classes_id').value;
            const dateOfIssue = this.el(prefix, 'date_of_issue').value;
            const preview = this.el(prefix, 'aro_no_preview');
            if (!officeAllotmentClassesId || !dateOfIssue) return;

            const params = new URLSearchParams({ office_allotment_classes_id: officeAllotmentClassesId, date_of_issue: dateOfIssue });
            fetch(`${aroRoutes.previewNo}?${params.toString()}`)
                .then(r => r.json())
                .then(data => { preview.value = data.aro_no; });
        },

        loadSignatories(prefix, defaults = {}) {
            fetch(aroRoutes.signatories)
                .then(r => r.json())
                .then(data => {
                    const pboSelect = this.el(prefix, 'pbo_signatory');
                    // Default to the employee (office 12) whose designation is "Provincial
                    // Budget Officer" when nothing is already selected (edit mode passes its
                    // own saved provincial_budget_officer_id in via `defaults`, which wins).
                    const defaultPboId = defaults.provincial_budget_officer_id
                        || (data.default_pbo ? data.default_pbo.id : null);
                    pboSelect.innerHTML = '<option value="">Select Employee</option>';
                    (data.pbo_employees || []).forEach(emp => {
                        const opt = document.createElement('option');
                        opt.value = emp.id;
                        opt.textContent = emp.name;
                        if (defaultPboId && String(defaultPboId) === String(emp.id)) opt.selected = true;
                        pboSelect.appendChild(opt);
                    });

                    // Local Chief Executive signatory is auto-resolved from the employee whose
                    // designation is "Provincial Governor" — never manually picked. Only fill it
                    // in when it isn't already set (edit mode pre-fills these fields itself before
                    // this call, so this branch only fires for the create modal).
                    const lceIdField = this.el(prefix, 'lce_signatory_id');
                    const lceAutoField = this.el(prefix, 'lce_signatory_auto');
                    if (!lceIdField.value && !lceAutoField.value && data.default_governor) {
                        lceIdField.value = data.default_governor.id;
                        lceAutoField.value = data.default_governor.name;
                    }
                    this.onGovernorTitleChange(prefix);
                });
        },

        onGovernorTitleChange(prefix) {
            const title = this.el(prefix, 'lce_designation').value;
            const isProvincialGovernor = title === 'Provincial Governor';
            this.el(prefix, 'lce_signatory_auto').style.display = isProvincialGovernor ? '' : 'none';
            this.el(prefix, 'lce_signatory_manual').style.display = isProvincialGovernor ? 'none' : '';
            if (isProvincialGovernor) {
                this.el(prefix, 'lce_signatory_manual').value = '';
            } else {
                this.el(prefix, 'lce_signatory_id').value = '';
            }
        },

        /**
         * Shows/clears the inline <p id="{prefix}_{field}_error"> under a given
         * field — replaces alert() popups so validation reads like normal form
         * feedback instead of interrupting the user with a dialog per error.
         */
        setFieldError(prefix, field, message) {
            const errorEl = this.el(prefix, `${field}_error`);
            if (!errorEl) return;

            if (message) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            } else {
                errorEl.classList.add('hidden');
            }
        },

        clearAllErrors(prefix) {
            this.setFieldError(prefix, 'general', null);
            ['office_allotment_class', 'date_of_issue', 'supplemental_no', 'pbo_signatory', 'lce_signatory', 'account_codes']
                .forEach(field => this.setFieldError(prefix, field, null));
        },

        validateAndSubmit(prefix) {
            const form = document.getElementById(`${prefix}AroForm`);
            let isValid = true;
            let firstInvalidField = null;

            this.clearAllErrors(prefix);

            const markInvalid = (field, message) => {
                this.setFieldError(prefix, field, message);
                isValid = false;
                if (!firstInvalidField) firstInvalidField = this.el(prefix, field) || this.el(prefix, `${field}_error`);
            };

            if (!this.el(prefix, 'office_allotment_classes_id').value) {
                markInvalid('office_allotment_class', 'Please select an Office and Allotment Class.');
            }
            if (!this.el(prefix, 'date_of_issue').value) {
                markInvalid('date_of_issue', 'Date of Issue is required.');
            }
            const fundSource = this.el(prefix, 'fund_source').value;
            if (fundSource === 'Supplemental Budget' && !this.el(prefix, 'supplemental_no').value.trim()) {
                markInvalid('supplemental_no', 'Please enter the SB No.');
            }
            if (!this.el(prefix, 'pbo_signatory').value) {
                markInvalid('pbo_signatory', 'Please select a Provincial Budget Officer Signatory.');
            }
            const governorTitle = this.el(prefix, 'lce_designation').value;
            if (governorTitle === 'Provincial Governor' && !this.el(prefix, 'lce_signatory_id').value) {
                markInvalid('lce_signatory', 'No employee with the "Provincial Governor" designation was found. Switch the Local Chief Executive Designation to "Acting Provincial Governor" and type the name instead.');
            }
            if (governorTitle === 'Acting Provincial Governor' && !this.el(prefix, 'lce_signatory_manual').value.trim()) {
                markInvalid('lce_signatory', 'Please type the Acting Provincial Governor\'s name.');
            }
            const checkedRows = form.querySelectorAll('.aro-row-check:checked');
            if (checkedRows.length === 0) {
                markInvalid('account_codes', 'Please check at least one Account Code.');
            }

            if (isValid) {
                form.submit();
            } else if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
    };

    document.addEventListener('click', function(event) {
        if (!event.target.closest('[id$="_office_allotment_class"]') && !event.target.closest('[id$="_office_allotment_class_dropdown"]')) {
            document.querySelectorAll('[id$="_office_allotment_class_dropdown"]').forEach(d => d.classList.add('hidden'));
        }
    });

    function openCreateAroModal() {
        closeAllDropdowns();
        const modal = document.getElementById('createAroModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        // Reset the LCE signatory fields so a previous Edit session's data
        // (or a prior Create attempt) doesn't linger in the reused modal.
        document.getElementById('create_lce_signatory_id').value = '';
        document.getElementById('create_lce_signatory_auto').value = '';
        document.getElementById('create_lce_signatory_manual').value = '';
        document.getElementById('create_lce_designation').value = 'Provincial Governor';
        AroForm.clearAllErrors('create');

        AroForm.loadSignatories('create');
    }

    function closeCreateAroModal() {
        const modal = document.getElementById('createAroModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
</script>
