<!-- Edit Office Modal -->
<form id="editOfficeForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editOfficeModal" style="display: none;" tabindex="-1" aria-labelledby="editOfficeLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="editOfficeLabel" class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-building text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Office') }}
                </h3>
                <button type="button" onclick="closeEditOfficeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                <div class="grid gap-4">
                        <!-- Office Name -->
                        <div class="space-y-2">
                            <input type="hidden" name="office_id" id="office_id" value="{{ $office->id }}">
                            <x-form.label
                                for="office_name"
                                :value="__('Office Name')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-qrcode"></i>
                                </x-slot>

                                <x-form.input
                                    withicon
                                    id="edit_office_name"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                    type="text"
                                    name="edit_office_name"
                                    :value="old('office_name')"
                                    autofocus
                                    autocomplete="off"
                                    placeholder="{{ __('Office Name') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="editofficeNameError" class="text-red-500 text-xs"></span>
                        </div>

                        <!-- Abbreviation and Sub Office -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Abbreviation -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="office_abbreviation"
                                    :value="__('Abbreviation')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-signature"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="edit_office_abbreviation"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        type="text"
                                        name="edit_office_abbreviation"
                                        :value="old('office_abbreviation')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Abbreviation') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editofficeAbbreviationError" class="text-red-500 text-xs"></span>
                            </div>

                            <!-- Sub Office -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="sub_office"
                                    :value="__('Sub Office')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-window-restore"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="edit_sub_office"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        type="text"
                                        name="edit_sub_office"
                                        :value="old('sub_office')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Sub Office') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editsubOfficeError" class="text-red-500 text-xs"></span>
                            </div>
                        </div>

                        <!-- Fund -->
                        <div class="space-y-2">
                            <x-form.label
                                for="fund"
                                :value="__('Fund')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-money-bill"></i>
                                </x-slot>

                                <x-form.select
                                    withicon
                                    id="edit_fund"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
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
                            <span id="editfundError" class="text-red-500 text-xs"></span>
                        </div>

                        <!-- FPP Code and Responsibility Code -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- FPP Code -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="fpp_code"
                                    :value="__('FPP Code')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-invoice"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="edit_fpp_code"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        type="text"
                                        name="edit_fpp_code"
                                        :value="old('fpp_code')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('FPP Code') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editfppCodeError" class="text-red-500 text-xs"></span>
                            </div>

                            <!-- Responsibility Code -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="responsibility_code"
                                    :value="__('Responsibility Code')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-file-lines"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="edit_responsibility_code"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        type="text"
                                        name="edit_responsibility_code"
                                        :value="old('responsibility_code')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('Responsibility Code') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editresponsibilityCodeError" class="text-red-500 text-xs"></span>
                            </div>

                            <!-- PPA Code -->
                            <div class="space-y-2">
                                <x-form.label
                                    for="ppa_code"
                                    :value="__('PPA Code')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <i class="fas fa-code"></i>
                                    </x-slot>

                                    <x-form.input
                                        withicon
                                        id="edit_ppa_code"
                                        class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                        type="text"
                                        name="edit_ppa_code"
                                        :value="old('ppa_code')"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="{{ __('PPA Code') }}" />
                                </x-form.input-with-icon-wrapper>
                                <span id="editppaCodeError" class="text-red-500 text-xs"></span>
                            </div>
                        </div>

                        <!-- Branch -->
                        <div class="space-y-2">
                            <x-form.label
                                for="branch"
                                :value="__('Branch')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />

                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-sitemap"></i>
                                </x-slot>

                                <x-form.input
                                    withicon
                                    id="edit_branch"
                                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                                    type="text"
                                    name="edit_branch"
                                    :value="old('branch')"
                                    autofocus
                                    autocomplete="off"
                                    placeholder="{{ __('Branch') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="editbranchError" class="text-red-500 text-xs"></span>
                        </div>

                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditOfficeForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditOfficeModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    function updateTextColor() {
        const fields = [
            document.getElementById('edit_office_name'),
            document.getElementById('edit_office_abbreviation'),
            document.getElementById('edit_sub_office'),
            document.getElementById('edit_fund'),
            document.getElementById('edit_fpp_code'),
            document.getElementById('edit_responsibility_code'),
            document.getElementById('edit_ppa_code'),
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
        document.getElementById('edit_ppa_code').value = office.ppa_code;
        document.getElementById('edit_branch').value = office.branch;
        
        const modal = document.getElementById('editOfficeModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 10);
        
        updateTextColor();
    }

    function closeEditOfficeModal() {
        const modal = document.getElementById('editOfficeModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        if (modalContent) {
            modalContent.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
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