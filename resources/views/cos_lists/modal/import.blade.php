<!-- Import COS Records Modal -->
<div id="importModal" style="display: none;" aria-hidden="true" class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-lg mx-4 bg-white rounded-lg shadow-lg dark:bg-gray-800 animate-scaleInUp flex flex-col" style="animation: scaleInUp 0.3s ease-out; max-height: 90vh;">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 rounded-t-lg bg-gradient-to-r from-purple-50 to-fuchsia-50 dark:from-gray-700 dark:to-gray-600 dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-import text-sm text-purple-600 dark:text-purple-400"></i>
                {{ __('Import Contract of Services') }}
            </h3>
            <button type="button" onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="px-6 py-4 overflow-y-auto flex-1">
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Download the template, fill it in, then upload it below. Records will be imported under the currently selected Office & Allotment Class and Account.') }}
            </p>

            <a href="{{ route('cos_lists.import_template') }}"
            class="inline-flex items-center gap-2 text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 rounded-lg text-xs px-4 py-2 mb-5 dark:border-blue-500 dark:text-blue-500 dark:hover:bg-blue-600 dark:hover:text-white transition-colors">
                <i class="fas fa-download"></i>
                {{ __('Download Import Template') }}
            </a>

            <form id="importCosForm" method="POST" action="{{ route('cos_lists.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="year1" value="{{ request('year1', date('Y')) }}">
                <input type="hidden" name="office_allotment_class_filter" value="{{ request('office_allotment_class_filter') }}">
                <input type="hidden" name="appropriation_filter" value="{{ request('appropriation_filter') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="search_column" value="{{ request('search_column') }}">
                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                <input type="hidden" name="page" value="{{ request('page') }}">

                <label for="import_file" class="block text-xs font-medium text-gray-900 dark:text-gray-200 mb-2">
                    {{ __('Excel File (.xlsx)') }}
                </label>
                <input type="file" id="import_file" name="file" accept=".xlsx,.xls" required
                       class="block w-full text-xs text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-white dark:bg-gray-800 focus:outline-none">
                <span id="importFileError" class="text-red-500 text-xs mt-1 block"></span>

                @if(session('import_success'))
                    <div class="mt-4 bg-green-50 border border-green-300 text-green-700 rounded-lg px-3 py-2 text-xs dark:bg-green-950 dark:border-green-800 dark:text-green-300">
                        {{ session('import_success') }}
                    </div>
                @endif

                @if(session('import_errors'))
                    <div class="mt-4 bg-red-50 border border-red-300 text-red-700 rounded-lg px-3 py-2 text-xs dark:bg-red-950 dark:border-red-800 dark:text-red-300 max-h-40 overflow-y-auto">
                        <p class="font-semibold mb-1">{{ __('Some rows could not be imported:') }}</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach(session('import_errors') as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </form>
        </div>

        <div class="justify-center items-center p-6 flex items-center gap-3 border-t-2 border-gray-200 rounded-b-lg dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
            <button type="button" id="importSubmitBtn" onclick="handleImportSubmit()"
                    class="text-purple-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-purple-600 hover:bg-purple-600 focus:ring-4 focus:outline-none focus:ring-purple-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-purple-500 dark:text-purple-500 dark:hover:text-white dark:hover:bg-purple-600 dark:focus:ring-purple-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i id="importBtnIcon" class="fas fa-upload text-xl mr-1 -ml-1 w-5 h-5"></i>
                <span id="importBtnLabel">{{ __('Import') }}</span>
            </button>
            <button type="button" onclick="closeImportModal()" class="text-gray-600 inline-flex leading-4 tracking-wider items-center hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-xs px-6 py-2 text-center dark:border-gray-500 dark:text-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-900 transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95">
                <i class="fas fa-times text-xl mr-1 -ml-1 w-5 h-5"></i>
                {{ __('Cancel') }}
            </button>
        </div>
    </div>
</div>

<script>
function openImportModal() {
    document.getElementById('importModal').style.display = 'flex';
    document.getElementById('importModal').setAttribute('aria-hidden', 'false');
}

function closeImportModal() {
    const modal = document.getElementById('importModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

function handleImportSubmit() {
    const fileInput = document.getElementById('import_file');
    const errorSpan = document.getElementById('importFileError');
    errorSpan.textContent = '';

    if (!fileInput.files.length) {
        errorSpan.textContent = '{{ __("Please choose a file to import.") }}';
        return;
    }

    const btn = document.getElementById('importSubmitBtn');
    if (btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';

    document.getElementById('importBtnIcon').classList.remove('fa-upload');
    document.getElementById('importBtnIcon').classList.add('fa-spinner', 'fa-spin');
    document.getElementById('importBtnLabel').textContent = '{{ __("Importing...") }}';
    btn.classList.add('opacity-60', 'pointer-events-none');

    // Real form submission (full page reload) — the server redirects back
    // with success/error flash data, so no fetch/AJAX is needed here.
    document.getElementById('importCosForm').submit();
}

// Reopen the modal automatically if we just redirected back with import results
@if(session('import_errors') || session('import_success'))
document.addEventListener('DOMContentLoaded', openImportModal);
@endif
</script>