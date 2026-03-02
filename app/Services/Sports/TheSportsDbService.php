<?php

namespace App\Services\Sports;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TheSportsDbService
{
    protected string $lastErrorCacheKey = 'sports:tsdb:last_error';

    public function configured(): bool
    {
        return (string) setting('sports_thesportsdb_key', '') !== '';
    }

    public function standings(string $leagueId, ?string $season = null): array
    {
        $minutes = (int) setting('cache_ttl_sports_standings', 360);
        $minutes = $minutes > 0 ? $minutes : 360;
        $season = $season ?: (string) setting('sports_thesportsdb_season', '');
        $seasons = $this->candidateSeasons($season);

        foreach ($seasons as $trySeason) {
            $cacheKey = "sports:tsdb:standings:{$leagueId}:{$trySeason}";
            $table = Cache::remember($cacheKey, now()->addMinutes($minutes), function () use ($leagueId, $trySeason) {
                $json = $this->request('/lookuptable.php', [
                    'l' => $leagueId,
                    's' => $trySeason,
                ]);

                return $json['table'] ?? [];
            });

            if (!empty($table)) {
                Cache::forget($this->lastErrorCacheKey);
                return $table;
            }
        }

        Cache::put($this->lastErrorCacheKey, 'Puan tablosu bulunamadi. Denenen sezonlar: ' . implode(', ', $seasons), now()->addMinutes(30));
        return [];
    }

    public function fixtures(string $leagueId): array
    {
        $minutes = (int) setting('cache_ttl_sports_fixtures', 120);
        $minutes = $minutes > 0 ? $minutes : 120;

        $cacheKey = "sports:tsdb:fixtures:{$leagueId}";

        return Cache::remember($cacheKey, now()->addMinutes($minutes), function () use ($leagueId) {
            $json = $this->request('/eventsnextleague.php', [
                'id' => $leagueId,
            ]);

            return $json['events'] ?? [];
        });
    }

    public function lastError(): ?string
    {
        $v = Cache::get($this->lastErrorCacheKey);
        return is_string($v) && $v !== '' ? $v : null;
    }

    protected function request(string $path, array $query): array
    {
        Cache::forget($this->lastErrorCacheKey);

        if (!$this->configured()) {
            Cache::put($this->lastErrorCacheKey, 'TheSportsDB key bos.', now()->addMinutes(30));
            return [];
        }

        $baseUrl = rtrim((string) setting('sports_thesportsdb_base_url', 'https://www.thesportsdb.com/api/v1/json'), '/');
        $key = (string) setting('sports_thesportsdb_key', '123');

        try {
            $res = Http::timeout(12)
                ->acceptJson()
                ->withoutVerifying()
                ->get($baseUrl . '/' . $key . $path, $query);
        } catch (\Throwable $e) {
            Cache::put($this->lastErrorCacheKey, 'Baglanti hatasi: ' . $e->getMessage(), now()->addMinutes(30));
            return [];
        }

        if (!$res->successful()) {
            Cache::put($this->lastErrorCacheKey, 'HTTP ' . $res->status(), now()->addMinutes(30));
            return [];
        }

        $json = $res->json();
        if (!is_array($json)) {
            Cache::put($this->lastErrorCacheKey, 'API cevabi parse edilemedi.', now()->addMinutes(30));
            return [];
        }

        return $json;
    }

    protected function candidateSeasons(string $season): array
    {
        $currentYear = (int) date('Y');
        $defaultCurrent = ($currentYear - 1) . '-' . $currentYear;

        $list = [];
        if ($season !== '') {
            $list[] = $season;
        }
        $list[] = $defaultCurrent;
        $list[] = ($currentYear - 2) . '-' . ($currentYear - 1);
        $list[] = ($currentYear + 0) . '-' . ($currentYear + 1);

        return collect($list)->filter()->unique()->values()->all();
    }
}
