<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents with search functionality
     */
    public function index(Request $request)
    {
        $query = $request->input('q');
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        // Whitelist sortable columns
        $allowedSorts = ['id', 'title', 'category', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }
        $sortOrder = $sortOrder === 'asc' ? 'asc' : 'desc';

        // Handle 'all' option
        if ($perPage === 'all') {
            $perPage = PHP_INT_MAX;
        } else {
            $perPage = (int) $perPage;
        }

        if ($query) {
            $documents = Document::search($query)
                ->with('uploadedBy', 'files')
                ->paginate($perPage)
                ->appends(['q' => $query, 'per_page' => $request->input('per_page', 10), 'sort_by' => $sortBy, 'sort_order' => $sortOrder]);
        } else {
            $documents = Document::with('uploadedBy', 'files')
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage)
                ->appends(request()->query());
        }

        return view('documents.index', [
            'documents' => $documents,
            'query' => $query,
            'perPage' => $request->input('per_page', 10),
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    /**
     * Real-time search API for documents
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json(['documents' => []]);
        }

        $documents = Document::search($query)
            ->get()
            ->load('uploadedBy', 'files')
            ->map(function ($doc) {
                // Build files array with main file + additional files
                $files = $doc->files->map(fn($file) => [
                    'id' => $file->id,
                    'filename' => $file->filename,
                    'file_path' => "/documents/{$doc->id}/files/{$file->id}",
                    'is_main' => false,
                ])->values()->toArray();
                
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'filename' => $doc->filename,
                    'category' => $doc->category,
                    'description' => $doc->description,
                    'uploaded_by' => $doc->uploadedBy?->name,
                    'uploaded_by_name' => $doc->uploadedBy?->name,
                    'created_at' => $doc->created_at->format('M d, Y'),
                    'tags' => $doc->tags,
                    'files' => $files,
                ];
            });

        return response()->json([
            'documents' => $documents,
            'total' => $documents->count(),
        ]);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'description' => 'nullable|string',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:102400',
        ], [
            'files.required' => 'At least one file is required',
            'files.min' => 'Please upload at least one file',
        ]);

        try {
            $mainFile = $request->file('files')[0];
            $mainFilename = $mainFile->getClientOriginalName();

            $document = Document::create([
                'title' => $validated['title'],
                'filename' => $mainFilename,
                'file_path' => 'documents/' . $mainFilename,
                'category' => $validated['category'],
                'tags' => $validated['tags'],
                'description' => $validated['description'],
                'uploaded_by' => auth()->id(),
                'pdf_content' => null,
            ]);

            foreach ($request->file('files') as $file) {
                $filePath = $file->store('documents');
                $pdfContent = $this->extractPdfText($file);

                $document->files()->create([
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'pdf_content' => $pdfContent,
                ]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'description' => "Uploaded archive '{$validated['title']}' with " . count($request->file('files')) . " file(s)",
                'event_type' => 'create',
                'details' => [
                    'module' => 'Archives',
                    'action' => 'Upload',
                    'document_id' => $document->id,
                    'title' => $validated['title'],
                    'file_count' => count($request->file('files')),
                    'category' => $validated['category'] ?? null,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'status' => 'success',
                'type' => 'create', // ✅ pass the type so JS can pick the right color
                'message' => "Archive '<strong>{$validated['title']}</strong>' has been uploaded successfully!",
                'redirect' => route('documents.index'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload archive: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified document (download)
     */
    /**
     * Display the document inline for viewing
     */
    public function show(Document $document)
    {
        $path = Storage::disk('local')->path($document->file_path);
        if (!file_exists($path)) {
            abort(404, 'File not found');
        }
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->filename . '"',
        ]);
    }

    /**
     * Download the document as attachment
     */
    public function download(Document $document)
    {
        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'description' => "Downloaded archive '{$document->title}'",
            'event_type' => 'download',
            'details' => [
                'module' => 'Archives',
                'action' => 'Download',
                'document_id' => $document->id,
                'filename' => $document->filename,
                'title' => $document->title,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::download($document->file_path, $document->filename);
    }

    /**
     * View a specific document file inline
     */
    public function viewFile(Document $document, $file)
    {
        // Check if it's the main file
        if ($file === 'main') {
            $path = Storage::disk('local')->path($document->file_path);
            if (!file_exists($path)) {
                abort(404, 'File not found');
            }
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->filename . '"',
            ]);
        }

        // It's an additional file - find it in document_files
        $documentFile = $document->files()->find($file);
        
        if (!$documentFile) {
            abort(404, 'File not found');
        }

        $path = Storage::disk('local')->path($documentFile->file_path);
        if (!file_exists($path)) {
            abort(404, 'File not found: ' . $path);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $documentFile->filename . '"',
        ]);
    }

    /**
     * Download a specific document file as attachment
     */
    public function downloadFile(Document $document, $file)
    {
        // Check if it's the main file
        if ($file === 'main') {
            $path = Storage::disk('local')->path($document->file_path);
            if (!file_exists($path)) {
                abort(404, 'File not found');
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'description' => "Downloaded file '{$document->filename}' from archive '{$document->title}'",
                'event_type' => 'download',
                'details' => [
                    'module' => 'Archives',
                    'action' => 'Download File',
                    'document_id' => $document->id,
                    'filename' => $document->filename,
                    'document_title' => $document->title,
                    'is_main_file' => true,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->download($path, $document->filename, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // It's an additional file - find it in document_files
        $documentFile = $document->files()->find($file);
        
        if (!$documentFile) {
            abort(404, 'File not found');
        }

        $path = Storage::disk('local')->path($documentFile->file_path);
        if (!file_exists($path)) {
            abort(404, 'File not found: ' . $path);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'description' => "Downloaded file '{$documentFile->filename}' from archive '{$document->title}'",
            'event_type' => 'download',
            'details' => [
                'module' => 'Archives',
                'action' => 'Download File',
                'document_id' => $document->id,
                'file_id' => $documentFile->id,
                'filename' => $documentFile->filename,
                'document_title' => $document->title,
                'is_main_file' => false,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->download($path, $documentFile->filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'description' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'nullable|file|max:102400', // Optional files, max 100MB each
        ]);

        // Handle multiple file uploads if provided
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file) {
                    // Store file
                    $filePath = $file->store('documents');
                    
                    // Extract PDF text for search indexing
                    $pdfContent = $this->extractPdfText($file);

                    // Create document file record
                    $document->files()->create([
                        'filename' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'pdf_content' => $pdfContent,
                    ]);
                }
            }
        }

        // Update document metadata
        $document->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'tags' => $validated['tags'],
            'description' => $validated['description'],
        ]);

        // Log activity
        $newFilesCount = $request->hasFile('files') ? count($request->file('files')) : 0;
        ActivityLog::create([
            'user_id' => auth()->id(),
            'description' => "Updated archive '{$validated['title']}'" . ($newFilesCount > 0 ? " and added {$newFilesCount} file(s)" : ''),
            'event_type' => 'update',
            'details' => [
                'module' => 'Archives',
                'action' => 'Update',
                'document_id' => $document->id,
                'title' => $validated['title'],
                'new_files_added' => $newFilesCount,
                'category' => $validated['category'] ?? null,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $message = "Archive '<strong>{$validated['title']}</strong>' has been updated successfully!";

        if ($request->wantsJson()) {
            // For AJAX requests, return JSON with message to store in sessionStorage
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'redirect' => route('documents.index'),
            ]);
        }

        // For regular requests, redirect with session message
        return redirect()->route('documents.index')->with('status', $message);
    }

    /**
     * Delete the specified document
     */
    public function destroy(Document $document)
    {
        try {
            $title = $document->title;
            $fileCount = $document->files->count();

            // Delete the file from storage
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Delete all associated files from storage
            foreach ($document->files as $file) {
                if (Storage::exists($file->file_path)) {
                    Storage::delete($file->file_path);
                }
            }

            // Log activity before deletion
            ActivityLog::create([
                'user_id' => auth()->id(),
                'description' => "Deleted archive '{$title}' with {$fileCount} file(s)",
                'event_type' => 'delete',
                'details' => [
                    'module' => 'Archives',
                    'action' => 'Delete',
                    'document_id' => $document->id,
                    'title' => $title,
                    'file_count' => $fileCount,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Delete the document
            $document->delete();

            return redirect()->route('documents.index')
                ->with('status', [
                    'type' => 'delete',
                    'message' => "Archive '<strong>{$title}</strong>' has been deleted successfully!"
                ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete archive: ' . $e->getMessage());
        }
    }

    /**
     * Extract text content from PDF file
     */
    private function extractPdfText($file)
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getRealPath());
            $pages = $pdf->getPages();
            
            $text = '';
            foreach ($pages as $page) {
                $text .= $page->getText() . "\n";
            }
            
            return $text;
        } catch (\Exception $e) {
            \Log::warning('PDF text extraction failed: ' . $e->getMessage());
            return null;
        }
    }
}
