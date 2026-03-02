@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-0">{{ session('error') }}</div>
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Haber Havuzu Duzenle #{{ $item->id }}</h3>
        <a href="{{ route('admin.news-pool.index') }}" class="btn btn-outline-secondary">Geri Don</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <form action="{{ route('admin.news-pool.update', $item->id) }}" method="POST" class="card shadow-sm border-0">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Kategori sec</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((int) old('category_id', $item->category_id) === (int) $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Baslik (Manuel)</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $item->title) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ozet (Manuel)</label>
                        <textarea name="summary" rows="4" class="form-control">{{ old('summary', $item->summary) }}</textarea>
                    </div>

                    @include('admin.components.rich-editor-field', [
                        'label' => 'Icerik (Manuel)',
                        'name' => 'content',
                        'id' => 'manual_editor',
                        'value' => old('content', $item->content),
                        'rows' => 10,
                    ])

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">AI Baslik</label>
                        <input type="text" name="ai_title" class="form-control" value="{{ old('ai_title', $item->ai_title) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">AI Ozet</label>
                        <textarea name="ai_summary" rows="4" class="form-control">{{ old('ai_summary', $item->ai_summary) }}</textarea>
                    </div>

                    @include('admin.components.rich-editor-field', [
                        'label' => 'AI Icerik (Haber)',
                        'name' => 'ai_content',
                        'id' => 'ai_editor',
                        'value' => old('ai_content', $item->ai_content),
                        'rows' => 10,
                    ])

                    <div class="mb-3">
                        <label class="form-label fw-semibold">AI Anahtar Kelimeler</label>
                        <input type="text" name="ai_keywords" class="form-control" value="{{ old('ai_keywords', $item->ai_keywords) }}" placeholder="etiket1,etiket2,etiket3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Durum</label>
                        <select name="status" class="form-select">
                            @foreach(['draft' => 'Taslak', 'approved' => 'Onayli', 'rejected' => 'Reddedilen', 'published' => 'Yayinlanan'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $item->status) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-semibold">Kaynak</div>
                <div class="card-body small">
                    <div><strong>Kaynak Adi:</strong> {{ $item->source->name ?? '-' }}</div>
                    <div><strong>Kaynak URL:</strong> <a href="{{ $item->source_url }}" target="_blank">Ac</a></div>
                    <div><strong>Tarih:</strong> {{ optional($item->source_published_at)->format('d.m.Y H:i') ?: '-' }}</div>
                    <div><strong>AI Durumu:</strong> {{ strtoupper($item->ai_status ?: 'pending') }}</div>
                    @if($item->ai_error)
                        <div class="text-danger mt-2"><strong>AI Hata:</strong> {{ $item->ai_error }}</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Islemler</div>
                <div class="card-body d-grid gap-2">
                    <form action="{{ route('admin.news-pool.ai.rewrite', $item->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-dark w-100">AI ile Oto Duzenle (Haber)</button>
                    </form>

                    @if($item->status !== 'published')
                        <form action="{{ route('admin.news-pool.approve', $item->id) }}" method="POST" class="d-grid gap-2">
                            @csrf
                            <select name="category_id" class="form-select">
                                <option value="">Kategori sec</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected((int) $item->category_id === (int) $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <select name="type" class="form-select">
                                @foreach(['normal' => 'Normal', 'manset' => 'Manset', 'surmanset' => 'Surmanset', 'top_manset' => 'Top Manset', 'spor_manset' => 'Spor Manset', 'ekonomi_manset' => 'Ekonomi Manset', 'gizli' => 'Gizli'] as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <label class="small d-flex align-items-center gap-1">
                                <input type="checkbox" name="is_breaking" value="1"> Son Dakika
                            </label>
                            <button class="btn btn-success w-100">Yayina Al</button>
                        </form>
                    @endif

                    <form action="{{ route('admin.news-pool.reject', $item->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-danger w-100">Reddet</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.media._modal')
@include('admin.media._manager_script')
<script>
    window.addEventListener('DOMContentLoaded', function () {
        initCkEditorWithMedia('#manual_editor');
        initCkEditorWithMedia('#ai_editor');
    });
</script>
@endsection
