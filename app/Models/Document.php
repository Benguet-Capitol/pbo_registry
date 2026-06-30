<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Document extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'filename',
        'file_path',
        'category',
        'tags',
        'description',
        'uploaded_by',
        'pdf_content',
    ];

    /**
     * Get the searchable array for Scout indexing
     */
    public function toSearchableArray()
    {
        // Combine pdf_content from document_files table with main document pdf_content
        $filesPdfContent = $this->files->pluck('pdf_content')->filter()->implode(' ');
        $combinedPdfContent = trim($this->pdf_content . ' ' . $filesPdfContent);

        return [
            'title' => $this->title,
            'filename' => $this->filename,
            'category' => $this->category,
            'tags' => $this->tags,
            'description' => $this->description,
            'uploaded_by_name' => $this->uploadedBy?->name,
            'pdf_content' => $combinedPdfContent,
        ];
    }

    /**
     * Get the user who uploaded the document
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get all files associated with this document
     */
    public function files()
    {
        return $this->hasMany(DocumentFile::class);
    }
}

