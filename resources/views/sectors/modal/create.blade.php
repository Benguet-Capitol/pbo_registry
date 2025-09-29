<!-- Create Sector Modal -->
<form id="createSectorForm" method="POST" action="{{ route('sectors.store') }}">
    @csrf
    <div id="createSectorModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Create Sector') }}
                    </h3>
                    <button type="button" onclick="closeCreateSectorModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Sector -->
                        <div class="space-y-2">
                            <x-form.label for="sector" :value="__('Sector')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-vector-square"></i>
                                </x-slot>
                                <x-form.input withicon id="sector" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="sector" autocomplete="off" :value="old('sector')" autofocus placeholder="{{ __('Sector') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="sectorError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Sector Code -->
                        <div class="space-y-2">
                            <x-form.label for="sector_code" :value="__('Sector Code')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-project-diagram"></i>
                                </x-slot>
                                <x-form.input withicon id="sector_code" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="sector_code" autocomplete="off" :value="old('sector_code')" autofocus placeholder="{{ __('Sector Code') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="sector_codeError" class="text-red-500 text-sm"></span>
                        </div>
                         <!-- Code -->
                        <div class="space-y-2">
                            <x-form.label for="code" :value="__('Code (For Remarks)')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-microchip"></i>
                                </x-slot>
                                <x-form.input withicon id="code" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="code" autocomplete="off" :value="old('code')" autofocus placeholder="{{ __('Code') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="codeError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateSectorForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateSectorModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function openCreateSectorModal() {
        closeAllDropdowns();
        document.getElementById('createSectorModal').classList.remove('hidden');
    }

    function closeCreateSectorModal() {
        document.getElementById('createSectorModal').classList.add('hidden');
    }

    //Checks if an input has a value and adjusts the text color accordingly
    document.addEventListener("DOMContentLoaded", function() {
        const elements = document.querySelectorAll("input, select");

        elements.forEach(element => {
            updateTextColor(element); // Check initial values

            element.addEventListener("input", function() {
                updateTextColor(this);
            });

            element.addEventListener("change", function() {
                updateTextColor(this);
            });

            element.addEventListener("focus", function() {
                updateTextColor(this);
            });
        });

        // Handle autofill values after a short delay
        setTimeout(() => {
            elements.forEach(updateTextColor);
        }, 100);

        function updateTextColor(element) {
            if (element.value.trim() !== "") {
                element.classList.remove("text-gray-500");
                element.classList.add("text-gray-900", "dark:text-gray-100");
            } else {
                element.classList.remove("text-gray-900", "dark:text-gray-100");
            }
        }
    });

    function validateCreateSectorForm() {
        let isValid = true;

        const sector = document.getElementById('sector').value;
        const sector_code = document.getElementById('sector_code').value;
        const code = document.getElementById('code').value;

        if (!sector) {
            document.getElementById('sectorError').innerText = 'Sector is required.';
            isValid = false;
        } else {
            document.getElementById('sectorError').innerText = '';
        }

        if (!sector_code) {
            document.getElementById('sector_codeError').innerText = 'Sector Code is required.';
            isValid = false;
        } else {
            document.getElementById('sector_codeError').innerText = '';
        }

        if (!code) {
            document.getElementById('codeError').innerText = 'Code is required.';
            isValid = false;
        } else {
            document.getElementById('codeError').innerText = '';
        }

        if (isValid) {
            document.getElementById('createSectorForm').submit();
        }
    }
</script>