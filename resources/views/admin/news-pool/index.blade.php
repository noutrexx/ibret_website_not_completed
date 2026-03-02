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
        <div class="alert alert-danger shadow-sm border-0">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
        <h3 class="mb-0">Haber Havuzu</h3>
        <div class="d-flex gap-2 flex-wrap">
            <form action="{{ route('admin.news-pool.ingest') }}" method="POST" class="d-flex gap-2">
                @csrf
                <select name="source_id" class="form-select">
                    <option value="">Tum kaynaklar</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                </select>
                <input type="number" min="1" max="100" name="limit" value="30" class="form-control" style="width:90px;">
                <button class="btn btn-primary"><i class="fa fa-download me-1"></i> RSS Cek</button>
            </form>

            <form action="{{ route('admin.news-pool.clear-drafts') }}" method="POST" onsubmit="return confirm('Tum taslak haber havuzu kayitlari silinsin mi?');">
                @csrf
                <button class="btn btn-outline-danger"><i class="fa fa-trash me-1"></i> Tum Taslaklari Temizle</button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body">Taslak: <strong>{{ $stats['draft'] }}</strong></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body">Onayli: <strong>{{ $stats['approved'] }}</strong></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body">Reddedilen: <strong>{{ $stats['rejected'] }}</strong></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body">Yayinlanan: <strong>{{ $stats['published'] }}</strong></div></div></div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Durum</label>
                    <select name="status" class="form-select">
                        <option value="draft" @selected($status === 'draft')>Taslak</option>
                        <option value="approved" @selected($status === 'approved')>Onayli</option>
                        <option value="rejected" @selected($status === 'rejected')>Reddedilen</option>
                        <option value="published" @selected($status === 'published')>Yayinlanan</option>
                        <option value="all" @selected($status === 'all')>Tumu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Kaynak</label>
                    <select name="source_id" class="form-select">
                        <option value="">Tum kaynaklar</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}" @selected((int)($sourceId ?? 0) === (int)$source->id)>{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Tarih</label>
                    <select name="date_range" class="form-select">
                        <option value="" @selected(($dateRange ?? '') === '')>Tum zamanlar</option>
                        <option value="today" @selected(($dateRange ?? '') === 'today')>Bugun</option>
                        <option value="24h" @selected(($dateRange ?? '') === '24h')>Son 24 saat</option>
                        <option value="7d" @selected(($dateRange ?? '') === '7d')>Son 7 gun</option>
                        <option value="30d" @selected(($dateRange ?? '') === '30d')>Son 30 gun</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Ara</label>
                    <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="Baslik / URL">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-secondary w-100">Filtrele</button>
                    <a href="{{ route('admin.news-pool.index') }}" class="btn btn-light border">Sifirla</a>
                </div>
            </form>
        </div>
    </div>

    <form action="{{ route('admin.news-pool.bulk') }}" method="POST" id="newsPoolBulkForm">
        @csrf

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Toplu Islem</label>
                        <select name="action" id="bulkActionSelect" class="form-select" required>
                            <option value="">Seciniz</option>
                            <option value="mark_approved">Secilileri onayli yap</option>
                            <option value="mark_draft">Secilileri taslak yap</option>
                            <option value="reject">Secilileri reddet</option>
                            <option value="set_category">Secililere kategori ata</option>
                            <option value="delete">Secilileri sil</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Kategori (opsiyonel / atama icin gerekli)</label>
                        <select name="category_id" id="bulkCategorySelect" class="form-select">
                            <option value="">Kategori sec</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100" onclick="return confirmBulkAction();">Uygula</button>
                    </div>
                    <div class="col-md-4 text-md-end small text-muted">
                        Tekrar uyarisi: ayni slug'a sahip havuz kayitlari veya yayinlanmis haberde ayni slug varsa gosterilir.
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:42px;">
                                <input type="checkbox" id="selectAllNewsPool" class="form-check-input">
                            </th>
                            <th>ID</th>
                            <th>Kaynak</th>
                            <th>Baslik</th>
                            <th>Kategori</th>
                            <th>Tarih</th>
                            <th>AI</th>
                            <th>Durum</th>
                            <th class="text-end pe-3">Islem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $duplicateCount = (int) ($duplicateSlugCounts[$item->slug] ?? 0);
                                $hasPoolDuplicate = $duplicateCount > 1;
                                $hasPublishedDuplicate = isset($existingPostSlugs[$item->slug]);
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $item->id }}" class="form-check-input news-pool-checkbox">
                                </td>
                                <td>#{{ $item->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->source->name ?? '-' }}</div>
                                    @if($item->source_url)
                                        <a href="{{ $item->source_url }}" target="_blank" class="small text-muted">Kaynagi ac</a>
                                    @endif
                                </td>
                                <td style="min-width:380px;">
                                    <div class="fw-semibold">{{ $item->ai_title ?: $item->title }}</div>
                                    <div class="small text-muted">{{ Str::limit(strip_tags((string) ($item->ai_summary ?: $item->summary ?: $item->content)), 120) }}</div>
                                    @if($hasPoolDuplicate || $hasPublishedDuplicate)
                                        <div class="mt-1 d-flex gap-1 flex-wrap">
                                            @if($hasPoolDuplicate)
                                                <span class="badge bg-warning text-dark">Tekrar Havuz ({{ $duplicateCount }})</span>
                                            @endif
                                            @if($hasPublishedDuplicate)
                                                <span class="badge bg-danger">Yayinda Benzeri Var</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $item->category->name ?? '-' }}</td>
                                <td>{{ optional($item->source_published_at)->format('d.m.Y H:i') ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->ai_status === 'processed' ? 'success' : ($item->ai_status === 'failed' ? 'danger' : ($item->ai_status === 'processing' ? 'warning text-dark' : 'secondary')) }}">
                                        {{ strtoupper($item->ai_status ?: 'pending') }}
                                    </span>
                                    @if($item->ai_error)
                                        <div class="small text-danger mt-1">{{ Str::limit($item->ai_error, 80) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $item->status === 'draft' ? 'secondary' : ($item->status === 'published' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'warning')) }}">
                                        {{ strtoupper($item->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    @if($item->status !== 'published')
                                        <a href="{{ route('admin.news-pool.edit', $item->id) }}" class="btn btn-sm btn-primary">Duzenle</a>
                                    @else
                                        @if($item->publishedPost)
                                            <a href="{{ route('admin.posts.edit', $item->publishedPost->id) }}" class="btn btn-sm btn-outline-primary">Habere Git</a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Haber havuzu bos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white fw-semibold">Kaynak Durumu</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kaynak</th>
                        <th>Aktif</th>
                        <th>Son Cekim</th>
                        <th>Son Hata</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sources as $source)
                        <tr>
                            <td>{{ $source->name }}</td>
                            <td>{{ $source->is_active ? 'Evet' : 'Hayir' }}</td>
                            <td>{{ optional($source->last_fetched_at)->format('d.m.Y H:i') ?: '-' }}</td>
                            <td class="small text-danger">{{ $source->last_error ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    const selectAll = document.getElementById('selectAllNewsPool');
    const rowChecks = Array.from(document.querySelectorAll('.news-pool-checkbox'));

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowChecks.forEach(cb => cb.checked = selectAll.checked);
        });
    }

    rowChecks.forEach(cb => cb.addEventListener('change', function () {
        if (!selectAll) return;
        selectAll.checked = rowChecks.length > 0 && rowChecks.every(x => x.checked);
    }));
})();

function confirmBulkAction() {
    const form = document.getElementById('newsPoolBulkForm');
    const checked = form ? form.querySelectorAll('.news-pool-checkbox:checked').length : 0;
    const action = document.getElementById('bulkActionSelect')?.value || '';
    const category = document.getElementById('bulkCategorySelect')?.value || '';

    if (!checked) {
        alert('Lutfen en az bir kayit secin.');
        return false;
    }
    if (!action) {
        alert('Lutfen bir toplu islem secin.');
        return false;
    }
    if (action === 'set_category' && !category) {
        alert('Kategori atama icin kategori secin.');
        return false;
    }

    return confirm(`${checked} kayit icin toplu islem uygulanacak. Devam edilsin mi?`);
}
</script>
@endsection
