<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    protected $fillable = [
        'document_id',
        'filename',
        'file_path',
        'pdf_content',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
