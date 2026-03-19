<?php

namespace App\Http\Controllers;

use App\Models\Obligation;
use App\Models\ObligationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ObligationFileController extends Controller
{
    const MAX_FILE_SIZE = 200 * 1024 * 1024; // 200 MB in bytes

    /**
     * Store an uploaded file for an obligation
     */
    public function store(Request $request, Obligation $obligation)
    {
        $request->validate([
            'file' => 'required|file|max:' . (self::MAX_FILE_SIZE / 1024),
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileType = $file->getMimeType();
            
            // Generate unique filename
            $fileName = time() . '_' . str_replace(' ', '_', $originalName);
            
            // Store file in the obligation_files directory
            $filePath = $file->storeAs(
                'obligation_files/' . $obligation->id,
                $fileName,
                'private'
            );

            // Create database record
            $obligationFile = ObligationFile::create([
                'obligation_id' => $obligation->id,
                'file_name' => $fileName,
                'original_file_name' => $originalName,
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'uploaded_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => $this->formatFileData($obligationFile),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a file
     */
    public function download(ObligationFile $obligationFile)
    {
        if (!Storage::disk('private')->exists($obligationFile->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('private')->download(
            $obligationFile->file_path,
            $obligationFile->original_file_name
        );
    }

    /**
     * Delete a file
     */
    public function destroy(ObligationFile $obligationFile)
    {
        try {
            // Delete from storage
            if (Storage::disk('private')->exists($obligationFile->file_path)) {
                Storage::disk('private')->delete($obligationFile->file_path);
            }

            // Delete database record
            $obligationFile->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update file name
     */
    public function update(Request $request, ObligationFile $obligationFile)
    {
        $request->validate([
            'original_file_name' => 'required|string|max:255',
        ]);

        try {
            $obligationFile->update([
                'original_file_name' => $request->input('original_file_name'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File name updated successfully',
                'file' => $this->formatFileData($obligationFile),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get file for response
     */
    public function getFiles(Obligation $obligation)
    {
        $files = $obligation->files()->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'files' => $files->map(fn($file) => $this->formatFileData($file))->toArray(),
        ]);
    }

    /**
     * Preview file metadata for view modal
     */
    public function preview(ObligationFile $obligationFile)
    {
        if (!Storage::disk('private')->exists($obligationFile->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'file_type' => $obligationFile->file_type,
            'file_path' => $obligationFile->file_path,
            'file_url' => route('obligation_files.view', $obligationFile),
        ]);
    }

    /**
     * Serve file inline for viewing (images, PDFs, etc.)
     */
    public function view(ObligationFile $obligationFile)
    {
        try {
            if (!Storage::disk('private')->exists($obligationFile->file_path)) {
                \Log::error('File path not found in storage', [
                    'file_id' => $obligationFile->id,
                    'file_path' => $obligationFile->file_path,
                    'full_path' => Storage::disk('private')->path($obligationFile->file_path),
                ]);
                abort(404, 'File not found in storage');
            }

            $fullPath = Storage::disk('private')->path($obligationFile->file_path);
            
            // Verify file actually exists
            if (!file_exists($fullPath)) {
                \Log::error('Physical file does not exist', [
                    'file_id' => $obligationFile->id,
                    'full_path' => $fullPath,
                ]);
                abort(404, 'Physical file does not exist');
            }

            \Log::info('Serving file', [
                'file_id' => $obligationFile->id,
                'file_name' => $obligationFile->file_name,
                'file_type' => $obligationFile->file_type,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => filesize($fullPath),
            ]);

            return response()->file($fullPath, [
                'Content-Type' => $obligationFile->file_type,
                'Content-Disposition' => 'inline; filename="' . $obligationFile->original_file_name . '"',
                'Cache-Control' => 'public, max-age=3600',
                'Pragma' => 'public',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('ObligationFile not found', ['obligationFile_id' => $obligationFile->id]);
            abort(404, 'File record not found');
        } catch (\Exception $e) {
            \Log::error('Error serving file: ' . $e->getMessage(), [
                'file_id' => $obligationFile->id,
                'exception' => $e->getTraceAsString(),
            ]);
            abort(500, 'Error serving file');
        }
    }

    /**
     * Format file data for response
     */
    private function formatFileData(ObligationFile $file)
    {
        return [
            'id' => $file->id,
            'original_file_name' => $file->original_file_name,
            'file_name' => $file->file_name,
            'file_size' => $file->file_size,
            'formatted_file_size' => $file->formatted_file_size,
            'file_type' => $file->file_type,
            'uploaded_by' => $file->uploaded_by,
            'created_at' => $file->created_at->format('Y-m-d H:i:s'),
            'uploader_name' => $file->uploader?->name ?? 'Unknown',
        ];
    }
}
