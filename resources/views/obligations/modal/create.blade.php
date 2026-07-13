<!-- Create Obligations Modal -->
<form id="createObligationsForm" method="POST" action="{{ route('obligations.store') }}">
    @csrf
    <input type="hidden" name="page" value="{{ request('page') }}">
    <input type="hidden" name="year1" value="{{ request('year1') }}">
    <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
    <input type="hidden" name="obr_type_filter" value="{{ request('obr_type_filter') }}">
    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="search_column" value="{{ request('search_column') }}">
    <input type="hidden" name="group_filter" value="{{ request('group_filter') }}">
    <input type="hidden" name="fund_type_filter" value="{{ request('fund_type_filter') }}">
    <input type="hidden" name="fund_filter" value="{{ request('fund_filter') }}">
    <input type="hidden" name="office_filter" value="{{ request('office_filter') }}">
    <input type="hidden" name="allotment_class_filter" value="{{ request('allotment_class_filter') }}">
    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
    <input type="hidden" name="from_dashboard" id="from_dashboard" value="0">
    <input type="hidden" name="preselected_class" id="preselected_class" value="0">
    <input type="hidden" name="preselected_appropriation_id" id="preselected_appropriation_id" value="">

    <div id="createModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-5xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out; max-height: 90vh;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-plus-circle text-sm text-blue-600 dark:text-blue-400"></i>
                    {{ __('Create Obligation') }}
                </h3>
                <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto flex-1" style="max-height: calc(90vh - 280px);">
                    <div class="grid gap-3">

                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-6">

                            <!-- Office and Allotment Class -->
                            <div class="sm:col-span-3 relative">
                                <x-form.label for="office_allotment_class" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Office and Allotment Class')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-laptop-house"></i>
                                        </x-slot>
                                        <x-form.input
                                            withicon
                                            type="text"
                                            name="office_allotment_class"
                                            id="office_allotment_class"
                                            placeholder="{{ __('Office and Allotment Class') }}"
                                            class="block w-full bg-white text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-200"
                                            oninput="filterOfficeAllotmentClasses()"
                                            autocomplete="off" />
                                    </x-form.input-with-icon-wrapper>
                                    <!-- Hidden input to store the selected ID -->
                                    <input type="hidden" name="office_allotment_class_id" id="office_allotment_class_id" />
                                    <div id="OfficeAllotmentClassDropdown" class="text-xs absolute w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-50">
                                        <!-- Suggestions appear here -->
                                    </div>
                                    <span id="OfficeAllotmentClassError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Date -->
                            <div class="sm:col-span-3">
                                <x-form.label for="obr_date" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Date')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-calendar"></i>
                                        </x-slot>
                                        <x-form.input 
                                            withicon 
                                            type='date' 
                                            name="obr_date" 
                                            autocomplete="off" 
                                            id="obr_date" 
                                            placeholder="{{ __('Date') }}" 
                                            value="{{ $selectedYear == date('Y') ? now()->format('Y-m-d') : $selectedYear . '-12-31' }}" 
                                            max="{{ $selectedYear == date('Y') ? now()->format('Y-m-d') : $selectedYear . '-12-31' }}" 
                                            class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Obligation Type -->
                            <div class="sm:col-span-3">
                                <x-form.label for="obr_type" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Obligation Type')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-arrow-up-right-dots"></i>
                                        </x-slot>
                                        <x-form.select withicon id="obr_type" class="block w-full text-xs" type="text" name="obr_type" placeholder="{{ __('Obligation Type') }}">
                                            <option value="Regular">{{ __('Regular') }}</option>
                                            <option value="Purchase Request">{{ __('Purchase Request') }}</option>
                                            <option value="Project/Contract">{{ __('Project/Contract') }}</option>
                                        </x-form.select>
                                    </x-form.input-with-icon-wrapper>
                                    <span id="obrTypeError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Number -->
                            <div class="sm:col-span-3">
                                <x-form.label for="obr_no" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('OBR No.')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-list-ol"></i>
                                        </x-slot>
                                        <x-form.input withicon type='text' name="obr_no" autocomplete="off" id="obr_no" placeholder="{{ __('OBR No.') }}" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="obrNoError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Particulars -->
                            <div class="sm:col-span-6">
                                <x-form.label for="particulars" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Particulars')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-align-justify"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="particulars" autocomplete="off" id="particulars" placeholder="{{ __('Particulars') }}" :value="old('particulars')" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                    <span id="particularsError" class="text-red-500 text-sm"></span>
                                </div>
                            </div>
                            <!-- Remarks -->
                            <div class="sm:col-span-6">
                                <x-form.label for="remarks" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Remarks')" />
                                <div class="mt-2">
                                    <x-form.input-with-icon-wrapper>
                                        <x-slot name="icon">
                                            <i class="fas fa-circle-info"></i>
                                        </x-slot>
                                        <x-form.textarea withicon name="remarks" autocomplete="off" id="remarks" placeholder="{{ __('Remarks') }}" :value="old('remarks')" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" />
                                    </x-form.input-with-icon-wrapper>
                                </div>
                            </div>
                            <!-- Programs Table -->
                            <div class="sm:col-span-6">
                                <x-form.label for="programs_table" class="block text-xs font-medium text-gray-900 dark:text-gray-200" :value="__('Accounts')" />
                                <!-- Message Placeholder -->
                                <div id="tableMessage" class="text-red-500 text-xs hidden mb-2"></div>
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
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200" id="programColumnHeader">
                                                    {{ __('Program') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Balance from Allotment') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Amount of Obligation') }}
                                                </th>
                                                <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-gray-200">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 ">
                                            <!-- Appropriations rows will be populated here by JavaScript -->
                                        </tbody>
                                        <!-- Fixed Total Row -->
                                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <td colspan="4" class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    {{ __('Total Obligation') }}
                                                </td>
                                                <td class="px-2 py-2 text-right text-xs font-medium text-gray-900 dark:text-gray-200">
                                                    <span id="totalObligation" class="block w-full font-bold text-green-700 dark:text-green-400 text-right text-xs">0.00</span>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <!-- Add Button for Dynamic Rows -->
                                <div class="mt-4">
                                    <button type="button" onclick="addRow()" class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                                        <i class="fas fa-plus text-sm mr-2"></i>
                                        {{ __('Add Row') }}
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
            </div>

            <!-- Modal footer -->
            <div class="justify-center items-center mt-0 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="handleSaveObligation()" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Save') }}
                </button>
                <button type="button" onclick="closeCreateModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6">
        <h2 class="text-lg font-semibold text-red-600 mb-4">Confirm Deletion</h2>
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-6">
            Are you sure you want to delete this row? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-2">
            <button id="confirmDeleteBtn" class="mr-1 text-red-600 inline-flex items-center hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                <i class="fas fa-trash mr-1 -ml-1"></i> Delete
            </button>
            <button id="cancelDeleteBtn" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                <i class="fas fa-times mr-1 -ml-1"></i> Cancel
            </button>
        </div>
    </div>
