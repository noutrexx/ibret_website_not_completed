<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'slug', 'page_title', 'page_description', 'page_keywords', 'order'];

    protected static function booted()
    {
        static::saved(function () {
            cache()->forget('nav:categories');
        });

        static::deleted(function () {
            cache()->forget('nav:categories');
        });
    }

    /**
     * Bu kategorinin bağlı olduğu ÜST kategoriyi getirir.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Bu kategoriye bağlı ALT kategorileri getirir.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order', 'asc');
    }

    public function getFrontendUrlAttribute(): string
    {
        if (!$this->parent_id) {
            return route('category.show', ['slug' => $this->slug]);
        }

        $parent = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first(['id', 'slug']);
        if ($parent && !empty($parent->slug)) {
            return route('category.child.show', [
                'parentSlug' => $parent->slug,
                'slug' => $this->slug,
            ]);
        }

        return route('category.show', ['slug' => $this->slug]);
    }
}
