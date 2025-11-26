<!-- Modal for Copying Appropriations from Last Year -->
<div id="copyLastYearModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-[120rem] w-full max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                Accounts from Last Year
            </h2>
            <button type="button" onclick="closeCopyLastYearModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div id="loadingIndicator" class="text-center py-8 hidden">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-600 dark:text-blue-400"></i>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Loading accounts from last year...</p>
            </div>

            <div id="noDataMessage" class="text-center py-8 hidden">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-2"></i>
                <p class="text-gray-600 dark:text-gray-400">No accounts found for last year</p>
            </div>

            <div id="validationErrors" class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4 mb-4 hidden">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3 flex-1">
                        <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <h3 class="font-semibold text-red-800 dark:text-red-200 mb-2">Please fix the following errors:</h3>
                            <ul id="errorList" class="list-disc list-inside text-red-700 dark:text-red-300 text-sm space-y-1">
                            </ul>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('validationErrors').classList.add('hidden')" class="flex-shrink-0 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 ml-2">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <div id="tableContainer" class="overflow-x-auto hidden">
                <table class="min-w-full border-collapse text-xs">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">Programs</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">Account Code</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">Description</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">FPP Code</th>
                            <th id="projectLocationHeader" class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">Project Location</th>
                            <th id="projectNoHeader" class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300 hidden">Project No.</th>
                            <th id="ccoYearHeader" class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300 hidden">CCO Year</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">Appropriation</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">1st Quarter Allotment</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">2nd Quarter Allotment</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">3rd Quarter Allotment</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">4th Quarter Allotment</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">Remarks</th>
                            <th class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-300">Action</th>
                        </tr>
                    </thead>
                    <tbody id="appropriationsTableBody" class="bg-white dark:bg-gray-800">
                    </tbody>
                    <tfoot class="bg-gray-200 dark:bg-gray-700 text-xs sticky bottom-0">
                        <tr>
                            <td colspan="4" class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-right dark:text-gray-200">
                            </td>
                            <td id="projectLocationFooter" class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 hidden"></td>
                            <td id="projectNoFooter" class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 hidden"></td>
                            <td id="ccoYearFooter" class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 hidden"></td>
                            <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-200">
                                <div class="text-xs mb-1 font-bold">Total Appropriations</div>
                                <span id="totalAppropriations">0.00</span>
                            </td>
                            <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-200">
                                <div class="text-xs mb-1 font-bold">1st Quarter</div>
                                <span id="totalQ1">0.00</span>
                            </td>
                            <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-200">
                                <div class="text-xs mb-1 font-bold">2nd Quarter</div>
                                <span id="totalQ2">0.00</span>
                            </td>
                            <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-200">
                                <div class="text-xs mb-1 font-bold">3rd Quarter</div>
                                <span id="totalQ3">0.00</span>
                            </td>
                            <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-200">
                                <div class="text-xs mb-1 font-bold">4th Quarter</div>
                                <span id="totalQ4">0.00</span>
                            </td>
                            <td colspan="2" class="border-y border-gray-300 dark:border-gray-600 px-3 py-3 text-center dark:text-gray-200">
                                <div class="text-xs mb-1 font-bold">Total Allotments</div>
                                <span id="totalAllotments">0.00</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 flex justify-between items-center p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
            <button type="button" onclick="addNewRow()" class="text-green-600 inline-flex items-center hover:text-white border border-green-600 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600">
                <i class="fas fa-plus mr-1"></i>Add Row
            </button>
            <div class="flex gap-3">
                <button type="button" onclick="submitCopyLastYearForm()" class="text-blue-600 inline-flex items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-4 py-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600">
                    <i class="fas fa-save mr-1"></i>Save All
                </button>
                <button type="button" onclick="closeCopyLastYearModal()" class="text-gray-600 inline-flex items-center border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-5 py-2 dark:border-gray-500 dark:text-gray-500 dark:hover:bg-gray-600 dark:hover:text-white hover:text-white">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let lastYearAppropriatationsData = [];
let rowCounter = 0;
let allocationClassType = null;
let autocompleteTimeout = null;

