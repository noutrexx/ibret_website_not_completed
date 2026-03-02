@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="{{ route('admin.posts.index') }}" class="btn btn-primary shadow-sm"><i class="fa fa-list me-1"></i> Tümü</a>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-success shadow-sm"><i class="fa fa-plus me-1"></i> Yeni Ekle</a>
            <a href="{{ route('admin.posts.trashed') }}" class="btn btn-outline-danger shadow-sm ms-auto"><i class="fa fa-trash"></i> Silinenler</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3 border-0">
        <div class="card-body">
            <form action="{{ route('admin.posts.index') }}" method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Haber başlığı ile ara..." value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">Tüm kategoriler</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) request('category') === (string) $cat->id)>
                                {{ $cat->parent ? $cat->parent->name . ' > ' : '' }}{{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Tüm türler</option>
                        <option value="manset" @selected(request('type') === 'manset')>Manşet</option>
                        <option value="surmanset" @selected(request('type') === 'surmanset')>Sürmanşet</option>
                        <option value="top_manset" @selected(request('type') === 'top_manset')>Top Manşet</option>
                        <option value="spor_manset" @selected(request('type') === 'spor_manset')>Spor Manşet</option>
                        <option value="ekonomi_manset" @selected(request('type') === 'ekonomi_manset')>Ekonomi Manşet</option>
                        <option value="normal" @selected(request('type') === 'normal')>Normal</option>
                        <option value="gizli" @selected(request('type') === 'gizli')>Gizli</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary w-100"><i class="fa fa-sync"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Aktif Haber Listesi</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="px-4">ID</th>
                            <th>Resim</th>
                            <th>Başlık</th>
                            <th>Kategori</th>
                            <th>Ekleyen</th>
                            <th>Tür</th>
                            <th class="text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                        <tr>
                            <td class="px-4 text-muted">#{{ $post->id }}</td>
                            <td>
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" class="rounded shadow-sm" style="width:70px;height:45px;object-fit:cover;" alt="">
                                @else
                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center rounded" style="width:70px;height:45px;font-size:10px;">Resim yok</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ Str::limit($post->title, 65) }}</div>
                                <div class="small text-muted">
                                    <i class="fa fa-calendar-alt me-1"></i> {{ $post->created_at->format('d.m.Y H:i') }}
                                    <span class="mx-1">|</span>
                                    <i class="fa fa-eye me-1"></i> {{ $post->view_count }}
                                    @if($post->is_breaking)
                                        <span class="badge bg-danger ms-2">Son Dakika</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $post->category?->parent?->name ? $post->category->parent->name . ' > ' : '' }}{{ $post->category->name ?? 'Genel' }}
                                </span>
                            </td>
                            <td>{{ $post->user->name ?? 'Admin' }}</td>
                            <td>
                                @php
                                    $badges = [
                                        'manset' => 'bg-danger',
                                        'surmanset' => 'bg-warning text-dark',
                                        'top_manset' => 'bg-primary',
                                        'spor_manset' => 'bg-success',
                                        'ekonomi_manset' => 'bg-info text-dark',
                                        'normal' => 'bg-secondary',
                                        'gizli' => 'bg-dark',
                                    ];
                                @endphp
                                <span class="badge {{ $badges[$post->type] ?? 'bg-secondary' }}">{{ strtoupper($post->type) }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group gap-1">
                                    <a href="{{ $post->frontend_url }}" target="_blank" class="btn btn-sm btn-outline-secondary shadow-sm" title="Sitede Gör">
                                        <i class="fa fa-external-link-alt"></i>
                                    </a>
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-sm btn-outline-warning shadow-sm" title="Düzenle">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="Çöp kutusuna taşı" onclick="return confirm('Bu haberi çöpe atmak istiyor musunuz?')">
                                            <i class="fa fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa fa-folder-open fa-3x mb-3"></i>
                                    <p class="mb-3">Henüz haber eklenmedi.</p>
                                    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">Yeni haber ekle</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($posts->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
