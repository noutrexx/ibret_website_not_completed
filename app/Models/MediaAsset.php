<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = [
        'user_id',
        'disk',
        'path',
        'original_name',
        'title',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'source_provider',
        'source_url',
        'credit',
        'is_favorite',
        'hash',
        'meta',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->path, '/'));
    }
}

