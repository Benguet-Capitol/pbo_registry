<!-- Create User Modal -->
<form id="createUserForm" method="POST" action="{{ route('users.store') }}" onsubmit="return validateCreateUSerForm()">
    @csrf
    <div id="createUserModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Create User') }}
                    </h3>
                    <button type="button" onclick="closeCreateUserModal()" class="text-black hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Name -->
                        <div class="space-y-2">
                            <x-form.label for="name" :value="__('Name')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-user"></i>
                                </x-slot>
                                <x-form.select withicon id="name" class="block w-full" type="text" name="name" :value="old('name')" placeholder="{{ __('Name') }}" onchange="updateOffice()">
                                    <option value="">{{ __('Name') }}</option>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->name }}" data-office="{{ $employee->office }}">{{ $employee->name }}</option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="nameError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Username -->
                        <div class="space-y-2">
                            <x-form.label for="username" :value="__('Username')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-envelope"></i>
                                </x-slot>
                                <x-form.input withicon id="username" class="block w-full" type="text" name="username" :value="old('username')" autocomplete="off" placeholder="{{ __('Username') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="usernameError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- User Type / Role and Office -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- User Type / Role -->
                            <div class="space-y-2">
                                <x-form.label for="usertype" :value="__('Role')" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-users"></i>
                                    </x-slot>
                                    <x-form.select withicon id="usertype" class="block w-full" type="text" name="usertype" :value="old('usertype')" placeholder="{{ __('Role') }}">
                                        <option value="">{{ __('Select Role') }}</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ old('usertype') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </x-form.select>
                                </x-form.input-with-icon-wrapper>
                                <span id="usertypeError" class="text-red-500 text-sm"></span>
                            </div>

                            <!-- Office -->
                            <div class="space-y-2">
                                <x-form.label for="office" :value="__('Office')" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-qrcode"></i>
                                    </x-slot>
                                    <x-form.select withicon id="office" class="block w-full" name="office" :value="old('office')" placeholder="{{ __('Office') }}">
                                        <option value="">{{ __('Select Office') }}</option>
                                        <option value="PBO">{{ __('Provincial Budget Office') }}</option>
                                        <option value="PAccO">{{ __('Provincial Accounting Office') }}</option>
                                        <option value="PGO">{{ __('Provincial Governors Office') }}</option>
                                    </x-form.select>
                                </x-form.input-with-icon-wrapper>
                                <span id="officeError" class="text-red-500 text-sm"></span>
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <x-form.label for="password" :value="__('Password')" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-lock"></i>
                                    </x-slot>
                                    <x-form.input withicon id="password" class="block w-full" type="password" name="password" autocomplete="new-password" placeholder="{{ __('Password') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="passwordError" class="text-red-500 text-sm"></span>
                            </div>

                            <!-- Confirm Password -->
                            <div class="space-y-2">
                                <x-form.label for="password_confirmation" :value="__('Confirm Password')" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-lock"></i>
                                    </x-slot>
                                    <x-form.input withicon id="password_confirmation" class="block w-full" type="password" name="password_confirmation" placeholder="{{ __('Confirm Password') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="passwordConfirmationError" class="text-red-500 text-sm"></span>
                            </div>
                        </div>
                    </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="justify-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        <button type="button" onclick="validateCreateUserForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                            <i class="fas fa-save text-xl mr-2"></i>
                            {{ __('Save') }}
                        </button>
                        <button type="button" onclick="closeCreateUserModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                            <i class="fas fa-times text-xl mr-2"></i>
                            {{ __('Cancel') }}
                        </button>
                    </div>
            </div>
        </div>
    </div>
</form>

<script>
    function updateOffice() {
        const employeeSelect = document.getElementById('name');
        const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
        const office = selectedOption.getAttribute('data-office');
        const officeSelect = document.getElementById('office');

        // Loop through the office select options and set the matching option as selected
        for (let i = 0; i < officeSelect.options.length; i++) {
            if (officeSelect.options[i].value === office) {
                officeSelect.selectedIndex = i;
                break;
            }
        }
    }

    function openCreateUserModal() {
        closeAllDropdowns();
        document.getElementById('createUserModal').classList.remove('hidden');
    }

    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
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

    function validateCreateUserForm() {
        let isValid = true;

        const name = document.getElementById('name').value;
        const username = document.getElementById('username').value;
        const usertype = document.getElementById('usertype').value;
        const office = document.getElementById('office').value;
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        if (!name) {
            document.getElementById('nameError').innerText = 'Name is required.';
            isValid = false;
        } else {
            document.getElementById('nameError').innerText = '';
        }

        if (!username) {
            document.getElementById('usernameError').innerText = 'Username is required.';
            isValid = false;
        } else {
            document.getElementById('usernameError').innerText = '';
        }

        if (!usertype) {
            document.getElementById('usertypeError').innerText = 'User Type is required.';
            isValid = false;
        } else {
            document.getElementById('usertypeError').innerText = '';
        }

        if (!office) {
            document.getElementById('officeError').innerText = 'Office is required.';
            isValid = false;
        } else {
            document.getElementById('officeError').innerText = '';
        }

        if (!password) {
            document.getElementById('passwordError').innerText = 'Password is required.';
            isValid = false;
        } else {
            document.getElementById('passwordError').innerText = '';
        }

        if (password !== passwordConfirmation) {
            document.getElementById('passwordConfirmationError').innerText = 'Passwords do not match.';
            isValid = false;
        } else {
            document.getElementById('passwordConfirmationError').innerText = '';
        }

        if (isValid) {
            document.getElementById('createUserForm').submit();
        }
    }
</script>