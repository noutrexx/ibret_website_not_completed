@extends('layouts.admin')

@section('content')
<div class="container-fluid">
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

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Yeni E-Gazete Taslagi</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.e-newspapers.generate') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sayi Tarihi</label>
                            <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="small text-muted mb-3">
                            Sistem secili tarihe gore yayinlanmis haberlerden otomatik bir e-gazete taslagi olusturur/gunceller.
                        </div>
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="fa fa-wand-magic-sparkles me-1"></i> Taslak Olustur / Guncelle
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">E-Gazete Sayilari</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Baslik</th>
                                    <th>Tarih</th>
                                    <th>Durum</th>
                                    <th>Item</th>
                                    <th>Islemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($issues as $issue)
                                    <tr>
                                        <td>{{ $issue->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $issue->title }}</div>
                                            <div class="small text-muted">{{ $issue->slug }}</div>
                                        </td>
                                        <td>{{ optional($issue->issue_date)->format('d.m.Y') }}</td>
                                        <td>
                                            @if($issue->status === 'published')
                                                <span class="badge bg-success">Yayinda</span>
                                            @else
                                                <span class="badge bg-secondary">Taslak</span>
                                            @endif
                                        </td>
                                        <td>{{ (int) $issue->items_count }}</td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <a href="{{ route('enewspapers.show', $issue->slug) }}{{ $issue->status !== 'published' ? '?preview=1' : '' }}" class="btn btn-sm btn-outline-dark" target="_blank">
                                                    <i class="fa fa-eye me-1"></i> Onizle
                                                </a>
                                                <a href="{{ route('admin.e-newspapers.edit', $issue->id) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pen me-1"></i> Haber Sec
                                                </a>
                                                <a href="{{ route('enewspapers.print', $issue->slug) }}{{ $issue->status !== 'published' ? '?preview=1' : '' }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fa fa-file-lines me-1"></i> Gazete Gorunumu
                                                </a>
                                                @if($issue->status === 'published')
                                                    <form method="POST" action="{{ route('admin.e-newspapers.unpublish', $issue->id) }}">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-warning" type="submit">Taslak Yap</button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.e-newspapers.publish', $issue->id) }}">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success" type="submit">Yayinla</button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('admin.e-newspapers.destroy', $issue->id) }}" onsubmit="return confirm('Bu e-gazete silinsin mi?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Sil</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Henuz e-gazete sayisi yok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                {{ $issues->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
