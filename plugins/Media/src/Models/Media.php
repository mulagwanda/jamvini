<?php

namespace Plugins\Media\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'cms_media';

    protected $fillable = [
        'filename', 'original_name', 'mime_type', 'size', 'path',
        'thumbnail_path', 'folder', 'source', 'external_id', 'attribution',
        'metadata', 'alt_text', 'uploaded_by'
    ];

    protected $casts = [
        'attribution' => 'array',
        'metadata' => 'array',
    ];

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : $this->url;
    }

    public function getIsImageAttribute()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getSizeForHumansAttribute()
    {
        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function deleteWithFile()
    {
        Storage::disk('public')->delete($this->path);
        if ($this->thumbnail_path) {
            Storage::disk('public')->delete($this->thumbnail_path);
        }
        $this->delete();
    }

    public static function getFolders()
    {
        return static::select('folder')->distinct()->pluck('folder')->toArray();
    }
}
