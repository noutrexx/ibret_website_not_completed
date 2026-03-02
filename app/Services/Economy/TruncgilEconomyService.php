<?php

namespace App\Services\Economy;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TruncgilEconomyService
{
    protected string $snapshotCacheKey = 'economy:custom_api:snapshot';
    protected string $errorCacheKey = 'economy:custom_api:last_error';

    public function snapshot(): array
    {
        $ttl = (int) setting('cache_ttl_economy', 60);
        $ttl = $ttl > 0 ? $ttl : 60;

        return Cache::remember($this->snapshotCacheKey, now()->addSeconds($ttl), function () {
            $doviz = $this->fetchCategory('doviz');
            $altin = $this->fetchCategory('altin');
            $borsa = $this->fetchCategory('borsa');
            $kripto = $this->fetchCategory('kripto');

            $payloads = [$doviz, $altin, $borsa, $kripto];
            $allEmpty = collect($payloads)->every(fn ($payload) => empty($payload['items'] ?? []));

            if ($allEmpty) {
                return [
                    'updatedAt' => null,
                    'currencies' => [],
                    'allCurrencies' => [],
                    'gold' => [],
                    'allGold' => [],
                    'crypto' => [],
                    'allCrypto' => [],
                    'markets' => [],
                    'allMarkets' => [],
                    'error' => $this->lastError(),
                ];
            }

            $currencyItems = $this->normalizeCategoryItems($doviz, 'Currency');
            $goldItems = $this->normalizeCategoryItems($altin, 'Gold');
            $cryptoItems = $this->normalizeCategoryItems($kripto, 'CryptoCurrency');
            $marketItems = $this->normalizeCategoryItems($borsa, 'Market');

            $currencies = $this->pickSymbols(
                collect($currencyItems),
                (string) setting('economy_currency_symbols', 'USD,EUR,GBP,CHF,RUB,SAR')
            );
            $allCurrencies = array_values($currencyItems);

            $gold = $this->pickSymbols(
                collect($goldItems),
                (string) setting('economy_gold_symbols', 'GRA,CEYREKALTIN,YARIMALTIN,TAMALTIN,CUMHURIYETALTINI,GUMUS')
            );
            $allGold = array_values($goldItems);

            $crypto = $this->pickSymbols(
                collect($cryptoItems),
                (string) setting('economy_crypto_symbols', 'BTC,ETH,USDT,BNB,XRP,SOL,DOGE,ADA')
            );
            $allCrypto = array_values($cryptoItems);

            // Yeni borsa endpointi hisse agirlikli. Markets widgeti icin once ayarli sembolleri dene, yoksa borsadan secili hisseleri fallback ver.
            $markets = $this->pickSymbols(
                collect($marketItems)->concat(collect($goldItems))->concat(collect($currencyItems)),
                (string) setting('economy_market_symbols', 'XU100,BRENT,ONS')
            );
            if (empty($markets)) {
                $markets = collect($marketItems)->take(8)->values()->all();
            }

            $updatedAt = collect($payloads)
                ->pluck('fetched_at')
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->first();

            Cache::forget($this->errorCacheKey);

            return [
                'updatedAt' => $updatedAt,
                'currencies' => $currencies,
                'allCurrencies' => $allCurrencies,
                'gold' => $gold,
                'allGold' => $allGold,
                'crypto' => $crypto,
                'allCrypto' => $allCrypto,
                'markets' => $markets,
                'allMarkets' => array_values($marketItems),
                'error' => $this->lastError(),
            ];
        });
    }

    public function lastError(): ?string
    {
        $value = Cache::get($this->errorCacheKey);
        return is_string($value) && $value !== '' ? $value : null;
    }

