<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.seo')

        @php
            $faviconValue = trim((string) (setting('site_favicon') ?? ''));
            if ($faviconValue === '') {
                $faviconValue = trim((string) \App\Models\Setting::whereRaw("TRIM(`key`) = 'site_favicon'")->value('value'));
            }
            if ($faviconValue === '') {
                $faviconValue = trim((string) \App\Models\Setting::where('key', 'like', '%favicon%')->value('value'));
            }
            $faviconUrl = null;
            if ($faviconValue !== '') {
                $faviconUrl = filter_var($faviconValue, FILTER_VALIDATE_URL)
                    ? $faviconValue
                    : \Illuminate\Support\Facades\Storage::url($faviconValue);
            }
        @endphp
        @if($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 text-gray-900">
        @php
            $routeName = request()->route()?->getName();
            $routeSlug = (string) request()->route('slug');
            $routeParentSlug = (string) request()->route('parentSlug');
            $sportsSlug = (string) ($sportsNavSlug ?? 'spor');
            $economySlug = (string) ($economyNavSlug ?? 'ekonomi');
            $isSportsContext = str_starts_with((string) $routeName, 'category.sports.')
                || ($routeName === 'category.show' && $routeSlug === $sportsSlug)
                || ($routeName === 'category.legacy' && $routeSlug === $sportsSlug)
                || ($routeName === 'category.child.show' && $routeParentSlug === $sportsSlug);
            $isEconomyContext = str_starts_with((string) $routeName, 'category.economy.')
                || ($routeName === 'category.show' && $routeSlug === $economySlug)
                || ($routeName === 'category.legacy' && $routeSlug === $economySlug)
                || ($routeName === 'category.child.show' && $routeParentSlug === $economySlug);

            $headerInlineStyle = '';
            if ($isSportsContext) {
                $headerInlineStyle = 'background:linear-gradient(90deg,#f4c430 0%,#f8d45f 100%);border-bottom:1px solid #d4a216;';
            } elseif ($isEconomyContext) {
                $headerInlineStyle = 'background:linear-gradient(90deg,#16a34a 0%,#22c55e 100%);border-bottom:1px solid #15803d;';
            }
        @endphp
        <header class="{{ ($isSportsContext || $isEconomyContext) ? '' : 'bg-white border-b' }}" @if($headerInlineStyle !== '') style="{{ $headerInlineStyle }}margin-bottom:12px;" @else style="margin-bottom:12px;" @endif>
            <div style="max-width:1060px;width:100%;margin:0 auto;height:70px;" class="px-4">
                <div class="h-full flex items-center justify-between">
                    @php
                        $logoValue = trim((string) (setting('site_logo') ?? ''));
                        if ($logoValue === '') {
                            $logoValue = trim((string) \App\Models\Setting::whereRaw("TRIM(`key`) = 'site_logo'")->value('value'));
                        }
                        if ($logoValue === '') {
                            $logoValue = trim((string) \App\Models\Setting::where('key', 'like', '%logo%')->value('value'));
                        }
                        $logoUrl = null;
                        if ($logoValue !== '') {
                            $logoUrl = filter_var($logoValue, FILTER_VALIDATE_URL)
                                ? $logoValue
                                : \Illuminate\Support\Facades\Storage::url($logoValue);
                        }
                    @endphp
                    <div class="flex items-center gap-6" style="height:70px;">
                        <a href="/" class="flex items-center gap-3" style="height:70px;">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ setting('site_title') ?? config('app.name') }}" style="height:42px;width:auto;display:block;">
                            @else
                                <span class="text-xl font-semibold">{{ setting('site_title') ?? config('app.name') }}</span>
                            @endif
                        </a>

                        @if($isSportsContext)
                            <nav class="flex gap-4 text-sm text-gray-900 overflow-visible" style="height:70px;align-items:center;">
                                @php
                                    $sportsCategory = $sportsNavCategory;
                                    $sportsRootUrl = $sportsCategory?->frontend_url ?: url('/' . $sportsSlug);
                                    $sportsHomeActive = (($routeName === 'category.show' || $routeName === 'category.legacy') && $routeSlug === $sportsSlug)
                                        || ($routeName === 'category.child.show' && $routeParentSlug === $sportsSlug);
                                    $sportsStandingsActive = $routeName === 'category.sports.standings';
                                    $sportsFixturesActive = $routeName === 'category.sports.fixtures';
                                @endphp
                                <a href="{{ $sportsRootUrl }}" class="font-semibold whitespace-nowrap {{ $sportsHomeActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                    Spor
                                </a>
                                <a href="{{ route('category.sports.standings', ['slug' => $sportsSlug]) }}" class="whitespace-nowrap {{ $sportsStandingsActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                    Puan Durumu
                                </a>
                                <a href="{{ route('category.sports.fixtures', ['slug' => $sportsSlug]) }}" class="whitespace-nowrap {{ $sportsFixturesActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                    Fikstur
                                </a>
                                @if(($sportsCategory?->children ?? collect())->isNotEmpty())
                                    @foreach($sportsCategory->children as $sub)
                                        @php
                                            $sportsSubActive = $routeName === 'category.child.show' && $routeParentSlug === $sportsSlug && $routeSlug === $sub->slug;
                                        @endphp
                                        <a href="{{ route('category.child.show', ['parentSlug' => $sportsSlug, 'slug' => $sub->slug]) }}" class="whitespace-nowrap {{ $sportsSubActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                            {{ $sub->name }}
                                        </a>
                                    @endforeach
                                @endif
                            </nav>
                        @elseif($isEconomyContext)
                            <nav class="flex gap-4 text-sm text-gray-900 overflow-visible" style="height:70px;align-items:center;">
                                @php
                                    $economyCategory = $economyNavCategory;
                                    $economyRootUrl = $economyCategory?->frontend_url ?: url('/' . $economySlug);
                                    $reservedEconomySlugs = ['doviz', 'döviz', 'altin', 'altın', 'kripto', 'crypto'];
                                    $economyHomeActive = (($routeName === 'category.show' || $routeName === 'category.legacy') && $routeSlug === $economySlug)
                                        || ($routeName === 'category.child.show' && $routeParentSlug === $economySlug && !in_array(mb_strtolower((string) $routeSlug, 'UTF-8'), $reservedEconomySlugs, true));
                                    $economyCurrencyActive = $routeName === 'category.economy.currencies';
                                    $economyGoldActive = $routeName === 'category.economy.gold';
                                    $economyCryptoActive = $routeName === 'category.economy.crypto';
                                @endphp
                                <a href="{{ $economyRootUrl }}" class="font-semibold whitespace-nowrap {{ $economyHomeActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                    Ekonomi
                                </a>
                                <a href="{{ route('category.economy.currencies', ['slug' => $economySlug]) }}" class="whitespace-nowrap {{ $economyCurrencyActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                    Doviz
                                </a>
                                <a href="{{ route('category.economy.gold', ['slug' => $economySlug]) }}" class="whitespace-nowrap {{ $economyGoldActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                    Altin
                                </a>
                                <a href="{{ route('category.economy.crypto', ['slug' => $economySlug]) }}" class="whitespace-nowrap {{ $economyCryptoActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                    Kripto
                                </a>
                                @if(($economyCategory?->children ?? collect())->isNotEmpty())
                                    @foreach($economyCategory->children as $sub)
                                        @continue(in_array(mb_strtolower((string) $sub->slug, 'UTF-8'), $reservedEconomySlugs, true))
                                        @php
                                            $economySubActive = $routeName === 'category.child.show' && $routeParentSlug === $economySlug && $routeSlug === $sub->slug;
                                        @endphp
                                        <a href="{{ route('category.child.show', ['parentSlug' => $economySlug, 'slug' => $sub->slug]) }}" class="whitespace-nowrap {{ $economySubActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-900/90 hover:bg-white/30' }}" style="height:38px;display:flex;align-items:center;padding:0 14px;border-radius:999px;">
                                            {{ $sub->name }}
                                        </a>
                                    @endforeach
                                @endif
                            </nav>
                        @else
                            <nav class="flex gap-4 text-sm text-gray-700 overflow-visible" style="height:70px;align-items:center;">
                            @if(($navCategories ?? collect())->isNotEmpty())
                                @foreach($navCategories as $cat)
                                    <div class="relative group" style="height:70px;display:flex;align-items:center;">
                                        <a href="{{ $cat->frontend_url }}" class="hover:text-gray-900 whitespace-nowrap" style="height:70px;display:flex;align-items:center;">
                                            {{ $cat->name }}
                                        </a>
                                        @if(($cat->children ?? collect())->isNotEmpty())
                                            <div class="hidden group-hover:block absolute left-0 top-[68px] bg-white border rounded shadow z-50 min-w-[220px]">
                                                @foreach($cat->children as $sub)
                                                    <a href="{{ route('category.child.show', ['parentSlug' => $cat->slug, 'slug' => $sub->slug]) }}" class="block px-3 py-2 text-sm hover:bg-gray-50">{{ $sub->name }}</a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <span class="text-gray-400 text-xs">Kategori yok</span>
                            @endif
                            </nav>
                        @endif
                    </div>

                    <button id="search-open" class="{{ ($isSportsContext || $isEconomyContext) ? 'text-gray-900 hover:text-black' : 'text-gray-700 hover:text-gray-900' }}" aria-label="Ara">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <div id="search-overlay" class="fixed inset-0 bg-black/50 hidden z-50">
            <div class="absolute inset-0" id="search-close"></div>
            <div class="relative max-w-[720px] mx-auto mt-20 bg-white rounded-xl shadow-lg border p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm font-semibold">Haberlerde Ara</div>
                    <button id="search-close-btn" class="text-gray-500 hover:text-gray-800">Kapat</button>
                </div>
                <form action="{{ route('search') }}" method="GET" class="flex gap-2">
                    <input type="text" name="q" value="{{ request('q') }}" id="search-input"
                           placeholder="Başlık, konu, kişi..." class="border rounded px-3 py-2 text-sm w-full">
                    <button class="bg-gray-900 text-white text-sm px-4 py-2 rounded">Ara</button>
                </form>
                <div class="mt-4">
                    <div class="text-xs text-gray-500 mb-2">Öneriler</div>
                    <div class="flex flex-wrap gap-2">
                        <span class="text-xs px-3 py-1 rounded-full border bg-gray-50">Gündem</span>
                        <span class="text-xs px-3 py-1 rounded-full border bg-gray-50">Ekonomi</span>
                        <span class="text-xs px-3 py-1 rounded-full border bg-gray-50">Spor</span>
                        <span class="text-xs px-3 py-1 rounded-full border bg-gray-50">Teknoloji</span>
                    </div>
                </div>
            </div>
        </div>

        <main class="py-8" style="max-width:1060px;width:100%;margin:0 auto;padding-left:0;padding-right:0;">
            @yield('content')
        </main>

        <footer class="border-t bg-white">
            <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-gray-600">
                © {{ date('Y') }} {{ setting('site_title') ?? config('app.name') }}
            </div>
        </footer>
        @stack('styles')
        @stack('scripts')
        <script>
            (function () {
                const overlay = document.getElementById('search-overlay');
                const openBtn = document.getElementById('search-open');
                const closeBtn = document.getElementById('search-close');
                const closeBtn2 = document.getElementById('search-close-btn');
                const input = document.getElementById('search-input');

                function open() {
                    overlay.classList.remove('hidden');
                    setTimeout(() => input && input.focus(), 50);
                }
                function close() {
                    overlay.classList.add('hidden');
                }

                if (openBtn) openBtn.addEventListener('click', open);
                if (closeBtn) closeBtn.addEventListener('click', close);
                if (closeBtn2) closeBtn2.addEventListener('click', close);
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') close();
                });
            })();
        </script>
    </body>
</html>
