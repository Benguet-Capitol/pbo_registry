<x-app-layout>
    @if (session('status') || session('error'))
        @php
            $message = session('status') ?? session('error');
            $isError = session()->has('error');

            // Handle both string and array messages
            if (is_array($message)) {
                $messageText = $message['message'] ?? '';
                $messageType = $message['type'] ?? 'success';
            } else {
                $messageText = $message;
                $messageType = 'success';
            }

            if ($isError) {
                $alertType = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200';
            } else {
                // Default success color
                $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200';
                if (str_contains($messageType, 'update') || str_contains($messageText, 'updated successfully')) {
                    $alertType = 'bg-blue-100 border-blue-400 text-blue-700 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-200';
                } elseif (str_contains($messageType, 'delete') || str_contains($messageText, 'deleted successfully')) {
                    $alertType = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200';
                } elseif (str_contains($messageType, 'success') || str_contains($messageText, 'created successfully') || str_contains($messageText, 'uploaded successfully')) {
                    $alertType = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200';
                }
            }
        @endphp

        <div class="border-l-4 p-4 mb-4 {{ $alertType }}" role="alert">
            <div class="flex justify-between items-center">
                <div>
                    <p>{!! $messageText !!}</p>
                </div>
                <button type="button"
                    class="text-2xl font-semibold leading-none dark:text-gray-200"
                    onclick="this.parentElement.parentElement.remove();">
                    &times;
                </button>
            </div>
        </div>
    @endif

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-xl leading-tight dark:text-gray-200">
                {{ __('Archives') }}
            </h3>
        </div>
    </x-slot>

    <!-- Page Content Wrapper with Transition -->
    <div class="page-transition">

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800">
        <div class="p-6 bg-white rounded-md border-b border-gray-200 relative overflow-x-auto shadow-md sm:rounded-lg dark:bg-gray-800 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-4 gap-4">
                <div class="self-start">
                    <button onclick="openCreateDocumentModal()" class="text-blue-600 inline-flex items-center leading-4 tracking-wider hover:text-white border border-blue-600 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 dark:focus:ring-blue-900">
                        <i class="fas fa-cloud-upload-alt text-xl mr-3 -ml-1 w-5 h-5"></i>
                        {{ __('Upload Archive') }}
                    </button>
                </div>
                <!-- Right: Total Records, Search Input, and Per Page -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                    <!-- Total Records -->
                    <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 shrink-0">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Total Records:</span>
                        <span id="totalRecordsCount" class="text-xs font-bold text-blue-900 dark:text-blue-200">{{ $documents->total() }}</span>
                    </div>
                    <!-- Search Input -->
                    <div class="flex items-center gap-2 w-full sm:w-auto sm:min-w-96">
                        <i class="fas fa-search text-gray-400"></i>
                        <input type="text" id="searchInput" autocomplete="off" placeholder="Search archives..." class="border border-gray-300 rounded-lg w-full min-w-0 px-4 py-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                    </div>
                    <!-- Per Page Dropdown -->
                    <div class="flex items-center gap-2 min-w-fit">
                        <label for="perPage" class="text-xs font-semibold text-gray-700 dark:text-gray-300">Per Page:</label>
                        <form method="GET" action="{{ route('documents.index') }}" class="inline" id="perPageForm">
                            @if(request('q'))
                                <input type="hidden" name="q" value="{{ request('q') }}">
                            @endif
                            <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                            <input type="hidden" name="sort_order" value="{{ $sortOrder }}">
                            <select name="per_page" id="perPage" onchange="document.getElementById('perPageForm').submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>All</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            <table id="documentsTable" class="text-center w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-center border-t-2 border-b-2 border-gray-400 text-xs text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        @php
                            $sortableColumns = [
                                'title' => 'Title',
                                'category' => 'Category',
                                'created_at' => 'Date',
                            ];
                        @endphp

                        @foreach($sortableColumns as $column => $label)
                            @php
                                $isCurrent = $sortBy === $column;
                                $nextOrder = $isCurrent && $sortOrder === 'asc' ? 'desc' : 'asc';
                                $icon = $isCurrent ? ($sortOrder === 'asc' ? '▲' : '▼') : '';
                                $sortQuery = array_merge(request()->except('page'), ['sort_by' => $column, 'sort_order' => $nextOrder]);
                            @endphp
                            <th class="px-6 py-3 border-gray-300 leading-4 text-gray-600 tracking-wider dark:text-gray-300 ">
                                <a href="?{{ http_build_query($sortQuery) }}" class="text-center gap-1 hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ __($label) }}
                                    <span class="text-[10px]">{!! $icon !!}</span>
                                </a>
                            </th>
                        @endforeach

                        <th class="px-6 py-3 border-gray-300 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            {{ __('Tags') }}
                        </th>
                        <th class="px-6 py-3 border-gray-300 leading-4 text-gray-600 tracking-wider dark:text-gray-300">
                            {{ __('File Details') }}
                        </th>
                        <th class="px-6 py-3 border-gray-300 leading-4 text-gray-600 tracking-wider dark:text-gray-300">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-3 border-b border-gray-300 text-left text-gray-600 dark:text-gray-300">{{ $document->title }}</td>
                        <td class="px-6 py-3 border-b border-gray-300 text-left text-gray-600 dark:text-gray-300">{{ $document->category ?? '-' }}</td>
                        <td class="px-6 py-3 border-b border-gray-300 text-left text-gray-600 dark:text-gray-300">{{ $document->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3 border-b border-gray-300 text-left text-gray-600 dark:text-gray-300">
                            @if($document->tags)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ $document->tags }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                            @if($document->files->count() > 0)
                                @php
                                    $totalFileSize = 0;
                                    $fileCount = $document->files->count();
                                    foreach ($document->files as $file) {
                                        $filePath = 'documents/' . basename($file->file_path);
                                        try {
                                            $totalFileSize += \Storage::disk('local')->size($filePath);
                                        } catch (\Exception $e) {
                                            // Skip files that can't be accessed
                                        }
                                    }
                                    $totalFileSizeFormatted = number_format($totalFileSize / 1024 / 1024, 2) . ' MB';
                                @endphp
                                <div class="text-xs">
                                    <div class="font-semibold text-gray-700 dark:text-gray-200">PDF × {{ $fileCount }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ $totalFileSizeFormatted }}</div>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                            @php
                                // Build files array from document_files (all files are stored there now)
                                $files = [];
                                foreach ($document->files as $file) {
                                    $files[] = (object)[
                                        'id' => $file->id,
                                        'filename' => $file->filename,
                                        'file_path' => $file->file_path,
                                        'is_main' => false
                                    ];
                                }
                            @endphp
                            <div class="flex items-center justify-center gap-2">
                                <button onclick='openViewDocumentModal({{ $document->id }}, {{ json_encode($document->title) }}, {{ json_encode($document->files->first()?->filename ?? $document->filename) }}, @json($files))'
                                    class="text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 rounded-lg px-2 py-1.5 text-xs dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 transition-colors"
                                    title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick='openEditDocumentModal(@json($document->toArray()))'
                                    class="text-amber-600 hover:text-white border border-amber-600 hover:bg-amber-600 rounded-lg px-2 py-1.5 text-xs dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 transition-colors"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="openDeleteDocumentModal({{ $document->id }}, '{{ addslashes($document->title) }}')"
                                    class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 rounded-lg px-2 py-1.5 text-xs dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 transition-colors"
                                    title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-inbox text-4xl mb-2 text-gray-300 dark:text-gray-600"></i>
                            <p class="mt-2">{{ request('q') ? 'No documents found matching your search.' : 'No documents uploaded yet.' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                @if ($perPage != 'all')
                    {{ $documents->appends(request()->query())->links() }}
                @else
                    <div class="text-center text-xs text-gray-600 dark:text-gray-400">
                        Displaying all {{ $documents->total() }} records
                    </div>
                @endif
            </div>
            </div>
            
        </div>
    </div>

    @include('documents.modal.create')
    @include('documents.modal.edit')
    @include('documents.modal.delete')
    @include('documents.modal.view')

<script>
    // Function to toggle dropdown menu
    function toggleDropdown(button) {
        let dropdown = button.nextElementSibling;
        let isOpen = !dropdown.classList.contains("hidden"); // true if already visible

        closeAllDropdowns(); // close all first

        if (!isOpen) {
            dropdown.classList.remove("hidden"); // open only if it wasn't open
        }
    }

    function closeAllDropdowns() {
        document.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.add("hidden"));
    }

    // Wrapper function to call openEditDocumentModal from search results
    function editDocumentFromSearch(docData) {
        openEditDocumentModal(docData);
    }

    // Close dropdown if click happens outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative.inline-block')) {
            closeAllDropdowns();
        }
    });

    // Real-time search
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const tbody = document.querySelector('tbody');
    let isSearching = false;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            // Only redirect if user had previously searched and now cleared the field
            if (isSearching) {
                location.href = '{{ route("documents.index") }}';
            }
            // Reset count back to server total when search is cleared
            document.getElementById('totalRecordsCount').textContent = '{{ $documents->total() }}';
            return;
        }

        isSearching = true;
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    async function performSearch(query) {
        try {
            const response = await fetch(`{{ route('documents.search') }}?q=${encodeURIComponent(query)}`);
            if (!response.ok) return;
            const data = await response.json();

            // ✅ Use server-returned total if available, fallback to array length
            const total = data.total ?? (data.documents || []).length;
            document.getElementById('totalRecordsCount').textContent = total;

            updateTable(data.documents || []);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    function updateTable(documents) {
        // Update total records count
        document.getElementById('totalRecordsCount').textContent = documents.length;

        if (!documents || documents.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300 dark:text-gray-600"></i>
                        <p class="mt-2">No documents found matching your search.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = documents.map(doc => `
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                <td class="px-6 py-3 border-b border-gray-300 text-left text-gray-600 dark:text-gray-300">
                    <i class="fas fa-file-pdf text-red-600 mr-2"></i>${escapeHtml(doc.title)}
                </td>
                <td class="px-6 py-3 border-b border-gray-300 text-left text-gray-600 dark:text-gray-300">
                    ${doc.category ? escapeHtml(doc.category) : '-'}
                </td>
                <td class="px-6 py-3 border-b border-gray-300 text-left text-gray-600 dark:text-gray-300">
                    ${doc.created_at}
                </td>
                <td class="px-6 py-3 border-b border-gray-300 text-left text-gray-600 dark:text-gray-300">
                    ${doc.tags
                        ? `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">${escapeHtml(doc.tags)}</span>`
                        : '<span class="text-gray-400">-</span>'
                    }
                </td>
                <td class="px-6 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                    ${doc.files && doc.files.length > 0
                        ? `<div class="text-xs">
                            <div class="font-semibold text-gray-700 dark:text-gray-200">PDF × ${doc.files.length}</div>
                        </div>`
                        : '<span class="text-gray-400">-</span>'
                    }
                </td>
                <td class="px-6 py-3 border-b border-gray-300 text-gray-600 dark:text-gray-300">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="openViewDocumentModal(${doc.id}, '${escapeHtml(doc.title)}', '${escapeHtml(doc.filename)}', ${JSON.stringify(doc.files || []).replace(/"/g, '&quot;')})"
                            class="text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 rounded-lg px-2 py-1.5 text-xs dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-600 transition-colors"
                            title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick='editDocumentFromSearch(${JSON.stringify(doc).replace(/"/g, "&quot;")})'
                            class="text-amber-600 hover:text-white border border-amber-600 hover:bg-amber-600 rounded-lg px-2 py-1.5 text-xs dark:border-amber-500 dark:text-amber-500 dark:hover:text-white dark:hover:bg-amber-600 transition-colors"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="openDeleteDocumentModal(${doc.id}, '${escapeHtml(doc.title)}')"
                            class="text-red-600 hover:text-white border border-red-600 hover:bg-red-600 rounded-lg px-2 py-1.5 text-xs dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 transition-colors"
                            title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
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

    function clearSearch() {
        searchInput.value = '';
        location.href = '{{ route("documents.index") }}';
    }

    // Check for success message from sessionStorage (from edit modal)
    window.addEventListener('DOMContentLoaded', function() {
        const successMessage = sessionStorage.getItem('successMessage');
        const successType = sessionStorage.getItem('successType');
        if (successMessage) {
            sessionStorage.removeItem('successMessage');
            sessionStorage.removeItem('successType');

            let alertClass = 'bg-green-100 border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200';
            if (successType === 'update') {
                alertClass = 'bg-blue-100 border-blue-400 text-blue-700 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-200';
            } else if (successType === 'delete') {
                alertClass = 'bg-red-100 border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200';
            }

            const alertDiv = document.createElement('div');
            alertDiv.className = `border-l-4 p-4 mb-4 ${alertClass}`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                <div class="flex justify-between items-center">
                    <div>
                        <p>${successMessage}</p>
                    </div>
                    <button type="button" class="text-2xl font-semibold leading-none dark:text-gray-200" onclick="this.parentElement.parentElement.remove();">
                        &times;
                    </button>
                </div>
            `;
            const firstAlert = document.querySelector('[role="alert"]') || document.querySelector('.page-transition');
            if (firstAlert) {
                firstAlert.parentElement.insertBefore(alertDiv, firstAlert);
            }
        }
    });
</script>

<style>
    @keyframes pageSlideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .page-transition {
        animation: pageSlideUp 0.4s ease-in-out;
    }
</style>
    </div>
</x-app-layout>
