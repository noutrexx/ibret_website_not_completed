@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-11 mx-auto">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0">
                    <i class="fa fa-edit me-2 text-warning"></i> Makale Düzenle
                </h3>
                <a href="{{ route('admin.articles.index') }}"
                   class="btn btn-light border shadow-sm px-4">
                    <i class="fa fa-arrow-left me-1"></i> Listeye Dön
                </a>
            </div>

            <form action="{{ route('admin.articles.update', $article->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">

                    <!-- SOL -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-body p-4">

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Makale Başlığı</label>
                                    <input type="text"
                                           name="title"
                                           value="{{ old('title', $article->title) }}"
                                           class="form-control form-control-lg bg-light border-0 shadow-sm"
                                           required>
                                </div>

                                <div>
                                    <label class="form-label fw-bold">Makale İçeriği</label>
                                    <textarea name="content"
                                              id="article_editor"
                                              class="form-control"
                                              rows="20">{{ old('content', $article->content) }}</textarea>
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
                                <select name="author_id"
                                        class="form-select bg-light border-0 shadow-sm">
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}"
                                            @selected($article->user_id == $author->id)>
                                            {{ $author->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- KATEGORİ -->
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white border-0 fw-bold">
                                <i class="fa fa-folder me-2"></i> Kategori
                            </div>
                            <div class="card-body">
                                <select name="category_id"
                                        class="form-select bg-light border-0 shadow-sm">
                                    <option value="">Kategori Yok</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            @selected($article->category_id == $category->id)>
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

                                <select name="status"
                                        class="form-select bg-light border-0 shadow-sm mb-3">
                                    <option value="draft"
                                        @selected($article->status === 'draft')>
                                        Taslak
                                    </option>
                                    <option value="published"
                                        @selected($article->status === 'published')>
                                        Yayında
                                    </option>
                                </select>

                                <button type="submit"
                                        class="btn btn-warning w-100 fw-bold py-3">
                                    <i class="fa fa-save me-2"></i> Güncelle
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
