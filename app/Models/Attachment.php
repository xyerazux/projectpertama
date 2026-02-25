<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'file_name',
        'file_mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        return rtrim(config('app.url'), '/') . '/storage/attachments/' . basename($this->file_path);
    }

    /**
     * Get the parent attachable model (task or roadmap).
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
