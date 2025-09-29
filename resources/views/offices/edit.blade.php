<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Offices') }}
            </h3>

            <!-- Right: Breadcrumb Navigation -->
            @if(isset($breadcrumb))
            <nav class="text-xs text-gray-600 dark:text-gray-300" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex items-center space-x-1 rtl:space-x-reverse">
                    @foreach ($breadcrumb as $index => $item)
                    <li>
                        @if (!empty($item['route']) && $index < count($breadcrumb) - 1)
                            <a href="{{ $item['route'] }}" class="text-gray-600 hover:underline dark:text-blue-400">
                            {{ $item['label'] }}
                            </a>
                            <span class="mx-2">/</span>
                            @else
                            <span class="text-gray-500 dark:text-gray-400">{{ $item['label'] }}</span>
                            @endif
                    </li>
                    @endforeach
                </ol>
            </nav>
            @endif
        </div>
    </x-slot>

    <div class="relative mx-auto border w-full shadow-lg rounded-md bg-white max-h-full dark:bg-gray-800 dark:border-gray-700">
        <!-- Content -->
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                    {{ __('Update / Edit') }}
                </h3>

            </div>
            <!-- Body -->
            <div class="mt-2 px-7 py-3 text-xs">
                <form id="editOfficeForm" method="POST" action="{{ route('offices.update', $office) }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-6">

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
                                    :value="old('office_name', $office->office_name)"
                                    autocomplete="off"
                                    autofocus />
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
                                        :value="old('office_abbreviation', $office->office_abbreviation)"
                                        autofocus
                                        autocomplete="off" />
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
                                        :value="old('sub_office', $office->sub_office)"
                                        autofocus
                                        autocomplete="off" />
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
                                    name="fund">
                                    @foreach($funds as $fund)
                                    <option value="{{ $fund->fund }}" {{ old('fund', $office->fund) == $fund->fund ? 'selected' : ''}}>
                                        {{ $fund->fund }}
                                    </option>
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
                                        :value="old('fpp_code', $office->fpp_code)"
                                        autofocus
                                        autocomplete="off" />
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
                                        :value="old('responsibility_code', $office->responsibility_code)"
                                        autofocus
                                        autocomplete="off" />
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
                                    :value="old('branch', $office->branch)"
                                    autofocus
                                    autocomplete="off" />
                            </x-form.input-with-icon-wrapper>
                            <span id="branchError" class="text-red-500 text-sm"></span>
                        </div>

                    </div>

            </div>
            <!-- Modal footer -->
            <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="validateForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                    <i class="fas fa-save text-xl mr-2"></i>
                    {{ __('Save') }}
                </button>
                <a href="{{ route('offices.index') }}" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times text-xl mr-2"></i>
                    {{ __('Back') }}
                </a>
            </div>
            </form>
        </div>
    </div>

</x-app-layout>

<script>
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

    function validateForm() {
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
            document.getElementById('editOfficeForm').submit();
        }
    }
</script>