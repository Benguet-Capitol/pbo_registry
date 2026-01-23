<!-- Create Fund Modal -->
<form id="createFundForm" method="POST" action="{{ route('funds.store') }}">
    @csrf
    <div id="createFundModal" style="display: none;" tabindex="-1" aria-labelledby="createFundLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="createFundLabel" class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-star-of-david text-blue-600 dark:text-blue-400"></i>
                    {{ __('Create Fund') }}
                </h3>
                <button type="button" onclick="closeCreateFundModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                    <div class="grid gap-4">
                        <!-- Fund -->
                        <div class="space-y-2">
                            <x-form.label for="fund" :value="__('Fund')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-star-of-david"></i>
                                </x-slot>
                                <x-form.input withicon id="fund" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="fund" :value="old('fund')" autocomplete="off" autofocus placeholder="{{ __('Fund') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="fundError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Fund Type -->
                        <div class="space-y-2">
                            <x-form.label for="fund_type" :value="__('Fund Type')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-spinner"></i>
                                </x-slot>
                                <x-form.select withicon id="fund_type" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="fund_type" :value="old('fund_type')" placeholder="{{ __('Fund Type') }}">
                                    <option value="">{{ __('Select Fund Type') }}</option>
                                    <option value="General Fund">General Fund</option>
                                    <option value="Benguet General Hospital Economic Enterprise">Benguet General Hospital Economic Enterprise</option>
                                    <option value="Special Education Fund">Special Education Fund</option>
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="fund_typeError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Fund Code -->
                        <div class="space-y-2">
                            <x-form.label for="fund_code" :value="__('Fund Code')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-sticky-note"></i>
                                </x-slot>
                                <x-form.input withicon id="fund_code" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="fund_code" autocomplete="off" :value="old('fund_code')" autofocus placeholder="{{ __('Fund Code') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="fund_codeError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateFundForm()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateFundModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    function openCreateFundModal() {
        closeAllDropdowns();
        const modal = document.getElementById('createFundModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 10);
    }

    function closeCreateFundModal() {
        const modal = document.getElementById('createFundModal');
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

    function validateCreateFundForm() {
        let isValid = true;

        const fund = document.getElementById('fund').value;
        const fund_type = document.getElementById('fund_type').value;
        const fund_code = document.getElementById('fund_code').value;

        if (!fund) {
            document.getElementById('fundError').innerText = 'Fund is required.';
            isValid = false;
        } else {
            document.getElementById('fundError').innerText = '';
        }

        if (!fund_type) {
            document.getElementById('fund_typeError').innerText = 'Fund Type is required.';
            isValid = false;
        } else {
            document.getElementById('fund_typeError').innerText = '';
        }

        if (!fund_code) {
            document.getElementById('fund_codeError').innerText = 'Fund Code is required.';
            isValid = false;
        } else {
            document.getElementById('fund_codeError').innerText = '';
        }

        if (isValid) {
            document.getElementById('createFundForm').submit();
        }
    }
</script>