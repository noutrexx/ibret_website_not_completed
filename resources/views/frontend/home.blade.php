@extends('frontend.layouts.app')

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400" style="height:250px;margin-top:24px;">
            1060x250 Reklam Alani
        </div>

        <section class="overflow-hidden relative" style="height:330px;margin-top:24px;border-radius:14px;background:#fff;box-shadow:0 16px 34px rgba(15,23,42,.08), 0 3px 10px rgba(15,23,42,.05);">
            @if(($topManset ?? collect())->isNotEmpty())
                @php
                    $svgColors = [
                        ['#c90914', '#83060d'],
                        ['#0b5ed7', '#0a2f6b'],
                        ['#198754', '#0f4d30'],
                        ['#fd7e14', '#8f4a0c'],
                        ['#6f42c1', '#3c1b73'],
                    ];
                @endphp
                <div class="h-full overflow-hidden" id="top-slider" style="width:100%;overflow:hidden;cursor:grab;position:relative;z-index:1;">
                    <div class="slider-track" id="top-track" style="display:flex;width:100%;height:100%;transition:transform 450ms cubic-bezier(.22,.61,.36,1);z-index:1;">
                    @foreach($topManset->take(20) as $index => $post)
                        @php
                            $pair = $svgColors[$index % count($svgColors)];
                            $gradId = 'asfer_' . $index;
                        @endphp
                        <a href="{{ $post->frontend_url }}" class="snap-center block h-full" style="flex:0 0 100%;width:100%;" draggable="false">
                            <div class="relative" style="height:330px;display:flex;">
                                <div style="width:606px;height:330px;position:relative;overflow:hidden;flex:0 0 606px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="606" height="330.08" viewBox="0 0 606 330.08" style="position:absolute;left:0;top:0;height:330px;width:606px;">
                                        <defs>
                                            <linearGradient id="{{ $gradId }}" x1="0.722" y1="0.985" x2="0.278" y2="0.015" gradientUnits="objectBoundingBox">
                                                <stop offset="0" stop-color="{{ $pair[0] }}"></stop>
                                                <stop offset="1" stop-color="{{ $pair[1] }}"></stop>
                                            </linearGradient>
                                        </defs>
                                        <path d="M719,167.04h403.977L1325,497.121H719Z" transform="translate(-719 -167.04)" fill="url(#{{ $gradId }})"></path>
                                    </svg>
                                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:24px;color:#fff;">
                                        <div style="display:inline-flex;align-items:center;gap:6px;width:max-content;padding:4px 10px;border-radius:999px;background:rgba(255,255,255,.14);backdrop-filter:blur(4px);font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;">
                                            <span style="width:6px;height:6px;border-radius:999px;background:#fff;"></span>
                                            {{ $post->category?->name ?? 'Genel' }}
                                        </div>
                                        <div class="text-2xl font-semibold leading-snug" style="margin-top:10px;text-shadow:0 2px 10px rgba(0,0,0,.22);">
                                            {{ \Illuminate\Support\Str::limit($post->title, 95) }}
                                        </div>
                                        <div style="margin-top:10px;font-size:12px;opacity:.9;line-height:1.35;max-width:340px;">
                                            {{ \Illuminate\Support\Str::limit($post->summary ?: strip_tags($post->content ?? ''), 70) }}
                                        </div>
                                    </div>
                                </div>
                                <div style="width:680px;height:330px;overflow:hidden;margin-left:-226px;">
                                    @if($post->image)
                                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:680px;height:330px;object-fit:cover;display:block;" draggable="false">
                                        <div style="position:absolute;right:0;top:0;width:680px;height:330px;background:linear-gradient(90deg, rgba(255,255,255,0) 58%, rgba(15,23,42,.04) 100%);pointer-events:none;"></div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                    </div>
                </div>
                <button class="top-prev absolute left-3 top-1/2 -translate-y-1/2" style="width:36px;height:36px;background:rgba(255,255,255,.9);border:1px solid #e5e7eb;border-radius:999px;color:#111827;box-shadow:0 4px 12px rgba(15,23,42,.08);"><</button>
                <button class="top-next absolute right-3 top-1/2 -translate-y-1/2" style="width:36px;height:36px;background:rgba(255,255,255,.9);border:1px solid #e5e7eb;border-radius:999px;color:#111827;box-shadow:0 4px 12px rgba(15,23,42,.08);">></button>
                <div class="flex flex-wrap" style="gap:6px;z-index:30;position:absolute;left:16px;bottom:12px;pointer-events:auto;background:rgba(255,255,255,.9);border:1px solid rgba(226,232,240,.9);padding:6px 8px;border-radius:999px;backdrop-filter:blur(6px);">
                    @foreach($topManset->take(20) as $dotIndex => $dotPost)
                        <button class="top-dot text-xs px-2 py-0.5 border" style="background:#fff;color:#111827;border-color:#d1d5db;min-width:26px;height:22px;border-radius:999px;font-weight:600;" data-index="{{ $dotIndex }}">{{ $dotIndex + 1 }}</button>
                    @endforeach
                </div>
            @else
                <div class="h-full flex items-center justify-center text-sm text-gray-400">1060x330 Top Manset</div>
            @endif
        </section>

        <section style="margin-top:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:10px;height:10px;border-radius:999px;background:#dc2626;box-shadow:0 0 0 6px rgba(220,38,38,.12);"></span>
                    <div style="font-size:15px;font-weight:800;color:#111827;letter-spacing:.02em;">SON DAKIKA</div>
                </div>
                <div style="font-size:12px;color:#6b7280;">En son guncellenen onemli haberler</div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
                @forelse(($breakingNewsPosts ?? collect()) as $post)
                    @php
                        $breakingTag = $post->category?->name ?? 'Gundem';
                    @endphp
                    <a href="{{ $post->frontend_url }}"
                       style="height:250px;text-decoration:none;display:flex;flex-direction:column;border-radius:12px;overflow:hidden;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.06);transition:transform .18s ease, box-shadow .18s ease;">
                        <div style="height:138px;position:relative;background:#f3f4f6;overflow:hidden;">
                            @if($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:138px;object-fit:cover;display:block;">
                            @endif
                            <div style="position:absolute;left:10px;top:10px;display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border-radius:999px;background:rgba(185,28,28,.92);color:#fff;font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;">
                                <span style="width:6px;height:6px;border-radius:999px;background:#fff;"></span>
                                Son Dakika
                            </div>
                        </div>
                        <div style="padding:12px 12px 10px;display:flex;flex-direction:column;gap:8px;flex:1;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <span style="display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;background:#f1f5f9;color:#334155;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;">{{ $breakingTag }}</span>
                                <span style="font-size:11px;color:#6b7280;">
                                    {{ $post->published_at?->format('H:i') ?? $post->created_at?->format('H:i') }}
                                </span>
                            </div>
                            <div style="font-size:14px;font-weight:700;line-height:1.35;color:#111827;">
                                {{ \Illuminate\Support\Str::limit($post->title, 78) }}
                            </div>
                            <div style="margin-top:auto;font-size:12px;color:#64748b;line-height:1.35;">
                                {{ \Illuminate\Support\Str::limit($post->summary ?: strip_tags($post->content ?? ''), 58) }}
                            </div>
                        </div>
                    </a>
                @empty
                    @for($i = 0; $i < 4; $i++)
                        <div style="height:250px;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:13px;box-shadow:0 8px 24px rgba(15,23,42,.05), 0 1px 2px rgba(15,23,42,.05);">
                            Son dakika haberi yok
                        </div>
                    @endfor
                @endforelse
            </div>
        </section>

        <section style="margin-top:24px;display:grid;grid-template-columns:660px 380px;gap:20px;width:1060px;max-width:100%;">
            <div class="overflow-hidden relative" style="height:460px;width:660px;border-radius:14px;background:#fff;box-shadow:0 14px 30px rgba(15,23,42,.08), 0 2px 6px rgba(15,23,42,.05);">
                @if(($manset ?? collect())->isNotEmpty())
                    <div style="height:402px;overflow:hidden;background:#e5e7eb;" id="hero-slider">
                        <div class="slider-track" id="hero-track" style="display:flex;width:100%;height:402px;transition:transform 450ms cubic-bezier(.22,.61,.36,1);cursor:grab;">
                        @foreach($manset->take(15) as $post)
                            <a href="{{ $post->frontend_url }}" class="snap-center block h-full" style="flex:0 0 100%;width:100%;" draggable="false">
                                <div style="height:402px;display:flex;align-items:center;justify-content:center;position:relative;">
                                    @if($post->image)
                                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:660px;height:402px;object-fit:cover;display:block;" draggable="false">
                                    @endif
                                    <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(2,6,23,0) 40%, rgba(2,6,23,.72) 100%);"></div>
                                    <div style="position:absolute;left:16px;top:14px;display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;background:rgba(255,255,255,.92);color:#111827;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;">
                                        <span style="width:6px;height:6px;border-radius:999px;background:#dc2626;"></span>
                                        Manset
                                    </div>
                                    <div style="position:absolute;left:20px;right:20px;bottom:14px;color:#fff;">
                                        <div style="font-size:18px;font-weight:700;line-height:1.25;text-shadow:0 2px 8px rgba(0,0,0,.35);">
                                            {{ \Illuminate\Support\Str::limit($post->title, 110) }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                        </div>
                    </div>
                    <div style="height:58px;width:660px;border-top:1px solid #edf2f7;display:flex;align-items:center;justify-content:center;background:#fff;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <button class="hero-prev" style="width:34px;height:34px;border:1px solid #e5e7eb;background:#fff;border-radius:999px;font-size:14px;color:#111827;"><</button>
                            <div style="display:flex;align-items:center;gap:6px;max-width:520px;overflow-x:auto;padding:2px 0;">
                                @foreach($manset->take(15) as $index => $p)
                                    <button class="hero-dot" style="min-width:24px;height:24px;border:1px solid #d1d5db;background:#fff;border-radius:999px;font-size:11px;line-height:22px;color:#111827;" data-index="{{ $index }}">{{ $index + 1 }}</button>
                                @endforeach
                            </div>
                            <button class="hero-next" style="width:34px;height:34px;border:1px solid #e5e7eb;background:#fff;border-radius:999px;font-size:14px;color:#111827;">></button>
                        </div>
                    </div>
                @else
                    <div class="h-full flex items-center justify-center text-sm text-gray-400">660x460 Manset Slider</div>
                @endif
            </div>

            <aside class="p-4 flex flex-col" style="height:460px;width:380px;border-radius:14px;background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);box-shadow:0 14px 30px rgba(15,23,42,.08), 0 2px 6px rgba(15,23,42,.05);">
                <div class="flex flex-wrap gap-2 mb-3" style="padding:4px;background:#eef2f7;border-radius:10px;margin-bottom:12px;">
                    @foreach(($featuredCategories ?? collect()) as $cat)
                        <button class="featured-tab text-xs px-3 py-1.5 rounded-md border-0 bg-gray-50"
                                data-target="cat-{{ $cat->id }}">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
                <div class="space-y-2 overflow-auto pr-1">
                    @foreach(($featuredCategories ?? collect()) as $cat)
                        <div class="featured-panel" id="cat-{{ $cat->id }}">
                            @foreach(($featuredPosts[$cat->id] ?? collect()) as $post)
                                <a href="{{ $post->frontend_url }}" class="block mb-2"
                                   style="text-decoration:none;background:#fff;border:1px solid #edf2f7;border-radius:10px;padding:9px;box-shadow:0 1px 2px rgba(15,23,42,.03);">
                                    <div style="display:grid;grid-template-columns:84px 1fr;gap:10px;align-items:start;">
                                        <div style="height:62px;border-radius:8px;overflow:hidden;background:#f1f5f9;">
                                            @if($post->image)
                                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:84px;height:62px;object-fit:cover;display:block;">
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-size:13px;font-weight:700;line-height:1.3;color:#111827;">
                                                {{ \Illuminate\Support\Str::limit($post->title, 72) }}
                                            </div>
                                            <div style="font-size:11px;color:#64748b;margin-top:6px;">
                                                {{ $post->published_at?->format('d.m.Y H:i') ?? $post->created_at?->format('d.m.Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                            @if(empty($featuredPosts[$cat->id]) || ($featuredPosts[$cat->id] ?? collect())->isEmpty())
                                <div class="text-sm text-gray-400">Bu kategoride haber yok.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </aside>
        </section>

        @php
            $sportsPosts = collect([$sportsSection['lead'] ?? null])->filter()->merge($sportsSection['items'] ?? collect())->take(8)->values();
            $economyPosts = collect([$economySection['lead'] ?? null])->filter()->merge($economySection['items'] ?? collect())->take(8)->values();
        @endphp

        <section style="margin-top:24px;width:1060px;max-width:100%;">
            <div class="line line-title" style="height:40px;margin-bottom:10px;">
                <div class="line-img" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;margin-right:8px;">
                    <img alt="SPOR HABERLERI" width="20" height="20" src="https://img.icons8.com/color/48/football2.png" style="width:20px;height:20px;display:block;border-radius:2px;">
                </div>
                <div class="line__container" style="background-color:#f5e629;">
                    <h2 class="line__title">SPOR HABERLERI</h2>
                    <a class="line__link" title="Tum Spor Haberleri" href="{{ ($sportsSection['category']->frontend_url ?? route('category.show', ['slug' => $sportsSlug ?? 'spor'])) }}">
                        <span class="line--text" style="background-color:#f5e629;">Tum Spor Haberleri</span>
                    </a>
                </div>
            </div>

            <div style="padding:4px 0 0;">
                <div id="sports-news-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                    @foreach($sportsPosts as $post)
                        @php
                            $sportTag = collect($post->tagList())->first() ?: ($post->category?->name ?? 'Spor');
                        @endphp
                        <a href="{{ $post->frontend_url }}" class="overflow-hidden" style="height:290px;display:flex;flex-direction:column;text-decoration:none;border-radius:10px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.06);transition:transform .18s ease, box-shadow .18s ease;">
                            <div style="height:210px;background:#f3f4f6;overflow:hidden;display:flex;align-items:center;justify-content:center;border-top-left-radius:10px;border-top-right-radius:10px;">
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:210px;object-fit:cover;display:block;">
                                @else
                                    <span style="font-size:28px;">âš½</span>
                                @endif
                            </div>
                            <div style="padding:12px 12px 10px;display:flex;flex-direction:column;gap:8px;flex:1;background:#fff;">
                                <div style="font-size:14px;font-weight:700;line-height:1.35;color:#111;">
                                    {{ \Illuminate\Support\Str::limit($post->title, 64) }}
                                </div>
                                <div>
                                    <span style="display:inline-flex;align-items:center;padding:3px 9px;border-radius:999px;background:#f3f4f6;color:#374151;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.02em;">
                                        {{ $sportTag }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:12px;padding:0;">
                @if(!empty($sportsStandingsHome))
                    <div id="sports-standings-cards" style="display:grid;grid-template-columns:repeat({{ max(1, count($sportsStandingsHome ?? [])) }}, minmax(0,1fr));gap:6px;align-items:stretch;">
                            @foreach(($sportsStandingsHome ?? []) as $row)
                                @php
                                    $teamName = (string) ($row['strTeam'] ?? '-');
                                    $logoUrl = team_logo_url($teamName);
                                    $teamTag = \Illuminate\Support\Str::slug($teamName);
                                @endphp
                                <a href="{{ route('tag.show', ['tag' => $teamTag]) }}"
                                   title="{{ $teamName }}"
                                   style="height:92px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-decoration:none;background:#fff;border-radius:8px;gap:5px;box-shadow:0 2px 8px rgba(15,23,42,.05);">
                                    <div style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                                        @if($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $teamName }}" style="width:28px;height:28px;object-fit:contain;">
                                        @else
                                            <span style="width:28px;height:28px;border-radius:50%;background:#f3f4f6;color:#6b7280;font-size:11px;display:inline-flex;align-items:center;justify-content:center;">
                                                {{ mb_strtoupper(mb_substr($teamName, 0, 1, 'UTF-8'), 'UTF-8') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div style="font-size:15px;color:#111;font-weight:800;line-height:1;">{{ $row['intPoints'] ?? '-' }}</div>
                                </a>
                            @endforeach
                    </div>
                @else
                    <div style="padding:10px 0;color:#9ca3af;font-size:13px;">Puan durumu verisi bulunamadi.</div>
                @endif
            </div>
        </section>

        <section style="margin-top:24px;width:1060px;max-width:100%;">
            <div class="line line-title" style="height:40px;margin-bottom:10px;">
                <div class="line-img" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;margin-right:8px;">
                    <img alt="EKONOMI HABERLERI" width="20" height="20" src="https://img.icons8.com/color/48/combo-chart--v1.png" style="width:20px;height:20px;display:block;border-radius:2px;">
                </div>
                <div class="line__container" style="background-color:#68a1ff;">
                    <h2 class="line__title">EKONOMI HABERLERI</h2>
                    <a class="line__link" title="Tum Ekonomi Haberleri" href="{{ ($economySection['category']->frontend_url ?? route('category.show', ['slug' => $economySlug ?? 'ekonomi'])) }}">
                        <span class="line--text" style="background-color:#68a1ff;">Tum Ekonomi Haberleri</span>
                    </a>
                </div>
            </div>

            <div style="padding:4px 0 0;">
                <div id="economy-news-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                    @foreach($economyPosts as $post)
                        @php
                            $economyTag = collect($post->tagList())->first() ?: ($post->category?->name ?? 'Ekonomi');
                        @endphp
                        <a href="{{ $post->frontend_url }}" class="overflow-hidden" style="height:290px;display:flex;flex-direction:column;text-decoration:none;border-radius:10px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.06);transition:transform .18s ease, box-shadow .18s ease;">
                            <div style="height:210px;background:#f3f4f6;overflow:hidden;display:flex;align-items:center;justify-content:center;border-top-left-radius:10px;border-top-right-radius:10px;">
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:210px;object-fit:cover;display:block;">
                                @else
                                    <span style="font-size:28px;">â‚º</span>
                                @endif
                            </div>
                            <div style="padding:12px 12px 10px;display:flex;flex-direction:column;gap:8px;flex:1;background:#fff;">
                                <div style="font-size:14px;font-weight:700;line-height:1.35;color:#111;">
                                    {{ \Illuminate\Support\Str::limit($post->title, 64) }}
                                </div>
                                <div>
                                    <span style="display:inline-flex;align-items:center;padding:3px 9px;border-radius:999px;background:#f3f4f6;color:#374151;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.02em;">
                                        {{ $economyTag }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:12px;padding:0;">
                <div style="border-radius:10px;overflow:hidden;background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);padding:12px;box-shadow:0 8px 22px rgba(15,23,42,.05), 0 1px 2px rgba(15,23,42,.05);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding:2px 2px 0;">
                        <div style="font-size:12px;font-weight:800;letter-spacing:.04em;color:#0f172a;text-transform:uppercase;">Piyasa Ozeti</div>
                    </div>
                    <div id="economy-widget-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;">
                        @foreach(($economyWidgetHome['rows'] ?? []) as $row)
                            @php
                                $symbol = (string) ($row['symbol'] ?? '-');
                                $name = (string) ($row['name'] ?? $symbol);
                                $sellVal = (float) ($row['selling'] ?? $row['buying'] ?? 0);
                                $buyVal = (float) ($row['buying'] ?? 0);
                                $changeVal = isset($row['change']) ? (float) $row['change'] : null;
                                $isNeg = ($changeVal ?? 0) < 0;
                            @endphp
                            <div style="min-height:138px;border-radius:10px;background:#fff;border:1px solid #eef2f7;padding:12px 10px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 2px rgba(15,23,42,.03);">
                                <div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;">
                                        <div style="font-size:11px;color:#64748b;font-weight:700;line-height:1.25;">{{ \Illuminate\Support\Str::limit($name, 18) }}</div>
                                        <span style="display:inline-flex;align-items:center;justify-content:center;padding:2px 6px;border-radius:999px;background:#f1f5f9;color:#0f172a;font-size:10px;font-weight:800;letter-spacing:.03em;">{{ $symbol }}</span>
                                    </div>
                                    <div style="margin-top:10px;font-size:21px;font-weight:800;color:#111827;line-height:1.1;">
                                        {{ number_format($sellVal, 2, ',', '.') }}
                                    </div>
                                </div>
                                <div style="margin-top:10px;display:flex;flex-direction:column;gap:4px;">
                                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#64748b;">
                                        <span>Alis</span>
                                        <span style="color:#334155;font-weight:700;">{{ number_format($buyVal, 2, ',', '.') }}</span>
                                    </div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;">
                                        <span style="color:#64748b;">Degisim</span>
                                        <span style="font-weight:800;color:{{ $changeVal === null ? '#94a3b8' : ($isNeg ? '#b91c1c' : '#047857') }};">
                                            {{ $changeVal === null ? '-' : number_format($changeVal, 2, ',', '.') . '%' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if(empty($economyWidgetHome['rows']))
                            <div style="grid-column:1 / -1;padding:16px;color:#9ca3af;font-size:13px;background:#fff;border-radius:10px;">Ekonomi verisi bulunamadi.</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section style="margin-top:24px;width:1060px;max-width:100%;position:relative;">
            <div class="border rounded-lg overflow-hidden relative" style="background:#c90914;">
                <svg xmlns="http://www.w3.org/2000/svg" width="1060" height="319.586" viewBox="0 0 1060 319.586" style="position:absolute;left:0;top:0;width:1060px;height:319.586px;z-index:0;">
                    <path d="M14,491H1074V602.441L14,810.586Z" transform="translate(-14 -491)" fill="#c90914"></path>
                </svg>
                <div style="position:relative;z-index:1;padding:22px 22px 20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;color:#fff;">
                        <div style="font-size:18px;font-weight:700;">Yazarlarimizin Kaleminden</div>
                        <a href="{{ route('authors.index') }}" style="color:#fff;font-size:12px;letter-spacing:.08em;text-transform:uppercase;display:flex;align-items:center;gap:8px;text-decoration:none;">
                            Tum Yazarlar
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="10" viewBox="0 0 14.001 10">
                                <path d="M1040,519h10.637l-2.643,2.751,1.2,1.25,3.6-3.75h0L1054,518l-4.806-5-1.2,1.249,2.643,2.751H1040Z" transform="translate(-1040 -512.999)" fill="#fff"></path>
                            </svg>
                        </a>
                    </div>

                    @if(($authorArticles ?? collect())->isNotEmpty())
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
                            @foreach($authorArticles as $post)
                                @php
                                    $author = $post->user;
                                    $authorName = $author?->author_name ?: ($author?->name ?? 'Yazar');
                                    $authorBio = $author?->bio ?: 'Yeni yazilar burada.';
                                    $excerpt = $post->summary ?: \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 140);
                                    $initial = mb_strtoupper(mb_substr($authorName, 0, 1, 'UTF-8'), 'UTF-8');
                                @endphp
                                <div style="background:#fff;border-radius:10px;overflow:hidden;border:1px solid rgba(255,255,255,.35);">
                                    <div style="display:flex;gap:12px;padding:14px 14px 10px;border-bottom:1px solid #f1f1f1;">
                                        <div style="width:64px;height:64px;border-radius:50%;overflow:hidden;background:#f3f4f6;flex:0 0 64px;display:flex;align-items:center;justify-content:center;">
                                            @if($author?->avatar)
                                                <img src="{{ asset('storage/' . $author->avatar) }}" alt="{{ $authorName }}" style="width:64px;height:64px;object-fit:cover;display:block;">
                                            @else
                                                <div style="font-weight:700;color:#6b7280;font-size:20px;">{{ $initial }}</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-weight:700;color:#111;font-size:15px;line-height:1.2;">{{ $authorName }}</div>
                                            <div style="font-size:12px;color:#6b7280;margin-top:4px;line-height:1.3;">{{ $authorBio }}</div>
                                        </div>
                                    </div>
                                    <div style="padding:12px 14px 16px;">
                                        <a href="{{ $post->frontend_url }}" style="display:block;color:#111;text-decoration:none;font-weight:600;font-size:14px;line-height:1.3;">
                                            {{ $post->title }}
                                        </a>
                                        <div style="color:#6b7280;font-size:12px;line-height:1.5;margin-top:6px;">
                                            {{ $excerpt }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="color:#fff;font-size:14px;">Henuz yazar icerikleri bulunmuyor.</div>
                    @endif
                </div>
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
        border-radius: 6px;
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
        padding: 2px 4px;
    }
    .ticker {
        animation: ticker 18s linear infinite;
    }
    @keyframes ticker {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .featured-panel { display: none; }
    #top-slider, #hero-slider {
        user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
    #top-slider a, #hero-slider a, #top-slider img, #hero-slider img {
        user-drag: none;
        -webkit-user-drag: none;
    }
    #sports-news-grid > a:hover,
    #economy-news-grid > a:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(15,23,42,.10), 0 2px 6px rgba(15,23,42,.08) !important;
    }
    section[style*="margin-top:24px;"] > div[style*="grid-template-columns:repeat(4,1fr)"] > a:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(15,23,42,.10), 0 2px 6px rgba(15,23,42,.08) !important;
    }
    @media (max-width: 900px) {
        #sports-news-grid, #economy-news-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        #economy-widget-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            overflow-y: auto;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        function setupSlider(id, interval, prevBtn, nextBtn, dotSelector, pauseOnHover, trackId) {
            const slider = document.getElementById(id);
            const track = document.getElementById(trackId);
            if (!slider) return;
            const slides = track ? track.children.length : slider.children.length;
            if (slides <= 1) return;
            let index = 0;
            let timer = null;
            let isDown = false;
            let startX = 0;
            let scrollLeft = 0;
            let dragged = false;
            let startTouchX = 0;
            let startTouchScroll = 0;

            function goTo(i) {
                index = (i + slides) % slides;
                const width = slider.clientWidth;
                if (track) {
                    track.style.transform = `translateX(${-width * index}px)`;
                }
                if (dotSelector) {
                    document.querySelectorAll(dotSelector).forEach((d, idx) => {
                        d.classList.toggle('bg-white', idx === index);
                        d.classList.toggle('bg-white/60', idx !== index);
                    });
                }
            }
            function snapToNearest() {
                const width = slider.clientWidth;
                const current = track ? Math.abs(parseInt((track.style.transform || 'translateX(0)').replace(/[^\d.-]/g, ''), 10)) : slider.scrollLeft;
                const i = Math.round(current / width);
                goTo(i);
            }

            function start() {
                if (timer) return;
                timer = setInterval(() => goTo(index + 1), interval);
            }

            function stop() {
                if (timer) clearInterval(timer);
                timer = null;
            }

            if (prevBtn) {
                const btn = document.querySelector(prevBtn);
                if (btn) btn.addEventListener('click', () => { stop(); goTo(index - 1); start(); });
            }
            if (nextBtn) {
                const btn = document.querySelector(nextBtn);
                if (btn) btn.addEventListener('click', () => { stop(); goTo(index + 1); start(); });
            }
            if (dotSelector) {
                document.querySelectorAll(dotSelector).forEach(d => {
                    d.addEventListener('click', () => { stop(); goTo(Number(d.dataset.index || 0)); start(); });
                    d.addEventListener('mouseenter', () => { stop(); goTo(Number(d.dataset.index || 0)); start(); });
                });
            }

            if (pauseOnHover) {
                slider.addEventListener('mouseenter', stop);
                slider.addEventListener('mouseleave', start);
            }

            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                dragged = false;
                slider.style.cursor = 'grabbing';
                startX = e.pageX - slider.offsetLeft;
                if (track) {
                    const current = track.style.transform || 'translateX(0)';
                    scrollLeft = Math.abs(parseInt(current.replace(/[^\d.-]/g, ''), 10)) || 0;
                    track.style.transition = 'none';
                } else {
                    scrollLeft = slider.scrollLeft;
                }
                stop();
            });
            slider.addEventListener('mouseleave', () => {
                if (!isDown) return;
                isDown = false;
                slider.style.cursor = 'grab';
                if (track) track.style.transition = 'transform 400ms ease';
                snapToNearest();
                start();
            });
            slider.addEventListener('mouseup', () => {
                isDown = false;
                slider.style.cursor = 'grab';
                if (track) track.style.transition = 'transform 400ms ease';
                snapToNearest();
                start();
            });
            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 1.2;
                if (Math.abs(walk) > 5) dragged = true;
                if (track) {
                    track.style.transform = `translateX(${- (scrollLeft - walk)}px)`;
                } else {
                    slider.scrollLeft = scrollLeft - walk;
                }
            });

            slider.addEventListener('click', (e) => {
                if (!dragged) return;
                e.preventDefault();
                e.stopPropagation();
            }, true);

            slider.addEventListener('touchstart', (e) => {
                startTouchX = e.touches[0].clientX;
                if (track) {
                    const current = track.style.transform || 'translateX(0)';
                    startTouchScroll = Math.abs(parseInt(current.replace(/[^\d.-]/g, ''), 10)) || 0;
                    track.style.transition = 'none';
                } else {
                    startTouchScroll = slider.scrollLeft;
                }
                stop();
            }, { passive: true });
            slider.addEventListener('touchmove', (e) => {
                const x = e.touches[0].clientX;
                const walk = (x - startTouchX) * 1.2;
                if (track) {
                    track.style.transform = `translateX(${- (startTouchScroll - walk)}px)`;
                } else {
                    slider.scrollLeft = startTouchScroll - walk;
                }
            }, { passive: true });
            slider.addEventListener('touchend', () => {
                if (track) track.style.transition = 'transform 400ms ease';
                snapToNearest();
                start();
            });

            goTo(0);
            start();
        }

        setupSlider('hero-slider', 5000, '.hero-prev', '.hero-next', '.hero-dot', true, 'hero-track');
        setupSlider('top-slider', 6000, '.top-prev', '.top-next', '.top-dot', true, 'top-track');

        const tabs = Array.from(document.querySelectorAll('.featured-tab'));
        const panels = Array.from(document.querySelectorAll('.featured-panel'));
        function showPanel(id) {
            panels.forEach(p => p.style.display = p.id === id ? 'block' : 'none');
            tabs.forEach(t => {
                const active = t.dataset.target === id;
                t.classList.toggle('bg-gray-900', active);
                t.classList.toggle('text-white', active);
                t.classList.toggle('bg-gray-50', !active);
            });
        }
        if (tabs.length) {
            showPanel(tabs[0].dataset.target);
            tabs.forEach(t => t.addEventListener('click', () => showPanel(t.dataset.target)));
        }
    })();
</script>
@endpush


