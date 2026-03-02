@extends('frontend.layouts.app')

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        @php
            $fixtures = collect($trFixtures ?? []);
            $roundGroups = $fixtures
                ->groupBy(fn ($row) => (string) ($row['round'] ?? 'Diger'))
                ->map(fn ($rows, $round) => ['round' => $round, 'rows' => $rows->values()]);

            $orderedRounds = $roundGroups
                ->sortBy(function ($group) {
                    $label = (string) ($group['round'] ?? '');
                    if (preg_match('/(\d+)/', $label, $m)) {
                        return (int) $m[1];
                    }
                    return PHP_INT_MAX;
                })
                ->values();

            $selectedRound = (string) request('round', '');
            if ($selectedRound === '' || !$orderedRounds->pluck('round')->contains($selectedRound)) {
                $upcomingGroup = $orderedRounds->first(function ($group) {
                    return collect($group['rows'] ?? [])->contains(fn ($row) => ($row['status'] ?? '') !== 'finished');
                });
                $selectedRound = (string) (($upcomingGroup['round'] ?? null) ?: ($orderedRounds->last()['round'] ?? ($orderedRounds->first()['round'] ?? '')));
            }
            $selectedGroup = $orderedRounds->firstWhere('round', $selectedRound);
            $selectedRows = collect($selectedGroup['rows'] ?? []);
            $roundLabel = $selectedRound;
            if (preg_match('/^Round\\s+(\\d+)$/i', (string) $selectedRound, $m)) {
                $roundLabel = 'Hafta ' . $m[1];
            }
            $fixturesFeedBaseUrl = route('category.sports.fixtures', ['slug' => $category->slug, 'league' => $currentLeagueKey, 'round' => $selectedRound]);
        @endphp

        <section style="display:grid;grid-template-columns:720px 320px;column-gap:20px;width:1060px;max-width:100%;">
            <div style="width:720px;">
                <section class="border bg-white rounded-lg overflow-hidden" style="width:100%;">
                    <div class="px-4 py-3 border-b flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ $currentLeagueName ?? 'Lig' }} Fiksturu</div>
                            @if(!empty($apiFootballError))
                                <div class="mt-1 text-xs text-red-600">API uyarisi: {{ $apiFootballError }}</div>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">{{ $selectedRound !== '' ? $roundLabel : 'Tum Haftalar' }}</div>
                    </div>

                    @if(!empty($availableLeagues))
                        <div class="px-4 py-3 border-b bg-gray-50">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-2">Lig Sec</div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($availableLeagues as $league)
                                            @php
                                                $leagueIconRel = 'images/teams/' . ($league['key'] ?? '') . '.png';
                                                $leagueIcon = is_file(public_path($leagueIconRel)) ? asset($leagueIconRel) : null;
                                            @endphp
                                            <a href="{{ route('category.sports.fixtures', ['slug' => $category->slug, 'league' => $league['key']]) }}"
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

                                @if($orderedRounds->isNotEmpty())
                                    <div class="min-w-[180px]">
                                        <label for="fixtures-round-select" class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-2">Hafta Sec</label>
                                        <select id="fixtures-round-select"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-white"
                                                onchange="if(this.value) window.location.href=this.value;">
                                            @foreach($orderedRounds as $group)
                                                @php
                                                    $roundText = (string) $group['round'];
                                                    $roundOptionLabel = $roundText;
                                                    if (preg_match('/^Round\\s+(\\d+)$/i', $roundText, $mm)) {
                                                        $roundOptionLabel = $mm[1] . '. Hafta';
                                                    }
                                                @endphp
                                                <option value="{{ route('category.sports.fixtures', ['slug' => $category->slug, 'league' => $currentLeagueKey, 'round' => $group['round']]) }}"
                                                    {{ $selectedRound === $group['round'] ? 'selected' : '' }}>
                                                    {{ $roundOptionLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div style="max-height:700px;overflow:auto;">
                        @if(empty($trFixtures))
                            <div class="p-4 text-sm text-gray-500">Fikstur verisi alinamadi.</div>
                        @elseif($selectedRows->isEmpty())
                            <div class="p-4 text-sm text-gray-500">Bu hafta icin fikstur bulunamadi.</div>
                        @else
                            <div class="px-4 py-3 border-b bg-gray-50 text-xs font-semibold text-gray-600">
                                <div style="display:grid;grid-template-columns:110px minmax(0,1fr) 86px minmax(0,1fr) 90px;column-gap:10px;align-items:center;">
                                    <div>Tarih</div>
                                    <div class="flex items-center justify-end gap-2">
                                        <span>Ev Sahibi</span>
                                        <span style="width:20px;height:20px;display:inline-block;flex-shrink:0;"></span>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:76px;">Skor</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span style="width:20px;height:20px;display:inline-block;flex-shrink:0;"></span>
                                        <span>Deplasman</span>
                                    </div>
                                    <div class="text-center">Durum</div>
                                </div>
                            </div>
                            @foreach($selectedRows as $row)
                                @php
                                    $homeTeam = (string) ($row['strHomeTeam'] ?? '-');
                                    $awayTeam = (string) ($row['strAwayTeam'] ?? '-');
                                    $homeLogo = team_logo_url($homeTeam);
                                    $awayLogo = team_logo_url($awayTeam);
                                    $status = (string) ($row['status'] ?? '');
                                @endphp
                                <div class="px-4 py-4 border-b last:border-b-0 text-sm">
                                    <div style="display:grid;grid-template-columns:110px minmax(0,1fr) 86px minmax(0,1fr) 90px;column-gap:10px;align-items:center;">
                                        <div class="text-xs text-gray-500">
                                            <div>{{ $row['dateEvent'] ?? '-' }}</div>
                                            <div class="mt-0.5">{{ !empty($row['strTime']) ? substr($row['strTime'], 0, 5) : '--:--' }}</div>
                                        </div>

                                        <div class="flex items-center justify-end gap-2 min-w-0">
                                            <span class="truncate text-right font-medium text-gray-900">{{ $homeTeam }}</span>
                                            @if($homeLogo)
                                                <img src="{{ $homeLogo }}" alt="{{ $homeTeam }}" style="width:20px;height:20px;object-fit:contain;flex-shrink:0;">
                                            @else
                                                <span style="width:20px;height:20px;border-radius:50%;background:#f3f4f6;color:#6b7280;font-size:10px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    {{ mb_strtoupper(mb_substr($homeTeam, 0, 1, 'UTF-8'), 'UTF-8') }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-center flex items-center justify-center">
                                            @if($status === 'finished')
                                                <div class="inline-flex items-center justify-center min-w-[76px] px-3 py-2 rounded-md bg-gray-100 text-gray-900 font-semibold text-sm border border-gray-200">
                                                    {{ $row['intHomeScore'] ?? '-' }} - {{ $row['intAwayScore'] ?? '-' }}
                                                </div>
                                            @else
                                                <div class="inline-flex items-center justify-center min-w-[76px] px-3 py-2 rounded-md bg-blue-50 text-blue-700 font-semibold text-xs border border-blue-100">
                                                    {{ !empty($row['strTime']) ? substr($row['strTime'], 0, 5) : 'TBA' }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2 min-w-0">
                                            @if($awayLogo)
                                                <img src="{{ $awayLogo }}" alt="{{ $awayTeam }}" style="width:20px;height:20px;object-fit:contain;flex-shrink:0;">
                                            @else
                                                <span style="width:20px;height:20px;border-radius:50%;background:#f3f4f6;color:#6b7280;font-size:10px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    {{ mb_strtoupper(mb_substr($awayTeam, 0, 1, 'UTF-8'), 'UTF-8') }}
                                                </span>
                                            @endif
                                            <span class="truncate font-medium text-gray-900">{{ $awayTeam }}</span>
                                        </div>

                                        <div class="text-center">
                                            @if($status === 'finished')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-[11px] font-semibold">Bitti</span>
                                            @elseif($status === 'scheduled')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold">Planli</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-semibold uppercase">{{ $status }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </section>

                <section style="margin-top:20px;">
                    <div id="sports-fixtures-feed">
                        @include('frontend.partials.category-list-items', ['posts' => $posts])
                    </div>

                    <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400 my-4" style="height:120px;">
                        740x120 In-Feed Reklam Alani
                    </div>

                    <div id="sports-fixtures-feed-loading" class="text-sm text-gray-500 py-3 hidden">Daha fazla haber yukleniyor...</div>
                    <div id="sports-fixtures-feed-sentinel" class="h-6"></div>
                </section>
            </div>

            @include('frontend.sports.partials.page-sidebar', ['weeklyPopularPosts' => $weeklyPopularPosts ?? collect(), 'currentLeagueName' => $currentLeagueName ?? null, 'hideSourceBox' => true])
        </section>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const feed = document.getElementById('sports-fixtures-feed');
        const loading = document.getElementById('sports-fixtures-feed-loading');
        const sentinel = document.getElementById('sports-fixtures-feed-sentinel');
        if (!feed || !loading || !sentinel) return;

        let nextPage = {{ $posts->hasMorePages() ? (int) ($posts->currentPage() + 1) : 'null' }};
        let pending = false;
        const baseUrl = @json($fixturesFeedBaseUrl);

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
