@if(session('error'))
    <div class="text-red-500 text-sm mb-2">
        {{ session('error') }}
    </div>
@endif
<!-- Container for AJAX-loaded Disbursement Modal -->
<div id="createDisbursementModalContainer"></div>
<!-- Disbursement Modal -->
<form id="CreateDisbursementForm" method="POST" action="{{ route('obligations.storeDisbursement', ['obligation' => $obligation->id]) }}">
    @csrf
    <div id="createDisbursementModal" tabindex="1" aria-hidden="true" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-5xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                        {{ __('Add Disbursement') }} (
                        <span class="text-blue-800 dark:text-blue-400">
                            {{ $obligation->officeAllotmentClass->offices->office_abbreviation ?? 'N/A' }} - {{ $obligation->officeAllotmentClass->allotmentClass->class ?? 'N/A' }} | 
                            {{ $obligation->obr_no }}
                            </span> )
                    </h3>
                    <button type="button" onclick="closeCreateDisbursementModal()" class="text-black hover:text-gray-600 dark:text-white">
                        <i class="fas fa-times text-xl mr-2"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="mt-2 px-7 py-3 text-xs">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
                            <input type="hidden" name="purchase_order_id" value="{{ isset($purchaseOrder) ? $purchaseOrder->id : null }}">
                            <!-- DV Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="disbursement_date" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('DV / Check Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="disbursement_date" autocomplete="off" id="disbursement_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- DV Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="dv_no" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('DV / Check Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-hashtag"></i>
                                        </x-slot>
                                        <x-form.input withicon name="dv_no" autocomplete="off" id="dv_no" placeholder="{{ __('DV / Check Number') }}" :value="old('dv_no')" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="dv_noError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="sm:col-span-3">
                                <x-form.label for="status" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Status')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </x-slot>
                                        <x-form.select withicon name="status" id="status" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200">
                                            <option value="">-- Select Status --</option>
                                            <option value="Partial Payment" {{ old('status') == 'Partial Payment' ? 'selected' : '' }}>Partial Payment</option>
                                            <option value="Full Payment" {{ old('status') == 'Full Payment' ? 'selected' : '' }}>Full Payment</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="statusError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="po_remarks" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="remarks" autocomplete="off" id="emarks" placeholder="{{ __('Remarks') }}" :value="old('remarks')" class="block w-full text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Current Disbursements Table -->
                            <div class="sm:col-span-6">
                                <h4 class="text-sm text-gray-900 dark:text-gray-200 mb-3 bold">Current Disbursements for this Obligation</h4>
                                @if($obligation->disbursements && $obligation->disbursements->count())
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">DV / Check No.</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Date</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Status</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Program</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Account Code</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">Description</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">DV / Check Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @php
                                                $lastDvNo = null;
                                            @endphp
                                            @foreach($obligation->disbursements as $disbursement)
                                            <tr>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->dv_no : '' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->disbursement_date : '' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->status : ''}}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($disbursement->appropriation)->programs ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($disbursement->appropriation)->account_code ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($disbursement->appropriation)->description ?? '-' }}</td>
                                                <td class="px-3 py-2 text-right text-xs text-gray-700 dark:text-gray-200">{{ number_format($disbursement->disbursement_amount, 2) }}</td>
                                            </tr>
                                            @php
                                                $lastDvNo = $disbursement->dv_no;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900 border-t border-gray-300 dark:border-gray-700">
                                                <td colspan="5" class="px-2 py-2 text-right text-xs text-gray-900 dark:text-gray-200 font-semibold">Obligation:   <span class="text-green-700 dark:text-green-300 font-semibold">{{ number_format($totalObligationAmount, 2) }}</span></td>
                                                <td colspan="2" class="px-2 py-2 text-right text-xs text-gray-900 dark:text-gray-200 font-semibold">Total DV / Check Amount:   <span class="text-blue-700 dark:text-blue-300 font-semibold">{{ number_format($obligation->disbursements->sum('disbursement_amount'), 2) }}</span></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400">No Disbursements found for this Obligation.</p>
                                @endif
                            </div>

                            <!-- Programs Table -->
                            <div class="sm:col-span-6 mb-3">
                                <x-form.label for="programs_table" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessageDV" class="text-red-500 text-sm hidden mb-2"></div>
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
                                                    {{ ($from === 'purchase_order') ? __('Balance from Purchase Order') : __('Balance from Obligation') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('DV / Check Amount') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($obligationAmounts as $obligationAmount)
                                                @php
                                                    $adjustments = \App\Models\ObligationAdjustment::where('obligation_amounts_id', $obligationAmount->id)->sum('adjustment_amount');
                                                    $disbursements = \App\Models\Disbursement::where('obligation_amounts_id', $obligationAmount->id)->sum('disbursement_amount');
                                                    $totalPO = \App\Models\PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)->sum('po_amount');
                                                    $appropriation = $obligationAmount->appropriation;

                                                    // Determine balance based on where modal is called from
                                                    if ($from === 'purchase_order' && isset($purchaseOrder)) {
                                                        // Balance from the specific purchase order only
                                                        $balance = $purchaseOrder->po_amount - $disbursements;
                                                    } else {
                                                        // Balance from obligation (default)
                                                        $balance = (($obligationAmount->obr_amount - $disbursements) + $adjustments);
                                                    }
                                                @endphp
                                            <tr>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->programs ?? '-' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->account_code ?? '-' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    {{ $appropriation->description ?? '-' }}
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-900 dark:text-gray-200">
                                                    <span class="adjustment-amount">{{ number_format($balance, 2) }}</span>
                                                </td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">
                                                    <x-form.input type="number" name="disbursement_amount[{{ $obligationAmount->id }}]" min="0" step="0.01" autocomplete="off" class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs" placeholder="" oninput="validateDisbursementAmount(this)" data-balance="{{ $balance  }}" />
                                                    <span class="text-red-500 text-xs"></span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50 dark:bg-gray-900">
                                                <td colspan="4" class="px-2 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-200">Total DV / Check Amount:</td>
                                                <td class="px-2 py-2 text-right text-xs font-bold text-green-700 dark:text-green-400" id="dvAmountTotalCell">0.00</td>
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
                <div class="justify-center items-center p-4 flex border-t border-gray-200 rounded-b dark:border-gray-600">
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    <button type="button" onclick="validateFormCreateDisbursement()" class="mr-1 text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-900">
                        <i class="fas fa-save text-xl mr-2"></i>
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeCreateDisbursementModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                        <i class="fas fa-times text-xl mr-2"></i>
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>