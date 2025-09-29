<!-- Edit Fund Modal -->
<form id="editFundForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editFundModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Fund') }}
                    </h3>
                    <button type="button" onclick="closeEditFundModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Fund -->
                        <div class="space-y-2">
                            <input type="hidden" name="fund_id" id="fund_id" value="{{ $fund->id }}">
                            <x-form.label for="fund" :value="__('Fund')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-star-of-david"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_fund" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_fund" :value="old('edit_fund')" autocomplete="off" autofocus placeholder="{{ __('Fund') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_fundError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Fund Type -->
                        <div class="space-y-2">
                            <x-form.label for="fund_type" :value="__('Fund Type')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-spinner"></i>
                                </x-slot>
                                <x-form.select withicon id="edit_fund_type" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_fund_type" :value="old('edit_fund_type')" placeholder="{{ __('Fund Type') }}">
                                    <option value="">{{ __('Select Fund Type') }}</option>
                                    <option value="General Fund">General Fund</option>
                                    <option value="Benguet General Hospital Economic Enterprise">Benguet General Hospital Economic Enterprise</option>
                                    <option value="Special Education Fund">Special Education Fund</option>
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_fund_typeError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Fund Code -->
                        <div class="space-y-2">
                            <x-form.label for="fund_code" :value="__('Fund Code')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-sticky-note"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_fund_code" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_fund_code" autocomplete="off" :value="old('fund_code')" autofocus placeholder="{{ __('Fund Code') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_fund_codeError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditFundForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditFundModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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
            document.getElementById('edit_fund'),
            document.getElementById('edit_fund_type'),
            document.getElementById('edit_fund_code')
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

    function openEditFundModal(fund) {
        closeAllDropdowns();
        document.querySelector("input[name='fund_id']").value = fund.id;
        document.getElementById('editFundForm').action = '/funds/' + fund.id;
        document.getElementById('edit_fund').value = fund.fund;
        document.getElementById('edit_fund_type').value = fund.fund_type;
        document.getElementById('edit_fund_code').value = fund.fund_code;
        document.getElementById('editFundModal').classList.remove('hidden');
        updateTextColor(); // Ensure text color updates after setting values
    }

    function closeEditFundModal() {
        document.getElementById('editFundModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['edit_fund', 'edit_fund_type', 'edit_fund_code'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateTextColor);
                el.addEventListener('change', updateTextColor);
                // Initial color update
                updateTextColor();
            }
        });
    });

    function validateEditFundForm() {
        let isValid = true;

        const edit_fund = document.getElementById('edit_fund').value;
        const edit_fund_type = document.getElementById('edit_fund_type').value;
        const edit_fund_code = document.getElementById('edit_fund_code').value;

        if (!edit_fund) {
            document.getElementById('edit_fundError').innerText = 'Fund is required.';
            isValid = false;
        } else {
            document.getElementById('edit_fundError').innerText = '';
        }

        if (!edit_fund_type) {
            document.getElementById('edit_fund_typeError').innerText = 'Fund Type is required.';
            isValid = false;
        } else {
            document.getElementById('edit_fund_typeError').innerText = '';
        }

        if (!edit_fund_code) {
            document.getElementById('edit_fund_codeError').innerText = 'Fund Code is required.';
            isValid = false;
        } else {
            document.getElementById('edit_fund_codeError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editFundForm').submit();
        }
    }
</script>