function openCopyLastYearModal() {
    const officeAllotmentClassId = document.querySelector('[name="office_allotment_class_id"]')?.value || new URLSearchParams(window.location.search).get('office_allotment_class_id');
    
    if (!officeAllotmentClassId) {
        alert('Please select an office allotment class');
        return;
    }

    // Fetch allotment class info to determine visibility
    const allotmentUrl = new URL(`/appropriations/allotment-class-info?office_allotment_class_id=${officeAllotmentClassId}`, window.location.origin).href;
    console.log('Fetching allotment class info from:', allotmentUrl);
    
    fetch(allotmentUrl, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                });
            }
            // Trim whitespace and parse JSON
            return response.text().then(text => {
                text = text.trim();
                return JSON.parse(text);
            });
        })
        .then(data => {
            if (data.error) {
                console.error('API error:', data.error);
            } else {
                allocationClassType = data.class;
                console.log('Allotment class:', allocationClassType);
            }
        })
        .catch(error => console.error('Error fetching allotment class:', error));

    document.getElementById('copyLastYearModal').classList.remove('hidden');
    document.getElementById('loadingIndicator').classList.remove('hidden');
    document.getElementById('tableContainer').classList.add('hidden');
    document.getElementById('noDataMessage').classList.add('hidden');
    
    fetchLastYearappropriations(officeAllotmentClassId);
}

function closeCopyLastYearModal() {
    document.getElementById('copyLastYearModal').classList.add('hidden');
    lastYearAppropriatationsData = [];
    rowCounter = 0;
}

function fetchLastYearappropriations(officeAllotmentClassId) {
    const url = `/appropriations/last-year?office_allotment_class_id=${officeAllotmentClassId}`;
    console.log('Fetching from URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('content-type'));
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`Expected JSON response, got: ${contentType}`);
            }
            
            return response.text().then(text => {
                console.log('Raw response:', text.substring(0, 200));
                // Trim whitespace that may be prepended by the server
                text = text.trim();
                if (!text) {
                    throw new Error('Empty response from server');
                }
                return JSON.parse(text);
            });
        })
        .then(data => {
            console.log('Parsed data:', data);
            document.getElementById('loadingIndicator').classList.add('hidden');
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            if (data.data && data.data.length > 0) {
                lastYearAppropriatationsData = data.data;
                populateTableRows(data.data);
                document.getElementById('tableContainer').classList.remove('hidden');
            } else {
                const message = data.message || 'No Accounts found for last year';
                document.getElementById('noDataMessage').innerHTML = `<i class="fas fa-inbox text-4xl text-gray-400 mb-2"></i><p class="text-gray-600 dark:text-gray-400">${message}</p>`;
                document.getElementById('noDataMessage').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error fetching last year appropriations:', error);
            document.getElementById('loadingIndicator').classList.add('hidden');
            document.getElementById('noDataMessage').innerHTML = `<i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-2"></i><p class="text-red-600 dark:text-red-400">Error: ${error.message}</p><p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Check browser console (F12) for details</p>`;
            document.getElementById('noDataMessage').classList.remove('hidden');
        });
}

function populateTableRows(appropriations) {
    const tbody = document.getElementById('appropriationsTableBody');
    tbody.innerHTML = '';
    rowCounter = 0;

    appropriations.forEach((app, index) => {
        addTableRow(app, index);
    });
    
    toggleCCOColumns();
    attachInputListeners();
    updateTotals();
}

function toggleCCOColumns() {
    const classType = allocationClassType?.toUpperCase() || '';
    
    // Determine visibility based on allotment class
    let showProjectLocation = true;
    let showProjectNo = false;
    let showCcoYear = false;
    
    if (classType === 'PS') {
        // PS: Hide all three columns
        showProjectLocation = false;
        showProjectNo = false;
        showCcoYear = false;
    } else if (classType === 'MOOE' || classType === 'CO') {
        // MOOE: Show Project Location, hide Project No and CCO Year
        showProjectLocation = true;
        showProjectNo = false;
        showCcoYear = false;
    } else if (classType === 'CCO') {
        // CCO: Show all columns
        showProjectLocation = true;
        showProjectNo = true;
        showCcoYear = true;
    }
    
    // Toggle header visibility
    document.getElementById('projectLocationHeader').classList.toggle('hidden', !showProjectLocation);
    document.getElementById('projectNoHeader').classList.toggle('hidden', !showProjectNo);
    document.getElementById('ccoYearHeader').classList.toggle('hidden', !showCcoYear);
    
    // Toggle footer visibility
    document.getElementById('projectLocationFooter').classList.toggle('hidden', !showProjectLocation);
    document.getElementById('projectNoFooter').classList.toggle('hidden', !showProjectNo);
    document.getElementById('ccoYearFooter').classList.toggle('hidden', !showCcoYear);
    
    // Toggle data cell visibility
    const rows = document.querySelectorAll('#appropriationsTableBody tr');
    rows.forEach(row => {
        const projectLocationCells = row.querySelectorAll('td:nth-child(5)');
        const projectNoCells = row.querySelectorAll('td:nth-child(6)');
        const ccoYearCells = row.querySelectorAll('td:nth-child(7)');
        
        projectLocationCells.forEach(cell => cell.classList.toggle('hidden', !showProjectLocation));
        projectNoCells.forEach(cell => cell.classList.toggle('hidden', !showProjectNo));
        ccoYearCells.forEach(cell => cell.classList.toggle('hidden', !showCcoYear));
    });
    
    console.log('Column visibility updated:', { showProjectLocation, showProjectNo, showCcoYear, classType });
}

