<div id="createObligationAdjustmentModalContainer"></div>
<!-- Adjust Obligations Modal -->
<form id="createObligationAdjustmentForm" method="POST" action="{{ route('obligations.storeObligationAdjustment', ['obligation' => $obligation->id]) }}">
    @csrf
    <div id="createObligationAdjustmentModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Adjust Obligation') }} (
                        <span class="text-blue-800 dark:text-blue-400">
                            {{ $obligation->officeAllotmentClass->offices->office_abbreviation ?? 'N/A' }} - {{ $obligation->officeAllotmentClass->allotmentClass->class ?? 'N/A' }} |
                            {{ $obligation->obr_no }}
                        </span> )
                    </h3>
                    <button type="button" onclick="closeCreateObligationAdjustmentModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="px-7 py-3 text-xs">

                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="adjustment_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="adjustment_date" autocomplete="off" id="adjustment_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="adjustment_remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="adjustment_remarks" autocomplete="off" id="adjustment_remarks" placeholder="{{ __('Remarks') }}" :value="old('adjustment_remarks')" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="remarksError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Current Obligation Adjustments Table -->
                            <div class="sm:col-span-6">
                                <h4 class="text-sm text-gray-900 dark:text-gray-200 mb-3">Current Obligation Adjustments for this Obligation</h4>
                                @if($obligation->obligationAdjustments && $obligation->obligationAdjustments->count())
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Date</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Remarks</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Program</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Account Code</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Description</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Adjustment Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @php
                                            $lastAdjustmentRemarks = null;
                                            @endphp
                                            @foreach($obligation->obligationAdjustments as $adj)
                                            <tr>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $adj->adjustment_remarks !== $lastAdjustmentRemarks ? $adj->adjustment_date : '' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $adj->adjustment_remarks !== $lastAdjustmentRemarks ? $adj->adjustment_remarks : '' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($adj->appropriation)->programs ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($adj->appropriation)->account_code ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($adj->appropriation)->description ?? '-' }}</td>
                                                <td class="px-3 py-2 text-right text-xs text-gray-700 dark:text-gray-200">{{ number_format($adj->adjustment_amount, 2) }}</td>
                                            </tr>
                                            @php
                                            $lastAdjustmentRemarks = $adj->adjustment_remarks;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900 border-t border-gray-300 dark:border-gray-700">
                                                <td colspan="5" class="px-2 py-2 text-right text-xs text-gray-900 dark:text-gray-200 font-semibold">Total Adjustment Amount:</td>
                                                <td class="px-2 py-2 text-right text-xs text-green-700 dark:text-green-400 font-bold">
                                                    {{ number_format($obligation->obligationAdjustments->sum('adjustment_amount'), 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400">No Obligation Adjustments found for this Obligation.</p>
                                @endif
                            </div>

                            <!-- Programs Table -->
                            <div class="sm:col-span-6 mb-3">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessage" class="text-red-500 text-sm hidden mb-2"></div>
                                <div class="mt-2 overflow-x-auto">
                                    <!-- Display Obligation Amounts and Appropriations in Programs Table -->
                                    @if(isset($obligationAmounts) && $obligationAmounts->isNotEmpty())
                                    <table id="programs_table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Program') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Account Code') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Description') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Allotment / Purchase Order Amount') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Amount of Obligation') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200 hidden">
                                                    {{ __('Balance from Allotment') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Adjustment Amount') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200 w-40">
                                                    {{ __('Adjusted Amount') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($obligationAmounts as $obligationAmount)
                                            @php
                                            $appropriation = $obligationAmount->appropriation;

                                            // Compute Adjusted OBR
                                            $adjustedObrAmount = $obligationAmount->obr_amount;
                                            if ($obligationAmount->obligationAdjustments && $obligationAmount->obligationAdjustments->isNotEmpty()) {
                                            $adjustedObrAmount += $obligationAmount->obligationAdjustments->sum('adjustment_amount');
                                            }

                                            // Determine current quarter
                                            $currentMonth = now()->month;
                                            if ($currentMonth >= 1 && $currentMonth <= 3) {
                                                $currentQuarter=1;
                                                } elseif ($currentMonth>= 4 && $currentMonth <= 6) {
                                                    $currentQuarter=2;
                                                    } elseif ($currentMonth>= 7 && $currentMonth <= 9) {
                                                        $currentQuarter=3;
                                                        } else {
                                                        $currentQuarter=4;
                                                        }

                                                        // Compute Total Appropriation (allotment base, only up to current quarter)
                                                        $totalAppropriation=0;
                                                        if ($appropriation) {
                                                        if ($currentQuarter>= 1) $totalAppropriation += $appropriation->quarter1 ?? 0;
                                                        if ($currentQuarter >= 2) $totalAppropriation += $appropriation->quarter2 ?? 0;
                                                        if ($currentQuarter >= 3) $totalAppropriation += $appropriation->quarter3 ?? 0;
                                                        if ($currentQuarter >= 4) $totalAppropriation += $appropriation->quarter4 ?? 0;
                                                        }

                                                        // Realignments
                                                        $realignmentTotal = 0;
                                                        foreach ($appropriation->realignments ?? [] as $realignment) {
                                                        if ($realignment->type === 'Source') {
                                                        $realignmentTotal -= $realignment->amount;
                                                        } elseif ($realignment->type === 'Recipient') {
                                                        $realignmentTotal += $realignment->amount;
                                                        }
                                                        }

                                                        // Supplementals
                                                        $supplementalTotal = 0;
                                                        foreach ($appropriation->supplementals ?? [] as $supplemental) {
                                                        if ($supplemental->type === 'Reversion') {
                                                        $supplementalTotal -= $supplemental->amount;
                                                        } elseif ($supplemental->type === 'Supplemental') {
                                                        $supplementalTotal += $supplemental->amount;
                                                        }
                                                        }

                                                        // Final Allotment
                                                        $allotment = $totalAppropriation + $realignmentTotal + $supplementalTotal;

                                                        // Balance
                                                        $balanceFromAllotment = $allotment - $adjustedObrAmount;
                                                        @endphp
                                                        <tr
                                                            data-obligation-type="{{ $obligationAmount->obligation->type ?? '' }}"
                                                            data-po-amount="{{ $obligationAmount->obligation->purchaseOrders->sum('po_amount') ?? 0 }}">

                                                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                                {{ $appropriation->programs ?? '-' }}
                                                            </td>
                                                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                                {{ $appropriation->account_code ?? '-' }}
                                                            </td>
                                                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                                {{ $appropriation->description ?? '-' }}
                                                            </td>
                                                            {{-- Show PO amount if obligation type is Purchase Request --}}
                                                            @if($obligationAmount->obligation->obr_type === 'Purchase Request')
                                                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 po-amount-cell">
                                                                {{ number_format($obligationAmount->purchaseOrders->sum('po_amount'), 2) }}
                                                            </td>
                                                            @else
                                                            {{-- Otherwise show Allotment --}}
                                                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 allotment-cell">
                                                                {{ number_format($allotment, 2) }}
                                                            </td>
                                                            @endif
                                                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                                {{ number_format($adjustedObrAmount, 2) }}
                                                            </td>
                                                            <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200 hidden">
                                                                {{ number_format($balanceFromAllotment, 2) }}
                                                            </td>
                                                            <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">
                                                                <span class="adjustment-amount">0.00</span>
                                                            </td>
                                                            <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200 w-40">
                                                                <x-form.input type="number" name="adjusted_amount[{{ $obligationAmount->id }}]" autocomplete="off" oninput="validateAmountAdjustment(this)" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" placeholder="" />
                                                                <span id="adjustmentAmountError" class="text-red-500 text-xs"></span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td colspan="6" class="px-2 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-200">Total Adjusted Amount:</td>
                                                <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400" id="adjustedAmountTotalCell">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    @else
                                    <p class="text-center text-gray-500 dark:text-gray-400">No obligation amounts found for this obligation.</p>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- Modal footer -->
                <div class="justify-center items-center mt-4 p-4 flex items-center border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateCreateObligationAdjustmentForm()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateObligationAdjustmentModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>