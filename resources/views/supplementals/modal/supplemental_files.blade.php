<!-- Supplemental Files Modal -->
<div id="supplementalFilesModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-2xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900 dark:to-cyan-900 dark:border-gray-600">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-upload text-blue-600 dark:text-blue-400"></i>
                <div class="flex flex-col">
                    <span>Supplemental Files</span>
                    <span id="modalSupplementalNo" class="text-xs font-normal text-gray-600 dark:text-gray-300">Supplemental No: -</span>
                </div>
            </h3>
            <button type="button" onclick="closeSupplementalFilesModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
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
                    id="supplementalFileDropZone"
                    onclick="document.getElementById('supplementalFileInput').click()">
                    <i class="fas fa-cloud-upload-alt text-3xl text-blue-500 mb-2"></i>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Drag and drop files here or click to select</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max 200 MB per file. Multiple files supported.</p>
                </div>
                <input type="file" id="supplementalFileInput" multiple style="display: none;" onchange="handleSupplementalFileSelect(event)">
                <div id="supplementalUploadProgress" class="mt-3" style="display: none;">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div id="supplementalProgressBar" class="bg-blue-500 h-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><span id="supplementalProgressText">0</span>%</p>
                </div>
            </div>
            @endrole

            <!-- Files List Section -->
            <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Uploaded Files</h4>
                <div id="supplementalFilesList" class="space-y-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No files uploaded yet</p>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closeSupplementalFilesModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- File View Modal -->
<div id="supplementalViewFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-7xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out; height: 90vh; max-height: 90vh;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900 dark:to-blue-900 dark:border-gray-600 flex-shrink-0">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-eye text-cyan-600 dark:text-cyan-400"></i>
                <span id="supplementalViewFileName">View File</span>
            </h3>
            <button type="button" onclick="closeSupplementalViewFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="flex-1 overflow-auto">
            <div id="supplementalFileViewContent" class="h-full bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600 flex flex-col">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400 dark:text-gray-500 mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400">Loading file...</p>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="closeSupplementalViewFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- File Edit Modal -->
<div id="supplementalEditFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900 dark:to-yellow-900 dark:border-gray-600">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                Rename File
            </h3>
            <button type="button" onclick="closeSupplementalEditFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-6 py-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">New File Name</label>
            <input type="text" id="supplementalEditFileName" class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Enter new file name">
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="submitSupplementalEditFileName()" class="text-white inline-flex leading-4 tracking-wider bg-amber-600 hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-save mr-2"></i>
                Save
            </button>
            <button type="button" onclick="closeSupplementalEditFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- File Delete Modal -->
