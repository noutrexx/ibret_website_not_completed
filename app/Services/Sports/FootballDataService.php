<?php

namespace App\Services\Sports;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FootballDataService
{
    public function configured(): bool
    {
        return (string) setting('sports_football_data_key', '') !== '';
    }

    public function standings(string $competitionCode): array
    {
        if (!$this->configured()) {
            return [];
        }

        $minutes = (int) setting('cache_ttl_sports_europe', 360);
        $minutes = $minutes > 0 ? $minutes : 360;
        $cacheKey = 'sports:fd:standings:' . strtoupper($competitionCode);

        return Cache::remember($cacheKey, now()->addMinutes($minutes), function () use ($competitionCode) {
            $baseUrl = rtrim((string) setting('sports_football_data_base_url', 'https://api.football-data.org/v4'), '/');
            $token = (string) setting('sports_football_data_key', '');

            try {
                $res = Http::timeout(12)
                    ->acceptJson()
                    ->withHeaders(['X-Auth-Token' => $token])
                    ->get($baseUrl . '/competitions/' . strtoupper($competitionCode) . '/standings');
            } catch (\Throwable $e) {
                return [];
            }

            if (!$res->successful()) {
                return [];
            }

            $json = $res->json();
            $table = $json['standings'][0]['table'] ?? [];
            if (!is_array($table)) {
                return [];
            }

            return array_slice($table, 0, 8);
        });
    }
}
