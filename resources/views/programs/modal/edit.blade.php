<!-- Edit Program Modal -->
<form id="editProgramForm" method="POST" action="{{ route('programs.store') }}">
    @csrf
    @method('PUT')
    <div id="editProgramModal" style="display: none;" tabindex="-1" aria-labelledby="editProgramLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="editProgramLabel" class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-tasks text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Program / Project / Activity') }}
                </h3>
                <button type="button" onclick="closeEditProgramModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                <div class="grid gap-4">
                        <!-- Program -->
                        <div class="space-y-2">
                            <input type="hidden" id="program_id" name="program_id" value="{{ $program->id }}" />
                            <x-form.label for="program" :value="__('Program / Project / Activity')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-file-alt"></i>
                                </x-slot>
                                <x-form.input withicon id="edit_program" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="edit_program" autocomplete="off" :value="old('program')" autofocus placeholder="{{ __('Program / Project / Activity') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="edit_programError" class="text-red-500 text-xs"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateEditProgramForm()" class="text-amber-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-amber-600 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 dark:focus:ring-amber-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                        <i class="fas fa-sync-alt text-xl mr-1 -ml-1 w-5 h-5"></i>
                        {{ __('Update') }}
                    </button>
                    <button type="button" onclick="closeEditProgramModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
        
        const modal = document.getElementById('editProgramModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 10);
        
        updateTextColor(document.getElementById('edit_program'));
    }

    function closeEditProgramModal() {
        const modal = document.getElementById('editProgramModal');
        const modalContent = modal.querySelector('div[style*="animation"]');
        if (modalContent) {
            modalContent.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
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