</div>

<script>
    // 1. Open/Close Modal Functions
function openCreateModal(officeAllotmentClassId = null, appropriationId = null, accountCode = null) {
    // Close any open dropdowns
    if (typeof closeAllDropdowns === 'function') {
        closeAllDropdowns();
    }
    
    const createModal = document.getElementById('createModal');
    if (!createModal) {
        console.error('Create modal element not found');
        return;
    }

    // Set from_dashboard flag if window.isFromDashboard is true
    const fromDashboardField = document.querySelector('input[name="from_dashboard"]');
    if (fromDashboardField && window.isFromDashboard) {
        fromDashboardField.value = '1';
    }

    createModal.style.display = 'flex';
    createModal.setAttribute('aria-hidden', 'false');
    
    
    // Update date field based on current year
    if (typeof updateDateFieldBasedOnYear === 'function') {
        updateDateFieldBasedOnYear();
    }

    // Ensure existing OBR numbers are fetched/populated BEFORE displaying modal
    const loadPromise = (typeof loadExistingObrNumbers === 'function') 
        ? loadExistingObrNumbers() 
        : Promise.resolve([]);
    
    loadPromise.then(() => {
        // Now proceed with opening modal after data is loaded
    
    // Clear form fields when reopening modal (except preselected fields)
    // This applies when we have a preselectedClassId but should reset for new entry
    if (officeAllotmentClassId && !appropriationId) {
        // Clear additional obligation amount rows, keep only first
        const tableBody = document.querySelector('#programs_table tbody');
        if (tableBody) {
            const rows = tableBody.querySelectorAll('tr');
            if (rows.length > 1) {
                for (let i = 1; i < rows.length; i++) {
                    rows[i].remove();
                }
            }
        }
        
        // Clear other fields
        const obrDateField = document.getElementById('obr_date');
        if (obrDateField) {
            const yearFilter = document.getElementById('year1');
            const selectedYear = yearFilter ? yearFilter.value : new Date().getFullYear();
            const currentYear = new Date().getFullYear();
            
            if (selectedYear == currentYear) {
                obrDateField.value = new Date().toISOString().split('T')[0];
            } else {
                obrDateField.value = selectedYear + '-12-31';
            }
        }
        
        const particularsField = document.getElementById('particulars');
        if (particularsField) particularsField.value = '';
        
        const remarksField = document.getElementById('remarks');
        if (remarksField) remarksField.value = '';
        
        const obrTypeSelect = document.getElementById('obr_type');
        if (obrTypeSelect) obrTypeSelect.value = '';
        
        // Clear account code and amount fields in first row
        const firstRow = document.querySelector('#programs_table tbody tr');
        if (firstRow) {
            firstRow.querySelectorAll('[name="account_code[]"], [name="amount_of_obligation[]"]').forEach(field => field.value = '');
        }
    }
    
    // Set preselection flags
    const preselectedInput = document.getElementById('preselected_class');
    const preselectedAppropriationInput = document.getElementById('preselected_appropriation_id');
    
    // Set preselected_class to '1' if we have either an officeAllotmentClassId OR an appropriationId
    if (preselectedInput && (officeAllotmentClassId || appropriationId)) {
        preselectedInput.value = '1';
    } else if (preselectedInput) {
        preselectedInput.value = '0';
    }
    
    if (preselectedAppropriationInput && appropriationId) {
        preselectedAppropriationInput.value = appropriationId;
    }
    
    // Find and select the office allotment class
    if (officeAllotmentClassId) {
        const selectedClass = officeAllotmentClasses.find(oac => oac.id == officeAllotmentClassId);
        if (selectedClass) {
            const classInput = document.getElementById('office_allotment_class');
            const classIdInput = document.getElementById('office_allotment_class_id');
            
            if (classInput && classIdInput) {
                classInput.value = selectedClass.name;
                classIdInput.value = selectedClass.id;
                selectedOfficeAllotmentClass = selectedClass;
                
                // Update obligation types based on the selected class
                updateObligationTypes(selectedClass.class);
                
                // Generate OBR number
                generateObrNumber();
                
                // Setup filtering for all class types
                if (!appropriationId) {
                    // Only setup account code filtering if we don't have a pre-populated appropriation
                    if (selectedClass.class === 'PS') {
                        populateAppropriationsTable(selectedClass.id);
                    } else {
                        setupAccountCodeFiltering(selectedClass.id);
                    }
                }
            }
        }
    }
    
    // Pre-populate the first row if appropriationId is provided
    if (appropriationId && accountCode) {
        setTimeout(() => {
            // Find the appropriation details
            const appropriation = appropriations.find(app => 
                app.id == appropriationId || app.account_code == accountCode
            );
            
            if (appropriation) {
                // Clear existing rows
                const tableBody = document.querySelector('#programs_table tbody');
                tableBody.innerHTML = '';
                
                // Create pre-populated readonly first row
                const row = createPrePopulatedRow(appropriation);
                tableBody.appendChild(row);
                
                // Show add row button to allow adding more rows with filtering
                const addRowBtn = document.querySelector('button[onclick="addRow()"]');
                if (addRowBtn) addRowBtn.style.display = 'inline-flex';
                
                // Calculate total obligation
                calculateTotalObligation();
                
                // Focus on the amount field for immediate data entry
                const amountField = tableBody.querySelector('[name="amount_of_obligation[]"]');
                if (amountField) {
                    amountField.focus();
                }
            }
        }, 300);
    }
    });
}

// Add this function to handle modal reopening after successful save from dashboard
document.addEventListener('DOMContentLoaded', function() {
    @if(session('reopen_modal') && session('preselected_class_id'))
        // Reopen modal with preselected class after successful save
        setTimeout(function() {
            openCreateModal({{ session('preselected_class_id') }});
        }, 500);
    @endif
});

