<!-- Create Office Allotment Class Modal -->
<form id="editOfficeAllotmentClassForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editOfficeAllotmentClassModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Allotment Class per Office') }}
                    </h3>
                    <button type="button" onclick="closeEditOfficeAllotmentClassModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
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
                                        <x-form.input withicon type="number" name="edit_year" id="edit_year" min="" placeholder="{{ __('Year') }}" :value="old('year')" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="edityearError" class="text-red-500 text-sm"></span>
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
                                            name="edit_fund_source"
                                            id="edit_fund_source"
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
                                    <span id="editfundSourceError" class="text-red-500 text-sm"></span>
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
                                            name="edit_office"
                                            id="edit_office"
                                            :value="old('office')"
                                            placeholder="{{ __('Office') }}"
                                            class="block w-full dark:bg-gray-800 dark:text-gray-200"
                                            onchange="fetchFundEdit(this.value)">
                                            <option value="">{{ __('Select Office') }}</option>
                                            @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->office_name }}</option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="editofficeError" class="text-red-500 text-sm"></span>
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
                                        <x-form.input withicon type="text" name="edit_office_abbreviation" id="edit_office_abbreviation" placeholder="{{ __('Office Abbreviation') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" readonly />
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
                                        <x-form.input withicon type="text" name="edit_sub_office" id="edit_sub_office" placeholder="{{ __('Sub Office') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" readonly />
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
                                        <x-form.input withicon type="text" name="edit_fund" id="edit_fund" placeholder="{{ __('Fund') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" readonly />
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
                                        <x-form.input withicon type="text" name="edit_fpp_code" id="edit_fpp_code" placeholder="{{ __('FPP Code') }}" autocomplete="off" class="block w-full bg-white text-gray-500 dark:bg-gray-800 dark:text-gray-200" />
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
                                        <x-form.input withicon type="text" name="edit_responsibility_code" id="edit_responsibility_code" placeholder="{{ __('Responsibility Code') }}" class="block w-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-200" readonly />
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
                                            name="edit_allotment_class"
                                            id="edit_allotment_class"
                                            placeholder="{{ __('Allotment Class') }}"
                                            class="block w-full dark:bg-gray-800 dark:text-gray-200">
                                            <option value="">{{ __('Select Allotment Class') }}</option>
                                            @foreach($allotment_classes as $allotment_class)
                                            <option value="{{ $allotment_class->class }}">{{ $allotment_class->description }}</option>
                                            @endforeach
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="editallotmentClassError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditOfficeAllotmentClassForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditOfficeAllotmentClassModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
     function openEditOfficeAllotmentClassModal(officeAllotmentClass) {
        closeAllDropdowns();

        document.getElementById('editOfficeAllotmentClassForm').action = '{{ route("office_allotment_classes.update", ":id") }}'.replace(':id', officeAllotmentClass.id);

        document.getElementById('edit_year').value = officeAllotmentClass.year;
        updateTextColor(document.getElementById('edit_year'));
        document.getElementById('edit_fund_source').value = officeAllotmentClass.fund_source;
        updateTextColor(document.getElementById('edit_fund_source'));
        document.getElementById('edit_office').value = officeAllotmentClass.office;
        updateTextColor(document.getElementById('edit_office'));
        document.getElementById('edit_office_abbreviation').value = officeAllotmentClass.office_abbreviation;
        updateTextColor(document.getElementById('edit_office_abbreviation'));
        document.getElementById('edit_sub_office').value = officeAllotmentClass.sub_office;
        updateTextColor(document.getElementById('edit_sub_office'));
        document.getElementById('edit_fund').value = officeAllotmentClass.fund;
        updateTextColor(document.getElementById('edit_fund'));
        document.getElementById('edit_fpp_code').value = officeAllotmentClass.fpp_code;
        updateTextColor(document.getElementById('edit_fpp_code'));
        document.getElementById('edit_responsibility_code').value = officeAllotmentClass.responsibility_code;
        updateTextColor(document.getElementById('edit_responsibility_code'));
        document.getElementById('edit_allotment_class').value = officeAllotmentClass.class;
        updateTextColor(document.getElementById('edit_allotment_class'));
        document.getElementById('editOfficeAllotmentClassModal').classList.remove('hidden');
    }

    function closeEditOfficeAllotmentClassModal() {
        document.getElementById('editOfficeAllotmentClassModal').classList.add('hidden');
    }

    function fetchFundEdit(officeId) {
        if (!officeId) {
            document.getElementById('edit_fund').value = '';
            document.getElementById('edit_fpp_code').value = '';
            document.getElementById('edit_responsibility_code').value = '';
            document.getElementById('edit_sub_office').value = '';
            document.getElementById('edit_office_abbreviation').value = '';
            return;
        }

        fetch(`/get-fund/${officeId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_fund').value = data.fund;
                document.getElementById('edit_fpp_code').value = data.fpp_code;
                document.getElementById('edit_responsibility_code').value = data.responsibility_code;
                document.getElementById('edit_sub_office').value = data.sub_office;
                document.getElementById('edit_office_abbreviation').value = data.office_abbreviation;
            })
            .catch(error => console.error('Error fetching fund:', error));
    }

    function fetchAllotmentClassesEdit(fundSource) {
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

    // Form Validation
    function validateEditOfficeAllotmentClassForm() {
        let isValid = true;

        const edit_year = document.getElementById('edit_year').value;
        const edit_fundSource = document.getElementById('edit_fund_source').value;
        const edit_office = document.getElementById('edit_office').value;
        const edit_allotmentClass = document.getElementById('edit_allotment_class').value;

        if (!edit_year) {
            document.getElementById('edityearError').innerText = 'Year is required.';
            isValid = false;
        } else {
            document.getElementById('edityearError').innerText = '';
        }

        if (!edit_fundSource) {
            document.getElementById('editfundSourceError').innerText = 'Fund Source is required.';
            isValid = false;
        } else {
            document.getElementById('editfundSourceError').innerText = '';
        }

        if (!edit_office) {
            document.getElementById('editofficeError').innerText = 'Office is required.';
            isValid = false;
        } else {
            document.getElementById('editofficeError').innerText = '';
        }

        if (!edit_allotmentClass) {
            document.getElementById('editallotmentClassError').innerText = 'Allotment Class is required.';
            isValid = false;
        } else {
            document.getElementById('editallotmentClassError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editOfficeAllotmentClassForm').submit();
        }
    }

    // Make updateTextColor globally accessible
    function updateTextColor(element) {
        if (element.disabled) {
            element.classList.remove("text-gray-900", "dark:text-gray-100");
            element.classList.add("text-gray-400");
        } else if (element.value.trim() !== "") {
            element.classList.remove("text-gray-400", "text-gray-400");
            element.classList.add("text-gray-900", "dark:text-gray-100");
        } else {
            element.classList.remove("text-gray-900", "dark:text-gray-100", "text-gray-400");
        }
    }

</script>