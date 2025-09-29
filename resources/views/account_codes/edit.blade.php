<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Account Codes') }}
            </h3>

            <!-- Right: Breadcrumb Navigation -->
            @if(isset($breadcrumb))
            <nav class="text-xs text-gray-600 dark:text-gray-300" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex items-center space-x-1 rtl:space-x-reverse">
                    @foreach ($breadcrumb as $index => $item)
                    <li>
                        @if (!empty($item['route']) && $index < count($breadcrumb) - 1)
                            <a href="{{ $item['route'] }}" class="text-gray-600 hover:underline dark:text-blue-400">
                            {{ $item['label'] }}
                            </a>
                            <span class="mx-2">/</span>
                            @else
                            <span class="text-gray-500 dark:text-gray-400">{{ $item['label'] }}</span>
                            @endif
                    </li>
                    @endforeach
                </ol>
            </nav>
            @endif
        </div>
    </x-slot>

    <div class="relative mx-auto border w-full shadow-lg rounded-md bg-white max-h-full dark:bg-gray-800 dark:border-gray-700">
        <!-- Content -->
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                    {{ __('Update / Edit') }}
                </h3>

            </div>
            <!-- Body -->
            <div class="justify-center items-center mb-4 px-7 py-3 max-w-3xl mx-auto">
                <form id="editAccountCodeForm" method="POST" action="{{ route('account_codes.update', $account_code) }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-6">

                        <!-- Code -->
                        <div class="space-y-2">
                            <x-form.label for="code" :value="__('Code')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-stream"></i></x-slot>
                                <x-form.input withicon id="code" class="block w-full" type="text" name="code" autocomplete="off" :value="old('code', $account_code->code)" required autofocus placeholder="{{ __('Code') }}" />
                            </x-form.input-with-icon-wrapper>
                            <span id="codeError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <x-form.label for="description" :value="__('Description')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-info-circle"></i></x-slot>
                                <x-form.input withicon id="description" class="block w-full" type="text" autocomplete="off" name="description" :value="old('description', $account_code->description)" required />
                            </x-form.input-with-icon-wrapper>
                            <span id="descriptionError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Class -->
                        <div class="space-y-2">
                            <x-form.label for="class" :value="__('Class')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-braille"></i></x-slot>
                                <x-form.select withicon id="class" class="block w-full" type="text" name="class" required>
                                    @foreach($allotment_classes as $allotment_class)
                                    <option value="{{ $allotment_class->class }}" {{ old('class', $account_code->class) == $allotment_class->class ? 'selected' : ''}}>
                                        {{ $allotment_class->description }}
                                    </option>
                                    @endforeach
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="classError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
            </div>
            <!-- Modal footer -->
            <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="validateForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                    <i class="fas fa-save text-xl mr-2"></i>
                    {{ __('Save') }}
                </button>
                <a href="{{ route('account_codes.index') }}" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times text-xl mr-2"></i>
                    {{ __('Back') }}
                </a>
            </div>
            </form>
        </div>
    </div>

</x-app-layout>

<script>
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

    function validateForm() {
        let isValid = true;

        const code = document.getElementById('code').value;
        const description = document.getElementById('description').value;
        const classField = document.getElementById('class').value;

        if (!code) {
            document.getElementById('codeError').innerText = 'Code is required.';
            isValid = false;
        } else {
            document.getElementById('codeError').innerText = '';
        }

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

        if (isValid) {
            document.getElementById('editAccountCodeForm').submit();
        }
    }
</script>