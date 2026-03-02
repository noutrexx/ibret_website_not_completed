@php
    $format = $format ?? function ($n, $decimals = 4) {
        if ($n === null) return '-';
        return number_format((float) $n, $decimals, ',', '.');
    };
    $mode = $mode ?? 'currency';
@endphp

<section class="bg-white border rounded-lg overflow-hidden mb-6">
    <div class="px-4 py-3 border-b font-semibold">{{ $title ?? 'Veriler' }}</div>
    <div class="p-4">
        @if(empty($items))
            <div class="text-sm text-gray-500">Veri yok.</div>
        @else
            <div class="space-y-2">
                @foreach($items as $item)
                    <div class="flex items-center justify-between text-sm border-b pb-2">
                        <div>
                            <div class="font-medium">{{ $item['symbol'] }}</div>
                            <div class="text-xs text-gray-500">{{ $item['name'] }}</div>
                        </div>

                        @if($mode === 'crypto')
                            <div class="text-right">
                                <div>TRY: {{ $format($item['buying'] ?? $item['try_price'], 2) }}</div>
                                <div class="text-xs text-gray-500">USD: {{ $format($item['usd_price'], 4) }}</div>
                            </div>
                        @elseif($mode === 'gold')
                            <div class="text-right">
                                <div>Alis: {{ $format($item['buying'], 2) }}</div>
                                <div>Satis: {{ $format($item['selling'], 2) }}</div>
                            </div>
                        @else
                            <div class="text-right">
                                <div>Alis: {{ $format($item['buying'], 4) }}</div>
                                <div>Satis: {{ $format($item['selling'], 4) }}</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

