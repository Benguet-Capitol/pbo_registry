<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <!-- Left: Obligations Title -->
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Obligation Adjustments') }}
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
                    {{ __('Update / Edit Obligation Adjustment') }}
                </h3>
            </div>
            <!-- Body -->
            <div class="mt-2 px-7 py-3 text-xs">
                <form id="editObligationAdjustmentForm" method="POST" action="{{ route('obligation_adjustments.update', $obligationAdjustment->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $obligationAdjustment->id }}">
                    <div class="grid gap-6">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <!-- Adjustment Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="adjustment_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Adjustment Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type="date" name="adjustment_date" id="adjustment_date" :value="$obligationAdjustment->adjustment_date" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Adjustment Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="adjustment_remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Adjustment Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-info-circle"></i>
                                        </x-slot>
                                        <x-form.input withicon name="adjustment_remarks" id="adjustment_remarks" :value="$obligationAdjustment->adjustment_remarks" class="block w-full dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="remarksError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Programs Table -->
                            <div class="sm:col-span-6">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Programs Table')" />
                                <div class="mt-2 overflow-x-auto">
                                    <table id="programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Account Code') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Description') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Program') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Amount of Obligation') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Adjustment Amount') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Adjusted Amount') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($obligationAmounts as $obligationAmount)
                                            @if($obligationAmount->id == $obligationAdjustment->obligation_amounts_id)
                                            @php
                                            $appropriation = $obligationAmount->appropriation;
                                            $adjustmentAmount = $obligationAdjustment->adjustment_amount ?? 0;
                                            $adjustedAmount = $obligationAmount->obr_amount + $adjustmentAmount;
                                            @endphp
                                            <tr>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->account_code ?? 'N/A' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->description ?? 'N/A' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->programs ?? 'N/A' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ number_format($obligationAmount->obr_amount, 2) }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">
                                                    <span class="adjustment-amount">{{ number_format($adjustmentAmount, 2) }}</span>
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    <x-form.input type="number" name="adjusted_amount" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" placeholder="" :value="$adjustedAmount" />
                                                    <span class="text-red-500 text-sm"></span>
                                                </td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Footer -->
            <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="submit" onclick="validateForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                    <i class="fas fa-save text-xl mr-2"></i>
                    {{ __('Save Changes') }}
                </button>
                <a href="{{ route('obligation_adjustments.index', ['obligation_id' => $obligationAdjustment->obligation_id]) }}" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times text-xl mr-2"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <script>
        // Function to compute adjustment amount for each row
        function computeAdjustmentAmountForRow(row) {
            const obrAmountCell = row.querySelector("td:nth-child(4)");
            const adjustedAmountInput = row.querySelector("input[name^='adjusted_amount']");
            const adjustmentAmountCell = row.querySelector("td:nth-child(5)");

            if (obrAmountCell && adjustedAmountInput && adjustmentAmountCell) {
                const obrAmount = parseFloat(obrAmountCell.textContent.replace(/,/g, '')) || 0;
                const adjustedAmount = parseFloat(adjustedAmountInput.value.replace(/,/g, '')) || 0;
                const adjustmentAmount = adjustedAmount - obrAmount;

                adjustmentAmountCell.textContent = adjustmentAmount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }

        document.addEventListener('input', function(event) {
            if (event.target.name && event.target.name.startsWith('adjusted_amount')) {
                const row = event.target.closest('tr');
                if (row) {
                    computeAdjustmentAmountForRow(row);
                }
            }
        });

        function validateForm() {
            const remarks = document.getElementById('adjustment_remarks');
            const adjustmentAmounts = document.querySelectorAll("input[name^='adjusted_amount']");

            let isValid = true;

            // Validate remarks
            if (!remarks.value.trim()) {
                document.getElementById('remarksError').innerText = 'Remarks are required.';
                isValid = false;
            } else {
                document.getElementById('remarksError').innerText = '';
            }

            if (isValid) {
                console.log('Form is being submitted');
                document.getElementById('editObligationAdjustmentForm').submit();
            }
        }
    </script>
</x-app-layout>