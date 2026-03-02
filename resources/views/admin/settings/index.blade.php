@extends('layouts.admin')

@section('content')
@php
    $grouped = $settings->groupBy('group');
    $groupLabels = [
        'general' => 'Genel',
        'seo' => 'SEO',
        'cache' => 'Cache',
        'sports' => 'Spor',
        'economy' => 'Ekonomi',
    ];
    $ttlOptions = [
        30 => '30 sn',
        60 => '1 dk',
        300 => '5 dk',
        600 => '10 dk',
        900 => '15 dk',
        1800 => '30 dk',
        3600 => '60 dk',
    ];
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-md-11 mx-auto">
            <h3 class="fw-bold mb-4"><i class="fa fa-cogs text-primary me-2"></i> Site Ayarları</h3>

            @if(session('success'))
                <div class="alert alert-success shadow-sm border-0">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger shadow-sm border-0">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf

                <ul class="nav nav-tabs mb-3">
                    @foreach($grouped as $group => $items)
                        <li class="nav-item">
                            <button class="nav-link @if($loop->first) active @endif" data-bs-toggle="tab" data-bs-target="#tab-{{ $group }}" type="button">
                                {{ $groupLabels[$group] ?? ucfirst($group) }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content bg-white p-4 rounded shadow-sm border">
                    @foreach($grouped as $group => $items)
                        <div class="tab-pane fade @if($loop->first) show active @endif" id="tab-{{ $group }}">
                            <div class="row g-4">
                                @if($group === 'seo')
                                    <div class="col-12">
                                        <div class="card shadow-sm border-0 rounded-4">
                                            <div class="card-header bg-white border-0 fw-bold"><i class="fa fa-sitemap me-2"></i> Sitemap</div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="fw-bold mb-1">Sitemap URL</div>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <input class="form-control bg-light" value="{{ $sitemapUrl ?? url('/sitemap.xml') }}" readonly>
                                                        <a href="{{ $sitemapUrl ?? url('/sitemap.xml') }}" target="_blank" class="btn btn-light border">Aç</a>
                                                    </div>
                                                    <div class="small text-muted mt-2">Search Console ve Bing Webmaster Tools için bu URL kullanılabilir.</div>
                                                </div>

                                                <div class="p-3 bg-light rounded-3 mb-3">
                                                    <div class="small text-muted">Sitemap içeriğine giren yayınlı içerik</div>
                                                    <div class="fs-4 fw-bold">{{ number_format((int)($sitemapCount ?? 0)) }}</div>
                                                </div>

                                                <button class="btn btn-primary fw-bold" type="submit" formmethod="POST" formaction="{{ route('admin.settings.seo.sitemapPing') }}" onclick="return confirm('Google ve Bing için ping gönderilsin mi?')">
                                                    <i class="fa fa-paper-plane me-2"></i> Arama Motorlarına Bildir
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @foreach($items as $setting)
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">{{ ucfirst(str_replace('_', ' ', $setting->key)) }}</label>

                                        @if($setting->type === 'text')
                                            @php
                                                $isCacheTtl = $group === 'cache' && str_starts_with($setting->key, 'cache_ttl_');
                                            @endphp
                                            @if($isCacheTtl)
                                                <select name="settings[{{ $setting->key }}]" class="form-select">
                                                    @foreach($ttlOptions as $seconds => $label)
                                                        <option value="{{ $seconds }}" @selected((int) old('settings.' . $setting->key, $setting->value) === (int) $seconds)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" name="settings[{{ $setting->key }}]" value="{{ old('settings.' . $setting->key, $setting->value) }}" class="form-control">
                                            @endif
                                        @elseif($setting->type === 'textarea')
                                            <textarea name="settings[{{ $setting->key }}]" rows="4" class="form-control">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                        @elseif($setting->type === 'bool')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="settings[{{ $setting->key }}]" value="1" @checked((int) old('settings.' . $setting->key, $setting->value) === 1)>
                                                <label class="form-check-label">Aktif</label>
                                            </div>
                                        @elseif($setting->type === 'image')
                                            <input type="file" name="settings[{{ $setting->key }}]" class="form-control">
                                            @if($setting->value)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $setting->value) }}" alt="{{ $setting->key }}" style="max-height:80px;">
                                                </div>
                                            @endif
                                        @else
                                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ old('settings.' . $setting->key, $setting->value) }}" class="form-control">
                                        @endif

                                        @if($setting->is_public)
                                            <div class="small text-muted mt-1">Frontend tarafında kullanılır.</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-end mt-4">
                    <button class="btn btn-primary px-5 py-2 fw-bold"><i class="fa fa-save me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
