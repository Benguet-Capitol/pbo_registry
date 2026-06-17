<!-- Create Employee Modal -->
<form id="createEmployeeForm" method="POST" action="{{ route('employees.store') }}">
    @csrf
    <div id="createEmployeeModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="w-full max-w-4xl rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp max-h-[90vh] flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:bg-gray-700 flex flex-col h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center px-6 py-4 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 flex-shrink-0">
                    <h3 class="text-base leading-6 font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-user-plus text-blue-600 dark:text-blue-400 mr-3 text-xl"></i>
                        {{ __('Add Employee') }}
                    </h3>
                    <button type="button" onclick="closeCreateEmployeeModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="overflow-y-auto flex-1 px-7 py-3">
                    <div class="grid gap-3">
                        <div class="space-y-3">
                            <x-form.label for="employee_id" :value="__('Employee ID')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-id-card"></i>
                                </x-slot>
                                {{-- Changed: added oninput, removed :value to keep it empty for search --}}
                                <x-form.input
                                    withicon
                                    id="employee_id"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600"
                                    type="text"
                                    name="employee_id"
                                    autofocus
                                    placeholder="{{ __('Type to search Employee ID or Name...') }}"
                                    autocomplete="off"
                                    oninput="searchEmployee(this.value)" />
                            </x-form.input-with-icon-wrapper>

                            {{-- Autocomplete dropdown --}}
                            <div id="employeeDropdown" class="hidden absolute z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto w-full text-xs">
                                {{-- Results injected by JS --}}
                            </div>

                            <span id="employeeIdError" class="text-red-500 text-xs"></span>

                            {{-- Hidden field to store the actual selected employee_id_number --}}
                            <input type="hidden" id="employee_id_number" name="employee_id_number" />
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <x-form.label for="name" :value="__('Name')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-user"></i>
                                </x-slot>
                                <x-form.input
                                    withicon
                                    id="name"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 bg-gray-50"
                                    type="text"
                                    name="name"
                                    placeholder="{{ __('Auto-filled') }}"
                                    autocomplete="off"
                                    readonly />
                            </x-form.input-with-icon-wrapper>
                            <span id="nameError" class="text-red-500 text-xs"></span>
                        </div>

                        <!-- Designation -->
                        <div class="space-y-2">
                            <x-form.label for="designation" :value="__('Designation')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-briefcase"></i>
                                </x-slot>
                                <x-form.input
                                    withicon
                                    id="designation"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 bg-gray-50"
                                    type="text"
                                    name="designation"
                                    placeholder="{{ __('Auto-filled') }}"
                                    autocomplete="off"
                                    readonly />
                            </x-form.input-with-icon-wrapper>
                            <span id="designationError" class="text-red-500 text-xs"></span>
                        </div>

                        <!-- Office -->
                        <div class="space-y-2">
                            <x-form.label for="office" :value="__('Office')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-building"></i>
                                </x-slot>
                                <x-form.select withicon id="office" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" name="office" :value="old('office')" placeholder="{{ __('Office') }}">
                                    <option value="">{{ __('Select Office') }}</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ old('office') == $office->id ? 'selected' : '' }}>
                                            {{ $office->office_abbreviation }} - {{ $office->office_name }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="officeError" class="text-red-500 text-xs"></span>
                        </div>

                    </div>
                </div>

                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="if(!isSubmittingEmployee) validateCreateEmployeeForm(); return false;" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateEmployeeModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let isSubmittingEmployee = false;
    let allEmployees = [];
    let searchTimeout = null;

    // Fetch all employees from API once when modal opens
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
            allEmployees = data;
        } catch (e) {
            console.error('Failed to fetch employees:', e);
            allEmployees = [];
        }
    }

    function openCreateEmployeeModal() {
        closeAllDropdowns();
        isSubmittingEmployee = false;
        resetCreateEmployeeForm();
        const modal = document.getElementById('createEmployeeModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            const box = modal.querySelector('div.hidden');
            if (box) box.classList.remove('hidden');
        }, 10);
        // Fetch employees when modal opens
        fetchEmployees();
    }

    function closeCreateEmployeeModal() {
        isSubmittingEmployee = false;
        closeEmployeeDropdown();
        const modal = document.getElementById('createEmployeeModal');
        const box = modal.querySelector('div.hidden, div[style*="animation"]') || modal.querySelector('> div');
        if (box) {
            box.classList.add('hidden');
            setTimeout(() => { modal.style.display = 'none'; }, 300);
        } else {
            modal.style.display = 'none';
        }
    }

    function resetCreateEmployeeForm() {
        document.getElementById('employee_id').value = '';
        document.getElementById('employee_id_number').value = '';
        document.getElementById('name').value = '';
        document.getElementById('designation').value = '';
        document.getElementById('office').value = '';
        ['employeeIdError','nameError','designationError','officeError'].forEach(id => {
            document.getElementById(id).innerText = '';
        });
    }

    function searchEmployee(query) {
        clearTimeout(searchTimeout);
        closeEmployeeDropdown();

        // Clear autofilled fields when user types again
        document.getElementById('employee_id_number').value = '';
        document.getElementById('name').value = '';
        document.getElementById('designation').value = '';
        document.getElementById('office').value = '';

        if (!query || query.trim().length < 1) return;

        searchTimeout = setTimeout(() => {
            const q = query.toLowerCase();
            const matches = allEmployees.filter(emp =>
                (emp.employee_id_number && emp.employee_id_number.toLowerCase().includes(q)) ||
                (emp.fullname && emp.fullname.toLowerCase().includes(q))
            ).slice(0, 10);

            renderEmployeeDropdown(matches);
        }, 250);
    }

    function renderEmployeeDropdown(employees) {
        const dropdown = document.getElementById('employeeDropdown');

        if (!employees.length) {
            dropdown.innerHTML = `<div class="px-4 py-3 text-gray-400 dark:text-gray-500 italic">No employees found.</div>`;
            dropdown.classList.remove('hidden');
            return;
        }

        dropdown.innerHTML = employees.map(emp => `
            <div
                class="px-4 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0"
                onclick="selectEmployee(${JSON.stringify(emp).replace(/"/g, '&quot;')})">
                <div class="font-semibold text-gray-800 dark:text-gray-100">${emp.employee_id_number}</div>
                <div class="text-gray-600 dark:text-gray-300">${formatEmployeeName(emp)}</div>
                <div class="text-gray-400 dark:text-gray-500 text-[10px]">${emp.position_title ?? ''} &middot; ${emp.office_desc ?? ''}</div>
            </div>
        `).join('');

        dropdown.classList.remove('hidden');
    }

    function selectEmployee(emp) {
        // Fill Employee ID field
        document.getElementById('employee_id').value = emp.employee_id_number;
        document.getElementById('employee_id_number').value = emp.employee_id_number;

        // Fill Name
        document.getElementById('name').value = formatEmployeeName(emp);

        // Fill Designation
        document.getElementById('designation').value = emp.position_title ?? '';

        // Match office by office_desc to the <select> options
        matchOfficeSelect(emp.office_desc);

        // Close dropdown
        closeEmployeeDropdown();

        // Clear errors
        document.getElementById('employeeIdError').innerText = '';
        document.getElementById('nameError').innerText = '';
        document.getElementById('designationError').innerText = '';
        document.getElementById('officeError').innerText = '';
    }

    function formatEmployeeName(emp) {
        const fname = (emp.fname ?? '').trim();
        const mname = (emp.mname ?? '').trim();
        const lname = (emp.lname ?? '').trim();

        const middleInitial = mname ? mname.charAt(0).toUpperCase() + '.' : '';

        return [fname, middleInitial, lname].filter(Boolean).join(' ');
    }

    function matchOfficeSelect(officeDesc) {
        if (!officeDesc) return;
        const select = document.getElementById('office');
        const q = officeDesc.toLowerCase().trim();

        // Try to match against office_name or office_abbreviation in the option text
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

    function closeEmployeeDropdown() {
        const dropdown = document.getElementById('employeeDropdown');
        if (dropdown) dropdown.classList.add('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('employeeDropdown');
        const input = document.getElementById('employee_id');
        if (dropdown && input && !dropdown.contains(e.target) && e.target !== input) {
            closeEmployeeDropdown();
        }
    });

    function validateCreateEmployeeForm() {
        if (isSubmittingEmployee) return false;

        let isValid = true;

        const employeeId = document.getElementById('employee_id').value.trim();
        const name = document.getElementById('name').value.trim();
        const office = document.getElementById('office').value;
        const designation = document.getElementById('designation').value.trim();

        if (!employeeId) {
            document.getElementById('employeeIdError').innerText = 'Employee ID is required.';
            isValid = false;
        } else if (!document.getElementById('employee_id_number').value) {
            // No employee was selected from the dropdown — typed manually or not confirmed
            document.getElementById('employeeIdError').innerText = 'Please select a valid Employee ID from the search results.';
            isValid = false;
        } else {
            document.getElementById('employeeIdError').innerText = '';
        }

        if (!name) {
            document.getElementById('nameError').innerText = 'Name is required.';
            isValid = false;
        } else {
            document.getElementById('nameError').innerText = '';
        }

        if (!designation) {
            document.getElementById('designationError').innerText = 'Designation is required.';
            isValid = false;
        } else {
            document.getElementById('designationError').innerText = '';
        }

        if (!office) {
            document.getElementById('officeError').innerText = 'Office is required.';
            isValid = false;
        } else {
            document.getElementById('officeError').innerText = '';
        }

        if (isValid) {
            // Check uniqueness against existing DB records via fetch before submitting
            const employeeIdNumber = document.getElementById('employee_id_number').value;
            checkEmployeeIdUnique(employeeIdNumber, null, function(isUnique) {
                if (!isUnique) {
                    document.getElementById('employeeIdError').innerText = 'The Employee ID already exists.';
                } else {
                    isSubmittingEmployee = true;
                    document.getElementById('createEmployeeForm').submit();
                }
            });
        }

        return false;
    }

    function checkEmployeeIdUnique(employeeId, excludeId, callback) {
        const params = new URLSearchParams({ employee_id: employeeId });
        if (excludeId) params.append('exclude_id', excludeId);

        fetch(`/employees/check-unique?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(res => res.json())
        .then(data => {
            callback(data.is_unique)
        })
        .catch(() => callback(true));
    }
</script>

<style>
    @keyframes scaleInUp {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-scaleInUp { animation: scaleInUp 0.3s ease-out; }
</style>