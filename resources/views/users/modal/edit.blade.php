<!-- Edit User Modal -->
<form id="editUserForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editUserModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="w-full max-w-4xl rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp max-h-[90vh] flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal content -->
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:bg-gray-700 flex flex-col h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-6 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-700 flex-shrink-0">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-user-edit text-amber-600 dark:text-amber-400 mr-3 text-xl"></i>
                        {{ __('Edit User') }}
                    </h3>
                    <button type="button" onclick="closeEditUserModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="overflow-y-auto flex-1 px-7 py-3">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <!-- Name -->
                            <div class="sm:col-span-3">
                                <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">
                                <x-form.label for="edit_name" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Name')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-user"></i>
                                        </x-slot>
                                        <x-form.select withicon id="edit_name" class="block w-full dark:bg-gray-800 dark:text-gray-200" name="edit_name" autocomplete="off">
                                            @foreach($employees as $employee)
                                            <option value="{{ $employee->name }}" {{ old('name', $user->name) == $employee->name ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="nameError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_username" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Username')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-envelope"></i>
                                        </x-slot>
                                        <x-form.input withicon id="edit_username" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_username" autocomplete="off" placeholder="{{ __('Username') }}" :value="old('username', $user->username)" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="username_Error" class="text-red-500 text-sm error-message"></span>
                                </div>
                            </div>

                            <!-- User Type / Role -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_usertype" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Role')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-users"></i>
                                        </x-slot>
                                        <x-form.select withicon id="edit_usertype" class="block w-full dark:bg-gray-800 dark:text-gray-200" name="edit_usertype" autocomplete="off">
                                            <option value="">{{ __('Select Role') }}</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('usertype', $user->usertype) == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="usertypeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                            <!-- Office -->
                            <div class="sm:col-span-3">
                                <x-form.label for="edit_office" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Office')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-building"></i>
                                        </x-slot>
                                        <x-form.select withicon id="edit_office" class="block w-full dark:bg-gray-800 dark:text-gray-200" name="edit_office" placeholder="{{ __('Office') }}">
                                            <option value="">{{ __('Select Office') }}</option>
                                            @foreach($offices as $office)
                                                <option value="{{ $office->id }}">
                                                    {{ $office->office_abbreviation }} - {{ $office->office_name }}
                                                </option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="editOfficeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditUserForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditUserModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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

        document.querySelector("input[name='user_id']").value = user.id ?? '';
        document.getElementById('editUserForm').action = '/users/' + user.id;
        document.getElementById('edit_name').value = user.name ?? '';
        document.getElementById('edit_username').value = user.username ?? '';
        document.getElementById('edit_usertype').value = user.usertype ?? '';
        document.getElementById('edit_office').value = user.office ?? '';
        
        const modal = document.getElementById('editUserModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            const box = modal.querySelector('div.hidden');
            if (box) box.classList.remove('hidden');
        }, 10);
        updateTextColor();
    }

    function closeEditUserModal() {
        const modal = document.getElementById('editUserModal');
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