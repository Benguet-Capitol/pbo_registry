<!-- Edit User Modal -->
<form id="editUserForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editUserModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white max-h-full dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit User') }}
                    </h3>
                    <button onclick="closeEditUserModal()" class="text-black hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Name -->
                        <div class="space-y-2">
                            <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">
                            <x-form.label for="name" :value="__('Name')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-user"></i></x-slot>
                                <x-form.select withicon id="edit_name" class="block w-full" name="edit_name" autocomplete="off">
                                @foreach($employees as $employee)
                                <option value="{{ $employee->name }}" {{ old('name', $user->name) == $employee->name ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                                @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="nameError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Username -->
                        <div class="space-y-2">
                            <x-form.label for="username" :value="__('Username')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-envelope"></i></x-slot>
                                <x-form.input withicon id="edit_username" class="block w-full" type="text" name="edit_username" autocomplete="off" placeholder="{{ __('Username') }}" :value="old('username', $user->username)" />
                            </x-form.input-with-icon-wrapper>
                            <span id="username_Error" class="text-red-500 text-sm error-message"></span>
                        </div>

                        <!-- User Type / Role -->
                        <div class="space-y-2">
                            <x-form.label for="usertype" :value="__('Role')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-users"></i></x-slot>
                                <x-form.select withicon id="edit_usertype" class="block w-full" name="edit_usertype" autocomplete="off">
                                    <option value="">{{ __('Select Role') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('usertype', $user->usertype) == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="usertypeError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Office -->
                        <div class="space-y-2">
                            <x-form.label for="office" :value="__('Office')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-qrcode"></i></x-slot>
                                <x-form.select withicon id="edit_office" class="block w-full" name="edit_office" autocomplete="off">
                                <option value="PBO" {{ old('office', $user->office) == 'PBO' ? 'selected' : '' }}>Provincial Budget Office</option>
                                <option value="PAccO" {{ old('office', $user->office) == 'PAccO' ? 'selected' : '' }}>Provincial Accounting Office</option>
                                <option value="PGO" {{ old('office', $user->office) == 'PGO' ? 'selected' : '' }}>Provincial Governor's Office</option>
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="officeError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditUserForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditUserModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function updateTextColor() {
        const fields = [
            document.getElementById('edit_name'),
            document.getElementById('edit_username'),
            document.getElementById('edit_usertype'),
            document.getElementById('edit_office')
        ];
        fields.forEach(field => {
            if (!field) return;
            if (field.value && field.value.trim() !== '') {
                field.classList.remove('text-gray-400');
                field.classList.add('text-gray-900');
            } else {
                field.classList.remove('text-gray-900');
                field.classList.add('text-gray-400');
            }
        });
    }

    function openEditUserModal(user) {
        closeAllDropdowns();

        document.querySelector("input[name='user_id']").value = user.id;
        document.getElementById('editUserForm').action = '/users/' + user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_usertype').value = user.usertype;
        document.getElementById('edit_office').value = user.office;
        document.getElementById('editUserModal').classList.remove('hidden');
        updateTextColor(); // Ensure text color updates after setting values
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    function validateEditUserForm() {
        let isValid = true;
        const name = document.getElementById('edit_name');
        const username = document.getElementById('edit_username');
        const usertype = document.getElementById('edit_usertype');
        const office = document.getElementById('edit_office');
        // Clear previous error messages
        document.getElementById('nameError').innerText = '';
        document.getElementById('username_Error').innerText = '';
        document.getElementById('usertypeError').innerText = '';
        document.getElementById('officeError').innerText = '';
        // Validate Name
        if (!name.value) {
            document.getElementById('nameError').innerText = 'Name is required.';
            isValid = false;
        }
        // Validate Username
        if (!username.value.trim()) {
            document.getElementById('username_Error').innerText = 'Username is required.';
            isValid = false;
        }
        // Validate User Type
        if (!usertype.value) {
            document.getElementById('usertypeError').innerText = 'User Type is required.';
            isValid = false;
        } else {
            document.getElementById('usertypeError').innerText = '';
        }
        // Validate Office
        if (!office.value) {
            document.getElementById('officeError').innerText = 'Office is required.';
            isValid = false;
        } else {
            document.getElementById('officeError').innerText = '';
        }
        if (isValid) {
            document.getElementById('editUserForm').submit();
        }
    }

    // Attach updateTextColor to input/change events for all relevant fields
    document.addEventListener('DOMContentLoaded', function() {
        ['edit_name', 'edit_username', 'edit_usertype', 'edit_office'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateTextColor);
                el.addEventListener('change', updateTextColor);
                // Initial color update
                updateTextColor();
            }
        });
    });
</script>