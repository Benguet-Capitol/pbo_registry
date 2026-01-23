<!-- Create Account Code Modal -->
<form id="createAccountCodeForm" method="POST" action="{{ route('account_codes.store') }}">
    @csrf
    <div id="createAccountCodeModal" style="display: none;" tabindex="-1" aria-labelledby="createAccountCodeLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="createAccountCodeLabel" class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-barcode text-blue-600 dark:text-blue-400"></i>
                    {{ __('Create Account Code') }}
                </h3>
                <button type="button" onclick="closeCreateAccountCodeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                <div class="grid gap-4">
                    <!-- Code -->
                    <div class="space-y-2">
                        <x-form.label for="code" :value="__('Code')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-stream"></i>
                            </x-slot>
                            <x-form.input withicon id="code" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="code" :value="old('code')" autocomplete="off" autofocus placeholder="{{ __('Code') }}" />
                        </x-form.input-with-icon-wrapper>
                        <span id="codeError" class="text-red-500 text-sm"></span>
                    </div>
                    <!-- Description -->
                    <div class="space-y-2">
                        <x-form.label for="description" :value="__('Description')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-info-circle"></i>
                            </x-slot>
                            <x-form.input withicon id="description" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="description" autocomplete="off" :value="old('description')" autofocus placeholder="{{ __('Description') }}" />
                        </x-form.input-with-icon-wrapper>
                        <span id="descriptionError" class="text-red-500 text-sm"></span>
                    </div>
                    <!-- Class -->
                    <div class="space-y-2">
                        <x-form.label for="class" :value="__('Class')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-braille"></i>
                            </x-slot>
                            <x-form.select withicon id="class" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="class" :value="old('class')" placeholder="{{ __('Allotment Class') }}">
                                <option value="">{{ __('Select Allotment Class') }}</option>
                                @foreach($allotment_classes as $allotment_class)
                                <option value="{{ $allotment_class->class }}">{{ $allotment_class->description }}</option>
                                @endforeach
                            </x-form.select>
                        </x-form.input-with-icon-wrapper>
                        <span id="classError" class="text-red-500 text-sm"></span>
                    </div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="validateCreateAccountCodeForm()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Save') }}
                </button>
                <button type="button" onclick="closeCreateAccountCodeModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    function openCreateAccountCodeModal() {
        closeAllDropdowns();
        const modal = document.getElementById('createAccountCodeModal');
        const modalContent = modal.querySelector('div.hidden');
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 10);
    }

    function closeCreateAccountCodeModal() {
        const modal = document.getElementById('createAccountCodeModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        if (modalContent) {
            modalContent.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
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

    function validateCreateAccountCodeForm() {
        let isValid = true;

        const code = document.getElementById('code').value;
        const description = document.getElementById('description').value;
        const classField = document.getElementById('class').value;

        if (!code) {
            document.getElementById('codeError').innerText = 'Code is required.';
            isValid = false;
        } else {
            document.getElementById('codeError').innerText = '';
        }

        if (!description) {
            document.getElementById('descriptionError').innerText = 'Description is required.';
            isValid = false;
        } else {
            document.getElementById('descriptionError').innerText = '';
        }

        if (!classField) {
            document.getElementById('classError').innerText = 'Class is required.';
            isValid = false;
        } else {
            document.getElementById('classError').innerText = '';
        }

        if (isValid) {
            document.getElementById('createAccountCodeForm').submit();
        }
    }
</script>