function closeCreateModal() {
    const modal = document.getElementById('createModal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
}

// 2. Data Arrays
const officeAllotmentClasses = [
    @foreach($office_allotment_classes as $office_allotment_class) {
        id: "{{ $office_allotment_class->id }}",
        name: "{{ $office_allotment_class->office_abbreviation }} - {{ $office_allotment_class->class }}",
        office: "{{ $office_allotment_class->office_abbreviation }}",
        class: "{{ $office_allotment_class->class }}", 
        fund: "{{ $office_allotment_class->fund ?? 'General Fund' }}"
    }, 
    @endforeach
];

const allowedObligationTypes = {
    'PS': ['Regular'],
    'MOOE': ['Regular', 'Purchase Request', 'Project/Contract'],
    'CO': ['Purchase Request', 'Project/Contract'],
    'CCO': ['Purchase Request', 'Project/Contract'],
    'FE': ['Regular']
};

let existingObrNumbers = [
    @foreach($obligations_check ?? [] as $obligation)
        "{{ $obligation->obr_no }}",
    @endforeach
];

let selectedOfficeAllotmentClass = null;
let isTablePopulationMode = false;

// 3. Filter Office Allotment Classes
function filterOfficeAllotmentClasses() {
    const input = document.getElementById("office_allotment_class");
    const dropdown = document.getElementById("OfficeAllotmentClassDropdown");
    const filter = input.value.toLowerCase();

    dropdown.innerHTML = "";

    if (!filter) {
        dropdown.classList.add("hidden");
        return;
    }

    const filteredClasses = officeAllotmentClasses.filter(item => item.name.toLowerCase().includes(filter));

    if (filteredClasses.length === 0) {
        dropdown.classList.add("hidden");
        return;
    }

    filteredClasses.forEach(item => {
        const option = document.createElement("div");
        option.className = "p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer";
        option.innerHTML = `${item.name}`;
        option.onclick = function() {
            input.value = item.name;
            document.getElementById("office_allotment_class_id").value = item.id;
            
            selectedOfficeAllotmentClass = item;
            
            document.getElementById('obr_type').value = '';
            
            updateObligationTypes(item.class);
            generateObrNumber();
            
            // For PS classes, display all appropriations; for others, show filtering
            if (item.class === 'PS') {
                populateAppropriationsTable(item.id);
            } else {
                setupAccountCodeFiltering(item.id);
            }
            
            dropdown.classList.add("hidden");
        };
        dropdown.appendChild(option);
    });

    dropdown.classList.remove("hidden");
}

// 4. UPDATE OBLIGATION TYPES
function updateObligationTypes(classType) {
    const obrTypeSelect = document.getElementById('obr_type');
    const allowedTypes = allowedObligationTypes[classType] || [];

    while (obrTypeSelect.options.length > 1) {
        obrTypeSelect.remove(1);
    }

    allowedTypes.forEach(type => {
        const option = document.createElement('option');
        option.value = type;
        option.textContent = type;
        obrTypeSelect.appendChild(option);
    });

    if (classType === 'PS' && allowedTypes.length === 1) {
        obrTypeSelect.value = 'Regular';
        updateTextColor(obrTypeSelect);
    }
}

// 5. GENERATE OBR NUMBER
function generateObrNumber() {
    const obrNoField = document.getElementById('obr_no');
    
    if (!selectedOfficeAllotmentClass) {
        obrNoField.value = '';
        console.warn('No office allotment class selected');
        return;
    }
    
    // Get the selected year from the year filter
    const yearFilter = document.getElementById('year1');
    const selectedYear = yearFilter ? yearFilter.value : new Date().getFullYear();
    
    const date = new Date();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = String(selectedYear).slice(-2); // Use selected year instead of current year

    const fundToSequence = {
        'General Fund': '100',
        'Special Education Fund': '200',
        'Benguet General Hospital Economic Enterprise': '109',
        'Provincial Development Fund': '107'
    };

    const fundCode = fundToSequence[selectedOfficeAllotmentClass.fund] || '000';
    const obrNumber = `${fundCode}-${year}-${month}-`;
    
    obrNoField.value = obrNumber;
    
    const obrNoError = document.getElementById('obrNoError');
    if (obrNoError) {
        obrNoError.textContent = '';
    }
    
    obrNoField.classList.remove('border-red-500');
    obrNoField.classList.add('border-gray-300', 'dark:border-gray-700');
    updateTextColor(obrNoField);
}

// 5b. LOAD EXISTING OBR NUMBERS (dynamically from server)
function loadExistingObrNumbers() {
    // Get the selected year from the year filter
    const yearFilter = document.getElementById('year1');
    const selectedYear = yearFilter ? yearFilter.value : new Date().getFullYear();
    
    // Fetch existing OBR numbers from the server
    return fetch(`/api/obligations/existing-obr-numbers?year=${selectedYear}`)
        .then(response => {
            if (!response.ok) {
                console.error('Failed to fetch OBR numbers');
                return [];
            }
            return response.json();
        })
        .then(data => {
            // Update the global existingObrNumbers array
            if (data && Array.isArray(data)) {
                window.existingObrNumbers = data.map(item => item.obr_no);
            }
            return data;
        })
        .catch(error => {
            console.error('Error loading OBR numbers:', error);
            return [];
        });
}

// 6. CHECK OBR NUMBER EXISTS
function checkObrNumberExists() {
    const obrNoField = document.getElementById('obr_no');
    const obrNoError = document.getElementById('obrNoError');
    const obrValue = obrNoField.value.trim();
    
    obrNoError.textContent = '';
    obrNoField.classList.remove('border-red-500');
    obrNoField.classList.add('border-gray-300', 'dark:border-gray-700');
    
    if (!obrValue) {
        return false;
    }
    
    const parts = obrValue.split('-');
    if (parts.length < 4) {
        return false;
    }
    
    const serial = parts[parts.length - 1];
    
    if (!serial || serial.trim() === '') {
        return false;
    }
    
    // Use the global existingObrNumbers array
    const existingList = window.existingObrNumbers || existingObrNumbers || [];
    const serialExists = existingList.some(existingObr => {
        const existingParts = existingObr.split('-');
        const existingSerial = existingParts[existingParts.length - 1];
        return existingSerial === serial;
    });
    
    if (serialExists) {
        obrNoField.classList.add('border-red-500');
        obrNoField.classList.remove('border-gray-300', 'dark:border-gray-700');
        obrNoError.textContent = `Serial number "${serial}" is already used. Please enter a different serial number.`;
        return true;
    }
    
    return false;
}

// 7. ACCOUNT CODES DATA
const appropriations = [
    @foreach($appropriations as $appropriation) {
        id: "{{ $appropriation->id }}",
        account_code: "{{ $appropriation->account_code }}",
        program: "{{ $appropriation->programs }}",
        project_no: "{{ $appropriation->project_no }}",
        description: "{{ $appropriation->description }}",
        office_allotment_class_id: "{{ $appropriation->office_allotment_class_id }}",
        balance: "{{ $appropriation->balance }}"
    },
    @endforeach
];

// 8. CREATE FILTERING ROW (used for adding new rows in hybrid mode)
function createFilteringRow() {
    const isPDF = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.fund === 'Provincial Development Fund';
    const isPEOCO = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.office === 'PEO' && selectedOfficeAllotmentClass.class === 'CO';
    const showProjectNo = isPDF || isPEOCO;
    const programLabel = showProjectNo ? "{{ __('Project No') }}" : "{{ __('Program') }}";
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="px-1 py-2 relative">
            <input 
                type="text" 
                name="account_code[]" 
                placeholder="{{ __('Account Code') }}"
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                oninput="filterAccountCodes(this)"
                onchange="resetAmountOnAccountCodeChange(this)"
                autocomplete="off" />
            <div class="fixed bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-[10004]" style="width: auto; min-width: 200px;">
                <!-- Filtered suggestions will appear here -->
            </div>
        </td>
        <td class="px-1 py-2 relative">
            <textarea 
                name="description[]" 
                placeholder="{{ __('Description') }}"
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                oninput="filterDescriptions(this)"
                autocomplete="off"></textarea>
            <div class="fixed bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-[10004]" style="width: auto; min-width: 200px;">
                <!-- Filtered descriptions will appear here -->
            </div>
        </td>
        <td class="px-1 py-2 relative">
            <textarea 
                name="programs[]" 
                placeholder="${programLabel}"
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                oninput="filterPrograms(this)"
                autocomplete="off"></textarea>
            <div class="fixed bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg hidden max-h-48 overflow-auto z-[10004]" style="width: auto; min-width: 200px;">
                <!-- Filtered programs will appear here -->
            </div>
        </td>
        <td class="px-1 py-2">
            <input 
                type="text" 
                name="balance_from_allotment[]" 
                placeholder="{{ __('Balance') }}"
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                readonly />
        </td>
        <td class="px-1 py-2">
            <input 
                type="text" 
                name="amount_of_obligation[]" 
                placeholder="{{ __('Amount') }}"
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                oninput="validateAmountInput(this); calculateTotalObligation();"
                onblur="formatAmountField(this); calculateTotalObligation();"
                autocomplete="off" />
        </td>
        <td class="px-1 py-2 text-center">
            <button type="button" onclick="deleteRow(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    return row;
}

// 8a. CREATE PRE-POPULATED READONLY ROW (for first row when opening from accounts)
function createPrePopulatedRow(appropriation) {
    const row = document.createElement('tr');
    row.setAttribute('data-prepopulated', 'true');
    
    const balance = parseFloat(appropriation.balance || 0);
    const formattedBalance = balance.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    const isPDF = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.fund === 'Provincial Development Fund';
    const isPEOCO = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.office === 'PEO' && selectedOfficeAllotmentClass.class === 'CO';
    const showProjectNo = isPDF || isPEOCO;
    const programValue = showProjectNo ? (appropriation.project_no || '') : (appropriation.program || '');
    
    row.innerHTML = `
        <td class="px-1 py-2">
            <input 
                type="text" 
                name="account_code[]" 
                value="${appropriation.account_code}"
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded bg-gray-100 dark:bg-gray-700"
                readonly />
        </td>
        <td class="px-1 py-2">
            <textarea 
                name="description[]" 
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded bg-gray-100 dark:bg-gray-700"
                readonly>${appropriation.description || ''}</textarea>
        </td>
        <td class="px-1 py-2">
            <textarea 
                name="programs[]" 
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded bg-gray-100 dark:bg-gray-700"
                readonly>${programValue}</textarea>
        </td>
        <td class="px-1 py-2">
            <input 
                type="text" 
                name="balance_from_allotment[]" 
                value="${formattedBalance}"
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded bg-gray-100 dark:bg-gray-700"
                readonly />
        </td>
        <td class="px-1 py-2">
            <input 
                type="text" 
                name="amount_of_obligation[]" 
                placeholder="{{ __('Amount') }}"
                class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                oninput="validateAmountInput(this); calculateTotalObligation();"
                onblur="formatAmountField(this); calculateTotalObligation();"
                autocomplete="off" />
        </td>
        <td class="px-1 py-2 text-center">
            <button type="button" onclick="deleteRow(this)" class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1 text-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    return row;
}

// 8b. SETUP ACCOUNT CODE FILTERING (for non-PS classes)
function setupAccountCodeFiltering(officeAllotmentClassId) {
    isTablePopulationMode = false;
    const tableBody = document.querySelector('#programs_table tbody');
    tableBody.innerHTML = ''; // Clear existing rows

    if (!officeAllotmentClassId) {
        return;
    }

    // Update column header based on office and class type
    updateProgramColumnHeader();

    // Show Add Row button
    const addRowBtn = document.querySelector('button[onclick="addRow()"]');
    if (addRowBtn) addRowBtn.style.display = 'inline-flex';

    // Create a single input row for filtering account codes
    const row = createFilteringRow();
    tableBody.appendChild(row);
    
    // Reset total obligation
    calculateTotalObligation();
}

// 9. FILTER ACCOUNT CODES (for non-PS classes)
function filterAccountCodes(inputElement) {
    const officeAllotmentClassId = document.getElementById('office_allotment_class_id').value;
    const dropdown = inputElement.nextElementSibling;
    const filter = inputElement.value.toLowerCase();

    dropdown.innerHTML = '';

    if (!filter || !officeAllotmentClassId) {
        dropdown.classList.add('hidden');
        return;
    }

    const isPDF = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.fund === 'Provincial Development Fund';
    const isPEOCO = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.office === 'PEO' && selectedOfficeAllotmentClass.class === 'CO';
    const showProjectNo = isPDF || isPEOCO;

    // Determine which field to search for program/project_no
    const fieldToSearch = showProjectNo ? 'project_no' : 'program';

    const filteredCodes = appropriations.filter(item =>
        item.office_allotment_class_id === officeAllotmentClassId &&
        (item.account_code.toLowerCase().includes(filter) ||
         item.description.toLowerCase().includes(filter) ||
         item[fieldToSearch].toLowerCase().includes(filter))
    );

    if (filteredCodes.length === 0) {
        dropdown.classList.add('hidden');
        return;
    }

    filteredCodes.forEach(item => {
        const option = document.createElement('div');
        option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-300 dark:border-gray-700';
        const programValue = showProjectNo ? (item.project_no || 'No project no') : (item.program || 'No program');
        option.innerHTML = `
            <strong>${item.account_code}</strong><br>
            <span class="text-gray-700 dark:text-gray-400">${item.description || 'No description'}</span><br>
            <span class="text-gray-700 dark:text-gray-400">${programValue}</span>
        `;
        option.onclick = function() {
            inputElement.value = item.account_code;
            populateFields(inputElement, item);
            calculateBalance(inputElement, item);
            dropdown.classList.add('hidden');
        };
        dropdown.appendChild(option);
    });

    // Position dropdown below the input field
    dropdown.classList.remove('hidden');
    const rect = inputElement.getBoundingClientRect();
    dropdown.style.top = (rect.bottom + 5) + 'px';
    dropdown.style.left = rect.left + 'px';
    dropdown.style.width = rect.width + 'px';
}

// 9b. FILTER DESCRIPTIONS (for non-PS classes)
function filterDescriptions(inputElement) {
    const officeAllotmentClassId = document.getElementById('office_allotment_class_id').value;
    const dropdown = inputElement.nextElementSibling;
    const filter = inputElement.value.toLowerCase();

    dropdown.innerHTML = '';

    if (!filter || !officeAllotmentClassId) {
        dropdown.classList.add('hidden');
        return;
    }

    const filteredDescriptions = appropriations.filter(item =>
        item.office_allotment_class_id === officeAllotmentClassId &&
        item.description.toLowerCase().includes(filter)
    );

    if (filteredDescriptions.length === 0) {
        dropdown.classList.add('hidden');
        return;
    }

    const isPDF = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.fund === 'Provincial Development Fund';
    const isPEOCO = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.office === 'PEO' && selectedOfficeAllotmentClass.class === 'CO';
    const showProjectNo = isPDF || isPEOCO;

    filteredDescriptions.forEach(item => {
        const option = document.createElement('div');
        option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-300 dark:border-gray-700';
        const programValue = showProjectNo ? (item.project_no || 'No project no') : (item.program || 'No program');
        option.innerHTML = `
            <strong>${item.description}</strong><br>
            <span class="text-gray-700 dark:text-gray-400">${item.account_code}</span><br>
            <span class="text-gray-700 dark:text-gray-400">${programValue}</span>
        `;
        option.onclick = function() {
            const accountCodeField = inputElement.closest('tr').querySelector('[name="account_code[]"]');
            accountCodeField.value = item.account_code;
            inputElement.value = item.description;
            populateFields(inputElement, item);
            calculateBalance(inputElement, item);
            dropdown.classList.add('hidden');
        };
        dropdown.appendChild(option);
    });

    // Position dropdown below the input field
    dropdown.classList.remove('hidden');
    const rect = inputElement.getBoundingClientRect();
    dropdown.style.top = (rect.bottom + 5) + 'px';
    dropdown.style.left = rect.left + 'px';
    dropdown.style.width = rect.width + 'px';
}

// 9c. FILTER PROGRAMS/PROJECT NO (for non-PS classes)
function filterPrograms(inputElement) {
    const officeAllotmentClassId = document.getElementById('office_allotment_class_id').value;
    const dropdown = inputElement.nextElementSibling;
    const filter = inputElement.value.toLowerCase();

    dropdown.innerHTML = '';

    if (!filter || !officeAllotmentClassId) {
        dropdown.classList.add('hidden');
        return;
    }

    const isPDF = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.fund === 'Provincial Development Fund';
    const isPEOCO = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.office === 'PEO' && selectedOfficeAllotmentClass.class === 'CO';
    const showProjectNo = isPDF || isPEOCO;

    // Filter by project_no if PDF/PEO-CO, otherwise by program
    const fieldToFilter = showProjectNo ? 'project_no' : 'program';

    const filteredPrograms = appropriations.filter(item =>
        item.office_allotment_class_id === officeAllotmentClassId &&
        item[fieldToFilter].toLowerCase().includes(filter)
    );

    if (filteredPrograms.length === 0) {
        dropdown.classList.add('hidden');
        return;
    }

    filteredPrograms.forEach(item => {
        const option = document.createElement('div');
        option.className = 'p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer text-xs border-b border-gray-300 dark:border-gray-700';
        const displayValue = showProjectNo ? item.project_no : item.program;
        option.innerHTML = `
            <strong>${displayValue || 'N/A'}</strong><br>
            <span class="text-gray-700 dark:text-gray-400">${item.account_code}</span><br>
            <span class="text-gray-700 dark:text-gray-400">${item.description || 'No description'}</span>
        `;
        option.onclick = function() {
            inputElement.value = displayValue;
            const accountCodeField = inputElement.closest('tr').querySelector('[name="account_code[]"]');
            const descriptionField = inputElement.closest('tr').querySelector('[name="description[]"]');
            accountCodeField.value = item.account_code;
            descriptionField.value = item.description;
            populateFields(inputElement, item);
            calculateBalance(inputElement, item);
            dropdown.classList.add('hidden');
        };
        dropdown.appendChild(option);
    });

    // Position dropdown below the input field
    dropdown.classList.remove('hidden');
    const rect = inputElement.getBoundingClientRect();
    dropdown.style.top = (rect.bottom + 5) + 'px';
    dropdown.style.left = rect.left + 'px';
    dropdown.style.width = rect.width + 'px';
}

// 10. DISPLAY ALL APPROPRIATIONS IN TABLE (for PS classes or Provincial Development Fund)
function populateAppropriationsTable(officeAllotmentClassId) {
    isTablePopulationMode = true;
    const tableBody = document.querySelector('#programs_table tbody');
    tableBody.innerHTML = ''; // Clear existing rows

    if (!officeAllotmentClassId) {
        return;
    }

    // Hide Add Row button
    const addRowBtn = document.querySelector('button[onclick="addRow()"]');
    if (addRowBtn) addRowBtn.style.display = 'none';

    // Update column header based on fund type
    updateProgramColumnHeader();

    // Get all appropriations for the selected office allotment class
    const appropriationsForClass = appropriations.filter(item =>
        item.office_allotment_class_id === officeAllotmentClassId
    );

    if (appropriationsForClass.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="6" class="px-4 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                {{ __('No appropriations found for this office and allotment class') }}
            </td>
        `;
        tableBody.appendChild(row);
        return;
    }

    // Check if this is Provincial Development Fund or PEO - CO
    const isPDF = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.fund === 'Provincial Development Fund';
    const isPEOCO = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.office === 'PEO' && selectedOfficeAllotmentClass.class === 'CO';
    const showProjectNo = isPDF || isPEOCO;

    // Create a row for each appropriation (without delete button)
    appropriationsForClass.forEach((item, index) => {
        const row = document.createElement('tr');
        const programValue = showProjectNo ? (item.project_no || '') : (item.program || '');
        
        row.innerHTML = `
            <td class="px-1 py-2">
                <input 
                    type="text" 
                    name="account_code[]" 
                    value="${item.account_code}" 
                    class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                    readonly />
            </td>
            <td class="px-1 py-2">
                <textarea 
                    name="description[]" 
                    class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-400"
                    readonly>${item.description || ''}</textarea>
            </td>
            <td class="px-1 py-2">
                <textarea 
                    name="programs[]" 
                    class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-400"
                    readonly>${programValue}</textarea>
            </td>
            <td class="px-1 py-2">
                <input 
                    type="text" 
                    name="balance_from_allotment[]" 
                    value="${parseFloat(item.balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}" 
                    class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                    readonly />
            </td>
            <td class="px-1 py-2">
                <input 
                    type="text" 
                    name="amount_of_obligation[]" 
                    placeholder="{{ __('Amount') }}"
                    class="block w-full dark:bg-gray-800 dark:text-gray-200 text-left text-xs border border-gray-300 dark:border-gray-700 px-2 py-1 rounded"
                    oninput="validateAmountInput(this); calculateTotalObligation();"
                    onblur="formatAmountField(this); calculateTotalObligation();"
                    autocomplete="off" />
            </td>
            <td class="px-1 py-2 text-center">
            </td>
        `;
        tableBody.appendChild(row);
    });

    // Reset total obligation
    calculateTotalObligation();
}