function addTableRow(data = null, index = null) {
    const tbody = document.getElementById('appropriationsTableBody');
    const rowId = `row-${rowCounter}`;
    rowCounter++;

    const row = document.createElement('tr');
    row.id = rowId;
    row.className = 'border-b border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 align-top';
    
    row.innerHTML = `
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <textarea name="appropriations[${rowId}][programs]" rows="3" class="w-full px-2 py-1 border border-gray-300 rounded text-xs resize-none dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">${data?.programs || ''}</textarea>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2 relative">
            <input type="text" name="appropriations[${rowId}][account_code]" data-row-id="${rowId}" class="account-code-input w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" value="${data?.account_code || ''}" required autocomplete="off"/>
            <div class="autocomplete-dropdown absolute hidden left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow-lg z-10 max-h-40 overflow-y-auto"></div>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <textarea name="appropriations[${rowId}][description]" rows="3" class="w-full px-2 py-1 border border-gray-300 rounded text-xs resize-none dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required>${data?.description || ''}</textarea>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="text" name="appropriations[${rowId}][fpp_code]" value="${data?.fpp_code || ''}" required class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="text" name="appropriations[${rowId}][project_location]" value="${data?.project_location || ''}" class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2 hidden">
            <input type="text" name="appropriations[${rowId}][project_no]" value="${data?.project_no || ''}" class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2 hidden">
            <input type="text" name="appropriations[${rowId}][cco_year]" value="${data?.cco_year || ''}" class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${rowId}][appropriation]" value="0" step="0.01" required class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${rowId}][quarter1]" value="0" step="0.01" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${rowId}][quarter2]" value="0" step="0.01" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${rowId}][quarter3]" value="0" step="0.01" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${rowId}][quarter4]" value="0" step="0.01" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <textarea name="appropriations[${rowId}][remarks]" rows="3" class="w-full px-2 py-1 border border-gray-300 rounded text-xs resize-none dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">${data?.remarks || ''}</textarea>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2 text-center align-middle">
            <div class="flex gap-1 justify-center">
                <button type="button" onclick="insertRowAfter('${rowId}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Insert row after this">
                    <i class="fas fa-plus text-base"></i>
                </button>
                <button type="button" onclick="deleteRow('${rowId}')" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete row">
                    <i class="fas fa-trash text-lg"></i>
                </button>
            </div>
        </td>
    `;

    tbody.appendChild(row);
    toggleCCOColumns();
}

function attachInputListeners() {
    const tbody = document.getElementById('appropriationsTableBody');
    const inputs = tbody.querySelectorAll('input[type="number"]');
    const accountCodeInputs = tbody.querySelectorAll('input[name*="[account_code]"]');
    
    inputs.forEach(input => {
        input.addEventListener('input', updateTotals);
        input.addEventListener('change', updateTotals);
    });

    accountCodeInputs.forEach(input => {
        input.addEventListener('input', handleAccountCodeInput);
        input.addEventListener('blur', hideAutocompleteDropdown);
    });
}

