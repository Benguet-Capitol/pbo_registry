<!-- Create Employee Modal -->
<form id="createEmployeeForm" method="POST" action="{{ route('employees.store') }}">
    @csrf
    <div id="createEmployeeModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="w-full max-w-4xl rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp max-h-[90vh] flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal content -->
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:bg-gray-700 flex flex-col h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-6 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 flex-shrink-0">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white flex items-center">
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
                            <x-form.label
                                for="employee_id"
                                :value="__('Employee ID')" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-id-card"></i>
                                </x-slot>

                                <x-form.input
                                    withicon
                                    id="employee_id"
                                    class="block w-full"
                                    type="text"
                                    name="employee_id"
                                    :value="old('employee_id')"
                                    autofocus
                                    placeholder="{{ __('Employee ID') }}"
                                    autocomplete="off" />
                            </x-form.input-with-icon-wrapper>
                            <span id="employeeIdError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Name -->
                        <div class="space-y-2">
                            <x-form.label
                                for="name"
                                :value="__('Name')" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-user"></i>
                                </x-slot>

                                <x-form.input
                                    withicon
                                    id="name"
                                    class="block w-full"
                                    type="text"
                                    name="name"
                                    :value="old('name')"
                                    autofocus
                                    placeholder="{{ __('Name') }}"
                                    autocomplete="off" />
                            </x-form.input-with-icon-wrapper>
                            <span id="nameError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Designation -->
                        <div class="space-y-2">
                            <x-form.label
                                for="designation"
                                :value="__('Designation')" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-briefcase"></i>
                                </x-slot>

                                <x-form.input
                                    withicon
                                    id="designation"
                                    class="block w-full"
                                    type="text"
                                    name="designation"
                                    :value="old('designation')"
                                    autofocus
                                    placeholder="{{ __('Designation') }}"
                                    autocomplete="off" />
                            </x-form.input-with-icon-wrapper>
                            <span id="designationError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Office -->
                            <div class="space-y-2">
                                <x-form.label for="office" :value="__('Office')" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-building"></i>
                                    </x-slot>
                                    <x-form.select withicon id="office" class="block w-full" name="office" :value="old('office')" placeholder="{{ __('Office') }}">
                                        <option value="">{{ __('Select Office') }}</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}" {{ old('office') == $office->id ? 'selected' : '' }}>
                                                {{ $office->office_abbreviation }} - {{ $office->office_name }}
                                            </option>
                                        @endforeach
                                    </x-form.select>
                                </x-form.input-with-icon-wrapper>
                                <span id="officeError" class="text-red-500 text-sm"></span>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateEmployeeForm()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateEmployeeModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</form>

<script>
    function openCreateEmployeeModal() {
        closeAllDropdowns();
        const modal = document.getElementById('createEmployeeModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            const box = modal.querySelector('div.hidden');
            if (box) box.classList.remove('hidden');
        }, 10);
    }

    function closeCreateEmployeeModal() {
        const modal = document.getElementById('createEmployeeModal');
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

    function validateCreateEmployeeForm() {
        let isValid = true;

        const employeeId = document.getElementById('employee_id').value;
        const name = document.getElementById('name').value;
        const office = document.getElementById('office').value;
        const designation = document.getElementById('designation').value;

        if (!employeeId) {
            document.getElementById('employeeIdError').innerText = 'Employee ID is required.';
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
            document.getElementById('createEmployeeForm').submit();
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