// 10b. UPDATE PROGRAM COLUMN HEADER
function updateProgramColumnHeader() {
    const isPDF = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.fund === 'Provincial Development Fund';
    const isPEOCO = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.office === 'PEO' && selectedOfficeAllotmentClass.class === 'CO';
    const showProjectNo = isPDF || isPEOCO;
    const columnHeader = document.getElementById('programColumnHeader');
    if (columnHeader) {
        columnHeader.textContent = showProjectNo ? "{{ __('Project No') }}" : "{{ __('Program') }}";
    }
}

// 11. POPULATE FIELDS
function populateFields(inputElement, item) {
    const row = inputElement.closest('tr');
    const programField = row.querySelector('[name="programs[]"]');
    const descriptionField = row.querySelector('[name="description[]"]');

    const isPDF = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.fund === 'Provincial Development Fund';
    const isPEOCO = selectedOfficeAllotmentClass && selectedOfficeAllotmentClass.office === 'PEO' && selectedOfficeAllotmentClass.class === 'CO';
    const showProjectNo = isPDF || isPEOCO;
    const programValue = showProjectNo ? (item.project_no || '') : (item.program || '');
    
    if (programField) programField.value = programValue ? programValue.trim() : '';
    if (descriptionField) descriptionField.value = item.description ? item.description.trim() : '';
}

