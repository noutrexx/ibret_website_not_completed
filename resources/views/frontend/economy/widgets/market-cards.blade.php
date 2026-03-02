@php
    $format = $format ?? function ($n, $decimals = 2) {
        if ($n === null) return '-';
        return number_format((float) $n, $decimals, ',', '.');
    };
@endphp

<section class="grid md:grid-cols-3 gap-4 mb-6">
    @forelse(($items ?? []) as $item)
        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">{{ $item['symbol'] }}</div>
            <div class="text-lg font-semibold mt-1">{{ $item['name'] }}</div>
            <div class="text-2xl font-bold mt-2">
                {{ $format($item['selling'] ?? $item['buying'] ?? $item['try_price'] ?? null, 2) }}
            </div>
            <div class="text-sm mt-2 {{ (($item['change'] ?? 0) >= 0) ? 'text-emerald-600' : 'text-red-600' }}">
                %{{ $format($item['change'] ?? 0, 2) }}
            </div>
        </div>
    @empty
        <div class="bg-white border rounded-lg p-4 text-sm text-gray-500 md:col-span-3">Veri yok.</div>
    @endforelse
</section>

