@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-11 mx-auto">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0">
                    <i class="fa fa-pen-fancy me-2 text-primary"></i> Yeni Makale
                </h3>
                <a href="{{ route('admin.articles.index') }}" class="btn btn-light border shadow-sm px-4">
                    <i class="fa fa-arrow-left me-1"></i> Listeye Dön
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger shadow-sm border-0">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.articles.store') }}" method="POST">
                @csrf

                <div class="row g-4">

                    <!-- SOL -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-body p-4">

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Makale Başlığı</label>
                                    <input type="text"
                                           name="title"
                                           value="{{ old('title') }}"
                                           class="form-control form-control-lg bg-light border-0 shadow-sm"
                                           required>
                                </div>

                                <div>
                                    <label class="form-label fw-bold">Makale İçeriği</label>
                                    <textarea name="content"
                                              id="article_editor"
                                              class="form-control"
                                              rows="20">{{ old('content') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- SAĞ -->
                    <div class="col-lg-4">

                        <!-- YAZAR -->
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 fw-bold">
                                <i class="fa fa-user-edit me-2"></i> Yazar
                            </div>
                            <div class="card-body">
                                <select name="author_id" class="form-select bg-light border-0 shadow-sm">
                                    <option value="">(Varsayılan: Ben)</option>
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}" @selected(old('author_id') == $author->id)>
                                            {{ $author->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-2">
                                    Boş bırakırsan giriş yapan kullanıcı atanır.
                                </div>
                            </div>
                        </div>

                        <!-- KATEGORİ -->
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 fw-bold">
                                <i class="fa fa-folder me-2"></i> Kategori
                            </div>
                            <div class="card-body">
                                <select name="category_id" class="form-select bg-light border-0 shadow-sm">
                                    <option value="">Kategori Yok</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- DURUM -->
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-header bg-white border-0 fw-bold">
                                <i class="fa fa-cog me-2"></i> Yayın Durumu
                            </div>
                            <div class="card-body">
                                <select name="status" class="form-select bg-light border-0 shadow-sm mb-3" required>
                                    <option value="draft" @selected(old('status', 'draft') === 'draft')>Taslak</option>
                                    <option value="published" @selected(old('status') === 'published')>Yayında</option>
                                </select>

                                <button type="submit" class="btn btn-primary w-100 fw-bold py-3">
                                    <i class="fa fa-cloud-upload-alt me-2"></i> Kaydet
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.19.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('article_editor', {
        height: 450,
        language: 'tr'
    });
</script>
@endsection
