<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Employees') }}
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
                <form id="editEmployeeForm" method="POST" action="{{ route('employees.update', $employee) }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-6">

                        <!-- Employee ID -->
                        <div class="space-y-2">
                            <x-form.label for="employee_id" :value="__('Employee ID')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-id-card"></i></x-slot>
                                <x-form.input
                                    withicon
                                    id="employee_id"
                                    class="block w-full"
                                    type="text"
                                    name="employee_id"
                                    :value="old('employee_id', $employee->employee_id)"
                                    autofocus
                                    placeholder="{{ __('Employee ID') }}"
                                    autocomplete="off" />
                            </x-form.input-with-icon-wrapper>
                            <span id="employeeIdError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <x-form.label for="name" :value="__('Name')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-user"></i></x-slot>
                                <x-form.input
                                    withicon
                                    id="name"
                                    class="block w-full"
                                    type="text"
                                    name="name"
                                    :value="old('name', $employee->name)"
                                    autocomplete="off" />
                            </x-form.input-with-icon-wrapper>
                            <span id="nameError" class="text-red-500 text-sm"></span>
                        </div>

                        <!-- Office -->
                        <div class="space-y-2">
                            <x-form.label for="office" :value="__('Office')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon"><i class="fas fa-qrcode"></i></x-slot>
                                <x-form.select
                                    withicon
                                    id="office"
                                    class="block w-full"
                                    type="text"
                                    name="office">
                                    <option value="PBO" {{ old('office', $employee->office) == 'PBO' ? 'selected' : '' }}>Provincial Budget Office</option>
                                    <option value="PAccO" {{ old('office', $employee->office) == 'PAccO' ? 'selected' : '' }}>Provincial Accounting Office</option>
                                    <option value="PGO" {{ old('office', $employee->office) == 'PGO' ? 'selected' : '' }}>Provincial Governor's Office</option>
                                </x-form.select>
                            </x-form.input-with-icon-wrapper>
                            <span id="officeError" class="text-red-500 text-sm"></span>
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
                <a href="{{ route('employees.index') }}" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
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

        const employeeId = document.getElementById('employee_id').value;
        const name = document.getElementById('name').value;
        const office = document.getElementById('office').value;

        if (!employeeId) {
            document.getElementById('employeeIdError').innerText = 'Employee ID is required.';
            isValid = false;
        } else {
            document.getElementById('employeeIdError').innerText = '';
        }

        if (!name) {
            document.getElementById('nameError').innerText = 'Name is required.';
            isValid = false;
        } else {
            document.getElementById('nameError').innerText = '';
        }

        if (!office) {
            document.getElementById('officeError').innerText = 'Office is required.';
            isValid = false;
        } else {
            document.getElementById('officeError').innerText = '';
        }

        if (isValid) {
            document.getElementById('editEmployeeForm').submit();
        }
    }
</script>