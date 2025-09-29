<!-- Edit Allotment Class Modal -->
<form id="editAllotmentClassForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editAllotmentClassModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Allotment Class') }}
                    </h3>
                    <button type="button" onclick="closeEditAllotmentClassModal()" class="text-black hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Class -->
                        <div class="space-y-2">
                            <input type="hidden" name="allotment_class_id" id="allotment_class_id" value="{{ $allotment_class->id }}">
                            <x-form.label for="class" :value="__('Class')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-torah"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_class" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_class" :value="old('class')" autocomplete="off" autofocus placeholder="{{ __('Class') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="editclassError" class="text-red-500 text-sm"></span>
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
                        <!-- Category -->
                        <div class="space-y-2">
                            <x-form.label for="category" :value="__('Category')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-stream"></i>
                                </x-slot>
                                <x-form.select withicon id="edit_category" class="block w-full dark:bg-gray-800 dark:text-gray-200" name="edit_category" placeholder="{{ __('Category') }}">
                                    <option value="">{{ __('Select Category') }}</option>
                                    <option value="Current" {{ (old('category', $allotment_class->category ?? null) == 'Current') ? 'selected' : '' }}>Current</option>
                                    <option value="Continuing" {{ (old('category', $allotment_class->category ?? null) == 'Continuing') ? 'selected' : '' }}>Continuing</option>
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="editcategoryError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditAllotmentClassForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditAllotmentClassModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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
            document.getElementById('edit_class'),
            document.getElementById('edit_description'),
            document.getElementById('edit_category')
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

    function openEditAllotmentClassModal(allotmentClass) {
        closeAllDropdowns();
        document.querySelector("input[name='allotment_class_id']").value = allotmentClass.id;
        document.getElementById('editAllotmentClassForm').action = `/allotment_classes/${allotmentClass.id}`;
        document.getElementById('edit_class').value = allotmentClass.class;
        document.getElementById('edit_description').value = allotmentClass.description;
        document.getElementById('edit_category').value = allotmentClass.category;
        document.getElementById('editAllotmentClassModal').classList.remove('hidden');
        updateTextColor(); // Ensure text color updates after setting values
    }

    function closeEditAllotmentClassModal() {
        document.getElementById('editAllotmentClassModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['edit_class', 'edit_description', 'edit_category'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateTextColor);
                el.addEventListener('change', updateTextColor);
                // Initial color update
                updateTextColor();
            }
        });
    });

    function validateEditAllotmentClassForm() {
        let isValid = true;

        const editclass = document.getElementById('edit_class').value;
        const editdescription = document.getElementById('edit_description').value;
        const editcategoryField = document.getElementById('edit_category').value;

        if (!editclass) {
            document.getElementById('editclassError').innerText = 'Class is required.';
            isValid = false;
        } else {
            document.getElementById('editclassError').innerText = '';
        }

        if (!editdescription) {
            document.getElementById('editdescriptionError').innerText = 'Description is required.';
            isValid = false;
        } else {
            document.getElementById('editdescriptionError').innerText = '';
        }

        if (!editcategoryField) {
            document.getElementById('editcategoryError').innerText = 'Category is required.';
            isValid = false;
        } else {
            document.getElementById('editcategoryError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editAllotmentClassForm').submit();
        }
    }
</script>