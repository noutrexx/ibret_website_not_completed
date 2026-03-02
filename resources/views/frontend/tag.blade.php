@extends('frontend.layouts.app')

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        <section class="mb-4 bg-white border rounded-lg p-3">
            <div class="text-xs uppercase tracking-wide text-gray-500">Etiket</div>
            <div class="text-2xl font-semibold mt-1">#{{ $tag }}</div>
            <div class="text-sm text-gray-500 mt-2">Toplam {{ number_format((int) $posts->total()) }} haber</div>
        </section>

        <section style="display:grid;grid-template-columns:720px 320px;column-gap:20px;width:1060px;max-width:100%;">
            <div style="width:720px;">
                <section class="border bg-white rounded-lg overflow-hidden relative" style="height:460px;width:100%;">
                    @if(($sliderPosts ?? collect())->isNotEmpty())
                        <div style="height:402px;overflow:hidden;" id="tag-hero-slider">
                            <div class="slider-track" id="tag-hero-track" style="display:flex;width:100%;height:402px;transition:transform 400ms ease;cursor:grab;">
                                @foreach($sliderPosts as $post)
                                    <a href="{{ $post->frontend_url }}" class="snap-center block h-full" style="flex:0 0 100%;width:100%;" draggable="false">
                                        <div style="height:402px;display:flex;align-items:center;justify-content:center;position:relative;">
                                            @if($post->image)
                                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:402px;object-fit:cover;display:block;" draggable="false">
                                            @endif
                                            <div style="position:absolute;left:0;right:0;bottom:0;height:110px;background:linear-gradient(to top, rgba(0,0,0,.68), rgba(0,0,0,0));"></div>
                                            <div style="position:absolute;left:20px;right:20px;bottom:16px;color:#fff;font-size:22px;font-weight:700;line-height:1.25;">
                                                {{ $post->title }}
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div style="height:58px;width:100%;border-top:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <button class="tag-hero-prev" style="width:34px;height:34px;border:1px solid #111;background:#fff;border-radius:4px;font-size:16px;"><</button>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    @foreach($sliderPosts as $index => $p)
                                        <button class="tag-hero-dot" style="width:24px;height:24px;border:1px solid #111;background:#fff;border-radius:3px;font-size:12px;line-height:22px;" data-index="{{ $index }}">{{ $index + 1 }}</button>
                                    @endforeach
                                </div>
                                <button class="tag-hero-next" style="width:34px;height:34px;border:1px solid #111;background:#fff;border-radius:4px;font-size:16px;">></button>
                            </div>
                        </div>
                    @else
                        <div class="h-full flex items-center justify-center text-sm text-gray-400">Etiket slideri icin yeterli haber yok</div>
                    @endif
                </section>

                <section style="margin-top:20px;">
                    <div id="tag-feed">
                        @include('frontend.partials.category-list-items', ['posts' => $posts])
                    </div>

                    <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400 my-4" style="height:120px;">
                        740x120 In-Feed Reklam Alani
                    </div>

                    <div id="tag-feed-loading" class="text-sm text-gray-500 py-3 hidden">Daha fazla haber yukleniyor...</div>
                    <div id="tag-feed-sentinel" class="h-6"></div>
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

@push('styles')
<style>
    #tag-hero-slider {
        user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
    #tag-hero-slider a, #tag-hero-slider img {
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

        setupSlider('tag-hero-slider', 5000, '.tag-hero-prev', '.tag-hero-next', '.tag-hero-dot', 'tag-hero-track');

        const feed = document.getElementById('tag-feed');
        const loading = document.getElementById('tag-feed-loading');
        const sentinel = document.getElementById('tag-feed-sentinel');
        if (!feed || !loading || !sentinel) return;

        let nextPage = {{ $posts->hasMorePages() ? (int) ($posts->currentPage() + 1) : 'null' }};
        let pending = false;
        const baseUrl = @json(route('tag.show', $tag));

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
