<!-- Purchase Order Files Modal -->
<div id="purchaseOrderFilesModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-2xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900 dark:to-emerald-900 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-upload text-green-600 dark:text-green-400"></i>
                <div class="flex flex-col">
                    <span>Purchase Order Files</span>
                    <span id="modalPurchaseOrderNo" class="text-xs font-normal text-gray-600 dark:text-gray-300">PO Number: -</span>
                </div>
            </h3>
            <button type="button" onclick="closePurchaseOrderFilesModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
            <!-- File Upload Section -->
             @role ('Administrator|Developer|Obligation')
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Upload Files</h4>
                <div class="border-2 border-dashed border-green-300 dark:border-green-600 rounded-lg p-6 text-center cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                    id="purchaseOrderFileDropZone"
                    onclick="document.getElementById('purchaseOrderFileInput').click()">
                    <i class="fas fa-cloud-upload-alt text-3xl text-green-500 mb-2"></i>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Drag and drop files here or click to select</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max 200 MB per file. Multiple files supported.</p>
                </div>
                <input type="file" id="purchaseOrderFileInput" multiple style="display: none;" onchange="handlePurchaseOrderFileSelect(event)">
                <div id="purchaseOrderUploadProgress" class="mt-3" style="display: none;">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div id="purchaseOrderProgressBar" class="bg-green-500 h-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><span id="purchaseOrderProgressText">0</span>%</p>
                </div>
            </div>
            @endrole

            <!-- Files List Section -->
            <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Uploaded Files</h4>
                <div id="purchaseOrderFilesList" class="space-y-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No files uploaded yet</p>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closePurchaseOrderFilesModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- File View Modal -->
<div id="purchaseOrderViewFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-7xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out; height: 90vh; max-height: 90vh;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900 dark:to-blue-900 dark:border-gray-600 flex-shrink-0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-eye text-cyan-600 dark:text-cyan-400"></i>
                <span id="purchaseOrderViewFileName">View File</span>
            </h3>
            <button type="button" onclick="closePurchaseOrderViewFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="flex-1 overflow-auto">
            <div id="purchaseOrderFileViewContent" class="h-full bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600 flex flex-col">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400 dark:text-gray-500 mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400">Loading file...</p>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closePurchaseOrderViewFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- File Edit Modal -->
<div id="purchaseOrderEditFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900 dark:to-yellow-900 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                Rename File
            </h3>
            <button type="button" onclick="closePurchaseOrderEditFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-6 py-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">New File Name</label>
            <input type="text" id="purchaseOrderEditFileName" class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Enter new file name">
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closePurchaseOrderEditFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button type="button" onclick="submitPurchaseOrderEditFileName()" class="text-white inline-flex leading-4 tracking-wider bg-amber-600 hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-save mr-2"></i>
                Save
            </button>
        </div>
    </div>
</div>

<!-- File Delete Modal -->
<div id="purchaseOrderDeleteFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900 dark:to-pink-900 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-trash text-red-600 dark:text-red-400"></i>
                Delete File
            </h3>
            <button type="button" onclick="closePurchaseOrderDeleteFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 dark:text-gray-300 text-sm">
                Are you sure you want to delete this file? This action cannot be undone.
            </p>
            <p class="text-gray-600 dark:text-gray-400 text-xs mt-2 italic">
                <span id="purchaseOrderDeleteFileName">File</span>
            </p>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closePurchaseOrderDeleteFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button type="button" onclick="submitPurchaseOrderDeleteFile()" class="text-white inline-flex leading-4 tracking-wider bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-trash mr-2"></i>
                Delete
            </button>
        </div>
    </div>
