<!-- Edit Document Modal -->
<form id="editDocumentForm" method="POST" action="" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div id="editDocumentModal" style="display: none;" tabindex="-1" aria-labelledby="editDocumentLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="editDocumentLabel" class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-edit text-amber-600 dark:text-amber-400"></i>
                    {{ __('Edit Archive') }}
                </h3>
                <button type="button" onclick="closeEditDocumentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                <div class="grid gap-4">
                    <!-- Document ID (hidden) -->
                    <input type="hidden" id="document_id" name="document_id">

                    <!-- Title -->
                    <div class="space-y-2">
                        <x-form.label for="edit_title" :value="__('Document Title')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-heading"></i>
                            </x-slot>
                            <x-form.input withicon id="edit_title" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="title" placeholder="{{ __('Enter document title') }}" />
                        </x-form.input-with-icon-wrapper>
                        <span id="edit_titleError" class="text-red-500 text-xs"></span>
                    </div>

                    <!-- Category -->
                    <div class="space-y-2">
                        <x-form.label for="edit_category" :value="__('Category')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-folder"></i>
                            </x-slot>
                            <x-form.input withicon id="edit_category" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="category" placeholder="{{ __('e.g., Budget, Reports, Policies') }}" />
                        </x-form.input-with-icon-wrapper>
                        <span id="edit_categoryError" class="text-red-500 text-xs"></span>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <x-form.label for="edit_tags" :value="__('Tags')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-tags"></i>
                            </x-slot>
                            <x-form.input withicon id="edit_tags" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="tags" placeholder="{{ __('e.g., Important, Urgent, 2026') }}" />
                        </x-form.input-with-icon-wrapper>
                        <span id="edit_tagsError" class="text-red-500 text-xs"></span>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <x-form.label for="edit_description" :value="__('Description')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <textarea id="edit_description" name="description" rows="3" class="block w-full border border-gray-300 rounded-lg px-4 py-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Enter document description (optional)') }}"></textarea>
                        <span id="edit_descriptionError" class="text-red-500 text-xs"></span>
                    </div>

                    <!-- File Upload (optional) -->
                    <div class="space-y-3">
                        <div class="space-y-2">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-200">{{ __('PDF File') }}</h4>
                            
                            <!-- Current File Display -->
                            <div class="p-3 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ __('Current file:') }}</span>
                                    <button type="button" id="viewCurrentFileBtn" onclick="viewCurrentFile()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        {{ __('View File') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Zone -->
                        <div class="space-y-2">
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition" id="editDropZone" onclick="document.getElementById('edit_files').click()">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    <span class="font-semibold">{{ __('Click to upload') }}</span> {{ __('or drag and drop') }}
                                </p>
                                <p class="text-gray-500 dark:text-gray-500 text-xs mt-1">{{ __('PDF files up to 100MB each, multiple files allowed') }}</p>
                                <input type="file" name="files[]" id="edit_files" multiple accept=".pdf" style="display:none;" />
                            </div>
                            <div id="edit_fileList" class="mt-3 space-y-2"></div>
                            <span id="edit_fileError" class="text-red-500 text-sm"></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="if(!isSubmittingEditDocument) validateEditDocumentForm(); return false;" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-save text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Save Changes') }}
                </button>
                <button type="button" onclick="closeEditDocumentModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</form>

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

    .animate-scaleInUp {
        animation: scaleInUp 0.3s ease-out;
    }
</style>

