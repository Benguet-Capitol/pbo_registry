<!-- Edit COS Record Modal -->
<form id="editCosForm" method="POST" action="">
    @csrf
    @method('PUT')
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
    <input type="hidden" name="office_allotment_class_id" id="edit_office_allotment_class_id" />
    <input type="hidden" name="appropriation_id" id="edit_appropriation_id" />
    <input type="hidden" name="employee_id" id="edit_employee_id" />

    <div id="editModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out; max-height: 90vh;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Contract of Service') }}
                </h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto flex-1" style="max-height: calc(90vh - 280px);">
                <div class="grid gap-3">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">

                        <!-- Office and Allotment Class -->
                        <div class="sm:col-span-3 relative">
                            <x-form.label for="edit_office_allotment_class" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-laptop-house"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="office_allotment_class"
                                        id="edit_office_allotment_class"
                                        placeholder="{{ __('Office and Allotment Class') }}"
                                        class="block w-full bg-white text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200"
                                        oninput="filterEditOfficeAllotmentClasses()"
                                        autocomplete="off" />
                                </x-form.input-with-icon-wrapper>
                                <div id="editOfficeAllotmentClassDropdown" class="absolute w-full bg-white dark:bg-gray-800 text-xs border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                    <!-- Suggestions appear here -->
                                </div>
                                <span id="edit_OfficeAllotmentClassError" class="text-red-500 text-xs mt-1 block"></span>
                            </div>
                        </div>

                        <!-- Accounts (Appropriations) -->
                        <div class="sm:col-span-3 relative">
                            <x-form.label for="edit_appropriation_name" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="appropriation_name"
                                        id="edit_appropriation_name"
                                        placeholder="{{ __('Select Account') }}"
                                        class="block w-full bg-white text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200"
                                        oninput="filterEditAppropriations()"
                                        autocomplete="off" />
                                </x-form.input-with-icon-wrapper>
                                <div id="editAppropriationDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-y-auto z-50">
                                    <!-- Suggestions appear here -->
                                </div>
                                <span id="edit_AppropriationError" class="text-red-500 text-xs mt-1 block"></span>
                            </div>
                        </div>

                        <!-- Employee -->
                        <div class="sm:col-span-6 relative">
                            <x-form.label for="edit_employee_name" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Employee')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-user"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="employee_name"
                                        id="edit_employee_name"
                                        placeholder="{{ __('Search Employee') }}"
                                        class="block w-full bg-white text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                        oninput="filterEditEmployees()"
                                        onfocus="filterEditEmployees()"
                                        autocomplete="off" />
                                </x-form.input-with-icon-wrapper>
                                <div id="editEmployeeDropdown" class="absolute w-full bg-white dark:bg-gray-800 text-xs border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                    <!-- Suggestions appear here -->
                                </div>
                                <span id="edit_EmployeeError" class="text-red-500 text-xs mt-1 block"></span>
                            </div>
                        </div>

                        <!-- Position Title (Read-only) -->
                        <div class="sm:col-span-3">
                            <x-form.label for="edit_position_title" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Position Title')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-briefcase"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="position_title"
                                        id="edit_position_title"
                                        placeholder="{{ __('Position Title') }}"
                                        class="block w-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed"
                                        readonly />
                                </x-form.input-with-icon-wrapper>
                                <span id="edit_PositionTitleError" class="text-red-500 text-xs mt-1 block"></span>
                            </div>
                        </div>

                        <!-- Salary Grade (Read-only) -->
                        <div class="sm:col-span-3">
                            <x-form.label for="edit_salary_grade" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Salary Grade')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-layer-group"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="text"
                                        name="salary_grade"
                                        id="edit_salary_grade"
                                        placeholder="{{ __('Salary Grade') }}"
                                        class="block w-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed"
                                        readonly />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>

                        <!-- Date Range: From Date and To Date -->
                        <div class="sm:col-span-3">
                            <x-form.label for="edit_from_date" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('From Date')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-calendar"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="date"
                                        name="from_date"
                                        id="edit_from_date"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        onchange="calculateEditTotalContractAmount()" />
                                </x-form.input-with-icon-wrapper>
                                <span id="edit_fromDateError" class="text-red-500 text-xs"></span>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <x-form.label for="edit_to_date" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('To Date')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-calendar"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="date"
                                        name="to_date"
                                        id="edit_to_date"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        onchange="calculateEditTotalContractAmount()" />
                                </x-form.input-with-icon-wrapper>
                                <span id="edit_toDateError" class="text-red-500 text-xs"></span>
                            </div>
                        </div>

                        <!-- Monthly Rate -->
                        <div class="sm:col-span-3">
                            <x-form.label for="edit_monthly_rate" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Monthly Rate')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-peso-sign"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="number"
                                        name="monthly_rate"
                                        id="edit_monthly_rate"
                                        step="0.01"
                                        placeholder="{{ __('0.00') }}"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        oninput="updateEditMonthlyRate()"
                                    />
                                </x-form.input-with-icon-wrapper>
                                <span id="edit_monthlyRateError" class="text-red-500 text-xs"></span>
                            </div>
                        </div>

                        <!-- Total Contract Amount (Read-only, Computed) -->
                        <div class="sm:col-span-3">
                            <x-form.label for="edit_annual_rate" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Total Contract Amount')" />
                            <div class="mt-2">
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-peso-sign"></i>
                                    </x-slot>
                                    <x-form.input
                                        withicon
                                        type="number"
                                        name="annual_rate"
                                        id="edit_annual_rate"
                                        step="0.01"
                                        placeholder="{{ __('0.00') }}"
                                        class="block w-full text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 cursor-not-allowed"
                                        readonly />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>

                    

                    <!-- Basis -->
                    <div class="sm:col-span-6">
                        <x-form.label for="edit_basis" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Basis')" />
                        <div class="mt-2">
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-file-contract"></i>
                                </x-slot>
                                <x-form.textarea
                                    withicon
                                    name="basis"
                                    id="edit_basis"
                                    placeholder="{{ __('Basis for Contract of Service') }}"
                                    rows="2"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                            </x-form.input-with-icon-wrapper>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="sm:col-span-6">
                        <x-form.label for="edit_remarks" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                        <div class="mt-2">
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-note-sticky"></i>
                                </x-slot>
                                <x-form.textarea
                                    withicon
                                    name="remarks"
                                    id="edit_remarks"
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
                <button type="button" onclick="handleSaveEditCos()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Update') }}
                </button>
                <button type="button" onclick="closeEditModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
