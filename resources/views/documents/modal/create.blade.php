<!-- Create Document Modal -->
<form id="createDocumentForm" method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
    @csrf
    <div id="createDocumentModal" style="display: none;" tabindex="-1" aria-labelledby="createDocumentLabel" aria-hidden="true" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 hidden animate-scaleInUp" style="animation: scaleInUp 0.3s ease-out;">
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
                <h3 id="createDocumentLabel" class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-cloud-upload-alt text-blue-600 dark:text-blue-400"></i>
                    {{ __('Upload Archive') }}
                </h3>
                <button type="button" onclick="closeCreateDocumentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <!-- Modal body -->
            <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                <div class="grid gap-4">
                    <!-- Title -->
                    <div class="space-y-2">
                        <x-form.label for="title" :value="__('Document Title')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-heading"></i>
                            </x-slot>
                            <x-form.input withicon id="title" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="title" :value="old('title')" autocomplete="off" autofocus placeholder="{{ __('Enter document title') }}" />
                        </x-form.input-with-icon-wrapper>
                        <span id="titleError" class="text-red-500 text-xs"></span>
                    </div>

                    <!-- Category -->
                    <div class="space-y-2">
                        <x-form.label for="category" :value="__('Category')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-folder"></i>
                            </x-slot>
                            <x-form.input withicon id="category" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="category" :value="old('category')" autocomplete="off" placeholder="{{ __('e.g., Budget, Reports, Policies') }}" />
                        </x-form.input-with-icon-wrapper>
                        <span id="categoryError" class="text-red-500 text-xs"></span>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <x-form.label for="tags" :value="__('Tags')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <i class="fas fa-tags"></i>
                            </x-slot>
                            <x-form.input withicon id="tags" class="block w-full text-xs dark:bg-gray-800 dark:text-gray-200" type="text" name="tags" :value="old('tags')" autocomplete="off" placeholder="{{ __('e.g., Important, Urgent, 2026') }}" />
                        </x-form.input-with-icon-wrapper>
                        <span id="tagsError" class="text-red-500 text-xs"></span>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <x-form.label for="description" :value="__('Description')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <textarea id="description" name="description" rows="3" class="block w-full border border-gray-300 rounded-lg px-4 py-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Enter document description (optional)') }}">{{ old('description') }}</textarea>
                        <span id="descriptionError" class="text-red-500 text-xs"></span>
                    </div>

                    <!-- File Upload -->
                    <div class="space-y-2">
                        <x-form.label for="files" :value="__('PDF Files')" class="block text-xs font-medium text-gray-900 dark:text-gray-200" />
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition" id="dropZone" onclick="document.getElementById('files').click()">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 dark:text-gray-500 mb-2"></i>
                            <p class="text-gray-600 dark:text-gray-400 text-xs">
                                <span class="font-semibold">{{ __('Click to upload') }}</span> {{ __('or drag and drop') }}
                            </p>
                            <p class="text-gray-500 dark:text-gray-500 text-xs">{{ __('PDF files up to 100MB each, multiple files allowed') }}</p>
                            <input type="file" name="files[]" id="files" multiple accept=".pdf" style="display:none;" />
                        </div>
                        <div id="fileList" class="mt-3 space-y-2"></div>
                        <span id="fileError" class="text-red-500 text-xs"></span>
                    </div>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="justify-center items-center mt-6 p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
                <button type="button" onclick="if(!isSubmittingDocument) validateCreateDocumentForm(); return false;" class="text-blue-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                    <i class="fas fa-upload text-xl mr-1 -ml-1 w-5 h-5"></i>
                    {{ __('Upload') }}
                </button>
                <button type="button" onclick="closeCreateDocumentModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
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
    let isSubmittingDocument = false;
    let selectedCreateFiles = [];

    function openCreateDocumentModal() {
        closeAllDropdowns();
        isSubmittingDocument = false;
        selectedCreateFiles = [];
        const modal = document.getElementById('createDocumentModal');
        const modalContent = modal.querySelector('div.max-w-4xl');
        modal.style.display = 'flex';
        setTimeout(() => {
            if (modalContent) modalContent.classList.remove('hidden');
        }, 0);
        document.getElementById('title').focus();
    }

    function closeCreateDocumentModal() {
        const modal = document.getElementById('createDocumentModal');
        const modalContent = modal.querySelector('div.max-w-4xl');
        modalContent.classList.add('hidden');
        setTimeout(() => {
            modal.style.display = 'none';
            document.getElementById('createDocumentForm').reset();
            document.getElementById('fileList').innerHTML = '';
            selectedCreateFiles = [];
            clearErrors();
        }, 300);
    }

    function validateCreateDocumentForm() {
        clearErrors();
        let isValid = true;
        const title = document.getElementById('title').value.trim();

        if (!title) {
            document.getElementById('titleError').textContent = 'Title is required';
            isValid = false;
        }

        if (selectedCreateFiles.length === 0) {
            document.getElementById('fileError').textContent = 'At least one PDF file is required';
            isValid = false;
        }

        if (isValid) {
            isSubmittingDocument = true;
            
            // Update button to show loading state
            const uploadBtn = document.querySelector('button[onclick*="validateCreateDocumentForm"]');
            const originalBtnHTML = uploadBtn.innerHTML;
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-xl mr-1 -ml-1 w-5 h-5"></i>{{ __("Uploading...") }}';
            
            // Create FormData and add all fields manually
            const formData = new FormData();
            
            // Add form fields
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('title', document.getElementById('title').value);
            formData.append('category', document.getElementById('category').value);
            formData.append('tags', document.getElementById('tags').value);
            formData.append('description', document.getElementById('description').value);
            
            // Add selected files
            selectedCreateFiles.forEach((file) => {
                formData.append('files[]', file);
            });

            // Submit via fetch to handle FormData properly
            fetch(document.getElementById('createDocumentForm').action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(async response => {
                const data = await response.json();
                
                // Check for validation errors (422)
                if (response.status === 422) {
                    uploadBtn.innerHTML = originalBtnHTML;
                    uploadBtn.disabled = false;
                    
                    // Extract error messages
                    let errorMsg = 'Validation error: ';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    
                    console.error('Validation errors:', data);
                    alert(errorMsg);
                    isSubmittingDocument = false;
                    return;
                }
                
                if (data.status === 'success') {
                    // Store message in sessionStorage to display after redirect
                    sessionStorage.setItem('successMessage', data.message);
                    
                    // Show success message briefly before redirecting
                    uploadBtn.innerHTML = '<i class="fas fa-check text-xl mr-1 -ml-1 w-5 h-5"></i>{{ __("Upload Successful!") }}';
                    uploadBtn.classList.remove('text-blue-600', 'border-blue-600', 'hover:bg-blue-600', 'dark:border-blue-500', 'dark:text-blue-500', 'dark:hover:bg-blue-600');
                    uploadBtn.classList.add('text-green-600', 'border-green-600', 'hover:bg-green-600', 'dark:border-green-500', 'dark:text-green-500', 'dark:hover:bg-green-600');
                    
                    // Redirect after 1 second
                    setTimeout(() => {
                        window.location.href = data.redirect || '/documents';
                    }, 1000);
                } else if (data.error) {
                    uploadBtn.innerHTML = originalBtnHTML;
                    uploadBtn.disabled = false;
                    alert(data.error);
                    isSubmittingDocument = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                uploadBtn.innerHTML = originalBtnHTML;
                uploadBtn.disabled = false;
                alert('An error occurred while uploading. Please try again.');
                isSubmittingDocument = false;
            });
        }
    }

    function clearErrors() {
        document.querySelectorAll('[id*="Error"]').forEach(el => el.textContent = '');
    }

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFileSelection(files);
    }

    // File upload handlers
    const dropZone = document.getElementById('dropZone');
    const filesInput = document.getElementById('files');
    const fileList = document.getElementById('fileList');

    if (dropZone && filesInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-gray-700');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-gray-700');
            });
        });

        dropZone.addEventListener('drop', handleDrop);

        filesInput.addEventListener('change', (e) => {
            handleFileSelection(e.target.files);
        });
    }

    function handleFileSelection(files) {
        selectedCreateFiles = [];
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
            
            selectedCreateFiles.push(file);
        }

        if (errors.length > 0) {
            document.getElementById('fileError').textContent = errors.join('; ');
        } else {
            document.getElementById('fileError').textContent = '';
        }

        displayCreateFileList();
    }

    function displayCreateFileList() {
        fileList.innerHTML = '';
        
        if (selectedCreateFiles.length === 0) {
            return;
        }

        const fileListHtml = selectedCreateFiles.map((file, index) => {
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
                    <button type="button" onclick="removeCreateFile(${index})" class="px-2 py-1 text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }).join('');

        fileList.innerHTML = fileListHtml;
    }

    function removeCreateFile(index) {
        selectedCreateFiles.splice(index, 1);
        // Update the file input DataTransfer object
        const dataTransfer = new DataTransfer();
        selectedCreateFiles.forEach(file => dataTransfer.items.add(file));
        filesInput.files = dataTransfer.files;
        displayCreateFileList();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
