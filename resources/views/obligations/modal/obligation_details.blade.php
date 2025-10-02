<!-- Obligation Details Modal -->
<div id="obligationModal" class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-4 border w-full max-w-6xl shadow-lg rounded-md bg-white dark:bg-gray-800">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex justify-between items-center p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-lg leading-6 font-semibold text-gray-900 dark:text-white">
                    {{ __('Obligation Details') }}
                </h3>
                <button type="button" onclick="closeModal()" class="text-black hover:text-gray-600 dark:text-white">
                    <i class="fas fa-times text-xl mr-2"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div id="modalContent" class="p-6">
                <!-- Content will be dynamically loaded here -->
            </div>
            <!-- Modal footer -->
            <div class="flex justify-end p-4 border-t border-gray-200 dark:border-gray-600">
                <button type="button" onclick="printModal()" class="mr-2 text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-print mr-1 -ml-1"></i>
                    Print
                </button>
                <button type="button" onclick="closeModal()" class="text-gray-600 inline-flex items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2 text-center dark:border-gray-200 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900">
                    <i class="fas fa-times mr-1 -ml-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * Opens the modal and fetches obligation details from the backend.
     * @param {number} obligationId - The ID of the obligation to retrieve and display.
     */
    function openModal(obligationId) {
        closeAllDropdowns(); // Hide other UI dropdowns if open
        const modal = document.getElementById('obligationModal');
        modal.classList.remove('hidden'); // Show modal

        // Request obligation data from the server
        fetch(`/obligations/${obligationId}`)
            .then(res => res.json())
            .then(data => {
                // Destructure fetched data for easier access
                const {
                    obligation,
                    obligation_amounts,
                    obligation_adjustments,
                    total_po_amount,
                    purchase_orders,
                    disbursements = [],
                    total_disbursement_amount = 0
                } = data;
                // Fields configuration for the Disbursement table
                const disbursementTableFields = [
                    { class: 'px-2 py-2 text-center', render: r => r.dv_no || '' },
                    { class: 'px-2 py-2 text-center', render: r => r.disbursement_date || '' },
                    { class: 'px-2 py-2 text-center', render: r => r.status || '' },
                    { class: 'px-2 py-2 text-center', render: r => r.programs || '-' },
                    { class: 'px-2 py-2 text-center', render: r => r.account_code || '-' },
                    { class: 'px-2 py-2 text-center', render: r => r.description || '-' },
                    { class: 'px-3 py-2 text-right', render: r => formatCurrency(r.disbursement_amount) }
                ];

                /**
                 * Utility function to format numbers as currency
                 * @param {number|string} value
                 * @returns {string} formatted currency string
                 */
                const formatCurrency = value =>
                    new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(parseFloat(value) || 0);

                // Compute the total obligation including adjustments
                const totalObligationAmount = obligation_amounts.reduce((sum, amt) => {
                    const obr = parseFloat(amt.obr_amount) || 0;
                    const adj = parseFloat(amt.adjustments) || 0;
                    return sum + obr + adj;
                }, 0);

                // Only show Purchase Order column if the type is "Purchase Request"
                const showPO = obligation.obr_type === 'Purchase Request';

                /**
                 * Renders rows for a table based on field configuration
                 * @param {Array} rows - the data array
                 * @param {Array} fields - array of objects defining column render logic
                 * @returns {string} - HTML string for table rows
                 */
                const buildRows = (rows, fields) =>
                    rows.map(row => `
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            ${fields.map(field => `<td class="${field.class}">${field.render(row)}</td>`).join('')}
                        </tr>
                    `).join('');

                // Fields configuration for the Adjustments table
                const adjTableFields = [{
                        class: 'px-3 py-2 text-center',
                        render: r => r.adjustment_date || 'N/A'
                    },
                    {
                        class: 'px-3 py-2 text-center',
                        render: r => r.programs || '-'
                    },
                    {
                        class: 'px-3 py-2 text-center',
                        render: r => r.account_code || 'N/A'
                    },
                    {
                        class: 'px-3 py-2 text-center',
                        render: r => r.description || 'N/A'
                    },
                    {
                        class: 'px-3 py-2 text-center',
                        render: r => r.remarks || 'N/A'
                    },
                    {
                        class: 'px-3 py-2 text-center',
                        render: r => r.adjusted_by || 'N/A'
                    },
                    {
                        class: 'px-3 py-2 text-right',
                        render: r => formatCurrency(r.adjustment_amount)
                    }
                ];

                // Fields configuration for the Summary table
                const summaryTableFields = [{
                        class: 'px-3 py-2',
                        render: r => r.programs || '-'
                    },
                    {
                        class: 'px-3 py-2',
                        render: r => r.account_code || 'N/A'
                    },
                    {
                        class: 'px-3 py-2',
                        render: r => r.description || 'N/A'
                    },
                    {
                        class: 'px-3 py-2 text-right',
                        render: r => formatCurrency(r.obr_amount)
                    },
                    {
                        class: 'px-3 py-2 text-right',
                        render: r => formatCurrency(r.adjustments)
                    },
                    {
                        class: 'px-3 py-2 text-right',
                        render: r => {
                            const obr = parseFloat(r.obr_amount) || 0;
                            const adj = parseFloat(r.adjustments) || 0;
                            // If adjustment is zero or empty, adjusted obligation is zero
                            if (!adj) return formatCurrency(0);
                            return formatCurrency(obr + adj);
                        }
                    },
                    ...(showPO ? [{
                        class: 'px-3 py-2 text-right',
                        render: r => formatCurrency(r.po_total)
                    }] : []),
                    {
                        class: 'px-3 py-2 text-right',
                        render: r => formatCurrency(r.disbursement_total)
                    }
                ];

                // Obligation summary rows
                const obligationDetails = `
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Office:</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${data.obligation.office || 'N/A'}</td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Allotment Class:</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${data.obligation.allotment_class || 'N/A'}</td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR No:</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${data.obligation.obr_no || 'N/A'}</td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">OBR Type:</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${data.obligation.obr_type || 'N/A'}</td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Particulars:</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${data.obligation.particulars || 'N/A'}</td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Processed by:</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${data.obligation.processed_by || 'N/A'}</td>
                        </tr>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-4 py-2 font-semibold bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">Remarks:</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-400">${data.obligation.remarks || ''}</td>
                        </tr>

                `;

                // Render content inside the modal dynamically
                document.getElementById('modalContent').innerHTML = `
                    <!-- Obligation Info -->
                    <table class="w-full text-xs text-left text-gray-500 dark:text-gray-300">
                        <tbody>${obligationDetails}</tbody>
                    </table>

                    <!-- Programs Table -->
                    <div class="mt-2">
                        <table class="w-full text-xs text-center border-t mt-3 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 bg-gray-100 dark:bg-gray-900 border-b dark:text-gray-300">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-center">Programs</th>
                                    <th scope="col" class="px-4 py-2 text-center">Account Code</th>
                                    <th scope="col" class="px-4 py-2 text-center">Description</th>
                                    <th scope="col" class="px-4 py-2 text-center">Original Obligation</th>
                                    <th scope="col" class="px-4 py-2 text-center">Adjustment</th>
                                    <th scope="col" class="px-4 py-2 text-center">Adjusted Obligation</th>
                                    ${showPO ? '<th scope="col" class="px-4 py-2 text-center">Purchase Order</th>' : ''}
                                    <th scope="col" class="px-4 py-2 text-center">Disbursement</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${buildRows(obligation_amounts, summaryTableFields)}
                                <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                                    <td colspan="3" class="text-right px-3 py-2">Total:</td>
                                    <td class="text-right px-3 py-2">${formatCurrency(obligation_amounts.reduce((sum, r) => sum + parseFloat(r.obr_amount || 0), 0))}</td>
                                    <td class="text-right px-3 py-2">${formatCurrency(obligation_amounts.reduce((sum, r) => sum + parseFloat(r.adjustments || 0), 0))}</td>
                                    <td class="text-right px-3 py-2">${
                                            (obligation_amounts.length === 0 || obligation_amounts.every(r => !parseFloat(r.adjustments)))
                                            ? formatCurrency(0)
                                            : formatCurrency(obligation_amounts.reduce((sum, r) => sum + parseFloat(r.obr_amount || 0) + parseFloat(r.adjustments || 0), 0))
                                    }</td>
                                    ${showPO ? `<td class="text-right px-3 py-2">${formatCurrency(total_po_amount)}</td>` : ''}
                                    <td class="text-right px-3 py-2">${formatCurrency(total_disbursement_amount)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Adjustments Table -->
                    <div class="mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Adjustments:</h3>
                        <table class="w-full text-xs text-center border-t mt-3 text-gray-600 dark:text-gray-400">
                            <thead class="text-xs text-gray-600 bg-gray-100 dark:bg-gray-900 border-b dark:text-gray-300">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-center">Date</th>
                                    <th scope="col" class="px-4 py-2 text-center">Remarks</th>
                                    <th scope="col" class="px-4 py-2 text-center">Programs</th>
                                    <th scope="col" class="px-4 py-2 text-center">Account Code</th>
                                    <th scope="col" class="px-4 py-2 text-center">Description</th>
                                    <th scope="col" class="px-4 py-2 text-center">Adjusted / Cancelled by</th>
                                    <th scope="col" class="px-4 py-2 text-center">Adjustment</th>
                                </tr>
                            </thead>
                            <tbody>${
                                obligation_adjustments.length
                                    ? (() => {
                                        let lastRemarks = null;
                                        return obligation_adjustments.map(row => {
                                            const showCells = row.remarks !== lastRemarks;
                                            lastRemarks = row.remarks;
                                            return `
                                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                    <td class="px-3 py-2 text-center">${showCells ? (row.adjustment_date || 'N/A') : ''}</td>
                                                    <td class="px-3 py-2 text-center">${showCells ? (row.remarks || 'N/A') : ''}</td>
                                                    <td class="px-3 py-2 text-center">${row.programs || '-'}</td>
                                                    <td class="px-3 py-2 text-center">${row.account_code || 'N/A'}</td>
                                                    <td class="px-3 py-2 text-center">${row.description || 'N/A'}</td>
                                                    <td class="px-3 py-2 text-center">${row.adjusted_by || 'N/A'}</td>
                                                    <td class="px-3 py-2 text-right">${formatCurrency(row.adjustment_amount)}</td>
                                                </tr>
                                            `;
                                        }).join('');
                                    })()
                                    : ` <tr><td colspan = "7" class = "px-3 py-3 text-center text-gray-500"> No adjustments found. </td></tr> `
                            }</tbody>
                            <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                                <td colspan="6" class="text-right px-3 py-2">Total Adjustment:</td>
                                <td class="text-right px-3 py-2">${formatCurrency(obligation_adjustments.reduce((sum, r) => sum + parseFloat(r.adjustment_amount || 0), 0))}</td>
                            </tr>
                        </table>
                    </div>
                
                    ${showPO ? `
                    <!-- Purchase Orders Table -->
                    <div class="mt-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-300">Purchase Orders:</h3>
                        <table class="w-full text-xs text-center border-t mt-3 text-gray-600 dark:text-gray-400">
                            <thead class="text-xs text-gray-600 bg-gray-100 dark:bg-gray-900 border-b dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-2">PO Number</th>
                                    <th class="px-3 py-2">PO Date</th>
                                    <th class="px-3 py-2">PR Number</th>
                                    <th class="px-3 py-2">Supplier</th>
                                    <th class="px-3 py-2">Delivery Period</th>
                                    <th class="px-3 py-2">Programs</th>
                                    <th class="px-3 py-2">Account Code</th>
                                    <th class="px-3 py-2">Description</th>
                                    <th class="px-3 py-2">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${
                                    purchase_orders.length
                                    ? (() => {
                                        // ✅ Sort purchase_orders by po_number to group them
                                        purchase_orders.sort((a, b) => a.po_number.localeCompare(b.po_number));

                                        let shownPoNumbers = new Set();

                                        return buildRows(purchase_orders, [
                                            {
                                                class: 'px-2 py-2',
                                                render: r => shownPoNumbers.has(r.po_number) ? '' : r.po_number
                                            },
                                            {
                                                class: 'px-2 py-2',
                                                render: r => shownPoNumbers.has(r.po_number) ? '' : r.po_date
                                            },
                                            {
                                                class: 'px-2 py-2',
                                                render: r => shownPoNumbers.has(r.po_number) ? '' : r.pr_no
                                            },
                                            {
                                                class: 'px-2 py-2',
                                                render: r => shownPoNumbers.has(r.po_number) ? '' : r.supplier
                                            },
                                            { 
                                                class: 'px-2 py-2', 
                                                render: r => shownPoNumbers.has(r.po_number) ? '' : r.delivery_period 
                                            },
                                            { class: 'px-2 py-2', render: r => r.programs && r.programs.trim() !== '' ? r.programs : '-' },
                                            { class: 'px-2 py-2', render: r => r.account_code },
                                            { class: 'px-2 py-2', render: r => r.description },
                                            {
                                                class: 'px-2 py-2 text-right',
                                                render: r => {
                                                    shownPoNumbers.add(r.po_number); // ✅ Mark as shown only after rendering PO Amount
                                                    return formatCurrency(r.po_amount);
                                                }
                                            }
                                        ]);
                                    })()
                                    : `<tr><td colspan="10" class="px-3 py-3 text-center text-gray-500">No purchase orders found.</td></tr>`
                                }
                            </tbody>
                            <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                                <td colspan="8" class="text-right px-3 py-2">Total Purchase Order Amount:</td>
                                <td class="text-right px-3 py-2">${formatCurrency(purchase_orders.reduce((sum, r) => sum + parseFloat(r.po_amount || 0), 0))}</td>
                            </tr>
                        </table>
                    </div>
                    ` : ''}
                    <!-- Disbursement Table -->
                    <div class="mt-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-300">Disbursements:</h3>
                        <table class="w-full text-xs text-center border-t mt-3 text-gray-600 dark:text-gray-400">
                            <thead class="text-xs text-gray-600 bg-gray-100 dark:bg-gray-900 border-b dark:text-gray-300">
                                <tr>
                                    <th class="px-2 py-2 text-center">DV / Check No.</th>
                                    <th class="px-2 py-2 text-center">Date</th>
                                    <th class="px-2 py-2 text-center">Status</th>
                                    <th class="px-2 py-2 text-center">Program</th>
                                    <th class="px-2 py-2 text-center">Account Code</th>
                                    <th class="px-2 py-2 text-center">Description</th>
                                    <th class="px-3 py-2 text-center">DV / Check Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${disbursements.length
                                    ? buildRows(disbursements, disbursementTableFields)
                                    : `<tr><td colspan="7" class="px-3 py-3 text-center text-gray-500">No disbursements found.</td></tr>`}
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-100 dark:bg-gray-900 font-semibold">
                                    <td colspan="6" class="text-right px-3 py-2">Total DV / Check Amount:</td>
                                    <td class="text-right px-3 py-2">${formatCurrency(total_disbursement_amount)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Failed to load obligation:', error);
                document.getElementById('modalContent').innerHTML = `<p class="text-red-500">Failed to load obligation details.</p>`;
            });
    }

    function printModal() {
        const modalContent = document.getElementById('modalContent').innerHTML;

        const printWindow = window.open('', '', 'width=1000,height=800');
        printWindow.document.write(`
            <html>
            <head>
                <title>Obligation Details</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px; }
                    th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
                    th { background-color:rgb(114, 114, 114); }
                    h3 { margin-top: 20px; margin-bottom: 5px; }
                </style>
            </head>
            <body onload="window.print(); window.close();">
                <h2>Obligation Request Details</h2>
                ${modalContent}
            </body>
            </html>
        `);
        printWindow.document.close();
    }

    /**
     * Closes the modal by hiding it
     */
    function closeModal() {
        document.getElementById('obligationModal').classList.add('hidden');
    }
</script>