<?php

namespace App\Services\Sports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiFootballService
{
    protected string $lastErrorCacheKey = 'sports:af:last_error';

    public function configured(): bool
    {
        return (string) setting('sports_api_football_key', '') !== '';
    }

    public function quotaStatus(): array
    {
        $today = Carbon::now('Europe/Istanbul')->format('Y-m-d');
        $budget = (int) setting('sports_api_football_daily_budget', 95);
        $budget = $budget > 0 ? $budget : 95;
        $used = (int) Cache::get($this->quotaKey($today), 0);

        return [
            'date' => $today,
            'budget' => $budget,
            'used' => $used,
            'remaining' => max(0, $budget - $used),
        ];
    }

    public function standings(int $leagueId, int $season): array
    {
        $minutes = (int) setting('cache_ttl_sports_standings', 360);
        $minutes = $minutes > 0 ? $minutes : 360;
        $seasons = $this->candidateSeasons($season);

        foreach ($seasons as $trySeason) {
            $key = "sports:af:standings:{$leagueId}:{$trySeason}";
            $data = Cache::remember($key, now()->addMinutes($minutes), function () use ($leagueId, $trySeason) {
                $json = $this->request('/standings', [
                    'league' => $leagueId,
                    'season' => $trySeason,
                ]);

                return $json['response'][0]['league']['standings'][0] ?? [];
            });

            if (!empty($data)) {
                return $data;
            }
        }

        return [];
    }

    public function fixtures(int $leagueId, int $season, string $fromDate, string $toDate): array
    {
        $minutes = (int) setting('cache_ttl_sports_fixtures', 120);
        $minutes = $minutes > 0 ? $minutes : 120;
        $seasons = $this->candidateSeasons($season);

        foreach ($seasons as $trySeason) {
            $ranges = [
                [$fromDate, $toDate],
            ];

            $fromYear = (int) substr($fromDate, 0, 4);
            if ($fromYear !== $trySeason) {
                $ranges[] = [$trySeason . '-01-01', $trySeason . '-12-31'];
            }

            foreach ($ranges as [$rangeFrom, $rangeTo]) {
                $key = "sports:af:fixtures:{$leagueId}:{$trySeason}:{$rangeFrom}:{$rangeTo}";
                $data = Cache::remember($key, now()->addMinutes($minutes), function () use ($leagueId, $trySeason, $rangeFrom, $rangeTo) {
                    $json = $this->request('/fixtures', [
                        'league' => $leagueId,
                        'season' => $trySeason,
                        'from' => $rangeFrom,
                        'to' => $rangeTo,
                    ]);

                    return $json['response'] ?? [];
                });

                if (!empty($data)) {
                    return $data;
                }
            }
        }

        return [];
    }

