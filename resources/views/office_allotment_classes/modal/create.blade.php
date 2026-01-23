<!-- Create Office Allotment Class Modal -->
<form id="createOfficeAllotmentClassForm" method="POST" action="{{ route('office_allotment_classes.store') }}">
    @csrf
    <div id="createOfficeAllotmentClassModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="w-full max-w-4xl rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp max-h-[90vh] flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal content -->
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:bg-gray-700 flex flex-col h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-6 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 flex-shrink-0">
                    <h3 class="text-lg leading-6 font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-plus-circle text-blue-600 dark:text-blue-400 mr-3 text-xl"></i>
                        {{ __('Create Allotment Class per Office') }}
                    </h3>
                    <button type="button" onclick="closeCreateOfficeAllotmentClassModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="overflow-y-auto flex-1 px-7 py-3">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <!-- Year -->
                            <div class="sm:col-span-3">
                                <x-form.label for="year" class="block text-xs/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Year')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </x-slot>
                                        <x-form.input withicon type="number" name="year" id="year" min="" required placeholder="{{ __('Year') }}" :value="old('year')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
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
                                            <option value="{{ $fund_source->source }}">{{ $fund_source->source }}</option>
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
                                            <option value="{{ $office->id }}">{{ $office->office_name }}</option>
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
                                        <x-form.input withicon type="text" name="office_abbreviation" id="office_abbreviation" placeholder="{{ __('Office Abbreviation') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" disabled />
                                        <input type="hidden" name="office_abbreviation" id="office_abbreviation_hidden">
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
                                        <x-form.input withicon type="text" name="sub_office" id="sub_office" placeholder="{{ __('Sub Office') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" disabled />
                                        <input type="hidden" name="sub_office" id="sub_office_hidden">
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
                                        <x-form.input withicon type="text" name="fund" id="fund" placeholder="{{ __('Fund') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" disabled />
                                        <input type="hidden" name="fund" id="fund_hidden">
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
                                        <x-form.input withicon type="text" name="fpp_code" id="fpp_code" placeholder="{{ __('FPP Code') }}" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="fpp_code" id="fpp_code_hidden">
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
                                        <x-form.input withicon type="text" name="responsibility_code" id="responsibility_code" placeholder="{{ __('Responsibility Code') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" disabled />
                                        <input type="hidden" name="responsibility_code" id="responsibility_code_hidden">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>

                            <!-- Allotment Class -->
                            <div class="sm:col-span-6">
                                <x-form.label for="allotment_class" class="block font-medium text-gray-900 dark:text-gray-200" :value="__('Allotment Class')" />
                                <div class="mt-2">
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
                                            <option value="{{ $allotment_class->class }}">{{ $allotment_class->description }}</option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="allotmentClassError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateOfficeAllotmentClassForm()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateOfficeAllotmentClassModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-3 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    function fetchFund(officeId) {
        if (!officeId) {
            document.getElementById('fund').value = '';
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
        const allotmentClassSelect = document.getElementById('allotment_class');

        if (fundSource === 'Continuing Capital Outlay') {
            fetch('/get-continuing-allotment-classes')
                .then(response => response.json())
                .then(data => {
                    allotmentClassSelect.innerHTML = ''; // Remove placeholder option

                    data.forEach((classItem, index) => {
                        const option = document.createElement('option');
                        option.value = classItem.class;
                        option.textContent = classItem.description;
                        allotmentClassSelect.appendChild(option);

                        // Automatically select the first option
                        if (index === 0) {
                            allotmentClassSelect.value = classItem.class;
                        }
                    });
                })
                .catch(error => console.error('Error fetching allotment classes:', error));
        } else {
            // Reset to default allotment classes if fund source is not "Continuing Capital Outlay"
            const defaultAllotmentClasses = @json($allotment_classes);
            allotmentClassSelect.innerHTML = ''; // Remove placeholder option

            defaultAllotmentClasses.forEach((classItem, index) => {
                const option = document.createElement('option');
                option.value = classItem.class;
                option.textContent = classItem.description;
                allotmentClassSelect.appendChild(option);

                // Automatically select the first option
                if (index === 0) {
                    allotmentClassSelect.value = classItem.class;
                }
            });
        }
    }

    function openCreateOfficeAllotmentClassModal() {
        closeAllDropdowns();
        const currentYear = new Date().getFullYear();
        document.getElementById('year').setAttribute('min', currentYear);
        document.getElementById('year').value = currentYear;
        const modal = document.getElementById('createOfficeAllotmentClassModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            const box = modal.querySelector('div.hidden');
            if (box) box.classList.remove('hidden');
        }, 10);
    }

    function closeCreateOfficeAllotmentClassModal() {
        const modal = document.getElementById('createOfficeAllotmentClassModal');
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

    // Form Validation
    function validateCreateOfficeAllotmentClassForm() {
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
            document.getElementById('createOfficeAllotmentClassForm').submit();
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

            // Detect when a field is enabled
            const observer = new MutationObserver(() => updateTextColor(element));
            observer.observe(element, {
                attributes: true,
                attributeFilter: ["disabled"]
            });
        });

        // Handle autofill values after a short delay
        setTimeout(() => {
            elements.forEach(updateTextColor);
        }, 100);

        function updateTextColor(element) {
            if (element.disabled) {
                element.classList.remove("text-gray-900", "dark:text-gray-100");
                element.classList.add("text-gray-400");
            } else if (element.value.trim() !== "") {
                element.classList.remove("text-gray-500", "text-gray-400");
                element.classList.add("text-gray-900", "dark:text-gray-100");
            } else {
                element.classList.remove("text-gray-900", "dark:text-gray-100", "text-gray-400");
            }
        }
    });
</script>