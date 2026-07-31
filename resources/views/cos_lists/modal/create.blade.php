<!-- Create COS Record Modal -->
<form id="createCosForm" method="POST" action="{{ route('cos_lists.store') }}">
    @csrf
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
    <input type="hidden" name="appropriation_filter" value="{{ request('appropriation_filter') }}">
    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="search_column" value="{{ request('search_column') }}">
    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
    <input type="hidden" name="page" value="{{ request('page') }}">
    <!-- Critical hidden fields for form submission -->
    <input type="hidden" name="office_allotment_class_id" id="office_allotment_class_id" />
    <input type="hidden" name="appropriation_id" id="appropriation_id" />
    <input type="hidden" name="employee_id" id="employee_id" />

    <div id="createModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out; max-height: 90vh;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-plus-circle text-sm text-blue-600 dark:text-blue-400"></i>
                    {{ __('Add Contract of Service') }}
                </h3>
                <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto flex-1" style="max-height: calc(90vh - 280px);">
                <div class="grid gap-3">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">

                        <!-- Office and Allotment Class -->
                        <div class="sm:col-span-3 relative">
                            <x-form.label for="office_allotment_class" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-laptop-house"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="office_allotment_class"
                                        id="office_allotment_class"
                                        placeholder="{{ __('Office and Allotment Class') }}"
                                        class="block w-full bg-white text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                        oninput="filterOfficeAllotmentClasses()"
                                        autocomplete="off" />
                                </x-form.input-with-icon-wrapper>
                                <div id="OfficeAllotmentClassDropdown" class="text-xs absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                    <!-- Suggestions appear here -->
                                </div>
                                <span id="OfficeAllotmentClassError" class="text-red-500 text-xs mt-1 block"></span>
                            </div>
                        </div>

                        <!-- Accounts (Appropriations) -->
                        <div class="sm:col-span-3 relative">
                            <x-form.label for="appropriation_name" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="appropriation_name"
                                        id="appropriation_name"
                                        placeholder="{{ __('Select Account') }}"
                                        class="block w-full bg-white text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                        oninput="filterAppropriations()"
                                        autocomplete="off" />
                                </x-form.input-with-icon-wrapper>
                                <div id="AppropriationDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-y-auto z-50">
                                    <!-- Suggestions appear here -->
                                </div>
                                <span id="AppropriationError" class="text-red-500 text-xs mt-1 block"></span>
                            </div>
                        </div>

                        <!-- Employee -->
                        <div class="sm:col-span-6 relative">
                            <x-form.label for="employee_name" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Employee')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-user"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="employee_name"
                                        id="employee_name"
                                        placeholder="{{ __('Search Employee') }}"
                                        class="block w-full bg-white text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                        oninput="searchEmployee(this.value)"
                                        onfocus="searchEmployee(this.value)"
                                        autocomplete="off" />
                                </x-form.input-with-icon-wrapper>
                                <div id="EmployeeDropdown" class="text-xs absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                    <!-- Suggestions appear here -->
                                </div>
                                <span id="EmployeeError" class="text-red-500 text-xs mt-1 block"></span>
                            </div>
                        </div>

                        <!-- Position Title (Read-only) -->
                        <div class="sm:col-span-3">
                            <x-form.label for="position_title" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Position Title')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-briefcase"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="position_title"
                                        id="position_title"
                                        placeholder="{{ __('Position Title') }}"
                                        class="block w-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed"
                                        readonly />
                                </x-form.input-with-icon-wrapper>
                                <span id="PositionTitleError" class="text-red-500 text-xs mt-1 block"></span>
                            </div>
                        </div>

                        <!-- Salary Grade (Read-only) -->
                        <div class="sm:col-span-3">
                            <x-form.label for="salary_grade" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Salary Grade')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-layer-group"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="salary_grade"
                                        id="salary_grade"
                                        placeholder="{{ __('Salary Grade') }}"
                                        class="block w-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed"
                                        readonly />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>

                        <!-- Date Range: From Date and To Date -->
                        <div class="sm:col-span-3">
                            <x-form.label for="from_date" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('From Date')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-calendar"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="date"
                                        name="from_date"
                                        id="from_date"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        onchange="calculateTotalContractAmount()" />
                                </x-form.input-with-icon-wrapper>
                                <span id="fromDateError" class="text-red-500 text-xs"></span>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <x-form.label for="to_date" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('To Date')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-calendar"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="date"
                                        name="to_date"
                                        id="to_date"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        onchange="calculateTotalContractAmount()" />
                                </x-form.input-with-icon-wrapper>
                                <span id="toDateError" class="text-red-500 text-xs"></span>
                            </div>
                        </div>

                        <!-- Monthly Rate -->
                        <div class="sm:col-span-3">
                            <x-form.label for="monthly_rate" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Monthly Rate')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-peso-sign"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="number"
                                        name="monthly_rate"
                                        id="monthly_rate"
                                        step="0.01"
                                        placeholder="{{ __('0.00') }}"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        oninput="updateMonthlyRate()"
                                    />
                                </x-form.input-with-icon-wrapper>
                                <span id="monthlyRateError" class="text-red-500 text-xs"></span>
                            </div>
                        </div>

                        <!-- Total Contract Amount (Read-only, Computed) -->
                        <div class="sm:col-span-3">
                            <x-form.label for="annual_rate" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Total Contract Amount')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-peso-sign"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="number"
                                        name="annual_rate"
                                        id="annual_rate"
                                        step="0.01"
                                        placeholder="{{ __('0.00') }}"
                                        class="block w-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed"
                                        readonly />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>

                        <!-- Basis -->
                        <div class="sm:col-span-6">
                            <x-form.label for="basis" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Basis')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-contract"></i>
                                    </x-slot>
                                    <x-form.textarea
                                        withicon
                                        name="basis"
                                        id="basis"
                                        placeholder="{{ __('Basis for Contract of Service') }}"
                                        rows="2"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="sm:col-span-6">
                            <x-form.label for="remarks" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-note-sticky"></i>
                                    </x-slot>
                                    <x-form.textarea
                                        withicon
                                        name="remarks"
                                        id="remarks"
                                        placeholder="{{ __('Additional remarks or notes') }}"
                                        rows="2"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="justify-center items-center mt-0 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="handleSaveCos()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Save') }}
                </button>
                <button type="button" onclick="closeCreateModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</form>

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
(function () {
    // Scoped state — no longer collides with the Employee modal's `allEmployees`
    let appropriations = [];
    let allEmployees = [];
    let employeeSearchTimeout;
    let isManualEntryMode = false;

    async function openCreateCOSListModal() {
        closeAllDropdowns();
        isManualEntryMode = false;
        document.getElementById('createModal').style.display = 'flex';
        document.getElementById('createModal').setAttribute('aria-hidden', 'false');
        document.getElementById('createCosForm').reset();
        setPositionFieldsEditable(false);

        const employeeInput = document.getElementById('employee_name');
        employeeInput.disabled = true;
        employeeInput.placeholder = '{{ __("Loading employees...") }}';

        await fetchEmployees();

        employeeInput.disabled = false;
        employeeInput.placeholder = '{{ __("Search Employee") }}';

        await prefillFromActiveFilters();
    }

    async function prefillFromActiveFilters() {
        const filterClassId = '{{ request('office_allotment_class_filter') }}';
        const filterAppropriationId = '{{ request('appropriation_filter') }}';

        if (!filterClassId) return;

        const oac = officeAllotmentClasses.find(o => String(o.id) === String(filterClassId));
        if (!oac) return;

        document.getElementById('office_allotment_class').value = oac.name;
        document.getElementById('office_allotment_class_id').value = oac.id;

        try {
            const res = await fetch(`/api/cos_lists/appropriations/${oac.id}`);
            const data = await res.json();
            appropriations = data;

            if (filterAppropriationId) {
                const selectedApp = appropriations.find(app => String(app.id) === String(filterAppropriationId));
                if (selectedApp) {
                    document.getElementById('appropriation_name').value =
                        (selectedApp.account_code || '') + ' - ' + (selectedApp.description || '');
                    document.getElementById('appropriation_id').value = selectedApp.id;
                }
            }
        } catch (error) {
            console.error('Error pre-filling appropriations from active filters:', error);
        }
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    function handleSaveCos() {
        // Clear all errors first
        document.getElementById('OfficeAllotmentClassError').textContent = '';
        document.getElementById('AppropriationError').textContent = '';
        document.getElementById('EmployeeError').textContent = '';
        document.getElementById('PositionTitleError').textContent = '';
        document.getElementById('fromDateError').textContent = '';
        document.getElementById('toDateError').textContent = '';
        document.getElementById('monthlyRateError').textContent = '';

        let hasError = false;
        const officeAllotmentClassId = document.getElementById('office_allotment_class_id').value;
        const appropriationId = document.getElementById('appropriation_id').value;
        const employeeId = document.getElementById('employee_id').value;
        const employeeName = document.getElementById('employee_name').value.trim();
        const positionTitle = document.getElementById('position_title').value.trim();
        const monthlyRate = document.getElementById('monthly_rate').value;
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;

        if (!officeAllotmentClassId) {
            document.getElementById('OfficeAllotmentClassError').textContent = '{{ __("Office and Allotment Class is required.") }}';
            hasError = true;
        }

        if (!appropriationId) {
            document.getElementById('AppropriationError').textContent = '{{ __("Account is required.") }}';
            hasError = true;
        }

        if (!employeeId || !employeeName) {
            document.getElementById('EmployeeError').textContent = '{{ __("Employee is required.") }}';
            hasError = true;
        }

        if (!positionTitle) {
            document.getElementById('PositionTitleError').textContent = '{{ __("Position Title is required.") }}';
            hasError = true;
        }

        if (!fromDate) {
            document.getElementById('fromDateError').textContent = '{{ __("From Date is required.") }}';
            hasError = true;
        }

        if (!toDate) {
            document.getElementById('toDateError').textContent = '{{ __("To Date is required.") }}';
            hasError = true;
        }

        if (fromDate && toDate && new Date(toDate) < new Date(fromDate)) {
            document.getElementById('toDateError').textContent = '{{ __("To Date must be equal to or after From Date.") }}';
            hasError = true;
        }

        if (!monthlyRate || parseFloat(monthlyRate) <= 0) {
            document.getElementById('monthlyRateError').textContent = '{{ __("Monthly Rate is required.") }}';
            hasError = true;
        }

        if (hasError) {
            return;
        }

        document.getElementById('createCosForm').submit();
    }

    function filterOfficeAllotmentClasses() {
        const officeAllotmentClassIdField = document.getElementById('office_allotment_class_id');
        const appropriationIdField = document.getElementById('appropriation_id');
        if (officeAllotmentClassIdField.value && appropriationIdField.value) {
            appropriationIdField.value = '';
            document.getElementById('appropriation_name').value = '';
            document.getElementById('AppropriationDropdown').classList.add('hidden');
            appropriations = [];
        }
        officeAllotmentClassIdField.value = '';

        const input = document.getElementById('office_allotment_class').value.toLowerCase();
        const dropdown = document.getElementById('OfficeAllotmentClassDropdown');
        const options = officeAllotmentClasses.filter(oac =>
            oac.name.toLowerCase().includes(input)
        );

        if (input && options.length > 0) {
            dropdown.innerHTML = options.map(oac =>
                `<div class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-xs" 
                     data-id="${oac.id}" data-name="${oac.name}" 
                     onclick="selectOfficeAllotmentClass(this)">
                    ${oac.name}
                </div>`
            ).join('');
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    }

    async function selectOfficeAllotmentClass(el) {
        const id = el.dataset.id;
        const name = el.dataset.name;

        document.getElementById('office_allotment_class').value = name;
        document.getElementById('office_allotment_class_id').value = id;
        document.getElementById('OfficeAllotmentClassDropdown').classList.add('hidden');

        // Clear appropriation fields
        document.getElementById('appropriation_name').value = '';
        document.getElementById('appropriation_id').value = '';
        document.getElementById('AppropriationDropdown').classList.add('hidden');

        // Fetch appropriations for this office/allotment class
        try {
            const res = await fetch(`/api/cos_lists/appropriations/${id}`);
            const data = await res.json();
            appropriations = data; // Store appropriations for filtering
        } catch (error) {
            console.error('Error fetching appropriations:', error);
        }
    }

    function filterAppropriations() {
        const input = document.getElementById('appropriation_name').value.toLowerCase();
        const dropdown = document.getElementById('AppropriationDropdown');

        if (!input || appropriations.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }

        const filtered = appropriations.filter(app => {
            const accountCode = (app.account_code || '').toLowerCase();
            const description = (app.description || '').toLowerCase();
            const programs = (app.programs || '').toLowerCase();
            return accountCode.includes(input) || description.includes(input) || programs.includes(input);
        });

        if (filtered.length > 0) {
            dropdown.innerHTML = filtered.map(app =>
                `<div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                     data-id="${app.id}" data-name="${app.account_code || ''} - ${app.description || ''}"
                     onclick="selectAppropriationFromDropdown(this)">
                    <div class="font-semibold text-gray-900 dark:text-gray-100">${app.account_code || ''}</div>
                    <div class="text-gray-600 dark:text-gray-400">${app.description || ''}</div>
                    ${app.programs ? `<div class="text-gray-500 dark:text-gray-500 text-xs">${app.programs}</div>` : ''}
                </div>`
            ).join('');
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    }

    function selectAppropriationFromDropdown(el) {
        const id = el.dataset.id;
        const name = el.dataset.name;

        document.getElementById('appropriation_name').value = name;
        document.getElementById('appropriation_id').value = id;
        document.getElementById('AppropriationDropdown').classList.add('hidden');
    }

    // Helper function to format employee name as "Juan B. Dela Cruz Jr."
    function formatEmployeeName(emp) {
        // API returns fname, mname, lname, suffix
        const firstname = emp.fname || emp.firstname || emp.first_name || emp.firstName || '';
        const middleName = emp.mname || emp.middle_name || emp.middleName || emp.middle || '';
        const lastname = emp.lname || emp.lastname || emp.last_name || emp.lastName || '';
        const suffix = emp.suffix || emp.suffix_name || '';

        let name = firstname;

        if (middleName) {
            const middleInitial = middleName.charAt(0).toUpperCase();
            name += (name ? ' ' : '') + middleInitial + '.';
        }

        if (lastname) {
            name += (name ? ' ' : '') + lastname;
        }

        if (suffix) {
            name += ' ' + suffix;
        }

        // Fallback to fullname or name field if all else fails
        return name.trim() || emp.fullname || emp.name || emp.employee_id_number || 'Unknown';
    }

    // Fetch all employees from API once when modal opens (mirrors Employee modal's fetchEmployees)
    async function fetchEmployees() {
        try {
            const res = await fetch('http://192.168.2.26/api/v1/getEmployees', {
                method: 'GET',
                headers: {
                    'X-API-KEY': '2idqUEqD16WlkMwoWohuluNqFIm9ZqKmsw4GuSsM15E',
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            allEmployees = Array.isArray(data) ? data : (data.data || data.employees || []);
        } catch (e) {
            console.error('[COS-CREATE] Failed to fetch employees:', e);
            allEmployees = [];
        }
    }

    // Mirrors Employee modal's searchEmployee(query) — takes the value directly
    // instead of re-reading the input, and debounces the same way.
    // "Vacant" is always pinned at the top of the dropdown, including on focus
    // with an empty query, since COS positions are often unfilled.
    function searchEmployee(query) {
        clearTimeout(employeeSearchTimeout);

        // Skip the reset/re-lock entirely while manually entering an employee —
        // the user is actively typing the name and shouldn't get kicked back
        // into "locked" Position Title / Salary Grade on every keystroke.
        if (isManualEntryMode) {
            return;
        }

        // Clear auto-filled fields when user types again
        document.getElementById('employee_id').value = '';
        document.getElementById('position_title').value = '';
        document.getElementById('salary_grade').value = '';
        setPositionFieldsEditable(false);

        const trimmed = (query || '').trim();

        if (trimmed.length < 1) {
            renderEmployeeDropdown([]);
            return;
        }

        employeeSearchTimeout = setTimeout(() => {
            const q = trimmed.toLowerCase();
            const matches = allEmployees.filter(emp =>
                (emp.employee_id_number && emp.employee_id_number.toLowerCase().includes(q)) ||
                (emp.fullname && emp.fullname.toLowerCase().includes(q)) ||
                (formatEmployeeName(emp).toLowerCase().includes(q))
            ).slice(0, 10);

            renderEmployeeDropdown(matches);
        }, 250);
    }

    // Mirrors Employee modal's renderEmployeeDropdown — builds markup and passes
    // the full employee object via JSON.stringify in onclick, instead of round-tripping
    // individual fields through data-* attributes. "Vacant" is always pinned first.
    function renderEmployeeDropdown(employees) {
        const dropdown = document.getElementById('EmployeeDropdown');
        const query = document.getElementById('employee_name').value.trim();

        const vacantOption = `
            <div
                class="px-3 py-2 hover:bg-amber-50 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-200 dark:border-gray-700 bg-amber-50/50 dark:bg-gray-700/50"
                onclick="selectVacantPosition()">
                <div class="font-semibold text-amber-700 dark:text-amber-400 flex items-center gap-1">
                    <i class="fas fa-user-slash"></i> {{ __('Vacant') }}
                </div>
                <div class="text-gray-500 dark:text-gray-500 text-xs">{{ __('No employee assigned to this position') }}</div>
            </div>`;

        const manualOption = `
            <div
                class="px-3 py-2 hover:bg-blue-50 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-200 dark:border-gray-700 bg-blue-50/50 dark:bg-gray-700/50"
                onclick="selectManualEntry()">
                <div class="font-semibold text-blue-700 dark:text-blue-400 flex items-center gap-1">
                    <i class="fas fa-pen"></i> {{ __('Enter Employee Manually') }}
                </div>
                <div class="text-gray-500 dark:text-gray-500 text-xs">{{ __('Employee not found in the list? Type details manually.') }}</div>
            </div>`;

        const employeeOptions = employees.map(emp => `
            <div
                class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                onclick="selectEmployeeFromDropdown(${JSON.stringify(emp).replace(/"/g, '&quot;')})">
                <div class="font-semibold text-gray-900 dark:text-gray-100">${emp.employee_id_number}</div>
                <div class="text-gray-600 dark:text-gray-400">${formatEmployeeName(emp)}</div>
                ${emp.position_title ? `<div class="text-gray-500 dark:text-gray-500 text-xs">${emp.position_title}</div>` : ''}
            </div>
        `).join('');

        const searchHasQuery = query.length >= 1;
        const noResults = (employees.length === 0 && searchHasQuery)
            ? `<div class="px-4 py-3 text-gray-400 dark:text-gray-500 italic">{{ __('No employees found.') }}</div>`
            : '';

        dropdown.innerHTML = vacantOption + manualOption + employeeOptions + noResults;
        dropdown.classList.remove('hidden');
    }

    // No matching employee record exists — let the user type the name, position,
    // and salary grade directly instead of picking from the API list.
    function selectManualEntry() {
        isManualEntryMode = true;

        const typedName = document.getElementById('employee_name').value.trim();

        document.getElementById('employee_id').value = 'MANUAL-' + Date.now();
        document.getElementById('employee_name').value = typedName;
        document.getElementById('employee_name').readOnly = false;

        document.getElementById('position_title').value = '';
        document.getElementById('salary_grade').value = '';
        setPositionFieldsEditable(true);

        document.getElementById('EmployeeDropdown').classList.add('hidden');
        document.getElementById('employee_name').focus();
    }

    // Toggles Position Title / Salary Grade between locked (autofilled from an
    // employee record) and editable (manual entry for a Vacant position).
    function setPositionFieldsEditable(editable) {
        const positionTitle = document.getElementById('position_title');
        const salaryGrade = document.getElementById('salary_grade');

        [positionTitle, salaryGrade].forEach(field => {
            field.readOnly = !editable;
            field.classList.toggle('bg-gray-100', !editable);
            field.classList.toggle('cursor-not-allowed', !editable);
            field.classList.toggle('text-gray-600', !editable);
            field.classList.toggle('dark:bg-gray-700', !editable);
            field.classList.toggle('dark:text-gray-400', !editable);
            field.classList.toggle('bg-white', editable);
            field.classList.toggle('dark:bg-gray-800', editable);
        });

        positionTitle.placeholder = editable ? '{{ __("Enter position title") }}' : '{{ __("Position Title") }}';
        salaryGrade.placeholder = editable ? '{{ __("Enter salary grade") }}' : '{{ __("Salary Grade") }}';
    }

    // Selecting "Vacant" — no employee record exists, so Position Title and
    // Salary Grade become manually editable instead of autofilled.
    function selectVacantPosition() {
        isManualEntryMode = false; // exiting manual mode
        document.getElementById('employee_name').value = '{{ __("Vacant") }}';
        document.getElementById('employee_id').value = 'VACANT';
        document.getElementById('position_title').value = '';
        document.getElementById('salary_grade').value = '';
        setPositionFieldsEditable(true);
        document.getElementById('EmployeeDropdown').classList.add('hidden');
        document.getElementById('position_title').focus();
    }

    // Mirrors Employee modal's selectEmployee — receives the employee object directly.
    function selectEmployeeFromDropdown(emp) {
        isManualEntryMode = false; // exiting manual mode
        document.getElementById('employee_name').value = formatEmployeeName(emp);
        document.getElementById('employee_id').value = emp.employee_id_number;
        document.getElementById('position_title').value = emp.position_title || '';
        document.getElementById('salary_grade').value = emp.grade || emp.salary_grade || '';
        setPositionFieldsEditable(false);
        document.getElementById('EmployeeDropdown').classList.add('hidden');
    }

    function parseDateOnly(dateStr) {
        const [y, m, d] = dateStr.split('-').map(Number);
        return new Date(y, m - 1, d);
    }

    // Counts Mon–Fri days between two dates (inclusive)
    function countWeekdays(from, to) {
        let count = 0;
        let current = new Date(from);
        while (current <= to) {
            const day = current.getDay(); // 0 = Sunday, 6 = Saturday
            if (day !== 0 && day !== 6) count++;
            current.setDate(current.getDate() + 1);
        }
        return count;
    }

    // Iterates month by month between from/to. Full calendar months use the flat
    // monthly rate; partial months (period doesn't span the whole month) use
    // working days × daily rate (monthlyRate / 22).
    function computeTotalAmount(monthlyRate, fromDate, toDate) {
        const dailyRate = monthlyRate / 22;
        let total = 0;

        let cursor = new Date(fromDate.getFullYear(), fromDate.getMonth(), 1);

        while (cursor <= toDate) {
            const monthStart = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
            const monthEnd = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0);

            const periodStart = fromDate > monthStart ? fromDate : monthStart;
            const periodEnd = toDate < monthEnd ? toDate : monthEnd;

            const isFullMonth =
                periodStart.getTime() === monthStart.getTime() &&
                periodEnd.getTime() === monthEnd.getTime();

            if (isFullMonth) {
                total += monthlyRate;
            } else {
                const workingDays = countWeekdays(periodStart, periodEnd);
                total += dailyRate * workingDays;
            }

            cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
        }

        return total;
    }

    function calculateTotalContractAmount() {
        const monthlyRate = parseFloat(document.getElementById('monthly_rate').value) || 0;
        const fromDateVal = document.getElementById('from_date').value;
        const toDateVal = document.getElementById('to_date').value;

        if (fromDateVal && toDateVal && monthlyRate > 0) {
            const from = parseDateOnly(fromDateVal);   // was: new Date(fromDateVal)
            const to = parseDateOnly(toDateVal);       // was: new Date(toDateVal)

            if (to >= from) {
                const totalAmount = computeTotalAmount(monthlyRate, from, to);
                document.getElementById('annual_rate').value = totalAmount.toFixed(2);
            } else {
                document.getElementById('annual_rate').value = '0.00';
            }
        } else {
            document.getElementById('annual_rate').value = '0.00';
        }
    }

    // Update total contract amount when monthly rate changes
    function updateMonthlyRate() {
        calculateTotalContractAmount();
    }

    // Renamed to avoid clashing with the Employee modal's own closeAllDropdowns()
    function closeAllDropdowns() {
        document.getElementById('OfficeAllotmentClassDropdown').classList.add('hidden');
        document.getElementById('AppropriationDropdown').classList.add('hidden');
        document.getElementById('EmployeeDropdown').classList.add('hidden');
    }

    // Expose what's referenced by inline HTML attributes (onclick/oninput)
    window.openCreateCOSListModal = openCreateCOSListModal;
    window.closeCreateModal = closeCreateModal;
    window.handleSaveCos = handleSaveCos;
    window.filterOfficeAllotmentClasses = filterOfficeAllotmentClasses;
    window.selectOfficeAllotmentClass = selectOfficeAllotmentClass;
    window.filterAppropriations = filterAppropriations;
    window.selectAppropriationFromDropdown = selectAppropriationFromDropdown;
    window.searchEmployee = searchEmployee;
    window.selectEmployeeFromDropdown = selectEmployeeFromDropdown;
    window.selectVacantPosition = selectVacantPosition;
    window.parseDateOnly = parseDateOnly;
    window.calculateTotalContractAmount = calculateTotalContractAmount;
    window.countWeekdays = countWeekdays;
    window.computeTotalAmount = computeTotalAmount;
    window.updateMonthlyRate = updateMonthlyRate;
    window.selectManualEntry = selectManualEntry;

    window.closeAllDropdowns = closeAllDropdowns;
})();
</script>