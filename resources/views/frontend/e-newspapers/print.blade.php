@extends('frontend.layouts.app')

@php
    $bySection = function (string $section) use ($sections) {
        return $sections->get($section) ?? collect();
    };
    $pick = function (string $section, int $position) use ($bySection) {
        return $bySection($section)->first(fn ($item) => (int) $item->position === $position);
    };

    $manset = $pick('manset', 1);
    $topBar = collect([1, 2, 3])->map(fn ($p) => $pick('gundem', $p))->filter()->values();
    $sideStack = collect([4, 5, 6])->map(fn ($p) => $pick('gundem', $p))->filter()->values();
    $secondManset = $pick('gundem', 7);
    $thirdManset = $pick('gundem', 8);
    $leftRail = collect([1, 2, 3, 4, 5])->map(fn ($p) => $pick('yasam', $p))->filter()->values();

    $paperTitle = mb_strtoupper((string) setting('site_title', 'IBRET GAZETESI'), 'UTF-8');
@endphp

@section('content')
<div class="paper-shell">
    <div class="paper-toolbar">
        <a href="{{ route('enewspapers.show', $issue->slug) }}{{ request()->boolean('preview') ? '?preview=1' : '' }}">Normal Gorunum</a>
        <button type="button" onclick="window.print()">Yazdir / PDF</button>
    </div>

    <article class="newsprint">
        <header class="newsprint-head">
            <div class="newsprint-date">{{ optional($issue->issue_date)->translatedFormat('d F Y, l') }}</div>
            <div class="newsprint-logo">{{ $paperTitle }}</div>
            <div class="newsprint-issue">Sayi {{ $issue->id }}</div>
        </header>

        <section class="newsprint-body">
            <aside class="left-rail">
                @foreach($leftRail as $item)
                    <article class="left-item">
                        <div class="left-kicker">{{ mb_strtoupper($item->category_name ?: 'GENEL', 'UTF-8') }}</div>
                        <h4>{{ \Illuminate\Support\Str::limit($item->title, 75) }}</h4>
                        @if(!empty($item->image) && ($item->show_image ?? true))
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}">
                        @endif
                        <p>{{ $item->summary ?: $item->title }}</p>
                    </article>
                @endforeach
            </aside>

            <main class="main-column">
                <div class="top-row">
                    @foreach($topBar as $item)
                        <a href="{{ $item->post_url }}" class="top-item">
                            @if(!empty($item->image))
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}">
                            @endif
                            <div>{{ \Illuminate\Support\Str::limit($item->title, 72) }}</div>
                        </a>
                    @endforeach
                </div>

                <div class="feature-row">
                    <div class="feature-left-stack">
                        <article class="feature-main">
                            @if($manset)
                                <a href="{{ $manset->post_url }}" class="feature-link">
                                    <div class="feature-media">
                                        @if(!empty($manset->image))
                                            <img src="{{ asset('storage/' . $manset->image) }}" alt="{{ $manset->title }}">
                                        @endif
                                        <h1>{{ \Illuminate\Support\Str::limit($manset->title, 130) }}</h1>
                                    </div>
                                    <div class="feature-copy">
                                        <p class="feature-summary">{{ $manset->summary ?: $manset->title }}</p>
                                    </div>
                                </a>
                            @endif
                        </article>

                        @if($secondManset)
                            <article class="second-manset">
                                <a href="{{ $secondManset->post_url }}">
                                    <h3>{{ \Illuminate\Support\Str::limit($secondManset->title, 120) }}</h3>
                                    <div class="second-manset-body">
                                        @if(!empty($secondManset->image))
                                            <img src="{{ asset('storage/' . $secondManset->image) }}" alt="{{ $secondManset->title }}">
                                        @endif
                                        <p>{{ $secondManset->summary ?: $secondManset->title }}</p>
                                    </div>
                                </a>
                            </article>
                        @endif
                    </div>

                    <div class="feature-side">
                        @foreach($sideStack as $item)
                            <article class="side-item">
                                <a href="{{ $item->post_url }}">
                                    <h4>{{ \Illuminate\Support\Str::limit($item->title, 86) }}</h4>
                                    @if(!empty($item->image))
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}">
                                    @endif
                                    <p>{{ $item->summary ?: $item->title }}</p>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>

                @if($thirdManset)
                    <article class="third-manset">
                        <a href="{{ $thirdManset->post_url }}">
                            <div class="third-manset-inner">
                                @if(!empty($thirdManset->image))
                                    <img src="{{ asset('storage/' . $thirdManset->image) }}" alt="{{ $thirdManset->title }}">
                                @endif
                                <div class="third-manset-body">
                                    <h2>{{ \Illuminate\Support\Str::limit($thirdManset->title, 140) }}</h2>
                                    <p>{{ $thirdManset->summary ?: $thirdManset->title }}</p>
                                </div>
                            </div>
                        </a>
                    </article>
                @endif

            </main>
        </section>
    </article>
