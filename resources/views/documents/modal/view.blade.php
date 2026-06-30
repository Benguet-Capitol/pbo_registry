<div id="viewDocumentModal" style="display: none;" tabindex="-1" aria-labelledby="viewDocumentLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="relative w-full max-w-4xl max-h-screen bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600 flex-shrink-0">
            <h3 id="viewDocumentLabel" class="text-base font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-2">
                <i class="fas fa-file-pdf text-red-600 dark:text-red-400"></i>
                <span id="viewDocumentTitle">View Document</span>
            </h3>
            <button type="button" onclick="closeViewDocumentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- File Selector Tabs -->
        <div id="fileSelector" class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 px-4 py-2 overflow-x-auto flex-shrink-0">
            <div id="fileTabs" class="flex gap-2"></div>
        </div>

        <!-- PDF Viewer Container - Scrollable -->
        <div class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-900 flex flex-col items-center py-4 px-4" style="min-height: 0;">
            <div id="pdfLoading" class="flex items-center justify-center h-96">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600 dark:text-gray-300">Loading PDF...</p>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 flex-shrink-0">
            <a id="downloadPdfBtn" href="#" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-6 py-2 dark:bg-green-700 dark:hover:bg-green-800 dark:focus:ring-green-900" download>
                <i class="fas fa-download mr-2"></i>{{ __('Download') }}
            </a>
            <button type="button" onclick="closeViewDocumentModal()" class="text-gray-600 hover:text-white bg-gray-300 hover:bg-gray-400 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                {{ __('Close') }}
            </button>
        </div>
    </div>

    <style>
        @keyframes scaleInUp {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        let currentPdfUrl = null;
        let currentDocId = null;
        let currentFileId = null;
        let documentFiles = [];

        function openViewDocumentModal(docId, docTitle, fileName, files = null) {
            closeAllDropdowns();
            const modal = document.getElementById('viewDocumentModal');
            const modalContent = modal.querySelector('div.max-w-4xl');
            const titleSpan = document.getElementById('viewDocumentTitle');
            const downloadBtn = document.getElementById('downloadPdfBtn');
            const fileTabs = document.getElementById('fileTabs');

            // Set title
            titleSpan.textContent = docTitle;
            
            // Store document ID
            currentDocId = docId;
            
            // Handle files parameter (could be array or null)
            if (files && Array.isArray(files)) {
                documentFiles = files;
            } else {
                // Fallback to single file
                documentFiles = [
                    {
                        id: 'main',
                        filename: fileName,
                        file_path: `/documents/${docId}`,
                        is_main: true,
                    }
                ];
            }
            
            // Set initial file
            currentFileId = documentFiles[0].id;
            const firstFilePath = documentFiles[0].file_path.startsWith('/') 
                ? documentFiles[0].file_path 
                : `/documents/${docId}/files/${documentFiles[0].id}`;
            
            currentPdfUrl = firstFilePath;
            
            // Set download button href
            const downloadUrl = documentFiles[0].id === 'main'
                ? `/documents/${docId}/download`
                : `/documents/${docId}/files/${documentFiles[0].id}/download`;
            downloadBtn.href = downloadUrl;
            
            // Build file tabs
            fileTabs.innerHTML = '';
            documentFiles.forEach((file, index) => {
                const tab = document.createElement('button');
                tab.type = 'button';
                tab.className = `px-3 py-2 rounded-lg text-xs font-medium transition-colors ${
                    index === 0 
                        ? 'bg-blue-600 text-white' 
                        : 'bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-500'
                }`;
                tab.innerHTML = `<i class="fas fa-file-pdf text-red-500 mr-1"></i>${escapeHtml(file.filename)}`;
                tab.onclick = () => switchFile(file, tab);
                fileTabs.appendChild(tab);
            });
            
            // Show modal
            modal.style.display = 'flex';
            setTimeout(() => {
                if (modalContent) modalContent.classList.remove('hidden');
                loadPDF(currentPdfUrl);
            }, 0);
        }

        function switchFile(file, tabElement) {
            currentFileId = file.id;
            const filePath = file.file_path.startsWith('/') 
                ? file.file_path 
                : `/documents/${currentDocId}/files/${file.id}`;
            
            currentPdfUrl = filePath;
            
            // Update download button href
            const downloadBtn = document.getElementById('downloadPdfBtn');
            const downloadUrl = file.id === 'main'
                ? `/documents/${currentDocId}/download`
                : `/documents/${currentDocId}/files/${file.id}/download`;
            downloadBtn.href = downloadUrl;
            
            // Update tab styles
            document.querySelectorAll('#fileTabs button').forEach(btn => {
                btn.className = btn.className.replace('bg-blue-600 text-white', 'bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-500');
            });
            tabElement.className = 'px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-blue-600 text-white';
            
            // Reload PDF
            loadPDF(currentPdfUrl);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function loadPDF(url) {
            // Wait for PDF.js to be available
            const waitForPdfJs = () => {
                return new Promise((resolve) => {
                    if (window.pdfjsLib) {
                        resolve();
                    } else {
                        const checkInterval = setInterval(() => {
                            if (window.pdfjsLib) {
                                clearInterval(checkInterval);
                                resolve();
                            }
                        }, 100);
                        // Timeout after 5 seconds
                        setTimeout(() => {
                            clearInterval(checkInterval);
                            resolve();
                        }, 5000);
                    }
                });
            };

            waitForPdfJs().then(() => {
                if (!window.pdfjsLib) {
                    const loading = document.getElementById('pdfLoading');
                    loading.innerHTML = '<p class="text-red-600 dark:text-red-400">PDF.js library not loaded</p>';
                    return;
                }

                const pdfjsLib = window.pdfjsLib;
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                const loading = document.getElementById('pdfLoading');
                const container = loading.parentElement;

                pdfjsLib.getDocument(url).promise.then(pdf => {
                    // Clear any existing pages
                    container.querySelectorAll('canvas').forEach(c => c.remove());
                    
                    const numPages = pdf.numPages;
                    let pagesRendered = 0;

                    // Get container width for consistent scaling
                    const containerWidth = container.clientWidth - 40; // Account for padding

                    // Render all pages
                    const renderPages = async () => {
                        for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                            const page = await pdf.getPage(pageNum);
                            
                            // Calculate scale based on container width
                            const unscaledViewport = page.getViewport({ scale: 1 });
                            const scale = containerWidth / unscaledViewport.width;
                            const viewport = page.getViewport({ scale: scale });

                            // Create canvas for this page
                            const canvas = document.createElement('canvas');
                            canvas.id = 'pdfCanvas_' + pageNum;
                            canvas.width = viewport.width;
                            canvas.height = viewport.height;
                            canvas.style.maxWidth = '100%';
                            canvas.style.border = '1px solid #ddd';
                            canvas.style.marginBottom = '16px';
                            canvas.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                            
                            container.insertBefore(canvas, loading);

                            // Render page to canvas
                            await page.render({
                                canvasContext: canvas.getContext('2d'),
                                viewport: viewport
                            }).promise;

                            pagesRendered++;
                        }

                        // Hide loading spinner
                        loading.style.display = 'none';
                    };

                    renderPages().catch(err => {
                        console.error('Error rendering PDF pages:', err);
                        loading.innerHTML = '<p class="text-red-600 dark:text-red-400">Error loading PDF</p>';
                    });
                }).catch(err => {
                    console.error('Error loading PDF:', err);
                    loading.innerHTML = '<p class="text-red-600 dark:text-red-400">Error loading PDF: ' + err.message + '</p>';
                });
            });
        }

        function closeViewDocumentModal() {
            const modal = document.getElementById('viewDocumentModal');
            const modalContent = modal.querySelector('div.max-w-4xl');
            const loading = document.getElementById('pdfLoading');
            const container = loading.parentElement;

            if (modalContent) {
                modalContent.classList.add('hidden');
                setTimeout(() => {
                    modal.style.display = 'none';
                    // Clear all canvas pages
                    container.querySelectorAll('canvas').forEach(c => c.remove());
                    loading.style.display = 'flex';
                    currentPdfUrl = null;
                }, 300);
            } else {
                modal.style.display = 'none';
                currentPdfUrl = null;
            }
        }
    </script>
</div>