// 12. CALCULATE BALANCE
function calculateBalance(inputElement, item) {
    const row = inputElement.closest('tr');
    const balanceField = row.querySelector('[name="balance_from_allotment[]"]');
    const balance = parseFloat(item.balance || 0);
    const formattedBalance = balance.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    if (balanceField) balanceField.value = formattedBalance;
}

// 12b. RESET AMOUNT ON ACCOUNT CODE CHANGE
function resetAmountOnAccountCodeChange(inputElement) {
    const row = inputElement.closest('tr');
    const amountField = row.querySelector('[name="amount_of_obligation[]"]');
    if (amountField) {
        amountField.value = '';
        calculateTotalObligation();
    }
}

// 13. VALIDATE AMOUNT INPUT (on input - only validation, no formatting)
function validateAmountInput(inputElement) {
    const row = inputElement.closest('tr');
    const balanceField = row.querySelector('[name="balance_from_allotment[]"]');

    if (balanceField) {
        // Parse the balance value (remove commas if any)
        const maxBalance = parseFloat((balanceField.value || 0).replace(/,/g, ''));
        
        // Parse the current input value (remove commas if any)
        let currentValue = (inputElement.value || '').replace(/,/g, '');
        currentValue = parseFloat(currentValue) || 0;

        // If current value exceeds max balance, prevent it and show warning
        if (currentValue > maxBalance) {
            // Add visual feedback - yellow ring to indicate warning
            inputElement.classList.add('ring-2', 'ring-yellow-500', 'ring-opacity-50');
            setTimeout(() => {
                inputElement.classList.remove('ring-2', 'ring-yellow-500', 'ring-opacity-50');
            }, 1000);
            
            // Reset to the raw unformatted max balance (for input, don't format yet)
            inputElement.value = maxBalance.toString();
        }
    }
}

