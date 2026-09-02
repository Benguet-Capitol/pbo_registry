{{-- Obligation Details Panel: per-appropriation breakdown shown below the list when a row is clicked. --}}
<div id="obligationDetailsPanel" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-blue-200 dark:border-blue-800 border-l-4 border-l-blue-500 overflow-hidden mt-3">
    <!-- Panel Header: same style as the Obligations table's thead -->
    <div class="flex flex-wrap justify-between items-center gap-2 px-4 py-3 bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 border-t-2 border-b-2 border-blue-700 dark:border-blue-800">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
            <span id="detailOfficeClass" class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-blue-600 dark:bg-blue-700 text-white text-[11px] font-semibold"></span>
            <span class="flex items-center gap-1 font-bold text-sm">
                <i class="fas fa-hashtag text-[11px] opacity-80"></i>
                <span id="detailObrNo"></span>
            </span>
            <span id="detailObrDate" class="inline-flex items-center gap-1 text-xs"></span>
            <span id="detailObrType" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold"></span>
        </div>
        <button onclick="closeObligationDetails()" title="Close" class="text-blue-700 dark:text-blue-300 hover:text-white hover:bg-blue-600 dark:hover:bg-blue-700 rounded-full w-7 h-7 flex items-center justify-center transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Particulars strip -->
    <div id="detailParticularsWrap" class="px-4 py-2 text-xs text-gray-700 dark:text-gray-300 bg-blue-50 dark:bg-blue-950/40 border-b border-blue-100 dark:border-blue-900">
        <span class="font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide text-[10px] mr-1">Particulars:</span>
        <span id="detailParticulars"></span>
    </div>

    <!-- Details Table -->
    <div class="overflow-x-auto px-4 py-3">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-300 dark:border-gray-700 rounded-md overflow-hidden text-xs">
            <thead class="bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold">Program</th>
                    <th class="px-3 py-2 text-left font-semibold">Account Code</th>
                    <th class="px-3 py-2 text-left font-semibold">Description</th>
                    <th class="px-3 py-2 text-right font-semibold">Original Obligation</th>
                    <th class="px-3 py-2 text-right font-semibold">Adjustment</th>
                    <th class="px-3 py-2 text-right font-semibold">Adjusted Obligation</th>
                    <th class="px-3 py-2 text-right font-semibold">Purchase Order</th>
                    <th class="px-3 py-2 text-right font-semibold">Disbursement</th>
                    <th class="px-3 py-2 text-right font-semibold">Balance</th>
                </tr>
            </thead>
            <tbody id="detailsTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Populated by JavaScript -->
            </tbody>
            <tfoot id="detailsTableFooter" class="bg-blue-50 dark:bg-blue-950/40 font-bold text-gray-800 dark:text-gray-100 border-t-2 border-blue-200 dark:border-blue-800">
                <!-- Populated by JavaScript -->
            </tfoot>
        </table>
    </div>
</div>

