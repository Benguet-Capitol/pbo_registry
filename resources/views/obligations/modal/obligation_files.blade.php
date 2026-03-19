<!-- Obligation Files Modal -->
<div id="obligationFilesModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-2xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900 dark:to-cyan-900 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-upload text-blue-600 dark:text-blue-400"></i>
                <div class="flex flex-col">
                    <span>Obligation Files</span>
                    <span id="modalObrNo" class="text-xs font-normal text-gray-600 dark:text-gray-300">OBR No: -</span>
                </div>
            </h3>
            <button type="button" onclick="closeObligationFilesModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
            <!-- File Upload Section -->
             @role ('Administrator|Developer|Obligation')
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Upload Files</h4>
                <div class="border-2 border-dashed border-blue-300 dark:border-blue-600 rounded-lg p-6 text-center cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                    id="fileDropZone"
                    onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-cloud-upload-alt text-3xl text-blue-500 mb-2"></i>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Drag and drop files here or click to select</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max 200 MB per file. Multiple files supported.</p>
                </div>
                <input type="file" id="fileInput" multiple style="display: none;" onchange="handleFileSelect(event)">
                <div id="uploadProgress" class="mt-3" style="display: none;">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div id="progressBar" class="bg-blue-500 h-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><span id="progressText">0</span>%</p>
                </div>
            </div>
            @endrole

            <!-- Files List Section -->
            <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Uploaded Files</h4>
                <div id="filesList" class="space-y-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No files uploaded yet</p>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closeObligationFilesModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- File View Modal -->
<div id="viewFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-7xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out; height: 90vh; max-height: 90vh;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900 dark:to-blue-900 dark:border-gray-600 flex-shrink-0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-eye text-cyan-600 dark:text-cyan-400"></i>
                <span id="viewFileName">View File</span>
            </h3>
            <button type="button" onclick="closeViewFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="flex-1 overflow-auto">
            <div id="fileViewContent" class="h-full bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600 flex flex-col">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400 dark:text-gray-500 mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400">Loading file...</p>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closeViewFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- File Edit Modal -->
<div id="editFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900 dark:to-yellow-900 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                Rename File
            </h3>
            <button type="button" onclick="closeEditFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-6 py-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">New File Name</label>
            <input type="text" id="editFileName" class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Enter new file name">
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closeEditFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button type="button" onclick="submitEditFileName()" class="text-white inline-flex leading-4 tracking-wider bg-amber-600 hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-save mr-2"></i>
                Save
            </button>
        </div>
    </div>
</div>

