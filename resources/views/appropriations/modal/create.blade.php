<!-- Create Appropriations Modal -->
<form id="createAppropriationsForm" method="POST" action="{{ route('appropriations.store') }}">
    @csrf
    <div id="createAppropriationsModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Create Account') }}
                    </h3>
                    <button type="button" onclick="closeCreateAppropriationsModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="office_allotment_class_id" id="office_allotment_class_id" value="{{ $officeAllotmentClassId }}">
                            <!-- Programs Dropdown -->
                            <div class="sm:col-span-6 relative">
                                <x-form.label for="programs" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Programs / Projects / Activities')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-folder-open"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            name="programs"
                                            id="programs"
                                            placeholder="{{ __('Programs / Projects / Activities') }}"
                                            class="block w-full dark:bg-gray-800 dark:text-gray-200 text-gray-500"
                                            autocomplete="off"
                                            oninput="showProgramsDropdown()"
                                            :value="old('programs')"
                                        />
                                    </x-form.input-with-icon-wrapper>
                                    <div id="programsDropdown" class="absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                        <!-- Suggestions appear here -->
                                    </div>
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
                                            class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            oninput="showAccountCodeDropdown()"
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
                                        <x-form.input withicon type="text" name="id2" id="id2" autocomplete="off" placeholder="{{ __('Account Code ID') }}" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"/>
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
                                        <x-form.input withicon type="text" name="fpp_code" id="fpp_code" autocomplete="off" placeholder="{{ __('FPP Code') }}" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.input withicon type="text" name="project_no" id="project_no" autocomplete="off" placeholder="{{ __('Project No.') }}" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.input withicon type="text" name="cco_year" id="cco_year" autocomplete="off" placeholder="{{ __('CCO Year') }}" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.input withicon type="text" name="project_location" id="project_location" autocomplete="off" placeholder="{{ __('Project Location') }}" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.input withicon type="text" name="appropriation_formatted" id="appropriation" value="{{ number_format(old('appropriation', 0.00), 2) }}" placeholder="{{ __('Appropriation') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="appropriation" id="appropriation_hidden" value="{{ old('appropriation', '0.00') }}">
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
                                        <x-form.input withicon type="text" name="quarter1_formatted" id="quarter1" value="{{ number_format(old('quarter1', 0.00), 2) }}" placeholder="{{ __('1st Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="quarter1" id="quarter1_hidden" value="{{ old('quarter1', '0.00') }}">
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
                                        <x-form.input withicon type="text" name="quarter2_formatted" id="quarter2" value="{{ number_format(old('quarter2', 0.00), 2) }}" placeholder="{{ __('2nd Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="quarter2" id="quarter2_hidden" value="{{ old('quarter2', '0.00') }}">
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
                                        <x-form.input withicon type="text" name="quarter3_formatted" id="quarter3" value="{{ number_format(old('quarter3', 0.00), 2) }}" placeholder="{{ __('3rd Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="quarter3" id="quarter3_hidden" value="{{ old('quarter3', '0.00') }}">
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
                                        <x-form.input withicon type="text" name="quarter4_formatted" id="quarter4" value="{{ number_format(old('quarter4', 0.00), 2) }}" placeholder="{{ __('4th Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="quarter4" id="quarter4_hidden" value="{{ old('quarter4', '0.00') }}">
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
                                        <x-form.input withicon name="remarks" id="remarks" autocomplete="off" placeholder="{{ __('Remarks') }}" :value="old('remarks')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="remarksError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateAppropriationsForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateAppropriationsModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    //Open Create Modal
    function openCreateAppropriationsModal() {
        closeAllDropdowns();
        document.getElementById('createAppropriationsModal').classList.remove('hidden');
    }
    //Close Create Modal
    function closeCreateAppropriationsModal() {
        document.getElementById('createAppropriationsModal').classList.add('hidden');
    }
    // Unhide Project No. and CCO year if the allotment class is CCO and unhide CCO Year only if the office is PDF
    document.addEventListener("DOMContentLoaded", function() {
        let officeName = "{{ $officeName }}".trim();
        let allotmentClassDescription = "{{ $allotmentClassDescription }}".trim();

        let projectNoField = document.getElementById("project_no").closest("div.sm\\:col-span-3");
        let ccoYearField = document.getElementById("cco_year").closest("div.sm\\:col-span-3");

        // Hide both fields initially
        projectNoField.style.display = "none";
        ccoYearField.style.display = "none";

        // Show Project No. if office is PDF
        if (officeName === "Provincial Development Fund") {
            projectNoField.style.display = "block";
        }

        // Show CCO Year if allotment class is CCO
        if (allotmentClassDescription === "Continuing Capital Outlay") {
            projectNoField.style.display = "block";
            ccoYearField.style.display = "block";
        }

        // Show projectNoField if allotment class is CO and MOOE
        if (allotmentClassDescription === "Capital Outlay" || allotmentClassDescription === "Maintenance and Other Operating Expenditures") {
            projectNoField.style.display = "block";
        }
    });
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

    function showAccountCodeDropdown() {
        const input = document.getElementById("account_code");
        const dropdown = document.getElementById("accountCodeDropdown");
        const filter = input.value.toLowerCase();

        dropdown.innerHTML = ""; // Clear previous results

        if (!filter) {
            dropdown.classList.add("hidden");
            return;
        }

        // Filter by code or description
        const filteredCodes = accountCodes.filter(item =>
            item.code.toLowerCase().includes(filter) ||
            item.description.toLowerCase().includes(filter)
        );

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

    // Programs Autocomplete Dropdown
    const programsList = [
        @foreach($programs as $program)
            { name: "{{ $program->program }}" },
        @endforeach
    ];

    function showProgramsDropdown() {
        const input = document.getElementById("programs");
        const dropdown = document.getElementById("programsDropdown");
        const filter = input.value.toLowerCase();
        dropdown.innerHTML = "";
        if (!filter) {
            dropdown.classList.add("hidden");
            return;
        }
        const filtered = programsList.filter(item => item.name.toLowerCase().includes(filter));
        if (filtered.length === 0) {
            dropdown.classList.add("hidden");
            return;
        }
        filtered.forEach(item => {
            const option = document.createElement("div");
            option.className = "p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer";
            option.innerHTML = `${item.name}`;
            option.onclick = function() {
                input.value = item.name;
                dropdown.classList.add("hidden");
                updateTextColor(input);
            };
            dropdown.appendChild(option);
        });
        dropdown.classList.remove("hidden");
    }

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("programsDropdown");
        if (!event.target.closest("#programs")) {
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

    // --- Quarter Allotment Sum Validation ---
    function validateQuarterAllotments() {
        const appropriationInput = document.getElementById('appropriation');
        const quarter1Input = document.getElementById('quarter1');
        const quarter2Input = document.getElementById('quarter2');
        const quarter3Input = document.getElementById('quarter3');
        const quarter4Input = document.getElementById('quarter4');
        const appropriationError = document.getElementById('appropriationError');

        // Remove commas and parse as float
        const appropriation = parseFloat(appropriationInput.value.replace(/,/g, '')) || 0;
        const q1 = parseFloat(quarter1Input.value.replace(/,/g, '')) || 0;
        const q2 = parseFloat(quarter2Input.value.replace(/,/g, '')) || 0;
        const q3 = parseFloat(quarter3Input.value.replace(/,/g, '')) || 0;
        const q4 = parseFloat(quarter4Input.value.replace(/,/g, '')) || 0;

        const totalQuarters = q1 + q2 + q3 + q4;

        if (totalQuarters > appropriation) {
            appropriationError.innerText = 'The sum of all quarter allotments must not exceed the Appropriation amount.';
            return false;
        } else {
            appropriationError.innerText = '';
            return true;
        }
    }

    ['appropriation', 'quarter1', 'quarter2', 'quarter3', 'quarter4'].forEach(function(id) {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', validateQuarterAllotments);
        }
    });

    //Form Validation
    function validateCreateAppropriationsForm() {
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

        // Quarter allotment sum validation
        if (!validateQuarterAllotments()) {
            isValid = false;
        }
        if (isValid) {
            document.getElementById('createAppropriationsForm').submit();
        }
    }
</script>