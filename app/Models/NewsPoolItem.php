<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsPoolItem extends Model
{
    protected $fillable = [
        'rss_source_id',
        'category_id',
        'published_post_id',
        'raw_title',
        'raw_summary',
        'raw_content',
        'title',
        'slug',
        'summary',
        'content',
        'ai_title',
        'ai_summary',
        'ai_content',
        'ai_keywords',
        'ai_status',
        'ai_error',
        'image_url',
        'source_url',
        'source_guid',
        'fingerprint',
        'source_published_at',
        'status',
        'ai_processed',
        'meta',
    ];

    protected $casts = [
        'source_published_at' => 'datetime',
        'ai_processed' => 'boolean',
        'meta' => 'array',
    ];

    public function source()
    {
        return $this->belongsTo(RssSource::class, 'rss_source_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function publishedPost()
    {
        return $this->belongsTo(Post::class, 'published_post_id');
    }
}
