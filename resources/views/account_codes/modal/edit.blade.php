<!-- Edit Account Code Modal -->
<form id="editAccountCodeForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editAccountCodeModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Account') }}
                    </h3>
                    <button type="button" onclick="closeEditAccountCodeModal()" class="text-black hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Code -->
                        <div class="space-y-2">
                            <input type="hidden" name="account_code_id" id="account_code_id" value="{{ $account_code->id }}">
                            <x-form.label for="code" :value="__('Code')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-stream"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_code" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_code" :value="old('code')" autocomplete="off" autofocus placeholder="{{ __('Code') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="editcodeError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Description -->
                        <div class="space-y-2">
                            <x-form.label for="description" :value="__('Description')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-info-circle"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_description" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_description" autocomplete="off" :value="old('description')" autofocus placeholder="{{ __('Description') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="editdescriptionError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Class -->
                        <div class="space-y-2">
                            <x-form.label for="class" :value="__('Class')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-braille"></i>
                                </x-slot>
                                <x-form.select withicon id="edit_class" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_class" :value="old('class')" placeholder="{{ __('Allotment Class') }}">
                                    <option value="">{{ __('Select Allotment Class') }}</option>
                                    @foreach($allotment_classes as $allotment_class)
                                    <option value="{{ $allotment_class->class }}">{{ $allotment_class->description }}</option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="editclassError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditAccountCodeForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditAccountCodeModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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
            document.getElementById('edit_code'),
            document.getElementById('edit_description'),
            document.getElementById('edit_class')
        ];
        fields.forEach(field => {
            if (!field) return;
            if (field.value && field.value.trim() !== '') {
                field.classList.remove('text-gray-500');
                field.classList.add('text-gray-900', 'dark:text-gray-100');
            } else {
                field.classList.remove('text-gray-900', 'dark:text-gray-100');
                field.classList.add('text-gray-500');
            }
        });
    }

    function openEditAccountCodeModal(accountCode) {
        closeAllDropdowns();
        document.querySelector("input[name='account_code_id']").value = accountCode.id;
        document.getElementById('editAccountCodeForm').action = `/account_codes/${accountCode.id}`;
        document.getElementById('edit_code').value = accountCode.code;
        document.getElementById('edit_description').value = accountCode.description;
        document.getElementById('edit_class').value = accountCode.class;
        document.getElementById('editAccountCodeModal').classList.remove('hidden');
        updateTextColor(); // Ensure text color updates after setting values
    }

    function closeEditAccountCodeModal() {
        document.getElementById('editAccountCodeModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        var ids = ['edit_code', 'edit_description', 'edit_class'];
        for (var i = 0; i < ids.length; i++) {
            var el = document.getElementById(ids[i]);
            if (el) {
                el.addEventListener('input', updateTextColor);
                el.addEventListener('change', updateTextColor);
                // Initial color update
                updateTextColor();
            }
        }
    });

    function validateEditAccountCodeForm() {
        let isValid = true;

        const editcode = document.getElementById('edit_code').value;
        const editdescription = document.getElementById('edit_description').value;
        const editclassField = document.getElementById('edit_class').value;

        if (!editcode) {
            document.getElementById('editcodeError').innerText = 'Code is required.';
            isValid = false;
        } else {
            document.getElementById('editcodeError').innerText = '';
        }

        if (!editdescription) {
            document.getElementById('editdescriptionError').innerText = 'Description is required.';
            isValid = false;
        } else {
            document.getElementById('editdescriptionError').innerText = '';
        }

        if (!editclassField) {
            document.getElementById('editclassError').innerText = 'Class is required.';
            isValid = false;
        } else {
            document.getElementById('editclassError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editAccountCodeForm').submit();
        }
    }
</script>