</div>

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

    #purchaseOrderFileDropZone.drag-over {
        background-color: #f0fdf4;
        border-color: #16a34a;
    }

    .dark #purchaseOrderFileDropZone.drag-over {
        background-color: rgba(34, 197, 94, 0.1);
        border-color: #16a34a;
    }

    /* Tooltip styling */
    button[title], a[title] {
        position: relative;
    }

    button[title]:hover::after, 
    a[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.5rem 0.75rem;
        background-color: #1f2937;
        color: white;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
        animation: tooltipFadeIn 0.2s ease-in-out;
    }

    .dark button[title]:hover::after,
    .dark a[title]:hover::after {
        background-color: #374151;
        color: #f3f4f6;
    }

    button[title]:hover::before, 
    a[title]:hover::before {
        content: '';
        position: absolute;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #1f2937;
        z-index: 1000;
        pointer-events: none;
        animation: tooltipFadeIn 0.2s ease-in-out;
    }

    .dark button[title]:hover::before,
    .dark a[title]:hover::before {
        border-top-color: #374151;
    }

    @keyframes tooltipFadeIn {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(5px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .animate-slideInRight {
        animation: slideInRight 0.3s ease-out;
    }

    .animate-slideOutRight {
        animation: slideOutRight 0.3s ease-in;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    // Set up PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let currentPurchaseOrderNo = null;
    let currentPurchaseOrderViewFileId = null;

    /**
     * Open the purchase order files modal
     */
    function openPurchaseOrderFilesModal(poNumber) {
        currentPurchaseOrderNo = poNumber;
        const modal = document.getElementById('purchaseOrderFilesModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        
        // Update modal header with PO Number
        const poNumberElement = document.getElementById('modalPurchaseOrderNo');
        if (poNumberElement && poNumber) {
            poNumberElement.textContent = `PO Number: ${poNumber}`;
        }
        
        // Load files for this purchase order
        loadPurchaseOrderFiles(poNumber);
        
        // Setup drag and drop
        setupPurchaseOrderDragAndDrop();
    }

    /**
     * Close the purchase order files modal
     */
    function closePurchaseOrderFilesModal() {
        const modal = document.getElementById('purchaseOrderFilesModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        currentPurchaseOrderNo = null;
    }

    /**
     * Handle file selection for purchase orders
     */
    function handlePurchaseOrderFileSelect(event) {
        const files = event.target.files;
        if (files.length > 0) {
            uploadPurchaseOrderFiles(files);
        }
    }

    /**
     * Upload files to the server for purchase order
     */
    function uploadPurchaseOrderFiles(files) {
        const uploadProgress = document.getElementById('purchaseOrderUploadProgress');
        const progressBar = document.getElementById('purchaseOrderProgressBar');
        const progressText = document.getElementById('purchaseOrderProgressText');
        
        uploadProgress.style.display = 'block';
        let loadedCount = 0;
        const totalFiles = files.length;
        
        Array.from(files).forEach(file => {
            // Validate file size (200 MB = 209715200 bytes)
            if (file.size > 200 * 1024 * 1024) {
                showPurchaseOrderToast(`File "${file.name}" exceeds 200 MB limit`, 'error');
                loadedCount++;
                if (loadedCount === totalFiles) resetProgress();
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('po_number', currentPurchaseOrderNo);

            const xhr = new XMLHttpRequest();
            
            // Track upload progress with real-time percentage
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percentComplete + '%';
                    progressText.textContent = percentComplete;
                }
            });

            xhr.addEventListener('load', () => {
                loadedCount++;
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (xhr.status === 200 && data.success) {
                        showPurchaseOrderToast(`File "${file.name}" uploaded successfully`, 'create');
                        loadPurchaseOrderFiles(currentPurchaseOrderNo);
                    } else {
                        showPurchaseOrderToast(`Error uploading "${file.name}": ${data.message || 'Unknown error'}`, 'error');
                    }
                } catch (e) {
                    showPurchaseOrderToast(`Error uploading "${file.name}": Invalid response`, 'error');
                }
                
                if (loadedCount === totalFiles) resetProgress();
            });

            xhr.addEventListener('error', () => {
                loadedCount++;
                showPurchaseOrderToast(`Error uploading "${file.name}": Network error`, 'error');
                if (loadedCount === totalFiles) resetProgress();
            });

            xhr.addEventListener('abort', () => {
                loadedCount++;
                showPurchaseOrderToast(`Upload cancelled for "${file.name}"`, 'error');
                if (loadedCount === totalFiles) resetProgress();
            });

            xhr.open('POST', '/purchase-order-files/upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            xhr.send(formData);
        });

        function resetProgress() {
            progressBar.style.width = '0%';
            progressText.textContent = '0';
            uploadProgress.style.display = 'none';
            document.getElementById('purchaseOrderFileInput').value = '';
        }
    }

    /**
     * Check if a file type is viewable in browser
     */
    function isPurchaseOrderFileViewable(fileType) {
        const viewableTypes = [
            // Images - Common formats
            /^image\/jpeg/,
            /^image\/jpg/,
            /^image\/png/,
            /^image\/gif/,
            /^image\/webp/,
            /^image\/svg/,
            /^image\/bmp/,
            /^image\/tiff/,
            /^image\/x-icon/,
            /^image\//,  // Catch all other image types
            // PDF
            /^application\/pdf$/,
            // Text files
            /^text\//,
            /application\/json/,
            /application\/xml/,
            // Video
            /^video\//,
            // Audio
            /^audio\//,
        ];

        return viewableTypes.some(pattern => pattern.test(fileType));
    }

    /**
     * Load files for a purchase order by po_number
     */
    function loadPurchaseOrderFiles(poNumber) {
        fetch(`/purchase-order-files/get?po_number=${encodeURIComponent(poNumber)}`)
            .then(response => response.json())
            .then(data => {
                const filesList = document.getElementById('purchaseOrderFilesList');
                
                if (data.success && data.files.length > 0) {
                    filesList.innerHTML = data.files.map(file => {
                        const isViewable = isPurchaseOrderFileViewable(file.file_type);
                        return `
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3 flex-1">
                                <i class="fas fa-file text-gray-400 dark:text-gray-500"></i>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">${file.original_file_name}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ${file.formatted_file_size} • Uploaded by ${file.uploader_name} on ${new Date(file.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                ${isViewable ? `<button onclick="openPurchaseOrderViewFileModal(${file.id}, '${file.original_file_name}')" title="View file preview" aria-label="View file preview" class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-800 dark:hover:text-cyan-300 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>` : ''}
                                <a href="/purchase-order-files/${file.id}/download" title="Download file" aria-label="Download file" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button onclick="openPurchaseOrderEditFileModal(${file.id}, '${file.original_file_name}')" title="Rename file" aria-label="Rename file" class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openPurchaseOrderDeleteFileModal(${file.id}, '${file.original_file_name}')" title="Delete file" aria-label="Delete file" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    }).join('');
                } else {
                    filesList.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No files uploaded yet</p>';
                }
            })
            .catch(error => {
                console.error('Error loading files:', error);
                showPurchaseOrderToast('Error loading files', 'error');
            });
    }

    /**
     * Edit file name
     */
    function openPurchaseOrderEditFileModal(fileId, currentName) {
        window.currentPurchaseOrderEditFileId = fileId;
        document.getElementById('purchaseOrderEditFileName').value = currentName;
        const modal = document.getElementById('purchaseOrderEditFileModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.getElementById('purchaseOrderEditFileName').focus();
    }

    function closePurchaseOrderEditFileModal() {
        const modal = document.getElementById('purchaseOrderEditFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        window.currentPurchaseOrderEditFileId = null;
    }

    function submitPurchaseOrderEditFileName() {
        const newName = document.getElementById('purchaseOrderEditFileName').value.trim();
        if (!newName) {
            showPurchaseOrderToast('Please enter a file name', 'error');
            return;
        }

        fetch(`/purchase-order-files/${window.currentPurchaseOrderEditFileId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ original_file_name: newName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPurchaseOrderToast('File name updated successfully', 'edit');
                loadPurchaseOrderFiles(currentPurchaseOrderNo);
                closePurchaseOrderEditFileModal();
            } else {
                showPurchaseOrderToast(`Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            showPurchaseOrderToast(`Error: ${error.message}`, 'error');
        });
    }

    /**
     * Delete file
     */
    function openPurchaseOrderDeleteFileModal(fileId, fileName) {
        window.currentPurchaseOrderDeleteFileId = fileId;
        document.getElementById('purchaseOrderDeleteFileName').textContent = fileName;
        const modal = document.getElementById('purchaseOrderDeleteFileModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closePurchaseOrderDeleteFileModal() {
        const modal = document.getElementById('purchaseOrderDeleteFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        window.currentPurchaseOrderDeleteFileId = null;
    }

    function submitPurchaseOrderDeleteFile() {
        fetch(`/purchase-order-files/${window.currentPurchaseOrderDeleteFileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPurchaseOrderToast('File deleted successfully', 'delete');
                loadPurchaseOrderFiles(currentPurchaseOrderNo);
                closePurchaseOrderDeleteFileModal();
            } else {
                showPurchaseOrderToast(`Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            showPurchaseOrderToast(`Error: ${error.message}`, 'error');
        });
    }

    /**
     * View file
     */
    function openPurchaseOrderViewFileModal(fileId, fileName) {
        window.currentPurchaseOrderViewFileId = fileId;
        const modal = document.getElementById('purchaseOrderViewFileModal');
        const content = document.getElementById('purchaseOrderFileViewContent');
        document.getElementById('purchaseOrderViewFileName').textContent = fileName;
        
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        // Load file content
        fetch(`/purchase-order-files/${fileId}/preview`)
            .then(response => response.json())
            .then(data => {
                console.log('Preview data received:', data);
                if (data.success) {
                    renderPurchaseOrderFilePreview(data.file_type, data.file_url, data.file_path);
                } else {
                    content.innerHTML = `<div class="text-center py-8"><i class="fas fa-exclamation-circle text-2xl text-red-500 mb-3"></i><p class="text-red-600 dark:text-red-400">Unable to preview this file type</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching preview:', error);
                // Fallback: provide download link
                content.innerHTML = `<div class="text-center py-8"><i class="fas fa-file text-4xl text-gray-400 dark:text-gray-500 mb-3"></i><p class="text-gray-600 dark:text-gray-400 mb-4">This file cannot be previewed</p><a href="/purchase-order-files/${fileId}/download" class="text-blue-600 dark:text-blue-400 hover:underline">Click here to download</a></div>`;
            });
    }

    function closePurchaseOrderViewFileModal() {
        const modal = document.getElementById('purchaseOrderViewFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    function renderPurchaseOrderFilePreview(fileType, fileUrl, filePath) {
        const content = document.getElementById('purchaseOrderFileViewContent');
        const fileId = window.currentPurchaseOrderViewFileId;
        
        console.log('Rendering preview for:', { fileType, fileUrl, fileId });
        
        // Clear previous content
        content.innerHTML = '';
        
        // Image types - PNG, JPG, GIF, WebP, SVG, BMP, TIFF, etc.
        if (fileType.startsWith('image/')) {
            const container = document.createElement('div');
            container.className = 'flex-1 overflow-auto flex items-center justify-center';
            
            const img = document.createElement('img');
            img.src = fileUrl;
            img.alt = 'File preview';
            img.className = 'rounded-lg';
            img.style.cssText = 'max-width: 100%; max-height: 100%; object-fit: contain;';
            
            img.onerror = function() {
                console.error('Image failed to load from:', fileUrl);
                container.innerHTML = '';
                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-center py-8 flex-1 flex items-center justify-center';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle text-2xl text-red-500 mb-3"></i><p class="text-red-600 dark:text-red-400">Failed to load image.</p>';
                const link = document.createElement('a');
                link.href = fileUrl;
                link.textContent = 'Try downloading instead';
                link.className = 'text-blue-600 hover:underline';
                errorDiv.appendChild(document.createElement('br'));
                errorDiv.appendChild(link);
                container.appendChild(errorDiv);
            };
            
            container.appendChild(img);
            content.appendChild(container);
            return;
        }

        // PDF - Check if it's image-based or regular PDF
        if (fileType === 'application/pdf') {
            renderPurchaseOrderPdfPreview(fileUrl);
            return;
        }

        // Text files
        if (fileType.startsWith('text/') || fileType.includes('application/json') || fileType.includes('application/xml') || fileType.includes('application/vnd.openxmlformats')) {
            fetch(`${fileUrl}?preview=true`)
                .then(response => response.text())
                .then(text => {
                    const lines = text.split('\n').slice(0, 100).join('\n');
                    content.innerHTML = `<pre class="flex-1 bg-gray-900 text-gray-100 p-4 rounded-lg overflow-auto text-xs line-numbers"><code>${escapePurchaseOrderHtml(lines)}${text.split('\n').length > 100 ? '\n\n... (file truncated)' : ''}</code></pre>`;
                })
                .catch(() => {
                    content.innerHTML = '';
                    const container = document.createElement('div');
                    container.className = 'flex-1 flex items-center justify-center text-center py-8';
                    container.innerHTML = '<p class="text-gray-600 dark:text-gray-400 mb-4">File preview temporarily unavailable</p>';
                    const link = document.createElement('a');
                    link.href = fileUrl;
                    link.download = true;
                    link.textContent = 'Download file';
                    link.className = 'text-blue-600 dark:text-blue-400 hover:underline';
                    container.appendChild(link);
                    content.appendChild(container);
                });
            return;
        }

        // Video types
        if (fileType.startsWith('video/')) {
            const container = document.createElement('div');
            container.className = 'flex-1 flex items-center justify-center';
            
            const video = document.createElement('video');
            video.controls = true;
            video.className = 'w-full rounded-lg bg-black';
            video.style.cssText = 'max-height: 100%; object-fit: contain;';
            
            const source = document.createElement('source');
            source.src = fileUrl;
            source.type = fileType;
            
            video.appendChild(source);
            video.appendChild(document.createTextNode('Your browser does not support the video tag.'));
            
            container.appendChild(video);
            content.appendChild(container);
            return;
        }

        // Audio types
        if (fileType.startsWith('audio/')) {
            const container = document.createElement('div');
            container.className = 'flex-1 flex items-center justify-center';
            
            const audio = document.createElement('audio');
            audio.controls = true;
            audio.className = 'w-full rounded-lg';
            
            const source = document.createElement('source');
            source.src = fileUrl;
            source.type = fileType;
            
            audio.appendChild(source);
            audio.appendChild(document.createTextNode('Your browser does not support the audio element.'));
            
            container.appendChild(audio);
            content.appendChild(container);
            return;
        }

        // Default
        const container = document.createElement('div');
        container.className = 'flex-1 flex flex-col items-center justify-center text-center';
        container.innerHTML = '<i class="fas fa-file text-4xl text-gray-400 dark:text-gray-500 mb-3"></i><p class="text-gray-600 dark:text-gray-400 mb-4">This file type cannot be previewed</p>';
        const link = document.createElement('a');
        link.href = fileUrl;
        link.download = true;
        link.textContent = 'Click here to download';
        link.className = 'text-blue-600 dark:text-blue-400 hover:underline';
        container.appendChild(link);
        content.appendChild(container);
    }

    function escapePurchaseOrderHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Render PDF preview - detects if image-based or regular PDF
     */
    async function renderPurchaseOrderPdfPreview(fileUrl) {
        const content = document.getElementById('purchaseOrderFileViewContent');
        
        try {
            // Load the PDF
            const pdf = await pdfjsLib.getDocument(fileUrl).promise;
            
            // Get first page to detect if it's image-based
            const page = await pdf.getPage(1);
            const operatorList = await page.getOperatorList();
            
            // Check if PDF contains text (true PDF) or just images (scanned document)
            const hasText = operatorList.fnArray.some(fn => {
                const fnName = pdfjsLib.OPS[fn];
                return fnName && (fnName.includes('Text') || fnName.includes('Show'));
            });

            if (!hasText && pdf.numPages <= 5) {
                // It's likely an image-based PDF (scanned document), render as image
                await renderPurchaseOrderPdfAsImage(fileUrl, page);
            } else {
                // Regular PDF with text, use iframe
                content.innerHTML = `<iframe src="${fileUrl}" class="w-full flex-1 rounded-lg border border-gray-300 dark:border-gray-600"></iframe>`;
            }
        } catch (error) {
            console.error('Error rendering PDF:', error);
            // Fallback to iframe for corrupted or problematic PDFs
            content.innerHTML = `<iframe src="${fileUrl}" class="w-full flex-1 rounded-lg border border-gray-300 dark:border-gray-600"></iframe>`;
        }
    }

    /**
     * Render image-based PDF as image
     */
    async function renderPurchaseOrderPdfAsImage(fileUrl, firstPage) {
        const content = document.getElementById('purchaseOrderFileViewContent');
        
        try {
            const viewport = firstPage.getViewport({ scale: 2 });
            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            const context = canvas.getContext('2d');

            await firstPage.render({ canvasContext: context, viewport }).promise;
            
            const imageDataUrl = canvas.toDataURL('image/png');
            content.innerHTML = `
                <div class="flex flex-col flex-1 items-center justify-center">
                    <div class="flex-1 overflow-auto w-full flex items-center justify-center">
                        <img src="${imageDataUrl}" alt="PDF preview" class="rounded-lg" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 flex-shrink-0">This is an image-based PDF (scanned document)</p>
                </div>
            `;
        } catch (error) {
            console.error('Error rendering PDF as image:', error);
            // Fallback to iframe
            content.innerHTML = `<iframe src="${fileUrl}" class="w-full h-96 rounded-lg border border-gray-300 dark:border-gray-600"></iframe>`;
        }
    }

    /**
     * Setup drag and drop for purchase orders
     */
    function setupPurchaseOrderDragAndDrop() {
        const dropZone = document.getElementById('purchaseOrderFileDropZone');
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                uploadPurchaseOrderFiles(files);
            }
        });
    }

    /**
     * Show toast notification for purchase orders
     */
    function showPurchaseOrderToast(message, type = 'success') {
        const toastId = 'toast_' + Date.now();
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        // Determine color and icon based on type
        let bgColor = 'bg-blue-500'; // Default: blue
        let iconClass = 'check-circle';
        
        if (type === 'create' || type === 'success') {
            bgColor = 'bg-green-500'; // Green for success/create
            iconClass = 'check-circle';
        } else if (type === 'edit') {
            bgColor = 'bg-blue-500'; // Blue for edit
            iconClass = 'edit';
        } else if (type === 'delete') {
            bgColor = 'bg-red-500'; // Red for delete
            iconClass = 'trash';
        } else if (type === 'error') {
            bgColor = 'bg-red-500'; // Red for error
            iconClass = 'exclamation-circle';
        }
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-slideInRight`;
        toast.innerHTML = `
            <i class="fas fa-${iconClass}"></i>
            <span>${message}</span>
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            const element = document.getElementById(toastId);
            if (element) {
                element.classList.add('animate-slideOutRight');
                setTimeout(() => element.remove(), 300);
            }
        }, 4000);
    }

    /**
     * Create toast container if it doesn't exist
     */
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'fixed top-4 right-4 z-50 space-y-3';
        document.body.appendChild(container);
        return container;
    }

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closePurchaseOrderFilesModal();
            closePurchaseOrderViewFileModal();
            closePurchaseOrderEditFileModal();
            closePurchaseOrderDeleteFileModal();
        }
        // Save on Enter in edit modal
        if (e.key === 'Enter' && document.getElementById('purchaseOrderEditFileModal').style.display === 'flex') {
            submitPurchaseOrderEditFileName();
        }
    });
</script>
