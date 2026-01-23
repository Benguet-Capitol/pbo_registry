<!-- Edit Allotment Class Modal -->
<form id="editAllotmentClassForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editAllotmentClassModal" style="display: none;" tabindex="-1" aria-labelledby="editAllotmentClassLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="editAllotmentClassLabel" class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-torah text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Allotment Class') }}
                </h3>
                <button type="button" onclick="closeEditAllotmentClassModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                    <div class="grid gap-4">
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
            <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="validateEditAllotmentClassForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Update') }}
                </button>
                <button type="button" onclick="closeEditAllotmentClassModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Cancel') }}
                </button>
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
        const modal = document.getElementById('editAllotmentClassModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        document.querySelector("input[name='allotment_class_id']").value = allotmentClass.id;
        document.getElementById('editAllotmentClassForm').action = `/allotment_classes/${allotmentClass.id}`;
        document.getElementById('edit_class').value = allotmentClass.class;
        document.getElementById('edit_description').value = allotmentClass.description;
        document.getElementById('edit_category').value = allotmentClass.category;
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 10);
        updateTextColor();
    }

    function closeEditAllotmentClassModal() {
        const modal = document.getElementById('editAllotmentClassModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        if (modalContent) {
            modalContent.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
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