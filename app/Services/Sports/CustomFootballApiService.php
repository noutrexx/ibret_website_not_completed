<?php

namespace App\Services\Sports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CustomFootballApiService
{
    protected string $lastErrorCacheKeyPrefix = 'sports:custom_api:last_error:';

    public function configured(?string $leagueKey = null): bool
    {
        return $this->baseUrl() !== '';
    }

    public function leagues(): array
    {
        return Cache::remember('sports:custom_api:leagues', now()->addHours(6), function () {
            $payload = $this->requestJson('/standings');
            $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];

            $leagues = collect($items)
                ->map(function ($league) {
                    return [
                        'key' => (string) ($league['league_key'] ?? ''),
                        'name' => (string) ($league['league_name'] ?? ''),
                        'country' => (string) ($league['country'] ?? ''),
                    ];
                })
                ->filter(fn ($league) => $league['key'] !== '' && $league['name'] !== '')
                ->values()
                ->all();

            $filtered = $this->filterLeaguesBySetting($leagues);

            return !empty($filtered) ? $filtered : $this->fallbackLeagues();
        });
    }

    public function defaultLeagueKey(): string
    {
        $preferred = trim((string) setting('sports_custom_default_league', 'super_lig'));
        $keys = collect($this->leagues())->pluck('key')->all();

        if ($preferred !== '' && in_array($preferred, $keys, true)) {
            return $preferred;
        }

        return (string) ($keys[0] ?? 'super_lig');
    }

    public function leagueName(?string $leagueKey = null): string
    {
        $key = $this->resolveLeagueKey($leagueKey);
        foreach ($this->leagues() as $league) {
            if (($league['key'] ?? null) === $key) {
                return (string) $league['name'];
            }
        }

        return 'Lig';
    }

    public function standings(?string $leagueKey = null): array
    {
        $key = $this->resolveLeagueKey($leagueKey);
        $minutes = max(5, (int) setting('cache_ttl_sports_standings', 360));

        return Cache::remember("sports:custom_api:standings:{$key}", now()->addMinutes($minutes), function () use ($key) {
            $payload = $this->requestJson('/standings/' . rawurlencode($key), $key);
            $rows = is_array($payload['items'] ?? null) ? $payload['items'] : [];
            if (empty($rows)) {
                $rows = $this->standingsRowsFromAggregate($key);
            }

            $mapped = collect($rows)
                ->map(function ($row) {
                    return [
                        'intRank' => (int) ($row['rank'] ?? 0),
                        'strTeam' => (string) ($row['team'] ?? ''),
                        'intPoints' => isset($row['points']) ? (int) $row['points'] : null,
                        'intPlayed' => isset($row['played']) ? (int) $row['played'] : null,
                        'intWon' => isset($row['won']) ? (int) $row['won'] : null,
                        'intDrawn' => isset($row['drawn']) ? (int) $row['drawn'] : null,
                        'intLost' => isset($row['lost']) ? (int) $row['lost'] : null,
                        'intGoalsFor' => isset($row['goals_for']) ? (int) $row['goals_for'] : null,
                        'intGoalsAgainst' => isset($row['goals_against']) ? (int) $row['goals_against'] : null,
                        'intGoalDifference' => isset($row['goal_difference']) ? (int) $row['goal_difference'] : null,
                    ];
                })
                ->filter(fn ($row) => $row['intRank'] > 0 && $row['strTeam'] !== '')
                ->sortBy('intRank')
                ->values()
                ->all();

            if (empty($mapped)) {
                $this->setError($key, 'Puan durumu verisi alinamadi.');
            } else {
                Cache::forget($this->lastErrorCacheKey($key));
            }

            return $mapped;
        });
    }

    public function fixtures(?string $leagueKey = null): array
    {
        $key = $this->resolveLeagueKey($leagueKey);
        $minutes = max(5, (int) setting('cache_ttl_sports_fixtures', 120));

        return Cache::remember("sports:custom_api:fixtures:{$key}", now()->addMinutes($minutes), function () use ($key) {
            $payload = $this->requestJson('/fixtures/' . rawurlencode($key), $key);
            $rows = is_array($payload['items'] ?? null) ? $payload['items'] : [];
            if (empty($rows)) {
                $rows = $this->fixtureRowsFromAggregate($key);
            }

            $mapped = collect($rows)
                ->map(function ($row) {
                    $kickoff = trim((string) ($row['kickoff_utc'] ?? ''));
                    $dateEvent = '';
                    $strTime = '';

                    if ($kickoff !== '') {
                        try {
                            $dt = Carbon::parse($kickoff)->timezone(config('app.timezone', 'UTC'));
                            $dateEvent = $dt->format('Y-m-d');
                            $strTime = $dt->format('H:i:s');
                        } catch (\Throwable $e) {
                            // Tarih parse edilmezse ham degerlerden devam ederiz.
                        }
                    }

                    return [
                        'idEvent' => (string) ($row['match_id'] ?? ''),
                        'dateEvent' => $dateEvent,
                        'strTime' => $strTime,
                        'strHomeTeam' => (string) ($row['home_team'] ?? ''),
                        'strAwayTeam' => (string) ($row['away_team'] ?? ''),
                        'round' => (string) ($row['round'] ?? ''),
                        'status' => (string) ($row['status'] ?? ''),
                        'intHomeScore' => $row['home_goals'],
                        'intAwayScore' => $row['away_goals'],
                        'kickoffUtc' => $kickoff,
                    ];
                })
                ->filter(fn ($row) => $row['strHomeTeam'] !== '' && $row['strAwayTeam'] !== '')
                ->values()
                ->all();

            if (empty($mapped)) {
                $this->setError($key, 'Fikstur verisi alinamadi.');
            } else {
                Cache::forget($this->lastErrorCacheKey($key));
            }

            return $mapped;
        });
    }

    public function refresh(?string $leagueKey = null, bool $clearCaches = true): bool
    {
        $key = $this->resolveLeagueKey($leagueKey);

        if ($clearCaches) {
            Cache::forget("sports:custom_api:standings:{$key}");
            Cache::forget("sports:custom_api:fixtures:{$key}");
        }

        try {
            $this->standings($key);
            $this->fixtures($key);
            return empty($this->lastError($key));
        } catch (\Throwable $e) {
            $this->setError($key, 'Spor API yenileme hatasi: ' . $e->getMessage());
            return false;
        }
    }

    public function refreshAll(bool $clearCaches = true): array
    {
        Cache::forget('sports:custom_api:leagues');

        $results = [];
        foreach ($this->leagues() as $league) {
            $key = (string) ($league['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $ok = $this->refresh($key, $clearCaches);
            $results[$key] = [
                'ok' => $ok,
                'name' => (string) ($league['name'] ?? $key),
                'error' => $ok ? null : $this->lastError($key),
            ];
        }

        return $results;
    }

    public function lastError(?string $leagueKey = null): ?string
    {
        $key = $this->resolveLeagueKey($leagueKey);
        $value = Cache::get($this->lastErrorCacheKey($key));
        return is_string($value) && $value !== '' ? $value : null;
    }

    protected function resolveLeagueKey(?string $leagueKey = null): string
    {
        $wanted = trim((string) $leagueKey);
        if ($wanted === '') {
            return $this->defaultLeagueKey();
        }

        $keys = collect($this->leagues())->pluck('key')->all();
        return in_array($wanted, $keys, true) ? $wanted : $this->defaultLeagueKey();
    }

    protected function requestJson(string $path, ?string $leagueKeyForError = null): array
    {
        $baseUrl = rtrim($this->baseUrl(), '/');
        if ($baseUrl === '') {
            $this->setError($leagueKeyForError ?: 'default', 'Spor API base URL ayari eksik.');
            return [];
        }

        $url = $baseUrl . '/' . ltrim($path, '/');

        $apiKey = trim((string) setting('sports_custom_api_key', ''));
        if ($apiKey !== '') {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . 'api_key=' . urlencode($apiKey);
        }

        try {
            $response = Http::acceptJson()
                ->timeout(max(5, (int) setting('sports_custom_api_timeout', 15)))
                ->get($url);
        } catch (\Throwable $e) {
            $this->setError($leagueKeyForError ?: 'default', 'Baglanti hatasi: ' . $e->getMessage());
            return [];
        }

        if (!$response->successful()) {
            $this->setError($leagueKeyForError ?: 'default', 'API hatasi: HTTP ' . $response->status());
            return [];
        }

        $json = $response->json();
        if (!is_array($json)) {
            $this->setError($leagueKeyForError ?: 'default', 'API yaniti gecersiz JSON.');
            return [];
        }

        return $json;
    }

    protected function standingsRowsFromAggregate(string $leagueKey): array
    {
        $payload = Cache::remember('sports:custom_api:standings:all_payload', now()->addMinutes(10), function () use ($leagueKey) {
            return $this->requestJson('/standings', $leagueKey);
        });

        if (!is_array($payload)) {
            return [];
        }

        $leagues = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        foreach ($leagues as $league) {
            if (($league['league_key'] ?? null) === $leagueKey && is_array($league['items'] ?? null)) {
                return $league['items'];
            }
        }

        return [];
    }

    protected function fixtureRowsFromAggregate(string $leagueKey): array
    {
        $payload = Cache::remember('sports:custom_api:fixtures:all_payload', now()->addMinutes(10), function () use ($leagueKey) {
            return $this->requestJson('/fixtures', $leagueKey);
        });

        if (!is_array($payload)) {
            return [];
        }

        $leagues = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        foreach ($leagues as $league) {
            if (($league['league_key'] ?? null) === $leagueKey && is_array($league['items'] ?? null)) {
                return $league['items'];
            }
        }

        return [];
    }

    protected function baseUrl(): string
    {
        return trim((string) setting('sports_custom_api_base_url', 'http://45.87.173.75:8000/v1/football'));
    }

    protected function lastErrorCacheKey(string $leagueKey): string
    {
        return $this->lastErrorCacheKeyPrefix . $leagueKey;
    }

    protected function setError(string $leagueKey, string $message): void
    {
        Cache::put($this->lastErrorCacheKey($leagueKey), $message, now()->addMinutes(30));
    }

    protected function filterLeaguesBySetting(array $leagues): array
    {
        $raw = trim((string) setting('sports_custom_enabled_leagues', ''));
        if ($raw === '') {
            return $leagues;
        }

        $allowed = collect(explode(',', $raw))
            ->map(fn ($key) => trim((string) $key))
            ->filter()
            ->values()
            ->all();

        if (empty($allowed)) {
            return $leagues;
        }

        $allowedMap = array_fill_keys($allowed, true);

        return collect($leagues)
            ->filter(fn ($league) => isset($allowedMap[$league['key']]))
            ->values()
            ->all();
    }

    protected function fallbackLeagues(): array
    {
        return [
            ['key' => 'super_lig', 'name' => 'Super Lig', 'country' => 'Turkey'],
            ['key' => 'premier_league', 'name' => 'Premier League', 'country' => 'England'],
            ['key' => 'laliga', 'name' => 'LaLiga', 'country' => 'Spain'],
            ['key' => 'serie_a', 'name' => 'Serie A', 'country' => 'Italy'],
            ['key' => 'bundesliga', 'name' => 'Bundesliga', 'country' => 'Germany'],
        ];
    }
}
