@extends('frontend.layouts.app')

@section('content')
    @php
        $standingsFeedBaseUrl = route('category.sports.standings', ['slug' => $category->slug, 'league' => $currentLeagueKey]);
    @endphp

    <div style="width:1060px;max-width:100%;margin:0 auto;">
        <section style="display:grid;grid-template-columns:720px 320px;column-gap:20px;width:1060px;max-width:100%;">
            <div style="width:720px;">
                <section class="border bg-white rounded-lg overflow-hidden" style="width:100%;">
                    <div class="px-4 py-3 border-b">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div>
                                <div class="font-semibold">{{ $currentLeagueName ?? 'Lig' }} Puan Durumu</div>
                                @if(!empty($apiFootballError))
                                    <div class="mt-1 text-xs text-red-600">API uyarisi: {{ $apiFootballError }}</div>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">Takim Istatistikleri</div>
                        </div>
                    </div>

                    @if(!empty($availableLeagues))
                        <div class="px-4 py-3 border-b bg-gray-50">
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableLeagues as $league)
                                    @php
                                        $leagueIconRel = 'images/teams/' . ($league['key'] ?? '') . '.png';
                                        $leagueIcon = is_file(public_path($leagueIconRel)) ? asset($leagueIconRel) : null;
                                    @endphp
                                    <a href="{{ route('category.sports.standings', ['slug' => $category->slug, 'league' => $league['key']]) }}"
                                       class="px-2 py-1.5 rounded-md text-sm inline-flex items-center justify-center {{ ($currentLeagueKey ?? '') === $league['key'] ? 'bg-gray-100 ring-1 ring-gray-300' : 'bg-transparent hover:bg-gray-50' }}"
                                       title="{{ $league['name'] }}">
                                        @if($leagueIcon)
                                            <img src="{{ $leagueIcon }}" alt="{{ $league['name'] }}" style="width:28px;height:28px;object-fit:contain;flex-shrink:0;">
                                        @else
                                            <span class="text-[10px] font-semibold">{{ mb_strtoupper(mb_substr($league['name'], 0, 2, 'UTF-8'), 'UTF-8') }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="px-4 py-2 border-b bg-gray-50 text-xs font-semibold text-gray-600">
                        <div style="display:grid;grid-template-columns:34px minmax(0,1fr) 34px 34px 34px 34px 48px 38px;column-gap:8px;align-items:center;">
                            <div>#</div>
                            <div>Takim</div>
                            <div class="text-center">O</div>
                            <div class="text-center">G</div>
                            <div class="text-center">B</div>
                            <div class="text-center">M</div>
                            <div class="text-center">AV</div>
                            <div class="text-center">P</div>
                        </div>
                    </div>

                    @if(empty($trStandings))
                        <div class="p-4 text-sm text-gray-500">Puan durumu verisi alinamadi.</div>
                    @else
                        <div>
                            @foreach($trStandings as $row)
                                @php
                                    $teamName = (string) ($row['strTeam'] ?? '-');
                                    $logoUrl = team_logo_url($teamName);
                                @endphp
                                <div class="px-4 py-2 border-b last:border-b-0 text-sm">
                                    <div style="display:grid;grid-template-columns:34px minmax(0,1fr) 34px 34px 34px 34px 48px 38px;column-gap:8px;align-items:center;">
                                        <div class="text-gray-500 text-right">{{ $row['intRank'] ?? '-' }}</div>
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if($logoUrl)
                                                <img src="{{ $logoUrl }}" alt="{{ $teamName }}" style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                            @else
                                                <span style="width:18px;height:18px;border-radius:50%;background:#f3f4f6;color:#6b7280;font-size:10px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    {{ mb_strtoupper(mb_substr($teamName, 0, 1, 'UTF-8'), 'UTF-8') }}
                                                </span>
                                            @endif
                                            <span class="truncate font-medium text-gray-900">{{ $teamName }}</span>
                                        </div>
                                        <div class="text-center text-gray-600">{{ $row['intPlayed'] ?? '-' }}</div>
                                        <div class="text-center text-gray-600">{{ $row['intWon'] ?? '-' }}</div>
                                        <div class="text-center text-gray-600">{{ $row['intDrawn'] ?? '-' }}</div>
                                        <div class="text-center text-gray-600">{{ $row['intLost'] ?? '-' }}</div>
                                        <div class="text-center text-gray-600">{{ $row['intGoalDifference'] ?? '-' }}</div>
                                        <div class="text-center font-semibold text-gray-900">{{ $row['intPoints'] ?? '-' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section style="margin-top:20px;">
                    <div id="sports-standings-feed">
                        @include('frontend.partials.category-list-items', ['posts' => $posts])
                    </div>

                    <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400 my-4" style="height:120px;">
                        740x120 In-Feed Reklam Alani
                    </div>

                    <div id="sports-standings-feed-loading" class="text-sm text-gray-500 py-3 hidden">Daha fazla haber yukleniyor...</div>
                    <div id="sports-standings-feed-sentinel" class="h-6"></div>
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

                <section class="space-y-5" style="margin-top:16px;">
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
        const feed = document.getElementById('sports-standings-feed');
        const loading = document.getElementById('sports-standings-feed-loading');
        const sentinel = document.getElementById('sports-standings-feed-sentinel');
        if (!feed || !loading || !sentinel) return;

        let nextPage = {{ $posts->hasMorePages() ? (int) ($posts->currentPage() + 1) : 'null' }};
        let pending = false;
        const baseUrl = @json($standingsFeedBaseUrl);

        async function loadMore() {
            if (!nextPage || pending) return;
            pending = true;
            loading.classList.remove('hidden');

            try {
                const url = `${baseUrl}${baseUrl.includes('?') ? '&' : '?'}page=${nextPage}&feed=1`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                if (data.html) feed.insertAdjacentHTML('beforeend', data.html);
                nextPage = data.hasMore ? data.nextPage : null;
                if (!nextPage) sentinel.remove();
            } catch (e) {
                nextPage = null;
                sentinel.remove();
            } finally {
                pending = false;
                loading.classList.add('hidden');
            }
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => { if (entry.isIntersecting) loadMore(); });
        }, { rootMargin: '300px 0px' });
        observer.observe(sentinel);
    })();
</script>
@endpush
