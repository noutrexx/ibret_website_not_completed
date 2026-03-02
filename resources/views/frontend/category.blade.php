@extends('frontend.layouts.app')

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        @include('frontend.partials.subcategory-nav', ['category' => $category])

        <section style="display:grid;grid-template-columns:720px 320px;column-gap:20px;width:1060px;max-width:100%;">
            <div style="width:720px;">
                <section class="bg-white rounded-2xl overflow-hidden relative shadow-sm border border-gray-100" style="height:460px;width:100%;">
                    @if(($sliderPosts ?? collect())->isNotEmpty())
                        <div style="height:402px;overflow:hidden;" id="category-hero-slider">
                            <div class="slider-track" id="category-hero-track" style="display:flex;width:100%;height:402px;transition:transform 520ms cubic-bezier(.22,.61,.36,1);cursor:grab;">
                                @foreach($sliderPosts as $post)
                                    <a href="{{ $post->frontend_url }}" class="snap-center block h-full" style="flex:0 0 100%;width:100%;" draggable="false">
                                        <div style="height:402px;display:flex;align-items:center;justify-content:center;position:relative;">
                                            @if($post->image)
                                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:402px;object-fit:cover;display:block;" draggable="false">
                                            @endif
                                            <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(0,0,0,.08) 0%, rgba(0,0,0,.18) 40%, rgba(0,0,0,.78) 100%);"></div>
                                            <div style="position:absolute;left:18px;top:16px;display:flex;align-items:center;gap:8px;">
                                                <span style="display:inline-flex;align-items:center;height:26px;padding:0 10px;border-radius:999px;background:rgba(255,255,255,.18);backdrop-filter:blur(6px);color:#fff;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">
                                                    {{ $post->category?->name ?? ($category->name ?? 'Kategori') }}
                                                </span>
                                                <span style="color:rgba(255,255,255,.9);font-size:12px;font-weight:500;">
                                                    {{ optional($post->published_at ?? $post->created_at)->format('d.m.Y H:i') }}
                                                </span>
                                            </div>
                                            <div style="position:absolute;left:20px;right:20px;bottom:18px;color:#fff;">
                                                <div style="font-size:25px;font-weight:800;line-height:1.18;text-shadow:0 2px 18px rgba(0,0,0,.35);">
                                                    {{ $post->title }}
                                                </div>
                                                @if(!empty($post->summary))
                                                    <div style="margin-top:8px;font-size:13px;line-height:1.45;color:rgba(255,255,255,.9);max-width:80%;">
                                                        {{ \Illuminate\Support\Str::limit(strip_tags($post->summary), 140) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div style="height:58px;width:100%;border-top:1px solid #eef2f7;display:flex;align-items:center;justify-content:center;background:#fbfcfe;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <button class="cat-hero-prev" style="width:36px;height:36px;border:1px solid #dbe2ea;background:#fff;border-radius:999px;font-size:16px;line-height:1;display:flex;align-items:center;justify-content:center;color:#111827;box-shadow:0 1px 2px rgba(16,24,40,.06);">&lt;</button>
                                <div style="display:flex;align-items:center;gap:6px;padding:4px 6px;border-radius:999px;background:#fff;border:1px solid #e6ebf1;">
                                    @foreach($sliderPosts as $index => $p)
                                        <button class="cat-hero-dot" style="width:28px;height:28px;border:1px solid #dbe2ea;background:#fff;border-radius:999px;font-size:12px;font-weight:700;line-height:26px;color:#374151;" data-index="{{ $index }}">{{ $index + 1 }}</button>
                                    @endforeach
                                </div>
                                <button class="cat-hero-next" style="width:36px;height:36px;border:1px solid #dbe2ea;background:#fff;border-radius:999px;font-size:16px;line-height:1;display:flex;align-items:center;justify-content:center;color:#111827;box-shadow:0 1px 2px rgba(16,24,40,.06);">&gt;</button>
                            </div>
                        </div>
                    @else
                        <div class="h-full flex items-center justify-center text-sm text-gray-400">740x460 Slider</div>
                    @endif
                </section>

                <section style="margin-top:20px;">
                    <div id="category-feed">
                        @include('frontend.partials.category-list-items', ['posts' => $posts])
                    </div>

                    <div class="bg-white rounded-xl flex items-center justify-center text-sm text-gray-400 my-5 shadow-sm border border-gray-100" style="height:120px;">
                        740x120 In-Feed Reklam Alani
                    </div>

                    <div id="category-feed-loading" class="text-sm text-gray-500 py-3 hidden">Daha fazla haber yukleniyor...</div>
                    <div id="category-feed-sentinel" class="h-6"></div>
                </section>
            </div>

            <aside style="width:320px;">
                <section class="mb-4">
                    <div class="text-sm font-semibold text-gray-800 mb-3">Haftanin En Cok Okunanlari</div>
                    <div class="space-y-5">
                        @forelse(($weeklyPopularPosts ?? collect()) as $post)
                            <a href="{{ $post->frontend_url }}" class="block rounded-2xl overflow-hidden relative shadow-sm border border-gray-100 transition duration-200 hover:-translate-y-0.5 hover:shadow-md" style="width:100%;height:220px;">
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:220px;object-fit:cover;display:block;">
                                @endif
                                <div style="position:absolute;left:0;right:0;bottom:0;height:90px;background:linear-gradient(to top, rgba(0,0,0,.72), rgba(0,0,0,0));"></div>
                                <div style="position:absolute;left:14px;right:14px;bottom:12px;color:#fff;font-size:15px;font-weight:600;line-height:1.3;">
                                    {{ $post->title }}
                                </div>
                            </a>
                        @empty
                            <div class="text-sm text-gray-500 bg-white rounded-2xl p-4 shadow-sm border border-gray-100">Bu hafta icin populer haber bulunamadi.</div>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-6 sticky" style="top:84px;margin-top:20px;">
                    <div class="bg-white rounded-2xl flex items-center justify-center text-sm text-gray-400 shadow-sm border border-gray-100" style="height:100px;">
                        320x100 Header Alti Reklam
                    </div>
                    <div class="bg-white rounded-2xl flex items-center justify-center text-sm text-gray-400 shadow-sm border border-gray-100" style="height:250px;">
                        320x250 Sidebar Reklam
                    </div>
                    <div class="bg-white rounded-2xl flex items-center justify-center text-sm text-gray-400 shadow-sm border border-gray-100" style="height:600px;">
                        300x600 Skyscraper Reklam
                    </div>
                </section>
            </aside>
        </section>
    </div>
@endsection

@push('styles')
<style>
    #category-hero-slider {
        user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
    #category-hero-slider a, #category-hero-slider img {
        user-drag: none;
        -webkit-user-drag: none;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        function setupSlider(id, interval, prevBtn, nextBtn, dotSelector, trackId) {
            const slider = document.getElementById(id);
            const track = document.getElementById(trackId);
            if (!slider || !track) return;
            const slides = track.children.length;
            if (slides <= 1) return;

            let index = 0;
            let timer = null;
            let isDown = false;
            let dragged = false;
            let startX = 0;
            let startTranslate = 0;

            function goTo(i) {
                index = (i + slides) % slides;
                const width = slider.clientWidth;
                track.style.transform = `translateX(${-width * index}px)`;
                document.querySelectorAll(dotSelector).forEach((d, idx) => {
                    d.classList.toggle('bg-gray-900', idx === index);
                    d.classList.toggle('text-white', idx === index);
                    d.classList.toggle('border-gray-900', idx === index);
                    d.classList.toggle('shadow-sm', idx === index);
                });
            }

            function start() {
                if (timer) return;
                timer = setInterval(() => goTo(index + 1), interval);
            }

            function stop() {
                if (timer) clearInterval(timer);
                timer = null;
            }

            const prev = document.querySelector(prevBtn);
            const next = document.querySelector(nextBtn);
            if (prev) prev.addEventListener('click', () => { stop(); goTo(index - 1); start(); });
            if (next) next.addEventListener('click', () => { stop(); goTo(index + 1); start(); });

            document.querySelectorAll(dotSelector).forEach(d => {
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
                track.style.transition = 'transform 400ms ease';
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

        setupSlider('category-hero-slider', 5000, '.cat-hero-prev', '.cat-hero-next', '.cat-hero-dot', 'category-hero-track');

        const feed = document.getElementById('category-feed');
        const loading = document.getElementById('category-feed-loading');
        const sentinel = document.getElementById('category-feed-sentinel');
        if (!feed || !loading || !sentinel) return;

        let nextPage = {{ $posts->hasMorePages() ? (int) ($posts->currentPage() + 1) : 'null' }};
        let pending = false;
        const baseUrl = @json($category->frontend_url);

        async function loadMore() {
            if (!nextPage || pending) return;
            pending = true;
            loading.classList.remove('hidden');

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
                    sentinel.remove();
                }
            } catch (e) {
                nextPage = null;
                sentinel.remove();
            } finally {
                pending = false;
                loading.classList.add('hidden');
            }
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) loadMore();
            });
        }, { rootMargin: '300px 0px' });
        observer.observe(sentinel);
    })();
</script>
@endpush


