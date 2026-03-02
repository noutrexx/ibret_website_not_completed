<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Services\Economy\TruncgilEconomyService;
use App\Services\Sports\CustomFootballApiService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(string $slug, Request $request, CustomFootballApiService $sportsApi, TruncgilEconomyService $economyApi)
    {
        [$category] = $this->loadCategoryAndPosts($slug, $request);

        if ($category->parent_id && $category->parent) {
            return redirect()->to($category->frontend_url, 301);
        }

        return $this->renderCategory($category, $request, $sportsApi, $economyApi);
    }

    public function showChild(string $parentSlug, string $slug, Request $request, CustomFootballApiService $sportsApi, TruncgilEconomyService $economyApi)
    {
        [$category] = $this->loadCategoryAndPosts($slug, $request, $parentSlug);

        return $this->renderCategory($category, $request, $sportsApi, $economyApi);
    }

    public function sportsStandings(string $slug, Request $request, CustomFootballApiService $sportsApi)
    {
        [$category, $posts] = $this->loadCategoryAndPosts($slug, $request);
        $this->ensureSportsSlug($slug);
        $categoryExtras = $this->loadCategoryExtras($category);

        if ($request->boolean('feed')) {
            return response()->json([
                'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
            ]);
        }

        $sportsData = $this->loadSportsData($sportsApi, $request);

        return view('frontend.sports.standings', [
            'category' => $category,
            'posts' => $posts,
            ...$sportsData,
            ...$categoryExtras,
            'seoTitle' => 'Puan Durumu | ' . ($category->page_title ?: $category->name),
            'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    public function sportsFixtures(string $slug, Request $request, CustomFootballApiService $sportsApi)
    {
        [$category, $posts] = $this->loadCategoryAndPosts($slug, $request);
        $this->ensureSportsSlug($slug);
        $categoryExtras = $this->loadCategoryExtras($category);

        if ($request->boolean('feed')) {
            return response()->json([
                'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
            ]);
        }

        $sportsData = $this->loadSportsData($sportsApi, $request);

        return view('frontend.sports.fixtures', [
            'category' => $category,
            'posts' => $posts,
            ...$sportsData,
            ...$categoryExtras,
            'seoTitle' => 'Fikstur | ' . ($category->page_title ?: $category->name),
            'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    public function sportsLive(string $slug, Request $request, CustomFootballApiService $sportsApi)
    {
        [$category, $posts] = $this->loadCategoryAndPosts($slug, $request);
        $this->ensureSportsSlug($slug);

        $sportsData = $this->loadSportsData($sportsApi, $request);

        return view('frontend.sports.live', [
            'category' => $category,
            'posts' => $posts,
            ...$sportsData,
            'seoTitle' => 'Canli Skor | ' . ($category->page_title ?: $category->name),
            'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    public function economyCurrencies(string $slug, Request $request, TruncgilEconomyService $economyApi)
    {
        [$category] = $this->loadCategoryAndPosts($slug, $request);
        $this->ensureEconomySlug($slug);
        $economyData = $this->loadEconomyData($economyApi);
        [$sectionCategory, $posts, $weeklyPopularPosts] = $this->loadEconomySubCategoryData($category, ['doviz', 'döviz'], $request);

        if ($request->boolean('feed')) {
            return response()->json([
                'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
            ]);
        }

        return view('frontend.economy.currencies', [
            'category' => $category,
            'sectionCategory' => $sectionCategory,
            'posts' => $posts,
            ...$economyData,
            'weeklyPopularPosts' => $weeklyPopularPosts,
            'seoTitle' => 'Doviz | ' . ($category->page_title ?: $category->name),
            'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    public function economyGold(string $slug, Request $request, TruncgilEconomyService $economyApi)
    {
        [$category] = $this->loadCategoryAndPosts($slug, $request);
        $this->ensureEconomySlug($slug);
        $economyData = $this->loadEconomyData($economyApi);
        [$sectionCategory, $posts, $weeklyPopularPosts] = $this->loadEconomySubCategoryData($category, ['altin', 'altın'], $request);

        if ($request->boolean('feed')) {
            return response()->json([
                'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
            ]);
        }

        return view('frontend.economy.gold', [
            'category' => $category,
            'sectionCategory' => $sectionCategory,
            'posts' => $posts,
            ...$economyData,
            'weeklyPopularPosts' => $weeklyPopularPosts,
            'seoTitle' => 'Altin | ' . ($category->page_title ?: $category->name),
            'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    public function economyCrypto(string $slug, Request $request, TruncgilEconomyService $economyApi)
    {
        [$category] = $this->loadCategoryAndPosts($slug, $request);
        $this->ensureEconomySlug($slug);
        $economyData = $this->loadEconomyData($economyApi);
        [$sectionCategory, $posts, $weeklyPopularPosts] = $this->loadEconomySubCategoryData($category, ['kripto', 'crypto'], $request);

        if ($request->boolean('feed')) {
            return response()->json([
                'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
            ]);
        }

        return view('frontend.economy.crypto', [
            'category' => $category,
            'sectionCategory' => $sectionCategory,
            'posts' => $posts,
            ...$economyData,
            'weeklyPopularPosts' => $weeklyPopularPosts,
            'seoTitle' => 'Kripto | ' . ($category->page_title ?: $category->name),
            'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    public function economyBorsa(string $slug, Request $request, TruncgilEconomyService $economyApi)
    {
        [$category] = $this->loadCategoryAndPosts($slug, $request);
        $this->ensureEconomySlug($slug);
        $economyData = $this->loadEconomyData($economyApi);
        [$sectionCategory, $posts, $weeklyPopularPosts] = $this->loadEconomySubCategoryData($category, ['borsa', 'hisse'], $request);

        if ($request->boolean('feed')) {
            return response()->json([
                'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
            ]);
        }

        return view('frontend.economy.borsa', [
            'category' => $category,
            'sectionCategory' => $sectionCategory,
            'posts' => $posts,
            ...$economyData,
            'weeklyPopularPosts' => $weeklyPopularPosts,
            'seoTitle' => 'Borsa | ' . ($category->page_title ?: $category->name),
            'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    protected function ensureSportsSlug(string $slug): void
    {
        $sportsSlug = (string) setting('sports_category_slug', 'spor');
        abort_unless($slug === $sportsSlug, 404);
    }

    protected function ensureEconomySlug(string $slug): void
    {
        $economySlug = (string) setting('economy_category_slug', 'ekonomi');
        abort_unless($slug === $economySlug, 404);
    }

    protected function renderCategory(Category $category, Request $request, CustomFootballApiService $sportsApi, TruncgilEconomyService $economyApi)
    {
        [$category, $posts] = $this->loadCategoryAndPosts($category->slug, $request, $category->parent?->slug);

        if ($category->slug === (string) setting('sports_category_slug', 'spor')) {
            $sportsData = $this->loadSportsData($sportsApi, $request);
            $sportsBlocks = $this->loadSportsHomepageBlocks($category);

            return view('frontend.sports-category', [
                'category' => $category,
                'posts' => $posts,
                ...$sportsData,
                ...$sportsBlocks,
                'seoTitle' => $category->page_title ?: ($category->name . ' | ' . setting('site_title')),
                'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
                'seoImage' => setting('seo_og_image'),
            ]);
        }

        if ($category->slug === (string) setting('economy_category_slug', 'ekonomi')) {
            $economyData = $this->loadEconomyData($economyApi);
            $economyBlocks = $this->loadEconomyHomepageBlocks($category);

            if ($request->boolean('feed')) {
                return response()->json([
                    'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                    'hasMore' => $posts->hasMorePages(),
                    'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
                ]);
            }

            return view('frontend.economy-category', [
                'category' => $category,
                'posts' => $posts,
                ...$economyData,
                ...$economyBlocks,
                'seoTitle' => $category->page_title ?: ($category->name . ' | ' . setting('site_title')),
                'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
                'seoImage' => setting('seo_og_image'),
            ]);
        }

        if ($request->boolean('feed')) {
            return response()->json([
                'html' => view('frontend.partials.category-list-items', ['posts' => $posts])->render(),
                'hasMore' => $posts->hasMorePages(),
                'nextPage' => $posts->hasMorePages() ? ($posts->currentPage() + 1) : null,
            ]);
        }

        $categoryExtras = $this->loadCategoryExtras($category);

        return view('frontend.category', [
            'category' => $category,
            'posts' => $posts,
            ...$categoryExtras,
            'seoTitle' => $category->page_title ?: ($category->name . ' | ' . setting('site_title')),
            'seoDescription' => $category->page_description ?: setting('seo_meta_description'),
            'seoImage' => setting('seo_og_image'),
        ]);
    }

    protected function loadCategoryAndPosts(string $slug, Request $request, ?string $parentSlug = null): array
    {
        $category = Category::where('slug', $slug)
            ->with([
                'parent:id,slug',
                'children:id,parent_id,name,slug,order',
            ])
            ->when($parentSlug !== null, function ($q) use ($parentSlug) {
                $q->whereHas('parent', function ($pq) use ($parentSlug) {
                    $pq->where('slug', $parentSlug);
                });
            })
            ->firstOrFail();

        $scopeCategoryIds = $this->categoryScopeIds($category);

        $ttl = (int) setting('cache_ttl_category', 300);
        $ttl = $ttl > 0 ? $ttl : 300;
        $expiresAt = now()->addSeconds($ttl);

        $page = (int) $request->query('page', 1);
        $cacheKey = 'category:' . $slug . ':scope:' . md5(implode(',', $scopeCategoryIds)) . ':page:' . $page;

        $posts = cache()->remember($cacheKey, $expiresAt, function () use ($scopeCategoryIds) {
            return Post::news()
                ->where('status', 'published')
                ->whereIn('category_id', $scopeCategoryIds)
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->paginate(10);
        });

        return [$category, $posts];
    }

    protected function loadCategoryExtras(Category $category): array
    {
        $scopeCategoryIds = $this->categoryScopeIds($category);

        $ttl = (int) setting('cache_ttl_category', 300);
        $ttl = $ttl > 0 ? $ttl : 300;
        $expiresAt = now()->addSeconds($ttl);

        $sliderPosts = cache()->remember('category:' . $category->slug . ':slider:' . md5(implode(',', $scopeCategoryIds)), $expiresAt, function () use ($scopeCategoryIds) {
            return Post::news()
                ->where('status', 'published')
                ->whereIn('category_id', $scopeCategoryIds)
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->limit(15)
                ->get();
        });

        $weeklyPopularPosts = cache()->remember('category:' . $category->slug . ':weekly_popular:' . md5(implode(',', $scopeCategoryIds)), $expiresAt, function () use ($scopeCategoryIds) {
            return Post::news()
                ->where('status', 'published')
                ->whereIn('category_id', $scopeCategoryIds)
                ->where('published_at', '>=', Carbon::now()->subDays(7))
                ->with(['category:id,name,slug'])
                ->orderByDesc('view_count')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();
        });

        return [
            'sliderPosts' => $sliderPosts,
            'weeklyPopularPosts' => $weeklyPopularPosts,
            'subCategories' => $category->children,
        ];
    }

    protected function loadSportsData(CustomFootballApiService $sportsApi, Request $request): array
    {
        $availableLeagues = $sportsApi->leagues();
        $defaultLeague = $sportsApi->defaultLeagueKey();
        $leagueKey = (string) $request->query('league', $defaultLeague);

        $trStandings = $sportsApi->standings($leagueKey);
        $trFixtures = $sportsApi->fixtures($leagueKey);
        $liveMatches = [];

        return [
            'trStandings' => $trStandings,
            'trFixtures' => $trFixtures,
            'availableLeagues' => $availableLeagues,
            'currentLeagueKey' => $leagueKey,
            'currentLeagueName' => $sportsApi->leagueName($leagueKey),
            'liveMatches' => $liveMatches,
            'quota' => ['remaining' => 0, 'used' => 0, 'budget' => 0],
            'apiFootballError' => $sportsApi->lastError($leagueKey),
            'europeStandings' => [],
        ];
    }

    protected function loadSportsHomepageBlocks(Category $category): array
    {
        $scopeCategoryIds = $this->categoryScopeIds($category);

        $ttl = (int) setting('cache_ttl_category', 300);
        $ttl = $ttl > 0 ? $ttl : 300;
        $expiresAt = now()->addSeconds($ttl);

        $sportsTopManset = cache()->remember('category:' . $category->slug . ':sports_top_manset:' . md5(implode(',', $scopeCategoryIds)), $expiresAt, function () use ($scopeCategoryIds) {
            $items = Post::news()
                ->where('status', 'published')
                ->whereIn('category_id', $scopeCategoryIds)
                ->where('type', 'spor_manset')
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->limit(20)
                ->get();

            if ($items->isEmpty()) {
                $items = Post::news()
                    ->where('status', 'published')
                    ->whereIn('category_id', $scopeCategoryIds)
                    ->with(['category:id,name,slug'])
                    ->orderByDesc('published_at')
                    ->limit(20)
                    ->get();
            }

            return $items;
        });

        $sportsLatestPosts = cache()->remember('category:' . $category->slug . ':sports_latest_4:' . md5(implode(',', $scopeCategoryIds)), $expiresAt, function () use ($scopeCategoryIds) {
            return Post::news()
                ->where('status', 'published')
                ->whereIn('category_id', $scopeCategoryIds)
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->limit(4)
                ->get();
        });

        $futbolCategory = $this->findChildCategoryByKeyword($category, 'futbol');
        $basketbolCategory = $this->findChildCategoryByKeyword($category, 'basketbol');
        $voleybolCategory = $this->findChildCategoryByKeyword($category, 'voleybol');
        $tenisCategory = $this->findChildCategoryByKeyword($category, 'tenis');

        $futbolPosts = $this->loadSubCategoryLatestPosts($category->slug, $futbolCategory?->id, 'futbol', $expiresAt);
        $basketbolPosts = $this->loadSubCategoryLatestPosts($category->slug, $basketbolCategory?->id, 'basketbol', $expiresAt);
        $voleybolPosts = $this->loadSubCategoryLatestPosts($category->slug, $voleybolCategory?->id, 'voleybol', $expiresAt);
        $tenisPosts = $this->loadSubCategoryLatestPosts($category->slug, $tenisCategory?->id, 'tenis', $expiresAt);

        return [
            'sportsTopManset' => $sportsTopManset,
            'sportsLatestPosts' => $sportsLatestPosts,
            'futbolCategory' => $futbolCategory,
            'futbolPosts' => $futbolPosts,
            'basketbolCategory' => $basketbolCategory,
            'basketbolPosts' => $basketbolPosts,
            'voleybolCategory' => $voleybolCategory,
            'voleybolPosts' => $voleybolPosts,
            'tenisCategory' => $tenisCategory,
            'tenisPosts' => $tenisPosts,
        ];
    }

    protected function findChildCategoryByKeyword(Category $category, string $keyword): ?Category
    {
        $keyword = mb_strtolower($keyword, 'UTF-8');

        return $category->children->first(function ($child) use ($keyword) {
            $name = mb_strtolower((string) ($child->name ?? ''), 'UTF-8');
            $slug = mb_strtolower((string) ($child->slug ?? ''), 'UTF-8');
            return $slug === $keyword || str_contains($name, $keyword);
        });
    }

    protected function loadSubCategoryLatestPosts(string $parentSlug, ?int $categoryId, string $key, $expiresAt)
    {
        if (!$categoryId) {
            return collect();
        }

        return cache()->remember('category:' . $parentSlug . ':' . $key . '_latest_4:' . $categoryId, $expiresAt, function () use ($categoryId) {
            return Post::news()
                ->where('status', 'published')
                ->where('category_id', $categoryId)
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->limit(4)
                ->get();
        });
    }

    protected function categoryScopeIds(Category $category): array
    {
        if ($category->parent_id) {
            return [$category->id];
        }

        $childrenIds = $category->children->pluck('id')->all();

        return array_values(array_unique(array_merge([$category->id], $childrenIds)));
    }

    protected function loadEconomyData(TruncgilEconomyService $economyApi): array
    {
        $snapshot = $economyApi->snapshot();

        return [
            'economyUpdatedAt' => $snapshot['updatedAt'] ?? null,
            'economyCurrencies' => $snapshot['currencies'] ?? [],
            'economyAllCurrencies' => $snapshot['allCurrencies'] ?? [],
            'economyGold' => $snapshot['gold'] ?? [],
            'economyAllGold' => $snapshot['allGold'] ?? [],
            'economyCrypto' => $snapshot['crypto'] ?? [],
            'economyAllCrypto' => $snapshot['allCrypto'] ?? [],
            'economyMarkets' => $snapshot['markets'] ?? [],
            'economyAllMarkets' => $snapshot['allMarkets'] ?? [],
            'economyError' => $snapshot['error'] ?? null,
        ];
    }

    protected function loadEconomyHomepageBlocks(Category $category): array
    {
        $scopeCategoryIds = $this->categoryScopeIds($category);

        $ttl = (int) setting('cache_ttl_category', 300);
        $ttl = $ttl > 0 ? $ttl : 300;
        $expiresAt = now()->addSeconds($ttl);

        $economyTopManset = cache()->remember('category:' . $category->slug . ':economy_top_manset:' . md5(implode(',', $scopeCategoryIds)), $expiresAt, function () use ($scopeCategoryIds) {
            $items = Post::news()
                ->where('status', 'published')
                ->whereIn('category_id', $scopeCategoryIds)
                ->where('type', 'ekonomi_manset')
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->limit(20)
                ->get();

            if ($items->isEmpty()) {
                $items = Post::news()
                    ->where('status', 'published')
                    ->whereIn('category_id', $scopeCategoryIds)
                    ->whereIn('type', ['top_manset', 'manset'])
                    ->with(['category:id,name,slug'])
                    ->orderByDesc('published_at')
                    ->limit(20)
                    ->get();
            }

            return $items;
        });

        $economyLatestPosts = cache()->remember('category:' . $category->slug . ':economy_latest_4:' . md5(implode(',', $scopeCategoryIds)), $expiresAt, function () use ($scopeCategoryIds) {
            return Post::news()
                ->where('status', 'published')
                ->whereIn('category_id', $scopeCategoryIds)
                ->with(['category:id,name,slug'])
                ->orderByDesc('published_at')
                ->limit(4)
                ->get();
        });

        $currencyCategory = $this->findChildCategoryByKeywords($category, ['doviz', 'döviz']);
        $goldCategory = $this->findChildCategoryByKeywords($category, ['altin', 'altın']);
        $cryptoCategory = $this->findChildCategoryByKeywords($category, ['kripto', 'crypto']);
        $borsaCategory = $this->findChildCategoryByKeywords($category, ['borsa', 'hisse']);

        $currencyPosts = $this->loadSubCategoryLatestPosts($category->slug, $currencyCategory?->id, 'doviz', $expiresAt);
        $goldPosts = $this->loadSubCategoryLatestPosts($category->slug, $goldCategory?->id, 'altin', $expiresAt);
        $cryptoPosts = $this->loadSubCategoryLatestPosts($category->slug, $cryptoCategory?->id, 'kripto', $expiresAt);
        $borsaPosts = $this->loadSubCategoryLatestPosts($category->slug, $borsaCategory?->id, 'borsa', $expiresAt);

        return [
            'economyTopManset' => $economyTopManset,
            'economyLatestPosts' => $economyLatestPosts,
            'currencyCategory' => $currencyCategory,
            'currencyPosts' => $currencyPosts,
            'goldCategory' => $goldCategory,
            'goldPosts' => $goldPosts,
            'cryptoCategory' => $cryptoCategory,
            'cryptoPosts' => $cryptoPosts,
            'borsaCategory' => $borsaCategory,
            'borsaPosts' => $borsaPosts,
        ];
    }

    protected function loadEconomySubCategoryData(Category $economyCategory, array $keywords, Request $request): array
    {
        $sectionCategory = $this->findChildCategoryByKeywords($economyCategory, $keywords);

        if (!$sectionCategory) {
            [$fallbackCategory, $fallbackPosts] = $this->loadCategoryAndPosts($economyCategory->slug, $request);
            $fallbackExtras = $this->loadCategoryExtras($fallbackCategory);
            return [null, $fallbackPosts, $fallbackExtras['weeklyPopularPosts'] ?? collect()];
        }

        [$subCategory, $posts] = $this->loadCategoryAndPosts($sectionCategory->slug, $request, $economyCategory->slug);
        $subCategoryExtras = $this->loadCategoryExtras($subCategory);

        return [$sectionCategory, $posts, $subCategoryExtras['weeklyPopularPosts'] ?? collect()];
    }

    protected function findChildCategoryByKeywords(Category $category, array $keywords): ?Category
    {
        $normalizedKeywords = array_map(fn ($k) => mb_strtolower((string) $k, 'UTF-8'), $keywords);

        return $category->children->first(function ($child) use ($normalizedKeywords) {
            $name = mb_strtolower((string) ($child->name ?? ''), 'UTF-8');
            $slug = mb_strtolower((string) ($child->slug ?? ''), 'UTF-8');

            foreach ($normalizedKeywords as $keyword) {
                if ($slug === $keyword || str_contains($slug, $keyword) || str_contains($name, $keyword)) {
                    return true;
                }
            }

            return false;
        });
    }
}
