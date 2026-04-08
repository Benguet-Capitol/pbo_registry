<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderFileController extends Controller
{
    const MAX_FILE_SIZE = 200 * 1024 * 1024; // 200 MB in bytes

    /**
     * Store an uploaded file for a purchase order by po_number
     */
    public function store(Request $request)
    {
        $request->validate([
            'po_number' => 'required|string',
            'file' => 'required|file|max:' . (self::MAX_FILE_SIZE / 1024),
        ]);

        try {
            $file = $request->file('file');
            $poNumber = $request->input('po_number');
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            
            // Get MIME type from file extension and finfo
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $fileType = $file->getMimeType();
            
            // Build MIME type map for common extensions
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'txt' => 'text/plain',
                'csv' => 'text/csv',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xls' => 'application/vnd.ms-excel',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
            
            // Override MIME type if we have a known mapping for this extension
            if (isset($mimeTypes[$extension])) {
                $fileType = $mimeTypes[$extension];
            }
            
            // Generate unique filename preserving extension
            $fileName = time() . '_' . str_replace(' ', '_', pathinfo($originalName, PATHINFO_FILENAME));
            if ($extension) {
                $fileName .= '.' . $extension;
            }
            
            // Store file in the purchase_order_files directory
            $filePath = $file->storeAs(
                'purchase_order_files/' . $poNumber,
                $fileName,
                'private'
            );
            
            // Verify file was stored correctly
            if (!Storage::disk('private')->exists($filePath)) {
                throw new \Exception('File storage verification failed');
            }
            
            // Verify file size matches
            $storedSize = Storage::disk('private')->size($filePath);
            if ($storedSize !== $fileSize) {
                \Log::warning('File size mismatch detected', [
                    'original_size' => $fileSize,
                    'stored_size' => $storedSize,
                ]);
            }

            // Create database record
            $purchaseOrderFile = PurchaseOrderFile::create([
                'po_number' => $poNumber,
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
                'file' => $this->formatFileData($purchaseOrderFile),
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
    public function download(PurchaseOrderFile $purchaseOrderFile)
    {
        if (!Storage::disk('private')->exists($purchaseOrderFile->file_path)) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::disk('private')->path($purchaseOrderFile->file_path);
        
        if (!file_exists($fullPath)) {
            abort(404, 'File not found on disk');
        }

        // Clear any output buffering to prevent file corruption
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        \Log::info('Downloading file', [
            'file_id' => $purchaseOrderFile->id,
            'file_name' => $purchaseOrderFile->original_file_name,
            'file_type' => $purchaseOrderFile->file_type,
            'file_size' => filesize($fullPath),
        ]);

        // Use response()->download() for better control over headers
        return response()->download(
            $fullPath,
            $purchaseOrderFile->original_file_name,
            [
                'Content-Type' => $purchaseOrderFile->file_type,
                'Content-Length' => filesize($fullPath),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ],
            'attachment'
        );
    }

    /**
     * Delete a file
     */
    public function destroy(PurchaseOrderFile $purchaseOrderFile)
    {
        try {
            // Delete from storage
            if (Storage::disk('private')->exists($purchaseOrderFile->file_path)) {
                Storage::disk('private')->delete($purchaseOrderFile->file_path);
            }

            // Delete database record
            $purchaseOrderFile->delete();

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
    public function update(Request $request, PurchaseOrderFile $purchaseOrderFile)
    {
        $request->validate([
            'original_file_name' => 'required|string|max:255',
        ]);

        try {
            $purchaseOrderFile->update([
                'original_file_name' => $request->input('original_file_name'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File name updated successfully',
                'file' => $this->formatFileData($purchaseOrderFile),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get files for a purchase order by po_number
     */
    public function getFiles(Request $request)
    {
        $poNumber = $request->input('po_number');
        
        if (!$poNumber) {
            return response()->json([
                'success' => false,
                'message' => 'PO number is required',
            ], 400);
        }
        
        $files = PurchaseOrderFile::where('po_number', $poNumber)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'files' => $files->map(fn($file) => $this->formatFileData($file))->toArray(),
        ]);
    }

    /**
     * Preview file metadata for view modal
     */
    public function preview(PurchaseOrderFile $purchaseOrderFile)
    {
        if (!Storage::disk('private')->exists($purchaseOrderFile->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'file_type' => $purchaseOrderFile->file_type,
            'file_path' => $purchaseOrderFile->file_path,
            'file_url' => route('purchase_order_files.view', $purchaseOrderFile),
        ]);
    }

    /**
     * Serve file inline for viewing (images, PDFs, etc.)
     */
    public function view(PurchaseOrderFile $purchaseOrderFile)
    {
        try {
            if (!Storage::disk('private')->exists($purchaseOrderFile->file_path)) {
                \Log::error('File path not found in storage', [
                    'file_id' => $purchaseOrderFile->id,
                    'file_path' => $purchaseOrderFile->file_path,
                    'full_path' => Storage::disk('private')->path($purchaseOrderFile->file_path),
                ]);
                abort(404, 'File not found in storage');
            }

            $fullPath = Storage::disk('private')->path($purchaseOrderFile->file_path);
            
            // Verify file actually exists
            if (!file_exists($fullPath)) {
                \Log::error('Physical file does not exist', [
                    'file_id' => $purchaseOrderFile->id,
                    'full_path' => $fullPath,
                ]);
                abort(404, 'Physical file does not exist');
            }

            \Log::info('Serving file', [
                'file_id' => $purchaseOrderFile->id,
                'file_name' => $purchaseOrderFile->file_name,
                'file_type' => $purchaseOrderFile->file_type,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => filesize($fullPath),
            ]);

            // Clear output buffer to prevent file corruption
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $fileSize = filesize($fullPath);
            
            // Serve file with correct headers
            return response()->file($fullPath, [
                'Content-Type' => $purchaseOrderFile->file_type,
                'Content-Length' => $fileSize,
                'Cache-Control' => 'public, max-age=3600',
                'Accept-Ranges' => 'bytes',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('PurchaseOrderFile not found', ['purchaseOrderFile_id' => $purchaseOrderFile->id]);
            abort(404, 'File record not found');
        } catch (\Exception $e) {
            \Log::error('Error serving file: ' . $e->getMessage(), [
                'file_id' => $purchaseOrderFile->id,
                'exception' => $e->getTraceAsString(),
            ]);
            abort(500, 'Error serving file');
        }
    }

    /**
     * Format file data for response
     */
    private function formatFileData(PurchaseOrderFile $file)
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
