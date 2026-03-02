<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ENewspaperItem extends Model
{
    use HasFactory;

    protected $table = 'e_newspaper_items';

    protected $fillable = [
        'e_newspaper_id',
        'post_id',
        'section',
        'position',
        'title',
        'summary',
        'image',
        'show_image',
        'category_name',
        'post_url',
        'post_published_at',
    ];

    protected $casts = [
        'post_published_at' => 'datetime',
        'show_image' => 'boolean',
    ];

    public function issue()
    {
        return $this->belongsTo(ENewspaper::class, 'e_newspaper_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
