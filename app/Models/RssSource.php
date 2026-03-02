<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RssSource extends Model
{
    protected $fillable = [
        'name',
        'feed_url',
        'source_domain',
        'default_category_id',
        'is_active',
        'fetch_interval_minutes',
        'last_fetched_at',
        'last_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_fetched_at' => 'datetime',
    ];

    public function defaultCategory()
    {
        return $this->belongsTo(Category::class, 'default_category_id');
    }

    public function items()
    {
        return $this->hasMany(NewsPoolItem::class);
    }
}
