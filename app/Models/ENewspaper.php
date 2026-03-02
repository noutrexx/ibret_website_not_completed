<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ENewspaper extends Model
{
    use HasFactory;

    protected $table = 'e_newspapers';

    protected $fillable = [
        'title',
        'slug',
        'issue_date',
        'status',
        'summary',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'published_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ENewspaperItem::class, 'e_newspaper_id')->orderBy('section')->orderBy('position');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'e-gazete';
        $slug = $base;
        $i = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function getFrontendUrlAttribute(): string
    {
        return route('enewspapers.show', ['slug' => $this->slug]);
    }
}