<div id="supplementalDeleteFileModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10003] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900 dark:to-pink-900 dark:border-gray-600">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-trash text-red-600 dark:text-red-400"></i>
                Delete File
            </h3>
            <button type="button" onclick="closeSupplementalDeleteFileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 dark:text-gray-300 text-sm">
                Are you sure you want to delete this file? This action cannot be undone.
            </p>
            <p class="text-gray-600 dark:text-gray-400 text-xs mt-2 italic">
                <span id="supplementalDeleteFileName">File</span>
            </p>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-600 flex-shrink-0">
            <button type="button" onclick="submitSupplementalDeleteFile()" class="text-white inline-flex leading-4 tracking-wider bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-trash mr-2"></i>
                Delete
            </button>
            <button type="button" onclick="closeSupplementalDeleteFileModal()" class="text-gray-600 dark:text-gray-300 inline-flex leading-4 tracking-wider hover:text-white border border-gray-600 dark:border-gray-400 hover:bg-gray-600 dark:hover:bg-gray-600 text-xs px-6 py-2 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95 rounded-lg">
                <i class="fas fa-times mr-2"></i>
                Cancel
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

    #supplementalFileDropZone.drag-over {
        background-color: #eff6ff;
        border-color: #0284c7;
    }

    .dark #supplementalFileDropZone.drag-over {
        background-color: rgba(30, 58, 138, 0.3);
        border-color: #0284c7;
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
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let currentSupplementalNo = null;
    let currentSupplementalViewFileId = null;

    function openSupplementalFilesModal(supplementalNo) {
        currentSupplementalNo = supplementalNo;
        const modal = document.getElementById('supplementalFilesModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        
        const supplementalNoElement = document.getElementById('modalSupplementalNo');
        if (supplementalNoElement && supplementalNo) {
            supplementalNoElement.textContent = `Supplemental No: ${supplementalNo}`;
        }
        
        loadSupplementalFiles(supplementalNo);
        setupSupplementalDragAndDrop();
    }

    function closeSupplementalFilesModal() {
        const modal = document.getElementById('supplementalFilesModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        currentSupplementalNo = null;
    }

    function handleSupplementalFileSelect(event) {
        const files = event.target.files;
        if (files.length > 0) {
            uploadSupplementalFiles(files);
        }
    }

    function uploadSupplementalFiles(files) {
        const uploadProgress = document.getElementById('supplementalUploadProgress');
        const progressBar = document.getElementById('supplementalProgressBar');
        const progressText = document.getElementById('supplementalProgressText');
        
        uploadProgress.style.display = 'block';
        let loadedCount = 0;
        const totalFiles = files.length;
        
        Array.from(files).forEach(file => {
            if (file.size > 200 * 1024 * 1024) {
                showSupplementalToast(`File "${file.name}" exceeds 200 MB limit`, 'error');
                loadedCount++;
                if (loadedCount === totalFiles) resetProgress();
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('supplemental_no', currentSupplementalNo);

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
                        showSupplementalToast(`File "${file.name}" uploaded successfully`, 'create');
                        loadSupplementalFiles(currentSupplementalNo);
                    } else {
                        showSupplementalToast(`Error uploading "${file.name}": ${data.message || 'Unknown error'}`, 'error');
                    }
                } catch (e) {
                    showSupplementalToast(`Error uploading "${file.name}": Invalid response`, 'error');
                }
                
                if (loadedCount === totalFiles) resetProgress();
            });

            xhr.addEventListener('error', () => {
                loadedCount++;
                showSupplementalToast(`Error uploading "${file.name}": Network error`, 'error');
                if (loadedCount === totalFiles) resetProgress();
            });

            xhr.addEventListener('abort', () => {
                loadedCount++;
                showSupplementalToast(`Upload cancelled for "${file.name}"`, 'error');
                if (loadedCount === totalFiles) resetProgress();
            });

            xhr.open('POST', '/supplemental-files/upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            xhr.send(formData);
        });

        function resetProgress() {
            progressBar.style.width = '0%';
            progressText.textContent = '0';
            uploadProgress.style.display = 'none';
            document.getElementById('supplementalFileInput').value = '';
        }
    }

    function isSupplementalFileViewable(fileType) {
        const viewableTypes = [
            /^image\/jpeg/, /^image\/jpg/, /^image\/png/, /^image\/gif/, /^image\/webp/,
            /^image\/svg/, /^image\/bmp/, /^image\/tiff/, /^image\/x-icon/, /^image\//,
            /^application\/pdf$/, /^text\//, /application\/json/, /application\/xml/,
            /^video\//, /^audio\//,
        ];
        return viewableTypes.some(pattern => pattern.test(fileType));
    }

    function loadSupplementalFiles(supplementalNo) {
        fetch(`/supplemental-files/get?supplemental_no=${encodeURIComponent(supplementalNo)}`)
            .then(response => response.json())
            .then(data => {
                const filesList = document.getElementById('supplementalFilesList');
                
                if (data.success && data.files.length > 0) {
                    filesList.innerHTML = data.files.map(file => {
                        const isViewable = isSupplementalFileViewable(file.file_type);
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
                                ${isViewable ? `<button onclick="openSupplementalViewFileModal(${file.id}, '${file.original_file_name}')" title="View file preview" class="text-cyan-600 dark:text-cyan-400 hover:text-cyan-800 dark:hover:text-cyan-300 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>` : ''}
                                <a href="/supplemental-files/${file.id}/download" title="Download file" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button onclick="openSupplementalEditFileModal(${file.id}, '${file.original_file_name}')" title="Rename file" class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openSupplementalDeleteFileModal(${file.id}, '${file.original_file_name}')" title="Delete file" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
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
                showSupplementalToast('Error loading files', 'error');
            });
    }

    function openSupplementalEditFileModal(fileId, currentName) {
        window.currentSupplementalEditFileId = fileId;
        document.getElementById('supplementalEditFileName').value = currentName;
        const modal = document.getElementById('supplementalEditFileModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.getElementById('supplementalEditFileName').focus();
    }

    function closeSupplementalEditFileModal() {
        const modal = document.getElementById('supplementalEditFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        window.currentSupplementalEditFileId = null;
    }

    function submitSupplementalEditFileName() {
        const newName = document.getElementById('supplementalEditFileName').value.trim();
        if (!newName) {
            showSupplementalToast('Please enter a file name', 'error');
            return;
        }

        fetch(`/supplemental-files/${window.currentSupplementalEditFileId}`, {
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
                showSupplementalToast('File name updated successfully', 'edit');
                loadSupplementalFiles(currentSupplementalNo);
                closeSupplementalEditFileModal();
            } else {
                showSupplementalToast(`Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            showSupplementalToast(`Error: ${error.message}`, 'error');
        });
    }

    function openSupplementalDeleteFileModal(fileId, fileName) {
        window.currentSupplementalDeleteFileId = fileId;
        document.getElementById('supplementalDeleteFileName').textContent = fileName;
        const modal = document.getElementById('supplementalDeleteFileModal');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeSupplementalDeleteFileModal() {
        const modal = document.getElementById('supplementalDeleteFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        window.currentSupplementalDeleteFileId = null;
    }

    function submitSupplementalDeleteFile() {
        fetch(`/supplemental-files/${window.currentSupplementalDeleteFileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSupplementalToast('File deleted successfully', 'delete');
                loadSupplementalFiles(currentSupplementalNo);
                closeSupplementalDeleteFileModal();
            } else {
                showSupplementalToast(`Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            showSupplementalToast(`Error: ${error.message}`, 'error');
        });
    }

    function openSupplementalViewFileModal(fileId, fileName) {
        window.currentSupplementalViewFileId = fileId;
        const modal = document.getElementById('supplementalViewFileModal');
        const content = document.getElementById('supplementalFileViewContent');
        document.getElementById('supplementalViewFileName').textContent = fileName;
        
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        fetch(`/supplemental-files/${fileId}/preview`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderSupplementalFilePreview(data.file_type, data.file_url, data.file_path);
                } else {
                    content.innerHTML = `<div class="text-center py-8"><i class="fas fa-exclamation-circle text-2xl text-red-500 mb-3"></i><p class="text-red-600 dark:text-red-400">Unable to preview this file type</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching preview:', error);
                content.innerHTML = `<div class="text-center py-8"><i class="fas fa-file text-4xl text-gray-400 dark:text-gray-500 mb-3"></i><p class="text-gray-600 dark:text-gray-400 mb-4">This file cannot be previewed</p><a href="/supplemental-files/${fileId}/download" class="text-blue-600 dark:text-blue-400 hover:underline">Click here to download</a></div>`;
            });
    }

    function closeSupplementalViewFileModal() {
        const modal = document.getElementById('supplementalViewFileModal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    function renderSupplementalFilePreview(fileType, fileUrl, filePath) {
        const content = document.getElementById('supplementalFileViewContent');
        content.innerHTML = '';
        
        if (fileType.startsWith('image/')) {
            const container = document.createElement('div');
            container.className = 'flex-1 overflow-auto flex items-center justify-center';
            const img = document.createElement('img');
            img.src = fileUrl;
            img.alt = 'File preview';
            img.className = 'rounded-lg';
            img.style.cssText = 'max-width: 100%; max-height: 100%; object-fit: contain;';
            img.onerror = function() {
                container.innerHTML = '';
                const errorDiv = document.createElement('div');
                errorDiv.className = 'text-center py-8 flex-1 flex items-center justify-center';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle text-2xl text-red-500 mb-3"></i><p class="text-red-600 dark:text-red-400">Failed to load image.</p>';
                container.appendChild(errorDiv);
            };
            container.appendChild(img);
            content.appendChild(container);
            return;
        }

        if (fileType === 'application/pdf') {
            content.innerHTML = `<iframe src="${fileUrl}" class="w-full flex-1 rounded-lg border border-gray-300 dark:border-gray-600"></iframe>`;
            return;
        }

        if (fileType.startsWith('text/') || fileType.includes('application/json') || fileType.includes('application/xml')) {
            fetch(fileUrl).then(r => r.text()).then(text => {
                const lines = text.split('\n').slice(0, 100).join('\n');
                content.innerHTML = `<pre class="flex-1 bg-gray-900 text-gray-100 p-4 rounded-lg overflow-auto text-xs"><code>${escapeSupplementalHtml(lines)}${text.split('\n').length > 100 ? '\n\n... (file truncated)' : ''}</code></pre>`;
            }).catch(() => {
                content.innerHTML = '<div class="flex-1 flex items-center justify-center text-center py-8"><p class="text-gray-600 dark:text-gray-400 mb-4">File preview temporarily unavailable</p>';
            });
            return;
        }

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
            container.appendChild(video);
            content.appendChild(container);
            return;
        }

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
            container.appendChild(audio);
            content.appendChild(container);
            return;
        }

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

    function escapeSupplementalHtml(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function setupSupplementalDragAndDrop() {
        const dropZone = document.getElementById('supplementalFileDropZone');
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
                uploadSupplementalFiles(files);
            }
        });
    }

    function showSupplementalToast(message, type = 'success') {
        const toastId = 'toast_' + Date.now();
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        let bgColor = 'bg-blue-500';
        let iconClass = 'check-circle';
        
        if (type === 'create' || type === 'success') {
            bgColor = 'bg-green-500';
            iconClass = 'check-circle';
        } else if (type === 'edit') {
            bgColor = 'bg-blue-500';
            iconClass = 'edit';
        } else if (type === 'delete') {
            bgColor = 'bg-red-500';
            iconClass = 'trash';
        } else if (type === 'error') {
            bgColor = 'bg-red-500';
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
        
        setTimeout(() => {
            const element = document.getElementById(toastId);
            if (element) {
                element.classList.add('animate-slideOutRight');
                setTimeout(() => element.remove(), 300);
            }
        }, 4000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'fixed top-4 right-4 z-50 space-y-3';
        document.body.appendChild(container);
        return container;
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSupplementalFilesModal();
            closeSupplementalViewFileModal();
            closeSupplementalEditFileModal();
            closeSupplementalDeleteFileModal();
        }
        if (e.key === 'Enter' && document.getElementById('supplementalEditFileModal').style.display === 'flex') {
            submitSupplementalEditFileName();
        }
    });
</script>
