<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostShowController extends Controller
{
    public function show(string $categorySlug, string $slugKey)
    {
        $parsed = $this->parseSlugKey($slugKey);
        abort_if(!$parsed, 404);

        [$slug, $id] = $parsed;

        $ttl = (int) setting('cache_ttl_post_show', 600);
        $ttl = $ttl > 0 ? $ttl : 600;
        $expiresAt = now()->addSeconds($ttl);

        $cacheKey = 'post:public:id:' . $id;
        $post = cache()->remember($cacheKey, $expiresAt, function () use ($id) {
            return Post::where('id', $id)
                ->where('status', 'published')
                ->with(['category:id,name,slug,parent_id', 'category.parent:id,name,slug', 'user:id,name,avatar,bio'])
                ->firstOrFail();
        });

        $mainCategorySlug = $post->category?->parent?->slug ?: ($post->category?->slug ?? 'genel');
        if ($post->slug !== $slug || $mainCategorySlug !== $categorySlug) {
            return redirect()->to($post->frontend_url, 301);
        }

        $relatedKey = 'post:related:' . $post->id;
        $related = cache()->remember($relatedKey, $expiresAt, function () use ($post) {
            return Post::news()
                ->where('status', 'published')
                ->where('id', '!=', $post->id)
                ->when($post->category_id, function ($q) use ($post) {
                    $q->where('category_id', $post->category_id);
                })
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->take(5)
                ->get();
        });

        $nextKey = 'post:next:' . $post->id;
        $nextPost = cache()->remember($nextKey, $expiresAt, fn () => $this->findNextPost($post));
        $commentsKey = 'post:comments:approved:' . $post->id;
        $approvedComments = cache()->remember($commentsKey, $expiresAt, function () use ($post) {
            return $post->approvedComments()
                ->orderByDesc('approved_at')
                ->orderByDesc('id')
                ->get(['id', 'post_id', 'name', 'content', 'approved_at', 'created_at']);
        });

        return view('frontend.post', [
            'post' => $post,
            'related' => $related,
            'nextPost' => $nextPost,
            'approvedComments' => $approvedComments,
            'postStreamEnabled' => (bool) ((int) setting('post_stream_enabled', 1)),
            'seoTitle' => ($post->seo_title ?: $post->title) . ' | ' . setting('site_title'),
            'seoDescription' => $post->seo_description
                ?: $post->summary
                ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 155),
            'seoKeywords' => $post->focus_keywords ?: $post->tags ?: setting('seo_meta_keywords'),
            'seoImage' => $post->image ?? setting('seo_og_image'),
        ]);
    }

    public function nextById(Request $request, int $id)
    {
        if (!(bool) ((int) setting('post_stream_enabled', 1))) {
            return response()->json(['ok' => true, 'hasNext' => false]);
        }

        $post = Post::where('id', $id)
            ->where('status', 'published')
            ->with(['category:id,name,slug,parent_id', 'category.parent:id,name,slug', 'user:id,name,avatar,bio'])
            ->firstOrFail();

        $next = $this->findNextPost($post);
        if (!$next) {
            return response()->json(['ok' => true, 'hasNext' => false]);
        }

        $next = Post::where('id', $next->id)
            ->where('status', 'published')
            ->with(['category:id,name,slug,parent_id', 'category.parent:id,name,slug', 'user:id,name,avatar,bio'])
            ->firstOrFail();

        $approvedComments = $next->approvedComments()
            ->orderByDesc('approved_at')
            ->orderByDesc('id')
            ->get(['id', 'post_id', 'name', 'content', 'approved_at', 'created_at']);

        $related = Post::news()
            ->where('status', 'published')
            ->where('id', '!=', $next->id)
            ->when($next->category_id, function ($q) use ($next) {
                $q->where('category_id', $next->category_id);
            })
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        $html = view('frontend.partials.post-stream-item', [
            'post' => $next,
            'approvedComments' => $approvedComments,
            'related' => $related,
        ])->render();

        return response()->json([
            'ok' => true,
            'hasNext' => true,
            'postId' => $next->id,
            'html' => $html,
        ]);
    }

    public function sidebarById(int $id)
    {
        $post = Post::where('id', $id)
            ->where('status', 'published')
            ->firstOrFail(['id', 'category_id']);

        $related = Post::news()
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->when($post->category_id, function ($q) use ($post) {
                $q->where('category_id', $post->category_id);
            })
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        $html = view('frontend.partials.post-related-sidebar', ['related' => $related])->render();

        return response()->json([
            'ok' => true,
            'html' => $html,
        ]);
    }

    public function legacy(string $slug)
    {
        $post = Post::where('slug', $slug)
            ->where('status', 'published')
            ->with(['category:id,slug,parent_id', 'category.parent:id,slug'])
            ->firstOrFail();

        return redirect()->to($post->frontend_url, 301);
    }

    protected function parseSlugKey(string $slugKey): ?array
    {
        if (!preg_match('/^(.*)-n(\d+)$/', $slugKey, $m)) {
            return null;
        }

        $slug = trim((string) ($m[1] ?? ''));
        $id = (int) ($m[2] ?? 0);

        if ($slug === '' || $id <= 0) {
            return null;
        }

        return [$slug, $id];
    }

    protected function findNextPost(Post $post): ?Post
    {
        return Post::news()
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->when($post->category_id, function ($q) use ($post) {
                $q->where('category_id', $post->category_id);
            })
            ->where(function ($q) use ($post) {
                $publishedAt = $post->published_at ?: $post->created_at;
                $q->where('published_at', '<', $publishedAt)
                    ->orWhere(function ($qq) use ($post, $publishedAt) {
                        $qq->where('published_at', $publishedAt)->where('id', '<', $post->id);
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first(['id', 'slug', 'category_id']);
    }
}
