<!-- Edit Program Modal -->
<form id="editProgramForm" method="POST" action="{{ route('programs.store') }}">
    @csrf
    @method('PUT')
    <div id="editProgramModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Edit Program / Project / Activity') }}
                    </h3>
                    <button type="button" onclick="closeEditProgramModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Program -->
                        <div class="space-y-2">
                            <input type="hidden" id="program_id" name="program_id" value="{{ $program->id }}" />
                            <x-form.label for="program" :value="__('Program / Project / Activity')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-file-alt"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_program" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_program" autocomplete="off" :value="old('program')" autofocus placeholder="{{ __('Program / Project / Activity') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_programError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditProgramForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeEditProgramModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function updateTextColor(element) {
        if (!element) return;
        if (element.value && element.value.trim() !== '') {
            element.classList.remove('text-gray-500');
            element.classList.add('text-gray-900', 'dark:text-gray-100');
        } else {
            element.classList.remove('text-gray-900', 'dark:text-gray-100');
            element.classList.add('text-gray-500');
        }
    }

    function openEditProgramModal(program) {
        closeAllDropdowns();
        document.querySelector("input[name='program_id']").value = program.id;
        document.getElementById('editProgramForm').action = `/programs/${program.id}`;
        document.getElementById('edit_program').value = program.program || '';
        updateTextColor(document.getElementById('edit_program'));
        document.getElementById('editProgramModal').classList.remove('hidden');
    }

    function closeEditProgramModal() {
        document.getElementById('editProgramModal').classList.add('hidden');
    }

    document.addEventListener("DOMContentLoaded", function() {
        var el = document.getElementById('edit_program');
        if (el) {
            updateTextColor(el); // Initial color update
            el.addEventListener("input", function() { updateTextColor(this); });
            el.addEventListener("change", function() { updateTextColor(this); });
        }
    });

    function validateEditProgramForm() {
        let isValid = true;
        const program = document.getElementById('edit_program').value;
        if (!program) {
            document.getElementById('edit_programError').innerText = 'Program is required.';
            isValid = false;
        } else {
            document.getElementById('edit_programError').innerText = '';
        }
        if (isValid) {
            document.getElementById('editProgramForm').submit();
        }
    }
</script>