<?php

namespace App\Services\Newspapers;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewspaperService
{
    private const BASE_URL = 'https://www.gazeteoku.com/gazeteler';

    public function getFrontPages(?string $dateInput = null): array
    {
        $dateMode = $this->normalizeDateInput($dateInput);
        $cacheKey = 'newspapers:front-pages:v2:' . md5(json_encode($dateMode));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($dateMode) {
            try {
                return $this->fetchNational($dateMode);
            } catch (\Throwable $e) {
                return [
                    'items' => [],
                    'error' => $e->getMessage(),
                    'selected_date' => $dateMode['value'],
                    'selected_label' => $dateMode['label'],
                    'source' => 'gazeteoku',
                ];
            }
        });
    }

    private function fetchNational(array $dateMode): array
    {
        $url = $this->buildNationalUrl($dateMode);

        $request = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122 Safari/537.36',
            'Accept-Language' => 'tr-TR,tr;q=0.9,en;q=0.8',
            'Referer' => 'https://www.gazeteoku.com/',
        ])->timeout(20)
            ->retry(1, 300);

        try {
            $response = $request->get($url);
        } catch (ConnectionException $e) {
            $message = $e->getMessage();

            // Windows/PHP CA bundle sorunlarında SSL verify kapatılmış fallback ile tekrar dene.
            if (str_contains($message, 'cURL error 60')) {
                $response = $request->withoutVerifying()->get($url);
            } else {
                throw $e;
            }
        }

        if (!$response->successful()) {
            throw new \RuntimeException('Gazete verisi alinamadi (HTTP ' . $response->status() . ').');
        }

        $html = $response->body();
        if (stripos($html, 'Attention Required! | Cloudflare') !== false || stripos($html, 'Sorry, you have been blocked') !== false) {
            throw new \RuntimeException('Kaynak site anti-bot korumasi nedeniyle sunucudan engelliyor. Hostta tekrar denenmeli.');
        }

        $items = $this->parseGazeteOkuHtml($html);

        if (empty($items)) {
            throw new \RuntimeException('Gazete listesi parse edilemedi.');
        }

        return [
            'items' => $items,
            'error' => null,
            'selected_date' => $dateMode['value'],
            'selected_label' => $dateMode['label'],
            'source' => 'gazeteoku',
        ];
    }

    private function parseGazeteOkuHtml(string $html): array
    {
        preg_match_all('/data-src="([^"]+)"\s+data-srcset/i', $html, $images);
        preg_match_all('/<strong>(.*?)<\/strong>/si', $html, $names);
        preg_match_all('/<small>(.*?)<\/small>/si', $html, $dates);

        $imageList = array_values(array_filter($images[1] ?? []));
        $nameList = array_values($names[1] ?? []);
        $dateList = array_values($dates[1] ?? []);

        $count = min(count($imageList), count($nameList));
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            if ($i > 28) {
                break;
            }

            $name = trim(html_entity_decode(strip_tags($nameList[$i] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $image = $this->normalizeGazeteImageUrl(trim($imageList[$i] ?? ''));
            $date = trim(html_entity_decode(strip_tags($dateList[$i] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($name === '' || $image === '') {
                continue;
            }

            $items[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'image_url' => $image,
                'date_text' => $date,
            ];
        }

        return $items;
    }

    private function normalizeGazeteImageUrl(string $url): string
    {
        if ($url === '') {
            return $url;
        }

        // Thumbnail pattern example:
        // https://i.gazeteoku.com/3/230/336/storage/files/images/...jpg
        // Full-size pattern:
        // https://i.gazeteoku.com/storage/files/images/...jpg
        return (string) preg_replace(
            '#^https?://i\.gazeteoku\.com/\d+/\d+/\d+/(storage/files/images/.+)$#i',
            'https://i.gazeteoku.com/$1',
            $url
        );
    }

    private function normalizeDateInput(?string $dateInput): array
    {
        $dateInput = trim((string) $dateInput);

        if ($dateInput === '' || $dateInput === 'today' || $dateInput === 'bugun') {
            return [
                'mode' => 'today',
                'value' => now()->toDateString(),
                'label' => 'Bugun',
            ];
        }

        if (in_array($dateInput, ['dun', 'yesterday'], true)) {
            return [
                'mode' => 'yesterday',
                'value' => now()->subDay()->toDateString(),
                'label' => 'Dun',
            ];
        }

        try {
            $date = Carbon::parse($dateInput);

            return [
                'mode' => 'custom',
                'value' => $date->toDateString(),
                'label' => $date->format('d.m.Y'),
            ];
        } catch (\Throwable $e) {
            return [
                'mode' => 'today',
                'value' => now()->toDateString(),
                'label' => 'Bugun',
            ];
        }
    }

    private function buildNationalUrl(array $dateMode): string
    {
        if (($dateMode['mode'] ?? 'today') === 'today') {
            return self::BASE_URL;
        }

        $date = Carbon::parse($dateMode['value']);
        $slug = Str::slug($date->day . ' ' . $date->toDateString());

        return self::BASE_URL . '?date=' . $slug;
    }
}
