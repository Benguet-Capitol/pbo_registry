<!-- Edit Appropriations Modal -->
<form id="editAppropriationsForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editAppropriationsModal" style="display: none;" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="w-full max-w-4xl rounded-xl shadow-2xl transform transition-all duration-300 ease-out bg-white dark:bg-gray-800 overflow-hidden hidden animate-scaleInUp max-h-[90vh] flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal content -->
            <div class="relative bg-white rounded-xl shadow-sm dark:bg-gray-700 flex flex-col h-full">
                <!-- Modal header -->
                <div class="flex justify-between items-center px-6 py-4 border-b-2 rounded-t-xl dark:border-gray-600 border-gray-200 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-700 flex-shrink-0">
                    <h3 class="text-base leading-6 font-bold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-edit text-amber-600 dark:text-amber-400 mr-3 text-xl"></i>
                        {{ __('Edit Account') }}
                    </h3>
                    <button type="button" onclick="closeEditAppropriationsModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="overflow-y-auto flex-1 px-6 py-4" style="max-height: calc(90vh - 280px);">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="office_allotment_class_id" id="office_allotment_class_id" value="{{ $officeAllotmentClassId ?? '' }}">
                            <input type="hidden" name="appropriation_id" id="appropriation_id" value="{{ $appropriation->id ?? '' }}">
                            <!-- Programs Dropdown -->
                            <div class="sm:col-span-6 relative">
                                <x-form.label for="edit_programs" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Programs / Projects / Activities')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-folder-open"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            name="edit_programs"
                                            id="edit_programs"
                                            placeholder="{{ __('Programs / Projects / Activities') }}"
                                            class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200 text-gray-500"
                                            autocomplete="off"
                                            oninput="showEditProgramsDropdown()"
                                            :value="old('programs', $appropriation->programs ?? '')"
                                        />
                                    </x-form.input-with-icon-wrapper>
                                    <div id="editProgramsDropdown" class="absolute w-full bg-white dark:bg-gray-800 text-xsborder border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                        <!-- Suggestions appear here -->
                                    </div>
                                </div>
                            </div>
                            <!-- Account Code -->
                            <div class="sm:col-span-3 relative">
                                <x-form.label for="account_code" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Account Code')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-stream"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            type="text"
                                            name="edit_account_code"
                                            id="edit_account_code"
                                            placeholder="{{ __('Account Code') }}"
                                            class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            oninput="filterAccountCodes()"
                                            autocomplete="off"
                                            :value="old('account_code', $appropriation->account_code ?? '')"
                                        />
                                    </x-form.input-with-icon-wrapper>
                                    <div id="edit_accountCodeDropdown" class="absolute w-full bg-white dark:bg-gray-800 text-xs border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                        <!-- Suggestions appear here -->
                                    </div>
                                    <span id="edit_accountCodeError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>

                            <!-- Particulars -->
                            <div class="sm:col-span-3">
                                <x-form.label for="description" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Particulars')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-clipboard"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            type="text"
                                            name="edit_description"
                                            id="edit_description"
                                            placeholder="{{ __('Particulars') }}"
                                            class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            autocomplete="off"
                                            :value="old('description', $appropriation->description ?? '')"
                                        />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_descriptionError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- FPP Code -->
                            <div class="sm:col-span-3">
                                <x-form.label for="fpp_code" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('FPP Code')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_fpp_code" id="edit_fpp_code" autocomplete="off" placeholder="{{ __('FPP Code') }}" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" :value="old('fpp_code', $appropriation->fpp_code ?? '')" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_fpp_codeError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- Project No. -->
                            <div class="sm:col-span-3">
                                <x-form.label for="project_no" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Project No.')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-hashtag"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_project_no" id="edit_project_no" autocomplete="off" placeholder="{{ __('Project No.') }}" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" :value="old('project_no', $appropriation->project_no ?? '')" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- CCO Year -->
                            <div class="sm:col-span-3">
                                <x-form.label for="cco_year" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('CCO Year')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_cco_year" id="edit_cco_year" autocomplete="off" placeholder="{{ __('CCO Year') }}" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" :value="old('cco_year', $appropriation->cco_year ?? '')" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Project Location -->
                            <div class="sm:col-span-3">
                                <x-form.label for="project_location" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Project Location')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-location-dot"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_project_location" id="edit_project_location" autocomplete="off" placeholder="{{ __('Project Location') }}" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" :value="old('project_location', $appropriation->project_location ?? '')" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Appropriation -->
                            <div class="sm:col-span-6">
                                <x-form.label for="appropriation" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Appropriation')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-money-check"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_appropriation_formatted" id="edit_appropriation" value="{{ number_format(old('appropriation', $appropriation->appropriation ?? 0.00), 2) }}" placeholder="{{ __('Appropriation') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="edit_appropriation" id="edit_appropriation_hidden" value="{{ old('appropriation', $appropriation->appropriation ?? '0.00') }}">
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edit_appropriationError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- 1st Quarter Allotment -->
                            <div class="sm:col-span-3">
                                <x-form.label for="quarter1" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('1st Quarter Allotment')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-1"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_quarter1_formatted" id="edit_quarter1" value="{{ number_format(old('quarter1', $appropriation->quarter1 ?? 0.00), 2) }}" placeholder="{{ __('1st Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="edit_quarter1" id="edit_quarter1_hidden" value="{{ old('quarter1', $appropriation->quarter1 ?? '0.00') }}">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- 2nd Quarter Allotment -->
                            <div class="sm:col-span-3">
                                <x-form.label for="quarter2" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('2nd Quarter Allotment')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-2"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_quarter2_formatted" id="edit_quarter2" value="{{ number_format(old('quarter2', $appropriation->quarter2 ?? 0.00), 2) }}" placeholder="{{ __('2nd Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="edit_quarter2" id="edit_quarter2_hidden" value="{{ old('quarter2', $appropriation->quarter2 ?? '0.00') }}">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- 3rd Quarter Allotment -->
                            <div class="sm:col-span-3">
                                <x-form.label for="quarter3" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('3rd Quarter Allotment')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-3"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_quarter3_formatted" id="edit_quarter3" value="{{ number_format(old('quarter3', $appropriation->quarter3 ?? 0.00), 2) }}" placeholder="{{ __('3rd Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="edit_quarter3" id="edit_quarter3_hidden" value="{{ old('quarter3', $appropriation->quarter3 ?? '0.00') }}">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- 4th Quarter Allotment -->
                            <div class="sm:col-span-3">
                                <x-form.label for="quarter4" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('4th Quarter Allotment')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-4"></i>
                                        </x-slot>
                                        <x-form.input withicon type="text" name="edit_quarter4_formatted" id="edit_quarter4" value="{{ number_format(old('quarter4', $appropriation->quarter4 ?? 0.00), 2) }}" placeholder="{{ __('4th Quarter Allotment') }}" oninput="formatCurrency(this)" autocomplete="off" class="block w-full text-xs bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
                                        <input type="hidden" name="edit_quarter4" id="edit_quarter4_hidden" value="{{ old('quarter4', $appropriation->quarter4 ?? '0.00') }}">
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="remarks" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-info-circle"></i>
                                        </x-slot>
                                        <x-form.input withicon name="edit_remarks" id="edit_remarks" autocomplete="off" placeholder="{{ __('Remarks') }}" :value="old('remarks', $appropriation->remarks ?? '')" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-6 p-4 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-xl dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditAppropriationsForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditAppropriationsModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times text-lg mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    //Open Edit Modal
    function openEditAppropriationsModal(appropriation) {
        closeAllDropdowns();
        document.querySelector("input[name='appropriation_id']").value = appropriation.id;

        // Corrected form ID
        document.getElementById('editAppropriationsForm').action = '/appropriations/' + appropriation.id;

        document.getElementById('edit_programs').value = appropriation.programs;
        updateTextColor(document.getElementById('edit_programs'));
        document.getElementById('edit_account_code').value = appropriation.account_code;
        updateTextColor(document.getElementById('edit_account_code'));
        document.getElementById('edit_description').value = appropriation.description;
        updateTextColor(document.getElementById('edit_description'));
        document.getElementById('edit_fpp_code').value = appropriation.fpp_code;
        updateTextColor(document.getElementById('edit_fpp_code'));
        document.getElementById('edit_project_no').value = appropriation.project_no;
        updateTextColor(document.getElementById('edit_project_no'));
        document.getElementById('edit_cco_year').value = appropriation.cco_year;
        updateTextColor(document.getElementById('edit_cco_year'));
        document.getElementById('edit_project_location').value = appropriation.project_location;
        updateTextColor(document.getElementById('edit_project_location'));
        document.getElementById('edit_appropriation').value = parseFloat(appropriation.appropriation).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        updateTextColor(document.getElementById('edit_appropriation'));
        document.getElementById('edit_appropriation_hidden').value = appropriation.appropriation;
        document.getElementById('edit_quarter1').value = parseFloat(appropriation.quarter1).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        updateTextColor(document.getElementById('edit_quarter1'));
        document.getElementById('edit_quarter1_hidden').value = appropriation.quarter1;
        document.getElementById('edit_quarter2').value = parseFloat(appropriation.quarter2).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        updateTextColor(document.getElementById('edit_quarter2'));
        document.getElementById('edit_quarter2_hidden').value = appropriation.quarter2;
        document.getElementById('edit_quarter3').value = parseFloat(appropriation.quarter3).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        updateTextColor(document.getElementById('edit_quarter3'));
        document.getElementById('edit_quarter3_hidden').value = appropriation.quarter3;
        document.getElementById('edit_quarter4').value = parseFloat(appropriation.quarter4).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        updateTextColor(document.getElementById('edit_quarter4'));
        document.getElementById('edit_quarter4_hidden').value = appropriation.quarter4;
        document.getElementById('edit_remarks').value = appropriation.remarks;

        const modal = document.getElementById('editAppropriationsModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            const box = modal.querySelector('div.hidden');
            if (box) box.classList.remove('hidden');
        }, 10);
    }
    //Close Edit Modal
    function closeEditAppropriationsModal() {
        const modal = document.getElementById('editAppropriationsModal');
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
    // Unhide Project No. and CCO year if the allotment class is CCO and unhide CCO Year only if the office is PDF
    document.addEventListener("DOMContentLoaded", function() {
        let officeName = "{{ $officeName }}".trim();
        let allotmentClassDescription = "{{ $allotmentClassDescription }}".trim();

        let projectNoField = document.getElementById("edit_project_no").closest("div.sm\\:col-span-3");
        let ccoYearField = document.getElementById("edit_cco_year").closest("div.sm\\:col-span-3");

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
        if (!value) {
            // Also clear the hidden input if present
            let hiddenInputId = input.id + "_hidden";
            let hiddenInput = document.getElementById(hiddenInputId);
            if (hiddenInput) hiddenInput.value = '';
            return;
        }

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
            // Always store a valid float string (or empty if not valid)
            let cleanValue = value.replace(/,/g, '');
            hiddenInput.value = cleanValue && !isNaN(cleanValue) ? cleanValue : '';
        }
    }
    //Autocomplete of Account Code and to populate the particulars based on the selected Account Code
    const edit_accountCodes = [
        @foreach($account_codes as $account_code) {
            code: "{{ $account_code->code }}",
            description: "{{ $account_code->description }}"
        },
        @endforeach
    ];

    function filterAccountCodes() {
        const input = document.getElementById("edit_account_code");
        const dropdown = document.getElementById("edit_accountCodeDropdown");
        const filter = input.value.toLowerCase();
        dropdown.innerHTML = ""; // Clear previous results
        if (!filter) {
            dropdown.classList.add("hidden");
            return;
        }
        const filteredCodes = edit_accountCodes.filter(item => item.code.toLowerCase().includes(filter));
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
                document.getElementById("edit_description").value = item.description; // Auto-fill Particulars field
                updateTextColor(input);
                updateTextColor(document.getElementById("edit_description"));
                dropdown.classList.add("hidden");
            };
            dropdown.appendChild(option);
        });
        dropdown.classList.remove("hidden");
    }

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("accountCodeDropdown");
        if (!event.target.closest("#edit_account_code")) {
            dropdown.classList.add("hidden");
        }
    });

    // Programs Autocomplete Dropdown for Edit
    const editProgramsList = [
        @foreach($programs as $program)
            { name: "{{ $program->name ?? $program->program }}" },
        @endforeach
    ];

    function showEditProgramsDropdown() {
        const input = document.getElementById("edit_programs");
        const dropdown = document.getElementById("editProgramsDropdown");
        const filter = input.value.toLowerCase();
        dropdown.innerHTML = "";
        if (!filter) {
            dropdown.classList.add("hidden");
            return;
        }
        const filtered = editProgramsList.filter(item => item.name && item.name.toLowerCase().includes(filter));
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
        const dropdown = document.getElementById("editProgramsDropdown");
        if (!event.target.closest("#edit_programs")) {
            dropdown.classList.add("hidden");
        }
    });

    // Make updateTextColor globally accessible
    function updateTextColor(input) {
        if (input.value.trim() !== "") {
            input.classList.remove("text-gray-500");
            input.classList.add("text-gray-900", "dark:text-gray-100");
        } else {
            input.classList.remove("text-gray-900", "dark:text-gray-100");
            input.classList.add("text-gray-500");
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const inputs = document.querySelectorAll("input");
        inputs.forEach(input => {
            updateTextColor(input); // Check initial values
            input.addEventListener("input", function() { updateTextColor(this); });
        });
    });

    //Form Validation
    function validateEditAppropriationsForm() {
        let isValid = true;

        // Get input values
        const edit_accountCode = document.getElementById('edit_account_code').value.trim();
        const edit_description = document.getElementById('edit_description').value.trim();
        const edit_appropriation = document.getElementById('edit_appropriation').value.trim();
        const edit_fpp_code = document.getElementById('edit_fpp_code').value.trim();

        // Validate fields
        if (!edit_accountCode) {
            document.getElementById('edit_accountCodeError').innerText = 'Account Code is required.';
            isValid = false;
        } else {
            document.getElementById('edit_accountCodeError').innerText = '';
        }

        if (!edit_fpp_code) {
            document.getElementById('edit_fpp_codeError').innerText = 'FPP Code is required.';
            isValid = false;
        } else {
            document.getElementById('edit_fpp_codeError').innerText = '';
        }

        if (!edit_description) {
            document.getElementById('edit_descriptionError').innerText = 'Particulars are required.';
            isValid = false;
        } else {
            document.getElementById('edit_descriptionError').innerText = '';
        }

        if (!edit_appropriation || isNaN(parseFloat(edit_appropriation.replace(/,/g, '')))) {
            document.getElementById('edit_appropriationError').innerText = 'Valid Appropriation amount is required.';
            isValid = false;
        } else {
            document.getElementById('edit_appropriationError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editAppropriationsForm').submit();
        }
    }
</script>

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