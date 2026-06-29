<!-- Edit Employee Modal -->
<form id="editEmployeeForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editEmployeeModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="w-full max-w-4xl rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp max-h-[90vh] flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:bg-gray-700 flex flex-col h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center px-6 py-4 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-700 flex-shrink-0">
                    <h3 class="text-base leading-6 font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-user-edit text-amber-600 dark:text-amber-400 mr-3 text-xl"></i>
                        {{ __('Edit Employee') }}
                    </h3>
                    <button type="button" onclick="closeEditEmployeeModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="overflow-y-auto flex-1 px-7 py-3">
                    <input type="hidden" name="id_employee" id="id_employee">
                    <div class="grid gap-3">

                        <!-- Employee ID -->
                        <div class="space-y-3 relative">
                            <x-form.label for="edit_employee_id" :value="__('Employee ID')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-id-card"></i></x-slot>
                                <x-form.input
                                    withicon
                                    id="edit_employee_id"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600"
                                    type="text"
                                    name="edit_employee_id"
                                    autocomplete="off"
                                    placeholder="{{ __('Type to search Employee ID or Name...') }}"
                                    oninput="searchEditEmployee(this.value)"
                                />
                            </x-form.input-with-icon-wrapper>

                            {{-- Autocomplete dropdown --}}
                            <div id="editEmployeeDropdown" class="hidden absolute z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto w-full text-xs">
                            </div>

                            <span class="text-red-500 text-xs error-message" id="edit_error_employee_id"></span>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <x-form.label for="edit_name" :value="__('Name')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-user"></i></x-slot>
                                <x-form.input
                                    withicon
                                    id="edit_name"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 bg-gray-50"
                                    type="text"
                                    name="edit_name"
                                    autocomplete="off"
                                    placeholder="{{ __('Auto-filled') }}"
                                    readonly
                                />
                            </x-form.input-with-icon-wrapper>
                            <span class="text-red-500 text-xs error-message" id="edit_error_name"></span>
                        </div>

                        <!-- Designation -->
                        <div class="space-y-2">
                            <x-form.label for="edit_designation" :value="__('Designation')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-briefcase"></i></x-slot>
                                <x-form.input
                                    withicon
                                    id="edit_designation"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 bg-gray-50"
                                    type="text"
                                    name="edit_designation"
                                    autocomplete="off"
                                    placeholder="{{ __('Auto-filled') }}"
                                    readonly
                                />
                            </x-form.input-with-icon-wrapper>
                            <span class="text-red-500 text-xs error-message" id="edit_error_designation"></span>
                        </div>

                        <!-- Office -->
                        <div class="space-y-2">
                            <x-form.label for="edit_office" :value="__('Office')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-building"></i></x-slot>
                                <x-form.select withicon id="edit_office" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" name="edit_office" placeholder="{{ __('Office') }}">
                                    <option value="">{{ __('Select Office') }}</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">
                                            {{ $office->office_abbreviation }} - {{ $office->office_name }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_error_office" class="text-red-500 text-xs"></span>
                        </div>

                    </div>
                </div>

                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditEmployeeForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditEmployeeModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let editSearchTimeout = null;

    // Reuses allEmployees from the create modal script.
    // If this file is included independently, declare it here:
    // let allEmployees = [];

    async function ensureEmployeesLoaded() {
        if (!allEmployees || allEmployees.length === 0) {
            try {
                const res = await fetch('http://192.168.2.26/api/v1/getEmployees', {
                    method: 'GET',
                    headers: {
                        'X-API-KEY': '2idqUEqD16WlkMwoWohuluNqFIm9ZqKmsw4GuSsM15E',
                        'Accept': 'application/json',
                    }
                });
                allEmployees = await res.json();
            } catch (e) {
                console.error('Failed to fetch employees:', e);
                allEmployees = [];
            }
        }
    }

    async function openEditEmployeeModal(employee) {
        closeAllDropdowns();
        const modal = document.getElementById('editEmployeeModal');
        const form = document.getElementById('editEmployeeForm');

        // Set hidden ID and form action
        document.querySelector("input[name='id_employee']").value = employee.id ?? '';
        form.action = `/employees/${employee.id}`;

        // Populate fields with existing employee data
        document.getElementById('edit_employee_id').value = employee.employee_id ?? '';
        document.getElementById('edit_name').value = employee.name ?? '';
        document.getElementById('edit_designation').value = employee.designation ?? '';
        document.getElementById('edit_office').value = employee.office ?? '';

        // Clear errors
        document.getElementById('edit_error_employee_id').innerText = '';
        document.getElementById('edit_error_name').innerText = '';
        document.getElementById('edit_error_designation').innerText = '';
        document.getElementById('edit_error_office').innerText = '';

        // Show modal
        modal.style.display = 'flex';
        setTimeout(() => {
            const box = modal.querySelector('div.hidden');
            if (box) box.classList.remove('hidden');
        }, 10);

        // Pre-load employees in background for search
        ensureEmployeesLoaded();
    }

    function closeEditEmployeeModal() {
        closeEditEmployeeDropdown();
        const modal = document.getElementById('editEmployeeModal');
        const box = modal.querySelector('div.hidden, div[style*="animation"]') || modal.querySelector('> div');
        if (box) {
            box.classList.add('hidden');
            setTimeout(() => { modal.style.display = 'none'; }, 300);
        } else {
            modal.style.display = 'none';
        }
    }

    function searchEditEmployee(query) {
        clearTimeout(editSearchTimeout);
        closeEditEmployeeDropdown();

        // Clear autofilled fields when user types a new search
        document.getElementById('edit_name').value = '';
        document.getElementById('edit_designation').value = '';
        document.getElementById('edit_office').value = '';

        if (!query || query.trim().length < 1) return;

        editSearchTimeout = setTimeout(() => {
            const q = query.toLowerCase();
            const matches = allEmployees.filter(emp =>
                (emp.employee_id_number && emp.employee_id_number.toLowerCase().includes(q)) ||
                (emp.fullname && emp.fullname.toLowerCase().includes(q))
            ).slice(0, 10);

            renderEditEmployeeDropdown(matches);
        }, 250);
    }

    function renderEditEmployeeDropdown(employees) {
        const dropdown = document.getElementById('editEmployeeDropdown');

        if (!employees.length) {
            dropdown.innerHTML = `<div class="px-4 py-3 text-gray-400 dark:text-gray-500 italic">No employees found.</div>`;
            dropdown.classList.remove('hidden');
            return;
        }

        dropdown.innerHTML = employees.map(emp => `
            <div
                class="px-4 py-2 cursor-pointer hover:bg-amber-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0"
                onclick="selectEditEmployee(${JSON.stringify(emp).replace(/"/g, '&quot;')})">
                <div class="font-semibold text-gray-800 dark:text-gray-100">${emp.employee_id_number}</div>
                <div class="text-gray-600 dark:text-gray-300">${formatEmployeeName(emp)}</div>
                <div class="text-gray-400 dark:text-gray-500 text-[10px]">${emp.position_title ?? ''} &middot; ${emp.office_desc ?? ''}</div>
            </div>
        `).join('');

        dropdown.classList.remove('hidden');
    }

    function selectEditEmployee(emp) {
        document.getElementById('edit_employee_id').value = emp.employee_id_number;
        document.getElementById('edit_name').value = formatEmployeeName(emp);
        document.getElementById('edit_designation').value = emp.position_title ?? '';
        matchEditOfficeSelect(emp.office_desc);
        closeEditEmployeeDropdown();

        // Clear errors
        document.getElementById('edit_error_employee_id').innerText = '';
        document.getElementById('edit_error_name').innerText = '';
        document.getElementById('edit_error_designation').innerText = '';
        document.getElementById('edit_error_office').innerText = '';
    }

    function formatEmployeeName(emp) {
        const fname = (emp.fname ?? '').trim();
        const mname = (emp.mname ?? '').trim();
        const lname = (emp.lname ?? '').trim();
        const suffix = (emp.suffix ?? emp.name_suffix ?? '').trim();

        const middleInitial = mname ? mname.charAt(0).toUpperCase() + '.' : '';

        return [fname, middleInitial, lname, suffix].filter(Boolean).join(' ');
    }

    function matchEditOfficeSelect(officeDesc) {
        if (!officeDesc) return;
        const select = document.getElementById('edit_office');
        const q = officeDesc.toLowerCase().trim();

        for (let option of select.options) {
            if (option.text.toLowerCase().includes(q) || q.includes(option.text.toLowerCase())) {
                select.value = option.value;
                return;
            }
        }

        // Fallback: partial word match
        for (let option of select.options) {
            const words = q.split(' ');
            if (words.some(w => w.length > 2 && option.text.toLowerCase().includes(w))) {
                select.value = option.value;
                return;
            }
        }
    }

    function closeEditEmployeeDropdown() {
        const dropdown = document.getElementById('editEmployeeDropdown');
        if (dropdown) dropdown.classList.add('hidden');
    }

    // Close edit dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('editEmployeeDropdown');
        const input = document.getElementById('edit_employee_id');
        if (dropdown && input && !dropdown.contains(e.target) && e.target !== input) {
            closeEditEmployeeDropdown();
        }
    });

    function validateEditEmployeeForm() {
        let isValid = true;

        const idInput = document.getElementById('edit_employee_id');
        const nameInput = document.getElementById('edit_name');
        const officeInput = document.getElementById('edit_office');
        const designationInput = document.getElementById('edit_designation');

        document.getElementById('edit_error_employee_id').innerText = '';
        document.getElementById('edit_error_name').innerText = '';
        document.getElementById('edit_error_office').innerText = '';
        document.getElementById('edit_error_designation').innerText = '';

        if (!idInput.value.trim()) {
            document.getElementById('edit_error_employee_id').innerText = 'Employee ID is required.';
            isValid = false;
        }

        if (!nameInput.value.trim()) {
            document.getElementById('edit_error_name').innerText = 'Name is required.';
            isValid = false;
        }

        if (!designationInput.value.trim()) {
            document.getElementById('edit_error_designation').innerText = 'Designation is required.';
            isValid = false;
        }

        if (!officeInput.value) {
            document.getElementById('edit_error_office').innerText = 'Please select an office.';
            isValid = false;
        }

        if (isValid) {
            document.getElementById('editEmployeeForm').submit();
        }
    }
</script>

<style>
    @keyframes scaleInUp {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-scaleInUp { animation: scaleInUp 0.3s ease-out; }
</style>