// 13b. FORMAT AMOUNT FIELD (on blur - format and final validation)
function formatAmountField(inputElement) {
    const row = inputElement.closest('tr');
    const balanceField = row.querySelector('[name="balance_from_allotment[]"]');

    if (balanceField && inputElement.value) {
        // Parse the balance value (remove commas if any)
        const maxBalance = parseFloat((balanceField.value || 0).replace(/,/g, ''));
        
        // Parse the current input value (remove commas if any)
        let currentValue = (inputElement.value || '').replace(/,/g, '');
        currentValue = parseFloat(currentValue) || 0;

        // If current value exceeds max balance, reset to max balance
        if (currentValue > maxBalance) {
            inputElement.value = maxBalance.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } else if (currentValue > 0) {
            // Format the value with commas and decimals
            inputElement.value = currentValue.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } else if (currentValue === 0 && inputElement.value.trim() !== '') {
            inputElement.value = '0.00';
        }
    }
}

// 14. CALCULATE TOTAL OBLIGATION
function calculateTotalObligation() {
    const amountFields = document.querySelectorAll('[name="amount_of_obligation[]"]');
    let total = 0;

    amountFields.forEach(field => {
        let value = (field.value || '').replace(/,/g, '');
        value = parseFloat(value) || 0;
        total += value;
    });

    const totalObligationElement = document.getElementById('totalObligation');
    totalObligationElement.textContent = total.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// 15. ADD ROW
function addRow() {
    const tableBody = document.querySelector('#programs_table tbody');
    const firstRow = tableBody.querySelector('tr');
    
    // Check if first row is pre-populated (readonly)
    const isFirstRowPrePopulated = firstRow && firstRow.getAttribute('data-prepopulated') === 'true';
    
    if (isFirstRowPrePopulated) {
        // Add a filtering row instead of cloning
        const newRow = createFilteringRow();
        tableBody.appendChild(newRow);
    } else {
        // Clone last row (normal behavior)
        const lastRow = tableBody.querySelector('tr:last-child');
        const newRow = lastRow.cloneNode(true);

        newRow.querySelectorAll('input, textarea').forEach(input => {
            input.value = '';
        });

        tableBody.appendChild(newRow);
    }
    
    calculateTotalObligation();
}

// 16. DELETE ROW
let rowToDelete = null;

function deleteRow(button) {
    rowToDelete = button.closest('tr');
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    const tableBody = rowToDelete.parentNode;
    const messageDiv = document.getElementById('tableMessage');

    if (tableBody.rows.length > 1) {
        tableBody.removeChild(rowToDelete);
        messageDiv.classList.add('hidden');
    } else {
        messageDiv.textContent = "At least one row must remain in the table.";
        messageDiv.classList.remove('hidden');
    }

    rowToDelete = null;
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    calculateTotalObligation();
});

document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
    rowToDelete = null;
    document.getElementById('deleteConfirmModal').classList.add('hidden');
});

