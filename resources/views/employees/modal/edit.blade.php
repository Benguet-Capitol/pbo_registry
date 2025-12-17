<!-- Edit Employee Modal -->
<form id="editEmployeeForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editEmployeeModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white max-h-full dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Employee') }}
                    </h3>
                    <button type="button" onclick="closeEditEmployeeModal()" class="text-black hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Employee ID -->
                        <div class="space-y-2">
                            <input type="hidden" name="id_employee" id="id_employee" value="{{ $employee->id }}">
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
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditEmployeeForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditEmployeeModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function updateTextColor(input) {
        if (input.value.trim() !== "") {
            input.classList.remove("text-gray-500");
            input.classList.add("text-gray-900", "dark:text-gray-100");
        } else {
            input.classList.remove("text-gray-900", "dark:text-gray-100");
            input.classList.add("text-gray-500");
        }
    }

    function openEditEmployeeModal(employee) {
        closeAllDropdowns();
        document.querySelector("input[name='id_employee']").value = employee.id;
        document.getElementById('editEmployeeForm').action = `/employees/${employee.id}`;
        document.getElementById('edit_employee_id').value = employee.employee_id;
        updateTextColor(document.getElementById('edit_employee_id'));
        document.getElementById('edit_name').value = employee.name;
        updateTextColor(document.getElementById('edit_name'));
        document.getElementById('edit_designation').value = employee.designation;
        updateTextColor(document.getElementById('edit_designation'));
        document.getElementById('edit_office').value = employee.office;
        updateTextColor(document.getElementById('edit_office'));
        document.getElementById('editEmployeeModal').classList.remove('hidden');
    }

    function closeEditEmployeeModal() {
        document.getElementById('editEmployeeModal').classList.add('hidden');
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

    document.addEventListener("DOMContentLoaded", function() {
        const inputs = document.querySelectorAll("input, select");
        inputs.forEach(input => {
            updateTextColor(input); // Check initial values
            input.addEventListener("input", function() { updateTextColor(this); });
            input.addEventListener("change", function() { updateTextColor(this); });
        });
    });
</script>