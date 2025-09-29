<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Appropriations and Allotments') }}
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
                <form id="editAppropriationsForm" method="POST" action="{{ route('appropriations.update', $appropriation) }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-6">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">

                            <!-- Programs -->
                            <div class="sm:col-span-6">
                                <x-form.label for="programs" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Programs')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-folder-open"></i>
                                        </x-slot>
                                        <x-form.input withicon name="programs" autocomplete="off" id="programs" placeholder="{{ __('Programs') }}" :value="old('programs', $appropriation->programs)" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Account Code -->
                            <div class="sm:col-span-3 relative">
                                <x-form.label for="account_code" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Account Code')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-stream"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            type="text"
                                            name="account_code"
                                            id="account_code"
                                            placeholder="{{ __('Account Code') }}"
                                            :value="old('account_code', $appropriation->account_code)"
                                            class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            oninput="filterAccountCodes()"
                                            autocomplete="off" />
                                    </x-form.input-with-icon-wrapper>
                                    <div id="accountCodeDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                        <!-- Suggestions appear here -->
                                    </div>
                                    <span id="accountCodeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                            <!-- Particulars -->
                            <div class="sm:col-span-3">
                                <x-form.label for="description" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Particulars')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-clipboard"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            type="text"
                                            name="description"
                                            id="description"
                                            placeholder="{{ __('Particulars') }}"
                                            :value="old('description', $appropriation->description)"
                                            class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            autocomplete="off" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="descriptionError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Account Code ID -->
                            <!-- <div class="sm:col-span-3">
                                <x-form.label for="id2" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('ID (For repeatedly used Account Codes)')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-landmark-flag"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="id2" id="id2" autocomplete="off" placeholder="{{ __('Account Code ID') }}" :value="old('id2', $appropriation->id2)" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"/>
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div> -->
                            <!-- FPP Code -->
                            <div class="sm:col-span-3">
                                <x-form.label for="fpp_code" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('FPP Code')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="fpp_code" id="fpp_code" autocomplete="off" placeholder="{{ __('FPP Code') }}" :value="old('fpp_code', $appropriation->fpp_code)" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="fpp_codeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Project No. -->
                            <div class="sm:col-span-3">
                                <x-form.label for="project_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Project No.')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-hashtag"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="project_no" id="project_no" autocomplete="off" placeholder="{{ __('Project No.') }}" :value="old('project_no', $appropriation->project_no)" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- CCO Year -->
                            <div class="sm:col-span-3">
                                <x-form.label for="cco_year" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('CCO Year')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="cco_year" id="cco_year" autocomplete="off" placeholder="{{ __('CCO Year') }}" :value="old('cco_year', $appropriation->cco_year)" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Project Location -->
                            <div class="sm:col-span-3">
                                <x-form.label for="project_location" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Project Location')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-location-dot"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="project_location" id="project_location" autocomplete="off" placeholder="{{ __('Project Location') }}" :value="old('project_location', $appropriation->project_location)" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Appropriation -->
                            <div class="sm:col-span-6">
                                <x-form.label for="appropriation" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Appropriation')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-money-check"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="appropriation_formatted" id="appropriation" value="{{ number_format(old('appropriation', $appropriation->appropriation), 2) }}" placeholder="{{ __('Appropriation') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="appropriation" id="appropriation_hidden" value="{{ old('appropriation', $appropriation->appropriation) }}">
                                    </x-form.input-with-icon-wrapper>
                                    <span id="appropriationError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- 1st Quarter Allotment -->
                            <div class="sm:col-span-3">
                                <x-form.label for="quarter1" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('1st Quarter Allotment')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-1"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="quarter1_formatted" id="quarter1" value="{{ number_format(old('quarter1', $appropriation->quarter1), 2) }}" placeholder="{{ __('1st Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="quarter1" id="quarter1_hidden" value="{{ old('quarter1', $appropriation->quarter1) }}">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- 2nd Quarter Allotment -->
                            <div class="sm:col-span-3">
                                <x-form.label for="quarter2" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('2nd Quarter Allotment')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-2"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="quarter2_formatted" id="quarter2" value="{{ number_format(old('quarter2', $appropriation->quarter2), 2) }}" placeholder="{{ __('2nd Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="quarter2" id="quarter2_hidden" value="{{ old('quarter2', $appropriation->quarter2) }}">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- 3rd Quarter Allotment -->
                            <div class="sm:col-span-3">
                                <x-form.label for="quarter3" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('3rd Quarter Allotment')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-3"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="quarter3_formatted" id="quarter3" value="{{ number_format(old('quarter3', $appropriation->quarter3), 2) }}" placeholder="{{ __('3rd Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="quarter3" id="quarter3_hidden" value="{{ old('quarter3', $appropriation->quarter3) }}">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- 4th Quarter Allotment -->
                            <div class="sm:col-span-3">
                                <x-form.label for="quarter4" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('4th Quarter Allotment')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-4"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="quarter4_formatted" id="quarter4" value="{{ number_format(old('quarter4', $appropriation->quarter4), 2) }}" placeholder="{{ __('4th Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="quarter4" id="quarter4_hidden" value="{{ old('quarter4', $appropriation->quarter4) }}">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-info-circle"></i>
                                        </x-slot>
                                        <x-form.input withicon name="remarks" id="remarks" autocomplete="off" placeholder="{{ __('Remarks') }}" :value="old('remarks', $appropriation->remarks)" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="remarksError" class="text-red-500 text-sm"></span>
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
                <a href="{{ route('appropriations.index', ['office_allotment_class_id' => $appropriation->office_allotment_class_id]) }}" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times text-xl mr-2"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    //Format the value of the numbers with comma and decimal
    function formatCurrency(input) {
        let value = input.value.replace(/,/g, ''); // Remove commas
        if (!value) return;

        let [integer, decimal] = value.split('.');
        integer = integer.replace(/\D/g, ''); // Remove non-numeric characters

        integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        if (decimal !== undefined) {
            decimal = decimal.replace(/\D/g, '').substring(0, 2);
            input.value = `${integer}.${decimal}`;
        } else {
            input.value = integer;
        }

        // Get the corresponding hidden input based on the name or id
        let hiddenInputId = input.id + "_hidden";
        let hiddenInput = document.getElementById(hiddenInputId);

        if (hiddenInput) {
            hiddenInput.value = value.replace(/,/g, ''); // Store clean numeric value
        }
    }
    //Autocomplete of Account Code and to populate the particulars based on the selected Account Code
    const accountCodes = [
        @foreach($account_codes as $account_code) {
            code: "{{ $account_code->code }}",
            description: "{{ $account_code->description }}"
        },
        @endforeach
    ];

    function filterAccountCodes() {
        const input = document.getElementById("account_code");
        const dropdown = document.getElementById("accountCodeDropdown");
        const filter = input.value.toLowerCase();

        dropdown.innerHTML = ""; // Clear previous results

        if (!filter) {
            dropdown.classList.add("hidden");
            return;
        }

        const filteredCodes = accountCodes.filter(item => item.code.toLowerCase().includes(filter));

        if (filteredCodes.length === 0) {
            dropdown.classList.add("hidden");
            return;
        }

        filteredCodes.forEach(item => {
            const option = document.createElement("div");
            option.className = "p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer";
            option.innerHTML = `<strong>${item.code}</strong> - ${item.description}`;
            option.onclick = function() {
                input.value = item.code;
                document.getElementById("description").value = item.description; // Auto-fill Particulars field
                dropdown.classList.add("hidden");
            };
            dropdown.appendChild(option);
        });

        dropdown.classList.remove("hidden");
    }

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("accountCodeDropdown");
        if (!event.target.closest("#account_code")) {
            dropdown.classList.add("hidden");
        }
    });

    //Checks if an input has a value and adjusts the text color accordingly
    document.addEventListener("DOMContentLoaded", function() {
        const inputs = document.querySelectorAll("input");

        inputs.forEach(input => {
            updateTextColor(input); // Check initial values

            input.addEventListener("input", function() {
                updateTextColor(this);
            });
        });

        function updateTextColor(input) {
            if (input.value.trim() !== "") {
                input.classList.remove("text-gray-500");
                input.classList.add("text-gray-900", "dark:text-gray-100");
            } else {
                input.classList.remove("text-gray-900", "dark:text-gray-100");
                input.classList.add("text-gray-500");
            }
        }
    });

    //Form Validation
    function validateForm() {
        let isValid = true;

        // Get input values
        const accountCode = document.getElementById('account_code').value.trim();
        const description = document.getElementById('description').value.trim();
        const appropriation = document.getElementById('appropriation').value.trim();
        const fpp_code = document.getElementById('fpp_code').value.trim();

        // Validate fields
        if (!accountCode) {
            document.getElementById('accountCodeError').innerText = 'Account Code is required.';
            isValid = false;
        } else {
            document.getElementById('accountCodeError').innerText = '';
        }

        if (!fpp_code) {
            document.getElementById('fpp_codeError').innerText = 'FPP Code is required.';
            isValid = false;
        } else {
            document.getElementById('fpp_codeError').innerText = '';
        }

        if (!description) {
            document.getElementById('descriptionError').innerText = 'Particulars are required.';
            isValid = false;
        } else {
            document.getElementById('descriptionError').innerText = '';
        }

        if (!appropriation || isNaN(parseFloat(appropriation.replace(/,/g, '')))) {
            document.getElementById('appropriationError').innerText = 'Valid Appropriation amount is required.';
            isValid = false;
        } else {
            document.getElementById('appropriationError').innerText = '';
        }

        if (isValid) {
            console.log("Form is valid, submitting...")
            document.getElementById('editAppropriationsForm').submit();
        }
    }
</script>