@php
    $pboTitles = ['Provincial Budget Officer', 'Acting Provincial Budget Officer', 'OIC, Provincial Budget Officer'];
@endphp
<!-- General (not tied to a single field) validation errors -->
<p id="{{ $prefix }}_general_error" class="hidden mb-3 text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-md px-3 py-2"></p>
<div class="grid gap-3">
    <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">

        <!-- Office and Allotment Class -->
        <div class="sm:col-span-3 relative">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
            <div class="mt-2">
                <x-form.input
                    type="text"
                    id="{{ $prefix }}_office_allotment_class"
                    placeholder="{{ __('Office and Allotment Class') }}"
                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                    oninput="AroForm.filterOfficeAllotmentClasses('{{ $prefix }}')"
                    autocomplete="off" />
                <input type="hidden" name="office_allotment_classes_id" id="{{ $prefix }}_office_allotment_classes_id" />
                <!-- Set by openEditAroModal() so balance lookups exclude this ARO's own (still-persisted) items; left empty for Create. -->
                <input type="hidden" id="{{ $prefix }}_aro_id" value="" />
                <div id="{{ $prefix }}_office_allotment_class_dropdown" class="absolute left-0 top-full w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50 mt-1"></div>
                <p id="{{ $prefix }}_office_allotment_class_error" class="hidden mt-1 text-[11px] text-red-600 dark:text-red-400">{{ __('Please select an Office and Allotment Class.') }}</p>
            </div>
        </div>

        <!-- Date of Issue -->
        <div class="sm:col-span-3">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Date of Issue')" />
            <div class="mt-2">
                <x-form.input type="date" name="date_of_issue" id="{{ $prefix }}_date_of_issue" :value="now()->format('Y-m-d')"
                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200"
                    oninput="AroForm.onDateOfIssueChange('{{ $prefix }}')" />
                <p id="{{ $prefix }}_date_of_issue_error" class="hidden mt-1 text-[11px] text-red-600 dark:text-red-400">{{ __('Date of Issue is required.') }}</p>
            </div>
        </div>

        <!-- Fund Source -->
        <div class="sm:col-span-2">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Fund Source')" />
            <div class="mt-2">
                <x-form.select name="fund_source" id="{{ $prefix }}_fund_source" class="block w-full text-xs" onchange="AroForm.onFundSourceChange('{{ $prefix }}')">
                    <option value="Annual Budget">Annual Budget</option>
                    <option value="Supplemental Budget">Supplemental Budget</option>
                    <option value="Reenacted Budget">Reenacted Budget</option>
                </x-form.select>
            </div>
        </div>

        <!-- SB No. (matches the Supplementals module's "SB No." / basis_no field) -->
        <div class="sm:col-span-2" id="{{ $prefix }}_supplemental_no_wrapper" style="display:none;">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('SB No.')" />
            <div class="mt-2">
                <x-form.input type="text" name="supplemental_no" id="{{ $prefix }}_supplemental_no" placeholder="{{ __('SB No.') }}"
                    class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" autocomplete="off"
                    onchange="AroForm.onSupplementalNoChange('{{ $prefix }}')" />
                <p id="{{ $prefix }}_supplemental_no_error" class="hidden mt-1 text-[11px] text-red-600 dark:text-red-400">{{ __('Please enter the SB No.') }}</p>
            </div>
        </div>

        <!-- PPA Code (office default — each Account Code row below can override this to split the ARO into multiple PPA Code groups) -->
        <div class="sm:col-span-2">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Default PPA Code')" />
            <div class="mt-2">
                <x-form.input type="text" name="ppa_code" id="{{ $prefix }}_ppa_code" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" autocomplete="off" />
            </div>
        </div>

        <!-- ARO No. Preview -->
        <div class="sm:col-span-6">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('ARO No. (auto-generated on save)')" />
            <div class="mt-2">
                <x-form.input type="text" id="{{ $prefix }}_aro_no_preview" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-400" readonly />
            </div>
        </div>

        <!-- Account Codes Table -->
        <div class="sm:col-span-6">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200 mb-2" :value="__('Account Codes')" />
            <p id="{{ $prefix }}_account_codes_error" class="hidden mb-2 text-xs text-red-600 dark:text-red-400">
                {{ __('Please check at least one Account Code.') }}
            </p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200 w-10">
                                <input type="checkbox" id="{{ $prefix }}_select_all_accounts" title="{{ __('Select All') }}"
                                    onchange="AroForm.toggleSelectAll('{{ $prefix }}', this)">
                            </th>
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200 w-64">{{ __('PPA Code') }}</th>
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('Account Code') }}</th>
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('PPA Description') }}</th>
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('Balance') }}</th>
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('Authorized Appropriation') }}</th>
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('For Later Release') }}</th>
                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">{{ __('This Release') }}</th>
                        </tr>
                    </thead>
                    <tbody id="{{ $prefix }}_items_tbody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr><td colspan="8" class="text-center text-xs text-gray-500 py-4">Select an Office and Allotment Class first.</td></tr>
                    </tbody>
                    <tfoot class="bg-gray-100 dark:bg-gray-900 font-semibold border-t-2 border-gray-300 dark:border-gray-600">
                        <tr>
                            <td colspan="5" class="px-2 py-2 text-xs text-right">{{ __('Total (Selected Account Codes)') }}</td>
                            <td class="px-2 py-2 text-xs text-right" id="{{ $prefix }}_total_authorized">0.00</td>
                            <td class="px-2 py-2 text-xs text-right" id="{{ $prefix }}_total_for_later">0.00</td>
                            <td class="px-2 py-2 text-xs text-right" id="{{ $prefix }}_total_this_release">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Signatories: Provincial Budget Officer -->
        <div class="sm:col-span-3">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Provincial Budget Officer Signatory')" />
            <div class="mt-2">
                <x-form.select name="provincial_budget_officer_id" id="{{ $prefix }}_pbo_signatory" class="block w-full text-xs">
                    <option value="">Select Employee</option>
                </x-form.select>
                <p id="{{ $prefix }}_pbo_signatory_error" class="hidden mt-1 text-[11px] text-red-600 dark:text-red-400">{{ __('Please select a Provincial Budget Officer Signatory.') }}</p>
            </div>
        </div>
        <div class="sm:col-span-3">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Provincial Budget Officer Designation')" />
            <div class="mt-2">
                <x-form.select name="provincial_budget_officer_title" id="{{ $prefix }}_pbo_designation" class="block w-full text-xs">
                    @foreach ($pboTitles as $title)
                        <option value="{{ $title }}">{{ $title }}</option>
                    @endforeach
                </x-form.select>
            </div>
        </div>

        <!-- Signatories: Local Chief Executive -->
        <div class="sm:col-span-3">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Local Chief Executive Signatory')" />
            <div class="mt-2">
                <input type="hidden" name="provincial_governor_id" id="{{ $prefix }}_lce_signatory_id" value="">
                <!-- Auto-resolved from the employee whose designation is "Provincial Governor" -->
                <x-form.input type="text" id="{{ $prefix }}_lce_signatory_auto" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-400" readonly placeholder="{{ __('No employee found with Provincial Governor designation') }}" />
                <x-form.input type="text" name="provincial_governor_name" id="{{ $prefix }}_lce_signatory_manual" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" style="display:none;" placeholder="{{ __('Type Acting Governor Name') }}" autocomplete="off" />
                <p id="{{ $prefix }}_lce_signatory_error" class="hidden mt-1 text-[11px] text-red-600 dark:text-red-400"></p>
            </div>
        </div>
        <div class="sm:col-span-3">
            <x-form.label class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Local Chief Executive Designation')" />
            <div class="mt-2">
                <x-form.select name="provincial_governor_title" id="{{ $prefix }}_lce_designation" class="block w-full text-xs" onchange="AroForm.onGovernorTitleChange('{{ $prefix }}')">
                    <option value="Provincial Governor">Provincial Governor</option>
                    <option value="Acting Provincial Governor">Acting Provincial Governor</option>
                </x-form.select>
            </div>
        </div>
    </div>
</div>
