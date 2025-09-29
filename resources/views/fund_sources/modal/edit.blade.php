<!-- Edit Fund Source Modal -->
<form id="editFundSourceForm" method="POST" action="">
    @csrf
    @method('PUT')
    <div id="editFundSourceModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Fund Source') }}
                    </h3>
                    <button type="button" onclick="closeEditFundModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Category -->
                        <div class="space-y-2">
                            <input type="hidden" id="fund_source_id" name="fund_source_id" value="{{ $fund_source->id }}" />
                            <x-form.label for="category" :value="__('Category')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-layer-group"></i>
                                </x-slot>
                                <x-form.select withicon id="edit_category" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_category" :value="old('category')" placeholder="{{ __('Category') }}">
                                    <option value="">{{ __('Select Category') }}</option>
                                    <option value="Current">Current</option>
                                    <option value="Continuing">Continuing</option>
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_categoryError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Source -->
                        <div class="space-y-2">
                            <x-form.label for="source" :value="__('Source')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-tag"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_source" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_source" autocomplete="off" :value="old('source')" autofocus placeholder="{{ __('Source') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_sourceError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditFundSourceForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditFundSourceModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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
            document.getElementById('edit_category'),
            document.getElementById('edit_source')
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

    function openEditFundSourceModal(fundSource) {
        closeAllDropdowns();
        document.querySelector("input[name='fund_source_id']").value = fundSource.id;
        document.getElementById('editFundSourceForm').action = `/fund_sources/${fundSource.id}`;
        document.getElementById('edit_category').value = fundSource.category || '';
        document.getElementById('edit_source').value = fundSource.source || '';
        document.getElementById('editFundSourceModal').classList.remove('hidden');
        updateTextColor(); // Ensure text color updates after setting values
    }

    function closeEditFundSourceModal() {
        document.getElementById('editFundSourceModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['edit_category', 'edit_source'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateTextColor);
                el.addEventListener('change', updateTextColor);
                // Initial color update
                updateTextColor();
            }
        });
    });

    function validateEditFundSourceForm() {
        let isValid = true;

        const category = document.getElementById('edit_category').value;
        const source = document.getElementById('edit_source').value;

        if (!category) {
            document.getElementById('edit_categoryError').innerText = 'Category is required.';
            isValid = false;
        } else {
            document.getElementById('edit_categoryError').innerText = '';
        }

        if (!source) {
            document.getElementById('edit_sourceError').innerText = 'Source is required.';
            isValid = false;
        } else {
            document.getElementById('edit_sourceError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editFundSourceForm').submit();
        }
    }
</script>