function openEditCOSListModal(cosId, officeAllotmentClassId, employeeId, employeeName, positionTitle, salaryGrade, period, monthlyRate) {
    closeAllDropdowns();
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('editModal').setAttribute('aria-hidden', 'false');
    
    // Find office allotment class name from the data
    const oac = officeAllotmentClasses.find(o => o.id === officeAllotmentClassId);
    
    document.getElementById('editCosForm').action = '{{ route("cos_lists.update", ":id") }}'.replace(':id', cosId);
    document.getElementById('edit_office_allotment_class_id').value = officeAllotmentClassId;
    document.getElementById('edit_office_allotment_class').value = oac ? oac.name : '';
    document.getElementById('edit_employee_id').value = employeeId;
    document.getElementById('edit_employee_name').value = employeeName;
    document.getElementById('edit_position_title').value = positionTitle;
    document.getElementById('edit_salary_grade').value = salaryGrade;
    document.getElementById('edit_period').value = period;
    document.getElementById('edit_monthly_rate').value = monthlyRate;
    calculateEditAnnualRate();
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
}

function handleSaveEditCos() {
    // Clear all errors first
    document.getElementById('edit_OfficeAllotmentClassError').textContent = '';
    document.getElementById('edit_AppropriationError').textContent = '';
    document.getElementById('edit_EmployeeError').textContent = '';
    document.getElementById('edit_PositionTitleError').textContent = '';
    document.getElementById('edit_fromDateError').textContent = '';
    document.getElementById('edit_toDateError').textContent = '';
    document.getElementById('edit_monthlyRateError').textContent = '';

    let hasError = false;
    const officeAllotmentClassId = document.getElementById('edit_office_allotment_class_id').value;
    const appropriationId = document.getElementById('edit_appropriation_id').value;
    const employeeId = document.getElementById('edit_employee_id').value;
    const employeeName = document.getElementById('edit_employee_name').value.trim();
    const positionTitle = document.getElementById('edit_position_title').value.trim();
    const monthlyRate = document.getElementById('edit_monthly_rate').value;
    const fromDate = document.getElementById('edit_from_date').value;
    const toDate = document.getElementById('edit_to_date').value;

    if (!officeAllotmentClassId) {
        document.getElementById('edit_OfficeAllotmentClassError').textContent = '{{ __("Office and Allotment Class is required.") }}';
        hasError = true;
    }
    
    if (!appropriationId) {
        document.getElementById('edit_AppropriationError').textContent = '{{ __("Account is required.") }}';
        hasError = true;
    }
    
    if (!employeeId || !employeeName) {
        document.getElementById('edit_EmployeeError').textContent = '{{ __("Employee is required.") }}';
        hasError = true;
    }

    if (!positionTitle) {
        document.getElementById('edit_PositionTitleError').textContent = '{{ __("Position Title is required.") }}';
        hasError = true;
    }

    if (!fromDate) {
        document.getElementById('edit_fromDateError').textContent = '{{ __("From Date is required.") }}';
        hasError = true;
    }

    if (!toDate) {
        document.getElementById('edit_toDateError').textContent = '{{ __("To Date is required.") }}';
        hasError = true;
    }

    if (fromDate && toDate && new Date(toDate) < new Date(fromDate)) {
        document.getElementById('edit_toDateError').textContent = '{{ __("To Date must be equal to or after From Date.") }}';
        hasError = true;
    }
    
    if (!monthlyRate || parseFloat(monthlyRate) <= 0) {
        document.getElementById('edit_monthlyRateError').textContent = '{{ __("Monthly Rate is required.") }}';
        hasError = true;
    }

    if (hasError) {
        return;
    }

    document.getElementById('editCosForm').submit();
}

