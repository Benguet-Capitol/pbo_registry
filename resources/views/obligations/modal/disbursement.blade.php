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
    <div id="createDisbursementModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="flex flex-col max-h-[90vh] w-full max-w-5xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl animate-scaleInUp">
            <!-- Modal header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-b-2 border-blue-200 dark:border-blue-700 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <i class="fas fa-plus-circle text-blue-600 dark:text-blue-300 text-xl"></i>
                    <div>
                        <h3 class="text-base leading-6 font-semibold text-blue-900 dark:text-blue-100">
                            {{ __('Add Disbursement') }}
                        </h3>
                        <span class="text-lg text-blue-700 dark:text-blue-300 font-semibold">
                            {{ $obligation->officeAllotmentClass->offices->office_abbreviation ?? 'N/A' }} - {{ $obligation->officeAllotmentClass->allotmentClass->class ?? 'N/A' }} | {{ $obligation->obr_no ?? 'N/A' }}
                        </span>
                    </div>
                </div>
                <button type="button" onclick="closeCreateDisbursementModal()" class="text-blue-600 dark:text-blue-300 hover:text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-full p-2 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal body -->
            <div class="overflow-y-auto flex-1 max-h-[calc(90vh-280px)] px-6 py-4 text-xs">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">
                            <input type="hidden" name="obligation_id" value="{{ $obligation->id }}">
                            <input type="hidden" name="purchase_order_id" value="{{ isset($purchaseOrder) ? $purchaseOrder->id : null }}">
                            <input type="hidden" name="from" value="{{ request('from') ?? 'obligation' }}">
                            <!-- Filter Parameters -->
                            <input type="hidden" name="page" value="{{ request('page') ?? '' }}">
                            <input type="hidden" name="search" value="{{ request('search') ?? '' }}">
                            <input type="hidden" name="search_column" value="{{ request('search_column') ?? '' }}">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by') ?? '' }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order') ?? '' }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page') ?? '' }}">
                            <input type="hidden" name="year1" value="{{ request('year1') ?? '' }}">
                            <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') ?? '' }}">
                            <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') ?? '' }}">
                            <input type="hidden" name="fund_filter" value="{{ request('fund_filter') ?? '' }}">
                            <!-- DV Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="disbursement_date" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('DV / Check Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input withicon type='date' name="disbursement_date" autocomplete="off" id="disbursement_date" placeholder="{{ __('Date') }}" :value="now()->format('Y-m-d')" max="{{ now()->format('Y-m-d') }}" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- DV Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="dv_no" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('DV / Check Number')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-hashtag"></i>
                                        </x-slot>
                                        <x-form.input withicon name="dv_no" autocomplete="off" id="dv_no" placeholder="{{ __('DV / Check Number') }}" :value="old('dv_no')" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="dv_noError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="sm:col-span-3">
                                <x-form.label for="status" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Status')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </x-slot>
                                        <x-form.select withicon name="status" id="status" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200">
                                            <option value="">-- Select Status --</option>
                                            <option value="Partial Payment" {{ old('status') == 'Partial Payment' ? 'selected' : '' }}>Partial Payment</option>
                                            <option value="Full Payment" {{ old('status') == 'Full Payment' ? 'selected' : '' }}>Full Payment</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="statusError" class="text-red-500 text-xs"></span>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-3">
                                <x-form.label for="po_remarks" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="remarks" autocomplete="off" id="emarks" placeholder="{{ __('Remarks') }}" :value="old('remarks')" class="block w-full text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Current Disbursements Table -->
                            <div class="sm:col-span-6">
                                <h4 class="text-sm text-gray-900 dark:text-gray-200 mb-3 bold">Current Disbursements for this Obligation</h4>
                                @php
                                    // Determine if this is a complex obligation with multiple POs per OA
                                    $filteredDisbursements = $obligation->disbursements;
                                    
                                    if ($from === 'purchase_order' && isset($purchaseOrder)) {
                                        // Get all PO IDs with the current po_number across all OAs
                                        $poIdsForThisNumber = \App\Models\PurchaseOrder::where('po_number', $purchaseOrder->po_number)
                                            ->pluck('id');
                                        
                                        // Check if any OA has multiple different PO numbers
                                        $hasComplexOAs = false;
                                        foreach ($obligationAmounts as $oa) {
                                            $totalPOsForOA = \App\Models\PurchaseOrder::where('obligation_amounts_id', $oa->id)->count();
                                            if ($totalPOsForOA > 1) {
                                                $hasComplexOAs = true;
                                                break;
                                            }
                                        }
                                        
                                        if ($hasComplexOAs) {
                                            // Complex: only show disbursements linked to this po_number
                                            $filteredDisbursements = $obligation->disbursements->whereIn('purchase_order_id', $poIdsForThisNumber);
                                        }
                                        // Simple: show all disbursements (they all belong to this PO anyway)
                                    }
                                @endphp
                                @if($filteredDisbursements && $filteredDisbursements->count())
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
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">Remarks</th>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">DV / Check Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @php
                                                $lastDvNo = null;
                                            @endphp
                                            @foreach($filteredDisbursements as $disbursement)
                                            <tr>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->dv_no : '' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->disbursement_date : '' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ $disbursement->dv_no !== $lastDvNo ? $disbursement->status : ''}}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($disbursement->appropriation)->programs ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($disbursement->appropriation)->account_code ?? '-' }}</td>
                                                <td class="px-2 py-2 text-center text-xs text-gray-700 dark:text-gray-200">{{ optional($disbursement->appropriation)->description ?? '-' }}</td>
                                                <td class="px-3 py-2 text-right text-xs text-gray-700 dark:text-gray-200">{{ $disbursement->remarks ?? '-' }}</td>
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
                                                <td colspan="3" class="px-2 py-2 text-right text-xs text-gray-900 dark:text-gray-200 font-semibold">Total DV / Check Amount:   <span class="text-blue-700 dark:text-blue-300 font-semibold">{{ number_format($filteredDisbursements->sum('disbursement_amount'), 2) }}</span></td>
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
                                                    $allDisbursements = \App\Models\Disbursement::where('obligation_amounts_id', $obligationAmount->id)->sum('disbursement_amount');
                                                    $totalPO = \App\Models\PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)->sum('po_amount');
                                                    $appropriation = $obligationAmount->appropriation;

                                                    // Determine balance based on where modal is called from
                                                    if ($from === 'purchase_order' && isset($purchaseOrder)) {
                                                        // Get PO amount for THIS specific obligation amount with the same po_number
                                                        $poAmountForThisOA = \App\Models\PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)
                                                            ->where('po_number', $purchaseOrder->po_number)
                                                            ->sum('po_amount');
                                                        
                                                        // Count total POs for this OA vs POs with current po_number
                                                        $totalPOsForOA = \App\Models\PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)->count();
                                                        $posWithThisNumber = \App\Models\PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)
                                                            ->where('po_number', $purchaseOrder->po_number)
                                                            ->count();
                                                        
                                                        if ($totalPOsForOA > 1 && $posWithThisNumber < $totalPOsForOA) {
                                                            // Multiple different PO numbers for this OA: strictly deduct only disbursements for this po_number
                                                            $poIdsWithThisNumber = \App\Models\PurchaseOrder::where('obligation_amounts_id', $obligationAmount->id)
                                                                ->where('po_number', $purchaseOrder->po_number)
                                                                ->pluck('id');
                                                            
                                                            $disbursementsForThisPN = \App\Models\Disbursement::where('obligation_amounts_id', $obligationAmount->id)
                                                                ->whereIn('purchase_order_id', $poIdsWithThisNumber)
                                                                ->sum('disbursement_amount');
                                                        } else {
                                                            // Only this po_number for this OA: deduct all disbursements
                                                            $disbursementsForThisPN = $allDisbursements;
                                                        }
                                                        
                                                        $balance = ($poAmountForThisOA ?? 0) - $disbursementsForThisPN;
                                                    } else {
                                                        // Balance from obligation (default)
                                                        $balance = (($obligationAmount->obr_amount - $allDisbursements) + $adjustments);
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
            <div class="flex justify-end gap-3 p-4 border-t-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 rounded-b-lg flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="if(!isSubmittingDisbursement) { validateFormCreateDisbursement(); return false; } return false;" id="submitDisbursementBtn" class="text-blue-600 dark:text-blue-400 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Save') }}
                </button>
                <button type="button" onclick="closeCreateDisbursementModal()" class="text-gray-600 dark:text-gray-400 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Cancel') }}
                </button>
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
</style>

<script>
    // Prevent multiple submissions
    let isSubmittingDisbursement = false;

    function closeCreateDisbursementModal() {
        const modal = document.getElementById('createDisbursementModal');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    // Real-time DV number validation
    function validateDvNumberInput() {
        const dvNoField = document.getElementById('dv_no');
        const dvNoError = document.getElementById('dv_noError');
        
        if (!dvNoField || !dvNoError) return;
        
        const dvNo = dvNoField.value.trim();
        
        // Clear error if field is empty
        if (dvNo === '') {
            dvNoError.innerText = '';
            dvNoField.classList.remove('border-red-500');
            return;
        }
        
        // Get the obligation year - search within the current form context
        const form = document.getElementById('CreateDisbursementForm');
        const obligationIdInput = form ? form.querySelector('input[name="obligation_id"]') : document.querySelector('input[name="obligation_id"]');
        const obligationId = obligationIdInput?.value;
        
        if (!obligationId) {
            dvNoError.innerText = 'Obligation ID not found';
            dvNoError.classList.add('text-red-500');
            console.warn('Obligation ID not found in form');
            return;
        }
        
        // Show validating state
        dvNoField.classList.remove('border-red-500');
        dvNoError.innerText = 'Validating...';
        dvNoError.classList.add('text-gray-500');
        dvNoError.classList.remove('text-red-500');
        
        // Fetch year and check DV uniqueness
        fetch(`/api/obligations/${obligationId}/year`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
            })
            .then(yearData => {
                if (!yearData.year) {
                    throw new Error('No year data returned');
                }
                return fetch('{{ route("disbursements.checkDvNumber") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        dv_no: dvNo,
                        year: yearData.year
                    })
                });
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.exists) {
                    dvNoError.innerHTML = data.message;
                    dvNoError.classList.remove('text-gray-500');
                    dvNoError.classList.add('text-red-500');
                    dvNoField.classList.add('border-red-500');
                    // Scroll error into view and focus on the field
                    dvNoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    dvNoField.focus();
                } else {
                    dvNoError.innerText = '';
                    dvNoError.classList.remove('text-gray-500');
                    dvNoError.classList.remove('text-red-500');
                    dvNoField.classList.remove('border-red-500');
                }
            })
            .catch(error => {
                console.error('Error validating DV number:', error);
                dvNoError.innerText = 'Error validating DV number: ' + error.message;
                dvNoError.classList.remove('text-gray-500');
                dvNoError.classList.add('text-red-500');
                // Scroll error into view and focus on the field
                dvNoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                dvNoField.focus();
            });
    }

    // Attach event listener when modal content is loaded
    // Use a more robust method to attach listeners to dynamically loaded content
    document.addEventListener('DOMContentLoaded', function() {
        const dvNoField = document.getElementById('dv_no');
        if (dvNoField && !dvNoField._validationListenerAttached) {
            dvNoField.addEventListener('blur', validateDvNumberInput);
            dvNoField.addEventListener('change', validateDvNumberInput);
            dvNoField._validationListenerAttached = true;
        }
    });

    // Also try to attach immediately in case DOM is already loaded
    const dvNoField = document.getElementById('dv_no');
    if (dvNoField && !dvNoField._validationListenerAttached) {
        dvNoField.addEventListener('blur', validateDvNumberInput);
        dvNoField.addEventListener('change', validateDvNumberInput);
        dvNoField._validationListenerAttached = true;
    }
</script>