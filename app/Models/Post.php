<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'user_id', 'title', 'slug', 'summary',
        'content', 'type', 'is_breaking', 'tags', 'image', 'video_url', 'photo_gallery', 'video_gallery', 'city',
        'view_count', 'published_at', 'seo_title', 'seo_description', 'focus_keywords', 'seo_entities', 'schema_jsonld',
        'content_kind', 'status',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'is_breaking' => 'boolean',
        'photo_gallery' => 'array',
        'video_gallery' => 'array',
        'seo_entities' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function (Post $post) {
            self::flushPublicCache($post);
        });

        static::deleted(function (Post $post) {
            self::flushPublicCache($post);
        });

        static::restored(function (Post $post) {
            self::flushPublicCache($post);
        });

        static::forceDeleted(function (Post $post) {
            self::flushPublicCache($post);
        });
    }

    public function scopeNews($q)
    {
        return $q->where('content_kind', 'news');
    }

    public function scopeArticles($q)
    {
        return $q->where('content_kind', 'article');
    }

    public function scopeFilter($q, array $filters)
    {
        if (!empty($filters['search'])) {
            $q->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['category'])) {
            $q->where('category_id', $filters['category']);
        }

        if (!empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q;
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function getFrontendUrlAttribute(): string
    {
        $category = $this->relationLoaded('category') ? $this->category : $this->category()->with('parent:id,slug')->first(['id', 'slug', 'parent_id']);
        $categorySlug = $category?->parent?->slug ?: $category?->slug ?: 'genel';

        return route('post.show', [
            'categorySlug' => $categorySlug,
            'slugKey' => $this->slug . '-n' . $this->id,
        ]);
    }

    public static function normalizeTags(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $parts = collect(preg_split('/[,;]+/', $raw) ?: [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->map(fn ($v) => Str::slug($v))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($parts) ? null : implode(',', $parts);
    }

    public function tagList(): array
    {
        $raw = trim((string) ($this->tags ?? ''));
        if ($raw === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function flushPublicCache(Post $post): void
    {
        cache()->forget('sitemap:index');
        cache()->forget('sitemap:posts');
        cache()->forget('sitemap:news');

        cache()->forget('post:public:' . $post->slug);
        $originalSlug = $post->getOriginal('slug');
        if ($originalSlug && $originalSlug !== $post->slug) {
            cache()->forget('post:public:' . $originalSlug);
        }
        cache()->forget('post:related:' . $post->id);

        cache()->forget('home:page:1');

        if ($post->category_id) {
            $category = Category::find($post->category_id);
            if ($category) {
                cache()->forget('category:' . $category->slug . ':page:1');
                if ($category->parent_id) {
                    $parent = Category::find($category->parent_id);
                    if ($parent) {
                        cache()->forget('category:' . $parent->slug . ':page:1');
                    }
                }
            }
        }
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(Comment::class)->where('status', 'approved');
    }
}
