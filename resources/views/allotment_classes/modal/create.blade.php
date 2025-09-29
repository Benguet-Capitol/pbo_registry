<!-- Create AllotmentClass Modal -->
<form id="createAllotmentClassForm" method="POST" action="{{ route('allotment_classes.store') }}">
    @csrf
    <div id="createAllotmentClassModal" tabindex="1" aria-hidden="true" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Create Allotment Class') }}
                    </h3>
                    <button type="button" onclick="closeCreateAllotmentClassModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="mt-2 px-7 py-3">
                    <div class="grid gap-3">
                        <!-- Class -->
                        <div class="space-y-2">
                            <x-form.label for="class" :value="__('Class')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-torah"></i>
                                </x-slot>
                                <x-form.input withicon id="class" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="class" :value="old('class')" autocomplete="off" autofocus placeholder="{{ __('Class') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="classError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Description -->
                        <div class="space-y-2">
                            <x-form.label for="description" :value="__('Description')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-info-circle"></i>
                                </x-slot>
                                <x-form.input withicon id="description" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="description" autocomplete="off" :value="old('description')" autofocus placeholder="{{ __('Description') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="descriptionError" class="text-red-500 text-sm"></span>
                        </div>
                        <!-- Category -->
                        <div class="space-y-2">
                            <x-form.label for="category" :value="__('Category')" class="block text-sm font-medium text-gray-900 dark:text-gray-200" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <i class="fas fa-stream"></i>
                                </x-slot>
                                <x-form.select withicon id="category" class="block w-full dark:bg-gray-800 dark:text-gray-200" type="text" name="category" :value="old('category')" placeholder="{{ __('Category') }}">
                                    <option value="">{{ __('Select Category') }}</option>
                                    <option value="Current">Current</option>
                                    <option value="Continuing">Continuing</option>
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="categoryError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateAllotmentClassForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateAllotmentClassModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function openCreateAllotmentClassModal() {
        closeAllDropdowns();
        document.getElementById('createAllotmentClassModal').classList.remove('hidden');
    }

    function closeCreateAllotmentClassModal() {
        document.getElementById('createAllotmentClassModal').classList.add('hidden');
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

    function validateCreateAllotmentClassForm() {
        let isValid = true;

        const category = document.getElementById('category').value;
        const description = document.getElementById('description').value;
        const classField = document.getElementById('class').value;

        if (!description) {
            document.getElementById('descriptionError').innerText = 'Description is required.';
            isValid = false;
        } else {
            document.getElementById('descriptionError').innerText = '';
        }

        if (!classField) {
            document.getElementById('classError').innerText = 'Class is required.';
            isValid = false;
        } else {
            document.getElementById('classError').innerText = '';
        }

        if (!category) {
            document.getElementById('categoryError').innerText = 'Category is required.';
            isValid = false;
        } else {
            document.getElementById('categoryError').innerText = '';
        }

        if (isValid) {
            document.getElementById('createAllotmentClassForm').submit();
        }
    }
</script>