<script>
    // Display Obligation Details Panel
    function displayObligationDetails(obligationId) {
        // Remove highlight from any previously highlighted card
        const previouslyHighlighted = document.querySelector('.obligation-row-highlighted');
        if (previouslyHighlighted) {
            previouslyHighlighted.classList.remove('obligation-row-highlighted');
        }

        // Add highlight to the current card
        const currentCard = document.querySelector(`.obligation-card[data-obligation-id="${obligationId}"]`);
        if (currentCard) {
            currentCard.classList.add('obligation-row-highlighted');
        }

        const panel = document.getElementById('obligationDetailsPanel');
        const tbody = document.getElementById('detailsTableBody');
        const tfoot = document.getElementById('detailsTableFooter');

        // Show the panel immediately with a loading skeleton instead of waiting in silence.
        panel.classList.remove('hidden');
        tfoot.innerHTML = '';
        tbody.innerHTML = `
            <tr><td colspan="9" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                <i class="fas fa-circle-notch fa-spin text-blue-500 mr-2"></i>Loading details...
            </td></tr>
        `;

        // Build URL with date range parameters if they exist
        const params = new URLSearchParams(window.location.search);
        let url = `/api/obligations/${obligationId}/details`;

        if (params.has('from_date') || params.has('to_date')) {
            const queryParams = new URLSearchParams();
            if (params.has('from_date')) {
                queryParams.append('from_date', params.get('from_date'));
            }
            if (params.has('to_date')) {
                queryParams.append('to_date', params.get('to_date'));
            }
            url += `?${queryParams.toString()}`;
        }

        // Fetch obligation details with amounts
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch obligation details');
                }
                return response.json();
            })
            .then(data => {
                // Populate header info
                document.getElementById('detailObrNo').textContent = data.obr_no;
                document.getElementById('detailOfficeClass').innerHTML = `<i class="fas fa-building text-[10px] opacity-80 mr-1"></i>${data.office_abbreviation} - ${data.allotment_class}`;
                document.getElementById('detailObrDate').innerHTML = `<i class="far fa-calendar mr-1"></i>${data.obr_date ?? '-'}`;

                // Color-codes the OBR Type badge by obligation kind, same as the card/list views.
                const obrTypeColors = {
                    'Regular': 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-400',
                    'Purchase Request': 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-400',
                    'Project/Contract': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-400',
                };
                const obrTypeEl = document.getElementById('detailObrType');
                obrTypeEl.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold ' + (obrTypeColors[data.obr_type] || 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200');
                obrTypeEl.textContent = data.obr_type ?? '-';

                document.getElementById('detailParticulars').textContent = data.particulars;

                // Helper function to format currency or show '-' if zero
                const formatCurrency = (value) => {
                    return value === 0 ? '-' : value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                };

                // Populate details rows
                if (data.obligation_amounts && data.obligation_amounts.length > 0) {
                    tbody.innerHTML = '';
                    let totalOriginal = 0, totalAdjustment = 0, totalAdjusted = 0, totalPO = 0, totalDisbursement = 0, totalBalance = 0;

                    data.obligation_amounts.forEach((amount, index) => {
                        const row = document.createElement('tr');
                        row.className = (index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900/40') + ' hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors';

                        const originalObligation = parseFloat(amount.amount || 0);
                        const adjustment = parseFloat(amount.adjustment || 0);
                        const adjustedObligation = parseFloat(amount.adjusted_obligation || 0);
                        const poAmount = parseFloat(amount.po_amount || 0);
                        const disbursementAmount = parseFloat(amount.disbursement_amount || 0);
                        const balance = adjustedObligation - poAmount - disbursementAmount;

                        totalOriginal += originalObligation;
                        totalAdjustment += adjustment;
                        totalAdjusted += adjustedObligation;
                        totalPO += poAmount;
                        totalDisbursement += disbursementAmount;
                        totalBalance += balance;

                        const adjustmentColor = adjustment > 0 ? 'text-green-700 dark:text-green-400' : (adjustment < 0 ? 'text-red-700 dark:text-red-400' : 'text-gray-500 dark:text-gray-400');
                        const balanceColor = balance < 0 ? 'text-red-700 dark:text-red-400 font-bold' : (balance === 0 ? 'text-green-700 dark:text-green-400 font-bold' : 'text-orange-700 dark:text-orange-400 font-bold');

                        row.innerHTML = `
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-200">${amount.program || '-'}</td>
                            <td class="px-3 py-2 font-semibold text-gray-900 dark:text-gray-200">${amount.account_code || '-'}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-200">${amount.description || '-'}</td>
                            <td class="px-3 py-2 text-right text-gray-900 dark:text-gray-200">${formatCurrency(originalObligation)}</td>
                            <td class="px-3 py-2 text-right ${adjustmentColor}">${formatCurrency(adjustment)}</td>
                            <td class="px-3 py-2 text-right font-semibold text-blue-700 dark:text-blue-400">${formatCurrency(adjustedObligation)}</td>
                            <td class="px-3 py-2 text-right text-sky-700 dark:text-sky-400">${formatCurrency(poAmount)}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-400">${formatCurrency(disbursementAmount)}</td>
                            <td class="px-3 py-2 text-right ${balanceColor}">${formatCurrency(balance)}</td>
                        `;
                        tbody.appendChild(row);
                    });

                    const totalBalanceColor = totalBalance < 0 ? 'text-red-700 dark:text-red-400' : (totalBalance === 0 ? 'text-green-700 dark:text-green-400' : 'text-orange-700 dark:text-orange-400');
                    tfoot.innerHTML = `
                        <tr>
                            <td class="px-3 py-2" colspan="3">Total</td>
                            <td class="px-3 py-2 text-right">${formatCurrency(totalOriginal)}</td>
                            <td class="px-3 py-2 text-right">${formatCurrency(totalAdjustment)}</td>
                            <td class="px-3 py-2 text-right text-blue-700 dark:text-blue-400">${formatCurrency(totalAdjusted)}</td>
                            <td class="px-3 py-2 text-right text-sky-700 dark:text-sky-400">${formatCurrency(totalPO)}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-400">${formatCurrency(totalDisbursement)}</td>
                            <td class="px-3 py-2 text-right ${totalBalanceColor}">${formatCurrency(totalBalance)}</td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = '<tr><td colspan="9" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 italic">No details available</td></tr>';
                    tfoot.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="9" class="px-3 py-6 text-center text-red-600 dark:text-red-400">Failed to load obligation details. Please try again.</td></tr>';
                if (typeof showToast === 'function') {
                    showToast('Failed to load obligation details', 'error');
                }
            });
    }

    // Close Obligation Details Panel
    function closeObligationDetails() {
        document.getElementById('obligationDetailsPanel').classList.add('hidden');

        // Remove highlight from the card when closing panel
        const highlightedCard = document.querySelector('.obligation-row-highlighted');
        if (highlightedCard) {
            highlightedCard.classList.remove('obligation-row-highlighted');
        }
    }
</script>