// 17. UPDATE TEXT COLOR
function updateTextColor(element) {
    if (element.value.trim() !== "") {
        element.classList.remove("text-gray-500");
        element.classList.add("text-gray-900", "dark:text-gray-100");
    } else {
        element.classList.remove("text-gray-900", "dark:text-gray-100");
        element.classList.add("text-gray-500");
    }

    if ((element.tagName === "INPUT" || element.tagName === "TEXTAREA") && element.hasAttribute("readonly")) {
        element.classList.add("text-gray-900", "dark:text-gray-400");
    }

    if (element.tagName === "SELECT" && element.disabled) {
        element.classList.add("text-gray-700", "dark:text-gray-500");
    }

    if (element.tagName === "TEXTAREA") {
        if (element.value.trim() !== "") {
            element.classList.remove("text-gray-400");
            element.classList.add("text-gray-900", "dark:text-gray-100");
        } else {
            element.classList.remove("text-gray-900", "dark:text-gray-100");
            element.classList.add("text-gray-900");
        }
    }
}

// 18. SETUP TEXT COLOR ON DOM LOAD
document.addEventListener("DOMContentLoaded", function() {
    const fields = document.querySelectorAll("input, select, textarea"); 

    fields.forEach(element => {
        updateTextColor(element);

        element.addEventListener("input", function() {
            updateTextColor(this);
        });

        if (element.tagName === "SELECT") {
            element.addEventListener("change", function() {
                updateTextColor(this);
            });
        }
    });

    // Add event listener to OBR number field
    const obrNoField = document.getElementById('obr_no');
    if (obrNoField) {
        obrNoField.addEventListener('blur', checkObrNumberExists);
        obrNoField.addEventListener('input', function() {
            const parts = this.value.split('-');
            if (parts.length >= 4 && parts[3]) {
                checkObrNumberExists();
            }
        });
    }

    // Enable keyboard navigation
    enableDropdownKeyboardNavigation("office_allotment_class", "OfficeAllotmentClassDropdown");
    enableDropdownKeyboardNavigation("account_code", "AccountCodeDropdown");
});

// 19. KEYBOARD NAVIGATION
function enableDropdownKeyboardNavigation(inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    let currentFocus = -1;

    if (!input || !dropdown) return;

    input.addEventListener("keydown", function (e) {
        let items = dropdown.querySelectorAll("div, li");
        if (dropdown.classList.contains("hidden") || items.length === 0) return;

        if (e.key === "ArrowDown") {
            e.preventDefault();
            currentFocus++;
            if (currentFocus >= items.length) currentFocus = 0;
            setActive(items, currentFocus);
        } 
        else if (e.key === "ArrowUp") {
            e.preventDefault();
            currentFocus--;
            if (currentFocus < 0) currentFocus = items.length - 1;
            setActive(items, currentFocus);
        } 
        else if (e.key === "Enter") {
            e.preventDefault();
            if (currentFocus > -1 && items[currentFocus]) {
                items[currentFocus].click();
                currentFocus = -1;
            }
        } 
        else if (e.key === "Escape") {
            dropdown.classList.add("hidden");
            currentFocus = -1;
        }
    });

    function setActive(items, index) {
        removeActive(items);
        if (items[index]) {
            items[index].classList.add("active");
            items[index].style.backgroundColor = "#e5e7eb";
            items[index].scrollIntoView({ block: "nearest" });
        }
    }

    function removeActive(items) {
        items.forEach(item => {
            item.classList.remove("active");
            item.style.backgroundColor = "";
            item.style.color = "";
        });
    }

    document.addEventListener("click", function (event) {
        if (!event.target.closest(`#${inputId}`) && !event.target.closest(`#${dropdownId}`)) {
            dropdown.classList.add("hidden");
            currentFocus = -1;
        }
    });
}

