<!-- Edit Account Code Modal -->
<form id="editAccountCodeForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editAccountCodeModal" style="display: none;" tabindex="-1" aria-labelledby="editAccountCodeLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="editAccountCodeLabel" class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-barcode text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Account Code') }}
                </h3>
                <button type="button" onclick="closeEditAccountCodeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                <div class="grid gap-4">
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
            <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="validateEditAccountCodeForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Update') }}
                </button>
                <button type="button" onclick="closeEditAccountCodeModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
        const modal = document.getElementById('editAccountCodeModal');
        const modalContent = modal.querySelector('div.hidden');
        document.querySelector("input[name='account_code_id']").value = accountCode.id;
        document.getElementById('editAccountCodeForm').action = `/account_codes/${accountCode.id}`;
        document.getElementById('edit_code').value = accountCode.code;
        document.getElementById('edit_description').value = accountCode.description;
        document.getElementById('edit_class').value = accountCode.class;
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 10);
        updateTextColor();
    }

    function closeEditAccountCodeModal() {
        const modal = document.getElementById('editAccountCodeModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        if (modalContent) {
            modalContent.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
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