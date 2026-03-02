<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(string $tag, Request $request)
    {
        $tag = trim($tag);
        abort_if($tag === '', 404);

        $ttl = (int) setting('cache_ttl_search', 60);
        $ttl = $ttl > 0 ? $ttl : 60;
        $expiresAt = now()->addSeconds($ttl);

        $page = (int) $request->query('page', 1);
        $cacheKey = 'tag:' . md5($tag) . ':page:' . $page;

        $posts = cache()->remember($cacheKey, $expiresAt, function () use ($tag) {
            return Post::news()
                ->where('status', 'published')
                ->whereRaw('FIND_IN_SET(?, tags)', [$tag])
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->paginate(10);
        });

        if ($request->boolean('feed')) {
            return response()->json([
                'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
            ]);
        }

        $sliderPosts = cache()->remember('tag:' . md5($tag) . ':slider', $expiresAt, function () use ($tag) {
            return Post::news()
                ->where('status', 'published')
                ->whereRaw('FIND_IN_SET(?, tags)', [$tag])
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->limit(15)
                ->get();
        });

        $weeklyPopularPosts = cache()->remember('tag:' . md5($tag) . ':weekly_popular', $expiresAt, function () use ($tag) {
            return Post::news()
                ->where('status', 'published')
                ->whereRaw('FIND_IN_SET(?, tags)', [$tag])
                ->where('published_at', '>=', now()->subDays(7))
                ->with(['category:id,name,slug'])
                ->orderByDesc('view_count')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();
        });

        return view('frontend.tag', [
            'tag' => $tag,
            'posts' => $posts,
            'sliderPosts' => $sliderPosts,
            'weeklyPopularPosts' => $weeklyPopularPosts,
            'seoTitle' => 'Etiket: ' . $tag . ' | ' . setting('site_title'),
            'seoDescription' => 'Etiket bazli haber listesi: ' . $tag,
            'seoImage' => setting('seo_og_image'),
        ]);
    }
}
