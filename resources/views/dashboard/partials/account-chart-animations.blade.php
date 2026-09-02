{{-- Accounts page card & chart utilities: success-alert dismissal, tooltips, color
     helpers, the stacked bar graph + legend, card value calculations, table filtering,
     animated counters, circular progress rings, heatmap toggling, card click handlers,
     and the Account Analytics Panel charts. Extracted from dashboard/accounts.blade.php
     to keep that file's length manageable. --}}
    <script>
        // Close success alert
        function closeSuccessAlert() {
            const alert = document.getElementById('successAlert');
            if (alert) {
                alert.style.display = 'none';
            }
        }

        // Auto-close success alert after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('successAlert');
            if (alert) {
                setTimeout(function() {
                    closeSuccessAlert();
                }, 5000);
            }
        });

        // Tooltip functions
        function showTooltip(element) {
            const tooltip = element.querySelector('.tooltip-box');
            if (tooltip) {
                tooltip.style.display = 'block';
                clampTooltipToViewport(tooltip);
            }
        }

        function hideTooltip(element) {
            const tooltip = element.querySelector('.tooltip-box');
            if (tooltip) {
                tooltip.style.display = 'none';
                tooltip.style.transform = '';
            }
        }

        // Nudges a horizontally-centered tooltip back into the viewport on narrow screens
        // instead of letting it run off the left/right edge.
        function clampTooltipToViewport(tooltip) {
            tooltip.style.transform = 'translateX(-50%)';
            requestAnimationFrame(function () {
                const rect = tooltip.getBoundingClientRect();
                const margin = 8;
                if (rect.left < margin) {
                    tooltip.style.transform = `translateX(calc(-50% + ${margin - rect.left}px))`;
                } else if (rect.right > window.innerWidth - margin) {
                    tooltip.style.transform = `translateX(calc(-50% - ${rect.right - (window.innerWidth - margin)}px))`;
                }
            });
        }

        // Same edge-clamping behavior for the CSS group-hover tooltips (progress bar
        // and card utilization tooltips) marked with the .edge-tooltip class.
        document.querySelectorAll('.edge-tooltip').forEach(function (tip) {
            const trigger = tip.parentElement;
            if (!trigger) return;
            trigger.addEventListener('mouseenter', function () {
                clampTooltipToViewport(tip);
            });
            trigger.addEventListener('mouseleave', function () {
                tip.style.transform = '';
            });
        });

        // Fixed color assignments for common accounts
        const fixedColorAssignments = {
            '5010101000': { color: 'bg-blue-500', hover: 'hover:bg-blue-600' },
            '5010201000': { color: 'bg-green-500', hover: 'hover:bg-green-600' },
            '5010301000': { color: 'bg-cyan-500', hover: 'hover:bg-cyan-600' },
            '5010402000': { color: 'bg-purple-500', hover: 'hover:bg-purple-600' },
            '5010499000': { color: 'bg-orange-500', hover: 'hover:bg-orange-600' },
            '5020101000': { color: 'bg-red-500', hover: 'hover:bg-red-600' },
            '5020321000': { color: 'bg-violet-500', hover: 'hover:bg-violet-600' },
        };

        const fallbackColorPalette = [
            { color: 'bg-pink-600', hover: 'hover:bg-pink-700' },
            { color: 'bg-indigo-600', hover: 'hover:bg-indigo-700' },
            { color: 'bg-teal-600', hover: 'hover:bg-teal-700' },
            { color: 'bg-lime-600', hover: 'hover:bg-lime-700' },
            { color: 'bg-amber-600', hover: 'hover:bg-amber-700' },
            { color: 'bg-rose-600', hover: 'hover:bg-rose-700' },
        ];

        function hashCode(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            return Math.abs(hash);
        }

        function getColorForAccount(accountCode) {
            if (fixedColorAssignments[accountCode]) {
                return fixedColorAssignments[accountCode].color;
            }
            const index = hashCode(accountCode) % fallbackColorPalette.length;
            return fallbackColorPalette[index].color;
        }

        function getHoverColorForAccount(accountCode) {
            if (fixedColorAssignments[accountCode]) {
                return fixedColorAssignments[accountCode].hover;
            }
            const index = hashCode(accountCode) % fallbackColorPalette.length;
            return fallbackColorPalette[index].hover;
        }

        function updateGraph() {
            const rows = document.querySelectorAll('#accountsTable tbody tr');
            const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
            
            const accountData = {};
            let totalAmount = 0;
            
            visibleRows.forEach(row => {
                const authorizedApprop = parseFloat(row.getAttribute('data-authorized-appropriations')) || 0;
                const accountCode = row.getAttribute('data-account-code');
                const description = row.cells[2].textContent.trim();
                
                if (!accountData[accountCode]) {
                    accountData[accountCode] = {
                        total: 0,
                        code: accountCode,
                        description: description
                    };
                }
                accountData[accountCode].total += authorizedApprop;
                totalAmount += authorizedApprop;
            });
            
            const sortedAccounts = Object.values(accountData).sort((a, b) => b.total - a.total);
            
            const stackedBarContainer = document.getElementById('stackedBarContainer');
            if (!stackedBarContainer) return;
            
            let barHTML = '<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 overflow-visible flex relative">';
            
            if (sortedAccounts.length === 0 || totalAmount === 0) {
                barHTML = `
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 flex items-center justify-center">
                        <span class="text-gray-500 dark:text-gray-400 text-sm italic">No data available</span>
                    </div>
                `;
            } else {
                sortedAccounts.forEach(accountItem => {
                    const percentage = totalAmount > 0 ? (accountItem.total / totalAmount) * 100 : 0;
                    const color = getColorForAccount(accountItem.code);
                    const hoverColor = getHoverColorForAccount(accountItem.code);
                    const truncatedDesc = accountItem.description.length > 25 
                        ? accountItem.description.substring(0, 25) + '...' 
                        : accountItem.description;
                    
                    barHTML += `
                        <div 
                            class="${color} ${hoverColor} h-8 transition-all duration-200 ease-out flex items-center justify-center relative cursor-pointer"
                            style="width: ${percentage}%"
                            onmouseenter="showTooltip(this)"
                            onmouseleave="hideTooltip(this)"
                        >
                            ${percentage > 8 ? `<span class="text-white text-xs font-semibold px-1 text-center truncate pointer-events-none">${truncatedDesc}</span>` : ''}
                            
                            <div class="tooltip-box absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap shadow-xl" style="display: none; z-index: 9999;">
                                <div class="font-semibold">${accountItem.code}</div>
                                <div class="text-[10px] max-w-xs truncate">${accountItem.description}</div>
                                <div>${percentage.toFixed(2)}%</div>
                                <div>${accountItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                    `;
                });
            }
            
            barHTML += '</div>';
            stackedBarContainer.innerHTML = barHTML;
            
            updateLegend(sortedAccounts, totalAmount);
        }

        function updateLegend(sortedAccounts, totalAmount) {
            const legendContainer = document.getElementById('graphLegend');
            if (!legendContainer) return;
            
            let legendHTML = '';
            const displayAccounts = sortedAccounts.slice(0, 5);
            
            displayAccounts.forEach(accountItem => {
                const percentage = totalAmount > 0 ? (accountItem.total / totalAmount) * 100 : 0;
                const color = getColorForAccount(accountItem.code);
                const truncatedDesc = accountItem.description.length > 40 
                    ? accountItem.description.substring(0, 40) + '...' 
                    : accountItem.description;
                const legendText = `${accountItem.code} - ${truncatedDesc}`;
                const fullText = `${accountItem.code} - ${accountItem.description}`;
                
                legendHTML += `
                    <div class="flex items-center space-x-2 text-xs">
                        <div class="w-4 h-4 ${color} rounded flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-700 dark:text-gray-300 truncate" title="${fullText}">
                                ${legendText}
                            </div>
                            <div class="text-gray-500 dark:text-gray-400">
                                ${percentage.toFixed(1)}%
                            </div>
                            <div class="text-gray-600 dark:text-gray-400 text-[10px]">
                                ${accountItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            if (sortedAccounts.length > 5) {
                legendHTML += `
                    <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 italic">
                        +${sortedAccounts.length - 5} more accounts
                    </div>
                `;
            }
            
            legendContainer.innerHTML = legendHTML;
        }

        const cardConfig = {
            'approved_appropriations': { column: 'data-appropriations' },
            'supplemental_appropriations': { column: 'data-supplementals' },
            'reversions': { column: 'data-reversions' },
            'realignments': { column: 'data-realignments' },
            'authorized_appropriations': { column: 'data-authorized-appropriations' },
            'allotments': { column: 'data-allotments' },
            'for_later_release': { column: 'data-for-later-release' },
            'obligations': { column: 'data-obligations' },
            'balance_appropriations': { column: 'data-balance-appropriations' },
            'balance_allotments': { column: 'data-balance-allotments' },
            'disbursements': { column: 'data-disbursements' },
            'disbursement_balance': { column: 'data-disbursement-balance' },
            'appropriation_accomplishment': { column: 'data-appropriation-accomplishment' },
            'allotment_accomplishment': { column: 'data-allotment-accomplishment' },
            'disbursements_to_obligations': { column: 'data-disbursements-to-obligations' },
            'disbursements_to_appropriations': { column: 'data-disbursements-to-appropriations' }
        };

        function updateCardValues() {
            const rows = document.querySelectorAll('#accountsTable tbody tr');
            const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');

            // Calculate totals from visible rows
            const totals = {};
            for (const [cardKey, config] of Object.entries(cardConfig)) {
                let total = 0;
                visibleRows.forEach(row => {
                    const value = parseFloat(row.getAttribute(config.column)) || 0;
                    total += value;
                });
                totals[cardKey] = total;
            }

            // Calculate percentage cards properly based on base values
            const obligations = totals['obligations'] || 0;
            const authorizedAppropriations = totals['authorized_appropriations'] || 0;
            const allotments = totals['allotments'] || 0;
            const disbursements = totals['disbursements'] || 0;

            // Calculate percentages using correct formulas
            const appropriationAccomplishment = authorizedAppropriations > 0 
                ? (obligations / authorizedAppropriations) * 100 
                : 0;
            
            const allotmentAccomplishment = allotments > 0 
                ? (obligations / allotments) * 100 
                : 0;
            
            const disbursementsToObligations = obligations > 0 
                ? (disbursements / obligations) * 100 
                : 0;
            
            const disbursementsToAppropriations = authorizedAppropriations > 0 
                ? (disbursements / authorizedAppropriations) * 100 
                : 0;

            // Helper function to format numbers
            function formatNumber(num) {
                return num.toLocaleString('en-US', {
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2
                });
            }

            // Update card values
            for (const [cardKey, total] of Object.entries(totals)) {
                const card = document.querySelector(`[data-card="${cardKey}"]`);
                if (card) {
                    const cardValue = card.querySelector('.card-value');
                    const circularProgress = card.querySelector('.circular-progress-bar');
                    const progressText = card.querySelector('text');
                    
                    if (cardValue) {
                        let percentage = 0;
                        
                        // Handle percentage cards specially
                        if (cardKey === 'appropriation_accomplishment') {
                            percentage = appropriationAccomplishment;
                            cardValue.textContent = formatNumber(percentage) + '%';
                            
                            // Update tooltip values
                            const tooltipObl = card.querySelector('.card-tooltip-obligations');
                            const tooltipAuthApprop = card.querySelector('.card-tooltip-auth-approp');
                            if (tooltipObl) tooltipObl.textContent = formatNumber(obligations);
                            if (tooltipAuthApprop) tooltipAuthApprop.textContent = formatNumber(authorizedAppropriations);
                            
                        } else if (cardKey === 'allotment_accomplishment') {
                            percentage = allotmentAccomplishment;
                            cardValue.textContent = formatNumber(percentage) + '%';
                            
                            // Update tooltip values
                            const tooltipObl = card.querySelector('.card-tooltip-obligations-allot');
                            const tooltipAllot = card.querySelector('.card-tooltip-allotments');
                            if (tooltipObl) tooltipObl.textContent = formatNumber(obligations);
                            if (tooltipAllot) tooltipAllot.textContent = formatNumber(allotments);
                            
                        } else if (cardKey === 'disbursements_to_obligations') {
                            percentage = disbursementsToObligations;
                            cardValue.textContent = formatNumber(percentage) + '%';
                            
                            // Update tooltip values
                            const tooltipDisb = card.querySelector('.card-tooltip-disbursements-ob');
                            const tooltipObl = card.querySelector('.card-tooltip-obligations-disb');
                            if (tooltipDisb) tooltipDisb.textContent = formatNumber(disbursements);
                            if (tooltipObl) tooltipObl.textContent = formatNumber(obligations);
                            
                        } else if (cardKey === 'disbursements_to_appropriations') {
                            percentage = disbursementsToAppropriations;
                            cardValue.textContent = formatNumber(percentage) + '%';
                            
                            // Update tooltip values
                            const tooltipDisb = card.querySelector('.card-tooltip-disbursements-ap');
                            const tooltipAuthApprop = card.querySelector('.card-tooltip-auth-approp-disb');
                            if (tooltipDisb) tooltipDisb.textContent = formatNumber(disbursements);
                            if (tooltipAuthApprop) tooltipAuthApprop.textContent = formatNumber(authorizedAppropriations);
                            
                        } else {
                            // Handle regular number cards
                            cardValue.textContent = formatNumber(total);
                        }
                        
                        // Update circular progress for percentage cards
                        if (circularProgress && percentage !== undefined) {
                            const cappedPercentage = Math.min(percentage, 100);
                            const dashArray = (cappedPercentage * 1.507).toFixed(2);
                            circularProgress.setAttribute('stroke-dasharray', `${dashArray} 150.7`);
                            circularProgress.setAttribute('data-percentage', percentage);
                            
                            if (progressText) {
                                progressText.textContent = Math.round(cappedPercentage) + '%';
                            }
                        }
                        
                        // Update color classes for supplementals, reversions, and realignments
                        if (cardKey === 'supplemental_appropriations') {
                            if (total > 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                                cardValue.classList.add('text-green-600', 'dark:text-green-400');
                            } else {
                                cardValue.classList.remove('text-green-600', 'dark:text-green-400');
                                cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                            }
                        } else if (cardKey === 'reversions') {
                            if (total > 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                                cardValue.classList.add('text-red-600', 'dark:text-red-400');
                            } else {
                                cardValue.classList.remove('text-red-600', 'dark:text-red-400');
                                cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                            }
                        } else if (cardKey === 'realignments') {
                            if (total < 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                                cardValue.classList.add('text-red-600', 'dark:text-red-400');
                            } else if (total > 0) {
                                cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                                cardValue.classList.add('text-green-600', 'dark:text-green-400');
                            } else {
                                cardValue.classList.remove('text-red-600', 'dark:text-red-400', 'text-green-600', 'dark:text-green-400');
                                cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                            }
                        }
                    }
                }
            }
        }

        function filterTable(searchValue) {
            const rows = document.querySelectorAll('#accountsTable tbody tr');
            const lowerSearch = String(searchValue).toLowerCase();

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(lowerSearch)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            updateCardValues();
            
            updateGraph();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                filterTable(this.value);
            });

            // Initial graph render
            setTimeout(() => {
                updateGraph();
            }, 100);
        });

// ============================================
// ANIMATED COUNTER FOR ACCOUNTS PAGE
// ============================================

/**
 * Animates a number from start to end value
 */
function animateCounter(element, start, end, duration = 1000, isPercentage = false) {
    const startTime = performance.now();
    const difference = end - start;
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation (easeOutExpo)
        const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        
        const currentValue = start + (difference * easeProgress);
        
        // Format the number
        const formattedValue = currentValue.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        element.textContent = formattedValue + (isPercentage ? '%' : '');
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

/**
 * Parse formatted number string to float
 */
function parseFormattedNumber(str) {
    if (!str) return 0;
    return parseFloat(str.replace(/,/g, '').replace('%', '')) || 0;
}

/**
 * Animate all card values with staggered timing
 */
function animateAllCards() {
    const cards = document.querySelectorAll('[data-card]');
    
    cards.forEach((card, index) => {
        const valueElement = card.querySelector('.card-value');
        if (!valueElement) return;
        
        const currentText = valueElement.textContent;
        const isPercentage = currentText.includes('%');
        const endValue = parseFormattedNumber(currentText);
        
        // Stagger animations slightly for visual effect
        setTimeout(() => {
            animateCounter(valueElement, 0, endValue, 1200, isPercentage);
        }, index * 50);
        
        // Also animate circular progress bars
        const circularProgress = card.querySelector('.circular-progress-bar');
        if (circularProgress) {
            const percentage = parseFloat(circularProgress.getAttribute('data-percentage')) || 0;
            animateCircularProgress(circularProgress, percentage);
        }
    });
}

/**
 * Animate circular progress bar
 */
function animateCircularProgress(element, targetPercentage, duration = 1200) {
    const startTime = performance.now();
    const cappedPercentage = Math.min(targetPercentage, 100);
    
    function updateProgress(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        const currentPercentage = cappedPercentage * easeProgress;
        const dashArray = (currentPercentage * 1.507).toFixed(2);
        
        element.setAttribute('stroke-dasharray', `${dashArray} 150.7`);
        
        // Update text inside circle
        const textElement = element.parentElement.querySelector('text');
        if (textElement) {
            textElement.textContent = Math.round(currentPercentage) + '%';
        }
        
        if (progress < 1) {
            requestAnimationFrame(updateProgress);
        }
    }
    
    requestAnimationFrame(updateProgress);
}

/**
 * Animate numeric values in accountsTable cells
 */


// ============================================
// HEATMAP TABLE ENHANCEMENT FOR ACCOUNTS
// ============================================

/**
 * Apply heatmap coloring to numeric table cells
 */
function applyHeatmap() {
    const table = document.getElementById('accountsTable');
    if (!table) return;
    
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
    if (rows.length === 0) return;
    
    // Get header row to identify specific columns
    const headerRow = table.querySelector('thead tr');
    if (!headerRow) return;
    
    const headers = Array.from(headerRow.cells).map(cell => cell.textContent.toLowerCase().trim());
    
    // Define exact column mappings for heatmap
    const columnMappings = [
        { names: ['approved appropriations'], type: 'value', color: 'blue' },
        { names: ['authorized appropriations'], type: 'value', color: 'blue' },
        { names: ['allotments'], type: 'value', color: 'green' },
        { names: ['obligations'], type: 'value', color: 'yellow' },
        { names: ['appropriation utilization'], type: 'percentage', color: 'blue' },
        { names: ['allotments utilization'], type: 'percentage', color: 'green' },
        { names: ['disbursements'], type: 'value', color: 'emerald' },
        { names: ['disbursements / oblgations', 'disbursements / obligations'], type: 'percentage', color: 'purple' },
        { names: ['disbursements / approp.', 'disbursements / appropriations'], type: 'percentage', color: 'amber' }
    ];
    
    // Find matching columns and apply heatmap
    columnMappings.forEach(mapping => {
        const colIndex = headers.findIndex(header => 
            mapping.names.some(name => header.includes(name))
        );
        
        if (colIndex === -1) return;
        
        const values = [];
        rows.forEach(row => {
            const cell = row.cells[colIndex];
            if (cell) {
                let value = 0;
                
                if (mapping.type === 'percentage') {
                    // Try to find progress bar or extract percentage value
                    const progressBar = cell.querySelector('[style*="width"]');
                    if (progressBar) {
                        const widthStr = progressBar.style.width;
                        value = parseFloat(widthStr) || 0;
                    } else {
                        const text = cell.textContent.trim();
                        const match = text.match(/(\d+(?:\.\d+)?)/);
                        if (match) {
                            value = parseFloat(match[1]);
                        }
                    }
                } else {
                    // Parse formatted number for value columns
                    value = parseFormattedNumber(cell.textContent);
                }
                
                values.push({ cell, value });
            }
        });
        
        if (values.length > 0) {
            applyColumnHeatmap(values, mapping.color, mapping.type);
        }
    });
}

function applyColumnHeatmap(values, colorName, type) {
    if (values.length === 0) return;
    
    // Calculate min and max
    const max = Math.max(...values.map(v => v.value));
    const min = Math.min(...values.map(v => v.value));
    const range = max - min;
    
    const colorPalettes = {
        'blue': { light: 'rgba(59, 130, 246, OPACITY)', dark: 'rgba(96, 165, 250, OPACITY)' },
        'green': { light: 'rgba(34, 197, 94, OPACITY)', dark: 'rgba(74, 222, 128, OPACITY)' },
        'red': { light: 'rgba(239, 68, 68, OPACITY)', dark: 'rgba(248, 113, 113, OPACITY)' },
        'yellow': { light: 'rgba(234, 179, 8, OPACITY)', dark: 'rgba(250, 204, 21, OPACITY)' },
        'purple': { light: 'rgba(168, 85, 247, OPACITY)', dark: 'rgba(196, 181, 253, OPACITY)' },
        'teal': { light: 'rgba(20, 184, 166, OPACITY)', dark: 'rgba(45, 212, 191, OPACITY)' },
        'emerald': { light: 'rgba(16, 185, 129, OPACITY)', dark: 'rgba(52, 211, 153, OPACITY)' },
    };
    
    const palette = colorPalettes[colorName] || colorPalettes['blue'];
    
    values.forEach(({ cell, value }, idx) => {
        if (range === 0) return;
        
        const normalized = (value - min) / range;
        const intensity = Math.round(normalized * 100);
        
        const baseOpacity = type === 'percentage' ? 0.15 : 0.1;
        const maxOpacity = type === 'percentage' ? 0.4 : 0.3;
        const opacity = baseOpacity + (intensity / 100) * maxOpacity;
        
        // Apply light mode color
        const lightColor = palette.light.replace('OPACITY', opacity);
        cell.style.backgroundColor = lightColor;
        cell.style.transition = 'background-color 0.3s ease';
        
        // Store dark mode color
        cell.setAttribute('data-dark-bg', palette.dark.replace('OPACITY', opacity * 0.6));
        
        // Apply dark mode if active
        if (document.documentElement.classList.contains('dark')) {
            cell.style.backgroundColor = palette.dark.replace('OPACITY', opacity * 0.6);
        }
        
        cell.classList.add('heatmap-cell-fade');
        cell.style.animationDelay = (idx % 10) * 30 + 'ms';
    });
}

/**
 * Remove heatmap coloring
 */
function removeHeatmap() {
    const table = document.getElementById('accountsTable');
    if (!table) return;
    
    const cells = table.querySelectorAll('tbody td');
    cells.forEach(cell => {
        cell.style.backgroundColor = '';
    });
}

/**
 * Toggle heatmap on/off
 */
let heatmapEnabled = false;
function toggleHeatmap() {
    heatmapEnabled = !heatmapEnabled;
    
    if (heatmapEnabled) {
        applyHeatmap();
    } else {
        removeHeatmap();
    }
    
    // Update toggle button if it exists
    const toggleBtn = document.getElementById('heatmapToggle');
    if (toggleBtn) {
        toggleBtn.textContent = heatmapEnabled ? '🎨 Disable Heatmap' : '🎨 Enable Heatmap';
    }
}

/**
 * Animate graph segments on initial load
 */
function animateGraphOnLoad() {
    const container = document.getElementById('stackedBarContainer');
    if (!container) return;
    
    // Wait for graph to be rendered first
    setTimeout(() => {
        const segments = container.querySelectorAll('[onmouseenter="showTooltip(this)"]');
        
        if (segments.length === 0) return;
        
        segments.forEach((segment, index) => {
            const targetWidth = segment.style.width || '0%';
            
            // Save original state
            const originalTransition = segment.style.transition;
            
            // Set to zero
            segment.style.transition = 'none';
            segment.style.width = '0%';
            segment.style.opacity = '0';
            
            // Force reflow
            void segment.offsetWidth;
            
            // Animate
            setTimeout(() => {
                segment.style.transition = 'width 0.8s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s ease-out';
                segment.style.width = targetWidth;
                segment.style.opacity = '1';
                
                // Restore original transition after animation
                setTimeout(() => {
                    segment.style.transition = originalTransition;
                }, 800);
            }, index * 80 + 100);
        });
    }, 200);
}

// toggleWidget function for accounts blade (handles analytics panel with account distribution and metrics)
function toggleWidget(widgetId) {
    if (widgetId === 'accountAnalyticsPanel') {
        const content = document.getElementById('accountAnalyticsPanelContent');
        const toggle = document.getElementById('accountAnalyticsPanelToggle');
        
        if (content && toggle) {
            const isHidden = content.style.display === 'none';
            content.style.display = isHidden ? 'block' : 'none';
            toggle.className = isHidden ? 'fas fa-circle-chevron-up' : 'fas fa-circle-chevron-down';
            
            // If opening the analytics panel, initialize charts
            if (isHidden) {
                setTimeout(() => {
                    initializeAccountAnalyticsCharts();
                }, 100);
            }
        }
    }
}

// ============================================
// ENHANCED UPDATE FUNCTIONS WITH ANIMATION
// ============================================

/**
 * Enhanced version of updateCardValues with animation
 */
function updateCardValuesAnimated() {
    const rows = document.querySelectorAll('#accountsTable tbody tr');
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');

    // Calculate totals from visible rows
    const totals = {};
    for (const [cardKey, config] of Object.entries(cardConfig)) {
        let total = 0;
        visibleRows.forEach(row => {
            const value = parseFloat(row.getAttribute(config.column)) || 0;
            total += value;
        });
        totals[cardKey] = total;
    }

    // Calculate percentage cards properly based on base values
    const obligations = totals['obligations'] || 0;
    const authorizedAppropriations = totals['authorized_appropriations'] || 0;
    const allotments = totals['allotments'] || 0;
    const disbursements = totals['disbursements'] || 0;

    const appropriationAccomplishment = authorizedAppropriations > 0 
        ? (obligations / authorizedAppropriations) * 100 
        : 0;
    
    const allotmentAccomplishment = allotments > 0 
        ? (obligations / allotments) * 100 
        : 0;
    
    const disbursementsToObligations = obligations > 0 
        ? (disbursements / obligations) * 100 
        : 0;
    
    const disbursementsToAppropriations = authorizedAppropriations > 0 
        ? (disbursements / authorizedAppropriations) * 100 
        : 0;

    // Update cards with animation
    let delay = 0;
    for (const [cardKey, total] of Object.entries(totals)) {
        const card = document.querySelector(`[data-card="${cardKey}"]`);
        if (card) {
            const cardValue = card.querySelector('.card-value');
            const circularProgress = card.querySelector('.circular-progress-bar');
            
            if (cardValue) {
                let targetValue = 0;
                let isPercentage = false;
                
                // Handle percentage cards specially
                if (cardKey === 'appropriation_accomplishment') {
                    targetValue = appropriationAccomplishment;
                    isPercentage = true;
                    
                    const tooltipObl = card.querySelector('.card-tooltip-obligations');
                    const tooltipAuthApprop = card.querySelector('.card-tooltip-auth-approp');
                    if (tooltipObl) tooltipObl.textContent = obligations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (tooltipAuthApprop) tooltipAuthApprop.textContent = authorizedAppropriations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (cardKey === 'allotment_accomplishment') {
                    targetValue = allotmentAccomplishment;
                    isPercentage = true;
                    
                    const tooltipObl = card.querySelector('.card-tooltip-obligations-allot');
                    const tooltipAllot = card.querySelector('.card-tooltip-allotments');
                    if (tooltipObl) tooltipObl.textContent = obligations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (tooltipAllot) tooltipAllot.textContent = allotments.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (cardKey === 'disbursements_to_obligations') {
                    targetValue = disbursementsToObligations;
                    isPercentage = true;
                    
                    const tooltipDisb = card.querySelector('.card-tooltip-disbursements-ob');
                    const tooltipObl = card.querySelector('.card-tooltip-obligations-disb');
                    if (tooltipDisb) tooltipDisb.textContent = disbursements.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (tooltipObl) tooltipObl.textContent = obligations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (cardKey === 'disbursements_to_appropriations') {
                    targetValue = disbursementsToAppropriations;
                    isPercentage = true;
                    
                    const tooltipDisb = card.querySelector('.card-tooltip-disbursements-ap');
                    const tooltipAuthApprop = card.querySelector('.card-tooltip-auth-approp-disb');
                    if (tooltipDisb) tooltipDisb.textContent = disbursements.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (tooltipAuthApprop) tooltipAuthApprop.textContent = authorizedAppropriations.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else {
                    targetValue = total;
                }
                
                // Get current value
                const currentValue = parseFormattedNumber(cardValue.textContent);
                
                // Animate from current to target
                setTimeout(() => {
                    animateCounter(cardValue, currentValue, targetValue, 800, isPercentage);
                }, delay);
                
                delay += 30;
                
                // Update circular progress
                if (circularProgress && isPercentage) {
                    setTimeout(() => {
                        animateCircularProgress(circularProgress, targetValue, 800);
                    }, delay - 30);
                }
                
                // Update color classes
                if (cardKey === 'supplemental_appropriations') {
                    if (total > 0) {
                        cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                        cardValue.classList.add('text-green-600', 'dark:text-green-400');
                    } else {
                        cardValue.classList.remove('text-green-600', 'dark:text-green-400');
                        cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                    }
                } else if (cardKey === 'reversions') {
                    if (total > 0) {
                        cardValue.classList.remove('text-gray-800', 'dark:text-gray-100');
                        cardValue.classList.add('text-red-600', 'dark:text-red-400');
                    } else {
                        cardValue.classList.remove('text-red-600', 'dark:text-red-400');
                        cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                    }
                } else if (cardKey === 'realignments') {
                    if (total < 0) {
                        cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-green-600', 'dark:text-green-400');
                        cardValue.classList.add('text-red-600', 'dark:text-red-400');
                    } else if (total > 0) {
                        cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-red-600', 'dark:text-red-400');
                        cardValue.classList.add('text-green-600', 'dark:text-green-400');
                    } else {
                        cardValue.classList.remove('text-red-600', 'dark:text-red-400', 'text-green-600', 'dark:text-green-400');
                        cardValue.classList.add('text-gray-800', 'dark:text-gray-100');
                    }
                }
            }
        }
    }
    
    // Apply heatmap after update
    if (heatmapEnabled) {
        setTimeout(() => applyHeatmap(), delay + 200);
    }
}

/**
 * Enhanced updateGraph with animation
 */
function updateGraphAnimated() {
    // Get all segments before updating
    const container = document.getElementById('stackedBarContainer');
    if (container) {
        const oldSegments = container.querySelectorAll('[onmouseenter="showTooltip(this)"]');
        
        // Store old widths for animation reference
        const oldWidths = Array.from(oldSegments).map(seg => seg.style.width);
    }
    
    updateGraph(); // Call original function to rebuild graph
    
    // Animate the new segments
    setTimeout(() => {
        const newSegments = container.querySelectorAll('[onmouseenter="showTooltip(this)"]');
        
        newSegments.forEach((segment, index) => {
            const targetWidth = segment.style.width || '0%';
            
            // Set initial state
            segment.style.transition = 'none';
            segment.style.width = '0%';
            segment.style.opacity = '0.5';
            
            // Force reflow
            void segment.offsetWidth;
            
            // Animate to target
            setTimeout(() => {
                segment.style.transition = 'width 0.6s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease-out';
                segment.style.width = targetWidth;
                segment.style.opacity = '1';
            }, index * 60);
        });
    }, 50);
}

    // ============================================
    // HEATMAP TOGGLE BUTTON
    // ============================================

    function addHeatmapToggle() {
        const tableHeader = document.querySelector('.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg.mt-4.mb-4 .flex.justify-between.items-center.mb-4');
        if (tableHeader && !document.getElementById('heatmapToggle')) {
            // Allow the header row to wrap on small screens instead of overflowing
            tableHeader.classList.add('flex-wrap', 'gap-2');

            const toggleButton = document.createElement('button');
            toggleButton.id = 'heatmapToggle';
            toggleButton.onclick = toggleHeatmap;
            toggleButton.className = 'text-blue-600 inline-flex leading-4 tracking-wider items-center justify-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-5 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none w-full sm:w-auto mt-2 sm:mt-0';
            toggleButton.innerHTML = '🎨 Enable Heatmap';

            tableHeader.appendChild(toggleButton);
        }
    }

    // ============================================
    // PROGRESS BAR ANIMATION
    // ============================================

    /**
     * Apply fade animation to all progress bars on page load
     */
    function animateProgressBars() {
        // Animate circular progress bars
        const circularBars = document.querySelectorAll('.circular-progress-bar');
        circularBars.forEach((bar) => {
            bar.style.animationDelay = '0ms';
        });

        // Animate linear progress bars (percentage columns)
        const linearBars = document.querySelectorAll('.bg-gradient-to-r');
        linearBars.forEach((bar) => {
            bar.classList.add('progress-bar-fade');
            bar.style.animationDelay = '0ms';
        });
    }

    // ============================================
    // INITIALIZATION
    // ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Add heatmap fade animation CSS
    const style = document.createElement('style');
    style.textContent = `
        .heatmap-cell-fade {
            animation: heatmapFadeIn 0.6s ease-out forwards;
        }
        
        @keyframes heatmapFadeIn {
            from {
                opacity: 0;
                background-color: transparent !important;
            }
            to {
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Add heatmap toggle button
    addHeatmapToggle();
    
    // Initialize progress bar animations
    setTimeout(() => {
        animateProgressBars();
    }, 200);
    
    // Initial animations on page load
    setTimeout(() => {
        animateAllCards();
        animateGraphOnLoad(); // Use the specific graph animation function
        if (heatmapEnabled) {
            applyHeatmap();
        }
    }, 300);
});

// Override the original updateCardValues function
const originalUpdateCardValues = window.updateCardValues;
if (typeof originalUpdateCardValues === 'function') {
    window.updateCardValues = function() {
        updateCardValuesAnimated();
    };
}

    
    // Store current appropriation context
    let currentAccountAppropriation = null;

    // Right-click context menu handler for accounts table
    document.addEventListener('DOMContentLoaded', function() {
        const accountsTable = document.getElementById('accountsTable');
        const contextMenu = document.getElementById('accountContextMenu');

        if (accountsTable) {
            accountsTable.addEventListener('contextmenu', function(event) {
                event.preventDefault();
                
                // Find the closest row
                const row = event.target.closest('tr');
                if (row && row.querySelector('td')) {
                    const appropriationId = row.dataset.appropriationId;
                    const accountCode = row.getAttribute('data-account-code');
                    const programs = row.querySelector('td:nth-child(1)')?.textContent?.trim();
                    const description = row.getAttribute('data-description');
                    
                    // Store context
                    currentAccountAppropriation = {
                        accountCode: accountCode,
                        description: description,
                        appropriationId: appropriationId,
                        programs: programs
                    };
                    
                    // Remove highlight from previously selected row
                    document.querySelectorAll('#accountsTable tbody tr.context-menu-active').forEach(r => {
                        r.classList.remove('context-menu-active');
                    });
                    
                    // Highlight the current row
                    row.classList.add('context-menu-active');
                    window.currentAccountContextMenuRow = row;
                    
                    // Position the context menu
                        contextMenu.style.left = event.clientX + 'px';
                        contextMenu.style.top = event.clientY + 'px';
                    contextMenu.classList.remove('hidden');
                }
            });

            // Hide context menu on click
            document.addEventListener('click', function(e) {
                if (!contextMenu.contains(e.target) && !e.target.closest('tr')) {
                    contextMenu.classList.add('hidden');
                    // Remove highlight when menu is closed
                    if (window.currentAccountContextMenuRow) {
                        window.currentAccountContextMenuRow.classList.remove('context-menu-active');
                        window.currentAccountContextMenuRow = null;
                    }
                }
            });
        }
    });


    /**
     * Setup card click handlers to highlight corresponding table columns
     */
    function setupCardClickHandlers() {
        // Mapping of card types to column header text patterns
        const cardToColumnHeader = {
            'approved_appropriations': 'Approved Appropriations',
            'supplemental_appropriations': 'Supplemental Appropriations',
            'reversions': 'Reversions',
            'realignments': 'Realignments',
            'authorized_appropriations': 'Authorized Appropriations',
            'allotments': 'Allotments',
            'for_later_release': 'For Later Release',
            'obligations': 'Obligations',
            'balance_appropriations': 'Authorized Approp. Balance',
            'appropriation_accomplishment': 'Appropriations Utilization',
            'balance_allotments': 'Allotments Balance',
            'allotment_accomplishment': 'Allotments Utilization',
            'disbursements': 'Disbursements',
            'disbursement_balance': 'Obligations Balance',
            'disbursements_to_obligations': 'Disbursements / Oblgations',
            'disbursements_to_appropriations': 'Disbursements / Approp.'
        };

        const cards = document.querySelectorAll('[data-card]');
        
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking toggle button
                if (e.target.closest('button')) return;
                
                // Scroll to table
                const table = document.getElementById('accountsTable');
                if (table) {
                    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Get the card type and find the corresponding header
                    const cardType = this.dataset.card;
                    const headerText = cardToColumnHeader[cardType];
                    
                    // Highlight the corresponding column
                    setTimeout(() => {
                        const headerCells = table.querySelectorAll('thead th');
                        let columnIndex = -1;
                        
                        // Find the column index by matching header text (exact match preferred)
                        headerCells.forEach((header, index) => {
                            const cellText = header.textContent.trim();
                            // Try exact match first
                            if (cellText === headerText) {
                                columnIndex = index;
                            }
                            // If no exact match and it's a partial match, use it as fallback
                            else if (columnIndex === -1 && cellText.includes(headerText)) {
                                columnIndex = index;
                            }
                        });
                        
                        if (columnIndex === -1) {
                            console.warn(`Column header not found for: ${headerText}`);
                            return;
                        }
                        
                        // Highlight header
                        headerCells[columnIndex].classList.add('highlight-column');
                        setTimeout(() => {
                            headerCells[columnIndex].classList.remove('highlight-column');
                        }, 1500);
                        
                        // Highlight all cells in the column
                        const bodyCells = table.querySelectorAll('tbody td');
                        bodyCells.forEach((cell, index) => {
                            if (index % (headerCells.length) === columnIndex) {
                                cell.classList.add('highlight-column');
                                setTimeout(() => {
                                    cell.classList.remove('highlight-column');
                                }, 1500);
                            }
                        });
                    }, 500);
                }
            });
        });
    }

    // Initialize card click handlers when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        setupCardClickHandlers();
        
        // Set up search input listener and apply initial filter
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            // Use a small delay to ensure DOM is fully rendered
            setTimeout(function() {
                // Apply filter if there's an initial search value
                if (searchInput.value.trim()) {
                    filterTable(searchInput.value);
                }
            }, 100);
            
            // Listen for search input changes
            searchInput.addEventListener('input', function() {
                filterTable(this.value);
            });
            
            // Prevent form submission when Enter is pressed in searchInput
            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        }
    });

