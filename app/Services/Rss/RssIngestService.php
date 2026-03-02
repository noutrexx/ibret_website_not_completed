<?php

namespace App\Services\Rss;

use App\Models\NewsPoolItem;
use App\Models\RssSource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RssIngestService
{
    public function ingest(?int $sourceId = null, int $perSourceLimit = 40): array
    {
        $sources = RssSource::query()
            ->when($sourceId, fn ($q) => $q->where('id', $sourceId))
            ->where('is_active', true)
            ->get();

        $result = [
            'sources' => 0,
            'inserted' => 0,
            'duplicates' => 0,
            'errors' => [],
        ];

        foreach ($sources as $source) {
            $result['sources']++;
            try {
                [$inserted, $duplicates] = $this->ingestSource($source, $perSourceLimit);
                $result['inserted'] += $inserted;
                $result['duplicates'] += $duplicates;

                $source->update([
                    'last_fetched_at' => now(),
                    'last_error' => null,
                ]);
            } catch (\Throwable $e) {
                $source->update([
                    'last_fetched_at' => now(),
                    'last_error' => mb_substr($e->getMessage(), 0, 2000),
                ]);
                $result['errors'][] = $source->name . ': ' . $e->getMessage();
            }
        }

        return $result;
    }

    private function ingestSource(RssSource $source, int $limit): array
    {
        $xmlRaw = $this->fetchFeed($source->feed_url);
        $items = $this->parseItems($xmlRaw);

        $inserted = 0;
        $duplicates = 0;

        foreach (array_slice($items, 0, $limit) as $item) {
            $fingerprint = $this->fingerprint($item['guid'], $item['link'], $item['title']);

            if (NewsPoolItem::where('fingerprint', $fingerprint)->exists()) {
                $duplicates++;
                continue;
            }

            NewsPoolItem::create([
                'rss_source_id' => $source->id,
                'category_id' => $source->default_category_id,
                'raw_title' => $item['title'],
                'raw_summary' => $item['summary'],
                'raw_content' => $item['content'],
                'title' => $item['title'],
                'slug' => Str::slug($item['title']) ?: Str::random(12),
                'summary' => $item['summary'],
                'content' => $item['content'],
                'ai_status' => 'pending',
                'image_url' => $item['image_url'],
                'source_url' => $item['link'],
                'source_guid' => $item['guid'],
                'fingerprint' => $fingerprint,
                'source_published_at' => $item['published_at'],
                'status' => 'draft',
                'meta' => [
                    'source_name' => $source->name,
                ],
            ]);

            $inserted++;
        }

        return [$inserted, $duplicates];
    }

    private function fetchFeed(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 20,
                'ignore_errors' => true,
                'user_agent' => 'IbretNewsBot/1.0 (+https://localhost)',
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false || trim($raw) === '') {
            throw new \RuntimeException('RSS çekilemedi');
        }

        return $raw;
    }

    private function parseItems(string $xmlRaw): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlRaw, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) {
            throw new \RuntimeException('RSS parse edilemedi');
        }

        $nodes = [];
        if (isset($xml->channel->item)) {
            $nodes = $xml->channel->item;
        } elseif (isset($xml->entry)) {
            $nodes = $xml->entry;
        }

        $items = [];
        foreach ($nodes as $node) {
            $children = $node->children('content', true);
            $media = $node->children('media', true);
            $atomNs = $node->children('http://www.w3.org/2005/Atom');

            $title = trim((string) ($node->title ?? ''));
            $link = trim((string) ($node->link ?? ''));
            if ($link === '' && isset($node->link['href'])) {
                $link = trim((string) $node->link['href']);
            }
            if ($link === '' && isset($atomNs->link['href'])) {
                $link = trim((string) $atomNs->link['href']);
            }
            $guid = trim((string) ($node->guid ?? ''));
            $description = trim((string) ($node->description ?? ''));
            $encoded = trim((string) ($children->encoded ?? ''));
            $atomSummary = trim((string) ($node->summary ?? $atomNs->summary ?? ''));
            $atomContent = trim((string) ($node->content ?? $atomNs->content ?? ''));

            $summaryRaw = $description !== '' ? $description : $atomSummary;
            $summary = trim(strip_tags($summaryRaw));

            $content = trim($encoded !== '' ? $encoded : ($description !== '' ? $description : $atomContent));
            if ($content === '') {
                $content = $summary;
            }

            $pubDate = trim((string) ($node->pubDate ?? $node->published ?? $node->updated ?? $atomNs->published ?? $atomNs->updated ?? ''));
            $imageUrl = '';

            if (isset($media->content)) {
                $attrs = $media->content->attributes();
                if (isset($attrs['url'])) {
                    $imageUrl = (string) $attrs['url'];
                }
            }

            if ($imageUrl === '' && isset($node->enclosure)) {
                $attrs = $node->enclosure->attributes();
                if (isset($attrs['url'])) {
                    $imageUrl = (string) $attrs['url'];
                }
            }

            // Atom/RSS content icindeki ilk gorseli yakala (ozellikle NTV feedleri icin)
            if ($imageUrl === '' && preg_match('/<img[^>]+src=["\\\']([^"\\\']+)["\\\']/i', $content, $m)) {
                $imageUrl = trim((string) ($m[1] ?? ''));
            }

            if ($title === '' || ($guid === '' && $link === '')) {
                continue;
            }

            if ($guid === '') {
                $guid = $link;
            }

            $publishedAt = null;
            if ($pubDate !== '') {
                try {
                    $publishedAt = Carbon::parse($pubDate);
                } catch (\Throwable $e) {
                    $publishedAt = null;
                }
            }

            $items[] = [
                'title' => $title,
                'link' => $link,
                'guid' => $guid,
                'summary' => mb_substr($summary, 0, 500),
                'content' => $content,
                'image_url' => $imageUrl ?: null,
                'published_at' => $publishedAt,
            ];
        }

        return $items;
    }

    private function fingerprint(string $guid, string $link, string $title): string
    {
        return hash('sha256', trim($guid . '|' . $link . '|' . Str::lower($title)));
    }
}
