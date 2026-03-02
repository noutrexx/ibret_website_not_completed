<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Services\Economy\TruncgilEconomyService;
use App\Services\Sports\CustomFootballApiService;

class HomeController extends Controller
{
    public function index(CustomFootballApiService $sportsApi, TruncgilEconomyService $economyApi)
    {
        $ttl = (int) setting('cache_ttl_home', 300);
        $ttl = $ttl > 0 ? $ttl : 300;
        $expiresAt = now()->addSeconds($ttl);

        $data = cache()->remember('home:page:1', $expiresAt, function () use ($sportsApi, $economyApi) {
            $base = Post::news()->where('status', 'published');

            $manset = (clone $base)
                ->where('type', 'manset')
                ->orderByDesc('published_at')
                ->take(15)
                ->get();

            $topManset = (clone $base)
                ->where('type', 'top_manset')
                ->orderByDesc('published_at')
                ->take(15)
                ->get();

            $topCategories = Category::orderBy('order', 'asc')
                ->orderBy('name', 'asc')
                ->take(4)
                ->get(['id', 'name', 'slug']);

            $breakingNewsPosts = (clone $base)
                ->where('is_breaking', true)
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->take(4)
                ->get();

            if ($breakingNewsPosts->count() < 4) {
                $excludeIds = $breakingNewsPosts->pluck('id')->all();
                $fallbackBreaking = (clone $base)
                    ->when(!empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
                    ->with(['category:id,name,slug'])
                    ->orderByDesc('published_at')
                    ->take(4 - $breakingNewsPosts->count())
                    ->get();
                $breakingNewsPosts = $breakingNewsPosts->concat($fallbackBreaking)->values();
            }

            $featuredCategoryOrder = ['spor', 'ekonomi', 'guncel', 'magazin'];
            $orderedFeatured = Category::query()
                ->whereIn('slug', $featuredCategoryOrder)
                ->get(['id', 'name', 'slug'])
                ->sortBy(fn ($cat) => array_search($cat->slug, $featuredCategoryOrder, true))
                ->values();

            $featuredCategories = $orderedFeatured;
            if ($featuredCategories->count() < 4) {
                $existingIds = $featuredCategories->pluck('id')->all();
                $fallbackFeatured = $topCategories->whereNotIn('id', $existingIds)->take(4 - $featuredCategories->count());
                $featuredCategories = $featuredCategories->concat($fallbackFeatured)->values();
            }

            $featuredPosts = [];
            foreach ($featuredCategories as $cat) {
                $featuredPosts[$cat->id] = (clone $base)
                    ->where('category_id', $cat->id)
                    ->with(['category:id,name,slug'])
                    ->orderByDesc('published_at')
                    ->take(4)
                    ->get();
            }

            $authorArticles = Post::articles()
                ->where('status', 'published')
                ->with(['user:id,name,author_name,avatar,bio'])
                ->orderByDesc('published_at')
                ->take(4)
                ->get();

            $sportsSlug = (string) setting('sports_category_slug', 'spor');
            $economySlug = (string) setting('economy_category_slug', 'ekonomi');
            $sportsSection = $this->buildSectionData($base, $sportsSlug, 8);
            $economySection = $this->buildSectionData($base, $economySlug, 8);
            $sportsStandingsHome = collect($sportsApi->standings())->take(20)->values()->all();
            $economyWidgetHome = $this->buildEconomyWidget($economyApi->snapshot());

            return compact(
                'manset',
                'topManset',
                'breakingNewsPosts',
                'featuredCategories',
                'featuredPosts',
                'authorArticles',
                'sportsSection',
                'economySection',
                'sportsSlug',
                'economySlug',
                'sportsStandingsHome',
                'economyWidgetHome'
            );
        });

        return view('frontend.home', $data + [
            'seoTitle' => setting('site_title'),
            'seoDescription' => setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    private function buildSectionData($baseQuery, string $rootSlug, int $limit = 5): array
    {
        $category = Category::query()
            ->where('slug', $rootSlug)
            ->whereNull('parent_id')
            ->with('children:id,parent_id,name,slug')
            ->first(['id', 'name', 'slug', 'parent_id']);

        if (!$category) {
            return [
                'category' => null,
                'lead' => null,
                'items' => collect(),
            ];
        }

        $scopeIds = collect([$category->id])
            ->merge($category->children->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $posts = (clone $baseQuery)
            ->whereIn('category_id', $scopeIds)
            ->with('category:id,name,slug')
            ->orderByDesc('published_at')
            ->take($limit)
            ->get();

        return [
            'category' => $category,
            'lead' => $posts->first(),
            'items' => $posts->skip(1)->values(),
        ];
    }

    private function buildEconomyWidget(array $snapshot): array
    {
        $pick = function (array $pool, string $symbol) {
            foreach ($pool as $item) {
                if (strtoupper((string) ($item['symbol'] ?? '')) === strtoupper($symbol)) {
                    return $item;
                }
            }
            return null;
        };

        $allCurrencies = is_array($snapshot['allCurrencies'] ?? null) ? $snapshot['allCurrencies'] : [];
        $allGold = is_array($snapshot['allGold'] ?? null) ? $snapshot['allGold'] : [];

        return [
            'updatedAt' => $snapshot['updatedAt'] ?? null,
            'rows' => array_values(array_filter([
                $pick($allGold, 'GRA'),
                $pick($allGold, 'CEYREKALTIN'),
                $pick($allCurrencies, 'USD'),
                $pick($allCurrencies, 'EUR'),
                $pick($allCurrencies, 'GBP'),
            ])),
        ];
    }
}