<!-- File Delete Modal -->
<div id="deleteFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900 dark:to-pink-900 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-trash text-red-600 dark:text-red-400"></i>
                Delete File
            </h3>
            <button type="button" onclick="closeDeleteFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 dark:text-gray-300 text-sm">
                Are you sure you want to delete this file? This action cannot be undone.
            </p>
            <p class="text-gray-600 dark:text-gray-400 text-xs mt-2 italic">
                <span id="deleteFileName">File</span>
            </p>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closeDeleteFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button type="button" onclick="submitDeleteFile()" class="text-white inline-flex leading-4 tracking-wider bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-xs px-5 py-3 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
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

    #fileDropZone.drag-over {
        background-color: #eff6ff;
        border-color: #0284c7;
    }

    .dark #fileDropZone.drag-over {
        background-color: rgba(30, 58, 138, 0.3);
        border-color: #0284c7;
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

    let currentObligationId = null;
    let currentViewFileId = null;

    /**
     * Open the obligation files modal
     */
    function openObligationFilesModal(obligationId, obrNo) {
        currentObligationId = obligationId;
        const modal = document.getElementById('obligationFilesModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        
        // Update modal header with OBR No
        const obrNoElement = document.getElementById('modalObrNo');
        if (obrNoElement && obrNo) {
            obrNoElement.textContent = `OBR No: ${obrNo}`;
        }
        
        // Load files for this obligation
        loadObligationFiles(obligationId);
        
        // Setup drag and drop
        setupDragAndDrop();
    }

    /**
     * Close the obligation files modal
     */
    function closeObligationFilesModal() {
        const modal = document.getElementById('obligationFilesModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        currentObligationId = null;
    }

    /**
     * Handle file selection
     */
    function handleFileSelect(event) {
        const files = event.target.files;
        if (files.length > 0) {
            uploadFiles(files);
        }
    }

    /**
     * Upload files to the server
     */
    function uploadFiles(files) {
        const uploadProgress = document.getElementById('uploadProgress');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        uploadProgress.style.display = 'block';
        let loadedCount = 0;
        
        Array.from(files).forEach(file => {
            // Validate file size (200 MB = 209715200 bytes)
            if (file.size > 200 * 1024 * 1024) {
                showToast(`File "${file.name}" exceeds 200 MB limit`, 'error');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            fetch(`/obligations/${currentObligationId}/files`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Upload response:', data);
                loadedCount++;
                if (data.success) {
                    console.log('Success! Showing green toast');
                    showToast(`File "${file.name}" uploaded successfully`, 'create');
                    loadObligationFiles(currentObligationId);
                } else {
                    console.log('Failed! Showing red toast:', data.message);
                    showToast(`Error uploading "${file.name}": ${data.message}`, 'error');
                }
                
                if (loadedCount === files.length) {
                    progressBar.style.width = '0%';
                    progressText.textContent = '0';
                    uploadProgress.style.display = 'none';
                    document.getElementById('fileInput').value = '';
                }
            })
            .catch(error => {
                loadedCount++;
                showToast(`Error uploading "${file.name}": ${error.message}`, 'error');
                if (loadedCount === files.length) {
                    progressBar.style.width = '0%';
                    progressText.textContent = '0';
                    uploadProgress.style.display = 'none';
                    document.getElementById('fileInput').value = '';
                }
            });
        });
    }

    /**
     * Check if a file type is viewable in browser
     */
    function isFileViewable(fileType) {
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
     * Load files for an obligation
     */
    function loadObligationFiles(obligationId) {
        fetch(`/obligations/${obligationId}/files`)
            .then(response => response.json())
            .then(data => {
                const filesList = document.getElementById('filesList');
                
                if (data.success && data.files.length > 0) {
                    filesList.innerHTML = data.files.map(file => {
                        const isViewable = isFileViewable(file.file_type);
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
                                ${isViewable ? `<button onclick="openViewFileModal(${file.id}, '${file.original_file_name}')" title="View file preview" aria-label="View file preview" class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-800 dark:hover:text-cyan-300 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>` : ''}
                                <a href="/obligation-files/${file.id}/download" title="Download file" aria-label="Download file" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button onclick="openEditFileModal(${file.id}, '${file.original_file_name}')" title="Rename file" aria-label="Rename file" class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openDeleteFileModal(${file.id}, '${file.original_file_name}')" title="Delete file" aria-label="Delete file" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
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
                showToast('Error loading files', 'error');
            });
    }

    /**
     * Edit file name
     */
    function openEditFileModal(fileId, currentName) {
        window.currentEditFileId = fileId;
        document.getElementById('editFileName').value = currentName;
        const modal = document.getElementById('editFileModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.getElementById('editFileName').focus();
    }

    function closeEditFileModal() {
        const modal = document.getElementById('editFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        window.currentEditFileId = null;
    }

    function submitEditFileName() {
        const newName = document.getElementById('editFileName').value.trim();
        if (!newName) {
            showToast('Please enter a file name', 'error');
            return;
        }

        fetch(`/obligation-files/${window.currentEditFileId}`, {
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
                showToast('File name updated successfully', 'edit');
                loadObligationFiles(currentObligationId);
                closeEditFileModal();
            } else {
                showToast(`Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            showToast(`Error: ${error.message}`, 'error');
        });
    }

    /**
     * Delete file
     */
    function openDeleteFileModal(fileId, fileName) {
        window.currentDeleteFileId = fileId;
        document.getElementById('deleteFileName').textContent = fileName;
        const modal = document.getElementById('deleteFileModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeDeleteFileModal() {
        const modal = document.getElementById('deleteFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        window.currentDeleteFileId = null;
    }

    function submitDeleteFile() {
        fetch(`/obligation-files/${window.currentDeleteFileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('File deleted successfully', 'delete');
                loadObligationFiles(currentObligationId);
                closeDeleteFileModal();
            } else {
                showToast(`Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            showToast(`Error: ${error.message}`, 'error');
        });
    }

    /**
     * View file
     */
    function openViewFileModal(fileId, fileName) {
        window.currentViewFileId = fileId;
        const modal = document.getElementById('viewFileModal');
        const content = document.getElementById('fileViewContent');
        document.getElementById('viewFileName').textContent = fileName;
        
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        // Load file content
        fetch(`/obligation-files/${fileId}/preview`)
            .then(response => response.json())
            .then(data => {
                console.log('Preview data received:', data);
                if (data.success) {
                    renderFilePreview(data.file_type, data.file_url, data.file_path);
                } else {
                    content.innerHTML = `<div class="text-center py-8"><i class="fas fa-exclamation-circle text-2xl text-red-500 mb-3"></i><p class="text-red-600 dark:text-red-400">Unable to preview this file type</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching preview:', error);
                // Fallback: provide download link
                content.innerHTML = `<div class="text-center py-8"><i class="fas fa-file text-4xl text-gray-400 dark:text-gray-500 mb-3"></i><p class="text-gray-600 dark:text-gray-400 mb-4">This file cannot be previewed</p><a href="/obligation-files/${fileId}/download" class="text-blue-600 dark:text-blue-400 hover:underline">Click here to download</a></div>`;
            });
    }

    function closeViewFileModal() {
        const modal = document.getElementById('viewFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    function renderFilePreview(fileType, fileUrl, filePath) {
        const content = document.getElementById('fileViewContent');
        const fileId = window.currentViewFileId;
        
        console.log('Rendering preview for:', { fileType, fileUrl, fileId });
        
        // Image types - PNG, JPG, GIF, WebP, SVG, BMP, TIFF, etc.
        if (fileType.startsWith('image/')) {
            content.innerHTML = `<div class="flex-1 overflow-auto flex items-center justify-center"><img src="${fileUrl}" alt="File preview" class="rounded-lg" style="max-width: 100%; max-height: 100%; object-fit: contain;" onerror="console.error('Image failed to load from:', '${fileUrl}'); this.closest('div').innerHTML='<div class=\'text-center py-8\'><i class=\'fas fa-exclamation-circle text-2xl text-red-500 mb-3\'></i><p class=\'text-red-600 dark:text-red-400\'>Failed to load image. <a href=\'${fileUrl}\' class=\'text-blue-600 hover:underline\'>Try downloading instead</a></p></div>';"></div>`;
            return;
        }

        // PDF - Check if it's image-based or regular PDF
        if (fileType === 'application/pdf') {
            renderPdfPreview(fileUrl);
            return;
        }

        // Text files
        if (fileType.startsWith('text/') || fileType.includes('application/json') || fileType.includes('application/xml') || fileType.includes('application/vnd.openxmlformats')) {
            // For text files, create a fetch request to get the content
            fetch(`${fileUrl}?preview=true`)
                .then(response => response.text())
                .then(text => {
                    const lines = text.split('\n').slice(0, 100).join('\n');
                    content.innerHTML = `<pre class="flex-1 bg-gray-900 text-gray-100 p-4 rounded-lg overflow-auto text-xs line-numbers"><code>${escapeHtml(lines)}${text.split('\n').length > 100 ? '\n\n... (file truncated)' : ''}</code></pre>`;
                })
                .catch(() => {
                    content.innerHTML = `<div class="flex-1 flex items-center justify-center text-center py-8"><div><p class="text-gray-600 dark:text-gray-400 mb-4">File preview temporarily unavailable</p><a href="${fileUrl}" download class="text-blue-600 dark:text-blue-400 hover:underline">Download file</a></div></div>`;
                });
            return;
        }

        // Video types
        if (fileType.startsWith('video/')) {
            content.innerHTML = `<div class="flex-1 flex items-center justify-center"><video controls class="w-full rounded-lg bg-black" style="max-height: 100%; object-fit: contain;"><source src="${fileUrl}" type="${fileType}">Your browser does not support the video tag.</video></div>`;
            return;
        }

        // Audio types
        if (fileType.startsWith('audio/')) {
            content.innerHTML = `<div class="flex-1 flex items-center justify-center"><audio controls class="w-full rounded-lg"><source src="${fileUrl}" type="${fileType}">Your browser does not support the audio element.</audio></div>`;
            return;
        }

        // Default
        content.innerHTML = `<div class="flex-1 flex items-center justify-center text-center"><div><i class="fas fa-file text-4xl text-gray-400 dark:text-gray-500 mb-3"></i><p class="text-gray-600 dark:text-gray-400 mb-4">This file type cannot be previewed</p><a href="${fileUrl}" download class="text-blue-600 dark:text-blue-400 hover:underline">Click here to download</a></div></div>`;
    }

    function escapeHtml(text) {
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
     * Render PDF preview - detects if image-based PDF or regular PDF
     */
    async function renderPdfPreview(fileUrl) {
        const content = document.getElementById('fileViewContent');
        
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
                await renderPdfAsImage(fileUrl, page);
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
    async function renderPdfAsImage(fileUrl, firstPage) {
        const content = document.getElementById('fileViewContent');
        
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
     * Setup drag and drop
     */
    function setupDragAndDrop() {
        const dropZone = document.getElementById('fileDropZone');
        
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
                uploadFiles(files);
            }
        });
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        const toastId = 'toast_' + Date.now();
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.id = toastId;
        
        // Determine color and icon based on type
        let backgroundColor = '#3b82f6'; // Default: blue
        let iconClass = 'check-circle';
        
        if (type === 'create' || type === 'success') {
            backgroundColor = '#10b981'; // Green for success/create
            iconClass = 'check-circle';
        } else if (type === 'edit') {
            backgroundColor = '#3b82f6'; // Blue for edit
            iconClass = 'edit';
        } else if (type === 'delete') {
            backgroundColor = '#ef4444'; // Red for delete
            iconClass = 'trash';
        } else if (type === 'error') {
            backgroundColor = '#ef4444'; // Red for error
            iconClass = 'exclamation-circle';
        }
        
        // Create inner div with inline styles
        const innerDiv = document.createElement('div');
        innerDiv.style.backgroundColor = backgroundColor + ' !important';
        innerDiv.style.color = 'white !important';
        innerDiv.style.padding = '12px 24px !important';
        innerDiv.style.borderRadius = '8px !important';
        innerDiv.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.1) !important';
        innerDiv.style.display = 'flex !important';
        innerDiv.style.alignItems = 'center !important';
        innerDiv.style.gap = '12px !important';
        innerDiv.style.animation = 'slideInRight 0.3s ease-out !important';
        
        innerDiv.innerHTML = `
            <i class="fas fa-${iconClass}"></i>
            <span>${message}</span>
        `;
        
        toast.appendChild(innerDiv);
        toastContainer.appendChild(toast);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            const element = document.getElementById(toastId);
            if (element) {
                const toastDiv = element.querySelector('div');
                if (toastDiv) {
                    toastDiv.style.animation = 'slideOutRight 0.3s ease-in !important';
                }
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
            closeObligationFilesModal();
            closeViewFileModal();
            closeEditFileModal();
            closeDeleteFileModal();
        }
        // Save on Enter in edit modal
        if (e.key === 'Enter' && document.getElementById('editFileModal').style.display === 'flex') {
            submitEditFileName();
        }
    });
</script>
