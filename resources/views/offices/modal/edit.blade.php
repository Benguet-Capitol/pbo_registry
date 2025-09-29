<!-- Edit Office Modal -->
<form id="editOfficeForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editOfficeModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Office') }}
                    </h3>
                    <button type="button" onclick="closEditOfficeModal()" class="text-black hover:text-gray-600 dark:text-gray-200 dark:hover:text-gray-400">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Office Name -->
                        <div class="space-y-2">
                            <input type="hidden" name="office_id" id="office_id" value="{{ $office->id }}">
                            <x-form.label
                                for="office_name"
                                :value="__('Office Name')" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-qrcode"></i>
                                </x-slot>

                                <x-form.input
                                    withicon
                                    id="edit_office_name"
                                    class="block w-full"
                                    type="text"
                                    name="edit_office_name"
                                    :value="old('office_name')"
                                    autofocus
                                    autocomplete="off"
                                    placeholder="{{ __('Office Name') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="editofficeNameError" class="text-red-500 text-sm"></span>
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
                                        id="edit_office_abbreviation"
                                        class="block w-full"
                                        type="text"
                                        name="edit_office_abbreviation"
                                        :value="old('office_abbreviation')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Abbreviation') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editofficeAbbreviationError" class="text-red-500 text-sm"></span>
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
                                        id="edit_sub_office"
                                        class="block w-full"
                                        type="text"
                                        name="edit_sub_office"
                                        :value="old('sub_office')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Sub Office') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editsubOfficeError" class="text-red-500 text-sm"></span>
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
                                    id="edit_fund"
                                    class="block w-full"
                                    type="text"
                                    name="edit_fund"
                                    :value="old('fund')"
                                    autocomplete="off"
                                    placeholder="{{ __('Fund') }}">
                                    <option value="">{{ __('Select Fund') }}</option>
                                    @foreach($funds as $fund)
                                    <option value="{{ $fund->fund }}">{{ $fund->fund }}</option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="editfundError" class="text-red-500 text-sm"></span>
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
                                        id="edit_fpp_code"
                                        class="block w-full"
                                        type="text"
                                        name="edit_fpp_code"
                                        :value="old('fpp_code')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('FPP Code') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editfppCodeError" class="text-red-500 text-sm"></span>
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
                                        id="edit_responsibility_code"
                                        class="block w-full"
                                        type="text"
                                        name="edit_responsibility_code"
                                        :value="old('responsibility_code')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Responsibility Code') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editresponsibilityCodeError" class="text-red-500 text-sm"></span>
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
                                    id="edit_branch"
                                    class="block w-full"
                                    type="text"
                                    name="edit_branch"
                                    :value="old('branch')"
                                    autofocus
                                    autocomplete="off"
                                    placeholder="{{ __('Branch') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="editbranchError" class="text-red-500 text-sm"></span>
                        </div>

                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditOfficeForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditOfficeModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</form>

<script>
    function updateTextColor() {
        const fields = [
            document.getElementById('edit_office_name'),
            document.getElementById('edit_office_abbreviation'),
            document.getElementById('edit_sub_office'),
            document.getElementById('edit_fund'),
            document.getElementById('edit_fpp_code'),
            document.getElementById('edit_responsibility_code'),
            document.getElementById('edit_branch')
        ];
        fields.forEach(field => {
            if (!field) return;
            if (field.value && field.value.trim() !== '') {
                field.classList.remove('text-gray-500');
                field.classList.add('text-gray-900', 'dark:text-gray-100');
            } else {
                field.classList.remove('text-gray-900', 'dark:text-gray-100');
                field.classList.add('text-gray-500');
            }
        });
    }

    function openEditOfficeModal(office) {
        closeAllDropdowns();
        document.querySelector("input[name='office_id']").value = office.id;
        document.getElementById('editOfficeForm').action = '/offices/' + office.id;
        document.getElementById('edit_office_name').value = office.office_name;
        document.getElementById('edit_office_abbreviation').value = office.office_abbreviation;
        document.getElementById('edit_sub_office').value = office.sub_office;
        document.getElementById('edit_fund').value = office.fund;
        document.getElementById('edit_fpp_code').value = office.fpp_code;
        document.getElementById('edit_responsibility_code').value = office.responsibility_code;
        document.getElementById('edit_branch').value = office.branch;
        document.getElementById('editOfficeModal').classList.remove('hidden');
        updateTextColor(); // Ensure text color updates after setting values
    }

    function closeEditOfficeModal() {
        document.getElementById('editOfficeModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        [
            'edit_office_name',
            'edit_office_abbreviation',
            'edit_sub_office',
            'edit_fund',
            'edit_fpp_code',
            'edit_responsibility_code',
            'edit_branch'
        ].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateTextColor);
                el.addEventListener('change', updateTextColor);
                // Initial color update
                updateTextColor();
            }
        });
    });

    function validateEditOfficeForm() {
        let isValid = true;

        const editofficeName = document.getElementById('edit_office_name').value;
        const editofficeAbbreviation = document.getElementById('edit_office_abbreviation').value;
        const editsubOffice = document.getElementById('edit_sub_office').value;
        const editfund = document.getElementById('edit_fund').value;
        const editbranch = document.getElementById('edit_branch').value;

        if (!editofficeName) {
            document.getElementById('editofficeNameError').innerText = 'Office Name is required.';
            isValid = false;
        } else {
            document.getElementById('editofficeNameError').innerText = '';
        }

        if (!editofficeAbbreviation) {
            document.getElementById('editofficeAbbreviationError').innerText = 'Abbreviation is required.';
            isValid = false;
        } else {
            document.getElementById('editofficeAbbreviationError').innerText = '';
        }

        if (!editfund) {
            document.getElementById('editfundError').innerText = 'Fund is required.';
            isValid = false;
        } else {
            document.getElementById('editfundError').innerText = '';
        }

        if (!editbranch) {
            document.getElementById('editbranchError').innerText = 'Branch is required.';
            isValid = false;
        } else {
            document.getElementById('editbranchError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editOfficeForm').submit();
        }
    }
</script>