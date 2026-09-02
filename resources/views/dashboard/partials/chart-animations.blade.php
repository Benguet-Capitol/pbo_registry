{{-- Dashboard card & chart animation utilities: tooltips, color helpers, the stacked
     bar graph, circular progress rings, heatmap toggling, animated counters, sparklines,
     the Top Performers widget, and card click handlers. Extracted from dashboard.blade.php
     to keep that file's length manageable. --}}
    <script>
        // BAR GRAPH LOGIC

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

    // Fixed color palette with hover colors
    const fixedColorAssignments = {
        'PS': { color: 'bg-blue-500', hover: 'hover:bg-blue-600' },
        'MOOE': { color: 'bg-green-500', hover: 'hover:bg-green-600' },
        'CO': { color: 'bg-cyan-500', hover: 'hover:bg-cyan-600' },
        'FE': { color: 'bg-red-500', hover: 'hover:bg-red-600' },
        'CCO': { color: 'bg-violet-500', hover: 'hover:bg-violet-600' },
    };

    const fallbackColorPalette = [
        { color: 'bg-pink-600', hover: 'hover:bg-pink-700' },
        { color: 'bg-indigo-600', hover: 'hover:bg-indigo-700' },
        { color: 'bg-cyan-600', hover: 'hover:bg-cyan-700' },
        { color: 'bg-orange-600', hover: 'hover:bg-orange-700' },
        { color: 'bg-teal-600', hover: 'hover:bg-teal-700' },
        { color: 'bg-lime-600', hover: 'hover:bg-lime-700' },
        { color: 'bg-amber-600', hover: 'hover:bg-amber-700' },
        { color: 'bg-rose-600', hover: 'hover:bg-rose-700' },
        { color: 'bg-emerald-600', hover: 'hover:bg-emerald-700' },
        { color: 'bg-fuchsia-600', hover: 'hover:bg-fuchsia-700' },
        { color: 'bg-sky-600', hover: 'hover:bg-sky-700' },
        { color: 'bg-violet-600', hover: 'hover:bg-violet-700' }
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

    function getColorForClass(classCode) {
        if (fixedColorAssignments[classCode]) {
            return fixedColorAssignments[classCode].color;
        }
        const index = hashCode(classCode) % fallbackColorPalette.length;
        return fallbackColorPalette[index].color;
    }

    function getHoverColorForClass(classCode) {
        if (fixedColorAssignments[classCode]) {
            return fixedColorAssignments[classCode].hover;
        }
        const index = hashCode(classCode) % fallbackColorPalette.length;
        return fallbackColorPalette[index].hover;
    }

    function updateGraph() {
        const rows = document.querySelectorAll('#dashboardTable tbody tr');
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        
        const classData = {};
        let totalAmount = 0;
        
        visibleRows.forEach(row => {
            const authorizedApprop = parseFloat(row.getAttribute('data-authorized-appropriations')) || 0;
            const classCode = row.cells[2].textContent.trim();
            
            if (!classData[classCode]) {
                classData[classCode] = {
                    total: 0,
                    code: classCode
                };
            }
            classData[classCode].total += authorizedApprop;
            totalAmount += authorizedApprop;
        });
        
        const sortedClasses = Object.values(classData).sort((a, b) => b.total - a.total);
        
        const stackedBarContainer = document.getElementById('stackedBarContainer');
        if (!stackedBarContainer) return;
        
        let barHTML = '<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 overflow-visible flex relative">';
        
        if (sortedClasses.length === 0 || totalAmount === 0) {
            barHTML = `
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-lg h-8 flex items-center justify-center">
                    <span class="text-gray-500 dark:text-gray-400 text-sm italic">No data available</span>
                </div>
            `;
        } else {
            sortedClasses.forEach(classItem => {
                const percentage = totalAmount > 0 ? (classItem.total / totalAmount) * 100 : 0;
                const color = getColorForClass(classItem.code);
                const hoverColor = getHoverColorForClass(classItem.code);
                
                barHTML += `
                    <div 
                        class="${color} ${hoverColor} h-8 transition-all duration-200 ease-out flex items-center justify-center relative cursor-pointer stacked-segment"
                        style="width: ${percentage}%"
                        onmouseenter="showTooltip(this)"
                        onmouseleave="hideTooltip(this)"
                    >
                        ${percentage > 5 ? `<span class="text-white text-xs font-semibold px-1 text-center truncate pointer-events-none">${classItem.code}</span>` : ''}
                        
                        <div class="tooltip-box absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-3 py-2 whitespace-nowrap shadow-xl" style="display: none; z-index: 9999;">
                            <div class="font-semibold">${classItem.code}</div>
                            <div>${percentage.toFixed(2)}%</div>
                            <div>${classItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                        </div>
                    </div>
                `;
            });
        }
        
        barHTML += '</div>';
        stackedBarContainer.innerHTML = barHTML;
        
        // Animate the bar segments
        animateStackedBar();
        
        updateLegend(sortedClasses, totalAmount);
    }

    function updateLegend(sortedClasses, totalAmount) {
        const legendContainer = document.getElementById('graphLegend');
        if (!legendContainer) return;
        
        let legendHTML = '';
        sortedClasses.forEach(classItem => {
            const percentage = totalAmount > 0 ? (classItem.total / totalAmount) * 100 : 0;
            const color = getColorForClass(classItem.code);
            
            legendHTML += `
                <div class="flex items-center space-x-2 text-xs">
                    <div class="w-4 h-4 ${color} rounded flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-gray-700 dark:text-gray-300 truncate">
                            ${classItem.code}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400">
                            ${percentage.toFixed(1)}%
                        </div>
                        <div class="text-gray-600 dark:text-gray-400 text-[10px]">
                            ${classItem.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </div>
                    </div>
                </div>
            `;
        });
        
        legendContainer.innerHTML = legendHTML;
    }

    // ============================================
    // ANIMATED COUNTER ENHANCEMENT
    // ============================================

    /**
     * Animates a number from start to end value
     * @param {HTMLElement} element - The element containing the number
     * @param {number} start - Starting value
     * @param {number} end - Ending value
     * @param {number} duration - Animation duration in milliseconds
     * @param {boolean} isPercentage - Whether the value is a percentage
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
     * Animate numeric values in dashboardTable cells
     */


    // ============================================
    // HEATMAP TABLE ENHANCEMENT
    // ============================================

    /**
     * Apply heatmap coloring to numeric table cells
     */
    function applyHeatmap() {
        const table = document.getElementById('dashboardTable');
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
        const table = document.getElementById('dashboardTable');
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

    // ============================================
    // STACKED BAR GRAPH ANIMATION
    // ============================================

    /**
     * Animate stacked bar segments on update
     */
    function animateStackedBar() {
        const stackedSegments = document.querySelectorAll('.stacked-segment');
        
        stackedSegments.forEach((segment, index) => {
            // Reset animation by removing and re-adding the class
            segment.style.animation = 'none';
            
            // Trigger reflow to restart animation
            void segment.offsetWidth;
            
            // Apply animation with staggered timing
            setTimeout(() => {
                segment.style.animation = `barSlideIn 0.6s ease-out ${index * 0.1}s forwards`;
            }, 10);
        });
    }

    // ============================================
    // ENHANCED UPDATE FUNCTION
    // ============================================

    /**
     * Enhanced version of updateCardValues with animation
     */
    function updateCardValuesAnimated() {
        const rows = document.querySelectorAll('#dashboardTable tbody tr');
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');

        // Card configuration (same as original)
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

        // Calculate totals
        const totals = {};
        for (const [cardKey, config] of Object.entries(cardConfig)) {
            let total = 0;
            visibleRows.forEach(row => {
                const value = parseFloat(row.getAttribute(config.column)) || 0;
                total += value;
            });
            totals[cardKey] = total;
        }

        // Calculate percentages
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
                    } else if (cardKey === 'allotment_accomplishment') {
                        targetValue = allotmentAccomplishment;
                        isPercentage = true;
                    } else if (cardKey === 'disbursements_to_obligations') {
                        targetValue = disbursementsToObligations;
                        isPercentage = true;
                    } else if (cardKey === 'disbursements_to_appropriations') {
                        targetValue = disbursementsToAppropriations;
                        isPercentage = true;
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
                    
                    // Update color classes (same as original)
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
                        if (total > 0) {
                            cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-red-600', 'dark:text-red-400');
                            cardValue.classList.add('text-green-600', 'dark:text-green-400');
                        } else if (total < 0) {
                            cardValue.classList.remove('text-gray-800', 'dark:text-gray-100', 'text-green-600', 'dark:text-green-400');
                            cardValue.classList.add('text-red-600', 'dark:text-red-400');
                        } else {
                            cardValue.classList.remove('text-green-600', 'dark:text-green-400', 'text-red-600', 'dark:text-red-400');
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

    // ============================================
    // INITIALIZATION
    // ============================================

    // Add heatmap toggle button to the page
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Add heatmap toggle button
        addHeatmapToggle();
        
        // Initial animation
        setTimeout(() => {
            animateAllCards();
            if (heatmapEnabled) {
                applyHeatmap();
            }
        }, 300);
    });

    // Override the original updateCardValues function
    if (typeof updateCardValues === 'function') {
        const originalUpdateCardValues = updateCardValues;
        updateCardValues = function() {
            updateCardValuesAnimated();
        };
    }

    // Override filterTable to include heatmap update
    const originalFilterTable = window.filterTable;
    if (typeof originalFilterTable === 'function') {
        window.filterTable = function(searchValue) {
            originalFilterTable(searchValue);
            if (heatmapEnabled) {
                setTimeout(() => applyHeatmap(), 100);
            }
        };
    }

    // ============================================
    // MICRO-INTERACTIONS & CARD ANIMATIONS
    // ============================================

    /**
     * Add ripple effect on card click
     */
    function createRipple(event) {
        const card = event.currentTarget;
        const ripple = document.createElement('span');
        const rect = card.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');
        
        card.style.position = 'relative';
        card.style.overflow = 'hidden';
        card.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }

    // ============================================
    // TOP PERFORMERS WIDGET
    // ============================================

    /**
     * Create and display top performers widget
     */
    function createTopPerformersWidget() {
    // Check if widget exists (role-based)
    const widget = document.getElementById('topPerformersWidget');
    if (!widget) {
        // User doesn't have permission
        return;
    }
    
    const rows = document.querySelectorAll('#dashboardTable tbody tr');
    const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
    
    // Collect data grouped by office
    const officeData = {};
    
    visibleRows.forEach(row => {
        const office = row.cells[1]?.textContent.trim() || 'N/A';
        const obligations = parseFloat(row.getAttribute('data-obligations')) || 0;
        const authorized = parseFloat(row.getAttribute('data-authorized-appropriations')) || 0;
        
        if (!officeData[office]) {
            officeData[office] = {
                office: office,
                totalObligations: 0,
                totalAuthorized: 0
            };
        }
        
        officeData[office].totalObligations += obligations;
        officeData[office].totalAuthorized += authorized;
    });
    
    // Calculate utilization per office and sort
    const performanceData = Object.values(officeData).map(data => ({
        office: data.office,
        utilization: data.totalAuthorized > 0 ? (data.totalObligations / data.totalAuthorized) * 100 : 0,
        obligations: data.totalObligations,
        authorized: data.totalAuthorized
    }));
    
    // Sort by utilization (top 5)
    const topPerformers = performanceData
        .sort((a, b) => b.utilization - a.utilization)
        .slice(0, 5);
    
    // Build ONLY the content HTML (not the container)
    let html = '';
    
    topPerformers.forEach((item, index) => {
        const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `${index + 1}.`;
        const barWidth = Math.min(item.utilization, 100);
        
        // Color based on performance
        let barColor = 'bg-green-500';
        if (item.utilization < 50) barColor = 'bg-yellow-500';
        if (item.utilization < 25) barColor = 'bg-red-500';
        
        html += `
            <div class="performance-item bg-gray-50 dark:bg-gray-700 rounded-lg p-3 hover:shadow-md transition-shadow duration-200">
                <div class="flex justify-between items-center mb-1">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">${medal}</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-100">${item.office}</span>
                    </div>
                    <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">${item.utilization.toFixed(2)}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 overflow-hidden">
                    <div class="${barColor} h-2 rounded-full transition-all duration-500" style="width: ${barWidth}%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mt-1">
                    <span>Obligations: ${item.obligations.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                    <span>Authorized Appropriations: ${item.authorized.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                </div>
            </div>
        `;
    });
    
    // Update only the content div
    const contentDiv = document.getElementById('topPerformersContent');
    if (contentDiv) {
        contentDiv.innerHTML = html;
    }
}

    /**
     * Toggle widget visibility
     */
    function toggleWidget(widgetId) {
        let content, toggle;
        
        if (widgetId === 'analyticsPanel') {
            content = document.getElementById('analyticsPanelContent');
            toggle = document.getElementById('analyticsPanelToggle');
        } else if (widgetId === 'topPerformersWidget') {
            content = document.getElementById('topPerformersContent');
            toggle = document.getElementById('topPerformersToggle');
        } else if (widgetId === 'allotmentDistributionWidget') {
            content = document.getElementById('allotmentDistributionContent');
            toggle = document.getElementById('allotmentDistributionToggle');
        } else if (widgetId === 'volumeMetricsWidget') {
            content = document.getElementById('volumeMetricsContent');
            toggle = document.getElementById('volumeMetricsToggle');
        }
        
        if (content && toggle) {
            const isHidden = content.style.display === 'none';
            content.style.display = isHidden ? 'block' : 'none';
            toggle.className = isHidden ? 'fas fa-circle-chevron-up' : 'fas fa-circle-chevron-down';
            
            // If opening the analytics panel, trigger animation
            if (isHidden && widgetId === 'analyticsPanel') {
                setTimeout(() => {
                    animateStackedBar();
                }, 100);
            }
        }
    }

    /**
     * Toggle filters visibility
     */
    function toggleFilters() {
        const filterContent = document.getElementById('filterContent');
        const filterToggle = document.getElementById('filterToggle');
        
        if (filterContent && filterToggle) {
            const isHidden = filterContent.style.display === 'none';
            filterContent.style.display = isHidden ? 'grid' : 'none';
            filterToggle.className = isHidden ? 'fas fa-circle-chevron-up' : 'fas fa-circle-chevron-down';
        }
    }

    // Budget Alerts Widget removed - redundant with Top Performers

    /**
     * Smooth scroll and highlight when clicking card
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
            'balance_appropriations': 'Authorized Appropriations Balance',
            'appropriation_accomplishment': 'Appropriation Utilization',
            'balance_allotments': 'Allotments Balance',
            'allotment_accomplishment': 'Allotments Utilization',
            'disbursements': 'Disbursements',
            'disbursement_balance': 'Obligations Balance',
            'disbursements_to_obligations': 'Disbursements / Obligations',
            'disbursements_to_appropriations': 'Disbursements / Approp.'
        };

        const cards = document.querySelectorAll('[data-card]');
        
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking toggle button
                if (e.target.closest('button')) return;
                
                createRipple(e);
                
                // Scroll to table
                const table = document.getElementById('dashboardTable');
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
        // Add ripple CSS
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .performance-item {
                animation: slideIn 0.3s ease-out;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            .alert-item {
                animation: fadeIn 0.3s ease-out;
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .sparkline {
                opacity: 0.7;
                transition: opacity 0.3s ease;
            }
            
            .sparkline:hover {
                opacity: 1;
            }
            
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
        
        // Initialize progress bar animations
        setTimeout(() => {
            animateProgressBars();
        }, 200);
        
        // Initialize all enhancements
        setTimeout(() => {
            createTopPerformersWidget();
            setupCardClickHandlers();
        }, 500);
    });

    // Update widgets when filtering
    const originalFilterTable2 = window.filterTable;
    if (typeof originalFilterTable2 === 'function') {
        window.filterTable = function(searchValue) {
            originalFilterTable2(searchValue);
            setTimeout(() => {
                createTopPerformersWidget();
            }, 200);
        };
    }
    </script>
