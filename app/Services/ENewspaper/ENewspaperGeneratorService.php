<?php

namespace App\Services\ENewspaper;

use App\Models\Category;
use App\Models\ENewspaper;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ENewspaperGeneratorService
{
    public function generateForDate(string $issueDate, ?int $userId = null): ENewspaper
    {
        return DB::transaction(function () use ($issueDate, $userId) {
            $title = 'E-Gazete ' . \Carbon\Carbon::parse($issueDate)->format('d.m.Y');

            $issue = ENewspaper::query()->firstOrNew(['issue_date' => $issueDate]);
            $issue->title = $title;
            $issue->slug = ENewspaper::uniqueSlug($title, $issue->exists ? $issue->id : null);
            $issue->summary = 'Gunluk e-gazete sayisi. ' . \Carbon\Carbon::parse($issueDate)->format('d.m.Y');
            $issue->status = $issue->status ?: 'draft';
            $issue->created_by = $issue->created_by ?: $userId;
            $issue->save();

            $this->fillItems($issue);

            return $issue->fresh(['items']);
        });
    }

    private function fillItems(ENewspaper $issue): void
    {
        $usedIds = [];

        $published = fn () => Post::query()
            ->news()
            ->where('status', 'published')
            ->with(['category:id,name,slug,parent_id'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $manset = (clone $published())
            ->whereIn('type', ['top_manset', 'manset', 'surmanset'])
            ->first() ?? (clone $published())->first();

        $items = [];

        if ($manset) {
            $items[] = $this->snapshot($manset, 'manset', 1);
            $usedIds[] = $manset->id;
        }

        $latest = (clone $published())
            ->whereNotIn('id', $usedIds)
            ->take(8)
            ->get();
        foreach ($latest as $idx => $post) {
            $items[] = $this->snapshot($post, 'gundem', $idx + 1);
            $usedIds[] = $post->id;
        }

        foreach (['spor', 'ekonomi'] as $sectionSlug) {
            $sectionPosts = $this->postsForCategorySlug($sectionSlug, $usedIds, 4);
            foreach ($sectionPosts as $idx => $post) {
                $items[] = $this->snapshot($post, $sectionSlug, $idx + 1);
                $usedIds[] = $post->id;
            }
        }

        $culturePosts = collect()
            ->merge($this->postsForCategorySlug('magazin', $usedIds, 2))
            ->merge($this->postsForCategorySlug('yasam', $usedIds, 4))
            ->take(5)
            ->values();
        foreach ($culturePosts as $idx => $post) {
            $items[] = $this->snapshot($post, 'yasam', $idx + 1);
            $usedIds[] = $post->id;
        }

        $issue->items()->delete();
        $issue->items()->createMany($items);
    }

    private function postsForCategorySlug(string $slug, array $excludeIds, int $limit): Collection
    {
        $categoryIds = Category::query()
            ->where('slug', $slug)
            ->orWhereHas('parent', fn ($q) => $q->where('slug', $slug))
            ->pluck('id')
            ->all();

        if (empty($categoryIds)) {
            return collect();
        }

        return Post::query()
            ->news()
            ->where('status', 'published')
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $excludeIds)
            ->with(['category:id,name,slug,parent_id'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take($limit)
            ->get();
    }

    private function snapshot(Post $post, string $section, int $position): array
    {
        return [
            'post_id' => $post->id,
            'section' => $section,
            'position' => $position,
            'title' => (string) $post->title,
            'summary' => $post->summary ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 180),
            'image' => $post->image,
            'show_image' => true,
            'category_name' => $post->category?->name,
            'post_url' => $post->frontend_url,
            'post_published_at' => $post->published_at ?? $post->created_at,
        ];
    }
}