function handleAccountCodeInput(event) {
    const input = event.target;
    const rowId = input.getAttribute('data-row-id');
    const value = input.value.trim();
    const dropdown = input.parentElement.querySelector('.autocomplete-dropdown');

    console.log('handleAccountCodeInput called with value:', value, 'rowId:', rowId);

    clearTimeout(autocompleteTimeout);

    if (value.length === 0) {
        dropdown.classList.add('hidden');
        return;
    }

    autocompleteTimeout = setTimeout(() => {
        // Build the fetch URL with search and class parameters
        let fetchUrl = `/appropriations/account-codes?search=${encodeURIComponent(value)}`;
        if (allocationClassType) {
            fetchUrl += `&class=${encodeURIComponent(allocationClassType)}`;
        }
        console.log('About to fetch from:', fetchUrl);
        console.log('Full URL would be:', new URL(fetchUrl, window.location.origin).href);
        console.log('Filter by class:', allocationClassType);
        
        fetch(new URL(fetchUrl, window.location.origin).href, {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                console.log('Fetch response received. Status:', response.status, 'URL:', response.url);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                    });
                }
                
                // Clone the response to read it as text and then parse as JSON
                return response.clone().text().then(text => {
                    console.log('Raw response text:', text.substring(0, 500));
                    console.log('Response text length:', text.length);
                    console.log('Response text as JSON:', JSON.stringify(text));
                    
                    // Trim whitespace that may be prepended by the server
                    text = text.trim();
                    
                    if (!text || text === '') {
                        throw new Error('Empty response from server');
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error(`Failed to parse JSON: ${e.message}. Response: ${text.substring(0, 200)}`);
                    }
                });
            })
            .then(data => {
                console.log('Account codes data received:', data);
                if (data.error) {
                    console.error('API error:', data.error);
                    dropdown.classList.add('hidden');
                    return;
                }
                dropdown.innerHTML = '';
                if (data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        const option = document.createElement('div');
                        option.className = 'px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 text-xs border-b border-gray-200 dark:border-gray-600 dark:text-gray-200';
                        option.innerHTML = `<strong>${item.code}</strong> - ${item.description}`;
                        option.addEventListener('click', () => selectAccountCode(rowId, item));
                        dropdown.appendChild(option);
                    });
                    dropdown.classList.remove('hidden');
                } else {
                    dropdown.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error fetching account codes:', error);
                console.error('Error stack:', error.stack);
                dropdown.classList.add('hidden');
            });
    }, 100);
}

function selectAccountCode(rowId, account) {
    const row = document.getElementById(rowId);
    if (!row) return;
    
    const accountCodeInput = row.querySelector('.account-code-input');
    const descriptionInput = row.querySelector('textarea[name*="[description]"]');
    const dropdown = accountCodeInput.parentElement.querySelector('.autocomplete-dropdown');
    
    accountCodeInput.value = account.code;
    if (descriptionInput) {
        descriptionInput.value = account.description;
    }
    
    dropdown.classList.add('hidden');
}