    protected function fetchCategory(string $category): array
    {
        $base = rtrim((string) setting('economy_custom_api_base_url', 'http://45.87.173.75:8000/v1/latest'), '/');
        if ($base === '') {
            Cache::put($this->errorCacheKey, 'Ekonomi API base URL bos.', now()->addMinutes(20));
            return [];
        }

        $url = $base . '/' . $category;
        $apiKey = trim((string) setting('economy_custom_api_key', 'dc8a6b6d-e53e-47d0-9424-3dd7dd9ee82c'));
        if ($apiKey !== '') {
            $url .= '?api_key=' . urlencode($apiKey);
        }

        try {
            $res = Http::timeout(15)->acceptJson()->get($url);
        } catch (\Throwable $e) {
            Cache::put($this->errorCacheKey, 'Baglanti hatasi: ' . $e->getMessage(), now()->addMinutes(20));
            return [];
        }

        if (!$res->successful()) {
            Cache::put($this->errorCacheKey, 'HTTP ' . $res->status(), now()->addMinutes(20));
            return [];
        }

        $json = $res->json();
        if (!is_array($json)) {
            Cache::put($this->errorCacheKey, 'API cevabi parse edilemedi.', now()->addMinutes(20));
            return [];
        }

        return $json;
    }

    protected function normalizeCategoryItems(array $payload, string $type): array
    {
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];

        return collect($items)
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => $this->normalizeItem($row, $type))
            ->filter(fn (array $row) => $row['symbol'] !== '' && ($row['selling'] !== null || $row['buying'] !== null || $row['try_price'] !== null))
            ->values()
            ->all();
    }

    protected function normalizeItem(array $row, string $type): array
    {
        $rawCode = trim((string) ($row['code'] ?? $row['id'] ?? ''));
        $symbol = $this->normalizeSymbol($rawCode, $type);

        $buying = $this->toFloat($row['buy'] ?? $row['Buying'] ?? null);
        $selling = $this->toFloat($row['sell'] ?? $row['Selling'] ?? null);
        $tryPrice = $this->toFloat($row['sell'] ?? $row['TRY_Price'] ?? null);
        $usdPrice = $this->toFloat($row['USD_Price'] ?? null);

        if ($buying === null) {
            $buying = $tryPrice;
        }

        return [
            'symbol' => $symbol,
            'raw_symbol' => $rawCode,
            'name' => (string) ($row['title'] ?? $row['Name'] ?? $symbol),
            'type' => $type,
            'buying' => $buying,
            'selling' => $selling,
            'try_price' => $tryPrice,
            'usd_price' => $usdPrice,
            'change' => $this->toFloat($row['change_percent'] ?? $row['Change'] ?? null),
            'change_value' => $this->toFloat($row['change_value'] ?? null),
            'time' => (string) ($row['time'] ?? ''),
        ];
    }

    protected function normalizeSymbol(string $code, string $type): string
    {
        $upper = strtoupper($code);

        if ($type === 'Gold') {
            $map = [
                'GA' => 'GRA',
                'C' => 'CEYREKALTIN',
                'Y' => 'YARIMALTIN',
                'T' => 'TAMALTIN',
                'CMR' => 'CUMHURIYETALTINI',
                'XAU/USD' => 'ONS',
                'XAG/USD' => 'GUMUS',
            ];
            if (isset($map[$upper])) {
                return $map[$upper];
            }
        }

        return $upper;
    }

    protected function pickSymbols(Collection $items, string $csvSymbols): array
    {
        $wanted = collect(explode(',', $csvSymbols))
            ->map(fn ($v) => strtoupper(trim($v)))
            ->filter()
            ->values();

        if ($wanted->isEmpty()) {
            return $items->take(8)->values()->all();
        }

        $map = $items->keyBy(fn ($item) => strtoupper((string) ($item['symbol'] ?? '')));
        $picked = [];

        foreach ($wanted as $symbol) {
            if ($map->has($symbol)) {
                $picked[] = $map->get($symbol);
            }
        }

        return $picked;
    }

    protected function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = str_replace(['.', ','], ['', '.'], $value);
            if (is_numeric($normalized)) {
                return (float) $normalized;
            }
        }

        return null;
    }
}
