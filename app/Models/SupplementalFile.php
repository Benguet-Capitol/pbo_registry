<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplementalFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplemental_no',
        'file_name',
        'original_file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    /**
     * Get all supplementals that share this file's supplemental_no
     */
    public function supplementals()
    {
        return Supplemental::where('supplemental_no', $this->supplemental_no)->get();
    }

    /**
     * Get the user who uploaded this file
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }

    /**
     * Format file size to human-readable format
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