function filterEditOfficeAllotmentClasses() {
    const officeAllotmentClassIdField = document.getElementById('edit_office_allotment_class_id');
    const appropriationIdField = document.getElementById('edit_appropriation_id');
    if (officeAllotmentClassIdField.value && appropriationIdField.value) {
        appropriationIdField.value = '';
        document.getElementById('edit_appropriation_name').value = '';
        document.getElementById('editAppropriationDropdown').classList.add('hidden');
        editAppropriations = [];
    }
    officeAllotmentClassIdField.value = '';

    const input = document.getElementById('edit_office_allotment_class').value.toLowerCase();
    const dropdown = document.getElementById('editOfficeAllotmentClassDropdown');
    const options = officeAllotmentClasses.filter(oac =>
        oac.name.toLowerCase().includes(input)
    );

    if (input && options.length > 0) {
        dropdown.innerHTML = options.map(oac =>
            `<div class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-xs" 
                 data-id="${oac.id}" data-name="${oac.name}" 
                 onclick="selectEditOfficeAllotmentClass(this)">
                ${oac.name}
            </div>`
        ).join('');
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

async function selectEditOfficeAllotmentClass(el) {
    const id = el.dataset.id;
    const name = el.dataset.name;
    
    document.getElementById('edit_office_allotment_class').value = name;
    document.getElementById('edit_office_allotment_class_id').value = id;
    document.getElementById('editOfficeAllotmentClassDropdown').classList.add('hidden');
    
    // Clear appropriation fields
    document.getElementById('edit_appropriation_name').value = '';
    document.getElementById('edit_appropriation_id').value = '';
    document.getElementById('editAppropriationDropdown').classList.add('hidden');
    
    // Fetch appropriations for this office/allotment class
    try {
        const res = await fetch(`/api/cos_lists/appropriations/${id}`);
        const data = await res.json();
        editAppropriations = data; // Store appropriations for filtering
        console.log('Edit Appropriations loaded:', editAppropriations); // Debug log
    } catch (error) {
        console.error('Error fetching appropriations:', error);
    }
}

function filterEditAppropriations() {
    const input = document.getElementById('edit_appropriation_name').value.toLowerCase();
    const dropdown = document.getElementById('editAppropriationDropdown');
    
    if (!input || editAppropriations.length === 0) {
        dropdown.classList.add('hidden');
        return;
    }
    
    const filtered = editAppropriations.filter(app => {
        const accountCode = (app.account_code || '').toLowerCase();
        const description = (app.description || '').toLowerCase();
        const programs = (app.programs || '').toLowerCase();
        return accountCode.includes(input) || description.includes(input) || programs.includes(input);
    });
    
    if (filtered.length > 0) {
        dropdown.innerHTML = filtered.map(app =>
            `<div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                 data-id="${app.id}" data-name="${app.account_code || ''} - ${app.description || ''}"
                 onclick="selectEditAppropriationFromDropdown(this)">
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

function selectEditAppropriationFromDropdown(el) {
    const id = el.dataset.id;
    const name = el.dataset.name;
    
    document.getElementById('edit_appropriation_name').value = name;
    document.getElementById('edit_appropriation_id').value = id;
    document.getElementById('editAppropriationDropdown').classList.add('hidden');
}

let editAppropriations = [];
let editAllEmployees = [];
let editEmployeeSearchTimeout;

// Helper function to format employee name as "Juan B. Dela Cruz Jr."
function formatEditEmployeeName(emp) {
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

// Fetch all employees when modal opens - directly from external API
async function fetchAllEditEmployees() {
    try {
        const res = await fetch('http://192.168.2.26/api/v1/getEmployees', {
            method: 'GET',
            headers: {
                'X-API-KEY': '2idqUEqD16WlkMwoWohuluNqFIm9ZqKmsw4GuSsM15E',
                'Accept': 'application/json',
            }
        });
        const data = await res.json();
        editAllEmployees = data;
    } catch (e) {
        console.error('Failed to fetch edit employees:', e);
        editAllEmployees = [];
    }
}

function filterEditEmployees() {
    clearTimeout(editEmployeeSearchTimeout);
    const input = document.getElementById('edit_employee_name').value;

    // Clear auto-filled fields when user types again
    document.getElementById('edit_employee_id').value = '';
    document.getElementById('edit_position_title').value = '';
    document.getElementById('edit_salary_grade').value = '';
    setEditPositionFieldsEditable(false);

    const trimmed = (input || '').trim();

    if (trimmed.length < 1) {
        // No query yet (e.g. just focused the field) — show only the Vacant option
        renderEditEmployeeDropdown([]);
        return;
    }

    editEmployeeSearchTimeout = setTimeout(() => {
        const q = trimmed.toLowerCase();
        const filtered = editAllEmployees.filter(emp =>
            (emp.employee_id_number && emp.employee_id_number.toLowerCase().includes(q)) ||
            (emp.fullname && emp.fullname.toLowerCase().includes(q)) ||
            (formatEditEmployeeName(emp).toLowerCase().includes(q))
        ).slice(0, 10);

        renderEditEmployeeDropdown(filtered);
    }, 250);
}

function renderEditEmployeeDropdown(employees) {
    const dropdown = document.getElementById('editEmployeeDropdown');
    const query = document.getElementById('edit_employee_name').value.trim();

    const vacantOption = `
        <div
            class="px-3 py-2 hover:bg-amber-50 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-200 dark:border-gray-700 bg-amber-50/50 dark:bg-gray-700/50"
            onclick="selectEditVacantPosition()">
            <div class="font-semibold text-amber-700 dark:text-amber-400 flex items-center gap-1">
                <i class="fas fa-user-slash"></i> {{ __('Vacant') }}
            </div>
            <div class="text-gray-500 dark:text-gray-500 text-xs">{{ __('No employee assigned to this position') }}</div>
        </div>`;

    const manualOption = `
        <div
            class="px-3 py-2 hover:bg-blue-50 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-200 dark:border-gray-700 bg-blue-50/50 dark:bg-gray-700/50"
            onclick="selectEditManualEntry()">
            <div class="font-semibold text-blue-700 dark:text-blue-400 flex items-center gap-1">
                <i class="fas fa-pen"></i> {{ __('Enter Employee Manually') }}
            </div>
            <div class="text-gray-500 dark:text-gray-500 text-xs">{{ __('Employee not found in the list? Type details manually.') }}</div>
        </div>`;

    const employeeOptions = employees.map(emp => `
        <div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-200 dark:border-gray-700 last:border-b-0"
             data-employee-id-number="${emp.employee_id_number || ''}"
             data-name="${formatEditEmployeeName(emp)}"
             data-position="${emp.position_title || ''}"
             data-grade="${emp.grade || emp.salary_grade || ''}"
             onclick="selectEditEmployeeFromDropdown(this)">
            <div class="font-semibold text-gray-900 dark:text-gray-100">${emp.employee_id_number}</div>
            <div class="text-gray-600 dark:text-gray-400">${formatEditEmployeeName(emp)}</div>
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

function selectEditManualEntry() {
    const typedName = document.getElementById('edit_employee_name').value.trim();

    document.getElementById('edit_employee_id').value = 'MANUAL-' + Date.now();
    document.getElementById('edit_employee_name').value = typedName;
    document.getElementById('edit_employee_name').readOnly = false;

    document.getElementById('edit_position_title').value = '';
    document.getElementById('edit_salary_grade').value = '';
    setEditPositionFieldsEditable(true);

    document.getElementById('editEmployeeDropdown').classList.add('hidden');
    document.getElementById('edit_position_title').focus();
}

function setEditPositionFieldsEditable(editable) {
    const positionTitle = document.getElementById('edit_position_title');
    const salaryGrade = document.getElementById('edit_salary_grade');

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
function selectEditVacantPosition() {
    document.getElementById('edit_employee_name').value = '{{ __("Vacant") }}';
    document.getElementById('edit_employee_id').value = 'VACANT';
    document.getElementById('edit_position_title').value = '';
    document.getElementById('edit_salary_grade').value = '';
    setEditPositionFieldsEditable(true);
    document.getElementById('editEmployeeDropdown').classList.add('hidden');
    document.getElementById('edit_position_title').focus();
}

function selectEditEmployeeFromDropdown(el) {
    const name = el.dataset.name;
    const employeeIdNumber = el.dataset.employeeIdNumber;
    const position = el.dataset.position;
    const grade = el.dataset.grade;
    
    document.getElementById('edit_employee_name').value = name;
    document.getElementById('edit_employee_id').value = employeeIdNumber;
    document.getElementById('edit_position_title').value = position;
    document.getElementById('edit_salary_grade').value = grade;
    setEditPositionFieldsEditable(false);
    document.getElementById('editEmployeeDropdown').classList.add('hidden');
}

// Counts Mon–Fri days between two dates (inclusive)
function countWeekdays(from, to) {
    let count = 0;
    let current = new Date(from);
    while (current <= to) {
        const day = current.getDay();
        if (day !== 0 && day !== 6) count++;
        current.setDate(current.getDate() + 1);
    }
    return count;
}

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

function parseDateOnly(dateStr) {
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d);
}

function calculateEditTotalContractAmount() {
    const monthlyRate = parseFloat(document.getElementById('edit_monthly_rate').value) || 0;
    const fromDateVal = document.getElementById('edit_from_date').value;
    const toDateVal = document.getElementById('edit_to_date').value;

    if (fromDateVal && toDateVal && monthlyRate > 0) {
        const from = parseDateOnly(fromDateVal);   // was: new Date(fromDateVal)
        const to = parseDateOnly(toDateVal);       // was: new Date(toDateVal)

        if (to >= from) {
            const totalAmount = computeTotalAmount(monthlyRate, from, to);
            document.getElementById('edit_annual_rate').value = totalAmount.toFixed(2);
        } else {
            document.getElementById('edit_annual_rate').value = '0.00';
        }
    } else {
        document.getElementById('edit_annual_rate').value = '0.00';
    }
}

function updateEditMonthlyRate() {
    calculateEditTotalContractAmount();
}

function closeAllDropdowns() {
    document.getElementById('editOfficeAllotmentClassDropdown').classList.add('hidden');    document.getElementById('editAppropriationDropdown').classList.add('hidden');    document.getElementById('editEmployeeDropdown').classList.add('hidden');
}
</script>