// Override filterTable to include animations
const originalFilterTable = window.filterTable;
if (typeof originalFilterTable === 'function') {
    window.filterTable = function(searchValue) {
        const rows = document.querySelectorAll('#accountsTable tbody tr');
        const lowerSearch = String(searchValue).toLowerCase();

        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(lowerSearch)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        updateCardValuesAnimated();
        updateGraphAnimated();
    };
}

    /**
     * Initialize charts for Account Analytics Panel
     */
    function initializeAccountAnalyticsCharts() {
        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#d1d5db' : '#6b7280';
        const gridColor = isDarkMode ? '#4b5563' : '#e5e7eb';
        const bgColor = isDarkMode ? '#111827' : '#ffffff';
        
        // Prepare data from server-side collections
        @php
        $histogramCountData = isset($obligationRanges) ? array_map(function($r) { return $r['count']; }, $obligationRanges) : array_fill(0, 6, 0);
        $quarterCategories = isset($obligationsByQuarter) ? array_map(function($q) { return $q['quarter']; }, $obligationsByQuarter) : array_fill(0, 4, 'Q0');
        $quarterCountData = isset($obligationsByQuarter) ? array_map(function($q) { return $q['count']; }, $obligationsByQuarter) : array_fill(0, 4, 0);
        @endphp

        // Obligation Distribution Histogram
        const histogramData = {
            categories: [
                '< 10K',
                '10K - 50K',
                '50K - 100K',
                '100K - 500K',
                '500K - 1M',
                '> 1M'
            ],
            series: [{
                name: 'Count',
                data: @json($histogramCountData)
            }]
        };

        const histogramOptions = {
            chart: {
                type: 'bar',
                height: 260,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    distributed: true,
                    dataLabels: { position: 'top' }
                }
            },
            colors: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#ec4899'],
            dataLabels: {
                enabled: true,
                formatter: function(val) { return val; },
                style: { colors: [textColor] }
            },
            xaxis: { 
                categories: histogramData.categories,
                labels: {
                    style: { colors: textColor }
                },
                axisBorder: { color: gridColor }
            },
            yaxis: { 
                title: { text: 'Count', style: { color: textColor } },
                labels: {
                    formatter: function(val) { return Math.floor(val); },
                    style: { colors: textColor }
                }
            },
            tooltip: {
                y: { formatter: function(val) { return val + ' obligations'; } },
                theme: isDarkMode ? 'dark' : 'light',
                style: {
                    backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                    color: isDarkMode ? '#f3f4f6' : '#111827'
                }
            },
            grid: { borderColor: gridColor },
            legend: { show: false }
        };

        if (document.getElementById('accountObligationHistogram')) {
            new ApexCharts(document.getElementById('accountObligationHistogram'), { 
                ...histogramOptions, 
                series: [{
                    name: 'Obligations',
                    data: histogramData.series[0].data
                }]
            }).render();
        }

        // Obligations by Quarter Line Chart
        const obligationsByQuarter = @json($obligationsByQuarter);
        const categories = obligationsByQuarter.map(q => q.quarter);
        const counts = obligationsByQuarter.map(q => q.count);
        
        const quarterlyData = {
            categories: categories,
            series: [{
                name: 'Obligations Created',
                data: counts
            }]
        };

        const quarterlyOptions = {
            chart: {
                type: 'line',
                height: 260,
                toolbar: { show: false }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#3b82f6'],
            markers: {
                size: 6,
                colors: ['#3b82f6'],
                strokeWidth: 2,
                fillOpacity: 1
            },
            xaxis: { 
                categories: quarterlyData.categories,
                labels: {
                    style: { colors: textColor }
                },
                axisBorder: { color: gridColor }
            },
            yaxis: {
                title: { text: 'Number of Obligations', style: { color: textColor } },
                labels: {
                    formatter: function(val) { return Math.floor(val); },
                    style: { colors: textColor }
                }
            },
            tooltip: {
                y: { formatter: function(val) { return val + ' obligations created'; } },
                theme: isDarkMode ? 'dark' : 'light',
                style: {
                    backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                    color: isDarkMode ? '#f3f4f6' : '#111827'
                }
            },
            grid: { 
                borderColor: gridColor,
                strokeDashArray: 5
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 0.1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            }
        };

        if (document.getElementById('accountObligationsByQuarter')) {
            new ApexCharts(document.getElementById('accountObligationsByQuarter'), { 
                ...quarterlyOptions, 
                series: quarterlyData.series 
            }).render();
        }
    }
    </script>
