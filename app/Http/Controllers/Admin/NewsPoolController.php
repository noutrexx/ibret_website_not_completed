<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\NewsPoolItem;
use App\Models\Post;
use App\Models\RssSource;
use App\Services\Rss\AiNewsRewriteService;
use App\Services\Rss\RssIngestService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NewsPoolController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'draft');
        $allowedStatuses = ['draft', 'approved', 'rejected', 'published', 'all'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'draft';
        }

        $sourceId = $request->filled('source_id') ? (int) $request->query('source_id') : null;
        $dateRange = (string) $request->query('date_range', '');
        $search = trim((string) $request->query('q', ''));

        $itemsQuery = NewsPoolItem::query()
            ->with(['source:id,name', 'category:id,name', 'publishedPost:id,title'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($sourceId, fn ($q) => $q->where('rss_source_id', $sourceId))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('title', 'like', '%' . $search . '%')
                        ->orWhere('ai_title', 'like', '%' . $search . '%')
                        ->orWhere('source_url', 'like', '%' . $search . '%');
                });
            });

        $this->applyDateRangeFilter($itemsQuery, $dateRange);

        $items = $itemsQuery->latest('id')
            ->paginate(30)
            ->withQueryString();

        $pageSlugs = $items->getCollection()
            ->pluck('slug')
            ->filter()
            ->unique()
            ->values();

        $duplicateSlugCounts = $pageSlugs->isEmpty()
            ? collect()
            : NewsPoolItem::query()
                ->selectRaw('slug, COUNT(*) as aggregate_count')
                ->whereIn('slug', $pageSlugs)
                ->groupBy('slug')
                ->pluck('aggregate_count', 'slug');

        $existingPostSlugs = $pageSlugs->isEmpty()
            ? collect()
            : Post::query()
                ->whereIn('slug', $pageSlugs)
                ->pluck('slug')
                ->flip();

        $stats = [
            'draft' => NewsPoolItem::where('status', 'draft')->count(),
            'approved' => NewsPoolItem::where('status', 'approved')->count(),
            'rejected' => NewsPoolItem::where('status', 'rejected')->count(),
            'published' => NewsPoolItem::where('status', 'published')->count(),
        ];

        $sources = RssSource::orderBy('name')->get(['id', 'name', 'is_active', 'last_fetched_at', 'last_error']);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.news-pool.index', compact(
            'items',
            'stats',
            'sources',
            'categories',
            'status',
            'sourceId',
            'dateRange',
            'search',
            'duplicateSlugCounts',
            'existingPostSlugs'
        ));
    }

    public function ingest(Request $request, RssIngestService $service)
    {
        $sourceId = $request->filled('source_id') ? (int) $request->input('source_id') : null;
        $limit = max(1, (int) $request->input('limit', 40));
        $result = $service->ingest($sourceId, $limit);

        $message = "RSS cekimi tamamlandi. Kaynak: {$result['sources']}, Yeni: {$result['inserted']}, Tekrar: {$result['duplicates']}";
        if (!empty($result['errors'])) {
            $message .= ' | Hata: ' . implode(' ; ', $result['errors']);
        }

        return back()->with('success', $message);
    }

    public function edit(int $id)
    {
        $item = NewsPoolItem::with(['source:id,name', 'category:id,name'])->findOrFail($id);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.news-pool.edit', compact('item', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $item = NewsPoolItem::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'ai_title' => 'nullable|string|max:255',
            'ai_summary' => 'nullable|string',
            'ai_content' => 'nullable|string',
            'ai_keywords' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,approved,rejected,published',
        ]);

        $item->update($data);

        return back()->with('success', 'Haber havuzu kaydi guncellendi.');
    }

    public function approve(Request $request, int $id)
    {
        $item = NewsPoolItem::findOrFail($id);

        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'nullable|in:normal,manset,surmanset,top_manset,spor_manset,ekonomi_manset,gizli',
            'is_breaking' => 'nullable|boolean',
        ]);

        $categoryId = $request->integer('category_id') ?: $item->category_id ?: $item->source?->default_category_id;
        if (!$categoryId) {
            return back()->with('error', 'Yayina almak icin kategori secmelisiniz.');
        }

        $resolvedTitle = $item->ai_title ?: $item->title;
        $resolvedSummary = $item->ai_summary ?: $item->summary;
        $resolvedContent = $item->ai_content ?: $item->content;
        $resolvedKeywords = $item->ai_keywords ?: null;
        $itemMeta = is_array($item->meta) ? $item->meta : [];
        $aiSeoMeta = is_array($itemMeta['ai_seo'] ?? null) ? $itemMeta['ai_seo'] : [];
        $slugCandidate = trim((string) ($aiSeoMeta['slug_suggestion'] ?? ''));
        $slugTitle = $slugCandidate !== '' ? $slugCandidate : $resolvedTitle;

        $post = new Post();
        $post->category_id = $categoryId;
        $post->user_id = auth()->id();
        $post->title = $resolvedTitle;
        $post->slug = Post::uniqueSlug($slugTitle);
        $post->summary = $resolvedSummary ?: Str::limit(strip_tags((string) $resolvedContent), 220);
        $post->seo_title = Str::limit((string) ($aiSeoMeta['seo_title'] ?? $resolvedTitle), 190, '');
        $post->seo_description = Str::limit((string) ($aiSeoMeta['meta_description'] ?? $post->summary), 220, '');
        $post->focus_keywords = $this->normalizeKeywordList((string) ($aiSeoMeta['focus_keywords'] ?? $resolvedKeywords), 5);
        $post->seo_entities = $this->normalizeEntities($aiSeoMeta['entities'] ?? []);

        $content = $resolvedContent ?: e($resolvedSummary ?: $resolvedTitle);
        if ($item->source_url) {
            $content .= '<p><strong>Kaynak:</strong> <a href="' . e($item->source_url) . '" rel="nofollow noopener" target="_blank">' . e($item->source->name ?? 'Orijinal haber') . '</a></p>';
        }

        $post->content = $content;
        $post->type = $request->input('type', 'normal');
        $post->is_breaking = (bool) $request->boolean('is_breaking');
        $post->content_kind = 'news';
        $post->status = 'published';
        $post->published_at = $item->source_published_at ?: now();
        $post->tags = Post::normalizeTags($resolvedKeywords);
        $post->save();

        $schemaPayload = $this->buildNewsSchemaPayload($post, $aiSeoMeta);
        if (!empty($schemaPayload)) {
            $post->schema_jsonld = json_encode($schemaPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $post->save();
        }

        $item->update([
            'status' => 'published',
            'category_id' => $categoryId,
            'published_post_id' => $post->id,
        ]);

        return back()->with('success', 'Haber yayina alindi.');
    }

    public function aiRewrite(int $id, AiNewsRewriteService $service)
    {
        $item = NewsPoolItem::findOrFail($id);
        $item->update(['ai_status' => 'processing', 'ai_error' => null]);

        $result = $service->rewrite($item);

        if (!($result['ok'] ?? false)) {
            $item->update([
                'ai_status' => 'failed',
                'ai_error' => mb_substr((string) ($result['error'] ?? 'Bilinmeyen hata'), 0, 2000),
            ]);
            return back()->with('error', 'AI duzenleme basarisiz: ' . ($result['error'] ?? 'Bilinmeyen hata'));
        }

        if (!empty($result['cached'])) {
            return back()->with('success', 'AI duzenleme atlandi: mevcut sonuc zaten guncel.');
        }

        $provider = strtoupper((string) ($result['provider'] ?? 'AI'));
        return back()->with('success', 'AI duzenleme tamamlandi. Saglayici: ' . $provider);
    }

    public function reject(int $id)
    {
        $item = NewsPoolItem::findOrFail($id);
        $item->update(['status' => 'rejected']);

        return back()->with('success', 'Haber reddedildi.');
    }

    public function clearDrafts()
    {
        $deleted = NewsPoolItem::query()
            ->where('status', 'draft')
            ->delete();

        return back()->with('success', "Taslaklar temizlendi. Silinen kayit: {$deleted}");
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:delete,reject,mark_draft,mark_approved,set_category',
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'integer|exists:news_pool_items,id',
            'category_id' => 'nullable|integer|exists:categories,id',
        ], [
            'selected_ids.required' => 'Toplu islem icin en az bir haber secin.',
        ]);

        $ids = collect($data['selected_ids'])->map(fn ($v) => (int) $v)->unique()->values()->all();
        $action = (string) $data['action'];

        $query = NewsPoolItem::query()->whereIn('id', $ids);
        $affected = 0;

        try {
            DB::transaction(function () use ($action, $data, $query, &$affected) {
                switch ($action) {
                    case 'delete':
                        $affected = (clone $query)->delete();
                        break;
                    case 'reject':
                        $affected = (clone $query)
                            ->where('status', '!=', 'published')
                            ->update(['status' => 'rejected', 'updated_at' => now()]);
                        break;
                    case 'mark_draft':
                        $affected = (clone $query)
                            ->where('status', '!=', 'published')
                            ->update(['status' => 'draft', 'updated_at' => now()]);
                        break;
                    case 'mark_approved':
                        $affected = (clone $query)
                            ->where('status', '!=', 'published')
                            ->update(['status' => 'approved', 'updated_at' => now()]);
                        break;
                    case 'set_category':
                        $categoryId = (int) ($data['category_id'] ?? 0);
                        if ($categoryId <= 0) {
                            throw new \InvalidArgumentException('Kategori secilmedi.');
                        }
                        $affected = (clone $query)->update([
                            'category_id' => $categoryId,
                            'updated_at' => now(),
                        ]);
                        break;
                }
            });
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $label = match ($action) {
            'delete' => 'silindi',
            'reject' => 'reddedildi',
            'mark_draft' => 'taslak yapildi',
            'mark_approved' => 'onayli yapildi',
            'set_category' => 'kategori atandi',
            default => 'guncellendi',
        };

        return back()->with('success', "Toplu islem tamamlandi. {$affected} kayit {$label}.");
    }

    private function applyDateRangeFilter($query, string $dateRange): void
    {
        $column = 'source_published_at';

        match ($dateRange) {
            'today' => $query->whereDate($column, today()),
            '24h' => $query->where($column, '>=', now()->subDay()),
            '7d' => $query->where($column, '>=', now()->subDays(7)),
            '30d' => $query->where($column, '>=', now()->subDays(30)),
            default => null,
        };
    }

    private function normalizeKeywordList(string $raw, int $max = 8): ?string
    {
        $parts = collect(preg_split('/[,;]+/u', $raw) ?: [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->map(fn ($v) => Str::slug($v))
            ->filter()
            ->unique()
            ->take($max)
            ->values()
            ->all();

        return empty($parts) ? null : implode(',', $parts);
    }

    private function normalizeEntities(mixed $entities): array
    {
        if (is_string($entities)) {
            $entities = preg_split('/[,;]+/u', $entities) ?: [];
        }

        if (!is_array($entities)) {
            return [];
        }

        return collect($entities)
            ->map(fn ($v) => trim(strip_tags((string) $v)))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->take(12)
            ->values()
            ->all();
    }

    private function buildNewsSchemaPayload(Post $post, array $aiSeoMeta = []): array
    {
        $custom = $aiSeoMeta['news_schema'] ?? null;
        if (is_string($custom)) {
            $decoded = json_decode($custom, true);
            if (is_array($decoded)) {
                $custom = $decoded;
            }
        }

        if (is_array($custom) && !empty($custom)) {
            $custom['@context'] = 'https://schema.org';
            $custom['@type'] = 'NewsArticle';
            $custom['headline'] = Str::limit((string) ($custom['headline'] ?? $post->title), 170, '');
            $custom['description'] = Str::limit((string) ($custom['description'] ?? ($post->seo_description ?: $post->summary)), 220, '');
            $custom['datePublished'] = ($post->published_at ?: $post->created_at)?->toAtomString();
            $custom['dateModified'] = ($post->updated_at ?: $post->created_at)?->toAtomString();
            $custom['mainEntityOfPage'] = $post->frontend_url;
            $custom['keywords'] = $post->focus_keywords ?: $post->tags;

            return $custom;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => $post->frontend_url,
            'headline' => Str::limit($post->title, 170, ''),
            'description' => Str::limit((string) ($post->seo_description ?: $post->summary ?: strip_tags((string) $post->content)), 220, ''),
            'datePublished' => ($post->published_at ?: $post->created_at)?->toAtomString(),
            'dateModified' => ($post->updated_at ?: $post->created_at)?->toAtomString(),
            'keywords' => $post->focus_keywords ?: $post->tags,
            'articleSection' => $post->category?->name,
            'author' => [
                '@type' => 'Person',
                'name' => $post->user?->name ?: 'Editor',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => setting('seo_publisher_name', setting('site_title')),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => setting('seo_publisher_logo', setting('seo_og_image')),
                ],
            ],
        ];
    }
}
