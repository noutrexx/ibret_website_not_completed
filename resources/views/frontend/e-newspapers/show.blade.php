@extends('frontend.layouts.app')

@php
    $manset = ($sections->get('manset') ?? collect())->first();
    $gundem = $sections->get('gundem') ?? collect();
    $spor = $sections->get('spor') ?? collect();
    $ekonomi = $sections->get('ekonomi') ?? collect();
    $yasam = $sections->get('yasam') ?? collect();
    $renderCard = function ($item, $h = 220) {
        if (!$item) return '';
        $img = $item->image ? asset('storage/' . $item->image) : null;
        return view('frontend.e-newspapers.partials.card', compact('item', 'img', 'h'))->render();
    };
@endphp

@section('content')
<div style="width:1060px;max-width:100%;margin:0 auto;">
    <section style="margin-top:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
            <div>
                <div style="font-size:11px;color:#64748b;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">E-Gazete</div>
                <h1 style="margin:6px 0 0;font-size:28px;line-height:1.2;color:#0f172a;">{{ $issue->title }}</h1>
                <div style="margin-top:6px;font-size:13px;color:#64748b;">
                    {{ optional($issue->issue_date)->format('d.m.Y') }} · {{ $issue->items->count() }} haber
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <a href="{{ route('enewspapers.print', $issue->slug) }}{{ request()->boolean('preview') ? '?preview=1' : '' }}" style="text-decoration:none;height:38px;padding:0 14px;display:inline-flex;align-items:center;border:1px solid #e5e7eb;border-radius:12px;background:#fff;color:#111827;font-size:12px;font-weight:700;">
                    Gazete Gorunumu
                </a>
                <a href="{{ route('enewspapers.index') }}" style="text-decoration:none;height:38px;padding:0 14px;display:inline-flex;align-items:center;border:1px solid #e5e7eb;border-radius:12px;background:#fff;color:#111827;font-size:12px;font-weight:700;">
                    Arsive Don
                </a>
            </div>
        </div>

        @if($manset)
            <a href="{{ $manset->post_url }}" style="text-decoration:none;display:block;border-radius:14px;overflow:hidden;background:#fff;border:1px solid #eaeef4;box-shadow:0 12px 28px rgba(15,23,42,.06),0 2px 6px rgba(15,23,42,.04);margin-bottom:16px;">
                <div style="display:grid;grid-template-columns:1.05fr 1fr;min-height:360px;">
                    <div style="padding:24px;background:linear-gradient(135deg,#111827 0%,#1f2937 55%,#374151 100%);color:#fff;display:flex;flex-direction:column;justify-content:center;">
                        <div style="display:inline-flex;align-items:center;width:max-content;padding:5px 10px;border-radius:999px;background:rgba(255,255,255,.14);font-size:11px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;">
                            Manset
                        </div>
                        <div style="margin-top:14px;font-size:30px;font-weight:800;line-height:1.15;">{{ $manset->title }}</div>
                        @if($manset->summary)
                            <div style="margin-top:12px;font-size:14px;line-height:1.55;color:rgba(255,255,255,.9);max-width:92%;">
                                {{ \Illuminate\Support\Str::limit($manset->summary, 220) }}
                            </div>
                        @endif
                    </div>
                    <div style="background:#e5e7eb;min-height:360px;">
                        @if($manset->image)
                            <img src="{{ asset('storage/' . $manset->image) }}" alt="{{ $manset->title }}" style="width:100%;height:100%;min-height:360px;object-fit:cover;display:block;">
                        @endif
                    </div>
                </div>
            </a>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
            <div>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;">
                    @foreach($gundem->take(4) as $item)
                        {!! $renderCard($item, 220) !!}
                    @endforeach
                </div>

                @if($spor->isNotEmpty())
                    <div style="margin-top:16px;">
                        <div style="font-size:13px;font-weight:800;color:#111827;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px;">Spor</div>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;">
                            @foreach($spor as $item)
                                {!! $renderCard($item, 180) !!}
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($ekonomi->isNotEmpty())
                    <div style="margin-top:16px;">
                        <div style="font-size:13px;font-weight:800;color:#111827;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px;">Ekonomi</div>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;">
                            @foreach($ekonomi as $item)
                                {!! $renderCard($item, 180) !!}
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside>
                <div style="border-radius:14px;background:#fff;border:1px solid #eaeef4;box-shadow:0 10px 22px rgba(15,23,42,.05),0 2px 4px rgba(15,23,42,.03);padding:14px;">
                    <div style="font-size:13px;font-weight:800;color:#111827;letter-spacing:.04em;text-transform:uppercase;margin-bottom:10px;">Kisa Haberler</div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach($gundem->slice(4)->concat($yasam)->take(8) as $item)
                            <a href="{{ $item->post_url }}" style="text-decoration:none;display:block;padding:10px;border-radius:10px;background:#f8fafc;border:1px solid #eef2f7;">
                                <div style="font-size:12px;color:#64748b;">{{ $item->category_name ?: 'Genel' }}</div>
                                <div style="margin-top:4px;font-size:13px;font-weight:700;line-height:1.35;color:#111827;">
                                    {{ \Illuminate\Support\Str::limit($item->title, 80) }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </section>
</div>
@endsection
