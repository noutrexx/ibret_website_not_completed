<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $ttl = (int) setting('cache_ttl_search', 60);
        $ttl = $ttl > 0 ? $ttl : 60;
        $expiresAt = now()->addSeconds($ttl);

        $page = (int) $request->query('page', 1);
        $cacheKey = 'search:' . md5($q) . ':page:' . $page;

        $posts = cache()->remember($cacheKey, $expiresAt, function () use ($q) {
            return Post::news()
                ->where('status', 'published')
                ->when($q !== '', function ($query) use ($q) {
                    $query->where('title', 'like', '%' . $q . '%');
                })
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->paginate(12);
        });

        return view('frontend.search', [
            'query' => $q,
            'posts' => $posts,
            'seoTitle' => ($q ? 'Arama: ' . $q : 'Arama') . ' | ' . setting('site_title'),
            'seoDescription' => setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }
}
