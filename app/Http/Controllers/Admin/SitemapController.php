<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;

class SitemapController extends Controller
{
    public function index()
    {
        $ttl = (int) setting('cache_ttl_sitemap', 600);
        $ttl = $ttl > 0 ? $ttl : 600;
        $expiresAt = now()->addSeconds($ttl);

        $xml = cache()->remember('sitemap:index', $expiresAt, function () {
            $posts = Post::where('status', 'published')
                ->orderByDesc('updated_at')
                ->select(['id', 'slug', 'category_id', 'updated_at'])
                ->with(['category:id,slug'])
                ->cursor();

            $changefreq = setting('seo_sitemap_changefreq_default', 'daily');
            $priority = setting('seo_sitemap_priority_default', '0.8');

            $xmlParts = [];
            $xmlParts[] = '<?xml version="1.0" encoding="UTF-8"?>';
            $xmlParts[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            $xmlParts[] = '<url>';
            $xmlParts[] = '<loc>' . e(url('/')) . '</loc>';
            $xmlParts[] = '<changefreq>' . e($changefreq) . '</changefreq>';
            $xmlParts[] = '<priority>1.0</priority>';
            $xmlParts[] = '</url>';

            foreach ($posts as $post) {
                $xmlParts[] = '<url>';
                $xmlParts[] = '<loc>' . e($post->frontend_url) . '</loc>';
                $xmlParts[] = '<lastmod>' . $post->updated_at->toAtomString() . '</lastmod>';
                $xmlParts[] = '<changefreq>' . e($changefreq) . '</changefreq>';
                $xmlParts[] = '<priority>' . e($priority) . '</priority>';
                $xmlParts[] = '</url>';
            }

            $xmlParts[] = '</urlset>';

            return implode('', $xmlParts);
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function categories()
    {
        $categories = Category::orderBy('name')->get(['id', 'slug', 'name']);

        return response()->view('admin.sitemaps.categories', compact('categories'))
            ->header('Content-Type', 'text/xml');
    }
}
