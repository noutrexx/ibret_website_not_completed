@extends('frontend.layouts.app')

@php
    $format = function ($n, $decimals = 4) {
        if ($n === null) return '-';
        return number_format((float) $n, $decimals, ',', '.');
    };
@endphp

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        <section style="display:grid;grid-template-columns:720px 320px;column-gap:20px;width:1060px;max-width:100%;">
            <div style="width:720px;">
                <section class="border bg-white rounded-lg overflow-hidden" style="width:100%;">
                    <div style="padding:14px 16px;border-bottom:1px solid #e5e7eb;">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Ekonomi</div>
                        <h1 class="text-2xl font-semibold mt-1">Doviz Kurlari</h1>
                        @if(!empty($economyUpdatedAt))
                            <div class="text-sm text-gray-500 mt-2">Son guncelleme: {{ $economyUpdatedAt }}</div>
                        @endif
                        @if(!empty($economyError))
                            <div class="text-sm text-red-600 mt-1">API uyarisi: {{ $economyError }}</div>
                        @endif
                    </div>

                    <div style="max-height:440px;overflow:auto;padding:6px 16px 12px;">
                        @if(empty($economyAllCurrencies))
                            <div class="text-sm text-gray-500 py-4">Veri yok.</div>
                        @else
                            @foreach($economyAllCurrencies as $item)
                                <div style="display:grid;grid-template-columns:1fr 115px 115px 70px 70px;gap:10px;align-items:center;font-size:13px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
                                    <div>
                                        <div style="font-weight:700;">{{ $item['symbol'] ?? '-' }}</div>
                                        <div style="color:#6b7280;">{{ $item['name'] ?? '-' }}</div>
                                    </div>
                                    <div style="text-align:right;">Alis: {{ $format($item['buying'] ?? null, 4) }}</div>
                                    <div style="text-align:right;">Satis: {{ $format($item['selling'] ?? null, 4) }}</div>
                                    <div style="text-align:right;color:{{ (($item['change'] ?? 0) < 0) ? '#b91c1c' : '#047857' }};">{{ isset($item['change']) && $item['change'] !== null ? $format($item['change'], 2) . '%' : '-' }}</div>
                                    <div style="text-align:right;color:#6b7280;font-size:12px;">{{ $item['time'] ?? '-' }}</div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </section>

                <section style="margin-top:20px;">
                    <div class="text-sm font-semibold text-gray-700 mb-3">Ekonomi Haberleri</div>
                    <div id="economy-currency-feed">
                        @include('frontend.partials.category-list-items', ['posts' => $posts])
                    </div>

                    <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400 my-4" style="height:120px;">
                        740x120 In-Feed Reklam Alani
                    </div>

                    <div class="text-center" id="economy-currency-load-wrap">
                        @if($posts->hasMorePages())
                            <button id="economy-currency-load-more" class="px-4 py-2 rounded border bg-white hover:bg-gray-50 text-sm">Daha Fazla Yukle</button>
                        @endif
                    </div>
                </section>
            </div>

            <aside style="width:320px;">
                <section class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-2">Haftanin En Cok Okunanlari</div>
                    <div class="space-y-4">
                        @forelse(($weeklyPopularPosts ?? collect()) as $post)
                            <a href="{{ $post->frontend_url }}" class="block border rounded-lg overflow-hidden relative" style="width:100%;height:220px;">
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:220px;object-fit:cover;display:block;">
                                @endif
                                <div style="position:absolute;left:0;right:0;bottom:0;height:90px;background:linear-gradient(to top, rgba(0,0,0,.72), rgba(0,0,0,0));"></div>
                                <div style="position:absolute;left:14px;right:14px;bottom:12px;color:#fff;font-size:15px;font-weight:600;line-height:1.3;">
                                    {{ $post->title }}
                                </div>
                            </a>
                        @empty
                            <div class="text-sm text-gray-500 bg-white border rounded-lg p-4">Bu hafta icin populer haber bulunamadi.</div>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-5 sticky" style="top:84px;margin-top:16px;">
                    <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400" style="height:100px;">
                        320x100 Header Alti Reklam
                    </div>
                    <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400" style="height:250px;">
                        320x250 Sidebar Reklam
                    </div>
                    <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400" style="height:600px;">
                        300x600 Skyscraper Reklam
                    </div>
                </section>
            </aside>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const loadBtn = document.getElementById('economy-currency-load-more');
        const feed = document.getElementById('economy-currency-feed');
        const wrap = document.getElementById('economy-currency-load-wrap');
        if (!loadBtn || !feed || !wrap) return;

        let nextPage = {{ $posts->hasMorePages() ? (int) ($posts->currentPage() + 1) : 'null' }};
        let pending = false;
        const baseUrl = @json(route('category.economy.currencies', $category->slug));

        loadBtn.addEventListener('click', async () => {
            if (!nextPage || pending) return;
            pending = true;
            loadBtn.disabled = true;
            loadBtn.textContent = 'Yukleniyor...';

            try {
                const url = `${baseUrl}?page=${nextPage}&feed=1`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (data.html) {
                    feed.insertAdjacentHTML('beforeend', data.html);
                }
                nextPage = data.hasMore ? data.nextPage : null;
                if (!nextPage) {
                    wrap.remove();
                } else {
                    loadBtn.disabled = false;
                    loadBtn.textContent = 'Daha Fazla Yukle';
                }
            } catch (e) {
                loadBtn.disabled = false;
                loadBtn.textContent = 'Tekrar Dene';
            } finally {
                pending = false;
            }
        });
    })();
</script>
@endpush
