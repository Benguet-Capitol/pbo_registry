<!-- Edit Employee Modal -->
<form id="editEmployeeForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editEmployeeModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="w-full max-w-4xl rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp max-h-[90vh] flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal content -->
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:bg-gray-700 flex flex-col h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-6 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-700 flex-shrink-0">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white flex items-center">
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
                        <div class="space-y-3">
                            <x-form.label for="employee_id" :value="__('Employee ID')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-id-card"></i></x-slot>
                                <x-form.input
                                    withicon
                                    id="edit_employee_id"
                                    class="block w-full"
                                    type="text"
                                    name="edit_employee_id"
                                    :value="old('employee_id', $employee->employee_id)"
                                    autofocus
                                    autocomplete="off"
                                    placeholder="{{ __('Employee ID') }}"
                                />
                            </x-form.input-with-icon-wrapper>
                            <span class="text-red-500 text-sm error-message" id="edit_error_employee_id"></span>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <x-form.label for="name" :value="__('Name')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-user"></i></x-slot>
                                <x-form.input
                                    withicon
                                    id="edit_name"
                                    class="block w-full"
                                    type="text"
                                    name="edit_name"
                                    autocomplete="off"
                                    :value="old('name', $employee->name)"
                                    placeholder="{{ __('Name') }}"
                                />
                            </x-form.input-with-icon-wrapper>
                            <span class="text-red-500 text-sm error-message" id="edit_error_name"></span>
                        </div>

                        <!-- Designation -->
                        <div class="space-y-2">
                            <x-form.label for="designation" :value="__('Designation')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-briefcase"></i></x-slot>
                                <x-form.input
                                    withicon
                                    id="edit_designation"
                                    class="block w-full"
                                    type="text"
                                    name="edit_designation"
                                    autocomplete="off"
                                    :value="old('designation', $employee->designation)"
                                    placeholder="{{ __('Designation') }}"
                                />
                            </x-form.input-with-icon-wrapper>
                            <span class="text-red-500 text-sm error-message" id="edit_error_designation"></span>
                        </div>

                        <!-- Office -->
                        <div class="space-y-2">
                            <x-form.label for="edit_office" :value="__('Office')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-building"></i>
                                </x-slot>
                                <x-form.select withicon id="edit_office" class="block w-full" name="edit_office" placeholder="{{ __('Office') }}">
                                    <option value="">{{ __('Select Office') }}</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">
                                            {{ $office->office_abbreviation }} - {{ $office->office_name }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_error_office" class="text-red-500 text-sm"></span>
                        </div>

                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditEmployeeForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditEmployeeModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function openEditEmployeeModal(employee) {
        closeAllDropdowns();
        const modal = document.getElementById('editEmployeeModal');
        const form = document.getElementById('editEmployeeForm');
        
        // Set hidden ID field
        document.querySelector("input[name='id_employee']").value = employee.id ?? '';
        
        // Set form action
        form.action = `/employees/${employee.id}`;
        
        // Populate form fields (null-safe)
        document.getElementById('edit_employee_id').value = employee.employee_id ?? '';
        document.getElementById('edit_name').value = employee.name ?? '';
        document.getElementById('edit_designation').value = employee.designation ?? '';
        document.getElementById('edit_office').value = employee.office ?? '';
        
        // Clear any previous error messages
        document.getElementById('edit_error_employee_id').innerText = '';
        document.getElementById('edit_error_name').innerText = '';
        document.getElementById('edit_error_designation').innerText = '';
        document.getElementById('edit_error_office').innerText = '';
        
        // Display modal with animation
        modal.style.display = 'flex';
        setTimeout(() => {
            const box = modal.querySelector('div.hidden');
            if (box) box.classList.remove('hidden');
        }, 10);
    }

    function closeEditEmployeeModal() {
        const modal = document.getElementById('editEmployeeModal');
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

    function validateEditEmployeeForm() {
        let isValid = true;

        const idInput = document.getElementById('edit_employee_id');
        const nameInput = document.getElementById('edit_name');
        const officeInput = document.getElementById('edit_office');
        const designationInput = document.getElementById('edit_designation');

        // Clear previous messages
        document.getElementById('edit_error_employee_id').innerText = '';
        document.getElementById('edit_error_name').innerText = '';
        document.getElementById('edit_error_office').innerText = '';
        document.getElementById('edit_error_designation').innerText = '';

        // Validate Employee ID
        if (!idInput.value.trim()) {
            document.getElementById('edit_error_employee_id').innerText = 'Employee ID is required.';
            isValid = false;
        }

        // Validate Name
        if (!nameInput.value.trim()) {
            document.getElementById('edit_error_name').innerText = 'Name is required.';
            isValid = false;
        }

        // Validate Designation
        if (!designationInput.value.trim()) {
            document.getElementById('edit_error_designation').innerText = 'Designation is required.';
            isValid = false;
        }

        // Validate Office
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