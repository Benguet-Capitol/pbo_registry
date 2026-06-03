<!-- Create User Modal -->
<form id="createUserForm" method="POST" action="{{ route('users.store') }}" onsubmit="return validateCreateUserForm()">
    @csrf
    <div id="createUserModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="w-full max-w-4xl rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp max-h-[90vh] flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal content -->
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:bg-gray-700 flex flex-col h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center px-6 py-4 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 flex-shrink-0">
                    <h3 class="text-base leading-6 font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-user-plus text-blue-600 dark:text-blue-400 mr-3 text-xl"></i>
                        {{ __('Create User') }}
                    </h3>
                    <button type="button" onclick="closeCreateUserModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="overflow-y-auto flex-1 px-7 py-3">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <!-- Name -->
                            <div class="sm:col-span-3">
                                <x-form.label for="name" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Name')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-user"></i>
                                        </x-slot>
                                        <x-form.select withicon id="name" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="name" :value="old('name')" placeholder="{{ __('Name') }}" onchange="updateOffice()">
                                            <option value="">{{ __('Name') }}</option>
                                            @foreach($employees as $employee)
                                            <option value="{{ $employee->name }}" data-office="{{ $employee->office }}">{{ $employee->name }}</option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="nameError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="sm:col-span-3">
                                <x-form.label for="username" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Username')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-envelope"></i>
                                        </x-slot>
                                        <x-form.input withicon id="username" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="username" :value="old('username')" autocomplete="off" placeholder="{{ __('Username') }}" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="usernameError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>

                            <!-- User Type / Role -->
                            <div class="sm:col-span-3">
                                <x-form.label for="usertype" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Role')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-users"></i>
                                        </x-slot>
                                        <x-form.select withicon id="usertype" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="usertype" :value="old('usertype')" placeholder="{{ __('Role') }}">
                                            <option value="">{{ __('Select Role') }}</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('usertype') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="usertypeError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>

                            <!-- Office -->
                            <div class="sm:col-span-3">
                                <x-form.label for="office" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Office')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-building"></i>
                                        </x-slot>
                                        <x-form.select withicon id="office" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" name="office" :value="old('office')" placeholder="{{ __('Office') }}">
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

                            <!-- Password -->
                            <div class="sm:col-span-3">
                                <x-form.label for="password" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Password')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-lock"></i>
                                        </x-slot>
                                        <x-form.input withicon id="password" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="password" name="password" autocomplete="new-password" placeholder="{{ __('Password') }}" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="passwordError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="sm:col-span-3">
                                <x-form.label for="password_confirmation" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Confirm Password')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-lock"></i>
                                        </x-slot>
                                        <x-form.input withicon id="password_confirmation" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="password" name="password_confirmation" placeholder="{{ __('Confirm Password') }}" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="passwordConfirmationError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="if(!isSubmittingUser) validateCreateUserForm(); return false;" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateUserModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
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
    
    .animate-scaleInUp {
        animation: scaleInUp 0.3s ease-out;
    }
</style>

<script>
    let isSubmittingUser = false;

    function updateOffice() {
        const employeeSelect = document.getElementById('name');
        const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
        const office = selectedOption.getAttribute('data-office');
        const officeSelect = document.getElementById('office');

        for (let i = 0; i < officeSelect.options.length; i++) {
            if (officeSelect.options[i].value === office) {
                officeSelect.selectedIndex = i;
                break;
            }
        }
    }

    function openCreateUserModal() {
        closeAllDropdowns();
        isSubmittingUser = false;
        const modal = document.getElementById('createUserModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.querySelector('div.hidden').classList.remove('hidden');
        }, 10);
        
        // Display server-side validation errors if they exist
        @if($errors->any())
            @if($errors->has('name'))
                document.getElementById('nameError').innerText = '{{ $errors->first('name') }}';
            @endif
            @if($errors->has('username'))
                document.getElementById('usernameError').innerText = '{{ $errors->first('username') }}';
            @endif
            @if($errors->has('usertype'))
                document.getElementById('usertypeError').innerText = '{{ $errors->first('usertype') }}';
            @endif
            @if($errors->has('office'))
                document.getElementById('officeError').innerText = '{{ $errors->first('office') }}';
            @endif
            @if($errors->has('password'))
                document.getElementById('passwordError').innerText = '{{ $errors->first('password') }}';
            @endif
            @if($errors->has('password_confirmation'))
                document.getElementById('passwordConfirmationError').innerText = '{{ $errors->first('password_confirmation') }}';
            @endif
        @endif
    }

    function closeCreateUserModal() {
        isSubmittingUser = false;
        const modal = document.getElementById('createUserModal');
        const box = modal.querySelector('div.hidden, div[style*="animation"]') || modal.querySelector('> div > div');
        if (box) {
            box.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        } else {
            modal.style.display = 'none';
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const elements = document.querySelectorAll("input, select");

        elements.forEach(element => {
            updateTextColor(element);

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
        if (isSubmittingUser) return false;
        
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
            isSubmittingUser = true;
            document.getElementById('createUserForm').submit();
        }
        return false;
    }
</script>
