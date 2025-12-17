<!-- Create Employee Modal -->
<form id="createEmployeeForm" method="POST" action="{{ route('employees.store') }}">
    <div id="createEmployeeModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white max-h-full dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Add Employee') }}
                    </h3>
                    <button type="button" onclick="closeCreateEmployeeModal()" class="text-black hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    @csrf
                    <div class="grid gap-3">
                        <!-- Employee ID -->
                        <div class="space-y-2">
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
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateEmployeeForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateEmployeeModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
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
        document.getElementById('createEmployeeModal').classList.remove('hidden');
    }

    function closeCreateEmployeeModal() {
        document.getElementById('createEmployeeModal').classList.add('hidden');
    }

    //Checks if an input has a value and adjusts the text color accordingly
    document.addEventListener("DOMContentLoaded", function() {
        const elements = document.querySelectorAll("input, select");

        elements.forEach(element => {
            updateTextColor(element); // Check initial values

            element.addEventListener("input", function() {
                updateTextColor(this);
            });

            element.addEventListener("change", function() {
                updateTextColor(this);
            });

            element.addEventListener("focus", function() {
                updateTextColor(this);
            });
        });

        // Handle autofill values after a short delay
        setTimeout(() => {
            elements.forEach(updateTextColor);
        }, 100);

        function updateTextColor(element) {
            if (element.value.trim() !== "") {
                element.classList.remove("text-gray-500");
                element.classList.add("text-gray-900", "dark:text-gray-100");
            } else {
                element.classList.remove("text-gray-900", "dark:text-gray-100");
            }
        }
    });

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