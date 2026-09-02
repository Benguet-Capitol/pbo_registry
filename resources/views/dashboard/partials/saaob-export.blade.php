{{-- Success-alert dismissal, the SAAOB report export flow (button state + toast
     notifications), and the ApexCharts volume-metrics chart initializer. Extracted
     from dashboard.blade.php to keep that file's length manageable. --}}
    <script>
    // Close success alert and fade out
    function closeSuccessAlert() {
        const alert = document.getElementById('successAlert');
        if (alert) {
            alert.classList.remove('animate-slide-in');
            alert.classList.add('animate-fade-out');
            setTimeout(() => {
                alert.remove();
            }, 300);
        }
    }

    // Auto-fade success alert after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                closeSuccessAlert();
            }, 5000);
        }

        // Initialize Volume Metrics Charts
        initializeVolumeMetricsCharts();
    });

    /**
         * Trigger the SAAOB export via fetch so we can detect true completion
         * (success or failure) instead of guessing with a fixed timeout, then
         * save the returned file and show a toast.
         */
        async function exportSaaob(button, url) {
            if (button.disabled) return;

            const icon = document.getElementById('exportSaaobIcon');
            const label = document.getElementById('exportSaaobLabel');

            button.disabled = true;
            icon.className = 'fas fa-spinner fa-spin mr-2';
            label.textContent = 'Generating...';

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }
                });

                if (!response.ok) {
                    throw new Error('Export failed with status ' + response.status);
                }

                const blob = await response.blob();

                // Pull the real filename from Content-Disposition instead of hardcoding one
                let filename = 'SAAOB_export.xlsx';
                const disposition = response.headers.get('Content-Disposition');
                if (disposition) {
                    const match = disposition.match(/filename="?([^";]+)"?/);
                    if (match && match[1]) filename = match[1];
                }

                const blobUrl = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(blobUrl);

                showToast('SAAOB report generated successfully.', 'success');
            } catch (error) {
                console.error('SAAOB export error:', error);
                showToast('Failed to generate SAAOB report. Please try again.', 'error');
            } finally {
                button.disabled = false;
                icon.className = 'fas fa-file-excel mr-2';
                label.textContent = 'Generate SAAOB';
            }
        }

        /**
         * Lightweight toast notification, styled to match the existing success
         * alert on this page (top-right, auto-dismiss after 5s).
         */
        function showToast(message, type = 'success') {
            const existing = document.getElementById('saaobToast');
            if (existing) existing.remove();

            const isSuccess = type === 'success';
            const bgClass = isSuccess
                ? 'bg-green-50 border-green-300 text-green-800 dark:bg-green-900 dark:border-green-600 dark:text-green-100'
                : 'bg-red-50 border-red-300 text-red-800 dark:bg-red-900 dark:border-red-600 dark:text-red-100';
            const iconClass = isSuccess
                ? 'fas fa-check-circle text-green-600 dark:text-green-400'
                : 'fas fa-exclamation-circle text-red-600 dark:text-red-400';

            const toast = document.createElement('div');
            toast.id = 'saaobToast';
            toast.className = 'fixed top-4 right-4 left-4 sm:left-auto sm:top-6 sm:right-6 max-w-md z-50 animate-slide-in';
            toast.innerHTML = `
                <div class="${bgClass} border-2 px-4 py-3 sm:px-5 sm:py-4 rounded-xl shadow-2xl flex items-start gap-3">
                    <i class="${iconClass} mt-0.5 flex-shrink-0 text-lg"></i>
                    <p class="font-semibold text-sm flex-1">${message}</p>
                    <button type="button" onclick="document.getElementById('saaobToast')?.remove()" class="flex-shrink-0 opacity-70 hover:opacity-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s ease';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

    // Function to initialize volume metrics charts
    function initializeVolumeMetricsCharts() {
        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#d1d5db' : '#6b7280';
        const gridColor = isDarkMode ? '#4b5563' : '#e5e7eb';
        const bgColor = isDarkMode ? '#111827' : '#ffffff';
        
        // Obligation Distribution Histogram
        const obligationRanges = @json($obligationRanges);
        const ranges = obligationRanges.map(r => r.label);
        const counts = obligationRanges.map(r => r.count);

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
                categories: ranges,
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

        const histogramSeries = [{
            name: 'Obligations',
            data: counts
        }];

        new ApexCharts(document.querySelector('#obligationHistogram'), { 
            ...histogramOptions, 
            series: histogramSeries 
        }).render();

        // Obligations by Quarter Chart
        const obligationsByQuarter = @json($obligationsByQuarter);
        const quarters = obligationsByQuarter.map(q => q.quarter);
        const quarterCounts = obligationsByQuarter.map(q => q.count);

        const quarterOptions = {
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
                categories: quarters,
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

        const quarterSeries = [{
            name: 'Obligations Created',
            data: quarterCounts
        }];

        new ApexCharts(document.querySelector('#obligationsByQuarter'), { 
            ...quarterOptions, 
            series: quarterSeries 
        }).render();
    }
    </script>