// 20. HIDE DROPDOWNS WHEN CLICKING OUTSIDE
document.addEventListener('click', function(event) {
    // Hide Office Allotment Class dropdown
    const officeDropdown = document.getElementById("OfficeAllotmentClassDropdown");
    if (!event.target.closest("#office_allotment_class") && !event.target.closest("#OfficeAllotmentClassDropdown")) {
        officeDropdown.classList.add("hidden");
    }

    // Hide Account Code dropdowns
    const accountCodeDropdowns = document.querySelectorAll('#AccountCodeDropdown');
    accountCodeDropdowns.forEach(dropdown => {
        if (!event.target.closest('[name="account_code[]"]') && !event.target.closest('#AccountCodeDropdown')) {
            dropdown.classList.add('hidden');
        }
    });

    // Hide all positioned dropdowns (dynamically created) in the table if clicking outside
    const tableBody = document.querySelector('#programs_table tbody');
    if (tableBody && !event.target.closest('input[name="account_code[]"]') && !event.target.closest('textarea[name="description[]"]')) {
        const allPositionedDropdowns = tableBody.querySelectorAll('div[style*="position"][style*="fixed"]');
        allPositionedDropdowns.forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Function to update date field based on selected year
function updateDateFieldBasedOnYear() {
    const yearFilter = document.getElementById('year1');
    const dateField = document.getElementById('obr_date');
    
    if (!yearFilter || !dateField) return;
    
    const selectedYear = parseInt(yearFilter.value);
    const currentYear = new Date().getFullYear();
    
    if (selectedYear === currentYear) {
        // Current year: use today's date
        const today = new Date().toISOString().split('T')[0];
        dateField.value = today;
        dateField.max = today;
    } else {
        // Past year: use December 31 of that year
        const lastDayOfYear = `${selectedYear}-12-31`;
        dateField.value = lastDayOfYear;
        dateField.max = lastDayOfYear;
    }
    
    updateTextColor(dateField);
}

// Update date field when opening modal
function updateDateFieldOnModalOpen() {
    updateDateFieldBasedOnYear();
}

// Also update when year filter changes (if modal is already open)
document.addEventListener('DOMContentLoaded', function() {
    const yearFilter = document.getElementById('year1');
    if (yearFilter) {
        yearFilter.addEventListener('change', function() {
            // If modal is open, update the date field
            const modal = document.getElementById('createModal');
            if (modal && !modal.classList.contains('hidden')) {
                updateDateFieldBasedOnYear();
            }
        });
    }
});

// 21. VALIDATE FORM
function handleSaveObligation() {
    // Reload existing OBR numbers before validation
    const yearFilter = document.getElementById('year1');
    const selectedYear = yearFilter ? yearFilter.value : new Date().getFullYear();
    
    fetch(`/api/obligations/existing-obr-numbers?year=${selectedYear}`)
        .then(response => response.json())
        .then(data => {
            // Update the global existingObrNumbers array
            if (data && Array.isArray(data)) {
                window.existingObrNumbers = data.map(item => item.obr_no);
            }
            
            // Now validate and submit
            if (!validateForm()) {
                return false;
            }
            
            cleanupFormData();
            document.getElementById('createObligationsForm').submit();
        })
        .catch(error => {
            console.error('Error reloading OBR numbers:', error);
            // Still allow validation to proceed even if API fails
            if (!validateForm()) {
                return false;
            }
            
            cleanupFormData();
            document.getElementById('createObligationsForm').submit();
        });
}

// 22. VALIDATE FORM
function validateForm() {
    const form = document.getElementById('createObligationsForm');
    let isValid = true;

    // Clear previous error messages
    const tableBody = document.querySelector('#programs_table tbody');
    if (tableBody) {
        tableBody.querySelectorAll('.text-red-500').forEach(error => error.remove());
    }
    document.querySelectorAll('.text-red-500').forEach(error => error.textContent = '');

    const officeAllotmentClass = document.getElementById('office_allotment_class');
    const officeAllotmentClassId = document.getElementById('office_allotment_class_id');
    if (!officeAllotmentClass.value.trim() || !officeAllotmentClassId.value.trim()) {
        document.getElementById('OfficeAllotmentClassError').textContent = 'Office and Allotment Class is required.';
        isValid = false;
    }

    const obrDate = document.getElementById('obr_date');
    if (!obrDate.value.trim()) {
        obrDate.classList.add('border-red-500');
        obrDate.classList.remove('border-gray-300');
        isValid = false;
    } else {
        obrDate.classList.remove('border-red-500');
        obrDate.classList.add('border-gray-300');
    }

    const obrType = document.getElementById('obr_type');
    if (!obrType.value.trim()) {
        document.getElementById('obrTypeError').textContent = 'Obligation Type is required.';
        isValid = false;
    }

    const obrNo = document.getElementById('obr_no');
    const obrNoError = document.getElementById('obrNoError');
    const obrValue = obrNo.value.trim();

    if (!obrValue) {
        obrNo.classList.add('border-red-500');
        obrNo.classList.remove('border-gray-300', 'dark:border-gray-700');
        obrNoError.textContent = 'OBR Number is required';
        isValid = false;
    } else {
        const parts = obrValue.split('-');
        const sequence = parts[parts.length - 1];

        if (!sequence || sequence.trim() === '') {
            obrNo.classList.add('border-red-500');
            obrNo.classList.remove('border-gray-300', 'dark:border-gray-700');
            obrNoError.textContent = 'OBR Number is incomplete. Please enter the serial number.';
            isValid = false;
        } else {
            const serialExists = (window.existingObrNumbers || existingObrNumbers || []).some(existingObr => {
                const existingParts = existingObr.split('-');
                const existingSerial = existingParts[existingParts.length - 1];
                return existingSerial === sequence;
            });

            if (serialExists) {
                obrNo.classList.add('border-red-500');
                obrNo.classList.remove('border-gray-300', 'dark:border-gray-700');
                obrNoError.textContent = `Serial number "${sequence}" is already used. Please enter a different serial number.`;
                isValid = false;
            } else {
                obrNo.classList.remove('border-red-500');
                obrNo.classList.add('border-gray-300', 'dark:border-gray-700');
                obrNoError.textContent = '';
            }
        }
    }

    const particulars = document.getElementById('particulars');
    if (!particulars.value.trim()) {
        document.getElementById('particularsError').textContent = 'Particulars field is required.';
        isValid = false;
    } else {
        particulars.classList.remove('border-red-500');
        particulars.classList.add('border-gray-300');
    }

    if (tableBody.rows.length === 0) {
        const tableMessage = document.getElementById('tableMessage');
        tableMessage.textContent = 'At least one appropriation must be available.';
        tableMessage.classList.remove('hidden');
        isValid = false;
    }

    const amountFields = document.querySelectorAll('[name="amount_of_obligation[]"]');
    let hasAtLeastOneAmount = false;
    
    amountFields.forEach((field, index) => {
        const value = parseFloat((field.value || '').replace(/,/g, '')) || 0;
        const row = field.closest('tr');
        const balanceField = row.querySelector('[name="balance_from_allotment[]"]');
        const balanceValue = parseFloat((balanceField.value || '').replace(/,/g, '')) || 0;
        
        // Only validate if an amount is entered (not empty/0)
        if (value > 0) {
            hasAtLeastOneAmount = true;
            
            // Check if amount exceeds balance
            if (value > balanceValue) {
                field.classList.add('border-red-500');
                field.classList.remove('border-gray-300');
                const errorMessage = document.createElement('div');
                errorMessage.className = 'text-red-500 text-xs mt-1';
                errorMessage.textContent = `Row ${index + 1}: Amount of Obligation (${value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}) cannot exceed Balance from Allotment (${balanceValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}).`;
                errorMessage.style.gridColumn = 'span 2';
                field.parentNode.appendChild(errorMessage);
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
                field.classList.add('border-gray-300');
            }
        } else {
            // Clear any red styling if amount is empty/0
            field.classList.remove('border-red-500');
            field.classList.add('border-gray-300');
        }
    });

    // Check that at least one amount is entered
    if (!hasAtLeastOneAmount) {
        const tableMessage = document.getElementById('tableMessage');
        tableMessage.textContent = 'At least one Amount of Obligation must be entered.';
        tableMessage.classList.remove('hidden');
        isValid = false;
    }

    return isValid;
}

// CLEANUP FORM DATA - Remove formatting before submission and remove empty rows
function cleanupFormData() {
    // Update hidden search input with current search field value
    const searchInput = document.getElementById('searchInput');
    const hiddenSearchInput = document.querySelector('#createObligationsForm input[name="search"]');
    if (searchInput && hiddenSearchInput) {
        hiddenSearchInput.value = searchInput.value;
    }
    
    // Remove empty rows and format amounts
    const tableBody = document.querySelector('#programs_table tbody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
    rows.forEach(row => {
        const amountField = row.querySelector('[name="amount_of_obligation[]"]');
        const value = (amountField.value || '').replace(/,/g, '').trim();
        
        // Remove row if amount is empty
        if (!value || parseFloat(value) === 0) {
            row.remove();
        } else {
            // Remove commas from amount field
            amountField.value = value;
            
            // Remove commas from balance field
            const balanceField = row.querySelector('[name="balance_from_allotment[]"]');
            if (balanceField && balanceField.value) {
                balanceField.value = balanceField.value.replace(/,/g, '');
            }
        }
    });
}

// Wrapper function for opening modal with pre-populated appropriation (called from accounts page)
function openCreateModalWithAppropriation(officeAllotmentClassId, appropriationId, accountCode) {
    openCreateModal(officeAllotmentClassId, appropriationId, accountCode);
}

// Make functions globally available
window.openCreateModal = openCreateModal;
window.openCreateModalWithAppropriation = openCreateModalWithAppropriation;
window.checkObrNumberExists = checkObrNumberExists;
window.loadExistingObrNumbers = loadExistingObrNumbers;
window.handleSaveObligation = handleSaveObligation;
</script>