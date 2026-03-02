<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'user_id',
        'category_id',
        'status',
        'view_count',
    ];

    protected $casts = [
        'view_count' => 'integer',
    ];

    protected static function booted()
    {
        // CREATE: slug yoksa üret (unique)
        static::creating(function (Article $article) {
            if (blank($article->slug)) {
                $article->slug = static::createUniqueSlug($article->title);
            } else {
                $article->slug = static::createUniqueSlug($article->slug);
            }

            // default view_count
            if (is_null($article->view_count)) {
                $article->view_count = 0;
            }
        });

        // UPDATE: title değiştiyse slug’ı unique yap (istersen bu kısmı kapatabilirsin)
        static::updating(function (Article $article) {
            if ($article->isDirty('title')) {
                $article->slug = static::createUniqueSlug($article->title, $article->id);
            }
        });
    }

    private static function createUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $i = 1;

        $query = static::query();
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->where('slug', $slug)->exists()) {
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        // BURASI BOZUKTU -> düzeltildi
        return $this->belongsTo(Category::class, 'category_id');
    }
}
