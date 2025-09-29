<!-- Edit Sector Modal -->
<form id="editSectorForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editSectorModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Sector') }}
                    </h3>
                    <button type="button" onclick="closeEditSectorModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Sector -->
                        <div class="space-y-2">
                            <input type="hidden" id="sector_id" name="sector_id" value="{{ $sector->id }}" />
                            <x-form.label for="edit_sector" :value="__('Sector')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-vector-square"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_sector" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_sector" autocomplete="off" :value="old('edit_sector')" autofocus placeholder="{{ __('Sector') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_sectorError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Sector Code -->
                        <div class="space-y-2">
                            <x-form.label for="edit_sector_code" :value="__('Sector Code')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-project-diagram"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_sector_code" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_sector_code" autocomplete="off" :value="old('edit_sector_code')" autofocus placeholder="{{ __('Sector Code') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_sector_codeError" class="text-red-500 text-sm"></span>
                        </div>
                         <!-- Code -->
                        <div class="space-y-2">
                            <x-form.label for="edit_code" :value="__('Code (For Remarks)')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-microchip"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_code" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_code" autocomplete="off" :value="old('edit_code')" autofocus placeholder="{{ __('Code') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_codeError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditSectorForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditSectorModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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
            document.getElementById('edit_sector'),
            document.getElementById('edit_sector_code'),
            document.getElementById('edit_code')
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

    function openEditSectorModal(sector) {
        closeAllDropdowns();
        document.querySelector("input[name='sector_id']").value = sector.id;
        document.getElementById('editSectorForm').action = `/sectors/${sector.id}`;
        document.getElementById('edit_sector').value = sector.sector || '';
        document.getElementById('edit_sector_code').value = sector.sector_code || '';
        document.getElementById('edit_code').value = sector.code || '';
        document.getElementById('editSectorModal').classList.remove('hidden');
        updateTextColor(); // Ensure text color updates after setting values
    }

    function closeEditSectorModal() {
        document.getElementById('editSectorModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['edit_sector', 'edit_sector_code', 'edit_code'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateTextColor);
                el.addEventListener('change', updateTextColor);
                // Initial color update
                updateTextColor();
            }
        });
    });

    function validateEditSectorForm() {
        let isValid = true;

        const sector = document.getElementById('edit_sector').value;
        const sector_code = document.getElementById('edit_sector_code').value;
        const code = document.getElementById('edit_code').value;

        if (!sector) {
            document.getElementById('edit_sectorError').innerText = 'Sector is required.';
            isValid = false;
        } else {
            document.getElementById('edit_sectorError').innerText = '';
        }

        if (!sector_code) {
            document.getElementById('edit_sector_codeError').innerText = 'Sector Code is required.';
            isValid = false;
        } else {
            document.getElementById('edit_sector_codeError').innerText = '';
        }

        if (!code) {
            document.getElementById('edit_codeError').innerText = 'Code is required.';
            isValid = false;
        } else {
            document.getElementById('edit_codeError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editSectorForm').submit();
        }
    }
</script>