</div>
@endsection

@push('styles')
<style>
    .paper-shell { width: 100%; max-width: 1320px; margin: 20px auto 26px; }
    .paper-toolbar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 10px; }
    .paper-toolbar a, .paper-toolbar button {
        height: 36px; padding: 0 12px; border: 1px solid #d6d3d1; border-radius: 10px;
        background: #fff; color: #111827; font-size: 12px; font-weight: 700; text-decoration: none;
        cursor: pointer; display: inline-flex; align-items: center;
    }

    .newsprint {
        background: #f6efdf;
        border: 1px solid #d6c7a9;
        box-shadow: 0 20px 56px rgba(15, 23, 42, 0.16);
        padding: 14px;
        color: #111827;
        font-family: Georgia, "Times New Roman", serif;
    }

    .newsprint-head {
        height: 80px;
        display: grid;
        grid-template-columns: 220px 1fr 140px;
        align-items: end;
        border-bottom: 4px double #111827;
        padding-bottom: 8px;
        margin-bottom: 10px;
    }
    .newsprint-logo { text-align: center; font-size: 52px; font-weight: 700; line-height: .92; letter-spacing: .02em; text-transform: uppercase; }
    .newsprint-date, .newsprint-issue {
        font-size: 12px;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif;
        font-weight: 700;
    }
    .newsprint-issue { text-align: right; }

    .newsprint-body {
        display: grid;
        grid-template-columns: 160px 830px;
        gap: 14px;
        justify-content: center;
    }

    .left-rail { display: flex; flex-direction: column; gap: 8px; }
    .left-item { border-bottom: 1px dotted #7c6f56; padding-bottom: 6px; }
    .left-kicker {
        font-size: 9px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
        border-top: 2px solid #111827; padding-top: 4px; margin-bottom: 3px;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif;
    }
    .left-item h4 { margin: 0 0 4px; font-size: 16px; line-height: 1.13; }
    .left-item img { width: 100%; height: 70px; object-fit: cover; display: block; border: 1px solid #cbbca0; margin-bottom: 4px; }
    .left-item p { margin: 0; font-size: 11px; line-height: 1.35; }

    .main-column { display: flex; flex-direction: column; gap: 10px; }
    .top-row { height: 70px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .top-item {
        display: grid; grid-template-columns: 74px 1fr; gap: 7px; align-items: center;
        text-decoration: none; color: #111827; border: 1px solid #cbbca0; background: #fcf7eb; padding: 4px;
    }
    .top-item img { width: 74px; height: 58px; object-fit: cover; display: block; border: 1px solid #d8ccb2; }
    .top-item div { font-size: 15px; line-height: 1.14; font-weight: 700; }

    .feature-row { display: grid; grid-template-columns: 1fr 255px; gap: 10px; }
    .feature-left-stack { display: flex; flex-direction: column; gap: 10px; }
    .feature-main { border: 1px solid #cbbca0; background: #fffaf0; }
    .feature-link { text-decoration: none; color: #111827; display: block; }
    .feature-media { position: relative; height: 350px; border-bottom: 1px solid #cbbca0; background: #e7dcc6; }
    .feature-media img { width: 100%; height: 350px; object-fit: cover; display: block; }
    .feature-media h1 {
        position: absolute; left: 10px; right: 10px; top: 10px; margin: 0;
        color: #fff; font-size: 34px; line-height: 1.04; font-weight: 700;
        text-shadow: 0 2px 10px rgba(0,0,0,.4);
    }
    .feature-copy { padding: 10px; }
    .feature-summary { margin: 0 0 6px; font-size: 16px; font-weight: 700; line-height: 1.3; }
    .feature-copy p { margin: 0; font-size: 13px; line-height: 1.45; }
    .second-manset {
        border: 1px solid #cbbca0;
        background: #fffaf0;
    }
    .second-manset a {
        text-decoration: none;
        color: #111827;
        display: block;
        padding: 10px;
    }
    .second-manset h3 {
        margin: 0 0 8px;
        font-size: 24px;
        line-height: 1.1;
    }
    .second-manset-body {
        display: grid;
        grid-template-columns: 170px 1fr;
        gap: 8px;
        align-items: start;
    }
    .second-manset-body img {
        width: 170px;
        height: 105px;
        object-fit: cover;
        display: block;
        border: 1px solid #d8ccb2;
        background: #efe8d8;
    }
    .second-manset-body p {
        margin: 0;
        font-size: 12px;
        line-height: 1.4;
    }

    .manset-sub-item {
        margin: 10px;
        border: 1px solid #d8ccb2;
        background: #fcf7eb;
        padding: 8px;
    }
    .manset-sub-item a {
        text-decoration: none;
        color: #111827;
        display: block;
    }
    .manset-sub-item h3 {
        margin: 0 0 6px;
        font-size: 20px;
        line-height: 1.16;
        color: #111827;
    }
    .manset-sub-body {
        display: grid;
        grid-template-columns: 170px 1fr;
        gap: 8px;
        align-items: start;
    }
    .manset-sub-body img {
        width: 170px;
        height: 105px;
        object-fit: cover;
        display: block;
        border: 1px solid #d8ccb2;
        background: #efe8d8;
    }
    .manset-sub-body p {
        margin: 0;
        font-size: 12px;
        line-height: 1.4;
    }
    .third-manset {
        border: 1px solid #cbbca0;
        background: #fffaf0;
    }
    .third-manset a {
        text-decoration: none;
        color: #111827;
        display: block;
    }
    .third-manset-inner {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 10px;
        align-items: start;
        padding: 10px;
    }
    .third-manset img {
        width: 300px;
        height: 190px;
        object-fit: cover;
        display: block;
        border: 1px solid #d8ccb2;
    }
    .third-manset-body {
        padding: 0;
    }
    .third-manset-body h2 {
        margin: 0 0 5px;
        font-size: 26px;
        line-height: 1.08;
    }
    .third-manset-body p {
        margin: 0;
        font-size: 13px;
        line-height: 1.4;
    }

    .feature-side { display: flex; flex-direction: column; gap: 8px; }
    .side-item { border: 1px solid #cbbca0; background: #fcf7eb; }
    .side-item a { text-decoration: none; color: #111827; display: block; padding: 6px; }
    .side-item h4 { margin: 0 0 4px; font-size: 18px; line-height: 1.12; }
    .side-item img { width: 100%; height: 100px; object-fit: cover; display: block; border: 1px solid #d8ccb2; margin-bottom: 4px; }
    .side-item p { margin: 0; font-size: 12px; line-height: 1.35; }

    @media (max-width: 1200px) {
        .newsprint-head { grid-template-columns: 1fr; height: auto; gap: 6px; }
        .newsprint-logo, .newsprint-issue { text-align: left; }
        .newsprint-body { grid-template-columns: 1fr; }
        .feature-row { grid-template-columns: 1fr; }
        .feature-left-stack { gap: 10px; }
        .second-manset-body { grid-template-columns: 1fr; }
        .second-manset-body img { width: 100%; height: 180px; }
        .manset-sub-body { grid-template-columns: 1fr; }
        .manset-sub-body img { width: 100%; height: 180px; }
        .third-manset-inner { grid-template-columns: 1fr; }
        .third-manset img { width: 100%; height: 220px; }
    }
    @media print {
        body { background: #fff !important; }
        .paper-toolbar { display: none !important; }
        .paper-shell { max-width: none; margin: 0; }
        .newsprint { box-shadow: none; border: none; background: #fff; padding: 10mm; }
        @page { size: A3 landscape; margin: 0; }
    }
</style>
@endpush
