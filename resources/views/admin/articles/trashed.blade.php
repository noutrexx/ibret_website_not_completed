@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">
                <i class="fa fa-trash text-danger me-2"></i> Makaleler - Çöp Kutusu
            </h3>
            <p class="text-muted small mb-0">Silinen makaleleri buradan geri yükleyebilir veya kalıcı silebilirsin.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.articles.index') }}" class="btn btn-light border shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Listeye Dön
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0">
            {{ session('success') }}
        </div>
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

    <!-- FİLTRELER -->
    <form method="GET" action="{{ route('admin.articles.trashed') }}" class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <div class="col-lg-5">
                    <label class="form-label fw-bold">Arama</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control bg-light border-0 shadow-sm"
                           placeholder="Başlığa göre ara...">
                </div>

                <div class="col-lg-3">
                    <label class="form-label fw-bold">Kategori</label>
                    <select name="category" class="form-select bg-light border-0 shadow-sm">
                        <option value="">Tümü</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label fw-bold">Durum</label>
                    <select name="status" class="form-select bg-light border-0 shadow-sm">
                        <option value="">Tümü</option>
                        <option value="draft" @selected(request('status') === 'draft')>Taslak</option>
                        <option value="published" @selected(request('status') === 'published')>Yayında</option>
                    </select>
                </div>

                <div class="col-lg-2 d-flex gap-2">
                    <button class="btn btn-danger w-100">
                        <i class="fa fa-filter me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('admin.articles.trashed') }}" class="btn btn-light border w-100">
                        Sıfırla
                    </a>
                </div>

            </div>
        </div>
    </form>

    <!-- LİSTE -->
    <div class="card shadow border-0 rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Yazar</th>
                        <th>Başlık</th>
                        <th>Kategori</th>
                        <th>Silinme</th>
                        <th class="text-center">Durum</th>
                        <th class="text-center">İşlem</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($articles as $article)
                    <tr>
                        <td class="ps-4">{{ $article->user?->name ?? '—' }}</td>

                        <td>
                            <strong>{{ \Illuminate\Support\Str::limit($article->title, 60) }}</strong>
                            <div class="small text-muted">{{ $article->slug }}</div>
                        </td>

                        <td>{{ $article->category?->name ?? '—' }}</td>

                        <td>{{ $article->deleted_at?->format('d.m.Y H:i') ?? '—' }}</td>

                        <td class="text-center">
                            @if($article->status === 'published')
                                <span class="badge bg-success">Yayında</span>
                            @else
                                <span class="badge bg-warning">Taslak</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="btn-group">

                                <a href="{{ route('admin.articles.restore', $article->id) }}"
                                   class="btn btn-sm btn-light border"
                                   onclick="return confirm('Bu makaleyi geri yüklemek istiyor musunuz?')">
                                    <i class="fa fa-undo text-success"></i>
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.articles.forceDelete', $article->id) }}"
                                      onsubmit="return confirm('Kalıcı olarak silinsin mi? Bu işlem geri alınamaz!')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-light border">
                                        <i class="fa fa-times text-danger"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            Çöp kutusunda makale yok.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($articles->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $articles->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