    public function liveBigThree(int $leagueId, int $season): array
    {
        $today = Carbon::now('Europe/Istanbul')->toDateString();
        $todayFixtures = $this->fixtures($leagueId, $season, $today, $today);

        $bigTeamIds = collect(explode(',', (string) setting('sports_api_big_three_team_ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->values()
            ->all();

        $hasBigThreeToday = collect($todayFixtures)->contains(function ($row) use ($bigTeamIds) {
            $homeId = (int) ($row['teams']['home']['id'] ?? 0);
            $awayId = (int) ($row['teams']['away']['id'] ?? 0);

            if (!empty($bigTeamIds) && (in_array($homeId, $bigTeamIds, true) || in_array($awayId, $bigTeamIds, true))) {
                return true;
            }

            return $this->isBigThreeByName(
                (string) ($row['teams']['home']['name'] ?? ''),
                (string) ($row['teams']['away']['name'] ?? '')
            );
        });

        if (!$hasBigThreeToday) {
            return [];
        }

        $minutes = (int) setting('cache_ttl_sports_live', 5);
        $minutes = $minutes > 0 ? $minutes : 5;

        $key = "sports:af:live:{$leagueId}";

        return Cache::remember($key, now()->addMinutes($minutes), function () use ($leagueId, $bigTeamIds) {
            $json = $this->request('/fixtures', [
                'league' => $leagueId,
                'live' => 'all',
            ]);

            $rows = collect($json['response'] ?? []);

            return $rows->filter(function ($row) use ($bigTeamIds) {
                $homeId = (int) ($row['teams']['home']['id'] ?? 0);
                $awayId = (int) ($row['teams']['away']['id'] ?? 0);

                if (!empty($bigTeamIds) && (in_array($homeId, $bigTeamIds, true) || in_array($awayId, $bigTeamIds, true))) {
                    return true;
                }

                return $this->isBigThreeByName(
                    (string) ($row['teams']['home']['name'] ?? ''),
                    (string) ($row['teams']['away']['name'] ?? '')
                );
            })->values()->all();
        });
    }

    protected function request(string $path, array $query): array
    {
        Cache::forget($this->lastErrorCacheKey);

        if (!$this->configured()) {
            Cache::put($this->lastErrorCacheKey, 'API-Football key bos.', now()->addMinutes(30));
            return [];
        }

        $status = $this->quotaStatus();
        if ($status['used'] >= $status['budget']) {
            Cache::put($this->lastErrorCacheKey, 'API-Football gunluk kota dolu.', now()->addMinutes(30));
            return [];
        }

        $today = $status['date'];
        Cache::add($this->quotaKey($today), 0, Carbon::tomorrow('Europe/Istanbul'));
        Cache::increment($this->quotaKey($today));

        $baseUrl = rtrim((string) setting('sports_api_football_base_url', 'https://v3.football.api-sports.io'), '/');
        $key = (string) setting('sports_api_football_key', '');
        $headers = $this->buildHeaders($baseUrl, $key);

        try {
            $client = Http::timeout(12)
                ->acceptJson()
                ->withHeaders($headers);

            if ($this->shouldDisableSslVerify()) {
                $client = $client->withoutVerifying();
            }

            $res = $client->get($baseUrl . $path, $query);
        } catch (\Throwable $e) {
            // Local Windows environments may fail CA chain resolution.
            // Retry once without SSL verification when cURL error 60 occurs.
            if (str_contains($e->getMessage(), 'cURL error 60')) {
                try {
                    $res = Http::timeout(12)
                        ->acceptJson()
                        ->withHeaders($headers)
                        ->withoutVerifying()
                        ->get($baseUrl . $path, $query);
                } catch (\Throwable $e2) {
                    Cache::put($this->lastErrorCacheKey, 'Baglanti hatasi: ' . $e2->getMessage(), now()->addMinutes(30));
                    return [];
                }
            } else {
                Cache::put($this->lastErrorCacheKey, 'Baglanti hatasi: ' . $e->getMessage(), now()->addMinutes(30));
                return [];
            }
        }

        if (!isset($res)) {
            Cache::put($this->lastErrorCacheKey, 'Baglanti hatasi: istek sonucu alinamadi.', now()->addMinutes(30));
            return [];
        }

        if (!$res->successful()) {
            Cache::put($this->lastErrorCacheKey, 'HTTP ' . $res->status() . ' - ' . mb_substr($res->body(), 0, 180), now()->addMinutes(30));
            return [];
        }

        $json = $res->json();
        if (!is_array($json)) {
            Cache::put($this->lastErrorCacheKey, 'API cevabi parse edilemedi.', now()->addMinutes(30));
            return [];
        }

        if (!empty($json['errors']) && is_array($json['errors'])) {
            $first = collect($json['errors'])->first();
            $message = is_string($first) ? $first : json_encode($json['errors'], JSON_UNESCAPED_UNICODE);
            Cache::put($this->lastErrorCacheKey, 'API hatasi: ' . $message, now()->addMinutes(30));
            return [];
        }

        return $json;
    }

    protected function quotaKey(string $date): string
    {
        return 'quota:api_football:' . $date;
    }

    protected function isBigThreeByName(string $homeName, string $awayName): bool
    {
        $home = $this->normalizeTeamName($homeName);
        $away = $this->normalizeTeamName($awayName);

        return str_contains($home, 'galatasaray')
            || str_contains($home, 'fenerbah')
            || str_contains($home, 'besiktas')
            || str_contains($away, 'galatasaray')
            || str_contains($away, 'fenerbah')
            || str_contains($away, 'besiktas');
    }

    protected function normalizeTeamName(string $name): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        if ($ascii === false) {
            $ascii = $name;
        }

        return strtolower($ascii);
    }

    protected function candidateSeasons(int $season): array
    {
        $allowFallback = (string) setting('sports_api_allow_old_season_fallback', '0') === '1';
        if (!$allowFallback) {
            return [$season];
        }

        $current = (int) Carbon::now('Europe/Istanbul')->format('Y');
        return collect([$season, $current, $current - 1, $current - 2, $current - 3, $current - 4])
            ->filter(fn ($v) => $v > 2000)
            ->unique()
            ->values()
            ->all();
    }

    protected function buildHeaders(string $baseUrl, string $key): array
    {
        if (str_contains($baseUrl, 'rapidapi.com')) {
            return [
                'x-rapidapi-key' => $key,
                'x-rapidapi-host' => (string) setting('sports_api_football_host', 'api-football-v1.p.rapidapi.com'),
            ];
        }

        return ['x-apisports-key' => $key];
    }

    protected function shouldDisableSslVerify(): bool
    {
        return (string) setting('sports_api_verify_ssl', '0') !== '1';
    }

    public function lastError(): ?string
    {
        $v = Cache::get($this->lastErrorCacheKey);
        return is_string($v) && $v !== '' ? $v : null;
    }
}
