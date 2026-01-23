<!-- Create Office Modal -->
<form id="createOfficeForm" method="POST" action="{{ route('offices.store') }}">
    @csrf
    <div id="createOfficeModal" style="display: none;" tabindex="-1" aria-labelledby="createOfficeLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="createOfficeLabel" class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-building text-blue-600 dark:text-blue-400"></i>
                    {{ __('Create Office') }}
                </h3>
                <button type="button" onclick="closeCreateOfficeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                <div class="grid gap-4">
                        <!-- Office Name -->
                        <div class="space-y-2">
                            <x-form.label
                                for="office_name"
                                :value="__('Office Name')" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-qrcode"></i>
                                </x-slot>

                                <x-form.input
                                    withicon
                                    id="office_name"
                                    class="block w-full"
                                    type="text"
                                    name="office_name"
                                    :value="old('office_name')"
                                    autofocus
                                    autocomplete="off"
                                    placeholder="{{ __('Office Name') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="officeNameError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Abbreviation and Sub Office -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Abbreviation -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="office_abbreviation"
                                    :value="__('Abbreviation')" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-signature"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="office_abbreviation"
                                        class="block w-full"
                                        type="text"
                                        name="office_abbreviation"
                                        :value="old('office_abbreviation')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Abbreviation') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="officeAbbreviationError" class="text-red-500 text-sm"></span>
                            </div>

                            <!-- Sub Office -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="sub_office"
                                    :value="__('Sub Office')" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-window-restore"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="sub_office"
                                        class="block w-full"
                                        type="text"
                                        name="sub_office"
                                        :value="old('sub_office')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Sub Office') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="subOfficeError" class="text-red-500 text-sm"></span>
                            </div>
                        </div>

                        <!-- Fund -->
                        <div class="space-y-2">
                            <x-form.label
                                for="fund"
                                :value="__('Fund')" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-money-bill"></i>
                                </x-slot>

                                <x-form.select
                                    withicon
                                    id="fund"
                                    class="block w-full"
                                    type="text"
                                    name="fund"
                                    :value="old('fund')"
                                    autocomplete="off"
                                    placeholder="{{ __('Fund') }}">
                                    <option value="">{{ __('Select Fund') }}</option>
                                    @foreach($funds as $fund)
                                    <option value="{{ $fund->fund }}">{{ $fund->fund }}</option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="fundError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- FPP Code and Responsibility Code -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- FPP Code -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="fpp_code"
                                    :value="__('FPP Code')" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-invoice"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="fpp_code"
                                        class="block w-full"
                                        type="text"
                                        name="fpp_code"
                                        :value="old('fpp_code')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('FPP Code') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="fppCodeError" class="text-red-500 text-sm"></span>
                            </div>

                            <!-- Responsibility Code -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="responsibility_code"
                                    :value="__('Responsibility Code')" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-lines"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="responsibility_code"
                                        class="block w-full"
                                        type="text"
                                        name="responsibility_code"
                                        :value="old('responsibility_code')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Responsibility Code') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="responsibilityCodeError" class="text-red-500 text-sm"></span>
                            </div>
                        </div>

                        <!-- Branch -->
                        <div class="space-y-2">
                            <x-form.label
                                for="branch"
                                :value="__('Branch')" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-sitemap"></i>
                                </x-slot>

                                <x-form.input
                                    withicon
                                    id="branch"
                                    class="block w-full"
                                    type="text"
                                    name="branch"
                                    :value="old('branch')"
                                    autofocus
                                    autocomplete="off"
                                    placeholder="{{ __('Branch') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="branchError" class="text-red-500 text-sm"></span>
                        </div>

                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateOfficeForm()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateOfficeModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    function openCreateOfficeModal() {
        closeAllDropdowns();
        const modal = document.getElementById('createOfficeModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 10);
    }

    function closeCreateOfficeModal() {
        const modal = document.getElementById('createOfficeModal');
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

    function validateCreateOfficeForm() {
        let isValid = true;

        const officeName = document.getElementById('office_name').value;
        const officeAbbreviation = document.getElementById('office_abbreviation').value;
        const subOffice = document.getElementById('sub_office').value;
        const fund = document.getElementById('fund').value;
        const branch = document.getElementById('branch').value;

        if (!officeName) {
            document.getElementById('officeNameError').innerText = 'Office Name is required.';
            isValid = false;
        } else {
            document.getElementById('officeNameError').innerText = '';
        }

        if (!officeAbbreviation) {
            document.getElementById('officeAbbreviationError').innerText = 'Abbreviation is required.';
            isValid = false;
        } else {
            document.getElementById('officeAbbreviationError').innerText = '';
        }

        if (!fund) {
            document.getElementById('fundError').innerText = 'Fund is required.';
            isValid = false;
        } else {
            document.getElementById('fundError').innerText = '';
        }

        if (!branch) {
            document.getElementById('branchError').innerText = 'Branch is required.';
            isValid = false;
        } else {
            document.getElementById('branchError').innerText = '';
        }

        if (isValid) {
            document.getElementById('createOfficeForm').submit();
        }
    }
</script>