<script>
    let isSubmittingEditDocument = false;
    let currentEditDocId = null;
    let currentEditDocFiles = [];
    let currentEditDocFilename = null;

    function openEditDocumentModal(docData) {
        closeAllDropdowns();
        isSubmittingEditDocument = false;
        selectedFiles = [];
        currentEditDocId = docData.id;
        currentEditDocFiles = docData.files || [];
        
        // Populate form fields
        document.getElementById('document_id').value = docData.id;
        document.getElementById('edit_title').value = docData.title;
        currentEditDocFilename = docData.filename;
        document.getElementById('edit_category').value = docData.category || '';
        document.getElementById('edit_tags').value = docData.tags || '';
        document.getElementById('edit_description').value = docData.description || '';
        document.getElementById('edit_files').value = ''; // Clear file input
        editFileList.innerHTML = ''; // Clear file list

        // Update form action
        document.getElementById('editDocumentForm').action = `/documents/${docData.id}`;

        const modal = document.getElementById('editDocumentModal');
        const modalContent = modal.querySelector('div.max-w-4xl');
        modal.style.display = 'flex';
        setTimeout(() => {
            modalContent.classList.remove('hidden');
        }, 0);
        document.getElementById('edit_title').focus();
    }

    function closeEditDocumentModal() {
        const modal = document.getElementById('editDocumentModal');
        const modalContent = modal.querySelector('div.max-w-4xl');
        if (modalContent) {
            modalContent.classList.add('hidden');
            setTimeout(() => {
                modal.style.display = 'none';
                document.getElementById('editDocumentForm').reset();
                editFileList.innerHTML = '';
                selectedFiles = [];
                clearEditErrors();
                currentEditDocId = null;
                currentEditDocFiles = [];
                currentEditDocFilename = null;
            }, 300);
        } else {
            modal.style.display = 'none';
            document.getElementById('editDocumentForm').reset();
            editFileList.innerHTML = '';
            selectedFiles = [];
            clearEditErrors();
            currentEditDocId = null;
            currentEditDocFiles = [];
            currentEditDocFilename = null;
        }
    }

    function viewCurrentFile() {
        if (currentEditDocId) {
            // Build files array if not already set
            let files = currentEditDocFiles && currentEditDocFiles.length > 0 ? currentEditDocFiles : [
                {
                    id: 'main',
                    filename: currentEditDocFilename,
                    file_path: `/documents/${currentEditDocId}`,
                    is_main: true,
                }
            ];
            
            // Don't close the edit modal - keep it open
            openViewDocumentModal(
                currentEditDocId, 
                document.getElementById('edit_title').value, 
                currentEditDocFilename,
                files
            );
        }
    }

    function editDocumentFromSearch(docData) {
        openEditDocumentModal(docData);
    }

    function validateEditDocumentForm() {
        clearEditErrors();
        let isValid = true;
        const title = document.getElementById('edit_title').value.trim();

        if (!title) {
            document.getElementById('edit_titleError').textContent = 'Title is required';
            isValid = false;
        }

        if (isValid) {
            isSubmittingEditDocument = true;
            
            // Update button to show loading state
            const saveBtn = document.querySelector('button[onclick*="validateEditDocumentForm"]');
            const originalBtnHTML = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-xl mr-1 -ml-1 w-5 h-5"></i>{{ __("Saving...") }}';
            
            // Create FormData to include multiple files
            const form = document.getElementById('editDocumentForm');
            const formData = new FormData(form);
            
            // Clear existing files and add selected files
            formData.delete('files[]');
            selectedFiles.forEach((file) => {
                formData.append('files[]', file);
            });

            // Submit via fetch to handle FormData properly
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Store message in sessionStorage to display after redirect
                    sessionStorage.setItem('successMessage', data.message);
                    
                    // Show success message briefly before redirecting
                    saveBtn.innerHTML = '<i class="fas fa-check text-xl mr-1 -ml-1 w-5 h-5"></i>{{ __("Update Successful!") }}';
                    saveBtn.classList.remove('text-blue-600', 'border-blue-600', 'hover:bg-blue-600', 'dark:border-blue-500', 'dark:text-blue-500', 'dark:hover:bg-blue-600');
                    saveBtn.classList.add('text-green-600', 'border-green-600', 'hover:bg-green-600', 'dark:border-green-500', 'dark:text-green-500', 'dark:hover:bg-green-600');
                    
                    // Redirect after 1 second
                    setTimeout(() => {
                        window.location.href = data.redirect || '/documents';
                    }, 1000);
                } else if (data.error) {
                    saveBtn.innerHTML = originalBtnHTML;
                    saveBtn.disabled = false;
                    alert(data.error);
                    isSubmittingEditDocument = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                saveBtn.innerHTML = originalBtnHTML;
                saveBtn.disabled = false;
                alert('An error occurred while updating. Please try again.');
                isSubmittingEditDocument = false;
            });
        }
    }

    function clearEditErrors() {
        document.querySelectorAll('[id*="edit_"][id*="Error"]').forEach(el => el.textContent = '');
    }

    // File upload handlers for edit modal
    const editDropZone = document.getElementById('editDropZone');
    const editFilesInput = document.getElementById('edit_files');
    const editFileList = document.getElementById('edit_fileList');
    let selectedFiles = [];

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        editDropZone.addEventListener(eventName, preventEditDefaults, false);
    });

    function preventEditDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        editDropZone.addEventListener(eventName, () => {
            editDropZone.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-gray-700');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        editDropZone.addEventListener(eventName, () => {
            editDropZone.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-gray-700');
        });
    });

    editDropZone.addEventListener('drop', handleEditDrop);

    function handleEditDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleEditFileSelection(files);
    }

    editFilesInput.addEventListener('change', (e) => {
        handleEditFileSelection(e.target.files);
    });

    function handleEditFileSelection(files) {
        selectedFiles = [];
        let errors = [];
        
        for (let file of files) {
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            if (file.type !== 'application/pdf') {
                errors.push(`${file.name} is not a PDF file`);
                continue;
            }
            if (sizeMB > 100) {
                errors.push(`${file.name} exceeds 100MB limit`);
                continue;
            }
            
            selectedFiles.push(file);
        }

        if (errors.length > 0) {
            document.getElementById('edit_fileError').textContent = errors.join('; ');
        } else {
            document.getElementById('edit_fileError').textContent = '';
        }

        displayEditFileList();
    }

    function displayEditFileList() {
        editFileList.innerHTML = '';
        
        if (selectedFiles.length === 0) {
            return;
        }

        const fileListHtml = selectedFiles.map((file, index) => {
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            return `
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center gap-2 flex-1">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-200 truncate">${escapeHtml(file.name)}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">${sizeMB}MB</p>
                        </div>
                    </div>
                    <button type="button" onclick="removeEditFile(${index})" class="px-2 py-1 text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }).join('');

        editFileList.innerHTML = fileListHtml;
    }

    function removeEditFile(index) {
        selectedFiles.splice(index, 1);
        // Update the file input DataTransfer object
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        editFilesInput.files = dataTransfer.files;
        displayEditFileList();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
