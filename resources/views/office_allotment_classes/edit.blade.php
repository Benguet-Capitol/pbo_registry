<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <!-- Left: Obligations Title -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Allotment Class per Office') }}
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
                <form id="editOfficeAllotmentClassForm" method="POST" action="{{ route('office_allotment_classes.update', $office_allotment_class) }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-3">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <!-- Year -->
                            <div class="sm:col-span-3">
                                <x-form.label for="year" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Year')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </x-slot>
                                        <x-form.input withicon type="number" name="year" id="year" min="" required placeholder="{{ __('Year') }}" :value="old('year', $office_allotment_class->year)" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="yearError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Fund Source -->
                            <div class="sm:col-span-3">
                                <x-form.label for="fund_source" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Fund Source')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-landmark"></i>
                                        </x-slot>
                                        <x-form.select
                                            withicon
                                            type="text"
                                            name="fund_source"
                                            id="fund_source"
                                            :value="old('fund_source')"
                                            placeholder="{{ __('Fund Source') }}"
                                            class="block w-full dark:bg-gray-800 dark:text-gray-200"
                                            onchange="fetchAllotmentClasses(this.value)">
                                            <option value="">{{ __('Select Fund Source') }}</option>
                                            @foreach($fund_sources as $fund_source)
                                            <option value="{{ $fund_source->source }}"
                                                {{ old('fund_source', $office_allotment_class->fund_source) == $fund_source->source ? 'selected' : '' }}>
                                                {{ $fund_source->source }}
                                            </option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="fundSourceError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Office -->
                            <div class="sm:col-span-3">
                                <x-form.label for="office" class="block font-medium text-gray-900 dark:text-gray-200" :value="__('Office')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-qrcode"></i>
                                        </x-slot>
                                        <x-form.select
                                            withicon
                                            type="text"
                                            name="office"
                                            id="office"
                                            :value="old('office')"
                                            placeholder="{{ __('Office') }}"
                                            class="block w-full dark:bg-gray-800 dark:text-gray-200"
                                            onchange="fetchFund(this.value)">
                                            <option value="">{{ __('Select Office') }}</option>
                                            @foreach($offices as $office)
                                            <option value="{{ $office->id }}"
                                                {{ old('office', $office_allotment_class->office) == $office->id ? 'selected' : '' }}>
                                                {{ $office->office_name }}
                                            </option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="officeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Office Abbreviation -->
                            <div class="sm:col-span-3">
                                <x-form.label for="office_abbreviation" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Office Abbreviation')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-file-fragment"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="office_abbreviation" id="office_abbreviation" :value="old('office_abbreviation', $office_allotment_class->office_abbreviation)" placeholder="{{ __('Office Abbreviation') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" disabled />
                                        <input type="hidden" name="office_abbreviation" value="{{ old('office_abbreviation', $office_allotment_class->office_abbreviation) }}" id="office_abbreviation_hidden">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Sub Office -->
                            <div class="sm:col-span-3">
                                <x-form.label for="sub_office" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Sub Office')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-window-restore"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="sub_office" id="sub_office" :value="old('sub_office', $office_allotment_class->sub_office)" placeholder="{{ __('Sub Office') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" disabled />
                                        <input type="hidden" name="sub_office" value="{{ old('sub_office', $office_allotment_class->sub_office) }}" id="sub_office_hidden">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Fund -->
                            <div class="sm:col-span-3">
                                <x-form.label for="fund" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Fund')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-money-bill"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="fund" id="fund" placeholder="{{ __('Fund') }}" :value="old('fund', $office_allotment_class->fund)" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" disabled />
                                        <input type="hidden" name="fund" value="{{ old('fund', $office_allotment_class->fund) }}" id="fund_hidden">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- FPP Code -->
                            <div class="sm:col-span-3">
                                <x-form.label for="fpp_code" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('FPP Code')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="fpp_code" id="fpp_code" placeholder="{{ __('FPP Code') }}" :value="old('fpp_code', $office_allotment_class->fpp_code)" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Responsibility Code -->
                            <div class="sm:col-span-3">
                                <x-form.label for="responsibility_code" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Responsibility Code')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-file-lines"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="responsibility_code" id="responsibility_code" :value="old('responsibility_code', $office_allotment_class->responsibility_code)" placeholder="{{ __('Responsibility Code') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" disabled />
                                        <input type="hidden" name="responsibility_code" value="{{ old('responsibility_code', $office_allotment_class->responsibility_code) }}" id="responsibility_code_hidden">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Allotment Class -->
                            <div class="sm:col-span-6">
                                <x-form.label for="allotment_class" class="block font-medium text-gray-900 dark:text-gray-200" :value="__('Allotment Class')" />
                                <div class="mt-2 mb-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-braille"></i>
                                        </x-slot>
                                        <x-form.select
                                            withicon
                                            type="text"
                                            name="allotment_class"
                                            id="allotment_class"
                                            :value="old('allotment_class')"
                                            placeholder="{{ __('Allotment Class') }}"
                                            class="block w-full dark:bg-gray-800 dark:text-gray-200">
                                            <option value="">{{ __('Select Allotment Class') }}</option>
                                            @foreach($allotment_classes as $allotment_class)
                                            <option value="{{ $allotment_class->class }}"
                                                {{ old('class', $office_allotment_class->class) == $allotment_class->class ? 'selected' : '' }}>
                                                {{ $allotment_class->description }}
                                            </option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="allotmentClassError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Modal footer -->
            <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="validateForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                    <i class="fas fa-save text-xl mr-2"></i>
                    {{ __('Save') }}
                </button>
                <a href="{{ route('office_allotment_classes.index') }}" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times text-xl mr-2"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    /* //Checks if an input has a value and adjusts the text color accordingly
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
                element.classList.remove("text-gray-400");
                element.classList.add("text-gray-900", "dark:text-gray-100");
            } else {
                element.classList.remove("text-gray-900", "dark:text-gray-100");
            }
        }
    }); */

    function fetchFundEdit(officeId) {
        if (!officeId) {
            document.getElementById('edit_fund').value = '';
            document.getElementById('fund_hidden').value = '';
            document.getElementById('fpp_code').value = '';
            document.getElementById('fpp_code_hidden').value = '';
            document.getElementById('responsibility_code').value = '';
            document.getElementById('responsibility_code_hidden').value = '';
            document.getElementById('sub_office').value = '';
            document.getElementById('sub_office_hidden').value = '';
            document.getElementById('office_abbreviation').value = '';
            document.getElementById('office_abbreviation_hidden').value = '';
            return;
        }

        fetch(`/get-fund/${officeId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('fund').value = data.fund;
                document.getElementById('fund_hidden').value = data.fund;
                document.getElementById('fpp_code').value = data.fpp_code;
                document.getElementById('fpp_code_hidden').value = data.fpp_code;
                document.getElementById('responsibility_code').value = data.responsibility_code;
                document.getElementById('responsibility_code_hidden').value = data.responsibility_code;
                document.getElementById('sub_office').value = data.sub_office;
                document.getElementById('sub_office_hidden').value = data.sub_office;
                document.getElementById('office_abbreviation').value = data.office_abbreviation;
                document.getElementById('office_abbreviation_hidden').value = data.office_abbreviation;
            })
            .catch(error => console.error('Error fetching fund:', error));
    }

    function fetchAllotmentClasses(fundSource) {
        if (fundSource === 'Continuing Capital Outlay') {
            fetch('/get-continuing-allotment-classes')
                .then(response => response.json())
                .then(data => {
                    const allotmentClassSelect = document.getElementById('allotment_class');
                    allotmentClassSelect.innerHTML = '<option value="">{{ __('
                    Select Allotment Class ') }}</option>';
                    data.forEach(classItem => {
                        const option = document.createElement('option');
                        option.value = classItem.class;
                        option.textContent = classItem.description;
                        allotmentClassSelect.appendChild(option);
                    });

                    // Auto-select the current value if it matches
                    const currentClass = '{{ old('
                    allotment_class ', $office_allotment_class->class) }}';
                    allotmentClassSelect.value = currentClass;
                })
                .catch(error => console.error('Error fetching allotment classes:', error));
        } else {
            // Reset to default allotment classes if fund source is not "Continuing Capital Outlay"
            const defaultAllotmentClasses = @json($allotment_classes);
            const allotmentClassSelect = document.getElementById('allotment_class');
            allotmentClassSelect.innerHTML = '<option value="">{{ __('
            Select Allotment Class ') }}</option>';
            defaultAllotmentClasses.forEach(classItem => {
                const option = document.createElement('option');
                option.value = classItem.class;
                option.textContent = classItem.description;
                allotmentClassSelect.appendChild(option);
            });

            // Auto-select the current value if it matches
            const currentClass = '{{ old('
            allotment_class ', $office_allotment_class->class) }}';
            allotmentClassSelect.value = currentClass;
        }
    }

    // Call fetchAllotmentClasses on page load if the old value of fund_source is "Continuing Capital Outlay"
    document.addEventListener('DOMContentLoaded', function() {
        const fundSource = document.getElementById('fund_source').value;
        if (fundSource === 'Continuing Capital Outlay') {
            fetchAllotmentClasses(fundSource);
        }
    });

    function validateForm() {
        let isValid = true;

        const year = document.getElementById('year').value;
        const fundSource = document.getElementById('fund_source').value;
        const office = document.getElementById('office').value;
        const allotmentClass = document.getElementById('allotment_class').value;

        if (!year) {
            document.getElementById('yearError').innerText = 'Year is required.';
            isValid = false;
        } else {
            document.getElementById('yearError').innerText = '';
        }

        if (!fundSource) {
            document.getElementById('fundSourceError').innerText = 'Fund Source is required.';
            isValid = false;
        } else {
            document.getElementById('fundSourceError').innerText = '';
        }

        if (!office) {
            document.getElementById('officeError').innerText = 'Office is required.';
            isValid = false;
        } else {
            document.getElementById('officeError').innerText = '';
        }

        if (!allotmentClass) {
            document.getElementById('allotmentClassError').innerText = 'Allotment Class is required.';
            isValid = false;
        } else {
            document.getElementById('allotmentClassError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editOfficeAllotmentClassForm').submit();
        }
    }
</script>