@extends('frontend.layouts.app')

@php
    $format = function ($n, $decimals = 4) {
        if ($n === null) return '-';
        return number_format((float) $n, $decimals, ',', '.');
    };
@endphp

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        @if(!empty($economyUpdatedAt) || !empty($economyError))
            <section class="mb-4 bg-white border border-gray-100 rounded-2xl p-3 shadow-sm">
                @if(!empty($economyUpdatedAt))
                    <div class="text-sm text-gray-500">Son guncelleme: {{ $economyUpdatedAt }}</div>
                @endif
                @if(!empty($economyError))
                    <div class="text-sm text-red-600 mt-1">API uyarisi: {{ $economyError }}</div>
                @endif
            </section>
        @endif

        <section class="bg-white rounded-2xl overflow-hidden relative shadow-sm border border-gray-100" style="height:450px;margin-top:16px;">
            @if(($economyTopManset ?? collect())->isNotEmpty())
                @php
                    $svgColors = [
                        ['#0f766e', '#134e4a'],
                        ['#1d4ed8', '#1e3a8a'],
                        ['#9333ea', '#6b21a8'],
                        ['#b45309', '#7c2d12'],
                        ['#0f172a', '#1e293b'],
                    ];
                @endphp
                <div class="h-full overflow-hidden" id="eco-top-slider" style="width:100%;overflow:hidden;cursor:grab;position:relative;z-index:1;background:#0f172a;">
                    <div class="slider-track" id="eco-top-track" style="display:flex;width:100%;height:100%;transition:transform 520ms cubic-bezier(.22,.61,.36,1);z-index:1;">
                        @foreach($economyTopManset->take(20) as $index => $post)
                            @php
                                $pair = $svgColors[$index % count($svgColors)];
                                $gradId = 'eco_asfer_' . $index;
                            @endphp
                            <a href="{{ $post->frontend_url }}" class="snap-center block h-full" style="flex:0 0 100%;width:100%;" draggable="false">
                                <div class="relative" style="height:450px;display:flex;background:#111827;">
                                    <div style="width:606px;height:450px;position:relative;overflow:hidden;flex:0 0 606px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="606" height="450" viewBox="0 0 606 450" style="position:absolute;left:0;top:0;height:450px;width:606px;">
                                            <defs>
                                                <linearGradient id="{{ $gradId }}" x1="0.722" y1="0.985" x2="0.278" y2="0.015" gradientUnits="objectBoundingBox">
                                                    <stop offset="0" stop-color="{{ $pair[0] }}"></stop>
                                                    <stop offset="1" stop-color="{{ $pair[1] }}"></stop>
                                                </linearGradient>
                                            </defs>
                                            <path d="M719,167.04h403.977L1325,617H719Z" transform="translate(-719 -167.04)" fill="url(#{{ $gradId }})"></path>
                                        </svg>
                                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:30px;color:#fff;">
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <span style="display:inline-flex;align-items:center;height:26px;padding:0 10px;border-radius:999px;background:rgba(255,255,255,.14);backdrop-filter:blur(4px);font-size:11px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;">Ekonomi Manset</span>
                                                <span style="font-size:12px;color:rgba(255,255,255,.9);">
                                                    {{ optional($post->published_at ?? $post->created_at)->format('d.m.Y H:i') }}
                                                </span>
                                            </div>
                                            <div class="text-3xl font-semibold leading-snug" style="margin-top:12px;text-shadow:0 4px 18px rgba(0,0,0,.25);">
                                                {{ $post->title }}
                                            </div>
                                            @if(!empty($post->summary))
                                                <div style="margin-top:10px;font-size:14px;line-height:1.6;color:rgba(255,255,255,.9);max-width:92%;">
                                                    {{ \Illuminate\Support\Str::limit(strip_tags($post->summary), 140) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div style="width:760px;height:450px;overflow:hidden;margin-left:-306px;position:relative;">
                                        @if($post->image)
                                            <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:760px;height:450px;object-fit:cover;display:block;" draggable="false">
                                        @endif
                                        <div style="position:absolute;inset:0;background:linear-gradient(90deg, rgba(17,24,39,.08) 0%, rgba(17,24,39,0) 35%, rgba(17,24,39,.12) 100%);"></div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                <button class="eco-top-prev absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 border border-white/70 rounded-full w-11 h-11 shadow-sm text-gray-900 flex items-center justify-center" style="backdrop-filter:blur(6px);">&lt;</button>
                <button class="eco-top-next absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 border border-white/70 rounded-full w-11 h-11 shadow-sm text-gray-900 flex items-center justify-center" style="backdrop-filter:blur(6px);">&gt;</button>
                <div class="flex flex-wrap" style="gap:6px;z-index:30;position:absolute;left:16px;bottom:14px;pointer-events:auto;padding:6px 8px;border-radius:999px;background:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.85);box-shadow:0 4px 14px rgba(15,23,42,.16);">
                    @foreach($economyTopManset->take(20) as $dotIndex => $dotPost)
                        <button class="eco-top-dot text-xs border" style="background:#fff;color:#111827;border-color:#dbe2ea;min-width:28px;height:28px;border-radius:999px;font-weight:700;" data-index="{{ $dotIndex }}">{{ $dotIndex + 1 }}</button>
                    @endforeach
                </div>
            @else
                <div class="h-full flex items-center justify-center text-sm text-gray-400">1060x450 Ekonomi Slider</div>
            @endif
        </section>

        <section style="margin-top:16px;display:grid;grid-template-columns:repeat(4,1fr);gap:16px;width:1060px;max-width:100%;">
            @forelse(($economyLatestPosts ?? collect()) as $post)
                <a href="{{ $post->frontend_url }}" class="block rounded-2xl overflow-hidden relative shadow-sm border border-gray-100 transition duration-200 hover:-translate-y-0.5 hover:shadow-md" style="height:200px;">
                    @if($post->image)
                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:200px;object-fit:cover;display:block;">
                    @endif
                    <div style="position:absolute;left:0;right:0;bottom:0;height:96px;background:linear-gradient(to top, rgba(0,0,0,.8), rgba(0,0,0,0));"></div>
                    <div style="position:absolute;left:12px;top:12px;">
                        <span style="display:inline-flex;height:22px;align-items:center;padding:0 8px;border-radius:999px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.25);backdrop-filter:blur(4px);color:#fff;font-size:10px;font-weight:700;text-transform:uppercase;">Ekonomi</span>
                    </div>
                    <div style="position:absolute;left:12px;right:12px;bottom:10px;color:#fff;font-size:14px;font-weight:700;line-height:1.35;text-shadow:0 2px 10px rgba(0,0,0,.25);">
                        {{ \Illuminate\Support\Str::limit($post->title, 90) }}
                    </div>
                </a>
            @empty
                @for($i = 0; $i < 4; $i++)
                    <div class="rounded-2xl bg-white flex items-center justify-center text-sm text-gray-400 border border-gray-100 shadow-sm" style="height:200px;">
                        Ekonomi Haber Kutusu
                    </div>
                @endfor
            @endforelse
        </section>

        @php
            $economyLines = [
                ['title' => 'ALTIN HABERLERI', 'category' => $goldCategory ?? null, 'posts' => $goldPosts ?? collect(), 'widgetTitle' => 'Altin Widget', 'widgetItems' => $economyGold ?? [], 'widgetMode' => 'gold'],
                ['title' => 'DOVIZ HABERLERI', 'category' => $currencyCategory ?? null, 'posts' => $currencyPosts ?? collect(), 'widgetTitle' => 'Doviz Widget', 'widgetItems' => $economyCurrencies ?? [], 'widgetMode' => 'currency'],
                ['title' => 'BORSA HABERLERI', 'category' => $borsaCategory ?? null, 'posts' => $borsaPosts ?? collect(), 'widgetTitle' => 'Borsa Widget', 'widgetItems' => $economyMarkets ?? [], 'widgetMode' => 'market'],
                ['title' => 'KRIPTO HABERLERI', 'category' => $cryptoCategory ?? null, 'posts' => $cryptoPosts ?? collect(), 'widgetTitle' => 'Kripto Widget', 'widgetItems' => $economyCrypto ?? [], 'widgetMode' => 'crypto'],
            ];
        @endphp

        @foreach($economyLines as $line)
            <section style="width:1060px;max-width:100%;margin-top:20px;">
                <div class="line line-title" style="height:40px;margin-bottom:10px;">
                    <div class="line-img" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;margin-right:8px;">
                        <img alt="{{ $line['title'] }}" width="20" height="20" src="https://img.icons8.com/color/48/combo-chart--v1.png" style="width:20px;height:20px;display:block;border-radius:2px;">
                    </div>
                    <div class="line__container" style="background-color:#f5e629;">
                        <h2 class="line__title">{{ $line['title'] }}</h2>
                        @if(!empty($line['category']))
                            <a class="line__link" title="Tum {{ $line['category']->name }} Haberleri" href="{{ $line['category']->frontend_url }}">
                                <span class="line--text" style="background-color:#f5e629;">Tum {{ $line['category']->name }} Haberleri</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:750px 290px;gap:20px;width:1060px;height:500px;">
                    <div style="width:750px;height:500px;display:grid;grid-template-rows:250px 230px;gap:20px;">
                        @php
                            $topPost = ($line['posts'] ?? collect())->first();
                            $bottomPosts = ($line['posts'] ?? collect())->slice(1, 3);
                        @endphp

                        <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm transition duration-200 hover:shadow-md" style="height:250px;">
                            @if($topPost)
                                <a href="{{ $topPost->frontend_url }}" style="display:grid;grid-template-columns:360px 1fr;height:250px;color:inherit;text-decoration:none;">
                                    <div style="height:250px;overflow:hidden;">
                                        @if($topPost->image)
                                            <img src="{{ asset('storage/' . $topPost->image) }}" alt="{{ $topPost->title }}" style="width:100%;height:250px;object-fit:cover;display:block;">
                                        @endif
                                    </div>
                                    <div style="padding:18px 20px;display:flex;flex-direction:column;height:250px;">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="display:inline-flex;align-items:center;height:22px;padding:0 8px;border-radius:999px;background:#f3f4f6;color:#111827;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;">Son Haber</span>
                                            <span style="font-size:12px;color:#9ca3af;">
                                                {{ optional($topPost->published_at ?? $topPost->created_at)->format('d.m.Y H:i') }}
                                            </span>
                                        </div>
                                        <div style="font-size:22px;font-weight:700;line-height:1.28;margin-top:10px;color:#111827;">
                                            {{ $topPost->title }}
                                        </div>
                                        <div style="font-size:14px;color:#4b5563;line-height:1.6;margin-top:10px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                            {{ \Illuminate\Support\Str::limit($topPost->summary ?: strip_tags($topPost->content ?? ''), 170) }}
                                        </div>
                                        <div style="margin-top:auto;color:#2563eb;font-size:12px;font-weight:700;">Habere git →</div>
                                    </div>
                                </a>
                            @else
                                <div class="h-full flex items-center justify-center text-sm text-gray-400">Bu alt kategoride haber yok</div>
                            @endif
                        </div>

                        <div style="height:230px;display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                            @for($i = 0; $i < 3; $i++)
                                @php $post = $bottomPosts->get($i); @endphp
                                <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm transition duration-200 hover:shadow-md hover:-translate-y-0.5" style="height:230px;">
                                    @if($post)
                                        <a href="{{ $post->frontend_url }}" style="display:block;height:230px;color:inherit;text-decoration:none;">
                                            <div style="height:130px;overflow:hidden;">
                                                @if($post->image)
                                                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:130px;object-fit:cover;display:block;">
                                                @endif
                                            </div>
                                            <div style="padding:11px 12px 12px;">
                                                <div style="font-size:14px;font-weight:700;line-height:1.35;color:#111827;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:38px;">
                                                    {{ \Illuminate\Support\Str::limit($post->title, 90) }}
                                                </div>
                                                <div style="font-size:12px;color:#6b7280;margin-top:6px;line-height:1.45;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                                    {{ \Illuminate\Support\Str::limit($post->summary ?: strip_tags($post->content ?? ''), 60) }}
                                                </div>
                                            </div>
                                        </a>
                                    @else
                                        <div class="h-full flex items-center justify-center text-sm text-gray-400">Haber yok</div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>

                    <aside class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm" style="width:290px;height:500px;">
                        <div style="height:44px;display:flex;align-items:center;padding:0 14px;border-bottom:1px solid #eef2f7;font-size:12px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;background:#fafbfc;">
                            {{ $line['widgetTitle'] }}
                        </div>
                        <div style="height:456px;overflow:auto;padding:10px;">
                            @if(empty($line['widgetItems']))
                                <div class="text-sm text-gray-500 pt-2">Widget verisi yok.</div>
                            @else
                                <div>
                                    @foreach($line['widgetItems'] as $item)
                                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:12px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
                                            <div style="min-width:0;">
                                                <div style="font-weight:700;color:#111827;">{{ $item['symbol'] ?? '-' }}</div>
                                                <div style="color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;">{{ $item['name'] ?? '-' }}</div>
                                            </div>
                                            <div style="text-align:right;">
                                                @if($line['widgetMode'] === 'crypto')
                                                    <div>TRY: {{ $format($item['buying'] ?? $item['try_price'] ?? null, 2) }}</div>
                                                    @if(isset($item['change']) && $item['change'] !== null)
                                                        <div style="color:{{ ($item['change'] < 0) ? '#b91c1c' : '#047857' }};">{{ $format($item['change'], 2) }}%</div>
                                                    @endif
                                                @elseif($line['widgetMode'] === 'market')
                                                    <div>Son: {{ $format($item['selling'] ?? $item['buying'] ?? null, 2) }}</div>
                                                    @if(isset($item['change']) && $item['change'] !== null)
                                                        <div style="color:{{ ($item['change'] < 0) ? '#b91c1c' : '#047857' }};">{{ $format($item['change'], 2) }}%</div>
                                                    @endif
                                                @elseif($line['widgetMode'] === 'gold')
                                                    <div>Alis: {{ $format($item['buying'] ?? null, 2) }}</div>
                                                    <div>Satis: {{ $format($item['selling'] ?? null, 2) }}</div>
                                                @else
                                                    <div>Alis: {{ $format($item['buying'] ?? null, 4) }}</div>
                                                    <div>Satis: {{ $format($item['selling'] ?? null, 4) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </aside>
                </div>
            </section>
        @endforeach

        <section style="margin-top:24px;">
            <div class="text-sm font-semibold text-gray-800" style="margin-bottom:12px;">Ekonomi Haberleri</div>
            <div id="economy-feed">
                @include('frontend.partials.category-list-items', ['posts' => $posts])
            </div>
            <div class="bg-white rounded-2xl flex items-center justify-center text-sm text-gray-400 my-5 border border-gray-100 shadow-sm" style="height:120px;">
                740x120 In-Feed Reklam Alani
            </div>

            <div class="text-center" id="economy-load-wrap">
                @if($posts->hasMorePages())
                    <button id="economy-load-more" class="px-4 py-2.5 rounded-2xl border border-gray-200 bg-white hover:bg-gray-50 text-sm shadow-sm transition duration-200 hover:shadow-md hover:-translate-y-0.5">Daha Fazla Yukle</button>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .line-title {
        display: flex;
        align-items: center;
        width: 100%;
    }
    .line__container {
        height: 40px;
        width: calc(100% - 32px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 12px;
        border-radius: 8px;
    }
    .line__title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: .02em;
        color: #111827;
    }
    .line__link {
        text-decoration: none;
        color: #111827;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .line--text {
        padding: 2px 6px;
        border-radius: 4px;
    }
    #eco-top-slider {
        user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
    #eco-top-slider a,
    #eco-top-slider img {
        user-drag: none;
        -webkit-user-drag: none;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const slider = document.getElementById('eco-top-slider');
        const track = document.getElementById('eco-top-track');
        if (slider && track) {
            const slides = track.children.length;
            if (slides > 1) {
                let index = 0;
                let timer = null;
                let isDown = false;
                let startX = 0;
                let startTranslate = 0;
                let dragged = false;

                function goTo(i) {
                    index = (i + slides) % slides;
                    const width = slider.clientWidth;
                    track.style.transform = `translateX(${-width * index}px)`;
                    document.querySelectorAll('.eco-top-dot').forEach((d, idx) => {
                        d.style.background = idx === index ? '#111827' : '#fff';
                        d.style.color = idx === index ? '#fff' : '#111827';
                        d.style.borderColor = idx === index ? '#111827' : '#dbe2ea';
                    });
                }

                function start() {
                    if (timer) return;
                    timer = setInterval(() => goTo(index + 1), 6000);
                }

                function stop() {
                    if (timer) clearInterval(timer);
                    timer = null;
                }

                const prev = document.querySelector('.eco-top-prev');
                const next = document.querySelector('.eco-top-next');
                if (prev) prev.addEventListener('click', () => { stop(); goTo(index - 1); start(); });
                if (next) next.addEventListener('click', () => { stop(); goTo(index + 1); start(); });

                document.querySelectorAll('.eco-top-dot').forEach((d) => {
                    d.addEventListener('click', () => { stop(); goTo(Number(d.dataset.index || 0)); start(); });
                });

                slider.addEventListener('mouseenter', stop);
                slider.addEventListener('mouseleave', start);

                slider.addEventListener('mousedown', (e) => {
                    isDown = true;
                    dragged = false;
                    slider.style.cursor = 'grabbing';
                    startX = e.pageX;
                    const current = track.style.transform || 'translateX(0)';
                    startTranslate = parseInt(current.replace(/[^\d.-]/g, ''), 10) || 0;
                    track.style.transition = 'none';
                    stop();
                });

                slider.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const delta = e.pageX - startX;
                    if (Math.abs(delta) > 5) dragged = true;
                    track.style.transform = `translateX(${startTranslate + delta}px)`;
                });

                function endDrag() {
                    if (!isDown) return;
                    isDown = false;
                    slider.style.cursor = 'grab';
                    track.style.transition = 'transform 450ms ease';
                    const width = slider.clientWidth;
                    const current = parseInt((track.style.transform || 'translateX(0)').replace(/[^\d.-]/g, ''), 10) || 0;
                    const nearest = Math.round(Math.abs(current) / width);
                    goTo(nearest);
                    start();
                }

                slider.addEventListener('mouseup', endDrag);
                slider.addEventListener('mouseleave', endDrag);
                slider.addEventListener('click', (e) => {
                    if (!dragged) return;
                    e.preventDefault();
                    e.stopPropagation();
                }, true);

                goTo(0);
                start();
            }
        }

        const loadBtn = document.getElementById('economy-load-more');
        const feed = document.getElementById('economy-feed');
        const wrap = document.getElementById('economy-load-wrap');
        if (!loadBtn || !feed || !wrap) return;

        let nextPage = {{ $posts->hasMorePages() ? (int) ($posts->currentPage() + 1) : 'null' }};
        let pending = false;
        const baseUrl = @json($category->frontend_url);

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