function hideAutocompleteDropdown(event) {
    setTimeout(() => {
        const dropdown = event.target.parentElement?.querySelector('.autocomplete-dropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }, 200);
}

function updateTotals() {
    const rows = document.querySelectorAll('#appropriationsTableBody tr');
    let totalApp = 0;
    let totalQ1 = 0;
    let totalQ2 = 0;
    let totalQ3 = 0;
    let totalQ4 = 0;

    rows.forEach(row => {
        const appInput = row.querySelector('input[name*="[appropriation]"]');
        const q1Input = row.querySelector('input[name*="[quarter1]"]');
        const q2Input = row.querySelector('input[name*="[quarter2]"]');
        const q3Input = row.querySelector('input[name*="[quarter3]"]');
        const q4Input = row.querySelector('input[name*="[quarter4]"]');

        const app = parseFloat(appInput?.value || 0);
        const q1 = parseFloat(q1Input?.value || 0);
        const q2 = parseFloat(q2Input?.value || 0);
        const q3 = parseFloat(q3Input?.value || 0);
        const q4 = parseFloat(q4Input?.value || 0);

        totalApp += app;
        totalQ1 += q1;
        totalQ2 += q2;
        totalQ3 += q3;
        totalQ4 += q4;
    });

    const totalAllotments = totalQ1 + totalQ2 + totalQ3 + totalQ4;

    document.getElementById('totalAppropriations').textContent = totalApp.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('totalQ1').textContent = totalQ1.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('totalQ2').textContent = totalQ2.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('totalQ3').textContent = totalQ3.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('totalQ4').textContent = totalQ4.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('totalAllotments').textContent = totalAllotments.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function addNewRow() {
    addTableRow();
    attachInputListeners();
    updateTotals();
}

function deleteRow(rowId) {
    const allRows = document.querySelectorAll('#appropriationsTableBody tr:not([data-error-row="true"])');
    
    // Prevent deleting if it's the last row
    if (allRows.length <= 1) {
        // Show error message instead of alert
        const errorContainer = document.getElementById('validationErrors');
        const errorList = document.getElementById('errorList');
        errorList.innerHTML = '';
        
        const errorMsg = document.createElement('li');
        errorMsg.textContent = 'You must keep at least 1 row. Cannot delete the last row.';
        errorList.appendChild(errorMsg);
        
        errorContainer.classList.remove('hidden');
        errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        return;
    }
    
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        updateTotals();
    }
}

function insertRowAfter(rowId) {
    const currentRow = document.getElementById(rowId);
    if (!currentRow) return;
    
    // Create new row
    const newRowId = `row-${rowCounter}`;
    rowCounter++;
    
    const newRow = document.createElement('tr');
    newRow.id = newRowId;
    newRow.className = 'border-b border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 align-top';
    
    newRow.innerHTML = `
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <textarea name="appropriations[${newRowId}][programs]" rows="3" class="w-full px-2 py-1 border border-gray-300 rounded text-xs resize-none dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2 relative">
            <input type="text" name="appropriations[${newRowId}][account_code]" data-row-id="${newRowId}" class="account-code-input w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required autocomplete="off"/>
            <div class="autocomplete-dropdown absolute hidden left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow-lg z-10 max-h-40 overflow-y-auto"></div>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <textarea name="appropriations[${newRowId}][description]" rows="3" class="w-full px-2 py-1 border border-gray-300 rounded text-xs resize-none dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required></textarea>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="text" name="appropriations[${newRowId}][fpp_code]" required class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="text" name="appropriations[${newRowId}][project_location]" class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2 hidden">
            <input type="text" name="appropriations[${newRowId}][project_no]" class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2 hidden">
            <input type="text" name="appropriations[${newRowId}][cco_year]" class="w-full px-2 py-1 border border-gray-300 rounded text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${newRowId}][appropriation]" value="0" step="0.01" required class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${newRowId}][quarter1]" value="0" step="0.01" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${newRowId}][quarter2]" value="0" step="0.01" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${newRowId}][quarter3]" value="0" step="0.01" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <input type="number" name="appropriations[${newRowId}][quarter4]" value="0" step="0.01" class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"/>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-3 py-2">
            <textarea name="appropriations[${newRowId}][remarks]" rows="3" class="w-full px-2 py-1 border border-gray-300 rounded text-xs resize-none dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
        </td>
        <td class="border-y border-gray-300 dark:border-gray-600 px-1 py-2 text-center align-middle">
            <div class="flex gap-3 justify-center">
                <button type="button" onclick="insertRowAfter('${newRowId}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Insert row after this">
                    <i class="fas fa-plus text-lg"></i>
                </button>
                <button type="button" onclick="deleteRow('${newRowId}')" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete row">
                    <i class="fas fa-trash text-lg"></i>
                </button>
            </div>
        </td>
    `;
    
    // Insert after the current row
    currentRow.insertAdjacentElement('afterend', newRow);
    
    // Apply column visibility to new row
    toggleCCOColumns();
    
    // Attach listeners to the new row's inputs
    attachInputListeners();
    updateTotals();
}

function submitCopyLastYearForm() {
    // Clear previous errors from all rows
    document.querySelectorAll('[data-error-row="true"]').forEach(row => row.remove());
    
    const errorContainer = document.getElementById('validationErrors');
    const errorList = document.getElementById('errorList');
    errorList.innerHTML = '';
    errorContainer.classList.add('hidden');

    const rows = document.querySelectorAll('#appropriationsTableBody tr:not([data-error-row="true"])');
    
    // Check if there's at least 1 row
    if (rows.length === 0) {
        const errorMsg = document.createElement('li');
        errorMsg.textContent = 'You must have at least 1 row of appropriation data';
        errorList.appendChild(errorMsg);
        errorContainer.classList.remove('hidden');
        errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        return;
    }

    let isValid = true;
    let hasErrors = false;

    rows.forEach(row => {
        const rowErrors = [];
        
        // Get required fields
        const accountCodeInput = row.querySelector('input[name*="[account_code]"]');
        const descriptionInput = row.querySelector('textarea[name*="[description]"]');
        const fppCodeInput = row.querySelector('input[name*="[fpp_code]"]');
        const appropriationInput = row.querySelector('input[name*="[appropriation]"]');
        
        // Get quarter inputs
        const quarter1Input = row.querySelector('input[name*="[quarter1]"]');
        const quarter2Input = row.querySelector('input[name*="[quarter2]"]');
        const quarter3Input = row.querySelector('input[name*="[quarter3]"]');
        const quarter4Input = row.querySelector('input[name*="[quarter4]"]');

        const accountCode = accountCodeInput?.value.trim() || '';
        const description = descriptionInput?.value.trim() || '';
        const fppCode = fppCodeInput?.value.trim() || '';
        const appropriation = parseFloat(appropriationInput?.value || 0);
        
        const quarter1 = parseFloat(quarter1Input?.value || 0);
        const quarter2 = parseFloat(quarter2Input?.value || 0);
        const quarter3 = parseFloat(quarter3Input?.value || 0);
        const quarter4 = parseFloat(quarter4Input?.value || 0);
        const quarterSum = quarter1 + quarter2 + quarter3 + quarter4;

        // Validate required fields
        if (!accountCode) {
            rowErrors.push('Account Code is required');
        }
        if (!description) {
            rowErrors.push('Description is required');
        }
        if (!fppCode) {
            rowErrors.push('FPP Code is required');
        }
        if (!appropriation || appropriation <= 0) {
            rowErrors.push('Appropriation is required and must be greater than 0');
        }

        // Validate quarters sum equals appropriation
        if (appropriation > 0 && Math.abs(quarterSum - appropriation) > 0.01) {
            rowErrors.push(`Sum of quarters (${quarterSum.toFixed(2)}) must equal Appropriation (${appropriation.toFixed(2)})`);
        }

        // If there are errors in this row, display them
        if (rowErrors.length > 0) {
            hasErrors = true;
            isValid = false;
            
            // Add error row after the data row
            const errorRow = document.createElement('tr');
            errorRow.setAttribute('data-error-row', 'true');
            errorRow.className = 'bg-red-50 dark:bg-red-900 border-b-2 border-red-300 dark:border-red-700';
            
            const errorCell = document.createElement('td');
            errorCell.colSpan = '100';
            errorCell.className = 'px-3 py-2';
            
            const errorContent = document.createElement('div');
            errorContent.className = 'flex items-start gap-2';
            
            const errorIcon = document.createElement('i');
            errorIcon.className = 'fas fa-exclamation-circle text-red-600 dark:text-red-400 mt-1 flex-shrink-0';
            
            const errorList = document.createElement('ul');
            errorList.className = 'list-disc list-inside text-red-700 dark:text-red-300 text-xs space-y-1';
            
            rowErrors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            
            errorContent.appendChild(errorIcon);
            errorContent.appendChild(errorList);
            errorCell.appendChild(errorContent);
            errorRow.appendChild(errorCell);
            
            row.parentNode.insertBefore(errorRow, row.nextSibling);
        }
    });

    if (!isValid) {
        // Show summary message
        const summaryLi = document.createElement('li');
        summaryLi.innerHTML = '<strong>Please review the highlighted rows above for specific errors.</strong>';
        errorList.appendChild(summaryLi);
        errorContainer.classList.remove('hidden');
        
        // Scroll to first error
        const firstErrorRow = document.querySelector('[data-error-row="true"]');
        if (firstErrorRow) {
            firstErrorRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/appropriations/store-from-last-year';
    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';

    const officeAllotmentClassId = document.querySelector('[name="office_allotment_class_id"]')?.value || new URLSearchParams(window.location.search).get('office_allotment_class_id');
    form.innerHTML += '<input type="hidden" name="office_allotment_class_id" value="' + officeAllotmentClassId + '">';

    const allRows = document.querySelectorAll('#appropriationsTableBody tr:not([data-error-row="true"])');
    let rowIndex = 0;

    allRows.forEach(row => {
        // Collect both input and textarea elements
        const inputs = row.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            const value = input.value;
            const newName = name.replace(/appropriations\[[^\]]+\]/, `appropriations[${rowIndex}]`);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = newName;
            hiddenInput.value = value;
            form.appendChild(hiddenInput);
        });
        rowIndex++;
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>